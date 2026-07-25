<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/RicercaRSAirportRepository.php';

$pdo = db_connect();
$pdo->exec("SET statement_timeout = '120s'");

$where = [
    'attivo = true',
    "tipo IN ('large_airport','medium_airport','small_airport')",
    'nazione = ?',
];
$params = ['VE'];
$bucketLat = 0.3;
$bucketLon = 0.5;

$completo = recuperaAeroportiDeduplicati(
    $pdo,
    $where,
    $params,
    $bucketLat,
    $bucketLon,
    'localita'
);

$pagina1 = recuperaAeroportiDeduplicati(
    $pdo,
    $where,
    $params,
    $bucketLat,
    $bucketLon,
    'localita',
    50,
    0
);

$pagina2 = recuperaAeroportiDeduplicati(
    $pdo,
    $where,
    $params,
    $bucketLat,
    $bucketLon,
    'localita',
    50,
    50
);

$chiave = static fn(array $r): array => [
    $r['origine_punto'] ?? null,
    $r['icao_code'] ?? null,
    $r['iata_code'] ?? null,
    $r['nome'] ?? null,
    $r['citta'] ?? null,
    $r['nazione'] ?? null,
    (string)$r['latitudine'],
    (string)$r['longitudine'],
];

$attesi = array_map($chiave, array_slice($completo['aeroporti'], 0, 100));
$ottenuti = array_map(
    $chiave,
    array_merge($pagina1['aeroporti'], $pagina2['aeroporti'])
);

if ($attesi !== $ottenuti) {
    fwrite(STDERR, "Paginazione repository non coerente con la sequenza completa\n");
    exit(1);
}

if (
    $pagina1['totale_deduplicato'] !== count($completo['aeroporti'])
    || $pagina2['totale_deduplicato'] !== count($completo['aeroporti'])
) {
    fwrite(STDERR, "Totale deduplicato errato nelle pagine limitate\n");
    exit(1);
}

if (count($pagina1['aeroporti']) > 50 || count($pagina2['aeroporti']) > 50) {
    fwrite(STDERR, "Una pagina supera il limite richiesto\n");
    exit(1);
}

echo "RSM REPOSITORY PAGINATION TEST OK; totale="
    . count($completo['aeroporti'])
    . " pagina1=" . count($pagina1['aeroporti'])
    . " pagina2=" . count($pagina2['aeroporti'])
    . PHP_EOL;
