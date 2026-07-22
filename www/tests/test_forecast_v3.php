<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ForecastEngineV3.php';

$temaRS = [
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
    ]
];

$engine = new ForecastEngineV3();

echo json_encode(
    $engine->generate($temaRS),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);
