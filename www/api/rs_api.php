<?php
require_once __DIR__ . '/../includes/bootstrap.php';
// ===== INIZIO AUTH =====
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autenticato.']);
    exit;
}
// ===== FINE AUTH =====

header('Content-Type: application/json');
require_once '../includes/SweCalc.php';
require_once '../includes/RuleEngine.php';
require_once '../includes/AnnualForecastEngine.php';
require_once '../includes/FiltroEsclusione.php';

$swe    = new SweCalc();
$engine   = new RuleEngine();
$forecast = new AnnualForecastEngine();

$g      = intval($_GET['g']);
$m      = intval($_GET['m']);
$a      = intval($_GET['a']);
$oraGmt = floatval($_GET['ora_gmt']);
$lat    = floatval($_GET['lat']);
$lon    = floatval($_GET['lon']);
$anno   = intval($_GET['anno'] ?? date('Y'));
$latRS  = floatval($_GET['lat_rs'] ?? $lat);
$lonRS  = floatval($_GET['lon_rs'] ?? $lon);
$cond     = $_GET['condizione'] ?? 'Decima';
$modalita = ($_GET['modalita'] ?? 'standard') === 'astri'
    ? 'astri'
    : 'standard';
$luogo    = $_GET['luogo_rs'] ?? '';

$astriInCasaConfronto = [];
if ($modalita === 'astri' && !empty($_GET['astri_in_casa'])) {
    $regoleRicevute = json_decode($_GET['astri_in_casa'], true);

    if (is_array($regoleRicevute)) {
        foreach ($regoleRicevute as $regola) {
            if (!is_array($regola) || !isset($regola['pianeta'], $regola['casa'])) {
                continue;
            }

            $idPianeta = filter_var(
                $regola['pianeta'],
                FILTER_VALIDATE_INT
            );
            $casaRichiesta = filter_var(
                $regola['casa'],
                FILTER_VALIDATE_INT
            );

            if ($idPianeta === false ||
                $casaRichiesta === false ||
                $casaRichiesta < 1 ||
                $casaRichiesta > 12) {
                continue;
            }

            $astriInCasaConfronto[] = [
                'pianeta' => (int)$idPianeta,
                'casa'     => (int)$casaRichiesta,
                'vuole'    => !empty($regola['vuole']),
            ];
        }
    }
}

// Calcola tema natale
$temaNatale = $swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);

// Calcola momento RS
$rs = $swe->calcolaRS($g, $m, $a, $oraGmt, $anno);

