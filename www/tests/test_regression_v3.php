<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/AnnualForecastEngine.php';

$tema = [
    'pianeti' => [
        'Sole'      => ['casa'=>10],
        'Giove'     => ['casa'=>10],
        'Venere'    => ['casa'=>5],
        'Mercurio'  => ['casa'=>3],
        'Marte'     => ['casa'=>6],
        'Luna'      => ['casa'=>4],
        'Saturno'   => ['casa'=>12],
        'Urano'     => ['casa'=>11],
        'Nettuno'   => ['casa'=>9],
        'Plutone'   => ['casa'=>8],
    ],
];

$data = (new AnnualForecastEngine())->genera($tema);

$report = $data['relazione_annuale'] ?? [];
$formattedEvidences = $data['formatted_evidences'] ?? [];
$planetConditions = $data['planet_conditions'] ?? [];

$hasConditionIds = true;

foreach ($planetConditions as $condition) {
    if (empty($condition['condition_id'])) {
        $hasConditionIds = false;
        break;
    }
}
$evidencesByTheme = $report['evidences_by_theme'] ?? [];
$sections = $report['sections'] ?? [];

$careerEvidences = $evidencesByTheme['carriera'] ?? [];

$hasRule0001InCareer = false;
$hasCompositeInCareer = false;
$hasRule0002InProve = false;
$hasRule0003InSalute = false;

foreach ($careerEvidences as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0001') {
        $hasRule0001InCareer = true;
    }

    if (
        ($evidence['code'] ?? '') ===
        'COMPOSITE_SUN_JUPITER_SAME_HOUSE'
    ) {
        $hasCompositeInCareer = true;
    }
}

foreach (($evidencesByTheme['prove'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0002') {
        $hasRule0002InProve = true;
        break;
    }
}

foreach (($evidencesByTheme['salute'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0003') {
        $hasRule0003InSalute = true;
        break;
    }
}
$explainability = $report['explainability'] ?? [];

$meaningEvidenceIds =
    $explainability['sections']['meaning_of_year']['evidence_ids']
    ?? [];

$meaningConditionIds =
    $explainability['sections']['meaning_of_year']['condition_ids']
    ?? [];

$meaningRuleIds =
    $explainability['sections']['meaning_of_year']['rule_ids']
    ?? [];

$hasEvidenceIds = count($meaningEvidenceIds) >= 1;
$hasConditionTrace = count($meaningConditionIds) >= 1;

$hasCompositeRuleId = in_array(
    'RULE_COMPOSITE_SUN_JUPITER_SAME_HOUSE',
    $meaningRuleIds,
    true
);

$compositeConditionIds = [];

foreach ($formattedEvidences as $evidence) {
    if (
        ($evidence['code'] ?? '') ===
        'COMPOSITE_SUN_JUPITER_SAME_HOUSE'
    ) {
        $compositeConditionIds = $evidence['condition_ids'] ?? [];
        break;
    }
}

$hasCompositeConditionTrace =
    count(array_unique($compositeConditionIds)) === 2;

$sectionsWithEvidences = 0;

foreach ($sections as $section) {
    if (count($section['evidences'] ?? []) > 0) {
        $sectionsWithEvidences++;
    }
}

$hasSunJupiterComposite = false;

foreach ($formattedEvidences as $evidence) {
    if (
        ($evidence['code'] ?? '') ===
        'COMPOSITE_SUN_JUPITER_SAME_HOUSE'
    ) {
        $hasSunJupiterComposite = true;
        break;
    }
}

$checks = [
    'sections'      => count($report['sections'] ?? []),
    'word_count'    => (int)($report['word_count'] ?? 0),
    'evidences'     => count($report['evidences'] ?? []),
    'valid'         => (bool)($data['report_validation']['valid'] ?? false),
    'composite'     => $hasSunJupiterComposite,
    'theme_groups'  => count($evidencesByTheme),
    'section_links' => $sectionsWithEvidences,
    'explainability' => count($explainability['sections'] ?? []),
    'rule_trace'     => $hasCompositeRuleId,
    'evidence_trace' => $hasEvidenceIds,
    'condition_trace' => $hasConditionTrace,
    'composite_conditions' => $hasCompositeConditionTrace,
    'conditions'     => count($planetConditions),
    'condition_ids'  => $hasConditionIds,
    'rule_theme_link' => $hasRule0001InCareer,
    'rule_0002_link' => $hasRule0002InProve,
    'rule_0003_link' => $hasRule0003InSalute,
    'composite_theme_link' => $hasCompositeInCareer,
];

foreach ($checks as $k=>$v) {
    echo str_pad($k,18).": ".$v.PHP_EOL;
}

if (
    $checks['sections'] < 10 ||
    $checks['word_count'] < 900 ||
    $checks['evidences'] < 10 ||
    !$checks['valid'] ||
    !$checks['composite'] ||
    $checks['theme_groups'] < 10 ||
    $checks['section_links'] < 8 ||
    $checks['explainability'] < 8 ||
    !$checks['rule_trace'] ||
    !$checks['evidence_trace'] ||
    !$checks['condition_trace'] ||
    !$checks['composite_conditions'] ||
    $checks['conditions'] < 10 ||
    !$checks['condition_ids'] ||
    !$checks['rule_theme_link'] ||
    !$checks['rule_0002_link'] ||
    !$checks['rule_0003_link'] ||
    !$checks['composite_theme_link']
) {
    fwrite(STDERR,"REGRESSION TEST FAILED\n");
    exit(1);
}

echo PHP_EOL."FULL REGRESSION OK".PHP_EOL;
