<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AnnualSummaryBuilder.php';

$builder = new AnnualSummaryBuilder();

$profiles = [
    'carriera' => [
        'rank' => 1,
        'polarity' => 'positive',
        'confidence' => 80,
    ],
    'salute' => [
        'rank' => 2,
        'polarity' => 'critical',
        'confidence' => 60,
    ],
    'amore' => [
        'rank' => 3,
        'polarity' => 'mixed',
        'confidence' => 40,
    ],
];

$result = $builder->build($profiles);
$executive = $result['executive_summary'] ?? null;

if (!is_array($executive)) {
    fwrite(STDERR, "executive_summary mancante\n");
    exit(1);
}

$expected = [
    'dominant_theme' => 'carriera',
    'top_strengths' => ['carriera'],
    'top_attention' => ['salute'],
    'overall_tone' => 'mixed',
    'confidence' => 60.0,
];

foreach ($expected as $key => $value) {
    if (($executive[$key] ?? null) !== $value) {
        fwrite(STDERR, "Valore executive_summary non valido: {$key}\n");
        exit(1);
    }
}

$empty = $builder->build([]);
$emptyExecutive = $empty['executive_summary'] ?? null;

if (
    !is_array($emptyExecutive)
    || ($emptyExecutive['overall_tone'] ?? null) !== 'neutral'
    || ($emptyExecutive['confidence'] ?? null) !== 0.0
) {
    fwrite(STDERR, "Contratto profili vuoti non valido\n");
    exit(1);
}

echo "ANNUAL SUMMARY BUILDER OK\n";
