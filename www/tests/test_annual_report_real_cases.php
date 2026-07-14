<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ForecastEngineV3.php';

$cases = [
    'career_focus' => [
        'pianeti' => [
            'Sole' => ['casa' => 10],
            'Luna' => ['casa' => 4],
            'Mercurio' => ['casa' => 3],
            'Venere' => ['casa' => 5],
            'Marte' => ['casa' => 6],
            'Giove' => ['casa' => 10],
            'Saturno' => ['casa' => 12],
            'Urano' => ['casa' => 11],
            'Nettuno' => ['casa' => 9],
            'Plutone' => ['casa' => 8],
        ],
    ],
    'relationship_focus' => [
        'pianeti' => [
            'Sole' => ['casa' => 7],
            'Luna' => ['casa' => 5],
            'Mercurio' => ['casa' => 7],
            'Venere' => ['casa' => 7],
            'Marte' => ['casa' => 4],
            'Giove' => ['casa' => 5],
            'Saturno' => ['casa' => 8],
            'Urano' => ['casa' => 3],
            'Nettuno' => ['casa' => 11],
            'Plutone' => ['casa' => 1],
        ],
    ],
    'transformation_focus' => [
        'pianeti' => [
            'Sole' => ['casa' => 8],
            'Luna' => ['casa' => 12],
            'Mercurio' => ['casa' => 8],
            'Venere' => ['casa' => 12],
            'Marte' => ['casa' => 8],
            'Giove' => ['casa' => 12],
            'Saturno' => ['casa' => 6],
            'Urano' => ['casa' => 10],
            'Nettuno' => ['casa' => 4],
            'Plutone' => ['casa' => 12],
        ],
    ],
];

$engine = new ForecastEngineV3();

foreach ($cases as $caseId => $temaRS) {
    $result = $engine->generate($temaRS);

    $report = $result['annual_report'] ?? null;
    $validation = $result['report_validation'] ?? null;
    $summary = $result['summary'] ?? null;

    if (!is_array($report)) {
        fwrite(STDERR, "{$caseId}: annual_report mancante\n");
        exit(1);
    }

    if (!is_array($validation)) {
        fwrite(STDERR, "{$caseId}: report_validation mancante\n");
        exit(1);
    }

    if (!is_array($summary)) {
        fwrite(STDERR, "{$caseId}: summary mancante\n");
        exit(1);
    }

    $sections = $report['sections'] ?? [];
    $wordCount = (int)($validation['word_count'] ?? 0);
    $issues = $validation['issues'] ?? [];

    if (count($sections) < 10) {
        fwrite(
            STDERR,
            "{$caseId}: numero sezioni insufficiente\n"
        );
        exit(1);
    }

    if ($wordCount < 900 || $wordCount > 2200) {
        fwrite(
            STDERR,
            "{$caseId}: lunghezza fuori soglia {$wordCount}\n"
        );
        exit(1);
    }

    if (($validation['valid'] ?? false) !== true) {
        fwrite(
            STDERR,
            "{$caseId}: validazione narrativa fallita\n"
        );
        fwrite(STDERR, json_encode($issues, JSON_PRETTY_PRINT).PHP_EOL);
        exit(1);
    }

    $executiveSummary = $report['executive_summary'] ?? null;

    if (!is_array($executiveSummary)) {
        fwrite(
            STDERR,
            "{$caseId}: executive_summary mancante\n"
        );
        exit(1);
    }

    if (($executiveSummary['dominant_theme'] ?? null) === null) {
        fwrite(
            STDERR,
            "{$caseId}: dominant_theme mancante\n"
        );
        exit(1);
    }

    $normalizedTexts = [];

    foreach ($sections as $section) {
        $text = trim((string)($section['text'] ?? ''));

        if ($text === '') {
            fwrite(
                STDERR,
                "{$caseId}: sezione narrativa vuota\n"
            );
            exit(1);
        }

        $normalized = mb_strtolower(
            preg_replace('/\s+/u', ' ', $text) ?? $text
        );

        if (isset($normalizedTexts[$normalized])) {
            fwrite(
                STDERR,
                "{$caseId}: sezione narrativa duplicata\n"
            );
            exit(1);
        }

        $normalizedTexts[$normalized] = true;
    }

    echo sprintf(
        "%s OK: %d sezioni, %d parole, tema dominante %s\n",
        strtoupper($caseId),
        count($sections),
        $wordCount,
        (string)$executiveSummary['dominant_theme']
    );
}

echo "ANNUAL REPORT REAL CASES OK\n";
