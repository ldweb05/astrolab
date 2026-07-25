<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$result = (new AARuleEngine())->evaluate([
    'sole' => [
        'condition_id' => 'CONDITION_TEST_SUN_HOUSE_9',
        'planet' => 'sole',
        'house' => 9,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0019'
));

if (count($evidences) !== 3) {
    throw new RuntimeException(
        'RULE-0019: attese 3 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['estero', 'studio', 'viaggi'];
sort($expected);

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0019: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (
        ($evidence['condition_id'] ?? '')
        !== 'CONDITION_TEST_SUN_HOUSE_9'
    ) {
        throw new RuntimeException(
            'RULE-0019: condition_id non propagata'
        );
    }
}

if (count(RuleRegistry::all()) !== 120) {
    throw new RuntimeException(
        'RULE-0019: attese 120 Rule registrate'
    );
}

echo "RULE-0019 TEST OK\n";
echo "evidences       : ".count($evidences)."\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".count(RuleRegistry::all())."\n";
