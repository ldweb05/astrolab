<?php
declare(strict_types=1);

$url = 'http://localhost/api/ricerca_stream_api.php?' . http_build_query([
    'g' => 5,
    'm' => 9,
    'a' => 1960,
    'ora_gmt' => 16.783333333333335,
    'lat' => 40.998800,
    'lon' => 14.131700,
    'anno' => 2026,
    'condizione' => 'Amore',
    'tipo_ricerca' => 'large_medium',
    'escludi_militari' => '1',
    'stelline_min' => '0',
    'mostra_escluse' => '1',
]);

$raw = @file_get_contents($url);

if ($raw === false) {
    echo "✗ ricerca Amore 2026: impossibile chiamare API\n";
    exit(1);
}

if (!preg_match('/event:\s*done\s+data:\s*(\{.*\})/s', $raw, $m)) {
    echo "✗ ricerca Amore 2026: evento done non trovato\n";
    exit(1);
}

$data = json_decode($m[1], true);

if (!is_array($data)) {
    echo "✗ ricerca Amore 2026: JSON done non valido\n";
    exit(1);
}

$totale = (int)($data['totale_risultati'] ?? -1);

if ($totale !== 1344) {
    echo "✗ ricerca Amore 2026: attesi 1344 risultati, ottenuti {$totale}\n";
    exit(1);
}

echo "✓ ricerca Amore 2026: 1344 risultati\n";
