<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_SUN_HOUSE_12';

$result = (new AARuleEngine())->evaluate([
    'sole' => [
        'condition_id' => $conditionId,
        'planet' => 'sole',
        'house' => 12,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0021'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0021: attese 2 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

if ($themes !== ['introspezione', 'spiritualita']) {
    throw new RuntimeException(
        'RULE-0021: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0021: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 21) {
    throw new RuntimeException(
        'RULE-0021: attese 21 Rule registrate, trovate '.$registeredRules
    );
}

echo "RULE-0021 TEST OK\n";
echo "evidences       : ".count($evidences)."\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
