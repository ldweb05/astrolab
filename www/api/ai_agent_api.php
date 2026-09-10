<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * api/ai_agent_api.php — Primo endpoint AI Agent (Fase 2, Provider 1: Gemini)
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * Endpoint minimale, solo prompt testuale -> risposta testuale. Nessun tool
 * calling (fuori scope, vedi docs/roadmaps/ROADMAP_AI_PRINCIPI.md, sezione 18).
 * Accesso riservato ad amministratori in questa prima fase.
 *
 * GET/POST {action: 'chiedi', prompt: string} -> risposta del provider AI
 */

session_start();
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/ai/AiProviderInterface.php';
require_once __DIR__ . '/../includes/ai/GeminiProvider.php';

header('Content-Type: application/json; charset=UTF-8');

$pdo  = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'errore' => 'Non autenticato.']);
    exit;
}

if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'errore' => 'Accesso riservato agli amministratori.']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? ($input['action'] ?? '');

switch ($action) {

    case 'chiedi':
        if (!AI_AGENT_ENABLED) {
            echo json_encode(['ok' => false, 'errore' => 'AI Agent non abilitato (AI_AGENT_ENABLED=false).']);
            break;
        }

        $prompt = trim($_GET['prompt'] ?? ($input['prompt'] ?? ''));

        if ($prompt === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'errore' => 'Parametro "prompt" mancante o vuoto.']);
            break;
        }

        $provider  = new GeminiProvider();
        $risultato = $provider->chiedi($prompt);

        echo json_encode($risultato);
        break;

    default:
        echo json_encode(['ok' => false, 'errore' => 'Azione non valida.']);
}
