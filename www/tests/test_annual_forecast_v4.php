<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/AnnualForecastEngine.php';

$temaRS = [
    'case' => [
        'ASC' => ['longitudine'=>15],
        'MC'  => ['longitudine'=>105],
    ],

    'pianeti' => [
        'Sole'      => ['casa'=>10, 'longitudine'=>120],
        'Giove'     => ['casa'=>10, 'longitudine'=>125],
        'Venere'    => ['casa'=>5,  'longitudine'=>30],
        'Mercurio'  => ['casa'=>3,  'longitudine'=>210],
        'Marte'     => ['casa'=>6,  'longitudine'=>300],
        'Luna'      => ['casa'=>4,  'longitudine'=>60],
        'Saturno'   => ['casa'=>12, 'longitudine'=>240, 'retrogrado'=>true],
    ]
];

$engine = new AnnualForecastEngine();

echo json_encode(
    $engine->genera($temaRS),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);
