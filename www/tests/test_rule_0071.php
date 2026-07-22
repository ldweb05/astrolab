<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId = 'CONDITION_TEST_JUPITER_HOUSE_6';

$result = (new AARuleEngine())->evaluate([
    'giove' => [
        'condition_id' => $conditionId,
        'planet' => 'giove',
        'house' => 6,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $e): bool =>
        ($e['rule_id'] ?? '') === 'RULE-0071'
));

if (count($evidences) !== 2) {
    throw new RuntimeException('RULE-0071: numero evidenze errato');
}

$themes = array_map(
    static fn(array $e): string => (string)($e['theme'] ?? ''),
    $evidences
);

sort($themes);

$expected = ['lavoro', 'salute'];

if ($themes !== $expected) {
    throw new RuntimeException('RULE-0071: temi errati');
}

foreach ($evidences as $e) {
    if (($e['condition_id'] ?? '') !== $conditionId) {
        throw new RuntimeException('RULE-0071: condition_id non propagata');
    }
}

if (count(RuleRegistry::all()) !== 120) {
    throw new RuntimeException('RULE-0071: numero Rule errato');
}

echo "RULE-0071 TEST OK\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".count(RuleRegistry::all())."\n";
