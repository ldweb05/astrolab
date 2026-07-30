<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$baseUrl = rtrim(
    getenv('ASTRO_VAL_BASE_URL') ?: 'http://127.0.0.1',
    '/'
);

$pdo = db_connect();
$suffix = bin2hex(random_bytes(6));
$username = 'comparator_' . $suffix;
$email = $username . '@example.test';
$sessionId = 'astrocompare' . bin2hex(random_bytes(10));
$sessionFile = null;
$userId = null;

function comparatorRequest(
    string $baseUrl,
    string $sessionId,
    string $tipo,
    int $totale
): array {
    $payload = json_encode([
        'tipo' => $tipo,
        'totale' => $totale,
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
                'User-Agent: AstroLab-Comparator-Limit-Test',
            ]),
            'content' => $payload,
        ],
    ]);

    $response = file_get_contents(
        $baseUrl . '/api/comparator_api.php',
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

    return [
        'status' => $status,
        'body' => json_decode($response, true),
    ];
}

function writeComparatorSession(
    string $sessionId,
    int $userId,
    string $username,
    string $piano
): string {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    session_name('PHPSESSID');
    session_id($sessionId);

    if (!session_start()) {
        throw new RuntimeException('Impossibile creare la sessione di test');
    }

    $_SESSION = [
        'utente' => [
            'id' => $userId,
            'username' => $username,
            'email' => $username . '@example.test',
            'ruolo' => 'user',
            'account_status' => 'active',
            'piano' => $piano,
        ],
        'utente_id' => $userId,
        'utente_username' => $username,
        'utente_ruolo' => 'user',
    ];

    session_write_close();

    $savePath = trim((string)ini_get('session.save_path'));
    $savePath = $savePath !== '' ? $savePath : sys_get_temp_dir();

    $sessionFile = rtrim($savePath, '/') . '/sess_' . $sessionId;

    if (!is_file($sessionFile) || !chmod($sessionFile, 0666)) {
        throw new RuntimeException(
            'Sessione HTTP del test non accessibile ad Apache'
        );
    }

    return $sessionFile;
}

try {
    $planStmt = $pdo->prepare(
        "SELECT id FROM piani WHERE code = 'free' LIMIT 1"
    );
    $planStmt->execute();
    $freePlanId = $planStmt->fetchColumn();

    if ($freePlanId === false) {
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
        (int)$freePlanId,
    ]);
    $userId = (int)$userStmt->fetchColumn();

    $sessionFile = writeComparatorSession(
        $sessionId,
        $userId,
        $username,
        'free'
    );

    $freeTwo = comparatorRequest($baseUrl, $sessionId, 'rs', 2);

    if (
        $freeTwo['status'] !== 200
        || ($freeTwo['body']['ok'] ?? false) !== true
        || ($freeTwo['body']['limite'] ?? null) !== 2
        || ($freeTwo['body']['residui'] ?? null) !== 0
    ) {
        throw new RuntimeException(
            'Il piano free non autorizza correttamente 2 risultati'
        );
    }

    foreach (['rs', 'rilocazioni'] as $tipo) {
        $freeThree = comparatorRequest(
            $baseUrl,
            $sessionId,
            $tipo,
            3
        );

        if ($freeThree['status'] !== 403) {
            throw new RuntimeException(
                "Status inatteso per limite free {$tipo}: "
                . var_export($freeThree['status'], true)
            );
        }

        if (
            !is_array($freeThree['body'])
            || ($freeThree['body']['ok'] ?? true) !== false
            || ($freeThree['body']['limite'] ?? null) !== 2
            || !str_contains(
                (string)($freeThree['body']['errore'] ?? ''),
                'piano Supporter'
            )
        ) {
            throw new RuntimeException(
                "Risposta limite free non corretta per {$tipo}"
            );
        }
    }

    $sessionFile = writeComparatorSession(
        $sessionId,
        $userId,
        $username,
        'supporter'
    );

    foreach (['rs', 'rilocazioni'] as $tipo) {
        $supporterThree = comparatorRequest(
            $baseUrl,
            $sessionId,
            $tipo,
            3
        );

        if (
            $supporterThree['status'] !== 200
            || ($supporterThree['body']['ok'] ?? false) !== true
            || ($supporterThree['body']['limite'] ?? null) !== 3
            || ($supporterThree['body']['residui'] ?? null) !== 0
        ) {
            throw new RuntimeException(
                "Il piano Supporter non autorizza 3 risultati {$tipo}"
            );
        }
    }

    echo "✓ Comparator: free massimo 2 e Supporter massimo 3 per RS e rilocazioni\n";
} catch (Throwable $e) {
    fwrite(
        STDERR,
        "✗ Limite Comparator fallito: " . $e->getMessage() . "\n"
    );
    $exitCode = 1;
} finally {
    if ($userId !== null) {
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
