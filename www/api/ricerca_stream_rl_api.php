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

require_once __DIR__ . '/../includes/AstroUtils.php';
require_once __DIR__ . '/../includes/RicercaRSFilters.php';
require_once __DIR__ . '/../includes/RicercaRSAirportRepository.php';
require_once __DIR__ . '/../includes/RicercaRSDeduplicator.php';
require_once __DIR__ . '/../includes/RicercaRSThemeBuilder.php';
require_once __DIR__ . '/../includes/RicercaRSPlanetHouseAssigner.php';
require_once __DIR__ . '/../includes/RicercaRSResultBuilder.php';
require_once __DIR__ . '/../includes/RicercaRSTopK.php';
require_once __DIR__ . '/../includes/RicercaRSExclusionFilter.php';

/**
 * api/ricerca_stream_api.php — Endpoint SSE per ricerca batch aeroporti
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * OTTIMIZZAZIONI PRESTAZIONALI (invariate)
 * ═══════════════════════════════════════════════════════════════════════════
 * 1. Pianeti RS calcolati UNA VOLTA SOLA per tutti gli aeroporti
 *    (i pianeti non cambiano con il luogo, solo le case cambiano).
 * 2. Per ogni aeroporto: solo calcolaCasePlacido() → risparmio ~50% shell_exec.
 * 3. Deduplicazione geografica a bucket 0.3°lat × 0.5°lon (~33km):
 *    aeroporti nello stesso bucket danno ASC identico → collassati a uno.
 *    Riduzione effettiva: ~60-70% degli aeroporti.
 * 4. Streaming SSE: risultati ≥3 stelle inviati in tempo reale,
 *    progress bar live con aeroporti processati / totale / percentuale.
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * RULE MAP DI ESCLUSIONE RADICALE
 * ═══════════════════════════════════════════════════════════════════════════
 * Applicata PRIMA della valutazione stelline, per ogni condizione tematica.
 * Se almeno un malevolo "proibito" occupa una casa "proibita" per quella
 * condizione, l'aeroporto è scartato immediatamente (esclusione_radicale=true).
 *
 * I malevoli "leggeri" (NE=8, PLU=9) vengono esclusi solo per le condizioni
 * Denaro, Denaro Low e Salute dove il loro impatto è dottrinalmente grave.
 * Per Denaro Low vengono esclusi solo MA/SA/UR (più permissivo per natura).
 *
 * Mappatura per condizione:
 *   Decima     → MA/SA/UR/NE/PLU in X
 *   Lavoro     → MA/SA/UR/NE/PLU in VI e X
 *   Amore      → MA/SA/UR/NE/PLU in V e VII  (gestito separatamente)
 *   Salute     → MA/SA/UR/NE/PLU in VI e XII (gestito separatamente con logica estesa)
 *   Denaro     → MA/SA/UR/NE/PLU in II e VIII
 *   Denaro Low → MA/SA/UR         in II e VIII (solo i tre principali)
 *   Casa       → MA/SA/UR/NE/PLU in IV (gestito separatamente con pre-ingresso)
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * FILTRO DI ESCLUSIONE RS (FiltroEsclusione.php) — checkbox "Mostra anche
 * le RS escluse dal filtro"
 * ═══════════════════════════════════════════════════════════════════════════
 * Indipendente dalla Rule Map radicale sopra e dal RuleEngine. Esclude le RS
 * con: Sole/Marte in I-VI-XII RS, ASC RS in I-VI-XII natale, Saturno in X RS,
 * stellium (3+ pianeti) in qualsiasi casa RS. Controllato dal parametro
 * mostra_escluse:
 *   - mostra_escluse=0 (default) → le RS escluse vengono saltate (non
 *     entrano in $risultati), esattamente come la Rule Map radicale.
 *   - mostra_escluse=1 → le RS escluse vengono incluse nei risultati ma
 *     marcate con 'esclusa_filtro' => true e 'motivi_esclusione' => [...],
 *     così il frontend può mostrare il badge "⚠️ esclusa" e la riga
 *     evidenziata (stessa logica già presente in cuspidi_search_api.php).
 * In entrambi i casi il conteggio totale_esclusi_filtro viene sempre
 * accumulato e restituito nell'evento 'done', a prescindere dal checkbox.
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * SSE Events emessi
 * ═══════════════════════════════════════════════════════════════════════════
 *   start    → { totale_aeroporti, rs_gmt, condizione }
 *   progress → { processed, totale, totale_originale, perc, fase }
 *   result   → singolo aeroporto con stelline ≥ soglia (streaming live)
 *   done     → { risultati[], totale_risultati, totale_calcolati,
 *                totale_originale, totale_esclusi_radicale,
 *                totale_esclusi_filtro,
 *                elapsed_ms, rs_gmt, condizione }
 *   error    → { message }
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * GET params
 * ═══════════════════════════════════════════════════════════════════════════
 *   g, m, a, ora_gmt        → dati natali
 *   lat, lon                → coordinate luogo di nascita
 *   anno                    → anno RS (default: anno corrente)
 *   condizione              → Decima|Lavoro|Amore|Salute|Denaro|Denaro Low|Casa
 *   tipo_ricerca            → large_medium|iata_only|tutti
 *   escludi_militari        → 1|0 (default 1)
 *   nazione                 → codice ISO singolo (legacy, compatibilità)
 *   nazioni_filtro          → "IT,FR,DE" — lista ISO separata da virgola
 *   lon_min, lon_max        → fascia oraria per longitudine geografica
 *   stelline_min            → 0-5, soglia minima per il risultato
 *   streaming_min           → 0-5, soglia per invio SSE live (default 3)
 *   astri_in_casa           → JSON: [{pianeta,casa,vuole},...] filtro custom
 *   escludi_radicale        → 1|0 (default 1) — attiva/disattiva la Rule Map
 *   mostra_escluse          → 1|0 (default 0) — mostra anche le RS escluse
 *                              dal FiltroEsclusione (checkbox UI)
 */

