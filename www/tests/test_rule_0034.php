<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId='CONDITION_TEST_MERCURY_HOUSE_2';

$result=(new AARuleEngine())->evaluate([
    'mercurio'=>[
        'condition_id'=>$conditionId,
        'planet'=>'mercurio',
        'house'=>2,
        'strength'=>1.0,
    ],
]);

$evidences=array_values(array_filter(
    $result['evidences']??[],
    static fn(array $e)=>($e['rule_id']??'')==='RULE-0034'
));

if(count($evidences)!==2){
    throw new RuntimeException('RULE-0034: evidenze errate');
}

$themes=array_map(
    static fn(array $e)=>(string)$e['theme'],
    $evidences
);

sort($themes);

if($themes!==['affari', 'denaro']){
    throw new RuntimeException('RULE-0034: temi errati');
}

foreach($evidences as $e){
    if(($e['condition_id']??'')!==$conditionId){
        throw new RuntimeException('RULE-0034: condition_id errata');
    }
}

$registeredRules=count(RuleRegistry::all());

if($registeredRules!==34){
    throw new RuntimeException('RULE-0034: registry errato');
}

echo "RULE-0034 TEST OK\n";
echo "themes          : ".implode(', ',$themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
