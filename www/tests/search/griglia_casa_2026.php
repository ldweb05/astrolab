<?php
declare(strict_types=1);

$condizione = 'Casa';
$atteso = 2693;

$url = 'http://localhost/api/ricerca_griglia_api.php?' . http_build_query([
    'g' => 5,
    'm' => 9,
    'a' => 1960,
    'ora_gmt' => 16.783333333333335,
    'lat' => 40.998800,
    'lon' => 14.131700,
    'anno' => 2026,
    'condizione' => $condizione,
    'griglia' => '2deg',
    'modalita' => 'standard',
    'stelline_min' => '0',
    'mostra_escluse' => '1',
]);

$raw = @file_get_contents($url);

if ($raw === false) {
    echo "✗ griglia {$condizione} 2026: impossibile chiamare API\n";
    exit(1);
}

if (!preg_match('/event:\s*done\s+data:\s*(\{.*\})/s', $raw, $m)) {
    echo "✗ griglia {$condizione} 2026: evento done non trovato\n";
    exit(1);
}

$data = json_decode($m[1], true);

if (!is_array($data)) {
    echo "✗ griglia {$condizione} 2026: JSON done non valido\n";
    exit(1);
}

$totale = (int)($data['totale_risultati'] ?? -1);

if ($totale !== $atteso) {
    echo "✗ griglia {$condizione} 2026: attesi {$atteso} risultati, ottenuti {$totale}\n";
    exit(1);
}

echo "✓ griglia {$condizione} 2026: {$atteso} risultati\n";
