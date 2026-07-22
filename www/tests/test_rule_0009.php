<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditions = [
    'plutone' => [
        'condition_id' => 'CONDITION_TEST_PLUTONE_HOUSE_8',
        'planet' => 'plutone',
        'house' => 8,
        'strength' => 1.0,
    ],
];

$result = (new AARuleEngine())->evaluate($conditions);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0009'
));

if (count($evidences) !== 1) {
    throw new RuntimeException(
        'RULE-0009: attese 1 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = array (
  0 => 'trasformazione',
);
sort($expected);

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0009: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== 'CONDITION_TEST_PLUTONE_HOUSE_8') {
        throw new RuntimeException(
            'RULE-0009: condition_id non propagata'
        );
    }
}

$registered = RuleRegistry::all();

if (count($registered) !== 120) {
    throw new RuntimeException(
        'RULE-0009: attese 120 Rule registrate, trovate '.count($registered)
    );
}

echo "RULE-0009 TEST OK
";
echo "evidences       : ".count($evidences)."
";
echo "themes          : ".implode(', ', $themes)."
";
echo "registered_rules: ".count($registered)."
";
