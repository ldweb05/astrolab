<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$result = (new AARuleEngine())->evaluate([
    'sole' => [
        'condition_id' => 'CONDITION_TEST_SUN_HOUSE_8',
        'planet' => 'sole',
        'house' => 8,
        'strength' => 1.0,
    ],
]);

$evidences = array_values(array_filter(
    $result['evidences'] ?? [],
    static fn(array $evidence): bool =>
        ($evidence['rule_id'] ?? '') === 'RULE-0018'
));

if (count($evidences) !== 2) {
    throw new RuntimeException(
        'RULE-0018: attese 2 evidenze, trovate '.count($evidences)
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
        'RULE-0018: temi errati: '.implode(', ', $themes)
    );
}

foreach ($evidences as $evidence) {
    if (
        ($evidence['condition_id'] ?? '')
        !== 'CONDITION_TEST_SUN_HOUSE_8'
    ) {
        throw new RuntimeException(
            'RULE-0018: condition_id non propagata'
        );
    }
}

$registered = RuleRegistry::all();

if (count($registered) !== 18) {
    throw new RuntimeException(
        'Attese 18 Rule registrate, trovate '.count($registered)
    );
}

echo "RULE-0018 TEST OK\n";
echo "evidences       : ".count($evidences)."\n";
echo "themes          : ".implode(', ', $themes)."\n";
echo "registered_rules: ".count($registered)."\n";
