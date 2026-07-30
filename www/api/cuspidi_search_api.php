<?php
require_once __DIR__ . '/../includes/bootstrap.php';
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$pdoAuth = db_connect();
$auth = new Auth($pdoAuth);

if (!$auth->isLoggedIn()) {
    header('Content-Type: text/event-stream');
    echo "event: error\n";
    echo "data: " . json_encode(['message' => 'Non autenticato.'], JSON_UNESCAPED_UNICODE) . "\n\n";
    exit;
}

require_once __DIR__ . '/../includes/CuspidiUtils.php';
/**
 * cuspidi_search_api.php — Endpoint SSE per ricerca per longitudine/cuspide
 *
 * Logica corretta:
 *  1. Calcola il momento RS una volta sola (uguale per tutti i luoghi)
 *  2. Per ogni aeroporto: calcolaCasePlacido(rs.g, rs.m, rs.a, rs.ora_gmt, lat, lon)
 *  3. Estrae la cuspide della casa scelta nella RS e controlla il match
 *
 * Ottimizzazioni da ricerca_stream.php:
 *  - Pianeti RS calcolati UNA SOLA VOLTA
 *  - Deduplicazione geografica bucket 0.3°lat × 0.5°lon
 *
 * SSE Events:
 *   start    → { totale, rs_gmt, casa, target_str }
 *   progress → { processed, totale, perc, trovati, fase }
 *   result   → singolo aeroporto matchato (streaming live)
 *   done     → { risultati[], totale_risultati, totale_calcolati, elapsed_ms, rs_gmt }
 *   error    → { message }
 *
 * GET params:
 *   g, m, a, ora_gmt, lat, lon  → dati natali soggetto
 *   anno                         → anno RS (default: anno corrente)
 *   casa                         → 1-12
 *   segno                        → 1-12 (0 = qualsiasi)
 *   gradi                        → 0-29
 *   tol_gradi                    → tolleranza gradi (default 0)
 *   minuti                       → 0-59
 *   tol_minuti                   → tolleranza minuti (default 0)
 *   tipo_ricerca                 → large_medium | iata_only | nazione | tutti
 *   nazione                      → codice ISO (solo se tipo=nazione)
 *   escludi_militari             → 1|0
 */

// ── SSE headers ──────────────────────────────────────────────────────────
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

set_time_limit(600);
ignore_user_abort(false);

function sse(string $event, array $data): void {
    echo "event: {$event}\n";
    echo "data: " . json_encode($data) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}


if (isset($_GET['espansione_orbe']) && $_GET['espansione_orbe'] === '1' && !$auth->hasFeature('dynamic_orb')) {
    sse('error', [
        'message' => 'Questa funzione è riservata agli utenti del piano Supporter.',
    ]);
    exit;
}

// ── Segni zodiacali ───────────────────────────────────────────────────────
const SEGNI = [
    1=>'Ariete',   2=>'Toro',      3=>'Gemelli',   4=>'Cancro',
    5=>'Leone',    6=>'Vergine',   7=>'Bilancia',  8=>'Scorpione',
    9=>'Sagittario',10=>'Capricorno',11=>'Acquario',12=>'Pesci',
];
const SEGNI_SIMBOLI = [
    1=>'♈',2=>'♉',3=>'♊',4=>'♋',5=>'♌',6=>'♍',
    7=>'♎',8=>'♏',9=>'♐',10=>'♑',11=>'♒',12=>'♓',
];


/**
 * Distanza in gradi decimali dal target (per ordinamento risultati)
 */

