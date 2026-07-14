<?php
declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../includes/forecast/AnnualReportPrintRenderer.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$renderer = new AnnualReportPrintRenderer();

$reportHtml = $renderer->render([
    'title' => 'Rivoluzione Solare 2027',
    'methodological_note' =>
        'Questa relazione propone una lettura simbolica e probabilistica.',
    'sections' => [
        [
            'id' => 'executive_summary',
            'title' => 'Sintesi esecutiva',
            'text' =>
                'L’anno potrebbe concentrarsi sulla realizzazione personale e '
                .'sulla gestione consapevole delle priorità.',
        ],
        [
            'id' => 'theme_summary',
            'title' => 'Profilo dei temi principali',
            'text' =>
                'Le relazioni, il lavoro e il benessere potrebbero interagire '
                .'tra loro nel corso dell’anno.',
        ],
        [
            'id' => 'conclusion',
            'title' => 'Sintesi conclusiva',
            'text' =>
                'La consapevolezza delle dinamiche principali potrebbe aiutare '
                .'a valorizzare le risorse disponibili.',
        ],
    ],
]);

$html = <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 18mm; }
body {
    font-family: Helvetica, Arial, sans-serif;
    color: #24324A;
    font-size: 10pt;
    line-height: 1.55;
}
.report-section-title {
    color: #2C3E6B;
    font-size: 16pt;
    font-weight: bold;
    margin-bottom: 12pt;
}
.annual-report-note {
    padding: 9pt;
    margin-bottom: 14pt;
    background: #F4F0E8;
    border-left: 3pt solid #2C3E6B;
}
.annual-report-section {
    margin-bottom: 14pt;
    page-break-inside: auto;
}
.annual-report-section-title {
    color: #2C3E6B;
    font-size: 11pt;
    font-weight: bold;
    margin-bottom: 5pt;
    page-break-after: avoid;
}
.annual-report-section-text p {
    margin: 0 0 7pt;
    text-align: justify;
}
</style>
</head>
<body>
{$reportHtml}
</body>
</html>
HTML;

$fontCache = '/tmp/astro-val-dompdf-font-cache';
$tempDir = '/tmp/astro-val-dompdf-temp';

if (!is_dir($fontCache) && !mkdir($fontCache, 0777, true) && !is_dir($fontCache)) {
    fwrite(STDERR, "Impossibile creare la font cache temporanea\n");
    exit(1);
}

if (!is_dir($tempDir) && !mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
    fwrite(STDERR, "Impossibile creare la directory temporanea Dompdf\n");
    exit(1);
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('isFontSubsettingEnabled', true);
$options->set('fontCache', $fontCache);
$options->set('tempDir', $tempDir);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdf = $dompdf->output();

if (!str_starts_with($pdf, '%PDF-')) {
    fwrite(STDERR, "Header PDF non valido\n");
    exit(1);
}

if (strlen($pdf) < 1500) {
    fwrite(STDERR, "PDF generato troppo piccolo: ".strlen($pdf)." byte\n");
    exit(1);
}

if (!str_contains(substr($pdf, -1024), '%%EOF')) {
    fwrite(STDERR, "Terminatore PDF mancante\n");
    exit(1);
}

$outputPath = '/tmp/astro-val-annual-report-smoke.pdf';

if (file_put_contents($outputPath, $pdf) === false) {
    fwrite(STDERR, "Impossibile salvare il PDF smoke test\n");
    exit(1);
}

echo "ANNUAL REPORT DOMPDF SMOKE OK: "
    .strlen($pdf)
    ." byte — {$outputPath}\n";
