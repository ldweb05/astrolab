<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_SATURN_HOUSE_10';

$result = (new AARuleEngine())->evaluate([
    'saturno' => [
        'condition_id' => $conditionId,
        'planet' => 'saturno',
        'house' => 10,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0086'
));

if (count($evidences) !== 3) {
    throw new RuntimeException('RULE-0086: numero evidenze errato');
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['carriera', 'prestigio', 'responsabilita'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0086: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0086: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 86) {
    throw new RuntimeException(
        'RULE-0086: attese 86 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0086 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
