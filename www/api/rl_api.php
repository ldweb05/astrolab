<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * api/rl_api.php — Endpoint JSON per le Rivoluzioni Lunari
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * VERSIONE CORRETTA - Usa SweCalc (libswe) come fonte unica per le RL
 */

date_default_timezone_set('UTC');
ini_set('date.timezone', 'UTC');

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
require_once '../includes/SweCalc.php';
require_once '../includes/RuleEngine.php';
set_time_limit(120);

$action     = $_GET['action']      ?? 'lista';
$soggettoId = intval($_GET['soggetto_id'] ?? 0);
$annoRS     = intval($_GET['anno_rs']     ?? date('Y'));
$rlIndex    = intval($_GET['rl_index']    ?? 0);
$jdDiretto  = isset($_GET['jd']) && is_numeric($_GET['jd'])
              ? floatval($_GET['jd']) : null;
$latRL      = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lonRL      = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
$condizione = $_GET['condizione'] ?? 'Decima';

$condizioniValide = ['Decima','Lavoro','Amore','Salute','Denaro','Denaro Low','Casa'];
if (!in_array($condizione, $condizioniValide)) $condizione = 'Decima';

if ($soggettoId <= 0) {
    echo json_encode(['errore' => 'soggetto_id mancante.']); exit;
}
$soggetto = $auth->verificaSoggetto($soggettoId);
if (!$soggetto) {
    http_response_code(403);
    echo json_encode(['errore' => 'Soggetto non trovato o non autorizzato.']); exit;
}

$dateNascita = new DateTime($soggetto['data_nascita']);
$g = (int)$dateNascita->format('d');
$m = (int)$dateNascita->format('m');
$a = (int)$dateNascita->format('Y');

$oraGmtParts = explode(':', $soggetto['ora_nascita_gmt']);
$oraGmt = (int)$oraGmtParts[0] + ((int)($oraGmtParts[1] ?? 0)) / 60.0;

$latNascita = (float)$soggetto['latitudine'];
$lonNascita = (float)$soggetto['longitudine'];

if ($latRL === null) {
    $latRL = $soggetto['residenza_latitudine']
           ? (float)$soggetto['residenza_latitudine']
           : $latNascita;
}
if ($lonRL === null) {
    $lonRL = $soggetto['residenza_longitudine']
           ? (float)$soggetto['residenza_longitudine']
           : $lonNascita;
}

$swe    = new SweCalc();
$engine = new RuleEngine();

// ════════════════════════════════════════════════════════════════════════════
// Helper per aspetti
// ════════════════════════════════════════════════════════════════════════════

function calcolaAspettiRL(array $pianeti): array {
    $ASPETTI = [
        ['angolo'=>  0,'orbe'=>8,'nome'=>'Congiunzione','tipo'=>'conjunction'],
        ['angolo'=> 60,'orbe'=>6,'nome'=>'Sestile',     'tipo'=>'sextile'   ],
        ['angolo'=> 90,'orbe'=>7,'nome'=>'Quadrato',    'tipo'=>'square'    ],
        ['angolo'=>120,'orbe'=>8,'nome'=>'Trigono',      'tipo'=>'trine'     ],
        ['angolo'=>180,'orbe'=>8,'nome'=>'Opposizione',  'tipo'=>'opposition'],
    ];
    $NOMI = [0=>'SO',1=>'LU',2=>'ME',3=>'VE',4=>'MA',5=>'GI',
             6=>'SA',7=>'UR',8=>'NE',9=>'PLU',11=>'NO'];
    $aspetti = [];
    $ids = array_keys($pianeti);
    for ($i = 0; $i < count($ids); $i++) {
        for ($j = $i + 1; $j < count($ids); $j++) {
            $idA = $ids[$i]; $idB = $ids[$j];
            $d   = fmod(abs($pianeti[$idA]['longitudine'] - $pianeti[$idB]['longitudine']), 360.0);
            $diff = $d > 180.0 ? 360.0 - $d : $d;
            foreach ($ASPETTI as $asp) {
                $sc = abs($diff - $asp['angolo']);
                if ($sc <= $asp['orbe']) {
                    $aspetti[] = [
                        'pianeta_a' => $idA, 'pianeta_b' => $idB,
                        'nome_a'    => $NOMI[$idA]??'?', 'nome_b' => $NOMI[$idB]??'?',
                        'aspetto'   => $asp['nome'], 'tipo' => $asp['tipo'],
                        'angolo_est'=> $asp['angolo'],
                        'diff_reale'=> round($diff,2), 'scarto' => round($sc,2),
                        'label'     => ($NOMI[$idA]??'?').' — '.($NOMI[$idB]??'?').' ('.round($sc,1).'°)',
                    ];
                    break;
                }
            }
        }
    }
    $ord = ['conjunction'=>1,'opposition'=>2,'square'=>3,'trine'=>4,'sextile'=>5];
    usort($aspetti, fn($a,$b) => ($ord[$a['tipo']]??9) <=> ($ord[$b['tipo']]??9));
    return $aspetti;
}

