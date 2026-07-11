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

$checks = [
    'sections'      => count($report['sections'] ?? []),
    'word_count'    => (int)($report['word_count'] ?? 0),
    'evidences'     => count($report['evidences'] ?? []),
    'valid'         => (bool)($data['report_validation']['valid'] ?? false),
];

foreach ($checks as $k=>$v) {
    echo str_pad($k,18).": ".$v.PHP_EOL;
}

if (
    $checks['sections'] < 10 ||
    $checks['word_count'] < 900 ||
    $checks['evidences'] < 10 ||
    !$checks['valid']
) {
    fwrite(STDERR,"REGRESSION TEST FAILED\n");
    exit(1);
}

echo PHP_EOL."FULL REGRESSION OK".PHP_EOL;
