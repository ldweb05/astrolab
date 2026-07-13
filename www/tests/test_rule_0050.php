<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_VENUS_HOUSE_8';

$result = (new AARuleEngine())->evaluate([
    'venere' => [
        'condition_id' => $conditionId,
        'planet' => 'venere',
        'house' => 8,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0050'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0050: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['denaro', 'trasformazione'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0050: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0050: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 50) {
    throw new RuntimeException(
        'RULE-0050: attese 50 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0050 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
