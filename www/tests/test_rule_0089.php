<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_URANUS_HOUSE_2';

$result = (new AARuleEngine())->evaluate([
    'urano' => [
        'condition_id' => $conditionId,
        'planet' => 'urano',
        'house' => 2,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0089'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0089: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['denaro', 'imprevisti'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0089: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0089: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 90) {
    throw new RuntimeException(
        'RULE-0089: attese 90 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0089 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
