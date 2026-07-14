<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AnnualReportPrintRenderer.php';

$renderer = new AnnualReportPrintRenderer();

$html = $renderer->render([
    'title' => 'Rivoluzione Solare 2027',
    'methodological_note' =>
        'Questa relazione descrive possibilità simboliche.',
    'sections' => [
        [
            'id' => 'executive_summary',
            'title' => 'Sintesi esecutiva',
            'text' => 'L’anno potrebbe concentrarsi sulla realizzazione personale.',
        ],
        [
            'id' => 'conclusion',
            'title' => 'Sintesi conclusiva',
            'text' => 'Le risorse disponibili potrebbero favorire maggiore equilibrio.',
        ],
    ],
]);

foreach ([
    'Rivoluzione Solare 2027',
    'Questa relazione descrive possibilità simboliche.',
    'Sintesi esecutiva',
    'Sintesi conclusiva',
    'page-break-before:always',
] as $expected) {
    if (!str_contains($html, $expected)) {
        fwrite(STDERR, "Contenuto HTML mancante: {$expected}\n");
        exit(1);
    }
}

$escaped = $renderer->render([
    'sections' => [
        [
            'title' => '<script>alert(1)</script>',
            'text' => '<b>testo</b>',
        ],
    ],
]);

if (
    str_contains($escaped, '<script>')
    || str_contains($escaped, '<b>testo</b>')
) {
    fwrite(STDERR, "Escape HTML non valido\n");
    exit(1);
}

if ($renderer->render([]) !== '') {
    fwrite(STDERR, "Report vuoto non valido\n");
    exit(1);
}

echo "ANNUAL REPORT PRINT RENDERER OK\n";
