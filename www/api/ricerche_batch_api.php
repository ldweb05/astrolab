<?php
/**
 * api/ricerche_batch_api.php — Endpoint JSON per la pagina "Ricerche" (UX-0010)
 *
 * Restituisce, per un soggetto e un anno dati, la RSM dell'anno e l'elenco
 * completo delle RL dell'anno (data/ora gia' calcolate), da usare per
 * popolare il dropdown "RS/RL dell'anno" in ricerche.php.
 *
 * Riusa esclusivamente funzioni gia' esistenti in SweCalc.php
 * (calcolaRS, calcolaTutteRLLibsweCompatibileLunaApi): nessuna modifica
 * a SweCalc.php, rs_api.php o rl_api.php.
 */

require_once __DIR__ . '/../includes/NascitaGmtHelper.php';
require_once __DIR__ . '/../includes/bootstrap.php';

date_default_timezone_set('UTC');
ini_set('date.timezone', 'UTC');

session_start();
require_once __DIR__ . '/../includes/Auth.php';

$pdo  = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['errore' => 'Non autenticato.']);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../includes/SweCalc.php';
set_time_limit(120);

$soggettoId = intval($_GET['soggetto_id'] ?? 0);
$anno       = intval($_GET['anno'] ?? date('Y'));

if ($soggettoId <= 0) {
    echo json_encode(['ok' => false, 'errore' => 'soggetto_id mancante.']);
    exit;
}

$soggetto = $auth->verificaSoggetto($soggettoId);
if (!$soggetto) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'errore' => 'Soggetto non trovato o non autorizzato.']);
    exit;
}

// Calcolo corretto data/ora GMT gestendo il cambio di giorno (stesso pattern di rl_api.php)
$gmtData = calcolaDataOraGmtCorretta(
    $soggetto['data_nascita'],
    $soggetto['ora_nascita'],
    (float)$soggetto['offset_gmt']
);

$dateGmt = new DateTime($gmtData['data_gmt'] . ' ' . $gmtData['ora_gmt']);
$g = (int)$dateGmt->format('d');
$m = (int)$dateGmt->format('m');
$a = (int)$dateGmt->format('Y');

$oraGmtParts = explode(':', $gmtData['ora_gmt']);
$oraGmt = (int)$oraGmtParts[0] + ((int)($oraGmtParts[1] ?? 0)) / 60.0;

$swe = new SweCalc();

try {
    // RSM dell'anno (1 sola bisezione, riuso diretto di calcolaRS)
    $rs = $swe->calcolaRS($g, $m, $a, $oraGmt, $anno);

    // RL dell'anno (scan gia' esistente, riuso diretto, nessuna modifica)
    $rlList = $swe->calcolaTutteRLLibsweCompatibileLunaApi($g, $m, $a, $oraGmt, $anno);

    echo json_encode([
        'ok'      => true,
        'anno'    => $anno,
        'rsm'     => [
            'giorno'  => $rs['giorno'],
            'mese'    => $rs['mese'],
            'anno'    => $rs['anno'],
            'ora_gmt' => $rs['ora_gmt'],
            'stringa' => $rs['stringa'],
        ],
        'rl_list' => $rlList,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'errore' => $e->getMessage()]);
}
