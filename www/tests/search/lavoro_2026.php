<?php
declare(strict_types=1);

require_once __DIR__ . '/../search_auth.php';
[$httpContext, $sessionFile] = searchTestHttpContext();

register_shutdown_function(
    static function () use ($sessionFile): void {
        if (is_file($sessionFile)) {
            @unlink($sessionFile);
        }
    }
);

$url = 'http://localhost/api/ricerca_stream_api.php?' . http_build_query([
    'g' => 5,
    'm' => 9,
    'a' => 1960,
    'ora_gmt' => 16.783333333333335,
    'lat' => 40.998800,
    'lon' => 14.131700,
    'anno' => 2026,
    'condizione' => 'Lavoro',
    'tipo_ricerca' => 'large_medium',
    'escludi_militari' => '1',
    'stelline_min' => '0',
    'mostra_escluse' => '1',
]);

$raw = @file_get_contents($url, false, $httpContext);

if ($raw === false) {
    echo "✗ ricerca Lavoro 2026: impossibile chiamare API\n";
    exit(1);
}

if (!preg_match('/event:\s*done\s+data:\s*(\{.*\})/s', $raw, $m)) {
    echo "✗ ricerca Lavoro 2026: evento done non trovato\n";
    exit(1);
}

$data = json_decode($m[1], true);

if (!is_array($data)) {
    echo "✗ ricerca Lavoro 2026: JSON done non valido\n";
    exit(1);
}

$totale = (int)($data['totale_risultati'] ?? -1);

if ($totale !== 2062) {
    echo "✗ ricerca Lavoro 2026: attesi 2062 risultati, ottenuti {$totale}\n";
    exit(1);
}

echo "✓ ricerca Lavoro 2026: 2062 risultati\n";
