<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_PLUTO_HOUSE_5';

$result = (new AARuleEngine())->evaluate([
    'plutone' => [
        'condition_id' => $conditionId,
        'planet' => 'plutone',
        'house' => 5,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0114'
));

if (count($evidences) !== 3) {
    throw new RuntimeException(
        'RULE-0114: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['amore', 'creativita', 'figli'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0114: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0114: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 114) {
    throw new RuntimeException(
        'RULE-0114: attese 114 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0114 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
