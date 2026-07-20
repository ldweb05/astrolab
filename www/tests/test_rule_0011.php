<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditions = [
    'sole' => [
        'condition_id' => 'CONDITION_TEST_SUN_HOUSE_1',
        'planet' => 'sole',
        'house' => 1,
        'strength' => 1.0,
    ],
];

$result = (new AARuleEngine())->evaluate($conditions);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0011'
));

if (count($evidences) !== 3) {
    throw new RuntimeException(
        'RULE-0011: attese 3 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['identita', 'iniziative', 'salute'];
sort($expected);

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0011: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (
        ($evidence['condition_id'] ?? '')
        !== 'CONDITION_TEST_SUN_HOUSE_1'
    ) {
        throw new RuntimeException(
            'RULE-0011: condition_id non propagata'
        );
    }
}

$registered = RuleRegistry::all();

if (count($registered) !== 11) {
    throw new RuntimeException(
        'Attese 11 Rule registrate, trovate '.count($registered)
    );
}

echo "RULE-0011 TEST OK\n";
echo "evidences       : ".count($evidences)."\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".count($registered)."\n";
