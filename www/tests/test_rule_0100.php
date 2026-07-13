<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_NEPTUNE_HOUSE_2';

$result = (new AARuleEngine())->evaluate([
    'nettuno' => [
        'condition_id' => $conditionId,
        'planet' => 'nettuno',
        'house' => 2,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0100'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0100: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['confusione', 'denaro'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0100: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0100: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 100) {
    throw new RuntimeException(
        'RULE-0100: attese 100 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0100 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