// ── SSE headers ──────────────────────────────────────────────────────────
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

set_time_limit(300);
ignore_user_abort(false);

// ── Helper SSE ────────────────────────────────────────────────────────────
function sse(string $event, array $data): void {
    echo "event: {$event}\n";
    echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}


if (isset($_GET['espansione_orbe']) && $_GET['espansione_orbe'] === '1' && !$auth->hasFeature('dynamic_orb')) {
    sse('error', [
        'message' => 'Questa funzione è riservata agli utenti del piano Supporter.',
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
//  PARAMETRI GET
// ═══════════════════════════════════════════════════════════════════════════

// Dati natali
$g      = intval($_GET['g']         ?? 1);
$m      = intval($_GET['m']         ?? 1);
$a      = intval($_GET['a']         ?? 1990);
$oraGmt = floatval($_GET['ora_gmt'] ?? 12.0);
$lat    = floatval($_GET['lat']     ?? 41.9);
$lon    = floatval($_GET['lon']     ?? 12.48);

// Parametri ricerca RS
$annoRS          = intval($_GET['anno_rs']        ?? (int)date('Y'));
$rlIndex         = intval($_GET['rl_index']       ?? 0);
$condizione      = $_GET['condizione']            ?? 'Decima';
$tipoRicerca     = $_GET['tipo_ricerca']          ?? 'large_medium';
$escludiMilitari = ($_GET['escludi_militari']     ?? '1') === '1';
$filtroNazione   = trim($_GET['nazione']          ?? '');
$stellineMin     = intval($_GET['stelline_min']   ?? 0);
$streamingMin    = intval($_GET['streaming_min']  ?? 3); // soglia SSE live

// Attiva/disattiva esclusione radicale (default ON)
$usaEscludiRadicale = ($_GET['escludi_radicale'] ?? '1') === '1';

// Mostra anche le RS escluse dal FiltroEsclusione (checkbox UI, default OFF)
$mostraEscluse = ($_GET['mostra_escluse'] ?? '0') === '1';

// Filtri avanzati
$nazioniFiltro = trim($_GET['nazioni_filtro'] ?? '');
$lonMin = isset($_GET['lon_min']) && $_GET['lon_min'] !== '' ? floatval($_GET['lon_min']) : null;
$lonMax = isset($_GET['lon_max']) && $_GET['lon_max'] !== '' ? floatval($_GET['lon_max']) : null;

// Tipo località: parametro opzionale, assenza = comportamento legacy
$tipoLocalita = trim($_GET['tipo_localita'] ?? '');
$tipiLocalitaValidi = ['aeroporti', 'localita'];
if ($tipoLocalita !== '' && !in_array($tipoLocalita, $tipiLocalitaValidi, true)) {
    $tipoLocalita = '';
}

if ($tipoLocalita === 'localita' && !$auth->hasFeature('locality_search')) {
    sse('error', [
        'message' => 'Questa funzione è riservata agli utenti del piano Supporter.',
    ]);
    exit;
}

// Ricerca progressiva delle località.
// Ogni richiesta analizza una tranche del dataset deduplicato.
$offsetRicerca = max(0, intval($_GET['offset_ricerca'] ?? 0));
$limiteRicerca = max(1, min(30000, intval($_GET['limite_ricerca'] ?? 30000)));

// Numero massimo di località restituite: valori consentiti 50, 100, 150, 200, 250, 500, 1000
$numeroLocalitaRaw = trim($_GET['numero_localita'] ?? '50');
$numeroLocalita = in_array(
    $numeroLocalitaRaw,
    ['50', '100', '150', '200', '250', '500', '1000'],
    true
)
    ? (int)$numeroLocalitaRaw
    : 50;

// Validazione condizione
$condizioniValide = ['Decima', 'Lavoro', 'Amore', 'Salute', 'Denaro', 'Denaro Low', 'Casa'];
if (!in_array($condizione, $condizioniValide, true)) {
    $condizione = 'Decima';
}

// Clamp soglie
$stellineMin  = max(0, min(5, $stellineMin));
$streamingMin = max(0, min(5, $streamingMin));

// ── Filtro Astri in Casa (custom) ────────────────────────────────────────
// Formato GET: astri_in_casa = JSON array di { pianeta: int|'ASC', casa: int, vuole: bool }
// Esempio: [{"pianeta":5,"casa":10,"vuole":true},{"pianeta":"ASC","casa":1,"vuole":false}]
$astriInCasa = [];
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
            $modalitaF = ($f['modalita'] ?? 'in_casa') === 'cuspide' ? 'cuspide' : 'in_casa';
            if ($modalitaF === 'cuspide' && !$auth->hasFeature('astri_in_cuspide')) {
                $modalitaF = 'in_casa'; // UX-0014: senza piano Supporter, fallback forzato
            }
            $astriInCasa[] = [
                'pianeta'  => $pid,
                'casa'     => $casaF,
                'vuole'    => (bool)$f['vuole'],
                'modalita' => $modalitaF,
            ];
        }
    }
}

