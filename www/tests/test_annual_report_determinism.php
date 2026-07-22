<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ForecastEngineV3.php';

$temaRS = [
    'pianeti' => [
        'Sole' => ['casa' => 10],
        'Luna' => ['casa' => 4],
        'Mercurio' => ['casa' => 3],
        'Venere' => ['casa' => 5],
        'Marte' => ['casa' => 6],
        'Giove' => ['casa' => 10],
        'Saturno' => ['casa' => 12],
        'Urano' => ['casa' => 11],
        'Nettuno' => ['casa' => 9],
        'Plutone' => ['casa' => 8],
    ],
];

$engine = new ForecastEngineV3();

$first = $engine->generate($temaRS);
$second = $engine->generate($temaRS);

$contracts = [
    'scores',
    'polarities',
    'theme_profiles',
    'summary',
    'report_outline',
    'annual_report',
    'report_validation',
    'planet_conditions',
    'rule_evidences',
    'contributions',
    'normalized_contributions',
    'evidence_groups',
    'evidences',
    'formatted_evidences',
];

foreach ($contracts as $contract) {
    if (!array_key_exists($contract, $first)) {
        fwrite(
            STDERR,
            "Contratto mancante nella prima esecuzione: {$contract}\n"
        );
        exit(1);
    }

    if (!array_key_exists($contract, $second)) {
        fwrite(
            STDERR,
            "Contratto mancante nella seconda esecuzione: {$contract}\n"
        );
        exit(1);
    }

    if ($first[$contract] !== $second[$contract]) {
        fwrite(
            STDERR,
            "Output non deterministico: {$contract}\n"
        );
        exit(1);
    }
}

$firstJson = json_encode(
    array_intersect_key(
        $first,
        array_flip($contracts)
    ),
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_PRESERVE_ZERO_FRACTION
);

$secondJson = json_encode(
    array_intersect_key(
        $second,
        array_flip($contracts)
    ),
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_PRESERVE_ZERO_FRACTION
);

if ($firstJson === false || $secondJson === false) {
    fwrite(STDERR, "Serializzazione deterministica fallita\n");
    exit(1);
}

if (!hash_equals(
    hash('sha256', $firstJson),
    hash('sha256', $secondJson)
)) {
    fwrite(STDERR, "Hash deterministico non coerente\n");
    exit(1);
}

$report = $first['annual_report'] ?? [];
$validation = $first['report_validation'] ?? [];

if (($validation['valid'] ?? false) !== true) {
    fwrite(STDERR, "Report deterministico non valido\n");
    exit(1);
}

if (
    (int)($report['word_count'] ?? 0)
    !== (int)($validation['word_count'] ?? -1)
) {
    fwrite(
        STDERR,
        "Conteggio parole incoerente tra report e validazione\n"
    );
    exit(1);
}

echo sprintf(
    "ANNUAL REPORT DETERMINISM OK: %s\n",
    hash('sha256', $firstJson)
);
