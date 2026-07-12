<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$result = (new AARuleEngine())->evaluate([
    'sole' => [
        'condition_id' => 'CONDITION_TEST_SUN_HOUSE_5',
        'planet' => 'sole',
        'house' => 5,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0015'
));

if (count($evidences) !== 3) {
    throw new RuntimeException(
        'RULE-0015: attese 3 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['amore', 'creativita', 'figli'];
sort($expected);

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0015: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (
        ($evidence['condition_id'] ?? '')
        !== 'CONDITION_TEST_SUN_HOUSE_5'
    ) {
        throw new RuntimeException(
            'RULE-0015: condition_id non propagata'
        );
    }
}

$registered = RuleRegistry::all();

if (count($registered) !== 15) {
    throw new RuntimeException(
        'Attese 15 Rule registrate, trovate '.count($registered)
    );
}

echo "RULE-0015 TEST OK\n";
echo "evidences       : ".count($evidences)."\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".count($registered)."\n";
