<?php
require_once __DIR__ . '/../SoggettoRepository.php';
require_once __DIR__ . '/../NascitaGmtHelper.php';

/**
 * Tool AI per la ricerca RSM (Rivoluzione Solare Mondiale) per condizione.
 *
 * Non contiene logica astrologica propria: chiama internamente l'endpoint
 * gia' esistente e testato api/ricerca_stream_api.php (via HTTP interno,
 * stessa sessione), ne consuma lo stream SSE e restituisce solo l'evento
 * finale 'done'. Zero duplicazione della logica di ricerca (Sezione 7).
 *
 * Non modifica mai dati ASTROLAB: e' una lettura, non una scrittura.
 */
class RicercaRsmTool
{
    private const CONDIZIONI_VALIDE = [
        'Decima', 'Lavoro', 'Amore', 'Salute', 'Denaro', 'Denaro Low', 'Casa',
    ];

    private PDO $pdo;
    private int $timeoutSecondi;

    public function __construct(PDO $pdo, int $timeoutSecondi = 90)
    {
        $this->pdo = $pdo;
        $this->timeoutSecondi = $timeoutSecondi;
    }

    /**
     * @param int    $soggettoId ID del soggetto (di norma il soggetto attivo di sessione).
     * @param int    $anno Anno della RSM da cercare.
     * @param string $condizione Una delle 7 condizioni standard.
     * @param int    $maxRisultati Quanti risultati migliori restituire (default 5).
     * @return array{ok: bool, errore: string|null, condizione: string|null,
     *               anno: int|null, totale_risultati: int|null,
     *               risultati_top: array|null, messaggio_speciale: string|null}
     */
    public function cerca(int $soggettoId, int $anno, string $condizione, int $maxRisultati = 5): array
    {
        if (!in_array($condizione, self::CONDIZIONI_VALIDE, true)) {
            return $this->errore(
                "Condizione '{$condizione}' non valida. Condizioni disponibili: " . implode(', ', self::CONDIZIONI_VALIDE) . '.'
            );
        }

        $soggetto = caricaSoggettoById($this->pdo, $soggettoId);
        if ($soggetto === null) {
            return $this->errore('Soggetto non trovato.');
        }

        try {
            $gmtData = calcolaDataOraGmtCorretta(
                $soggetto['data_nascita'],
                $soggetto['ora_nascita'],
                (float) $soggetto['offset_gmt']
            );
        } catch (Throwable $e) {
            return $this->errore('Errore nella conversione data/ora GMT: ' . $e->getMessage());
        }

        $dateGmt = new DateTime($gmtData['data_gmt'] . ' ' . $gmtData['ora_gmt']);
        $oraGmtParts = explode(':', $gmtData['ora_gmt']);
        $oraGmtDec = (int) $oraGmtParts[0] + ((int) ($oraGmtParts[1] ?? 0)) / 60.0;

        $params = http_build_query([
            'g'          => (int) $dateGmt->format('d'),
            'm'          => (int) $dateGmt->format('m'),
            'a'          => (int) $dateGmt->format('Y'),
            'ora_gmt'    => $oraGmtDec,
            'lat'        => (float) $soggetto['latitudine'],
            'lon'        => (float) $soggetto['longitudine'],
            'anno'       => $anno,
            'condizione' => $condizione,
        ]);

        $sessionId = session_id();
        if ($sessionId === false || $sessionId === '') {
            return $this->errore('Sessione non disponibile per la chiamata interna.');
        }

        // Rilascia il lock del file di sessione prima della chiamata interna:
        // ricerca_stream_api.php chiama a sua volta session_start() sulla
        // stessa sessione, e senza questo rilascio le due richieste vanno
        // in deadlock (la interna resta bloccata in attesa del lock finche'
        // la esterna, che sta aspettando la interna, non lo rilascia).
        session_write_close();

        $ch = curl_init("http://localhost/api/ricerca_stream_api.php?{$params}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Cookie: ' . session_name() . '=' . $sessionId],
            CURLOPT_TIMEOUT => $this->timeoutSecondi,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $risposta = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlErrore = curl_error($ch);
        curl_close($ch);

        if ($curlErrno !== 0) {
            return $this->errore("Errore di rete verso il motore di ricerca: {$curlErrore}");
        }

        $eventoDone = $this->estraiEventoSse((string) $risposta, 'done');
        if ($eventoDone === null) {
            $eventoErrore = $this->estraiEventoSse((string) $risposta, 'error');
            if ($eventoErrore !== null && isset($eventoErrore['message'])) {
                return $this->errore('Il motore di ricerca ha restituito un errore: ' . $eventoErrore['message']);
            }
            return $this->errore('Risposta del motore di ricerca non interpretabile (nessun evento done).');
        }

        $risultati = $eventoDone['risultati'] ?? [];

        return [
            'ok'                 => true,
            'errore'             => null,
            'condizione'         => $condizione,
            'anno'               => $anno,
            'totale_risultati'   => $eventoDone['totale_risultati'] ?? count($risultati),
            'risultati_top'      => array_slice($risultati, 0, $maxRisultati),
            'messaggio_speciale' => $eventoDone['messaggio_speciale'] ?? null,
        ];
    }

    /**
     * Estrae il payload JSON dell'ultimo evento SSE del tipo indicato.
     */
    private function estraiEventoSse(string $streamGrezzo, string $tipoEvento): ?array
    {
        $blocchi = preg_split('/\n\n+/', trim($streamGrezzo));
        $trovato = null;

        foreach ($blocchi as $blocco) {
            if (strpos($blocco, "event: {$tipoEvento}") === false) {
                continue;
            }

            foreach (explode("\n", $blocco) as $riga) {
                if (str_starts_with($riga, 'data: ')) {
                    $decodificato = json_decode(substr($riga, 6), true);
                    if (is_array($decodificato)) {
                        $trovato = $decodificato;
                    }
                }
            }
        }

        return $trovato;
    }

    private function errore(string $messaggio): array
    {
        return [
            'ok' => false,
            'errore' => $messaggio,
            'condizione' => null,
            'anno' => null,
            'totale_risultati' => null,
            'risultati_top' => null,
            'messaggio_speciale' => null,
        ];
    }
}
