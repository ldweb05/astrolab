<?php
require_once __DIR__ . '/AiProviderInterface.php';

/**
 * Implementazione del provider AI Agent per Google Gemini.
 *
 * Nessuna eccezione viene mai lasciata propagare: qualunque errore
 * (chiave assente, rete, timeout, HTTP non-200, quota esaurita, risposta
 * malformata) viene intercettato e restituito nel campo 'errore'.
 *
 * Oltre al contratto minimo di AiProviderInterface (chiedi), espone due
 * metodi aggiuntivi specifici di Gemini per il tool calling:
 * chiediConFunzione() e rispondiConRisultatoFunzione(). Questi non fanno
 * parte del contratto generico del provider (Sezione 11): un domani, con
 * un provider diverso, l'orchestrazione del tool calling andra' scritta
 * in modo specifico per quel provider, riusando pero' lo stesso tool
 * deterministico (es. RicercaRsmTool) che non conosce alcun provider AI.
 */
class GeminiProvider implements AiProviderInterface
{
    private string $modello;
    private bool $abilitaThinking;
    private int $timeoutSecondi;

    /**
     * @param bool   $abilitaThinking Se false (default), riduce il
     *   "thinking" del modello al minimo per non consumare inutilmente la
     *   quota free-tier su richieste semplici.
     * @param string $modello Nome del modello Gemini da usare.
     * @param int    $timeoutSecondi Timeout della chiamata HTTP.
     */
    public function __construct(
        bool $abilitaThinking = false,
        string $modello = 'gemini-3.6-flash',
        int $timeoutSecondi = 20
    ) {
        $this->abilitaThinking = $abilitaThinking;
        $this->modello = $modello;
        $this->timeoutSecondi = $timeoutSecondi;
    }

    public function chiedi(string $prompt): array
    {
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
        ];
        $this->applicaThinkingConfig($payload);

        $esito = $this->eseguiRichiesta($payload);
        if (!$esito['ok']) {
            return ['ok' => false, 'testo' => null, 'errore' => $esito['errore']];
        }

        $testo = $esito['dati']['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($testo === null || $testo === '') {
            return ['ok' => false, 'testo' => null, 'errore' => 'risposta priva di testo utilizzabile'];
        }

