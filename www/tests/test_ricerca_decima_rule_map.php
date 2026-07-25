<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/RicercaRSFilters.php';

function assertSameValue(
    mixed $expected,
    mixed $actual,
    string $message
): void {
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message
            ."\nAtteso: ".var_export($expected, true)
            ."\nOttenuto: ".var_export($actual, true)
        );
    }
}

$ruleMap = getRuleMapEsclusione('Decima');

assertSameValue(
    [4, 6, 7, 8, 9],
    $ruleMap['malevoli'] ?? null,
    'DECIMA: elenco pianeti malevoli errato'
);

assertSameValue(
    [10],
    $ruleMap['case'] ?? null,
    'DECIMA: elenco case vietate errato'
);

/*
 * Scenario segnalato per Atlanta:
 * Sole e Giove in X casa, nessun malevolo in X.
 */
$atlantaScenario = [
    0 => ['nome' => 'Sole',    'casa' => 10],
    1 => ['nome' => 'Luna',    'casa' => 9],
    2 => ['nome' => 'Mercurio','casa' => 9],
    3 => ['nome' => 'Venere',  'casa' => 11],
    4 => ['nome' => 'Marte',   'casa' => 8],
    5 => ['nome' => 'Giove',   'casa' => 10],
    6 => ['nome' => 'Saturno', 'casa' => 7],
    7 => ['nome' => 'Urano',   'casa' => 6],
    8 => ['nome' => 'Nettuno', 'casa' => 5],
    9 => ['nome' => 'Plutone', 'casa' => 4],
];

assertSameValue(
    false,
    escludiPerRuleMap($atlantaScenario, 'Decima'),
    'DECIMA: Sole e Giove in X sono stati esclusi erroneamente'
);

$malevoli = [
    4 => 'Marte',
    6 => 'Saturno',
    7 => 'Urano',
    8 => 'Nettuno',
    9 => 'Plutone',
];

foreach ($malevoli as $planetId => $planetName) {
    $scenario = $atlantaScenario;
    $scenario[$planetId]['casa'] = 10;

    assertSameValue(
        true,
        escludiPerRuleMap($scenario, 'Decima'),
        "DECIMA: {$planetName} in X non è stato escluso"
    );

    $scenario[$planetId]['casa'] = 11;

    assertSameValue(
        false,
        escludiPerRuleMap($scenario, 'Decima'),
        "DECIMA: {$planetName} in XI è stato escluso erroneamente"
    );
}

echo "RICERCA DECIMA RULE MAP TEST OK\n";
echo "Sole in X   : consentito\n";
echo "Giove in X  : consentito\n";

foreach ($malevoli as $planetName) {
    echo str_pad($planetName." in X", 14).": escluso\n";
}
