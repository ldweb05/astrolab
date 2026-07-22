<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditions = [
    'luna' => [
        'condition_id' => 'CONDITION_TEST_LUNA_HOUSE_4',
        'planet' => 'luna',
        'house' => 4,
        'strength' => 1.0,
    ],
];

$result = (new AARuleEngine())->evaluate($conditions);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0006'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0006: attese 2 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = array (
  0 => 'casa',
  1 => 'famiglia',
);
sort($expected);

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0006: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== 'CONDITION_TEST_LUNA_HOUSE_4') {
        throw new RuntimeException(
            'RULE-0006: condition_id non propagata'
        );
    }
}

$registered = RuleRegistry::all();

if (count($registered) !== 120) {
    throw new RuntimeException(
        'RULE-0006: attese 120 Rule registrate, trovate '.count($registered)
    );
}

echo "RULE-0006 TEST OK
";
echo "evidences       : ".count($evidences)."
";
echo "themes          : ".implode(', ', $themes)."
";
echo "registered_rules: ".count($registered)."
";
