<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/bootstrap.php';

$issues = [];

try {
    $pdo = db_connect();

    if (!$pdo instanceof PDO) {
        throw new RuntimeException(
            'db_connect() non ha restituito una connessione PDO'
        );
    }

    $driver = (string)$pdo->getAttribute(
        PDO::ATTR_DRIVER_NAME
    );

    if ($driver !== 'pgsql') {
        $issues[] = "Driver database inatteso: {$driver}";
    }

    $queryResult = $pdo->query(
        'SELECT 1 AS connection_test'
    );

    $value = $queryResult !== false
        ? $queryResult->fetchColumn()
        : false;

    if ((int)$value !== 1) {
        $issues[] = 'Query di connessione PostgreSQL fallita';
    }

    $versionNumber = (int)$pdo
        ->query("SHOW server_version_num")
        ->fetchColumn();

    if ($versionNumber < 160000) {
        $issues[] = sprintf(
            'PostgreSQL 16 richiesto, versione rilevata: %s',
            (string)$pdo
                ->query("SHOW server_version")
                ->fetchColumn()
        );
    }

    $pdo->beginTransaction();

    $transactionResult = $pdo->query(
        "SELECT txid_current() IS NOT NULL"
    );

    if (
        $transactionResult === false
        || $transactionResult->fetchColumn() !== true
    ) {
        $issues[] =
            'Verifica transazione PostgreSQL fallita';
    }

    $pdo->rollBack();

    if ($pdo->inTransaction()) {
        $issues[] =
            'Rollback della transazione non completato';
    }
} catch (Throwable $exception) {
    $issues[] = 'Connessione PostgreSQL fallita: '
        .$exception->getMessage();
}

if ($issues !== []) {
    fwrite(
        STDERR,
        "DATABASE ENVIRONMENT FAILED
"
        .implode("
", $issues)
        ."
"
    );
    exit(1);
}

echo sprintf(
    "DATABASE ENVIRONMENT OK: PostgreSQL %s
",
    (string)$pdo
        ->query("SHOW server_version")
        ->fetchColumn()
);
