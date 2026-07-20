<?php
declare(strict_types=1);

$stampaPath = __DIR__.'/../stampa.php';
$cssPath = __DIR__.'/../css/print.css';

$stampa = file_get_contents($stampaPath);
$css = file_get_contents($cssPath);

if ($stampa === false || $css === false) {
    fwrite(STDERR, "Impossibile leggere i file di stampa\n");
    exit(1);
}

foreach ([
    'function escapeReportHtml(value)',
    'function reportParagraphs(text)',
    'function buildAnnualReportHTML(report)',
    '_temaRSCache?.relazione_annuale',
    'buildAnnualReportHTML(',
    'annual-report-note',
    'annual-report-section-title',
    'annual-report-section-text',
] as $expected) {
    if (!str_contains($stampa, $expected)) {
        fwrite(STDERR, "Integrazione browser mancante: {$expected}\n");
        exit(1);
    }
}

foreach ([
    '.annual-report-print {',
    '.annual-report-note {',
    '.annual-report-section {',
    '.annual-report-section-title {',
    '.annual-report-section-text {',
    'orphans: 3;',
    'widows: 3;',
] as $expected) {
    if (!str_contains($css, $expected)) {
        fwrite(STDERR, "Stile browser print mancante: {$expected}\n");
        exit(1);
    }
}

echo "ANNUAL REPORT BROWSER PRINT OK\n";
