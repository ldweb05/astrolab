<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_NEPTUNE_HOUSE_11';

$result = (new AARuleEngine())->evaluate([
    'nettuno' => [
        'condition_id' => $conditionId,
        'planet' => 'nettuno',
        'house' => 11,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0108'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0108: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['amicizie', 'progetti'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0108: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0108: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 108) {
    throw new RuntimeException(
        'RULE-0108: attese 108 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0108 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
