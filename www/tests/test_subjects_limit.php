<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$baseUrl = rtrim(
    getenv('ASTRO_VAL_BASE_URL') ?: 'http://127.0.0.1',
    '/'
);

$pdo = db_connect();
$suffix = bin2hex(random_bytes(6));
$username = 'limit_' . $suffix;
$email = $username . '@example.test';
$sessionId = 'astrolimit' . bin2hex(random_bytes(12));
$sessionFile = null;
$userId = null;

try {
    $planStmt = $pdo->prepare(
        "SELECT id FROM piani WHERE code = 'free' LIMIT 1"
    );
    $planStmt->execute();
    $planId = $planStmt->fetchColumn();

    if ($planId === false) {
        throw new RuntimeException('Piano free non trovato');
    }

    $userStmt = $pdo->prepare(
        "INSERT INTO utenti
         (
             username,
             email,
             password_hash,
             ruolo,
             attivo,
             account_status,
             email_verified_at,
             plan_id
         )
         VALUES (?, ?, ?, 'user', TRUE, 'active', NOW(), ?)
         RETURNING id"
    );
    $userStmt->execute([
        $username,
        $email,
        password_hash('Password123!', PASSWORD_DEFAULT),
        (int)$planId,
    ]);
    $userId = (int)$userStmt->fetchColumn();

    $subjectStmt = $pdo->prepare(
        "INSERT INTO soggetti
         (
             codice,
             nome,
             data_nascita,
             ora_nascita,
             ora_nascita_gmt,
             luogo_nascita,
             nazione_nascita,
             latitudine,
             longitudine,
             offset_gmt,
             utente_id
         )
         VALUES (?, ?, '1990-01-01', '12:00:00', '11:00:00',
                 'Roma', 'IT', 41.9028, 12.4964, 1, ?)"
    );

    for ($index = 1; $index <= 2; $index++) {
        $subjectStmt->execute([
            'L' . $suffix . $index,
            'Soggetto limite ' . $index,
            $userId,
        ]);
    }

    session_name('PHPSESSID');
    session_id($sessionId);

    if (!session_start()) {
        throw new RuntimeException('Impossibile creare la sessione di test');
    }

    $_SESSION['utente_id'] = $userId;
    $_SESSION['utente_username'] = $username;
    $_SESSION['utente_ruolo'] = 'user';

    session_write_close();

    $savePath = trim((string)ini_get('session.save_path'));
    $savePath = $savePath !== '' ? $savePath : sys_get_temp_dir();
    $sessionFile = rtrim($savePath, '/') . '/sess_' . $sessionId;

    if (!is_file($sessionFile) || !chmod($sessionFile, 0666)) {
        throw new RuntimeException(
            'Sessione HTTP del test non accessibile ad Apache'
        );
    }

    $payload = json_encode([
        'action' => 'inserisci',
        'codice' => 'L' . $suffix . '3',
        'nome' => 'Soggetto oltre limite',
        'data_nascita' => '1990-01-01',
        'ora_nascita' => '12:00:00',
        'ora_nascita_gmt' => '11:00:00',
        'luogo_nascita' => 'Roma',
        'nazione_nascita' => 'IT',
        'latitudine' => 41.9028,
        'longitudine' => 12.4964,
        'timezone' => 'Europe/Rome',
        'offset_gmt' => 1,
        'note' => '',
        'residenza_luogo' => '',
        'residenza_latitudine' => '',
        'residenza_longitudine' => '',
        'residenza_nazione' => '',
    ], JSON_THROW_ON_ERROR);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'ignore_errors' => true,
            'timeout' => 30,
            'header' => implode("\r\n", [
                'Accept: application/json',
                'Content-Type: application/json',
                'Cookie: PHPSESSID=' . $sessionId,
                'User-Agent: AstroLab-Subjects-Limit-Test',
            ]),
            'content' => $payload,
        ],
    ]);

    $response = file_get_contents(
        $baseUrl . '/api/soggetti_api.php',
        false,
        $context
    );

    if ($response === false) {
        throw new RuntimeException('Risposta HTTP non disponibile');
    }

    $status = null;

    foreach ($http_response_header ?? [] as $header) {
        if (
            preg_match(
                '~^HTTP/\S+\s+([0-9]{3})~i',
                $header,
                $matches
            ) === 1
        ) {
            $status = (int)$matches[1];
        }
    }

    $decoded = json_decode($response, true);

    if ($status !== 400) {
        throw new RuntimeException(
            'Status HTTP inatteso: ' . var_export($status, true)
        );
    }

    if (
        !is_array($decoded)
        || !str_contains(
            (string)($decoded['errore'] ?? ''),
            'numero massimo di soggetti'
        )
    ) {
        throw new RuntimeException(
            'Messaggio JSON del limite non corretto'
        );
    }

    $countStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM soggetti WHERE utente_id = ?"
    );
    $countStmt->execute([$userId]);

    if ((int)$countStmt->fetchColumn() !== 2) {
        throw new RuntimeException(
            'Il terzo soggetto è stato inserito nonostante il limite'
        );
    }

    echo "✓ Limite soggetti piano free: terzo inserimento bloccato\n";
} catch (Throwable $e) {
    fwrite(
        STDERR,
        "✗ Limite soggetti fallito: " . $e->getMessage() . "\n"
    );
    $exitCode = 1;
} finally {
    if ($userId !== null) {
        $deleteSubjects = $pdo->prepare(
            "DELETE FROM soggetti WHERE utente_id = ?"
        );
        $deleteSubjects->execute([$userId]);

        $deleteUser = $pdo->prepare(
            "DELETE FROM utenti WHERE id = ?"
        );
        $deleteUser->execute([$userId]);
    }

    if ($sessionFile !== null && is_file($sessionFile)) {
        unlink($sessionFile);
    }
}

exit($exitCode ?? 0);
