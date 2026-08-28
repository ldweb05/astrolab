<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/AnnualReportExportQuota.php';

$pdo = db_connect();
$suffix = bin2hex(random_bytes(6));
$userId = null;

try {
    $planStmt = $pdo->query(
        "SELECT id FROM piani WHERE code = 'free' LIMIT 1"
    );
    $planId = $planStmt->fetchColumn();

    if ($planId === false) {
        throw new RuntimeException('Piano free non trovato.');
    }

    $userStmt = $pdo->prepare(
        "INSERT INTO utenti (
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
        'report_quota_' . $suffix,
        'report_quota_' . $suffix . '@example.test',
        password_hash('Password123!', PASSWORD_DEFAULT),
        (int)$planId,
    ]);
    $userId = (int)$userStmt->fetchColumn();

    $quota = new AnnualReportExportQuota($pdo);

    $initial = $quota->getStatus($userId);

    if (
        $initial['allowed'] !== true
        || $initial['limit'] !== 3
        || $initial['count'] !== 0
        || $initial['remaining'] !== 3
    ) {
        throw new RuntimeException('Stato iniziale della quota non corretto.');
    }

    for ($index = 1; $index <= 3; $index++) {
        $result = $quota->consume(
            $userId,
            null,
            'pdf',
            'quota-test-' . $index
        );

        if (
            $result['allowed'] !== ($index < 3)
            || $result['consumed'] !== true
            || $result['duplicate'] !== false
            || $result['count'] !== $index
            || $result['remaining'] !== 3 - $index
        ) {
            throw new RuntimeException(
                "Risultato inatteso per il consumo {$index}."
            );
        }
    }

    $duplicate = $quota->consume(
        $userId,
        null,
        'pdf',
        'quota-test-3'
    );

    if (
        $duplicate['consumed'] !== false
        || $duplicate['duplicate'] !== true
        || $duplicate['count'] !== 3
    ) {
        throw new RuntimeException('Idempotenza non rispettata.');
    }

    $blocked = $quota->consume(
        $userId,
        null,
        'browser_print',
        'quota-test-4'
    );

    if (
        $blocked['allowed'] !== false
        || $blocked['consumed'] !== false
        || $blocked['duplicate'] !== false
        || $blocked['count'] !== 3
        || $blocked['remaining'] !== 0
    ) {
        throw new RuntimeException('Quarto utilizzo non bloccato.');
    }

    echo "ANNUAL REPORT EXPORT QUOTA OK\n";
} catch (Throwable $e) {
    fwrite(
        STDERR,
        "ANNUAL REPORT EXPORT QUOTA FAILED: {$e->getMessage()}\n"
    );
    $exitCode = 1;
} finally {
    if ($userId !== null) {
        $deleteUser = $pdo->prepare(
            "DELETE FROM utenti WHERE id = ?"
        );
        $deleteUser->execute([$userId]);
    }
}

exit($exitCode ?? 0);
