<?php
declare(strict_types=1);
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

if (!$auth->hasFeature('grid_search')) {
    header('Content-Type: text/event-stream');
    echo "event: error\n";
    echo "data: " . json_encode([
        'message' => 'Questa funzione è riservata agli utenti del piano Supporter.',
    ], JSON_UNESCAPED_UNICODE) . "\n\n";
    exit;
}

require_once __DIR__ . '/../includes/RicercaRSFilters.php';
require_once __DIR__ . '/../includes/CuspidiUtils.php';

/**
 * api/ricerca_griglia_api.php — Ricerca a Griglia Geometrica mondiale
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * Scansiona posizioni astrologiche su una griglia fissa di coordinate
 * geografiche (2° / 1° / 0.5°) invece di limitarsi al database aeroporti.
 * Utile quando la configurazione desiderata cade in zone remote prive
 * di aeroporti.
 *
 * v1.2 — TRE MODALITÀ (parità con ricerca_stream_api.php / cuspidi_search_api.php)
 * ═══════════════════════════════════════════════════════════════════════════
 *   modalita=standard (default) → 7 condizioni tematiche, Rule Map radicale
 *     + filtri specifici Amore/Casa/Salute/Denaro/Denaro Low, RuleEngine.
 *   modalita=astri  → filtro "Astri nelle Case" personalizzato (pianeta/
 *     casa/vuole), stelline calcolate su condizione 'Decima' solo per il
 *     ranking. Rule Map radicale disabilitata (stesso motivo di
 *     ricerca_stream_api.php: eviterebbe RS valide per i criteri utente).
 *   modalita=cuspidi → ricerca per longitudine cuspide (casa/segno/gradi/
 *     tolleranza), NESSUNA valutazione RuleEngine, risultati ordinati per
 *     distanza dal target (stessa logica di cuspidi_search_api.php).
 *
 * Le funzioni di filtro sono duplicate LOCALMENTE da ricerca_stream_api.php
 * e cuspidi_search_api.php (stesso pattern già in uso nel progetto per
 * diffAngolo(), presente separatamente in RuleEngine.php, SweCalc.php,
 * ricerca_stream_api.php, riloc_angolari_api.php). Nessuno dei due file
 * sorgente viene toccato da questo endpoint — restano le fonti autorevoli
 * per la ricerca aeroporti. Modifiche future alle regole tematiche vanno
 * replicate manualmente qui (refactor in modulo condiviso non ancora fatto).
 *
 * NON applicato (specifico aeroporti, non pertinente a coordinate pure):
 * deduplicazione geografica a bucket, filtri nazione/tipo/militari. Il
 * filtro "Macro-Area" non ha effetto qui (nessun dato nazione associato a
 * una coordinata); resta attivo solo il bbox lon_min/lon_max.
 *
 * SICUREZZA / PERFORMANCE:
 *   Limite hard di 500.000 punti per singola ricerca (MAX_PUNTI_GRIGLIA).
 *   Latitudine limitata a [-60, +60] per default (regola 31: oltre 60° è
 *   comunque veto assoluto).
 *
 * SSE Events:
 *   start    → { totale, step, rs_gmt, modalita, condizione?, target_str?, bbox }
 *   progress → { processed, totale, perc, trovati, esclusi_radicale, esclusi_filtro, esclusi_condizione }
 *   result   → singolo punto griglia (forma variabile in base a modalita)
 *   done     → { risultati[], totale_risultati, totale_calcolati, totale_esclusi_*, elapsed_ms, rs_gmt }
 *   error    → { message }
 *
 * GET params comuni:
 *   g, m, a, ora_gmt, lat, lon   → dati natali soggetto
 *   anno                          → anno RS (default: anno corrente)
 *   griglia                       → '2deg' | '1deg' | '0.5deg' (default '2deg')
 *   lat_min, lat_max              → bbox latitudine (default -60, 60)
 *   lon_min, lon_max              → bbox longitudine (default -180, 180)
 *   mostra_escluse                → 1|0 (default 0)
 *   modalita                      → 'standard' | 'astri' | 'cuspidi' (default 'standard')
 *
 * GET params modalita=standard:
 *   condizione                    → Decima|Lavoro|Amore|Salute|Denaro|Denaro Low|Casa
 *   stelline_min                  → 0-5 (default 0)
 *   streaming_min                 → 0-5 (default 3)
 *
 * GET params modalita=astri:
 *   astri_in_casa                 → JSON [{pianeta,casa,vuole},...]
 *   stelline_min, streaming_min   → come sopra
 *
 * GET params modalita=cuspidi:
 *   casa                          → 1-12
 *   segno                         → 0-12 (0 = qualsiasi)
 *   gradi                         → 0-29
 *   tol_gradi                     → 0-30
 *   minuti                        → 0-59
 *   tol_minuti                    → 0-59
 */

