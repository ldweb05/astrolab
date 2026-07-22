<?php
declare(strict_types=1);

require_once __DIR__
    .'/../includes/forecast/NarrativeStyleEngine.php';

$engine = new NarrativeStyleEngine();

$sections = [
    [
        'id' => 'one',
        'title' => 'Prima sezione',
        'text' =>
            'L’anno sembrerebbe svilupparsi attraverso nuove priorità. '
            .'Il tema potrebbe rappresentare un passaggio importante. '
            .'Le circostanze potrebbero richiedere prudenza.',
        'metadata' => ['trace' => 'A'],
    ],
    [
        'id' => 'two',
        'title' => 'Seconda sezione',
        'text' =>
            'Il percorso sembrerebbe svilupparsi gradualmente. '
            .'Le risorse potrebbero offrire sostegno. '
            .'Altre risorse potrebbero offrire continuità.',
        'metadata' => ['trace' => 'B'],
    ],
];

$result = $engine->refine($sections);

if (count($result) !== 2) {
    fwrite(STDERR, "Numero sezioni alterato\n");
    exit(1);
}

if (
    ($result[0]['id'] ?? null) !== 'one'
    || ($result[0]['title'] ?? null) !== 'Prima sezione'
    || ($result[0]['metadata']['trace'] ?? null) !== 'A'
) {
    fwrite(STDERR, "Contratto della sezione alterato\n");
    exit(1);
}

$fullText = implode(
    ' ',
    array_map(
        static fn(array $section): string =>
            (string)($section['text'] ?? ''),
        $result
    )
);

foreach ([
    'sembrerebbe svilupparsi',
    'potrebbe rappresentare',
    'potrebbero richiedere',
    'potrebbero offrire',
    'apparirebbe destinato',
] as $forbidden) {
    if (str_contains($fullText, $forbidden)) {
        fwrite(
            STDERR,
            "Formula non rifinita o non prudenziale: {$forbidden}\n"
        );
        exit(1);
    }
}

foreach ([
    'potrebbe svilupparsi',
    'tenderebbe a svilupparsi',
    'potrebbe costituire',
    'potrebbero domandare',
    'potrebbero mettere a disposizione',
    'potrebbero favorire',
] as $expected) {
    if (!str_contains($fullText, $expected)) {
        fwrite(
            STDERR,
            "Variante narrativa mancante: {$expected}\n"
        );
        exit(1);
    }
}

$empty = $engine->refine([
    ['id' => 'empty', 'text' => ''],
]);

if (($empty[0]['text'] ?? null) !== '') {
    fwrite(STDERR, "Sezione vuota alterata\n");
    exit(1);
}

echo "NARRATIVE STYLE ENGINE OK\n";
