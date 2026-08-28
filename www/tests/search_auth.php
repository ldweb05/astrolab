<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

function searchTestHttpContext(): array
{
    $pdo = db_connect();

    $subject = $pdo->query(
        "
        SELECT
            u.id AS utente_id,
            u.username,
            u.ruolo,
            p.name AS piano,
            s.id AS soggetto_id,
            s.nome AS soggetto_nome
        FROM utenti u
        LEFT JOIN piani p ON p.id = u.plan_id
        INNER JOIN soggetti s ON s.utente_id = u.id
        WHERE COALESCE(u.attivo, true) = true
        ORDER BY u.id, s.id
        LIMIT 1
        "
    )->fetch(PDO::FETCH_ASSOC);

    if (!is_array($subject)) {
        throw new RuntimeException(
            'Nessun utente attivo con soggetto disponibile'
        );
    }

    $sessionId = 'astrosearch' . bin2hex(random_bytes(12));

    session_name('PHPSESSID');
    session_id($sessionId);

    if (!session_start()) {
        throw new RuntimeException(
            'Impossibile creare la sessione del test ricerca'
        );
    }

    $_SESSION['utente'] = [
        'id'             => (int)$subject['utente_id'],
        'username'       => (string)$subject['username'],
        'ruolo'          => (string)$subject['ruolo'],
        'piano'          => strtolower((string)$subject['piano']),
    ];

    $_SESSION['utente_id'] = (int)$subject['utente_id'];
    $_SESSION['utente_username'] = (string)$subject['username'];
    $_SESSION['utente_ruolo'] = (string)$subject['ruolo'];
    $_SESSION['soggetto_id'] = (int)$subject['soggetto_id'];
    $_SESSION['soggetto_nome'] = (string)$subject['soggetto_nome'];

    session_write_close();

    $savePath = trim((string)ini_get('session.save_path'));
    $savePath = $savePath !== '' ? $savePath : sys_get_temp_dir();
    $sessionFile = rtrim($savePath, '/') . '/sess_' . $sessionId;

    if (!is_file($sessionFile) || !chmod($sessionFile, 0666)) {
        throw new RuntimeException(
            'Sessione HTTP del test non accessibile ad Apache'
        );
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
            'timeout' => 600,
            'header' => implode("\r\n", [
                'Accept: text/event-stream',
                'Cookie: PHPSESSID=' . $sessionId,
                'User-Agent: AstroLab-Search-Test',
            ]),
        ],
    ]);

    return [$context, $sessionFile];
}