// ── SSE headers ──────────────────────────────────────────────────────────
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

set_time_limit(0);
ignore_user_abort(false);

function sseG(string $event, array $data): void {
    echo "event: {$event}\n";
    echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

const GRID_STEP_MAP = [
    '2deg'   => 2.0,
    '1deg'   => 1.0,
    '0.5deg' => 0.5,
];

const MAX_PUNTI_GRIGLIA = 500000;

const GG_SEGNI = [
    1=>'Ariete',2=>'Toro',3=>'Gemelli',4=>'Cancro',
    5=>'Leone',6=>'Vergine',7=>'Bilancia',8=>'Scorpione',
    9=>'Sagittario',10=>'Capricorno',11=>'Acquario',12=>'Pesci',
];

/**
 * Case tematiche e benefici rilevanti per il calcolo della "vicinanza alla
 * cuspide" (ranking secondario). Rispecchia le case bonus di
 * RuleEngine::CASE_TEMATICHE e i benefici già usati nelle funzioni
 * ggVerificaAmore/Casa/Salute/Denaro qui sopra. 'Denaro Low' è
 * intenzionalmente assente: è una condizione puramente difensiva che non
 * richiede la presenza di un benefico, quindi non ha senso classificarla
 * per vicinanza.
 */
const GG_CASE_TARGET_BENEFICI = [
    'Decima' => ['case' => [10, 5],    'benefici' => [0, 5, 3]],
    'Lavoro' => ['case' => [6, 10],    'benefici' => [0, 5, 3]],
    'Amore'  => ['case' => [5, 7],     'benefici' => [3, 5, 0]],
    'Salute' => ['case' => [1, 6, 12], 'benefici' => [5, 3]],
    'Denaro' => ['case' => [2, 8],     'benefici' => [0, 5, 3]],
    'Casa'   => ['case' => [4],        'benefici' => [0, 5, 3]],
];

/**
 * Trova il benefico più vicino alla cuspide della sua casa tematica, tra
 * quelli effettivamente presenti nelle case bersaglio della condizione.
 * Usato come criterio di ordinamento SECONDARIO (dopo le stelline): a
 * parità di stelle, un Giove/Venere a 0.5° dalla cuspide di X è "più
 * potente" — ed è quindi preferibile mostrarlo prima — di uno a 28°.
 *
 * @return array{pianeta:int, casa:int, distanza:float}|null null se la
 *         condizione non ha case tematiche con benefici (es. Denaro Low)
 *         oppure se nessun benefico è presente nelle case bersaglio.
 */
function ggCalcolaVicinanzaBenefici(array $pianetiConCase, array $caseRS, string $condizione): ?array {
    $target = GG_CASE_TARGET_BENEFICI[$condizione] ?? null;
    if ($target === null) return null;

    $migliore = null;

    foreach ($target['case'] as $casaTarget) {
        if (!isset($caseRS[$casaTarget])) continue;
        $cuspide = $caseRS[$casaTarget]['longitudine'];

        foreach ($target['benefici'] as $idBenef) {
            if (!isset($pianetiConCase[$idBenef])) continue;
            if ((int)$pianetiConCase[$idBenef]['casa'] !== $casaTarget) continue;

            $lonBenef = (float)$pianetiConCase[$idBenef]['longitudine'];
            $dist     = abs(ggDiffAngolo($lonBenef, $cuspide));

            if ($migliore === null || $dist < $migliore['distanza']) {
                $migliore = ['pianeta' => $idBenef, 'casa' => $casaTarget, 'distanza' => $dist];
            }
        }
    }

    return $migliore;
}



// ═══════════════════════════════════════════════════════════════════════════
//  FUNZIONI DUPLICATE DA ricerca_stream_api.php — mantenere sincronizzate
//  manualmente. Vedi nota in testa al file.
// ═══════════════════════════════════════════════════════════════════════════

function ggDiffAngolo(float $a, float $b): float {
    $d = fmod($a - $b + 360, 360);
    return $d > 180 ? $d - 360 : $d;
}

function ggNomePianeta(int $id): string {
    static $NOMI = [0=>'SO',1=>'LU',2=>'ME',3=>'VE',4=>'MA',5=>'GI',6=>'SA',7=>'UR',8=>'NE',9=>'PLU',11=>'NO'];
    return $NOMI[$id] ?? 'P' . $id;
}

// ═══════════════════════════════════════════════════════════════════════════
//  CORPO PRINCIPALE
// ═══════════════════════════════════════════════════════════════════════════

try {
    require_once '../includes/SweCalc.php';
    require_once '../includes/RuleEngine.php';
    require_once '../includes/FiltroEsclusione.php';

    $tStart = microtime(true);
    $swe    = new SweCalc();
    $engine = new RuleEngine();

    // ── Parametri natali ────────────────────────────────────────────────
    $g      = intval($_GET['g']         ?? 1);
    $m      = intval($_GET['m']         ?? 1);
    $a      = intval($_GET['a']         ?? 1990);
    $oraGmt = floatval($_GET['ora_gmt'] ?? 12.0);
    $lat    = floatval($_GET['lat']     ?? 41.9);
    $lon    = floatval($_GET['lon']     ?? 12.48);
    $anno   = intval($_GET['anno']      ?? (int)date('Y'));

    // ── Modalità ────────────────────────────────────────────────────────
    $modalita = $_GET['modalita'] ?? 'standard';
    if (!in_array($modalita, ['standard', 'astri', 'cuspidi'], true)) $modalita = 'standard';

    // ── Griglia / bbox ──────────────────────────────────────────────────
    $stepKey = $_GET['griglia'] ?? '2deg';
    if (!isset(GRID_STEP_MAP[$stepKey])) $stepKey = '2deg';
    $step = GRID_STEP_MAP[$stepKey];

    $latMin = max(-60.0, min(60.0, floatval($_GET['lat_min'] ?? -60.0)));
    $latMax = max(-60.0, min(60.0, floatval($_GET['lat_max'] ?? 60.0)));
    if ($latMin > $latMax) { $tmp = $latMin; $latMin = $latMax; $latMax = $tmp; }

    $lonMin = max(-180.0, min(180.0, floatval($_GET['lon_min'] ?? -180.0)));
    $lonMax = max(-180.0, min(180.0, floatval($_GET['lon_max'] ?? 180.0)));
    if ($lonMin > $lonMax) { $tmp = $lonMin; $lonMin = $lonMax; $lonMax = $tmp; }

    $mostraEscluse = ($_GET['mostra_escluse'] ?? '0') === '1';
    $debugMode     = ($_GET['debug'] ?? '0') === '1';

    // ── Parametri specifici per modalità ───────────────────────────────
    $condizioniValide = ['Decima','Lavoro','Amore','Salute','Denaro','Denaro Low','Casa'];

    $condizioneInput = $_GET['condizione'] ?? 'Decima';
    if (!in_array($condizioneInput, $condizioniValide, true)) $condizioneInput = 'Decima';

    $stellineMin  = max(0, min(5, intval($_GET['stelline_min']  ?? 0)));
    $streamingMin = max(0, min(5, intval($_GET['streaming_min'] ?? 3)));

    $astriInCasa = [];
    if ($modalita === 'astri') {
        if (!empty($_GET['astri_in_casa'])) {
            $raw = json_decode($_GET['astri_in_casa'], true);
            if (is_array($raw)) {
                foreach ($raw as $f) {
                    if (!isset($f['pianeta'], $f['casa'], $f['vuole'])) continue;
                    $casaF = intval($f['casa']);
                    if ($casaF < 1 || $casaF > 12) continue;
                    $pid = $f['pianeta'];
                    if ($pid !== 'ASC' && $pid !== -1) {
                        $pid = intval($pid);
                        if (!in_array($pid, [0,1,2,3,4,5,6,7,8,9,11], true)) continue;
                    }
                    $astriInCasa[] = ['pianeta' => $pid, 'casa' => $casaF, 'vuole' => (bool)$f['vuole']];
                }
            }
        }
        if (empty($astriInCasa)) {
            sseG('error', ['message' => 'Nessuna regola "Astri nelle Case" impostata.']);
            exit;
        }
    }

    $cuspCasa = 1; $cuspSegno = 0; $cuspGradiTarget = 0; $cuspTolGradi = 0;
    $cuspMinutiTarget = 0; $cuspTolMinuti = 0;
    $cuspGradiMin = 0; $cuspGradiMax = 29; $cuspMinMin = 0; $cuspMinMax = 59;
    $targetStr = '';
    if ($modalita === 'cuspidi') {
        $cuspCasa         = max(1, min(12, intval($_GET['casa'] ?? 1)));
        $cuspSegno        = intval($_GET['segno'] ?? 0);
        $cuspGradiTarget  = max(0, min(29, intval($_GET['gradi'] ?? 0)));
        $cuspTolGradi     = max(0, min(30, intval($_GET['tol_gradi'] ?? 0)));
        $cuspMinutiTarget = max(0, min(59, intval($_GET['minuti'] ?? 0)));
        $cuspTolMinuti    = max(0, min(59, intval($_GET['tol_minuti'] ?? 0)));

        $cuspGradiMin = max(0,  $cuspGradiTarget  - $cuspTolGradi);
        $cuspGradiMax = min(29, $cuspGradiTarget  + $cuspTolGradi);
        $cuspMinMin   = max(0,  $cuspMinutiTarget - $cuspTolMinuti);
        $cuspMinMax   = min(59, $cuspMinutiTarget + $cuspTolMinuti);

        $segnoNome  = $cuspSegno > 0 ? (GG_SEGNI[$cuspSegno] ?? '?') : 'qualsiasi segno';
        $targetStr  = sprintf('Casa %d: %s %d°%02d′ ±%d°%02d′',
            $cuspCasa, $segnoNome, $cuspGradiTarget, $cuspMinutiTarget, $cuspTolGradi, $cuspTolMinuti);
    }

    // Condizione effettivamente usata per RuleEngine::valuta() (astri → sempre 'Decima')
    $condizioneValutazione = ($modalita === 'astri') ? 'Decima' : $condizioneInput;

    // ── Cap di sicurezza sul numero di punti ───────────────────────────
    $nLat = (int)round(($latMax - $latMin) / $step);
    $nLon = (int)round(($lonMax - $lonMin) / $step);
    $totalePunti = ($nLat + 1) * ($nLon + 1);

    if ($totalePunti > MAX_PUNTI_GRIGLIA) {
        sseG('error', [
            'message' => sprintf(
                'Griglia troppo grande: %s punti richiesti (limite %s). Restringi l\'area oppure aumenta lo step.',
                number_format($totalePunti, 0, ',', '.'),
                number_format(MAX_PUNTI_GRIGLIA, 0, ',', '.')
            ),
        ]);
        exit;
    }

    // ── RS + pianeti (una sola volta) ──────────────────────────────────
    $rs        = $swe->calcolaRS($g, $m, $a, $oraGmt, $anno);
    $oraGmtRS  = $rs['ora_gmt'];
    $giornoRS  = $rs['giorno'];
    $meseRS    = $rs['mese'];
    $annoRSeff = $rs['anno'];

    $pianetiRS  = $swe->calcolaPianeti($giornoRS, $meseRS, $annoRSeff, $oraGmtRS);
    $temaNatale = $swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);

    // Rule Map radicale: attiva solo in modalita=standard (disabilitata per
    // 'astri' come in ricerca_stream_api.php, non pertinente per 'cuspidi')
    $ruleMapCorrente = getRuleMapEsclusione($condizioneInput);
    $hasRuleMap = ($modalita === 'standard')
                  && !empty($ruleMapCorrente['malevoli'])
                  && !empty($ruleMapCorrente['case']);

    $startPayload = [
        'totale'     => $totalePunti,
        'step'       => $step,
        'rs_gmt'     => $rs['stringa'],
        'modalita'   => $modalita,
        'condizione' => $condizioneValutazione,
        'bbox'       => ['lat_min' => $latMin, 'lat_max' => $latMax, 'lon_min' => $lonMin, 'lon_max' => $lonMax],
    ];
    if ($modalita === 'cuspidi') {
        $startPayload['target_str'] = $targetStr;
        $startPayload['casa']       = $cuspCasa;
    }
    if ($modalita === 'astri') {
        $startPayload['astri_in_casa'] = $astriInCasa;
    }
    sseG('start', $startPayload);

    // ── Loop principale ─────────────────────────────────────────────────
    $risultati = [];
    $processed = 0;
    $trovati   = 0;
    $totaleEsclusiRadicale   = 0;
    $totaleEsclusiFiltro     = 0;
    $totaleEsclusiCondizione = 0;

    $progressOgni = max(50, (int)floor($totalePunti / 500));

    $emitProgress = function () use (
        &$processed, $totalePunti, &$trovati,
        &$totaleEsclusiRadicale, &$totaleEsclusiFiltro, &$totaleEsclusiCondizione
    ) {
        sseG('progress', [
            'processed'          => $processed,
            'totale'             => $totalePunti,
            'perc'               => round($processed / $totalePunti * 100),
            'trovati'            => $trovati,
            'esclusi_radicale'   => $totaleEsclusiRadicale,
            'esclusi_filtro'     => $totaleEsclusiFiltro,
            'esclusi_condizione' => $totaleEsclusiCondizione,
        ]);
    };

    for ($i = 0; $i <= $nLat; $i++) {
        $latPoint = round($latMin + $i * $step, 4);

        for ($j = 0; $j <= $nLon; $j++) {
            $lonPoint = round($lonMin + $j * $step, 4);

            if ($processed % 200 === 0 && connection_aborted()) break 2;

            try {
                $caseRS = $swe->calcolaCasePlacido(
                    $giornoRS, $meseRS, $annoRSeff,
                    $oraGmtRS, $latPoint, $lonPoint
                );

                $pianetiConCase = $pianetiRS;
                foreach ($pianetiConCase as $idP => $p) {
                    $pianetiConCase[$idP]['casa'] = $swe->trovaCasaPublic($p['longitudine'], $caseRS);
                }

                // ═══════════════════════════════════════════════════════
                //  MODALITÀ CUSPIDI — nessun RuleEngine, solo match cuspide
                // ═══════════════════════════════════════════════════════
                if ($modalita === 'cuspidi') {
                    $motiviEsclusione = verificaEsclusioneRS($pianetiConCase, $caseRS, $temaNatale);
                    $esclusaFiltro    = !empty($motiviEsclusione);
                    if ($esclusaFiltro) {
                        $totaleEsclusiFiltro++;
                        if (!$mostraEscluse) {
                            $processed++;
                            if ($processed % $progressOgni === 0) $emitProgress();
                            continue;
                        }
                    }

                    if (!isset($caseRS[$cuspCasa])) {
                        $processed++;
                        continue;
                    }

                    $lonCuspide = $caseRS[$cuspCasa]['longitudine'];
                    $pos        = lon2Sgm($lonCuspide);

                    $matchSegno = ($cuspSegno === 0) || ($pos['segno'] === $cuspSegno);
                    $matchGradi = ($pos['gradi']  >= $cuspGradiMin) && ($pos['gradi']  <= $cuspGradiMax);
                    $matchMin   = ($pos['minuti'] >= $cuspMinMin)   && ($pos['minuti'] <= $cuspMinMax);

                    if ($matchSegno && $matchGradi && $matchMin) {
                        $trovati++;
                        $dist    = distanzaDalTarget($pos, $cuspGradiTarget, $cuspMinutiTarget);
                        $segNome = GG_SEGNI[$pos['segno']] ?? '?';

                        $ris = [
                            'lat'               => $latPoint,
                            'lon'               => $lonPoint,
                            'segno_num'         => $pos['segno'],
                            'segno_nome'        => $segNome,
                            'gradi'             => $pos['gradi'],
                            'minuti'            => $pos['minuti'],
                            'secondi'           => $pos['secondi'],
                            'lon_cuspide'       => round($lonCuspide, 4),
                            'cuspide_str'       => sprintf('%s %d°%02d′%02d″',
                                                    $segNome, $pos['gradi'], $pos['minuti'], $pos['secondi']),
                            'distanza'          => round($dist, 4),
                            'esclusa_filtro'    => $esclusaFiltro,
                            'motivi_esclusione' => $motiviEsclusione,
                        ];

                        $risultati[] = $ris;
                        sseG('result', $ris);
                    }

                    $processed++;
                    if ($processed % $progressOgni === 0) $emitProgress();
                    continue;
                }

                // ═══════════════════════════════════════════════════════
                //  MODALITÀ STANDARD — Rule Map + filtri condizione
                // ═══════════════════════════════════════════════════════
                if ($modalita === 'standard' && $hasRuleMap
                    && escludiPerRuleMap($pianetiConCase, $condizioneInput)) {
                    $totaleEsclusiRadicale++;
                    $processed++;
                    if ($processed % $progressOgni === 0) $emitProgress();
                    continue;
                }

                $motiviEsclusione = verificaEsclusioneRS($pianetiConCase, $caseRS, $temaNatale);
                $esclusaFiltro    = !empty($motiviEsclusione);
                if ($esclusaFiltro) {
                    $totaleEsclusiFiltro++;
                    if (!$mostraEscluse) {
                        $processed++;
                        if ($processed % $progressOgni === 0) $emitProgress();
                        continue;
                    }
                }

                if ($modalita === 'standard') {
                    $verificaCond = match($condizioneInput) {
                        'Amore'      => verificaCondizioneAmore($pianetiConCase, $caseRS),
                        'Casa'       => verificaCondizioneCasa($pianetiConCase, $caseRS),
                        'Salute'     => verificaCondizioneSalute($pianetiConCase, $caseRS, $temaNatale['case'], $latPoint),
                        'Denaro'     => verificaCondizioneDenaro($pianetiConCase, $caseRS),
                        'Denaro Low' => verificaCondizioneDenaroLow($pianetiConCase, $caseRS),
                        default      => ['valida' => true],
                    };
                    if (!$verificaCond['valida']) {
                        $totaleEsclusiCondizione++;
                        $processed++;
                        if ($processed % $progressOgni === 0) $emitProgress();
                        continue;
                    }
                }

                // ═══════════════════════════════════════════════════════
                //  MODALITÀ ASTRI — filtro diretto pianeta/casa/vuole
                // ═══════════════════════════════════════════════════════
                if ($modalita === 'astri') {
                    $violazioni = verificaAstriInCasaDirectly($pianetiConCase, $astriInCasa);
                    if (!empty($violazioni)) {
                        $totaleEsclusiCondizione++;
                        $processed++;
                        if ($processed % $progressOgni === 0) $emitProgress();
                        continue;
                    }
                }

                // ── RuleEngine (comune a standard e astri) ──────────────
                $temaRS = [
                    'pianeti' => $pianetiConCase,
                    'case'    => $caseRS,
                    'lat'     => $latPoint,
                    'lon'     => $lonPoint,
                ];

                $val = $engine->valuta($temaNatale, $temaRS, $condizioneValutazione);

                if ($stellineMin > 0 && $val['stelline'] < $stellineMin) {
                    $processed++;
                    if ($processed % $progressOgni === 0) $emitProgress();
                    continue;
                }

                $trovati++;

                // Vicinanza del benefico più vicino alla cuspide della casa
                // tematica (ranking secondario, vedi ggCalcolaVicinanzaBenefici).
                // Non calcolato in modalita 'cuspidi' (gestita a parte, sopra,
                // con 'continue' prima di questo punto del codice).
                $vicinanza = ggCalcolaVicinanzaBenefici($pianetiConCase, $caseRS, $condizioneValutazione);

                $ris = [
                    'lat'               => $latPoint,
                    'lon'               => $lonPoint,
                    'stelline'          => $val['stelline'],
                    'stelle_str'        => $val['stelle_str'],
                    'val'               => $val['val'],
                    'valido'            => $val['is_valida'],
                    'veti'              => $val['veti'],
                    'esclusa_filtro'    => $esclusaFiltro,
                    'motivi_esclusione' => $motiviEsclusione,
                    'vicinanza_gradi'   => $vicinanza ? round($vicinanza['distanza'], 2) : null,
                    'vicinanza_pianeta' => $vicinanza ? ggNomePianeta($vicinanza['pianeta']) : null,
                    'vicinanza_casa'    => $vicinanza ? $vicinanza['casa'] : null,
                ];

                // Debug opt-in (?debug=1): espone la casa esatta di ogni pianeta
                // per questo punto, cosi' un risultato sospetto puo' essere
                // diagnosticato dal JSON senza dover ricalcolare nulla.
                if ($debugMode) {
                    $ris['debug_case_pianeti'] = array_map(
                        fn($p) => $p['casa'],
                        $pianetiConCase
                    );
                    $ris['debug_cuspidi'] = array_map(
                        fn($c) => round($c['longitudine'], 3),
                        array_filter($caseRS, fn($k) => is_int($k), ARRAY_FILTER_USE_KEY)
                    );
                }

                $risultati[] = $ris;

                if ($val['stelline'] >= $streamingMin) {
                    sseG('result', $ris);
                }

            } catch (Exception $e) {
                // punto non calcolabile (latitudine polare, errore swetest) — salta
            }

            $processed++;
            if ($processed % $progressOgni === 0) $emitProgress();
        }
    }

    // ── Ordinamento risultati ───────────────────────────────────────────
    // standard/astri: stelline decrescenti, poi (a parità di stelle) il
    // benefico tematico più vicino alla cuspide vince — più potente secondo
    // Discepolo. cuspidi: invariato, per distanza dal target di longitudine.
    if ($modalita === 'cuspidi') {
        usort($risultati, static fn(array $a, array $b): int => $a['distanza'] <=> $b['distanza']);
    } else {
        usort($risultati, static function (array $a, array $b): int {
            $cmpStelle = $b['stelline'] <=> $a['stelline'];
            if ($cmpStelle !== 0) return $cmpStelle;
            $va = $a['vicinanza_gradi'] ?? PHP_FLOAT_MAX;
            $vb = $b['vicinanza_gradi'] ?? PHP_FLOAT_MAX;
            return $va <=> $vb;
        });
    }

    sseG('done', [
        'risultati'                 => $risultati,
        'totale_risultati'          => count($risultati),
        'totale_calcolati'          => $processed,
        'totale_originale'          => $totalePunti,
        'totale_esclusi_radicale'   => $totaleEsclusiRadicale,
        'totale_esclusi_filtro'     => $totaleEsclusiFiltro,
        'totale_esclusi_condizione' => $totaleEsclusiCondizione,
        'elapsed_ms'                => round((microtime(true) - $tStart) * 1000),
        'rs_gmt'                    => $rs['stringa'],
        'modalita'                  => $modalita,
        'condizione'                => $condizioneValutazione,
        'step'                      => $step,
    ]);

} catch (Throwable $e) {
    sseG('error', ['message' => $e->getMessage()]);
}
