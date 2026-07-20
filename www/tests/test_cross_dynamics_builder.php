<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/CrossDynamicsBuilder.php';

$builder = new CrossDynamicsBuilder();

$text = $builder->build(
    ['carriera', 'salute', 'amore'],
    [
        'carriera' => ['polarity' => 'positive'],
        'salute' => ['polarity' => 'critical'],
        'amore' => ['polarity' => 'mixed'],
    ]
);

foreach ([
    'carriera e realizzazione personale',
    'benessere e gestione delle energie',
    'relazioni e vita affettiva',
    'favorendo adattamento, recupero e ricerca di soluzioni',
    'prudenza, continuità e capacità di risposta',
] as $expected) {
    if (!str_contains($text, $expected)) {
        fwrite(STDERR, "Contenuto Cross Dynamics mancante: {$expected}\n");
        exit(1);
    }
}

if ($builder->build([], []) !== '') {
    fwrite(STDERR, "Cross Dynamics vuota non valida\n");
    exit(1);
}

echo "CROSS DYNAMICS BUILDER OK\n";
