<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_MERCURIO_HOUSE_12';

$result = (new AARuleEngine())->evaluate([
    'mercurio' => [
        'condition_id' => $conditionId,
        'planet' => 'mercurio',
        'house' => 12,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0043'
));

if (count($evidences) !== 1) {
    throw new RuntimeException(
        'RULE-0043: numero evidenze errato'
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['introspezione'];

if ($themes !== $expected) {
    throw new RuntimeException(
        'RULE-0043: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (($evidence['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException(
            'RULE-0043: condition_id non propagata'
        );
    }
}

$registeredRules = count(RuleRegistry::all());

if ($registeredRules !== 44) {
    throw new RuntimeException(
        'RULE-0043: attese 44 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0043 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
