<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * api/session_api.php — Stato sessione utente (login + soggetto attivo)
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * Endpoint leggero, distinto da sessioni_api.php (CRUD sessioni RS/RL salvate).
 * Usato da js/app.js per popolare/aggiornare l'header con il soggetto attivo
 * e da index.php per il pulsante "↺ Cambia soggetto".
 *
 * GET  ?action=stato              → utente loggato + soggetto attivo correnti
 * POST {action: 'clear_soggetto'} → rimuove il soggetto attivo dalla sessione
 */

session_start();
require_once '../includes/Auth.php';

header('Content-Type: application/json; charset=UTF-8');

$pdo  = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'errore' => 'Non autenticato.']);
    exit;
}

$action = $_GET['action'] ?? (json_decode(file_get_contents('php://input'), true)['action'] ?? '');

switch ($action) {

    case 'stato':
        echo json_encode([
            'ok'            => true,
            'utente_id'     => $auth->getCurrentUserId(),
            'username'      => $auth->getCurrentUsername(),
            'ruolo'         => $auth->getCurrentRuolo(),
            'soggetto_id'   => $auth->getSoggettoAttivo(),
            'soggetto_nome' => $auth->getSoggettoNome(),
        ]);
        break;

    case 'clear_soggetto':
        $auth->clearSoggettoAttivo();
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['ok' => false, 'errore' => 'Azione non valida.']);
}