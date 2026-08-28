<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

session_start();
require_once __DIR__ . '/../includes/Auth.php';

header('Content-Type: application/json; charset=UTF-8');

$pdo = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(
        ['ok' => false, 'errore' => 'Non autenticato.'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(
        ['ok' => false, 'errore' => 'Richiesta non valida.'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$tipo = $data['tipo'] ?? '';
$totale = filter_var(
    $data['totale'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!in_array($tipo, ['rs', 'rilocazioni'], true) || $totale === false) {
    http_response_code(400);
    echo json_encode(
        ['ok' => false, 'errore' => 'Dati del confronto non validi.'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$limite = $auth->getComparatorLimit();

if ($totale > $limite) {
    http_response_code(403);

    $elemento = $tipo === 'rs' ? 'RSM' : 'rilocazioni';
    $messaggio = sprintf(
        'Il piano gratuito consente di confrontare fino a %d risultati.',
        $limite
    );

    if ($limite < 3) {
        $messaggio .= sprintf(
            ' Per confrontare 3 %s è necessario il piano Supporter.',
            $elemento
        );
    }

    echo json_encode([
        'ok' => false,
        'errore' => $messaggio,
        'limite' => $limite,
        'richiesti' => $totale,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'ok' => true,
    'limite' => $limite,
    'richiesti' => $totale,
    'residui' => $limite - $totale,
], JSON_UNESCAPED_UNICODE);
