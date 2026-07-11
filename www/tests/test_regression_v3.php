<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/AnnualForecastEngine.php';

$tema = [
    'pianeti' => [
        'Sole'      => ['casa'=>10],
        'Giove'     => ['casa'=>10],
        'Venere'    => ['casa'=>5],
        'Mercurio'  => ['casa'=>3],
        'Marte'     => ['casa'=>6],
        'Luna'      => ['casa'=>4],
        'Saturno'   => ['casa'=>12],
        'Urano'     => ['casa'=>11],
        'Nettuno'   => ['casa'=>9],
        'Plutone'   => ['casa'=>8],
    ],
];

$data = (new AnnualForecastEngine())->genera($tema);

$report = $data['relazione_annuale'] ?? [];
$formattedEvidences = $data['formatted_evidences'] ?? [];
$evidencesByTheme = $report['evidences_by_theme'] ?? [];
$sections = $report['sections'] ?? [];
$explainability = $report['explainability'] ?? [];

$sectionsWithEvidences = 0;

foreach ($sections as $section) {
    if (count($section['evidences'] ?? []) > 0) {
        $sectionsWithEvidences++;
    }
}

$hasSunJupiterComposite = false;

foreach ($formattedEvidences as $evidence) {
    if (
        ($evidence['code'] ?? '') ===
        'COMPOSITE_SUN_JUPITER_SAME_HOUSE'
    ) {
        $hasSunJupiterComposite = true;
        break;
    }
}

$checks = [
    'sections'      => count($report['sections'] ?? []),
    'word_count'    => (int)($report['word_count'] ?? 0),
    'evidences'     => count($report['evidences'] ?? []),
    'valid'         => (bool)($data['report_validation']['valid'] ?? false),
    'composite'     => $hasSunJupiterComposite,
    'theme_groups'  => count($evidencesByTheme),
    'section_links' => $sectionsWithEvidences,
    'explainability' => count($explainability['sections'] ?? []),
];

foreach ($checks as $k=>$v) {
    echo str_pad($k,18).": ".$v.PHP_EOL;
}

if (
    $checks['sections'] < 10 ||
    $checks['word_count'] < 900 ||
    $checks['evidences'] < 10 ||
    !$checks['valid'] ||
    !$checks['composite'] ||
    $checks['theme_groups'] < 10 ||
    $checks['section_links'] < 8 ||
    $checks['explainability'] < 8
) {
    fwrite(STDERR,"REGRESSION TEST FAILED\n");
    exit(1);
}

echo PHP_EOL."FULL REGRESSION OK".PHP_EOL;
