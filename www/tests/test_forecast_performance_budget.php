<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ForecastEngineV3.php';
require_once __DIR__.'/../includes/forecast/RuleRegistry.php';

const MAX_CASE_SECONDS = 3.0;
const MAX_TOTAL_SECONDS = 8.0;
const MAX_PEAK_MEMORY_BYTES = 128 * 1024 * 1024;

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

$registeredRules = RuleRegistry::all();

if (count($registeredRules) !== 120) {
    fwrite(
        STDERR,
        'Rule Registry non valido: '
        .count($registeredRules)
        ." Rule\n"
    );
    exit(1);
}

$engine = new ForecastEngineV3();
$totalStart = hrtime(true);
$measurements = [];

foreach ($cases as $caseId => $temaRS) {
    $caseStart = hrtime(true);
    $result = $engine->generate($temaRS);
    $elapsedSeconds = (
        hrtime(true) - $caseStart
    ) / 1_000_000_000;

    $report = $result['annual_report'] ?? null;
    $validation = $result['report_validation'] ?? null;

    if (!is_array($report)) {
        fwrite(
            STDERR,
            "{$caseId}: annual_report mancante\n"
        );
        exit(1);
    }

    if (
        !is_array($validation)
        || ($validation['valid'] ?? false) !== true
    ) {
        fwrite(
            STDERR,
            "{$caseId}: validazione report fallita\n"
        );
        exit(1);
    }

    if (count($report['sections'] ?? []) !== 12) {
        fwrite(
            STDERR,
            "{$caseId}: numero sezioni inatteso\n"
        );
        exit(1);
    }

    if ($elapsedSeconds > MAX_CASE_SECONDS) {
        fwrite(
            STDERR,
            sprintf(
                "%s: tempo %.4fs oltre il limite %.2fs\n",
                $caseId,
                $elapsedSeconds,
                MAX_CASE_SECONDS
            )
        );
        exit(1);
    }

    $measurements[$caseId] = $elapsedSeconds;
}

$totalSeconds = (
    hrtime(true) - $totalStart
) / 1_000_000_000;

if ($totalSeconds > MAX_TOTAL_SECONDS) {
    fwrite(
        STDERR,
        sprintf(
            "Tempo totale %.4fs oltre il limite %.2fs\n",
            $totalSeconds,
            MAX_TOTAL_SECONDS
        )
    );
    exit(1);
}

$peakMemory = memory_get_peak_usage(true);

if ($peakMemory > MAX_PEAK_MEMORY_BYTES) {
    fwrite(
        STDERR,
        sprintf(
            "Memoria di picco %.2f MB oltre il limite %.2f MB\n",
            $peakMemory / 1024 / 1024,
            MAX_PEAK_MEMORY_BYTES / 1024 / 1024
        )
    );
    exit(1);
}

foreach ($measurements as $caseId => $seconds) {
    echo sprintf(
        "%s PERFORMANCE OK: %.4fs\n",
        strtoupper($caseId),
        $seconds
    );
}

echo sprintf(
    "FORECAST PERFORMANCE BUDGET OK: totale %.4fs, memoria %.2f MB\n",
    $totalSeconds,
    $peakMemory / 1024 / 1024
);
