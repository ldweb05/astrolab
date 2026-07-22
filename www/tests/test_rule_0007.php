<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditions = [
    'urano' => [
        'condition_id' => 'CONDITION_TEST_URANO_HOUSE_11',
        'planet' => 'urano',
        'house' => 11,
        'strength' => 1.0,
    ],
];

$result = (new AARuleEngine())->evaluate($conditions);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0007'
));

if (count($evidences) !== 1) {
    throw new RuntimeException(
        'RULE-0007: attese 1 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = array (
  0 => 'amicizie',
);
sort($expected);

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0007: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== 'CONDITION_TEST_URANO_HOUSE_11') {
        throw new RuntimeException(
            'RULE-0007: condition_id non propagata'
        );
    }
}

$registered = RuleRegistry::all();

if (count($registered) !== 120) {
    throw new RuntimeException(
        'RULE-0007: attese 120 Rule registrate, trovate '.count($registered)
    );
}

echo "RULE-0007 TEST OK
";
echo "evidences       : ".count($evidences)."
";
echo "themes          : ".implode(', ', $themes)."
";
echo "registered_rules: ".count($registered)."
";
