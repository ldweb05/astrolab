<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ConclusionNarrativeBuilder.php';

$builder = new ConclusionNarrativeBuilder();

$text = $builder->build(
    'carriera',
    [
        'primary_themes' => ['carriera', 'amore', 'salute'],
        'support_themes' => ['carriera', 'amore'],
        'attention_themes' => ['salute'],
    ],
    [
        'carriera' => [],
        'amore' => [],
        'salute' => [],
    ]
);

foreach ([
    'carriera e della realizzazione personale',
    'relazioni e vita affettiva',
    'benessere e gestione delle energie',
    'capacità di recupero',
    'non descrive eventi inevitabili',
] as $expected) {
    if (!str_contains($text, $expected)) {
        fwrite(STDERR, "Contenuto conclusione mancante: {$expected}\n");
        exit(1);
    }
}

if (
    substr_count($text, 'carriera e realizzazione personale') > 1
) {
    fwrite(STDERR, "Tema dominante ripetuto nella conclusione\n");
    exit(1);
}

if ($builder->build('', [], []) !== '') {
    fwrite(STDERR, "Conclusione vuota non valida\n");
    exit(1);
}

echo "CONCLUSION NARRATIVE BUILDER OK\n";
