<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_NEPTUNE_HOUSE_5';

$result = (new AARuleEngine())->evaluate([
    'nettuno' => [
        'condition_id' => $conditionId,
        'planet' => 'nettuno',
        'house' => 5,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0103'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0103: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['amore', 'creativita'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0103: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0103: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 104) {
    throw new RuntimeException(
        'RULE-0103: attese 104 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0103 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
