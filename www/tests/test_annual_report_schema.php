<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ForecastEngineV3.php';

$temaRS = [
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

foreach ([
    'title',
    'methodological_note',
    'dominant_theme',
    'executive_summary',
    'outline',
    'sections',
    'word_count',
] as $requiredKey) {
    if (!array_key_exists($requiredKey, $report)) {
        fwrite(
            STDERR,
            "Campo annual_report mancante: {$requiredKey}\n"
        );
        exit(1);
    }
}

$sections = $report['sections'];

if (!is_array($sections)) {
    fwrite(STDERR, "sections non è un array\n");
    exit(1);
}

$expectedPrefixIds = [
    'executive_summary',
    'meaning_of_year',
    'theme_summary',
];

$expectedSuffixIds = [
    'cross_dynamics',
    'opportunities',
    'attention',
    'conclusion',
];

$actualIds = [];
$fullText = [];

foreach ($sections as $index => $section) {
    if (!is_array($section)) {
        fwrite(
            STDERR,
            "Sezione {$index} non valida\n"
        );
        exit(1);
    }

    foreach (['id', 'title', 'text'] as $requiredKey) {
        if (!array_key_exists($requiredKey, $section)) {
            fwrite(
                STDERR,
                "Sezione {$index}: campo mancante {$requiredKey}\n"
            );
            exit(1);
        }
    }

    $id = trim((string)$section['id']);
    $title = trim((string)$section['title']);
    $text = trim((string)$section['text']);

    if ($id === '' || $title === '' || $text === '') {
        fwrite(
            STDERR,
            "Sezione {$index}: contenuto obbligatorio vuoto\n"
        );
        exit(1);
    }

    if (isset($actualIds[$id])) {
        fwrite(
            STDERR,
            "ID sezione duplicato: {$id}\n"
        );
        exit(1);
    }

    $actualIds[$id] = true;
    $fullText[] = $text;
}

$actualIdList = array_keys($actualIds);

if (count($actualIdList) !== 12) {
    fwrite(
        STDERR,
        "Numero sezioni inatteso: ".count($actualIdList)."\n"
    );
    exit(1);
}

if (array_slice($actualIdList, 0, 3) !== $expectedPrefixIds) {
    fwrite(
        STDERR,
        "Prefisso delle sezioni non valido\n"
        ."Attuale: ".implode(', ', $actualIdList)."\n"
    );
    exit(1);
}

$themeIds = array_slice($actualIdList, 3, 5);

if (count($themeIds) !== 5) {
    fwrite(STDERR, "Numero sezioni tematiche non valido\n");
    exit(1);
}

foreach ($themeIds as $themeId) {
    if (
        preg_match(
            '/^theme_[a-z0-9_]+$/',
            $themeId
        ) !== 1
    ) {
        fwrite(
            STDERR,
            "ID sezione tematica non valido: {$themeId}\n"
        );
        exit(1);
    }
}

if (count(array_unique($themeIds)) !== 5) {
    fwrite(STDERR, "Sezioni tematiche duplicate\n");
    exit(1);
}

if (array_slice($actualIdList, -4) !== $expectedSuffixIds) {
    fwrite(
        STDERR,
        "Suffisso delle sezioni non valido\n"
        ."Attuale: ".implode(', ', $actualIdList)."\n"
    );
    exit(1);
}

$expectedIds = [
    ...$expectedPrefixIds,
    ...$themeIds,
    ...$expectedSuffixIds,
];

$outline = $report['outline'];

if (!is_array($outline) || count($outline) !== count($sections)) {
    fwrite(STDERR, "Outline non coerente con le sezioni\n");
    exit(1);
}

$outlineIds = array_map(
    static fn(array $section): string =>
        (string)($section['id'] ?? ''),
    $outline
);

if ($outlineIds !== $expectedIds) {
    fwrite(STDERR, "Outline ID non coerenti\n");
    exit(1);
}

$executiveSummary = $report['executive_summary'];

if (!is_array($executiveSummary)) {
    fwrite(STDERR, "executive_summary non valida\n");
    exit(1);
}

if (
    ($executiveSummary['dominant_theme'] ?? null)
    !== ($report['dominant_theme'] ?? null)
) {
    fwrite(
        STDERR,
        "Tema dominante incoerente con Executive Summary\n"
    );
    exit(1);
}

foreach ([
    'top_strengths',
    'top_attention',
    'overall_tone',
    'confidence',
] as $requiredKey) {
    if (!array_key_exists($requiredKey, $executiveSummary)) {
        fwrite(
            STDERR,
            "Executive Summary: campo mancante {$requiredKey}\n"
        );
        exit(1);
    }
}

$calculatedWordCount = str_word_count(
    implode(' ', $fullText)
);

if (
    $calculatedWordCount
    !== (int)($report['word_count'] ?? -1)
) {
    fwrite(
        STDERR,
        "word_count del report non coerente: "
        .$calculatedWordCount
        ." != "
        .(int)($report['word_count'] ?? -1)
        ."\n"
    );
    exit(1);
}

if (
    $calculatedWordCount
    !== (int)($validation['word_count'] ?? -1)
) {
    fwrite(
        STDERR,
        "word_count della validazione non coerente\n"
    );
    exit(1);
}

if (($validation['valid'] ?? false) !== true) {
    fwrite(STDERR, "Validazione narrativa negativa\n");
    exit(1);
}

if (
    (int)($validation['section_count'] ?? 0)
    !== count($sections)
) {
    fwrite(
        STDERR,
        "section_count della validazione non coerente\n"
    );
    exit(1);
}

echo sprintf(
    "ANNUAL REPORT SCHEMA OK: %d sezioni, %d parole\n",
    count($sections),
    $calculatedWordCount
);
