<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ForecastEngineV3.php';

$temaRS = [
    'pianeti' => [
        'Sole' => ['casa' => 10],
        'Giove' => ['casa' => 10],
        'Venere' => ['casa' => 5],
        'Mercurio' => ['casa' => 3],
        'Marte' => ['casa' => 6],
        'Luna' => ['casa' => 4],
        'Saturno' => ['casa' => 12],
        'Urano' => ['casa' => 11],
        'Nettuno' => ['casa' => 9],
        'Plutone' => ['casa' => 8],
    ],
];

$result = (new ForecastEngineV3())->generate($temaRS);

$report = $result['annual_report'] ?? null;
$validation = $result['report_validation'] ?? null;

if (!is_array($report)) {
    fwrite(STDERR, "annual_report mancante\n");
    exit(1);
}

if (!is_array($validation)) {
    fwrite(STDERR, "report_validation mancante\n");
    exit(1);
}

if (($validation['valid'] ?? false) !== true) {
    fwrite(STDERR, "Validazione narrativa fallita\n");
    exit(1);
}

$sections = $report['sections'] ?? [];
$evidences = $report['evidences'] ?? [];

if (count($sections) < 8) {
    fwrite(STDERR, "Numero sezioni insufficiente\n");
    exit(1);
}

if (count($evidences) < 1) {
    fwrite(STDERR, "Evidenze relazione mancanti\n");
    exit(1);
}

$wordCount = (int)($validation['word_count'] ?? 0);

if ($wordCount < 900 || $wordCount > 2200) {
    fwrite(STDERR, "Lunghezza relazione fuori soglia: {$wordCount}\n");
    exit(1);
}

echo "ANNUAL REPORT OK: "
    .count($sections)
    ." sezioni, "
    .$wordCount
    ." parole, "
    .count($evidences)
    ." evidenze\n";
