<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AnnualReportBuilder.php';

$builder = new AnnualReportBuilder();

$summary = [
    'dominant_theme' => 'carriera',
    'executive_summary' => [
        'dominant_theme' => 'carriera',
        'top_strengths' => ['carriera'],
        'top_attention' => ['salute'],
        'overall_tone' => 'mixed',
        'confidence' => 75.0,
    ],
];

$outline = [
    [
        'id' => 'meaning_of_year',
        'title' => "Il significato dell'anno",
    ],
];

$draft = [
    [
        'id' => 'meaning_of_year',
        'title' => "Il significato dell'anno",
        'text' => "L'anno potrebbe concentrarsi sulla realizzazione personale.",
    ],
];

$report = $builder->build($summary, $outline, $draft);
$executive = $report['executive_summary'] ?? null;

if (!is_array($executive)) {
    fwrite(STDERR, "executive_summary mancante nel report\n");
    exit(1);
}

if (($executive['dominant_theme'] ?? null) !== 'carriera') {
    fwrite(STDERR, "dominant_theme executive_summary non valido\n");
    exit(1);
}

if (($executive['overall_tone'] ?? null) !== 'mixed') {
    fwrite(STDERR, "overall_tone executive_summary non valido\n");
    exit(1);
}

if (($executive['confidence'] ?? null) !== 75.0) {
    fwrite(STDERR, "confidence executive_summary non valida\n");
    exit(1);
}

$fallback = $builder->build(
    ['dominant_theme' => 'amore'],
    $outline,
    $draft
);

if (
    !is_array($fallback['executive_summary'] ?? null)
    || ($fallback['executive_summary']['dominant_theme'] ?? null) !== 'amore'
    || ($fallback['executive_summary']['overall_tone'] ?? null) !== 'neutral'
) {
    fwrite(STDERR, "Fallback executive_summary non valido\n");
    exit(1);
}

echo "ANNUAL REPORT EXECUTIVE SUMMARY OK\n";
