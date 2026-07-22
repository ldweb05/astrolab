<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$result = (new AARuleEngine())->evaluate([
    'sole' => [
        'condition_id' => 'CONDITION_TEST_SUN_HOUSE_11',
        'planet' => 'sole',
        'house' => 11,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0020'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0020: attese 2 evidenze, trovate '.count($evidences)
    );
}

$themes = array_map(
    static fn(array $evidence): string =>
        (string)($evidence['theme'] ?? ''),
    $evidences
);

sort($themes);

if ($themes !== ['amicizie', 'progetti']) {
    throw new RuntimeException(
        'RULE-0020: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (
        ($evidence['condition_id'] ?? '')
        !== 'CONDITION_TEST_SUN_HOUSE_11'
    ) {
        throw new RuntimeException(
            'RULE-0020: condition_id non propagata'
        );
    }
}

if (count(RuleRegistry::all()) !== 120) {
    throw new RuntimeException(
        'RULE-0020: attese 120 Rule registrate'
    );
}

echo "RULE-0020 TEST OK\n";
echo "evidences       : ".count($evidences)."\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".count(RuleRegistry::all())."\n";
