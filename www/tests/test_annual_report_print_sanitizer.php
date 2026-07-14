<?php
declare(strict_types=1);

require_once __DIR__
    .'/../includes/forecast/AnnualReportPrintSanitizer.php';

$sanitizer = new AnnualReportPrintSanitizer();

$sections = [];

for ($index = 1; $index <= 25; $index++) {
    $sections[] = [
        'id' => 'section_'.$index,
        'title' => str_repeat('T', 250),
        'text' => 'Testo valido della sezione '.$index,
    ];
}

$sections[] = [
    'id' => 'empty',
    'title' => 'Sezione vuota',
    'text' => '   ',
];

$sections[] = 'invalid';

$result = $sanitizer->sanitize([
    'title' => str_repeat('R', 250),
    'methodological_note' => str_repeat('N', 4000),
    'dominant_theme' => ['invalid'],
    'sections' => $sections,
]);

if (count($result['sections'] ?? []) !== 20) {
    fwrite(STDERR, "Limite sezioni non rispettato\n");
    exit(1);
}

if (mb_strlen((string)($result['title'] ?? '')) !== 180) {
    fwrite(STDERR, "Limite titolo report non rispettato\n");
    exit(1);
}

if (
    mb_strlen(
        (string)($result['methodological_note'] ?? '')
    ) !== 3000
) {
    fwrite(
        STDERR,
        "Limite nota metodologica non rispettato\n"
    );
    exit(1);
}

if (($result['dominant_theme'] ?? null) !== '') {
    fwrite(STDERR, "Valore non scalare non rimosso\n");
    exit(1);
}

foreach ($result['sections'] as $section) {
    if (mb_strlen((string)$section['title']) > 180) {
        fwrite(STDERR, "Titolo sezione non limitato\n");
        exit(1);
    }

    if (trim((string)$section['text']) === '') {
        fwrite(STDERR, "Sezione vuota conservata\n");
        exit(1);
    }
}

if ($sanitizer->sanitize([]) !== []) {
    fwrite(STDERR, "Report vuoto non gestito\n");
    exit(1);
}

if (
    $sanitizer->sanitize([
        'sections' => [
            ['text' => ['invalid']],
        ],
    ]) !== []
) {
    fwrite(STDERR, "Testo non scalare non rimosso\n");
    exit(1);
}

echo "ANNUAL REPORT PRINT SANITIZER OK\n";
