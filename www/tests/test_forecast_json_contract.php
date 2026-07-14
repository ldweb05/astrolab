<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ForecastEngineV3.php';

$cases = [
    'career' => [
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
    ],
    'relationships' => [
        'pianeti' => [
            'Sole' => ['casa' => 7],
            'Luna' => ['casa' => 5],
            'Mercurio' => ['casa' => 7],
            'Venere' => ['casa' => 7],
            'Marte' => ['casa' => 4],
            'Giove' => ['casa' => 5],
            'Saturno' => ['casa' => 8],
            'Urano' => ['casa' => 3],
            'Nettuno' => ['casa' => 11],
            'Plutone' => ['casa' => 1],
        ],
    ],
    'transformation' => [
        'pianeti' => [
            'Sole' => ['casa' => 8],
            'Luna' => ['casa' => 12],
            'Mercurio' => ['casa' => 8],
            'Venere' => ['casa' => 12],
            'Marte' => ['casa' => 8],
            'Giove' => ['casa' => 12],
            'Saturno' => ['casa' => 6],
            'Urano' => ['casa' => 10],
            'Nettuno' => ['casa' => 4],
            'Plutone' => ['casa' => 12],
        ],
    ],
];

$requiredContracts = [
    'scores',
    'paragraphs',
    'polarities',
    'theme_profiles',
    'summary',
    'report_outline',
    'report_draft',
    'annual_report',
    'report_validation',
    'context',
    'planet_conditions',
    'rule_evidences',
    'contributions',
    'normalized_contributions',
    'evidence_groups',
    'evidences',
    'formatted_evidences',
];

$engine = new ForecastEngineV3();

foreach ($cases as $caseId => $temaRS) {
    $result = $engine->generate($temaRS);

    foreach ($requiredContracts as $contract) {
        if (!array_key_exists($contract, $result)) {
            fwrite(
                STDERR,
                "{$caseId}: contratto JSON mancante: {$contract}\n"
            );
            exit(1);
        }
    }

    $json = json_encode(
        $result,
        JSON_THROW_ON_ERROR
        | JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_PRESERVE_ZERO_FRACTION
    );

    if (!mb_check_encoding($json, 'UTF-8')) {
        fwrite(
            STDERR,
            "{$caseId}: output JSON non UTF-8\n"
        );
        exit(1);
    }

    if (
        preg_match(
            '/(?:^|[^A-Za-z])(?:NAN|INF|-INF)(?:[^A-Za-z]|$)/',
            $json
        ) === 1
    ) {
        fwrite(
            STDERR,
            "{$caseId}: valore numerico JSON non finito\n"
        );
        exit(1);
    }

    $decoded = json_decode(
        $json,
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($decoded)) {
        fwrite(
            STDERR,
            "{$caseId}: deserializzazione JSON non valida\n"
        );
        exit(1);
    }

    foreach ($requiredContracts as $contract) {
        if (!array_key_exists($contract, $decoded)) {
            fwrite(
                STDERR,
                "{$caseId}: contratto perso dopo JSON: {$contract}\n"
            );
            exit(1);
        }
    }

    $report = $decoded['annual_report'] ?? [];
    $validation = $decoded['report_validation'] ?? [];

    if (
        !is_array($report)
        || !is_array($report['sections'] ?? null)
        || count($report['sections']) < 10
    ) {
        fwrite(
            STDERR,
            "{$caseId}: annual_report JSON non valido\n"
        );
        exit(1);
    }

    if (($validation['valid'] ?? false) !== true) {
        fwrite(
            STDERR,
            "{$caseId}: report_validation JSON non valida\n"
        );
        exit(1);
    }

    echo sprintf(
        "%s JSON OK: %d byte\n",
        strtoupper($caseId),
        strlen($json)
    );
}

echo "FORECAST JSON CONTRACT OK\n";
