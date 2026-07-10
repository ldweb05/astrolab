<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * api/sensibilita_api.php — Analisi Sensibilità Oraria RS
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * Risponde alla domanda fondamentale di Discepolo:
 *   "La RS è robusta rispetto all'incertezza dell'ora di nascita?"
 *
 * Per ogni δ-minuto nell'intervallo [-60, -45, -30, -15, 0, +15, +30, +45, +60]
 * calcola la RS e restituisce: ora GMT, ASC RS (stringa + longitudine),
 * casa natale dell'ASC RS, stelline, stringa VAL, veti attivi.
 *
 * La RS è considerata "robusta" se stelline e casa natale dell'ASC RS
 * rimangono invariati su tutta la finestra di variazione.
 *
 * GET params (identici a rs_api.php):
 *   g, m, a, ora_gmt   → dati natali (ora_gmt in decimale)
 *   lat, lon            → coordinate nascita
 *   anno                → anno RS
 *   lat_rs, lon_rs      → coordinate luogo RS
 *   condizione          → condizione tematica
 *   delta_step          → passo in minuti (default: 15, min: 5, max: 30)
 *   delta_range         → ampiezza variazione in minuti (default: 60, min: 15, max: 120)
 */

// ── Auth ──────────────────────────────────────────────────────────────────
session_start();
require_once '../includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autenticato.']);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');
set_time_limit(120);

require_once '../includes/SweCalc.php';
require_once '../includes/RuleEngine.php';

// ── Parametri ─────────────────────────────────────────────────────────────
$g      = intval($_GET['g']        ?? 1);
$m      = intval($_GET['m']        ?? 1);
$a      = intval($_GET['a']        ?? 1990);
$oraGmt = floatval($_GET['ora_gmt'] ?? 12.0);
$lat    = floatval($_GET['lat']    ?? 41.9);
$lon    = floatval($_GET['lon']    ?? 12.48);
$anno   = intval($_GET['anno']     ?? date('Y'));
$latRS  = floatval($_GET['lat_rs'] ?? $lat);
$lonRS  = floatval($_GET['lon_rs'] ?? $lon);
$cond   = $_GET['condizione']      ?? 'Decima';

// Passo e ampiezza: sanitizzati per evitare DoS (troppe chiamate swetest)
$deltaStep  = max(5,  min(30,  intval($_GET['delta_step']  ?? 15)));
$deltaRange = max(15, min(120, intval($_GET['delta_range'] ?? 60)));

$condizioniValide = ['Decima','Lavoro','Amore','Salute','Denaro','Denaro Low','Casa'];
if (!in_array($cond, $condizioniValide)) $cond = 'Decima';

