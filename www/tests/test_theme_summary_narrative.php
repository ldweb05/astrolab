<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ThemeSummaryNarrativeBuilder.php';

$builder = new ThemeSummaryNarrativeBuilder();

$text = $builder->build(
    ['carriera', 'salute', 'amore'],
    [
        'carriera' => ['polarity' => 'positive'],
        'salute' => ['polarity' => 'critical'],
        'amore' => ['polarity' => 'mixed'],
    ]
);

foreach ([
    'risorse favorevoli',
    'maggiore attenzione',
    'dinamiche articolate e variabili',
] as $expected) {
    if (!str_contains($text, $expected)) {
        fwrite(STDERR, "Contenuto Theme Summary mancante: {$expected}\n");
        exit(1);
    }
}

if ($builder->build([], []) !== '') {
    fwrite(STDERR, "Theme Summary vuota non valida\n");
    exit(1);
}

echo "THEME SUMMARY NARRATIVE OK\n";