// Calcola tema RS per il luogo scelto
try {
    $temaRS = $swe->calcolaTema(
        $rs['giorno'], $rs['mese'], $rs['anno'],
        $rs['ora_gmt'], $latRS, $lonRS
    );
} catch (RuntimeException $e) {
    echo json_encode([
        'errore' => 'Per questa località non è possibile calcolare le case Placido: la latitudine è troppo elevata.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
// Valuta con RuleEngine
$val = $engine->valuta($temaNatale, $temaRS, $cond);
$previsioneAnnuale = $forecast->genera($temaRS, $val);

// Tabella sintetica per Comparator RS: Pianeta | Casa | stato cromatico.
// La classificazione usa direttamente le regole della condizione selezionata.
$nomiPianetiConfronto = [
    0 => 'Sole',
    1 => 'Luna',
    2 => 'Mercurio',
    3 => 'Venere',
    4 => 'Marte',
    5 => 'Giove',
    6 => 'Saturno',
    7 => 'Urano',
    8 => 'Nettuno',
    9 => 'Plutone',
    11 => 'Nodo',
];

$regoleCondizione = RuleEngine::CONDIZIONI[$cond]
    ?? RuleEngine::CONDIZIONI['Decima'];

$tabellaConfronto = [];

foreach ($temaRS['pianeti'] as $idPianeta => $pianeta) {
    $idPianeta = (int)$idPianeta;
    $casa = (int)($pianeta['casa'] ?? 0);
    $stato = 'neutro';

    if (isset($regoleCondizione['bonus'][$idPianeta][$casa])) {
        $stato = 'positivo';
    } elseif (isset($regoleCondizione['penalita'][$idPianeta][$casa])) {
        $stato = 'negativo';
    }

    // Solo in modalità "Astri nelle case", una richiesta positiva rispettata
    // prevale sulla valutazione astrologica ordinaria.
    if ($modalita === 'astri') {
        foreach ($astriInCasaConfronto as $regolaScelta) {
            if ($regolaScelta['vuole'] &&
                $regolaScelta['pianeta'] === $idPianeta &&
                $regolaScelta['casa'] === $casa) {
                $stato = 'positivo';
                break;
            }
        }
    }

    $tabellaConfronto[] = [
        'pianeta' => $nomiPianetiConfronto[$idPianeta] ?? ('Pianeta ' . $idPianeta),
        'casa'     => $casa,
        'stato'    => $stato,
    ];
}

// ── Filtro di esclusione RS (aggiuntivo, non RuleEngine) ─────────────────
// Sole/Marte in I/VI/XII RS, ASC RS in I/VI/XII natale, Saturno in X RS,
// stellium in qualsiasi casa RS. Non modifica la valutazione: la RS viene
// comunque restituita e mostrata, con un avviso lato frontend.
$motiviEsclusioneRS = verificaEsclusioneRS($temaRS['pianeti'], $temaRS['case'], $temaNatale);

// ── Calcolo aspetti tra pianeti della RS ─────────────────────────────────

/**
 * Restituisce la differenza angolare minima tra due longitudini (0–180°).
 */
function diffAngolo(float $a, float $b): float {
    $d = fmod(abs($a - $b), 360.0);
    return $d > 180.0 ? 360.0 - $d : $d;
}

// Definizione aspetti: angolo esatto, orbe, nome, tipo per il frontend
const ASPETTI_DEF = [
    ['angolo' =>   0, 'orbe' => 8, 'nome' => 'Congiunzione', 'tipo' => 'conjunction'],
    ['angolo' =>  60, 'orbe' => 6, 'nome' => 'Sestile',      'tipo' => 'sextile'    ],
    ['angolo' =>  90, 'orbe' => 7, 'nome' => 'Quadrato',     'tipo' => 'square'     ],
    ['angolo' => 120, 'orbe' => 8, 'nome' => 'Trigono',      'tipo' => 'trine'      ],
    ['angolo' => 180, 'orbe' => 8, 'nome' => 'Opposizione',  'tipo' => 'opposition' ],
];

// Nomi brevi pianeti per la stringa visualizzata
const NOMI_BREVI = [
     0 => 'SO',  1 => 'LU',  2 => 'ME',  3 => 'VE',  4 => 'MA',
     5 => 'GI',  6 => 'SA',  7 => 'UR',  8 => 'NE',  9 => 'PLU',
    11 => 'NO',
];

$aspetti = [];
$pianeti = $temaRS['pianeti'];
$ids     = array_keys($pianeti);

for ($i = 0; $i < count($ids); $i++) {
    for ($j = $i + 1; $j < count($ids); $j++) {
        $idA = $ids[$i];
        $idB = $ids[$j];
        $lonA = $pianeti[$idA]['longitudine'];
        $lonB = $pianeti[$idB]['longitudine'];

        $diff = diffAngolo($lonA, $lonB);

        foreach (ASPETTI_DEF as $asp) {
            $scarto = abs($diff - $asp['angolo']);
            if ($scarto <= $asp['orbe']) {
                $aspetti[] = [
                    'pianeta_a'  => $idA,
                    'pianeta_b'  => $idB,
                    'nome_a'     => NOMI_BREVI[$idA] ?? '?',
                    'nome_b'     => NOMI_BREVI[$idB] ?? '?',
                    'aspetto'    => $asp['nome'],
                    'tipo'       => $asp['tipo'],
                    'angolo_est' => $asp['angolo'],
                    'diff_reale' => round($diff, 2),
                    'scarto'     => round($scarto, 2),
                    // stringa compatta tipo "SO △ GI (2.1°)"
                    'label'      => (NOMI_BREVI[$idA] ?? '?') . ' — ' .
                                    (NOMI_BREVI[$idB] ?? '?') .
                                    ' (' . round($scarto, 1) . '°)',
                ];
                break; // un solo aspetto per coppia (il primo che matcha)
            }
        }
    }
}

// Ordine: prima congiunzioni/opposizioni/quadrati (tensione), poi trigoni, poi sestili
$ordineAspetto = [
    'conjunction' => 1,
    'opposition'  => 2,
    'square'      => 3,
    'trine'       => 4,
    'sextile'     => 5,
];
usort($aspetti, fn($a, $b) =>
    ($ordineAspetto[$a['tipo']] ?? 9) <=> ($ordineAspetto[$b['tipo']] ?? 9)
);

echo json_encode([
    'rs_gmt'     => $rs['stringa'],
    'tema_rs'    => $temaRS,
    'valutazione'=> $val,
    'previsione_annuale' => $previsioneAnnuale,
    'relazione_annuale' => $previsioneAnnuale['relazione_annuale'] ?? [],
    'tabella_confronto' => $tabellaConfronto,
    'aspetti'    => $aspetti,
    'escluso_filtro' => $motiviEsclusioneRS,
]);
