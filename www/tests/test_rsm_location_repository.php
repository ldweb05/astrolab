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
        'tipo_localita' => '',
    ],
    'v3_solo_aeroporti' => [
        'where' => [
            'attivo = true',
            "tipo IN ('large_airport','medium_airport','small_airport')",
            'nazione = ?',
        ],
        'params' => ['IT'],
        'attende_localita' => false,
        'tipo_localita' => 'solo_aeroporti',
    ],
    'v3_solo_localita' => [
        'where' => [
            'attivo = true',
            "tipo IN ('large_airport','medium_airport','small_airport')",
            'nazione = ?',
        ],
        'params' => ['IT'],
        'attende_localita' => true,
        'tipo_localita' => 'solo_localita',
        'attende_aeroporti' => false,
    ],
];

$normalizza = static fn(array $righe): array => array_map(
    static fn(array $riga): array => [
        $riga['icao_code'] ?? null,
        $riga['iata_code'] ?? null,
        $riga['nome'] ?? null,
        $riga['citta'] ?? null,
        $riga['nazione'] ?? null,
        $riga['tipo'] ?? null,
        $riga['popolazione'] ?? null,
        $riga['aeroporto_associato'] ?? null,
        (string)$riga['latitudine'],
        (string)$riga['longitudine'],
    ],
    $righe
);

foreach ($casi as $nome => $caso) {
    $grezzi = recuperaAeroporti($pdo, $caso['where'], $caso['params']);

    if ($caso['tipo_localita'] === 'solo_aeroporti') {
        $grezzi = array_values(array_filter(
            $grezzi,
            static fn(array $riga): bool =>
                ($riga['icao_code'] ?? null) !== null
                || ($riga['iata_code'] ?? null) !== null
        ));
    } elseif ($caso['tipo_localita'] === 'solo_localita') {
        $grezzi = array_values(array_filter(
            $grezzi,
            static fn(array $riga): bool =>
                ($riga['icao_code'] ?? null) === null
                && ($riga['iata_code'] ?? null) === null
        ));
    }

    $deduplicatiPhp = deduplicaAeroporti($grezzi, $bucketLat, $bucketLon);

    $risultatoSql = recuperaAeroportiDeduplicati(
        $pdo,
        $caso['where'],
        $caso['params'],
        $bucketLat,
        $bucketLon,
        $caso['tipo_localita']
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

    $numeroAeroporti = count($grezzi) - $numeroLocalita;

    $contrattoValido = array_reduce(
        $deduplicatiSql,
        static function (bool $valido, array $riga): bool {
            if (!$valido
                || !array_key_exists('tipo', $riga)
                || !array_key_exists('popolazione', $riga)
                || !array_key_exists('aeroporto_associato', $riga)
            ) {
                return false;
            }

            $isAeroporto =
                ($riga['icao_code'] ?? null) !== null
                || ($riga['iata_code'] ?? null) !== null;

            if ($isAeroporto) {
                return $riga['popolazione'] === null
                    && $riga['aeroporto_associato'] === $riga['nome'];
            }

            return $riga['icao_code'] === null
                && $riga['iata_code'] === null
                && $riga['aeroporto_associato'] === null;
        },
        true
    );

    $valido =
        count($grezzi) === $risultatoSql['totale_originale']
        && $contrattoValido
        && $sequenzaIdentica
        && (($caso['attende_aeroporti'] ?? true) ? true : $numeroAeroporti === 0)
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
        fwrite(STDERR, "Contratto unificato valido: " . ($contrattoValido ? 'SÌ' : 'NO') . "\n");
        exit(1);
    }

    echo "{$nome}: OK\n";
}

echo "RSM LOCATION REPOSITORY TEST OK\n";