try {
    require_once '../includes/SweCalc.php';
    require_once '../includes/FiltroEsclusione.php';

    $tStart = microtime(true);
    $swe    = new SweCalc();

    $pdo = db_connect();

    // ── Parametri natali ──────────────────────────────────────────────────
    $g      = intval($_GET['g']        ?? 1);
    $m      = intval($_GET['m']        ?? 1);
    $a      = intval($_GET['a']        ?? 1990);
    $oraGmt = floatval($_GET['ora_gmt'] ?? 12.0);
    $lat    = floatval($_GET['lat']    ?? 41.9);
    $lon    = floatval($_GET['lon']    ?? 12.48);
    $anno   = intval($_GET['anno']     ?? date('Y'));

    // ── Parametri ricerca cuspide ─────────────────────────────────────────
    $casa        = max(1, min(12, intval($_GET['casa']      ?? 1)));
    $segnoTarget = intval($_GET['segno']     ?? 0);   // 0 = qualsiasi
    $gradiTarget = max(0, min(29, intval($_GET['gradi']     ?? 0)));
    $tolGradi    = max(0, min(30, intval($_GET['tol_gradi'] ?? 0)));
    $minutiTarget= max(0, min(59, intval($_GET['minuti']    ?? 0)));
    $tolMinuti   = max(0, min(59, intval($_GET['tol_minuti']?? 0)));

    // ── Parametri aeroporti ───────────────────────────────────────────────
    $tipoRicerca     = $_GET['tipo_ricerca']      ?? 'large_medium';
    $nazioneFiltro   = strtoupper(trim($_GET['nazione'] ?? ''));
    $escludiMilitari = ($_GET['escludi_militari'] ?? '1') === '1';
    $mostraEscluse = ($_GET['mostra_escluse'] ?? '0') === '1';

    // Bucket dedup identico a ricerca_stream.php
    $bucketLat = 0.3;
    $bucketLon = 0.5;

    // ── Step 1: Calcola momento RS (UNA VOLTA per tutti gli aeroporti) ────
    $rs        = $swe->calcolaRS($g, $m, $a, $oraGmt, $anno);
    $oraGmtRS  = $rs['ora_gmt'];
    $giornoRS  = $rs['giorno'];
    $meseRS    = $rs['mese'];
    $annoRSeff = $rs['anno'];

    // Pianeti RS calcolati una sola volta (identici per tutti gli aeroporti)
    // e tema natale — necessari per il filtro di esclusione RS
    $pianetiRS  = $swe->calcolaPianeti($giornoRS, $meseRS, $annoRSeff, $oraGmtRS);
    $temaNatale = $swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);

    // ── Stringa target ────────────────────────────────────────────────────
    $segnoNome = $segnoTarget > 0 ? (SEGNI[$segnoTarget] ?? '?') : 'qualsiasi segno';
    $targetStr = sprintf(
        'Casa %d: %s %d°%02d′ ±%d°%02d′',
        $casa, $segnoNome, $gradiTarget, $minutiTarget, $tolGradi, $tolMinuti
    );

    // ── Step 2: Query aeroporti ───────────────────────────────────────────
    $where = ['attivo = true'];
    if ($escludiMilitari) $where[] = 'militare = false';

    switch ($tipoRicerca) {
        case 'large_medium':
            $where[] = "tipo IN ('large_airport','medium_airport')";
            break;
        case 'iata_only':
            $where[] = "iata_code IS NOT NULL AND iata_code <> ''";
            $where[] = "tipo IN ('large_airport','medium_airport','small_airport')";
            break;
        case 'nazione':
            if ($nazioneFiltro !== '') {
                $where[] = 'nazione = ' . $pdo->quote($nazioneFiltro);
            }
            $where[] = "tipo IN ('large_airport','medium_airport','small_airport')";
            break;
        default: // tutti
            $where[] = "tipo IN ('large_airport','medium_airport','small_airport')";
    }

    $sql = "SELECT icao_code, iata_code, nome, citta, nazione,
                   latitudine, longitudine
            FROM aeroporti
            WHERE " . implode(' AND ', $where) . "
            ORDER BY nazione, latitudine, longitudine";

    $aeroporti  = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $totaleAero = count($aeroporti);

    sse('start', [
        'totale'     => $totaleAero,
        'rs_gmt'     => $rs['stringa'],
        'casa'       => $casa,
        'target_str' => $targetStr,
    ]);

    // ── Step 3: Deduplicazione geografica (stesso bucket di ricerca_stream) ─
    $buckets     = [];
    $selezionati = [];
    foreach ($aeroporti as $aero) {
        $bLat = round(floatval($aero['latitudine'])  / $bucketLat);
        $bLon = round(floatval($aero['longitudine']) / $bucketLon);
        $key  = "{$bLat}:{$bLon}";
        if (!isset($buckets[$key])) {
            $buckets[$key] = true;
            $selezionati[] = $aero;
        }
    }
    $totaleCalc = count($selezionati);

    sse('progress', [
        'processed'        => 0,
        'totale'           => $totaleCalc,
        'totale_originale' => $totaleAero,
        'perc'             => 0,
        'trovati'          => 0,
        'fase'             => 'dedup_done',
    ]);

    // ── Intervalli match ──────────────────────────────────────────────────
    $gradiMin  = max(0,  $gradiTarget  - $tolGradi);
    $gradiMax  = min(29, $gradiTarget  + $tolGradi);
    $minutiMin = max(0,  $minutiTarget - $tolMinuti);
    $minutiMax = min(59, $minutiTarget + $tolMinuti);

    // ── Step 4: Loop principale ───────────────────────────────────────────
    $risultati = [];
    $processed = 0;
    $trovati   = 0;
    $totaleEsclusiFiltro = 0;

    foreach ($selezionati as $aero) {
        if ($processed % 200 === 0 && connection_aborted()) break;

        $latA = floatval($aero['latitudine']);
        $lonA = floatval($aero['longitudine']);

        try {
            // Calcola solo le case della RS per questo aeroporto
            // (i pianeti RS sono identici per tutti — non li ricalcoliamo)
            $caseRS = $swe->calcolaCasePlacido(
                $giornoRS, $meseRS, $annoRSeff,
                $oraGmtRS, $latA, $lonA
            );

            // ── Filtro di esclusione RS (aggiuntivo, non RuleEngine) ───────
            // Sole/Marte in I/VI/XII RS, ASC RS in I/VI/XII natale,
            // Saturno in X RS, stellium in qualsiasi casa RS.
            $pianetiConCase = $pianetiRS;
            foreach ($pianetiConCase as $idP => $p) {
                $pianetiConCase[$idP]['casa'] = $swe->trovaCasaPublic($p['longitudine'], $caseRS);
            }
            $motiviEsclusione = verificaEsclusioneRS($pianetiConCase, $caseRS, $temaNatale);
            if (!empty($motiviEsclusione) && !$mostraEscluse) {
                $totaleEsclusiFiltro++;
                $processed++;
                continue;
            }

            // Cuspide della casa richiesta nella RS
            if (!isset($caseRS[$casa])) {
                $processed++;
                continue;
            }

            $lonCuspide = $caseRS[$casa]['longitudine'];
            $pos        = lon2sgm($lonCuspide);

            // ── Match ──────────────────────────────────────────────────────
            $matchSegno = ($segnoTarget === 0) || ($pos['segno'] === $segnoTarget);
            $matchGradi = ($pos['gradi']  >= $gradiMin)  && ($pos['gradi']  <= $gradiMax);
            $matchMin   = ($pos['minuti'] >= $minutiMin) && ($pos['minuti'] <= $minutiMax);

            if ($matchSegno && $matchGradi && $matchMin) {
                $trovati++;
                $dist    = distanzaDalTarget($pos, $gradiTarget, $minutiTarget);
                $segNome = SEGNI[$pos['segno']]         ?? '?';
                $segSim  = SEGNI_SIMBOLI[$pos['segno']] ?? '';

             $ris = [
                    'icao'        => $aero['icao_code'],
                    'iata'        => $aero['iata_code'],
                    'nome'        => $aero['nome'],
                    'citta'       => $aero['citta'],
                    'nazione'     => $aero['nazione'],
                    'lat'         => $latA,
                    'lon'         => $lonA,
                    'segno_num'   => $pos['segno'],
                    'segno_nome'  => $segNome,
                    'segno_sim'   => $segSim,
                    'gradi'       => $pos['gradi'],
                    'minuti'      => $pos['minuti'],
                    'secondi'     => $pos['secondi'],
                    'lon_cuspide' => round($lonCuspide, 4),
                    'cuspide_str' => sprintf('%s %d°%02d′%02d″',
                                     $segNome, $pos['gradi'], $pos['minuti'], $pos['secondi']),
                    'distanza'    => round($dist, 4),
                    'esclusa_filtro'    => !empty($motiviEsclusione),
                    'motivi_esclusione' => $motiviEsclusione,
                    ];

                $risultati[] = $ris;
                sse('result', $ris);
            }

        } catch (Exception $e) {
            // latitudine polare o calcolo impossibile — salta
        }

        $processed++;

        if ($processed % 100 === 0) {
            sse('progress', [
                'processed' => $processed,
                'totale'    => $totaleCalc,
                'perc'      => round($processed / $totaleCalc * 100),
                'trovati'   => $trovati,
                'fase'      => 'calcolo',
            ]);
        }
    }

    // ── Step 5: Ordina per distanza e invia done ──────────────────────────
    usort($risultati, fn($a, $b) => $a['distanza'] <=> $b['distanza']);

           sse('done', [
               'risultati'        => $risultati,
               'totale_risultati' => count($risultati),
               'totale_calcolati' => $totaleCalc,
               'totale_originale' => $totaleAero,
               'totale_esclusi_filtro' => $totaleEsclusiFiltro,
               'mostra_escluse'   => $mostraEscluse,
               'elapsed_ms'       => round((microtime(true) - $tStart) * 1000),
               'rs_gmt'           => $rs['stringa'],
               'casa'             => $casa,
               'target_str'       => $targetStr,
            ]);

} catch (Throwable $e) {
    sse('error', ['message' => $e->getMessage()]);
}
