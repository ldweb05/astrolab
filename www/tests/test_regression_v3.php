<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/AnnualForecastEngine.php';
require_once __DIR__.'/../includes/forecast/RuleRegistry.php';

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

$registeredRules = RuleRegistry::all();
$allRulesValid = true;

foreach ($registeredRules as $rule) {
    if (!$rule instanceof AstrologyRuleInterface) {
        $allRulesValid = false;
        break;
    }
}

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
$hasMarsSaturnInSalute = false;
$hasMarsSaturnInProve = false;
$hasRule0002InProve = false;
$hasRule0003InSalute = false;
$hasRule0004InAmore = false;
$hasRule0004InCreativita = false;
$hasRule0005InStudio = false;
$hasRule0006InCasa = false;
$hasRule0006InFamiglia = false;
$hasRule0007InAmicizie = false;
$hasRule0008InSpiritualita = false;
$hasRule0009InTrasformazione = false;
$hasRule0010InCarriera = false;

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
    }

    if (
        ($evidence['code'] ?? '') ===
        'COMPOSITE_MARS6_SATURN12'
    ) {
        $hasMarsSaturnInProve = true;
    }
}

foreach (($evidencesByTheme['salute'] ?? []) as $evidence) {
    if (
        ($evidence['code'] ?? '') ===
        'COMPOSITE_MARS6_SATURN12'
    ) {
        $hasMarsSaturnInSalute = true;
        break;
    }
}

foreach (($evidencesByTheme['salute'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0003') {
        $hasRule0003InSalute = true;
        break;
    }
}

foreach (($evidencesByTheme['amore'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0004') {
        $hasRule0004InAmore = true;
        break;
    }
}

foreach (($evidencesByTheme['creativita'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0004') {
        $hasRule0004InCreativita = true;
        break;
    }
}

foreach (($evidencesByTheme['studio'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0005') {
        $hasRule0005InStudio = true;
        break;
    }
}

foreach (($evidencesByTheme['casa'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0006') {
        $hasRule0006InCasa = true;
        break;
    }
}

foreach (($evidencesByTheme['famiglia'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0006') {
        $hasRule0006InFamiglia = true;
        break;
    }
}

foreach (($evidencesByTheme['amicizie'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0007') {
        $hasRule0007InAmicizie = true;
        break;
    }
}

foreach (($evidencesByTheme['spiritualita'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0008') {
        $hasRule0008InSpiritualita = true;
        break;
    }
}

foreach (($evidencesByTheme['trasformazione'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0009') {
        $hasRule0009InTrasformazione = true;
        break;
    }
}

foreach (($evidencesByTheme['carriera'] ?? []) as $evidence) {
    if (($evidence['rule_id'] ?? '') === 'RULE-0010') {
        $hasRule0010InCarriera = true;
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
    'rule_0004_amore' => $hasRule0004InAmore,
    'rule_0004_creativita' => $hasRule0004InCreativita,
    'rule_0005_studio' => $hasRule0005InStudio,
    'rule_0006_casa' => $hasRule0006InCasa,
    'rule_0006_famiglia' => $hasRule0006InFamiglia,
    'rule_0007_amicizie' => $hasRule0007InAmicizie,
    'rule_0008_spiritualita' => $hasRule0008InSpiritualita,
    'rule_0009_trasformazione' => $hasRule0009InTrasformazione,
    'rule_0010_carriera' => $hasRule0010InCarriera,
    'composite_theme_link' => $hasCompositeInCareer,
    'registered_rules' => count($registeredRules),
    'rules_contract' => $allRulesValid,
    'mars_saturn_salute' => $hasMarsSaturnInSalute,
    'mars_saturn_prove' => $hasMarsSaturnInProve,
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
    !$checks['rule_0004_amore'] ||
    !$checks['rule_0004_creativita'] ||
    !$checks['rule_0005_studio'] ||
    !$checks['rule_0006_casa'] ||
    !$checks['rule_0006_famiglia'] ||
    !$checks['rule_0007_amicizie'] ||
    !$checks['rule_0008_spiritualita'] ||
    !$checks['rule_0009_trasformazione'] ||
    !$checks['rule_0010_carriera'] ||
    !$checks['composite_theme_link'] ||
    $checks['registered_rules'] < 10 ||
    !$checks['rules_contract'] ||
    !$checks['mars_saturn_salute'] ||
    !$checks['mars_saturn_prove']
) {
    fwrite(STDERR,"REGRESSION TEST FAILED\n");
    exit(1);
}

echo PHP_EOL."FULL REGRESSION OK".PHP_EOL;
