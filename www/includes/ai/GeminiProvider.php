<?php
require_once __DIR__ . '/AiProviderInterface.php';

/**
 * Implementazione del provider AI Agent per Google Gemini.
 *
 * Nessuna eccezione viene mai lasciata propagare: qualunque errore
 * (chiave assente, rete, timeout, HTTP non-200, quota esaurita, risposta
 * malformata) viene intercettato e restituito nel campo 'errore'.
 *
 * Non effettua tool calling (fuori scope Fase 1). Non modifica mai dati
 * ASTROLAB: si limita a inviare un prompt testuale e restituire il testo
 * di risposta del modello.
 */
class GeminiProvider implements AiProviderInterface
{
    private string $modello;
    private bool $abilitaThinking;
    private int $timeoutSecondi;

    /**
     * @param bool   $abilitaThinking Se false (default), disattiva il
     *   "thinking" del modello per non consumare inutilmente la quota
     *   free-tier su richieste semplici. Da valutare true quando servirà
     *   ragionamento per il tool calling (Fase 2+).
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
        if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
            return ['ok' => false, 'testo' => null, 'errore' => 'GEMINI_API_KEY non configurata'];
        }

        if (!function_exists('curl_init')) {
            return ['ok' => false, 'testo' => null, 'errore' => 'estensione curl non disponibile'];
        }

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $this->modello,
            GEMINI_API_KEY
        );

        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
        ];

        if (!$this->abilitaThinking) {
            // Gemini 3.x usa 'thinkingLevel' (non 'thinkingBudget', che e'
            // la sintassi della serie 2.5 e causa un errore "invalid
            // argument" su questo modello). Nota: i modelli Flash di
            // Gemini 3 non supportano la disattivazione completa del
            // thinking - 'low' e' il minimo disponibile.
            $payload['generationConfig'] = [
                'thinkingConfig' => ['thinkingLevel' => 'low'],
            ];
        }

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
            return ['ok' => false, 'testo' => null, 'errore' => "errore di rete: {$curlErrore}"];
        }

        $decodificato = json_decode((string) $risposta, true);

        if ($httpCode === 429) {
            return ['ok' => false, 'testo' => null, 'errore' => 'quota Gemini esaurita (HTTP 429)'];
        }

        if ($httpCode !== 200) {
            $messaggio = is_array($decodificato) && isset($decodificato['error']['message'])
                ? $decodificato['error']['message']
                : "errore HTTP {$httpCode}";
            return ['ok' => false, 'testo' => null, 'errore' => (string) $messaggio];
        }

        if (!is_array($decodificato)) {
            return ['ok' => false, 'testo' => null, 'errore' => 'risposta non decodificabile come JSON'];
        }

        $testo = $decodificato['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($testo === null || $testo === '') {
            return ['ok' => false, 'testo' => null, 'errore' => 'risposta priva di testo utilizzabile'];
        }

        return ['ok' => true, 'testo' => (string) $testo, 'errore' => null];
    }
}
