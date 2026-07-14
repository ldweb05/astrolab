<?php
declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__
    .'/../includes/forecast/AnnualReportPrintRenderer.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function buildPdf(): string
{
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
                    'L’anno potrebbe concentrarsi sulla realizzazione personale '
                    .'e sulla gestione consapevole delle priorità.',
            ],
            [
                'id' => 'theme_summary',
                'title' => 'Profilo dei temi principali',
                'text' =>
                    'Le relazioni, il lavoro e il benessere potrebbero interagire '
                    .'tra loro nel corso dell’anno.',
            ],
            [
                'id' => 'cross_dynamics',
                'title' => 'Dinamiche trasversali',
                'text' =>
                    'Le aree principali potrebbero influenzarsi reciprocamente, '
                    .'richiedendo equilibrio e continuità.',
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
}
.annual-report-section-title {
    color: #2C3E6B;
    font-size: 11pt;
    font-weight: bold;
    margin-bottom: 5pt;
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

    $fontCache = '/tmp/astro-val-pdf-determinism-font-cache';
    $tempDir = '/tmp/astro-val-pdf-determinism-temp';

    foreach ([$fontCache, $tempDir] as $directory) {
        if (
            !is_dir($directory)
            && !mkdir($directory, 0777, true)
            && !is_dir($directory)
        ) {
            throw new RuntimeException(
                "Impossibile creare {$directory}"
            );
        }
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

    return $dompdf->output();
}

function pdfStructure(string $pdf): array
{
    if (!str_starts_with($pdf, '%PDF-')) {
        throw new RuntimeException('Header PDF non valido');
    }

    if (!str_contains(substr($pdf, -2048), '%%EOF')) {
        throw new RuntimeException('Terminatore PDF mancante');
    }

    preg_match_all(
        '/\b([0-9]+)\s+([0-9]+)\s+obj\b/',
        $pdf,
        $objectMatches,
        PREG_SET_ORDER
    );

    preg_match_all(
        '/\/Type\s*\/Page\b/',
        $pdf,
        $pageMatches
    );

    preg_match_all(
        '/\/Type\s*\/Font\b/',
        $pdf,
        $fontMatches
    );

    preg_match_all(
        '/\/Subtype\s*\/Image\b/',
        $pdf,
        $imageMatches
    );

    return [
        'objects' => count($objectMatches),
        'pages' => count($pageMatches[0] ?? []),
        'fonts' => count($fontMatches[0] ?? []),
        'images' => count($imageMatches[0] ?? []),
    ];
}

$first = buildPdf();
$second = buildPdf();

$firstStructure = pdfStructure($first);
$secondStructure = pdfStructure($second);

if ($firstStructure !== $secondStructure) {
    fwrite(
        STDERR,
        "Struttura PDF non deterministica\n"
        .json_encode(
            [
                'first' => $firstStructure,
                'second' => $secondStructure,
            ],
            JSON_PRETTY_PRINT
        )
        ."\n"
    );
    exit(1);
}

if (($firstStructure['pages'] ?? 0) < 1) {
    fwrite(STDERR, "Numero pagine PDF non valido\n");
    exit(1);
}

$firstSize = strlen($first);
$secondSize = strlen($second);
$sizeDifference = abs($firstSize - $secondSize);

if ($sizeDifference > 64) {
    fwrite(
        STDERR,
        "Dimensione PDF non stabile: "
        ."{$firstSize} vs {$secondSize}\n"
    );
    exit(1);
}

foreach ([
    '/tmp/astro-val-annual-report-determinism-1.pdf' => $first,
    '/tmp/astro-val-annual-report-determinism-2.pdf' => $second,
] as $path => $content) {
    if (file_put_contents($path, $content) === false) {
        fwrite(
            STDERR,
            "Impossibile salvare il PDF temporaneo: {$path}\n"
        );
        exit(1);
    }
}

echo sprintf(
    "ANNUAL REPORT PDF DETERMINISM OK: "
    ."%d pagine, %d oggetti, %d/%d byte\n",
    $firstStructure['pages'],
    $firstStructure['objects'],
    $firstSize,
    $secondSize
);