// ════════════════════════════════════════════════════════════════════════════
// Funzione per ottenere la lista delle RL da SweCalc
// ════════════════════════════════════════════════════════════════════════════
function getRLList($g, $m, $a, $oraGmt, $annoRS) {
    $calc = new SweCalc();

    return [
        'rl_list' => $calc->calcolaTutteRLLibsweCompatibileLunaApi(
            (int)$g,
            (int)$m,
            (int)$a,
            (float)$oraGmt,
            (int)$annoRS
        ),
        'natal_moon' => $calc->calcolaPianeti(
            (int)$g,
            (int)$m,
            (int)$a,
            (float)$oraGmt
        )[1]['longitudine'] ?? 0
    ];
}

// ════════════════════════════════════════════════════════════════════════════
// AZIONE: lista
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'lista') {
    try {
        $result = getRLList($g, $m, $a, $oraGmt, $annoRS);
        
        if (!$result) {
            echo json_encode(['ok' => false, 'errore' => 'Errore nel calcolo delle RL']);
            exit;
        }
        
        $rsInizio = $swe->calcolaRS($g, $m, $a, $oraGmt, $annoRS);
        $rsFine = $swe->calcolaRS($g, $m, $a, $oraGmt, $annoRS + 1);
        
        echo json_encode([
            'ok' => true,
            'anno_rs' => $annoRS,
            'rs_gmt' => $rsInizio['stringa'],
            'rs_fine' => $rsFine['stringa'],
            'luna_lon' => $result['natal_moon'],
            'rl_list' => $result['rl_list'],
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['errore' => $e->getMessage()]);
    }
    exit;
}

// ════════════════════════════════════════════════════════════════════════════
// AZIONE: calcola
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'calcola') {
    try {
        // Ottieni la lista delle RL da SweCalc
        $result = getRLList($g, $m, $a, $oraGmt, $annoRS);
        
        if (!$result) {
            echo json_encode(['errore' => 'Errore nel calcolo delle RL']);
            exit;
        }
        
        $rlList = $result['rl_list'];
        
        // Verifica che l'indice esista
        if (!isset($rlList[$rlIndex])) {
            echo json_encode(['errore' => "Indice RL {$rlIndex} non trovato (trovate " . count($rlList) . " RL)."]);
            exit;
        }
        
        $rlData = $rlList[$rlIndex];
        $giornoRL = $rlData['giorno'];
        $meseRL = $rlData['mese'];
        $annoRLeff = $rlData['anno'];
        $oraGmtRL = $rlData['ora_gmt'];
        $rlGmtStr = $rlData['gmt_str'];
        $jdUsato = $rlData['jd'];
        
        // Calcola i temi
        $temaNatale = $swe->calcolaTema($g, $m, $a, $oraGmt, $latNascita, $lonNascita);
        $temaRL = $swe->calcolaTema($giornoRL, $meseRL, $annoRLeff, $oraGmtRL, $latRL, $lonRL);
        $val = $engine->valuta($temaNatale, $temaRL, $condizione);
        $aspetti = calcolaAspettiRL($temaRL['pianeti']);
        
        // Calcola RS per metadati
        $rsInizio = $swe->calcolaRS($g, $m, $a, $oraGmt, $annoRS);
        $rsFine = $swe->calcolaRS($g, $m, $a, $oraGmt, $annoRS + 1);
        
        $risposta = [
            'ok' => true,
            'rl_index' => $rlIndex,
            'rl_gmt' => $rlGmtStr,
            'jd' => $jdUsato,
            'rl_giorno' => $giornoRL,
            'rl_mese' => $meseRL,
            'rl_anno' => $annoRLeff,
            'rl_ora_gmt' => $oraGmtRL,
            'lat' => $latRL,
            'lon' => $lonRL,
            'tema_natale' => $temaNatale,
            'tema_rl' => $temaRL,
            'valutazione' => $val,
            'aspetti' => $aspetti,
            'rl_list' => $rlList,
            'rs_gmt' => $rsInizio['stringa'],
            'rs_fine' => $rsFine['stringa'],
        ];
        
        echo json_encode($risposta);
        
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['errore' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
    exit;
}

echo json_encode(['errore' => 'Azione non valida: ' . htmlspecialchars($action)]);