// ── Bucket dedup ──────────────────────────────────────────────────────────
// 0.3° lat × 0.5° lon ≈ 33km × 35km a lat 45°
// Aeroporti nello stesso bucket producono ASC identico → un solo calcolo
$bucketLat = 0.3;
$bucketLon = 0.5;

// ── Flag modalità "Astri nelle Case" ─────────────────────────────────────
// Quando l'utente ha impostato filtri astri personalizzati, la condizione
// passata dall'API è sempre 'Decima' (usata solo per il ranking stelline).
// In questa modalità l'esclusione radicale Decima (malevoli in X) verrebbe
// applicata PRIMA del filtro astri, scartando RS valide per i criteri utente
// solo perché hanno anche Saturno in X. Disabilitiamo quindi $hasRuleMap
// per l'intera ricerca quando ci sono filtri astri attivi.
// N.B.: $usaEscludiRadicale rimane invariato — tocchiamo solo $hasRuleMap.
$modalitaAstri = !empty($astriInCasa);

// ═══════════════════════════════════════════════════════════════════════════
//  CORPO PRINCIPALE
// ═══════════════════════════════════════════════════════════════════════════

try {
    require_once '../includes/SweCalc.php';
    require_once '../includes/RuleEngine.php';
    require_once '../includes/FiltroEsclusione.php';
    require_once '../includes/StellineV2Calculator.php';
    if (MYASTRAL_ALIGNMENT_MODE) {
        require_once '../includes/RuleEngineExtended.php';
    }

    $tStart = microtime(true);
    $swe    = new SweCalc();
    $engine = new RuleEngine();
    // Sistema V2 parallelo (roadmap sostituzione stelline) — calcolato in
    // aggiunta al sistema attuale, non lo sostituisce ancora (Fase 2a additiva).
    $v2Calc = new StellineV2Calculator();
    $engineExt = MYASTRAL_ALIGNMENT_MODE ? new RuleEngineExtended() : null;

    $pdo = db_connect();

    // ─────────────────────────────────────────────────────────────────────
    //  Step 1 — Calcolo RS e tema natale (invarianti per tutti gli aeroporti)
    // ─────────────────────────────────────────────────────────────────────
    $rlList = $swe->calcolaTutteRLLibsweCompatibileLunaApi($g, $m, $a, $oraGmt, $annoRS);
    if (!isset($rlList[$rlIndex])) {
        sse('error', ['message' => "Indice RL {$rlIndex} non trovato (trovate " . count($rlList) . " RL)."]);
        exit;
    }
    $rl        = $rlList[$rlIndex];
    $oraGmtRS  = $rl['ora_gmt'];
    $giornoRS  = $rl['giorno'];
    $meseRS    = $rl['mese'];
    $annoRSeff = $rl['anno'];

    // Tema natale — serve al RuleEngine per i veti ASC RS in casa natale
    // e al FiltroEsclusione per il check ASC RS in I/VI/XII natale.
    $temaNatale = $swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);

    // Pianeti RS — calcolati UNA VOLTA SOLA, identici per ogni aeroporto
    // (solo le case Placido cambiano con lat/lon)
    $pianetiRS = $swe->calcolaPianeti($giornoRS, $meseRS, $annoRSeff, $oraGmtRS);

    // ─────────────────────────────────────────────────────────────────────
    //  Step 2 — Pre-calcolo Rule Map per la condizione selezionata
    //  Lo eseguiamo una sola volta fuori dal loop.
    // ─────────────────────────────────────────────────────────────────────
    $ruleMapCorrente = getRuleMapEsclusione($condizione);
    $hasRuleMap      = $usaEscludiRadicale
                       && !empty($ruleMapCorrente['malevoli'])
                       && !empty($ruleMapCorrente['case']);

    // In modalità "Astri nelle Case" la condizione è 'Decima' solo per il
    // ranking. Disabilitiamo l'esclusione radicale per non perdere RS valide
    // per i criteri dell'utente prima ancora di verificarli.
    if ($modalitaAstri) {
        $hasRuleMap = false;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Step 3 — Query aeroporti con filtri SQL
    // ─────────────────────────────────────────────────────────────────────
    $where  = ['attivo = true'];
    $params = [];

    // Esclusione militari
    if ($escludiMilitari) {
        $where[] = 'militare = false';
    }

    // Tipo aeroporto
    if ($tipoRicerca === 'large_medium') {
        $where[] = "tipo IN ('large_airport','medium_airport')";
    } elseif ($tipoRicerca === 'iata_only') {
        $where[] = "iata_code IS NOT NULL AND iata_code <> ''";
        $where[] = "tipo IN ('large_airport','medium_airport','small_airport')";
    } else {
        // 'tutti'
        $where[] = "tipo IN ('large_airport','medium_airport','small_airport')";
    }

    // Filtro singola nazione (legacy — mantenuto per compatibilità con ricerca.php)
    if ($filtroNazione !== '') {
        $where[] = 'nazione = ?';
        $params[] = strtoupper($filtroNazione);
    }

    // Filtro multiple nazioni (nuovo — prevale su singola nazione se entrambi presenti)
    if ($nazioniFiltro !== '') {
        $naz          = array_values(array_filter(
            array_map('strtoupper', array_map('trim', explode(',', $nazioniFiltro)))
        ));
        if (!empty($naz)) {
            $placeholders = implode(',', array_fill(0, count($naz), '?'));
            $where[]      = "nazione IN ({$placeholders})";
            $params       = array_merge($params, $naz);
        }
    }

    // Fascia oraria per longitudine geografica
    if ($lonMin !== null) {
        $where[]  = 'longitudine >= ?';
        $params[] = $lonMin;
    }
    if ($lonMax !== null) {
        $where[]  = 'longitudine <= ?';
        $params[] = $lonMax;
    }

    $dimensioneBlocco = $tipoLocalita === 'localita' ? 30000 : 500;

    // Per gli aeroporti manteniamo il comportamento storico.
    // Per le località partiamo invece dall'offset della tranche richiesta.
    $offsetBlocco = $tipoLocalita === 'localita' ? $offsetRicerca : 0;
    $fineTranche = $tipoLocalita === 'localita'
        ? $offsetRicerca + $limiteRicerca
        : null;

    $dimensionePrimoBlocco = $fineTranche !== null
        ? min($dimensioneBlocco, $fineTranche - $offsetBlocco)
        : $dimensioneBlocco;

    $recupero = recuperaAeroportiDeduplicati(
        $pdo,
        $where,
        $params,
        $bucketLat,
        $bucketLon,
        $tipoLocalita,
        $dimensionePrimoBlocco,
        $offsetBlocco
    );

    $selezionati = $recupero['aeroporti'];
    $totaleAero  = $recupero['totale_originale'];
    $totaleCalc  = $recupero['totale_deduplicato'];

    // Evento SSE: informa il frontend sul totale grezzo e il momento RS
    sse('start', [
        'totale_aeroporti'   => $totaleAero,
        'rl_gmt'             => $rl['gmt_str'],
        'anno_rs'            => $annoRS,
        'rl_index'           => $rlIndex,
        'condizione'         => $condizione,
        'escludi_radicale'   => $usaEscludiRadicale,
        'mostra_escluse'     => $mostraEscluse,
        'rule_map_case'      => $hasRuleMap ? $ruleMapCorrente['case'] : [],
        'rule_map_malevoli'  => $hasRuleMap ? $ruleMapCorrente['malevoli'] : [],
    ]);

    // ─────────────────────────────────────────────────────────────────────
    //  Step 4 — Deduplicazione geografica eseguita direttamente in PostgreSQL
    // ─────────────────────────────────────────────────────────────────────

    sse('progress', [
        'processed'        => 0,
        'totale'           => $totaleCalc,
        'totale_originale' => $totaleAero,
        'perc'             => 0,
        'fase'             => 'dedup_done',
    ]);

    // ─────────────────────────────────────────────────────────────────────
    //  Step 5 — Loop principale: calcolo case + esclusione + valutazione
    // ─────────────────────────────────────────────────────────────────────
    $risultati             = [];
    $limiteRisultatiInMemoria = ($tipoLocalita === 'localita' && $numeroLocalita !== null)
        ? $numeroLocalita
        : null;
    $processed             = 0;
    $totaleEsclusiRadicale = 0; // contatore diagnostico (Rule Map)
    $totaleEsclusiFiltro   = 0; // contatore RS escluse da FiltroEsclusione (checkbox)
    $totaleEsclusiAmore    = 0; // contatore RS escluse dal filtro specifico Amore
    $totaleEsclusiCasa     = 0; // contatore RS escluse dal filtro specifico Casa
    $totaleEsclusiSalute   = 0; // contatore RS escluse dal filtro specifico Salute
    $totaleEsclusiDenaro   = 0; // contatore RS escluse dal filtro specifico Denaro
    $totaleEsclusiDenaroLow = 0; // contatore RS escluse dal filtro specifico Denaro Low
$totaleCalcoliPlacido = 0;   // diagnostica: numero calcoli case Placido eseguiti
$totaleValutazioniRuleEngine = 0; // diagnostica: numero chiamate RuleEngine::valuta()
    $ricercaInterrotta = false;

    while ($selezionati !== []) {
        foreach ($selezionati as $aero) {

            // Interruzione se il client ha chiuso la connessione
            if ($processed % 200 === 0 && connection_aborted()) {
                $ricercaInterrotta = true;
                break;
            }

            $latA = floatval($aero['latitudine']);
            $lonA = floatval($aero['longitudine']);

            try {
                // ── A. Calcolo case Placido per questo aeroporto ──────────────
                // I pianeti $pianetiRS sono già calcolati — solo le case variano.
                $totaleCalcoliPlacido++;
                $caseRS = $swe->calcolaCasePlacido(
                    $giornoRS, $meseRS, $annoRSeff,
                    $oraGmtRS, $latA, $lonA
                );

                // ── B. Assegna casa RS a ogni pianeta (riusa metodo pubblico) ─
                $pianetiConCase = assegnaCaseAiPianeti($pianetiRS, $caseRS, $swe);

                // ── C. ESCLUSIONE RADICALE ─────────────────────────────────────
                // Applicata PRIMA del RuleEngine per massima efficienza:
                // se un malevolo "proibito" è nella casa "proibita" per questa
                // condizione, l'aeroporto viene scartato immediatamente senza
                // chiamare valuta() (risparmio CPU significativo su 84k aeroporti).
                // Nota: per le condizioni "Amore", "Casa" e "Denaro" la Rule Map
                // può essere vuota (per Amore/Casa) o non applicabile (Denaro gestito
                // separatamente con pre-ingresso), quindi il controllo viene saltato.
                if ($hasRuleMap && escludiPerRuleMap($pianetiConCase, $condizione)) {
                    $totaleEsclusiRadicale++;
                    $processed++;
                    // Aggiorna progress ogni 50 anche per gli esclusi
                    if ($processed % 50 === 0) {
                        sse('progress', [
                            'processed'        => $processed,
                            'totale'           => $totaleCalc,
                            'perc'             => round($processed / $totaleCalc * 100),
                            'fase'             => 'calcolo',
                            'esclusi_radicale' => $totaleEsclusiRadicale,
                            'esclusi_filtro'   => $totaleEsclusiFiltro,
                                'calcoli_placido'  => $totaleCalcoliPlacido,
                        ]);
                    }
                    continue;
                }

                // ── C-bis. FILTRO DI ESCLUSIONE RS (FiltroEsclusione.php) ─────
                // Sole/Marte in I-VI-XII RS, ASC RS in I-VI-XII natale,
                // Saturno in X RS, stellium in qualsiasi casa RS. Indipendente
                // dal RuleEngine e dalla Rule Map radicale qui sopra.
                // Controllato dal checkbox "Mostra anche le RS escluse dal
                // filtro" (mostra_escluse): se non spuntato, l'aeroporto viene
                // saltato come prima; se spuntato, viene comunque incluso nei
                // risultati ma marcato 'esclusa_filtro' => true.
                $valutazioneFiltroRS = valutaEsclusioneFiltroRS($pianetiConCase, $caseRS, $temaNatale);
                $motiviEsclusioneFiltro = $valutazioneFiltroRS['motivi'];
                $esclusaFiltro = $valutazioneFiltroRS['esclusa'];

                if ($esclusaFiltro) {
                    $totaleEsclusiFiltro++;
                    if (!$mostraEscluse) {
                        $processed++;
                        if ($processed % 50 === 0) {
                            sse('progress', [
                                'processed'        => $processed,
                                'totale'           => $totaleCalc,
                                'perc'             => round($processed / $totaleCalc * 100),
                                'fase'             => 'calcolo',
                                'esclusi_radicale' => $totaleEsclusiRadicale,
                                'esclusi_filtro'   => $totaleEsclusiFiltro,
                            'calcoli_placido'  => $totaleCalcoliPlacido,
                            ]);
                        }
                        continue;
                    }
                }

                // ── C-ter. FILTRO SPECIFICO PER AMORE ──────────────────────────
                // Applicato DOPO i filtri globali (veti e FiltroEsclusione).
                // Verifica che almeno un benefico (VE/GI/SO) sia in V o VII casa RS,
                // che non ci siano malevoli (MA/SA/UR/NE/PLU) in V o VII,
                // e che i benefici non siano troppo vicini all'uscita dalla casa.
                if ($condizione === 'Amore') {
                    $verificaAmore = verificaCondizioneAmore($pianetiConCase, $caseRS);
                    if (!$verificaAmore['valida']) {
                        $totaleEsclusiAmore++;
                        // Le RS escluse dal filtro Amore NON vengono incluse nei risultati
                        // (non c'è un checkbox "Mostra anche le RS escluse da Amore")
                        $processed++;
                        if ($processed % 50 === 0) {
                            sse('progress', [
                                'processed'        => $processed,
                                'totale'           => $totaleCalc,
                                'perc'             => round($processed / $totaleCalc * 100),
                                'fase'             => 'calcolo',
                                'esclusi_radicale' => $totaleEsclusiRadicale,
                                'esclusi_filtro'   => $totaleEsclusiFiltro,
                            'calcoli_placido'  => $totaleCalcoliPlacido,
                                'esclusi_amore'    => $totaleEsclusiAmore,
                            ]);
                        }
                        continue;
                    }
                }

                // ── C-quater. FILTRO SPECIFICO PER CASA (IV Casa) ─────────────
                // Applicato DOPO i filtri globali (veti e FiltroEsclusione).
                // Verifica che almeno un benefico (SO/GI/VE) sia in IV casa RS
                // con pre-ingresso di 3°, che non ci siano malevoli (MA/SA/UR/NE/PLU)
                // in IV casa (con pre-ingresso), e che i benefici non siano
                // troppo vicini all'uscita dalla IV (cuspide V).
                if ($condizione === 'Casa') {
                    $verificaCasa = verificaCondizioneCasa($pianetiConCase, $caseRS);
                    if (!$verificaCasa['valida']) {
                        $totaleEsclusiCasa++;
                        // Le RS escluse dal filtro Casa NON vengono incluse nei risultati
                        // (non c'è un checkbox "Mostra anche le RS escluse da Casa")
                        $processed++;
                        if ($processed % 50 === 0) {
                            sse('progress', [
                                'processed'        => $processed,
                                'totale'           => $totaleCalc,
                                'perc'             => round($processed / $totaleCalc * 100),
                                'fase'             => 'calcolo',
                                'esclusi_radicale' => $totaleEsclusiRadicale,
                                'esclusi_filtro'   => $totaleEsclusiFiltro,
                            'calcoli_placido'  => $totaleCalcoliPlacido,
                                'esclusi_amore'    => $totaleEsclusiAmore,
                                'esclusi_casa'     => $totaleEsclusiCasa,
                            ]);
                        }
                        continue;
                    }
                }

                // ── C-quinquies. FILTRO SPECIFICO PER SALUTE ────────────────────
                // Applicato DOPO i filtri globali (veti e FiltroEsclusione).
                // Implementa i criteri di protezione massima della scuola di Ciro Discepolo:
                //   1. Tolleranza pre-ingresso ampliata a 4° per malefici in I/VI/XII
                //   2. Scudo benefico in I casa (Giove/Venere) con sicurezza uscita 3°
                //   3. Esclusione Sole in XII casa (spegnimento energia vitale)
                //   4. Rafforzamento ASC natale con tolleranza 3°
                //   5. Protezione universale: Giove o Venere in I/VI/XII
                $scudoBeneficoAttivo = false;
                $beneficoInI = null;
            
                if ($condizione === 'Salute') {
                    $verificaSalute = verificaCondizioneSalute(
                        $pianetiConCase,
                        $caseRS,
                        $temaNatale['case'],
                        $latA
                    );
                
                    if (!$verificaSalute['valida']) {
                        $totaleEsclusiSalute++;
                        // Le RS escluse dal filtro Salute NON vengono incluse nei risultati
                        $processed++;
                        if ($processed % 50 === 0) {
                            sse('progress', [
                                'processed'        => $processed,
                                'totale'           => $totaleCalc,
                                'perc'             => round($processed / $totaleCalc * 100),
                                'fase'             => 'calcolo',
                                'esclusi_radicale' => $totaleEsclusiRadicale,
                                'esclusi_filtro'   => $totaleEsclusiFiltro,
                            'calcoli_placido'  => $totaleCalcoliPlacido,
                                'esclusi_amore'    => $totaleEsclusiAmore,
                                'esclusi_casa'     => $totaleEsclusiCasa,
                                'esclusi_salute'   => $totaleEsclusiSalute,
                            ]);
                        }
                        continue;
                    }
                
                    // Se lo scudo benefico è attivo, lo registriamo per il risultato
                    $scudoBeneficoAttivo = $verificaSalute['scudo_benefico'] ?? false;
                    $beneficoInI = $verificaSalute['benefico_in_i'] ?? null;
                }

                // ── C-sexies. FILTRO SPECIFICO PER DENARO ──────────────────────
                // Applicato DOPO i filtri globali (veti e FiltroEsclusione).
                // Verifica che almeno un benefico (SO/GI/VE) sia in II o VIII casa RS
                // con pre-ingresso di 3°, che non ci siano malevoli (MA/SA/UR/NE/PLU)
                // in II o VIII (con pre-ingresso), e che i benefici non siano
                // troppo vicini all'uscita (III se in II, IX se in VIII).
                $denaroAlertGiove = false;
                $denaroBeneficioTrovato = null;
            
                if ($condizione === 'Denaro') {
                    $verificaDenaro = verificaCondizioneDenaro($pianetiConCase, $caseRS);
                    if (!$verificaDenaro['valida']) {
                        $totaleEsclusiDenaro++;
                        // Le RS escluse dal filtro Denaro NON vengono incluse nei risultati
                        $processed++;
                        if ($processed % 50 === 0) {
                            sse('progress', [
                                'processed'        => $processed,
                                'totale'           => $totaleCalc,
                                'perc'             => round($processed / $totaleCalc * 100),
                                'fase'             => 'calcolo',
                                'esclusi_radicale' => $totaleEsclusiRadicale,
                                'esclusi_filtro'   => $totaleEsclusiFiltro,
                            'calcoli_placido'  => $totaleCalcoliPlacido,
                                'esclusi_amore'    => $totaleEsclusiAmore,
                                'esclusi_casa'     => $totaleEsclusiCasa,
                                'esclusi_salute'   => $totaleEsclusiSalute,
                                'esclusi_denaro'   => $totaleEsclusiDenaro,
                            ]);
                        }
                        continue;
                    }
                
                    // Registra i dettagli del beneficio trovato e l'alert Giove
                    $denaroBeneficioTrovato = $verificaDenaro['beneficio_trovato'] ?? null;
                    $denaroAlertGiove = $verificaDenaro['alert_giove_bistabile'] ?? false;
                }

                // ── C-septies. FILTRO SPECIFICO PER DENARO LOW ──────────────────
                // Applicato DOPO i filtri globali (veti e FiltroEsclusione).
                // Implementa la logica di "difesa del patrimonio":
                //   1. Esclusione assoluta di MA/SA/UR/NE/PLU in II o VIII
                //   2. Sconfinamento dalla I (per II) o VII (per VIII) a meno di 3°
                // NOTA: NON richiede la presenza di benefici (Sole/Giove/Venere).
                if ($condizione === 'Denaro Low') {
                    $verificaDenaroLow = verificaCondizioneDenaroLow($pianetiConCase, $caseRS);
                    if (!$verificaDenaroLow['valida']) {
                        $totaleEsclusiDenaroLow++;
                        // Le RS escluse dal filtro Denaro Low NON vengono incluse nei risultati
                        $processed++;
                        if ($processed % 50 === 0) {
                            sse('progress', [
                                'processed'           => $processed,
                                'totale'              => $totaleCalc,
                                'perc'                => round($processed / $totaleCalc * 100),
                                'fase'                => 'calcolo',
                                'esclusi_radicale'    => $totaleEsclusiRadicale,
                                'esclusi_filtro'      => $totaleEsclusiFiltro,
                                'esclusi_amore'       => $totaleEsclusiAmore,
                                'esclusi_casa'        => $totaleEsclusiCasa,
                                'esclusi_salute'      => $totaleEsclusiSalute,
                                'esclusi_denaro'      => $totaleEsclusiDenaro,
                                'esclusi_denaro_low'  => $totaleEsclusiDenaroLow,
                            ]);
                        }
                        continue;
                    }
                }

                // ── E. Filtro Astri in Casa anticipato ─────────────────────────
                if ($modalitaAstri && !empty($astriInCasa)) {
                    $violazioniDirette = verificaAstriInCasaDirectly($pianetiConCase, $astriInCasa, $caseRS);
                    if (!empty($violazioniDirette)) {
                        $processed++;
                        continue;
                    }
                }

                // ── E. Costruzione tema RS per questo aeroporto ───────────────
                $temaRS = costruisciTemaRS(
                    $pianetiConCase,
                    $caseRS,
                    $latA,
                    $lonA
                );

                // ── E. Valutazione RuleEngine (34 regole Discepolo) ───────────
                $totaleValutazioniRuleEngine++;
                $val = $engine->valuta($temaNatale, $temaRS, $condizione, $astriInCasa);

                // ── E-v2. Calcolo Stelline V2 parallelo (additivo, non influenza
                // ordinamento/filtro stelline_min in questa sotto-fase) ─────────
                $pianetiRS_v2 = [];
                foreach ($temaRS['pianeti'] as $_pid => $_p) {
                    $pianetiRS_v2[$_pid] = ['casa' => $_p['casa'], 'longitudine' => $_p['longitudine']];
                }
                $caseRS_v2 = $temaRS['case'] ?? [];
                $valV2 = $v2Calc->calcola($pianetiRS_v2, $caseRS_v2, $condizione, $temaNatale);

                // Punteggio "Discepolo parziale" opzionale (roadmap MyAstral).
                // Calcolato SOLO se il flag e attivo; non influenza $val ne i veti.
                $punteggioMyAstral = null;
                if ($engineExt !== null) {
                    $punteggioMyAstral = $engineExt->calcolaPunteggioParziale(
                        $temaRS['pianeti'],
                        $temaRS['case'],
                        $condizione
                    );
                }

                // Regola 33 (Saturno prevale) - ESCLUSIONE, non azzeramento.
                // Attiva solo con MYASTRAL_ALIGNMENT_MODE=true. Se Saturno e nella
                // stessa casa della condizione, la RS/RL va tolta dai risultati -
                // confermato esplicitamente dal committente, non solo punteggio a 0.
                if ($punteggioMyAstral !== null && ($punteggioMyAstral['saturno_prevale'] ?? false)) {
                    $processed++;
                    continue;
                }

                // ── F. Filtro stelline minime (ora su V2, sistema primario) ────
                if ($stellineMin > 0 && $valV2['stelle_totali'] < $stellineMin) {
                    $processed++;
                    continue;
                }

                // ── G. Filtro Astri in Casa personalizzato ────────────────────
                //
                // FIX BUG: il vecchio codice leggeva $val['astri_warning'],
                // prodotto dalla Fase 3 del RuleEngine. Problema: se la Fase 1
                // del RuleEngine scatta un VETO (Marte in VI, ASC in XII natale,
                // stellium, reg.33, lat>60°), la funzione valuta() ritorna
                // immediatamente con astri_warning=[] VUOTO, senza eseguire la
                // Fase 3. Di conseguenza gli aeroporti con veto passavano sempre
                // il filtro astri, anche se il pianeta richiesto era in un'altra
                // casa — e venivano inclusi nei risultati erroneamente.
                //
                // SOLUZIONE CHIRURGICA: quando siamo in modalità astri, verifichiamo
                // i criteri DIRETTAMENTE su $pianetiConCase (già disponibile al
                // passo B), prima ancora di chiamare il RuleEngine. Il RuleEngine
                // continua a fare il suo lavoro invariato; usiamo solo il suo
                // risultato per stelline/VAL/veti, non per il filtro astri.
                //
                // Il flag $modalitaAstri garantisce che questo blocco venga eseguito
                // SOLO quando l'utente ha impostato filtri astri personalizzati;
                // in tutti gli altri casi il comportamento è identico a prima.
                $astriWarnings = $val['astri_warning'] ?? []; // compatibilità campo output

                if (!$modalitaAstri && !empty($astriInCasa) && !empty($astriWarnings)) {
                    // Fallback per il caso non-modalitaAstri con filtri attivi
                    // (comportamento originale preservato)
                    $processed++;
                    continue;
                }

    // ── H. Costruzione record risultato ───────────────────────────
            $ris = costruisciRisultatoRicercaRS(
                $aero,
                $latA,
                $lonA,
                $val,
                $astriWarnings,
                $usaEscludiRadicale,
                $esclusaFiltro,
                $motiviEsclusioneFiltro,
                $condizione,
                $scudoBeneficoAttivo,
                $beneficoInI,
                $denaroBeneficioTrovato,
                $denaroAlertGiove
            );
            // Campi V2 aggiunti al record risultato (additivo, Fase 2a)
            $ris['v2_stelle_totali']   = $valV2['stelle_totali'];
            $ris['v2_stelle_verdi']    = $valV2['stelle_verdi'];
            $ris['v2_stelle_gialle']   = $valV2['stelle_gialle'];
            $ris['v2_stelle_arancio']  = $valV2['stelle_arancio'];
            $ris['v2_stelle_rosse']    = $valV2['stelle_rosse'];
            $ris['v2_malus']           = $valV2['malus'];
            $ris['v2_html']            = $v2Calc->renderHTML($valV2);
            $ris['v2_alert_stellium']  = $valV2['alert_stellium_misto'];
            $ris['v2_delta']           = $valV2['stelle_totali'] - $val['stelline'];

            aggiungiRisultatoTopK(
                $risultati,
                $ris,
                $limiteRisultatiInMemoria
            );
                // ── I. Streaming live dei risultati top ───────────────────────
                // Inviamo subito al frontend i risultati sopra la soglia streaming
                // (default 3 stelle) senza aspettare la fine del loop. Ora su V2.
                if ($valV2['stelle_totali'] >= $streamingMin) {
                    sse('result', $ris);
                }

            } catch (Exception $e) {
                // Aeroporto saltato (latitudine polare, calcolo impossibile, ecc.)
                // Non blocca il loop — i fix DST e swetest -house già prevengono
                // la maggior parte degli errori noti.
            }

            $processed++;

            // Progress ogni 50 aeroporti calcolati
            if ($processed % 50 === 0) {
                sse('progress', [
                    'processed'           => $processed,
                    'totale'              => $totaleCalc,
                    'perc'                => round($processed / $totaleCalc * 100),
                    'fase'                => 'calcolo',
                    'esclusi_radicale'    => $totaleEsclusiRadicale,
                    'esclusi_filtro'      => $totaleEsclusiFiltro,
                    'esclusi_amore'       => $totaleEsclusiAmore,
                    'esclusi_casa'        => $totaleEsclusiCasa,
                    'esclusi_salute'      => $totaleEsclusiSalute,
                    'esclusi_denaro'      => $totaleEsclusiDenaro,
                    'esclusi_denaro_low'  => $totaleEsclusiDenaroLow,
                ]);
            }
        }

        if ($ricercaInterrotta) {
            break;
        }

        $offsetBlocco += count($selezionati);
        unset($selezionati);

        if ($offsetBlocco >= $totaleCalc) {
            break;
        }

        // La modalità località procede per tranche, normalmente da 20.000.
        if ($fineTranche !== null && $offsetBlocco >= $fineTranche) {
            break;
        }

        $dimensioneProssimoBlocco = $dimensioneBlocco;
        if ($fineTranche !== null) {
            $dimensioneProssimoBlocco = min(
                $dimensioneBlocco,
                $fineTranche - $offsetBlocco
            );
        }

        $recuperoBlocco = recuperaAeroportiDeduplicati(
            $pdo,
            $where,
            $params,
            $bucketLat,
            $bucketLon,
            $tipoLocalita,
            $dimensioneProssimoBlocco,
            $offsetBlocco
        );
        $selezionati = $recuperoBlocco['aeroporti'];
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Step 6 — Ordina per stelline decrescenti e invia evento 'done'
    // ─────────────────────────────────────────────────────────────────────
    // Ordinamento primario su V2 (roadmap sostituzione stelline); a parità
    // di V2, il vecchio sistema resta come criterio secondario di stabilita.
    usort($risultati, static fn(array $a, array $b): int =>
        ($b['v2_stelle_totali'] <=> $a['v2_stelle_totali'])
            ?: ($b['stelline'] <=> $a['stelline'])
    );

    if ($tipoLocalita === 'localita') {
        $risultati = arricchisciLocalitaConAeroporti(
            $pdo,
            $risultati
        );
    }

    sse('done', [
        'risultati'             => $risultati,
        'totale_risultati'      => count($risultati),
        'totale_calcolati'      => $totaleCalc,
        'totale_originale'      => $totaleAero,
        'offset_ricerca'         => $tipoLocalita === 'localita' ? $offsetRicerca : 0,
        'limite_ricerca'         => $tipoLocalita === 'localita' ? $limiteRicerca : $totaleCalc,
        'analizzati_fino_a'      => $tipoLocalita === 'localita'
            ? min($offsetBlocco, $totaleCalc)
            : $totaleCalc,
        'ricerca_completata'     => $tipoLocalita !== 'localita'
            || $offsetBlocco >= $totaleCalc,
        'totale_esclusi_radicale' => $totaleEsclusiRadicale,
        'totale_esclusi_filtro'   => $totaleEsclusiFiltro,
        'totale_calcoli_placido' => $totaleCalcoliPlacido,
        'totale_valutazioni_rule_engine' => $totaleValutazioniRuleEngine,
        'profilo_ricerca' => array_merge([
            'riduzione_dedup_percento' => $totaleAero > 0
                ? round((1 - ($totaleCalc / $totaleAero)) * 100, 2)
                : 0,
            'placido_su_dedup_percento' => $totaleCalc > 0
                ? round(($totaleCalcoliPlacido / $totaleCalc) * 100, 2)
                : 0,
            'rule_engine_su_dedup_percento' => $totaleCalc > 0
                ? round(($totaleValutazioniRuleEngine / $totaleCalc) * 100, 2)
                : 0,
        ], SweCalc::getProfilazione()),
        'totale_esclusi_amore'    => $totaleEsclusiAmore,
        'totale_esclusi_casa'     => $totaleEsclusiCasa,
        'totale_esclusi_salute'   => $totaleEsclusiSalute,
        'totale_esclusi_denaro'   => $totaleEsclusiDenaro,
        'totale_esclusi_denaro_low' => $totaleEsclusiDenaroLow,
        'elapsed_ms'            => round((microtime(true) - $tStart) * 1000),
        'rl_gmt'                => $rl['gmt_str'],
        'anno_rs'               => $annoRS,
        'rl_index'              => $rlIndex,
        'condizione'            => $condizione,
        'escludi_radicale_attivo' => $usaEscludiRadicale,
        'mostra_escluse_attivo'   => $mostraEscluse,
    ]);

} catch (Throwable $e) {
    sse('error', [
        'message' => $e->getMessage(),
        'file'    => basename($e->getFile()),
        'line'    => $e->getLine(),
    ]);
}
