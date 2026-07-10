<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

/**
 * riloc_angolari_api.php — SSE: ricerca luoghi con Venere/Giove vicino
 * alle cuspidi angolari (I, IV, VII, X) nel TEMA NATALE RILOCATO
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * LOGICA:
 *   Il tema natale rilocato mantiene le longitudini zodiacali dei pianeti
 *   identiche al natale originale; cambiano solo le case Placido.
 *   Quindi i pianeti (VE=3, GI=5) sono fissi; varia solo dove cadono
 *   le cuspidi angolari (I=ASC, IV=FC, VII=DSC, X=MC).
 *
 *   Un luogo è VALIDO se:
 *     • Venere è entro ±2.5° da almeno una cuspide angolare (I/IV/VII/X)
 *     E/O
 *     • Giove è entro ±2.5° da almeno una cuspide angolare (I/IV/VII/X)
 *
 *   La distanza è calcolata come diffAngolo(lon_pianeta, lon_cuspide),
 *   simmetrica su entrambi i lati (prima e dopo la cuspide).
 *
 * SSE Events:
 *   start    → { totale, totale_dedup }
 *   progress → { processed, totale, perc, trovati }
 *   result   → singolo luogo matchato
 *   done     → { risultati[], totale_risultati, totale_calcolati, elapsed_ms }
 *   error    → { message }
 *
 * GET params:
 *   g, m, a, ora_gmt   → dati natali (longitudine pianeti)
 *   lat, lon            → coordinate nascita (non usate per il calcolo, solo metadati)
 *   tipo_ricerca        → large_medium | iata_only | tutti  (default: large_medium)
 *   escludi_militari    → 1|0  (default: 1)
 *   nazione             → codice ISO singolo opzionale
 *   tolleranza          → gradi ±  (default: 2.5, max: 10)
 */

// ── SSE headers ──────────────────────────────────────────────────────────
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

set_time_limit(300);
ignore_user_abort(false);

// ── Auth ──────────────────────────────────────────────────────────────────
session_start();
require_once '../includes/Auth.php';

$pdo  = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    _sse('error', ['message' => 'Non autenticato.']);
    exit;
}

