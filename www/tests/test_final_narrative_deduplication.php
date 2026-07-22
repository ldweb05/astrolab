<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/FinalNarrativeEngine.php';

$engine = new FinalNarrativeEngine();

$forecast = [
    'narrative_v3' => [
        'headline' => 'Un anno di consolidamento.',
        'dominant_themes' => [
            [
                'text' => 'Le responsabilità richiedono attenzione e continuità.',
            ],
            [
                'text' => 'Le responsabilità   richiedono attenzione e continuità.',
            ],
            [
                'text' => 'Le relazioni potrebbero assumere maggiore importanza.',
            ],
            [
                'text' => 'un anno di consolidamento.',
            ],
        ],
    ],
];

$result = $engine->compose($forecast);

$expected = implode("\n\n", [
    'Un anno di consolidamento.',
    'Le responsabilità richiedono attenzione e continuità.',
    'Le relazioni potrebbero assumere maggiore importanza.',
]);

if (($result['text'] ?? null) !== $expected) {
    fwrite(STDERR, "TEST FAILED: deduplicazione narrativa non corretta.\n");
    fwrite(STDERR, "EXPECTED:\n".$expected."\n\n");
    fwrite(STDERR, "ACTUAL:\n".($result['text'] ?? '')."\n");
    exit(1);
}

echo "FINAL NARRATIVE DEDUPLICATION OK\n";
