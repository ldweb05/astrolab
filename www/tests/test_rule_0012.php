<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditions = [
    'sole' => [
        'condition_id' => 'CONDITION_TEST_SUN_HOUSE_2',
        'planet' => 'sole',
        'house' => 2,
        'strength' => 1.0,
    ],
];

$result = (new AARuleEngine())->evaluate($conditions);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0012'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0012: attese 2 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

if ($themes !== ['denaro', 'patrimonio']) {
    throw new RuntimeException(
        'RULE-0012: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (
        ($evidence['condition_id'] ?? '')
        !== 'CONDITION_TEST_SUN_HOUSE_2'
    ) {
        throw new RuntimeException(
            'RULE-0012: condition_id non propagata'
        );
    }
}

$registered = RuleRegistry::all();

if (count($registered) !== 120) {
    throw new RuntimeException(
        'Attese 120 Rule registrate, trovate '.count($registered)
    );
}

echo "RULE-0012 TEST OK\n";
echo "evidences       : ".count($evidences)."\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".count($registered)."\n";
