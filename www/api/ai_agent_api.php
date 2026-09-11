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
require_once __DIR__ . '/../includes/ai/RicercaRsmTool.php';

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

        // Tool calling: ricerca RSM per condizione (Fase 7). Il soggetto e sempre
        // quello attivo di sessione, mai un parametro che Gemini puo scegliere
        // (Sezione 23 - nessun dato anagrafico esposto al modello).
        $dichiarazioneCercaRsm = [
            'name' => 'cerca_rsm',
            'description' => 'Cerca la Rivoluzione Solare Mondiale (RSM) del soggetto attivo per un anno e una condizione tematica specifici, tra gli aeroporti e localita del mondo. Restituisce i luoghi migliori trovati dal motore ASTROLAB.',
            'parameters' => [
                'type' => 'OBJECT',
                'properties' => [
                    'anno' => [
                        'type' => 'INTEGER',
                        'description' => 'Anno della Rivoluzione Solare da cercare, es. 2027.',
                    ],
                    'condizione' => [
                        'type' => 'STRING',
                        'enum' => ['Decima', 'Lavoro', 'Amore', 'Salute', 'Denaro', 'Denaro Low', 'Casa'],
                        'description' => 'Tema di vita su cui orientare la ricerca. Decima riguarda il successo e la visibilita generale.',
                    ],
                ],
                'required' => ['anno', 'condizione'],
            ],
        ];

        $provider = new GeminiProvider();
        $primoTurno = $provider->chiediConFunzione($prompt, $dichiarazioneCercaRsm);

        if ($primoTurno['tipo'] === 'errore') {
            echo json_encode(['ok' => false, 'testo' => null, 'errore' => $primoTurno['errore']]);
            break;
        }

        if ($primoTurno['tipo'] === 'testo') {
            echo json_encode(['ok' => true, 'testo' => $primoTurno['testo'], 'errore' => null]);
            break;
        }

        // tipo === 'function_call'
        $soggettoId = $auth->getSoggettoAttivo();
        if (!$soggettoId) {
            echo json_encode([
                'ok' => true,
                'testo' => 'Per cercare una RSM devi prima selezionare un soggetto attivo in ASTROLAB.',
                'errore' => null,
            ]);
            break;
        }

        $tool = new RicercaRsmTool($pdo);
        $risultatoTool = $tool->cerca(
            $soggettoId,
            (int) ($primoTurno['argomenti']['anno'] ?? (int) date('Y')),
            (string) ($primoTurno['argomenti']['condizione'] ?? 'Decima')
        );

        $secondoTurno = $provider->rispondiConRisultatoFunzione(
            $prompt,
            $primoTurno['model_content'],
            $primoTurno['nome'],
            $risultatoTool,
            $dichiarazioneCercaRsm
        );

        echo json_encode($secondoTurno);
        break;

    default:
        echo json_encode(['ok' => false, 'errore' => 'Azione non valida.']);
}
