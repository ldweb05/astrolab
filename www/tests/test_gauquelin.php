<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AngularPowerEngine.php';

$temaRS = [
    'case' => [
        'MC' => [
            'longitudine' => 100.0
        ]
    ],
    'pianeti' => [
        'Giove' => [
            'casa' => 9,
            'longitudine' => 99.2
        ],
        'Saturno' => [
            'casa' => 9,
            'longitudine' => 97.8
        ],
        'Sole' => [
            'casa' => 10,
            'longitudine' => 101.0
        ],
        'Venere' => [
            'casa' => 8,
            'longitudine' => 95.0
        ]
    ]
];

$engine = new AngularPowerEngine();

echo json_encode(
    $engine->calculate($temaRS),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);
