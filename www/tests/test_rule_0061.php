<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_MARS_HOUSE_8';

$result = (new AARuleEngine())->evaluate([
    'marte' => [
        'condition_id' => $conditionId,
        'planet' => 'marte',
        'house' => 8,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0061'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0061: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['prove', 'trasformazione'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0061: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0061: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 62) {
    throw new RuntimeException(
        'RULE-0061: attese 62 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0061 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
