<?php
require_once __DIR__ . '/../includes/bootstrap.php';
// ===== INIZIO AUTH =====
session_start();
require_once '../includes/Auth.php';

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

$swe  = new SweCalc();
$tipo = $_GET['tipo'] ?? 'natale';

$g      = intval($_GET['g']       ?? 5);
$m      = intval($_GET['m']       ?? 9);
$a      = intval($_GET['a']       ?? 1960);
$oraGmt = floatval($_GET['ora_gmt'] ?? 16.783);
$lat    = floatval($_GET['lat']   ?? 41.0167);
$lon    = floatval($_GET['lon']   ?? 14.1333);

if ($tipo === 'natale') {
    $tema = $swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);
} elseif ($tipo === 'transito') {
    $tema = $swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);
    $oreInt = (int)floor($oraGmt);
    $minInt = (int)round(($oraGmt - $oreInt) * 60);
    $tema['transito_gmt'] = sprintf('%02d/%02d/%04d %02d:%02d:00', $g, $m, $a, $oreInt, $minInt);
} else {
    $annoRS = intval($_GET['anno'] ?? date('Y'));
    $latRS  = floatval($_GET['lat_rs'] ?? $lat);
    $lonRS  = floatval($_GET['lon_rs'] ?? $lon);
    $rs     = $swe->calcolaRS($g, $m, $a, $oraGmt, $annoRS);
    $tema   = $swe->calcolaTema(
        $rs['giorno'], $rs['mese'], $rs['anno'],
        $rs['ora_gmt'], $latRS, $lonRS
    );
    $tema['rs_gmt'] = $rs['stringa'];
}

echo json_encode($tema);
