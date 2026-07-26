<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');
session_start();

require_once __DIR__ . '/../includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autenticato.']);
    exit;
}

$rows = $pdo->query(
    "SELECT
         iso_nazione,
         MIN(nazione) AS nome_nazione
     FROM localita
     WHERE attivo = true
       AND iso_nazione IS NOT NULL
       AND iso_nazione <> ''
     GROUP BY iso_nazione
     ORDER BY LOWER(MIN(nazione)), iso_nazione"
)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);
