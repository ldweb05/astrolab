<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/RicercaRSAirportRepository.php';
require __DIR__ . '/../includes/RicercaRSDeduplicator.php';

$pdo = db_connect();
$pdo->exec("SET statement_timeout = '120s'");

$bucketLat = 0.3;
$bucketLon = 0.5;

$casi = [
    'legacy_solo_aeroporti' => [
        'where' => [
            'attivo = true',
            "tipo IN ('large_airport','medium_airport','small_airport')",
        ],
        'params' => [],
        'attende_localita' => false,
    ],
    'v3_localita_multinazione' => [
        'where' => [
            'attivo = true',
            "tipo IN ('large_airport','medium_airport','small_airport')",
            'nazione IN (?,?)',
            'longitudine >= ?',
            'longitudine <= ?',
        ],
        'params' => ['IT', 'FR', -6.0, 19.0],
        'attende_localita' => true,
    ],
];

$normalizza = static fn(array $righe): array => array_map(
    static fn(array $riga): array => [
        $riga['icao_code'] ?? null,
        $riga['iata_code'] ?? null,
        $riga['nome'] ?? null,
        $riga['citta'] ?? null,
        $riga['nazione'] ?? null,
        (string)$riga['latitudine'],
        (string)$riga['longitudine'],
    ],
    $righe
);

foreach ($casi as $nome => $caso) {
    $grezzi = recuperaAeroporti($pdo, $caso['where'], $caso['params']);
    $deduplicatiPhp = deduplicaAeroporti($grezzi, $bucketLat, $bucketLon);

    $risultatoSql = recuperaAeroportiDeduplicati(
        $pdo,
        $caso['where'],
        $caso['params'],
        $bucketLat,
        $bucketLon
    );

    $deduplicatiSql = $risultatoSql['aeroporti'];

    $numeroLocalita = count(array_filter(
        $grezzi,
        static fn(array $riga): bool =>
            ($riga['icao_code'] ?? null) === null
            && ($riga['iata_code'] ?? null) === null
    ));

    $sequenzaIdentica =
        $normalizza($deduplicatiPhp) === $normalizza($deduplicatiSql);

    $valido =
        count($grezzi) === $risultatoSql['totale_originale']
        && $sequenzaIdentica
        && (
            $caso['attende_localita']
                ? $numeroLocalita > 0
                : $numeroLocalita === 0
        );

    if (!$valido) {
        fwrite(STDERR, "TEST FALLITO: {$nome}\n");
        fwrite(STDERR, "Punti grezzi: " . count($grezzi) . "\n");
        fwrite(STDERR, "Totale SQL: {$risultatoSql['totale_originale']}\n");
        fwrite(STDERR, "Località: {$numeroLocalita}\n");
        fwrite(STDERR, "Deduplicati PHP: " . count($deduplicatiPhp) . "\n");
        fwrite(STDERR, "Deduplicati SQL: " . count($deduplicatiSql) . "\n");
        fwrite(STDERR, "Sequenza identica: " . ($sequenzaIdentica ? 'SÌ' : 'NO') . "\n");
        exit(1);
    }

    echo "{$nome}: OK\n";
}

echo "RSM LOCATION REPOSITORY TEST OK\n";
