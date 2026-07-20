<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/AnnualForecastEngine.php';

$temaRS = [
    'pianeti' => [
        'Sole'      => ['casa'=>10],
        'Giove'     => ['casa'=>10],
        'Venere'    => ['casa'=>5],
        'Mercurio'  => ['casa'=>3],
        'Marte'     => ['casa'=>6],
        'Luna'      => ['casa'=>4],
        'Saturno'   => ['casa'=>12],
    ]
];

$engine = new AnnualForecastEngine();

echo json_encode(
    $engine->genera($temaRS),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);
