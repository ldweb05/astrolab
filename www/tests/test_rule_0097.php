<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_URANUS_HOUSE_10';

$result = (new AARuleEngine())->evaluate([
    'urano' => [
        'condition_id' => $conditionId,
        'planet' => 'urano',
        'house' => 10,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0097'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0097: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['carriera', 'innovazione'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0097: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0097: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 98) {
    throw new RuntimeException(
        'RULE-0097: attese 98 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0097 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
