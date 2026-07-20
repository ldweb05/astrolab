<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_MARS_HOUSE_10';

$result = (new AARuleEngine())->evaluate([
    'marte' => [
        'condition_id' => $conditionId,
        'planet' => 'marte',
        'house' => 10,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0063'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0063: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['carriera', 'conflitti'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0063: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0063: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 64) {
    throw new RuntimeException(
        'RULE-0063: attese 64 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0063 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
