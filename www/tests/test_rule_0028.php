<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_MOON_HOUSE_8';

$result = (new AARuleEngine())->evaluate([
    'luna' => [
        'condition_id' => $conditionId,
        'planet' => 'luna',
        'house' => 8,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0028'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0028: attese 2 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

if ($themes !== ['prove', 'trasformazione']) {
    throw new RuntimeException(
        'RULE-0028: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0028: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 28) {
    throw new RuntimeException(
        'RULE-0028: attese 28 Rule registrate, trovate '.$registeredRules
    );
}

echo "RULE-0028 TEST OK\n";
echo "evidences       : ".count($evidences)."\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
