<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_NEPTUNE_HOUSE_4';

$result = (new AARuleEngine())->evaluate([
    'nettuno' => [
        'condition_id' => $conditionId,
        'planet' => 'nettuno',
        'house' => 4,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0102'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0102: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['casa', 'famiglia'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0102: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0102: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 102) {
    throw new RuntimeException(
        'RULE-0102: attese 102 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0102 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
