<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_PLUTO_HOUSE_3';

$result = (new AARuleEngine())->evaluate([
    'plutone' => [
        'condition_id' => $conditionId,
        'planet' => 'plutone',
        'house' => 3,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0112'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0112: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['comunicazione', 'studio'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0112: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0112: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 112) {
    throw new RuntimeException(
        'RULE-0112: attese 112 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0112 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
