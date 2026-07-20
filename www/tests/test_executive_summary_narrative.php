<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ExecutiveSummaryNarrativeBuilder.php';

$builder = new ExecutiveSummaryNarrativeBuilder();

$text = $builder->build([
    'dominant_theme' => 'carriera',
    'top_strengths' => ['carriera', 'amore'],
    'top_attention' => ['salute'],
    'overall_tone' => 'mixed',
    'confidence' => 75.0,
]);

foreach ([
    'carriera e la realizzazione personale',
    'relazioni e la vita affettiva',
    'benessere e la gestione delle energie',
] as $expected) {
    if (!str_contains($text, $expected)) {
        fwrite(STDERR, "Contenuto executive summary mancante: {$expected}\n");
        exit(1);
    }
}

if ($builder->build([]) !== '') {
    fwrite(STDERR, "Executive summary vuota non valida\n");
    exit(1);
}

echo "EXECUTIVE SUMMARY NARRATIVE OK\n";
