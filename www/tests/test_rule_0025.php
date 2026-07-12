<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_MOON_HOUSE_5';

$result = (new AARuleEngine())->evaluate([
    'luna' => [
        'condition_id' => $conditionId,
        'planet' => 'luna',
        'house' => 5,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0025'
));

if (count($evidences) !== 3) {
    throw new RuntimeException(
        'RULE-0025: attese 3 evidenze, trovate '.count($evidences)
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
        'RULE-0025: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0025: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 25) {
    throw new RuntimeException(
        'RULE-0025: attese 25 Rule registrate, trovate '.$registeredRules
    );
}

echo "RULE-0025 TEST OK\n";
echo "evidences       : ".count($evidences)."\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
