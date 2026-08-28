<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * cambia_password_api.php — Cambio password via AJAX per il modale
 * Impostazioni di dashboard.php. Stessa logica/validazione di
 * cambia_password.php, riusa Auth::cambiaPropriaPassword().
 */
header('Content-Type: application/json');
session_start();

require_once '../includes/Auth.php';

$pdo  = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'errore' => 'Non autenticato.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'errore' => 'Metodo non consentito.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];

$csrf     = (string)($data['csrf_token'] ?? '');
$vecchia  = (string)($data['password_attuale']  ?? '');
$nuova    = (string)($data['nuova_password']    ?? '');
$conferma = (string)($data['conferma_password'] ?? '');

if (empty($_SESSION['dash_settings_csrf']) || !hash_equals((string)$_SESSION['dash_settings_csrf'], $csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'errore' => 'Sessione non valida. Ricarica la pagina e riprova.']);
    exit;
}

if ($nuova !== $conferma) {
    echo json_encode(['ok' => false, 'errore' => 'La nuova password e la conferma non coincidono.']);
    exit;
}

if (strlen($nuova) < 8) {
    echo json_encode(['ok' => false, 'errore' => 'La nuova password deve essere di almeno 8 caratteri.']);
    exit;
}

$result = $auth->cambiaPropriaPassword($vecchia, $nuova);
echo json_encode([
    'ok'      => $result['ok'],
    'errore'  => $result['ok'] ? null : $result['errore'],
]);
