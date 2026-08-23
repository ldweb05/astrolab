<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * foto_profilo_api.php — Upload foto profilo per il modale Impostazioni.
 * Riservato al piano Supporter (Auth::hasFeature('foto_profilo')).
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

if (!$auth->hasFeature('foto_profilo')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'errore' => 'Funzionalità riservata al piano Supporter.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'errore' => 'Metodo non consentito.']);
    exit;
}

$csrf = (string)($_POST['csrf_token'] ?? '');
if (empty($_SESSION['dash_settings_csrf']) || !hash_equals((string)$_SESSION['dash_settings_csrf'], $csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'errore' => 'Sessione non valida. Ricarica la pagina e riprova.']);
    exit;
}

if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'errore' => 'Nessun file valido ricevuto.']);
    exit;
}

$file = $_FILES['foto'];

$maxBytes = 2 * 1024 * 1024; // 2MB
if ($file['size'] > $maxBytes) {
    echo json_encode(['ok' => false, 'errore' => 'Il file supera i 2MB consentiti.']);
    exit;
}

$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeReale = $finfo->file($file['tmp_name']);

$estensioniConsentite = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

if (!isset($estensioniConsentite[$mimeReale])) {
    echo json_encode(['ok' => false, 'errore' => 'Formato non supportato. Usa JPG, PNG o WEBP.']);
    exit;
}

$userId = $auth->getCurrentUserId();
$ext    = $estensioniConsentite[$mimeReale];

$dirUpload = __DIR__ . '/../uploads/avatar/';
if (!is_dir($dirUpload)) {
    mkdir($dirUpload, 0775, true);
}

// Rimuove eventuali file precedenti dello stesso utente con altra estensione
foreach (['jpg', 'png', 'webp'] as $vecchiaExt) {
    $vecchioFile = $dirUpload . 'user_' . $userId . '.' . $vecchiaExt;
    if (is_file($vecchioFile)) {
        unlink($vecchioFile);
    }
}

$nomeFile   = 'user_' . $userId . '.' . $ext;
$percorsoFs = $dirUpload . $nomeFile;

if (!move_uploaded_file($file['tmp_name'], $percorsoFs)) {
    echo json_encode(['ok' => false, 'errore' => 'Impossibile salvare il file.']);
    exit;
}

$percorsoRelativo = 'uploads/avatar/' . $nomeFile;

$stmt = $pdo->prepare('UPDATE utenti SET foto_profilo = ? WHERE id = ?');
$stmt->execute([$percorsoRelativo, $userId]);

echo json_encode(['ok' => true, 'url' => $percorsoRelativo . '?v=' . time()]);
