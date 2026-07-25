<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_SATURN_HOUSE_6';

$result = (new AARuleEngine())->evaluate([
    'saturno' => [
        'condition_id' => $conditionId,
        'planet' => 'saturno',
        'house' => 6,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0082'
));

if (count($evidences) !== 3) {
    throw new RuntimeException(
        'RULE-0082: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['lavoro', 'responsabilita', 'salute'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0082: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0082: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 120) {
    throw new RuntimeException(
        'RULE-0082: attese 120 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0082 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
