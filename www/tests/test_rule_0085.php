<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_SATURN_HOUSE_9';

$result = (new AARuleEngine())->evaluate([
    'saturno' => [
        'condition_id' => $conditionId,
        'planet' => 'saturno',
        'house' => 9,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0085'
));

if (count($evidences) !== 2) {
    throw new RuntimeException('RULE-0085: numero evidenze errato');
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['estero', 'studio'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0085: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0085: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 86) {
    throw new RuntimeException(
        'RULE-0085: attese 86 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0085 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
