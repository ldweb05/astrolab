<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/ForecastEngineV3.php';
require_once __DIR__.'/../includes/forecast/RuleRegistry.php';

const MAX_BOUNDARY_CASE_SECONDS = 3.0;

$planets = [
    'Sole',
    'Luna',
    'Mercurio',
    'Venere',
    'Marte',
    'Giove',
    'Saturno',
    'Urano',
    'Nettuno',
    'Plutone',
];

$cases = [
    'all_house_1' => 1,
    'all_house_12' => 12,
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

foreach ($cases as $caseId => $house) {
    $temaRS = ['pianeti' => []];

    foreach ($planets as $planet) {
        $temaRS['pianeti'][$planet] = [
            'casa' => $house,
        ];
    }

    $start = hrtime(true);
    $result = $engine->generate($temaRS);
    $elapsed = (hrtime(true) - $start) / 1_000_000_000;

    if ($elapsed > MAX_BOUNDARY_CASE_SECONDS) {
        fwrite(
            STDERR,
            sprintf(
                "%s: tempo %.4fs oltre il limite %.2fs\n",
                $caseId,
                $elapsed,
                MAX_BOUNDARY_CASE_SECONDS
            )
        );
        exit(1);
    }

    $report = $result['annual_report'] ?? null;
    $validation = $result['report_validation'] ?? null;
    $conditions = $result['planet_conditions'] ?? null;

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
            "{$caseId}: validazione narrativa fallita\n"
        );
        exit(1);
    }

    $sections = $report['sections'] ?? null;

    if (!is_array($sections) || count($sections) !== 12) {
        fwrite(
            STDERR,
            "{$caseId}: attese 12 sezioni, trovate "
            .(is_array($sections) ? count($sections) : 0)
            ."\n"
        );
        exit(1);
    }

    foreach ($sections as $index => $section) {
        if (
            !is_array($section)
            || trim((string)($section['id'] ?? '')) === ''
            || trim((string)($section['title'] ?? '')) === ''
            || trim((string)($section['text'] ?? '')) === ''
        ) {
            fwrite(
                STDERR,
                "{$caseId}: sezione {$index} non valida\n"
            );
            exit(1);
        }
    }

    if (!is_array($conditions)) {
        fwrite(
            STDERR,
            "{$caseId}: planet_conditions mancante\n"
        );
        exit(1);
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

    echo sprintf(
        "%s OK: casa %d, %d sezioni, %d parole, %.4fs\n",
        strtoupper($caseId),
        $house,
        count($sections),
        (int)($validation['word_count'] ?? 0),
        $elapsed
    );
}

echo "FORECAST HOUSE BOUNDARIES OK\n";
