<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_URANUS_HOUSE_4';

$result = (new AARuleEngine())->evaluate([
    'urano' => [
        'condition_id' => $conditionId,
        'planet' => 'urano',
        'house' => 4,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0091'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0091: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['casa', 'traslochi'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0091: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0091: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 92) {
    throw new RuntimeException(
        'RULE-0091: attese 92 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0091 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
