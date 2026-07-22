<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_JUPITER_HOUSE_7';

$result = (new AARuleEngine())->evaluate([
    'giove' => [
        'condition_id' => $conditionId,
        'planet' => 'giove',
        'house' => 7,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $e): bool =>
        ($e['rule_id'] ?? '') === 'RULE-0072'
));

if (count($evidences) !== 2) {
    throw new RuntimeException('RULE-0072: numero evidenze errato');
}

$themes = array_map(
    static fn(array $e): string => (string)($e['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['matrimonio', 'relazioni'];

if ($themes !== $expected) {
    throw new RuntimeException('RULE-0072: temi errati');
}

foreach ($evidences as $e) {
    if (($e['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException('RULE-0072: condition_id non propagata');
    }
}

if (count(RuleRegistry::all()) !== 120) {
    throw new RuntimeException('RULE-0072: numero Rule errato');
}

echo "RULE-0072 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".count(RuleRegistry::all())."\n";
