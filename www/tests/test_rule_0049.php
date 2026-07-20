<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_VENUS_HOUSE_7';

$result = (new AARuleEngine())->evaluate([
    'venere' => [
        'condition_id' => $conditionId,
        'planet' => 'venere',
        'house' => 7,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0049'
));

if (count($evidences) !== 3) {
    throw new RuntimeException(
        'RULE-0049: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['matrimonio', 'relazioni', 'societa'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0049: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0049: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 50) {
    throw new RuntimeException(
        'RULE-0049: attese 50 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0049 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