// ── Calcolo ───────────────────────────────────────────────────────────────
try {
    $swe    = new SweCalc();
    $engine = new RuleEngine();

    // Tema natale dell'ora di nascita "ufficiale" — serve per trovaCasaNatale
    // Lo calcoliamo una sola volta perché le case natali si spostano con δ,
    // ma NON vogliamo questo: vogliamo confrontare sempre rispetto al tema
    // registrato (l'ora scritta sull'atto di nascita).
    // Il RuleEngine internamente usa già temaNatale.case per trovaCasaNatale,
    // quindi lo passiamo sempre invariato.
    $temaNataleBase = $swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);

    // Costruiamo l'array dei delta da testare
    $deltas = [];
    for ($d = -$deltaRange; $d <= $deltaRange; $d += $deltaStep) {
        $deltas[] = $d;
    }
    // Assicuriamo che δ=0 sia sempre presente
    if (!in_array(0, $deltas)) {
        $deltas[] = 0;
        sort($deltas);
    }

    $punti = [];

    foreach ($deltas as $deltaMin) {
        // Ora GMT modificata (in decimale)
        $oraGmtVariata = $oraGmt + $deltaMin / 60.0;

        // Gestione overflow giornaliero (es. nascita 23:45 + 30min = 00:15 del giorno dopo)
        // Per la bisezione della RS conta solo la longitudine solare natale,
        // non la data; un overflow di ±60min non cambia il segno zodiacale del Sole.
        // Normalizziamo in [0, 24).
        $oraGmtVariata = fmod($oraGmtVariata + 48.0, 24.0);

        // Calcola RS con questa ora variata
        $rs = $swe->calcolaRS($g, $m, $a, $oraGmtVariata, $anno);

        // Calcola tema RS per il luogo scelto
        $temaRS = $swe->calcolaTema(
            $rs['giorno'], $rs['mese'], $rs['anno'],
            $rs['ora_gmt'], $latRS, $lonRS
        );

        // Valuta con RuleEngine, sempre rispetto al tema natale BASE
        // (non vogliamo che cambino le case natali di riferimento con δ)
        $val = $engine->valuta($temaNataleBase, $temaRS, $cond);

        // ASC RS: longitudine e stringa formattata
        $ascLon    = $temaRS['case']['ASC']['longitudine'] ?? 0.0;
        $ascStr    = $temaRS['case']['ASC']['posizione']['stringa'] ?? '—';
        $mcStr     = $temaRS['case']['MC']['posizione']['stringa']  ?? '—';

        // Casa natale dell'ASC RS (la regola più importante: I/VI/XII = veto)
        // Usiamo le case natali BASE (invariate)
        $casaNataleAsc = _trovaCasaNatale($ascLon, $temaNataleBase['case']);

        // Variazione ora locale (per visualizzazione)
        $segno   = $deltaMin >= 0 ? '+' : '';
        $oraBase = $oraGmt;  // ora GMT originale in decimale
        $h       = intval($oraBase);
        $mBase   = intval(round(($oraBase - $h) * 60));
        // Ora locale corrente = ora GMT + offset non ci interessa qui,
        // mostriamo direttamente il δ come etichetta.

        $punti[] = [
            'delta_min'       => $deltaMin,
            'delta_label'     => $segno . $deltaMin . '′',
            'ora_gmt_variata' => round($oraGmtVariata, 4),
            'rs_gmt'          => $rs['stringa'],
            'asc_rs_lon'      => round($ascLon, 4),
            'asc_rs_str'      => $ascStr,
            'mc_rs_str'       => $mcStr,
            'casa_natale_asc' => $casaNataleAsc,
            'stelline'        => $val['stelline'],
            'stelle_str'      => $val['stelle_str'],
            'val'             => $val['val'],
            'veti'            => $val['veti'],
            'is_valida'       => $val['is_valida'],
            'is_punto_base'   => ($deltaMin === 0),
        ];
    }

    // ── Calcola indice di stabilità ───────────────────────────────────────
    // Conta quante variazioni mantengono: stesse stelline E stessa casa natale ASC
    $puntoBase  = null;
    foreach ($punti as $p) {
        if ($p['is_punto_base']) { $puntoBase = $p; break; }
    }

    $nStabili      = 0;
    $nTotale       = count($punti);
    $stelleCambiano = false;
    $casaCambia    = false;
    $vetoCompare   = false;  // un veto compare che a δ=0 non c'era
    $vetoScompare  = false;  // un veto scompare che a δ=0 c'era

    if ($puntoBase) {
        foreach ($punti as $p) {
            $stesseStelle = ($p['stelline'] === $puntoBase['stelline']);
            $stessaCasa   = ($p['casa_natale_asc'] === $puntoBase['casa_natale_asc']);
            if ($stesseStelle && $stessaCasa) $nStabili++;
            if (!$stesseStelle)      $stelleCambiano = true;
            if (!$stessaCasa)        $casaCambia     = true;
            if (!$p['is_punto_base']) {
                if (!$puntoBase['is_valida'] && $p['is_valida'])  $vetoScompare = true;
                if ($puntoBase['is_valida']  && !$p['is_valida']) $vetoCompare  = true;
            }
        }
    }

    // Livello di robustezza: alto / medio / basso / critico
    $percStabile = $nTotale > 0 ? round($nStabili / $nTotale * 100) : 0;
    $robustezza  = match(true) {
        $percStabile === 100                   => 'alta',
        $percStabile >= 70 && !$vetoCompare    => 'media',
        $vetoCompare || $percStabile < 50      => 'critica',
        default                                => 'bassa',
    };

    // Messaggio sintetico
    $messaggi = [
        'alta'    => 'La RS è robusta: stelline e casa dell\'ASC rimangono stabili su tutta la finestra di variazione oraria.',
        'media'   => 'La RS è mediamente robusta: qualche variazione alle estremità, ma il nucleo rimane stabile.',
        'bassa'   => 'Attenzione: stelline o casa dell\'ASC variano significativamente con l\'incertezza oraria.',
        'critica' => '⚠️ RS instabile: con una diversa ora di nascita potrebbero comparire veti o il numero di stelle potrebbe cambiare drasticamente. Verificare con cautela.',
    ];

    echo json_encode([
        'ok'           => true,
        'condizione'   => $cond,
        'delta_step'   => $deltaStep,
        'delta_range'  => $deltaRange,
        'n_punti'      => $nTotale,
        'robustezza'   => $robustezza,
        'perc_stabile' => $percStabile,
        'stelle_cambiano' => $stelleCambiano,
        'casa_cambia'  => $casaCambia,
        'veto_compare' => $vetoCompare,
        'veto_scompare'=> $vetoScompare,
        'messaggio'    => $messaggi[$robustezza],
        'punti'        => $punti,
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'     => false,
        'errore' => $e->getMessage(),
    ]);
}

// ── Helper: trova casa natale per una longitudine ─────────────────────────
// (duplicato locale per evitare dipendenza da un metodo privato di RuleEngine)
function _trovaCasaNatale(float $lon, array $caseNatale): int {
    $lon = fmod($lon + 360, 360);
    for ($c = 1; $c <= 12; $c++) {
        if (!isset($caseNatale[$c])) continue;
        $ini  = fmod($caseNatale[$c]['longitudine'] + 360, 360);
        $fine = fmod($caseNatale[($c % 12) + 1]['longitudine'] + 360, 360);
        if ($ini <= $fine) { if ($lon >= $ini && $lon < $fine) return $c; }
        else               { if ($lon >= $ini || $lon < $fine) return $c; }
    }
    return 1;
}