// ── Helper SSE ────────────────────────────────────────────────────────────
function _sse(string $event, array $data): void {
    echo "event: {$event}\n";
    echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

/**
 * Differenza angolare con segno — modulo 360°.
 * Restituisce un valore in (-180°, +180°].
 * Negativo = $a è PRIMA di $b (senso diretto zodiacale).
 * Positivo = $a è DOPO $b.
 */
function _diffAngolo(float $a, float $b): float {
    $d = fmod($a - $b + 360.0, 360.0);
    return $d > 180.0 ? $d - 360.0 : $d;
}

try {
    require_once '../includes/SweCalc.php';

    $tStart = microtime(true);
    $swe    = new SweCalc();

    // ── Parametri GET ─────────────────────────────────────────────────────
    $g      = intval($_GET['g']         ?? 1);
    $m      = intval($_GET['m']         ?? 1);
    $a      = intval($_GET['a']         ?? 1990);
    $oraGmt = floatval($_GET['ora_gmt'] ?? 12.0);
    $lat    = floatval($_GET['lat']     ?? 41.9);  // solo metadati
    $lon    = floatval($_GET['lon']     ?? 12.48); // solo metadati

    $tipoRicerca     = $_GET['tipo_ricerca']      ?? 'large_medium';
    $escludiMilitari = ($_GET['escludi_militari'] ?? '1') === '1';
    $filtroNazione   = strtoupper(trim($_GET['nazione'] ?? ''));
    $tolleranza      = max(0.1, min(10.0, floatval($_GET['tolleranza'] ?? 2.5)));

    // ── Step 1: Calcola pianeti natali (longitudini fisse per tutta la ricerca) ──
    // Nel tema rilocato i pianeti non cambiano posizione zodiacale,
    // cambia solo il sistema di case. Quindi calcoliamo le longitudini
    // di Venere (id=3) e Giove (id=5) una sola volta.
    $pianetiNatali = $swe->calcolaPianeti($g, $m, $a, $oraGmt);

    $lonVenere = $pianetiNatali[3]['longitudine'] ?? null;
    $lonGiove  = $pianetiNatali[5]['longitudine'] ?? null;

    if ($lonVenere === null || $lonGiove === null) {
        _sse('error', ['message' => 'Impossibile calcolare le longitudini di Venere e Giove.']);
        exit;
    }

    $venereStr = $pianetiNatali[3]['posizione']['stringa'] ?? '?';
    $gioveStr  = $pianetiNatali[5]['posizione']['stringa'] ?? '?';

    // Case angolari target: I=1, IV=4, VII=7, X=10
    $caseAngolari = [1, 4, 7, 10];
    $nomiCase     = [1 => 'I (ASC)', 4 => 'IV (FC)', 7 => 'VII (DSC)', 10 => 'X (MC)'];

    // ── Step 2: Query aeroporti ───────────────────────────────────────────
    $where  = ['attivo = true'];
    $params = [];

    if ($escludiMilitari) $where[] = 'militare = false';

    switch ($tipoRicerca) {
        case 'large_medium':
            $where[] = "tipo IN ('large_airport','medium_airport')";
            break;
        case 'iata_only':
            $where[] = "iata_code IS NOT NULL AND iata_code <> ''";
            $where[] = "tipo IN ('large_airport','medium_airport','small_airport')";
            break;
        default:
            $where[] = "tipo IN ('large_airport','medium_airport','small_airport')";
    }

    if ($filtroNazione !== '') {
        $where[]  = 'nazione = ?';
        $params[] = $filtroNazione;
    }

    $sql = "SELECT icao_code, iata_code, nome, citta, nazione,
                   latitudine, longitudine
            FROM aeroporti
            WHERE " . implode(' AND ', $where) . "
            ORDER BY nazione, latitudine, longitudine";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $aeroporti  = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totaleAero = count($aeroporti);

    // ── Step 3: Deduplicazione geografica (bucket 0.3°lat × 0.5°lon) ─────
    // Aeroporti nello stesso bucket hanno case Placido praticamente identiche.
    $bucketLat   = 0.3;
    $bucketLon   = 0.5;
    $buckets     = [];
    $selezionati = [];

    foreach ($aeroporti as $aero) {
        $bLat = round(floatval($aero['latitudine'])  / $bucketLat);
        $bLon = round(floatval($aero['longitudine']) / $bucketLon);
        $key  = "{$bLat}:{$bLon}";
        if (!isset($buckets[$key])) {
            $buckets[$key]  = true;
            $selezionati[]  = $aero;
        }
    }

    $totaleCalc = count($selezionati);

    _sse('start', [
        'totale'           => $totaleAero,
        'totale_dedup'     => $totaleCalc,
        'venere'           => $venereStr,
        'giove'            => $gioveStr,
        'tolleranza'       => $tolleranza,
    ]);

    // ── Step 4: Loop principale ───────────────────────────────────────────
    $risultati = [];
    $processed = 0;
    $trovati   = 0;

    foreach ($selezionati as $aero) {
        if ($processed % 200 === 0 && connection_aborted()) break;

        $latA = floatval($aero['latitudine']);
        $lonA = floatval($aero['longitudine']);

        try {
            // Per il tema rilocato calcoliamo solo le case Placido.
            // I pianeti (longitudini) sono quelli natali calcolati sopra.
            // calcolaCasePlacido usa la data natale con le coordinate del luogo target.
            $caseRiloc = $swe->calcolaCasePlacido($g, $m, $a, $oraGmt, $latA, $lonA);

            // Per ogni casa angolare: calcola distanza da Venere e da Giove
            $matchVenere = [];
            $matchGiove  = [];

            foreach ($caseAngolari as $numCasa) {
                if (!isset($caseRiloc[$numCasa])) continue;
                $lonCuspide = $caseRiloc[$numCasa]['longitudine'];

                // Distanza Venere — simmetrica ±tolleranza
                $distV = abs(_diffAngolo($lonVenere, $lonCuspide));
                if ($distV <= $tolleranza) {
                    $matchVenere[] = [
                        'casa'     => $numCasa,
                        'nome'     => $nomiCase[$numCasa],
                        'distanza' => round($distV, 3),
                        'cuspide'  => $caseRiloc[$numCasa]['posizione']['stringa'] ?? '?',
                    ];
                }

                // Distanza Giove — simmetrica ±tolleranza
                $distG = abs(_diffAngolo($lonGiove, $lonCuspide));
                if ($distG <= $tolleranza) {
                    $matchGiove[] = [
                        'casa'     => $numCasa,
                        'nome'     => $nomiCase[$numCasa],
                        'distanza' => round($distG, 3),
                        'cuspide'  => $caseRiloc[$numCasa]['posizione']['stringa'] ?? '?',
                    ];
                }
            }

            // Il luogo è valido se almeno uno dei due pianeti ha un match
            if (!empty($matchVenere) || !empty($matchGiove)) {
                $trovati++;

                // Distanza minima complessiva (per ordinamento)
                $distMinV = empty($matchVenere) ? 999.0
                    : min(array_column($matchVenere, 'distanza'));
                $distMinG = empty($matchGiove) ? 999.0
                    : min(array_column($matchGiove, 'distanza'));
                $distMin  = min($distMinV, $distMinG);

                $ris = [
                    'icao'          => $aero['icao_code'],
                    'iata'          => $aero['iata_code'],
                    'nome'          => $aero['nome'],
                    'citta'         => $aero['citta'],
                    'nazione'       => $aero['nazione'],
                    'lat'           => $latA,
                    'lon'           => $lonA,
                    'match_venere'  => $matchVenere,
                    'match_giove'   => $matchGiove,
                    'dist_min'      => round($distMin, 3),
                ];

                $risultati[] = $ris;
                _sse('result', $ris);
            }

        } catch (Exception $e) {
            // Latitudine polare o calcolo impossibile → salta silenziosamente
        }

        $processed++;

        if ($processed % 100 === 0) {
            _sse('progress', [
                'processed' => $processed,
                'totale'    => $totaleCalc,
                'perc'      => round($processed / $totaleCalc * 100),
                'trovati'   => $trovati,
            ]);
        }
    }

    // ── Step 5: Ordina per distanza minima e invia done ───────────────────
    usort($risultati, fn($a, $b) => $a['dist_min'] <=> $b['dist_min']);

    _sse('done', [
        'risultati'          => $risultati,
        'totale_risultati'   => count($risultati),
        'totale_calcolati'   => $totaleCalc,
        'totale_originale'   => $totaleAero,
        'elapsed_ms'         => round((microtime(true) - $tStart) * 1000),
        'venere'             => $venereStr,
        'giove'              => $gioveStr,
        'tolleranza'         => $tolleranza,
    ]);

} catch (Throwable $e) {
    _sse('error', ['message' => $e->getMessage()]);
}