        return ['ok' => true, 'testo' => (string) $testo, 'errore' => null];
    }

    /**
     * Invia un prompt insieme alla dichiarazione di un tool disponibile.
     * Gemini decide autonomamente se rispondere con testo libero o
     * chiedere di invocare il tool.
     *
     * @param array $dichiarazioneFunzione Formato Gemini: name, description, parameters (JSON Schema semplificato).
     * @return array{tipo: string, testo: string|null, nome: string|null,
     *               argomenti: array|null, model_content: array|null, errore: string|null}
     *   tipo e' uno tra: 'testo', 'function_call', 'errore'.
     */
    public function chiediConFunzione(string $prompt, array $dichiarazioneFunzione): array
    {
        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $prompt]]],
            ],
            'tools' => [
                ['functionDeclarations' => [$dichiarazioneFunzione]],
            ],
        ];
        $this->applicaThinkingConfig($payload);

        $esito = $this->eseguiRichiesta($payload);
        if (!$esito['ok']) {
            return $this->rispostaFunzioneErrore($esito['errore']);
        }

        $content = $esito['dati']['candidates'][0]['content'] ?? null;
        if (!is_array($content) || !isset($content['parts']) || !is_array($content['parts'])) {
            return $this->rispostaFunzioneErrore('risposta priva di contenuto interpretabile');
        }

        foreach ($content['parts'] as $part) {
            if (isset($part['functionCall']['name'])) {
                return [
                    'tipo' => 'function_call',
                    'testo' => null,
                    'nome' => (string) $part['functionCall']['name'],
                    'argomenti' => $part['functionCall']['args'] ?? [],
                    'model_content' => $content,
                    'errore' => null,
                ];
            }
        }

        foreach ($content['parts'] as $part) {
            if (isset($part['text']) && $part['text'] !== '') {
                return [
                    'tipo' => 'testo',
                    'testo' => (string) $part['text'],
                    'nome' => null,
                    'argomenti' => null,
                    'model_content' => null,
                    'errore' => null,
                ];
            }
        }

        return $this->rispostaFunzioneErrore('risposta priva di testo o function call utilizzabile');
    }

    /**
     * Prosegue la conversazione dopo aver eseguito realmente il tool
     * richiesto da chiediConFunzione(), passando a Gemini il risultato
     * vero e chiedendo la spiegazione finale in linguaggio naturale.
     *
     * @param string $promptOriginale Il prompt utente del primo turno.
     * @param array  $modelContent Il campo 'model_content' restituito da chiediConFunzione().
     * @param string $nomeFunzione Nome della funzione invocata.
     * @param array  $risultatoFunzione Risultato reale del tool (es. RicercaRsmTool::cerca()).
     * @param array  $dichiarazioneFunzione Stessa dichiarazione usata nel primo turno.
     * @return array{ok: bool, testo: string|null, errore: string|null}
     */
    public function rispondiConRisultatoFunzione(
        string $promptOriginale,
        array $modelContent,
        string $nomeFunzione,
        array $risultatoFunzione,
        array $dichiarazioneFunzione
    ): array {
        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $promptOriginale]]],
                $modelContent,
                [
                    'role' => 'user',
                    'parts' => [[
                        'functionResponse' => [
                            'name' => $nomeFunzione,
                            'response' => $risultatoFunzione,
                        ],
                    ]],
                ],
            ],
            'tools' => [
                ['functionDeclarations' => [$dichiarazioneFunzione]],
            ],
        ];
        $this->applicaThinkingConfig($payload);

        $esito = $this->eseguiRichiesta($payload);
        if (!$esito['ok']) {
            return ['ok' => false, 'testo' => null, 'errore' => $esito['errore']];
        }

        $testo = $esito['dati']['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($testo === null || $testo === '') {
            return ['ok' => false, 'testo' => null, 'errore' => 'risposta finale priva di testo utilizzabile'];
        }

        return ['ok' => true, 'testo' => (string) $testo, 'errore' => null];
    }

    /**
     * Applica al payload la riduzione del thinking, se richiesta.
     * Gemini 3.x usa 'thinkingLevel' (non 'thinkingBudget', sintassi
     * valida solo per la serie 2.5). I modelli Flash di Gemini 3 non
     * supportano la disattivazione completa del thinking: 'low' e' il
     * minimo disponibile.
     */
    private function applicaThinkingConfig(array &$payload): void
    {
        if (!$this->abilitaThinking) {
            $payload['generationConfig'] = [
                'thinkingConfig' => ['thinkingLevel' => 'low'],
            ];
        }
    }

    /**
     * Esegue la chiamata HTTP verso generateContent, con la stessa
     * gestione errori (chiave assente, rete, timeout, HTTP non-200,
     * quota 429, JSON non decodificabile) per ogni metodo pubblico.
     *
     * @return array{ok: bool, dati: array|null, errore: string|null}
     */
    private function eseguiRichiesta(array $payload): array
    {
        if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
            return ['ok' => false, 'dati' => null, 'errore' => 'GEMINI_API_KEY non configurata'];
        }

        if (!function_exists('curl_init')) {
            return ['ok' => false, 'dati' => null, 'errore' => 'estensione curl non disponibile'];
        }

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $this->modello,
            GEMINI_API_KEY
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => $this->timeoutSecondi,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $risposta = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlErrore = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErrno !== 0) {
            return ['ok' => false, 'dati' => null, 'errore' => "errore di rete: {$curlErrore}"];
        }

        $decodificato = json_decode((string) $risposta, true);

        if ($httpCode === 429) {
            return ['ok' => false, 'dati' => null, 'errore' => 'quota Gemini esaurita (HTTP 429)'];
        }

        if ($httpCode !== 200) {
            $messaggio = is_array($decodificato) && isset($decodificato['error']['message'])
                ? $decodificato['error']['message']
                : "errore HTTP {$httpCode}";
            return ['ok' => false, 'dati' => null, 'errore' => (string) $messaggio];
        }

        if (!is_array($decodificato)) {
            return ['ok' => false, 'dati' => null, 'errore' => 'risposta non decodificabile come JSON'];
        }

        return ['ok' => true, 'dati' => $decodificato, 'errore' => null];
    }

    private function rispostaFunzioneErrore(string $messaggio): array
    {
        return [
            'tipo' => 'errore',
            'testo' => null,
            'nome' => null,
            'argomenti' => null,
            'model_content' => null,
            'errore' => $messaggio,
        ];
    }
}
