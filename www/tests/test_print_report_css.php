<?php
declare(strict_types=1);

$path = __DIR__.'/../css/print.css';
$css = file_get_contents($path);

if ($css === false) {
    fwrite(STDERR, "Impossibile leggere print.css\n");
    exit(1);
}

foreach ([
    '.report-section {',
    'page-break-inside: auto;',
    '.report-section-title {',
    'page-break-after: avoid;',
    '.report-section-title + * {',
    'page-break-before: avoid;',
    'orphans: 3;',
    'widows: 3;',
] as $expected) {
    if (!str_contains($css, $expected)) {
        fwrite(STDERR, "Regola print mancante: {$expected}\n");
        exit(1);
    }
}

echo "PRINT REPORT CSS OK\n";
