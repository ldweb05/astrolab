<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/NarrativeQualityValidator.php';

$validator = new NarrativeQualityValidator();

$report = [
    'sections' => [
        [
            'id' => 'executive_summary',
            'text' => 'L’anno potrebbe richiedere equilibrio e consapevolezza.',
        ],
        [
            'id' => 'meaning_of_year',
            'text' => 'L’ANNO   potrebbe richiedere equilibrio e consapevolezza.',
        ],
        [
            'id' => 'conclusion',
            'text' => 'Le opportunità potrebbero essere valorizzate con prudenza.',
        ],
    ],
];

$result = $validator->validate($report);
$issues = $result['issues'] ?? [];

$duplicates = array_values(array_filter(
    $issues,
    static fn(array $issue): bool =>
        ($issue['type'] ?? '') === 'duplicate_section'
));

if (count($duplicates) !== 1) {
    fwrite(STDERR, "Rilevamento sezioni duplicate non valido\n");
    exit(1);
}

if (
    ($duplicates[0]['section'] ?? null) !== 'meaning_of_year'
    || ($duplicates[0]['duplicate_of'] ?? null) !== 'executive_summary'
) {
    fwrite(STDERR, "Tracciamento duplicato non valido\n");
    exit(1);
}

$unique = $validator->validate([
    'sections' => [
        ['id' => 'one', 'text' => 'Primo testo narrativo.'],
        ['id' => 'two', 'text' => 'Secondo testo narrativo.'],
    ],
]);

if (($unique['valid'] ?? false) !== true) {
    fwrite(STDERR, "Validazione testi unici non valida\n");
    exit(1);
}

echo "NARRATIVE QUALITY DUPLICATES OK\n";
