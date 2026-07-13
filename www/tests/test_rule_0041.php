<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$conditionId='CONDITION_TEST_MERCURY_HOUSE_10';

$result=(new AARuleEngine())->evaluate([
    'mercurio'=>[
        'condition_id'=>$conditionId,
        'planet'=>'mercurio',
        'house'=>10,
        'strength'=>1.0,
    ],
]);

$evidences=array_values(array_filter(
    $result['evidences']??[],
    static fn(array $e)=>($e['rule_id']??'')==='RULE-0041'
));

if(count($evidences)!==2){
    throw new RuntimeException('RULE-0041: evidenze errate');
}

$themes=array_map(
    static fn(array $e)=>(string)$e['theme'],
    $evidences
);

sort($themes);

$expected=['carriera', 'successo'];

if($themes!==$expected){
    throw new RuntimeException(
        'RULE-0041: temi errati: '.implode(', ',$themes)
    );
}

foreach($evidences as $e){
    if(($e['condition_id']??'')!==$conditionId){
        throw new RuntimeException(
            'RULE-0041: condition_id non propagata'
        );
    }
}

$registeredRules=count(RuleRegistry::all());

if($registeredRules!==42){
    throw new RuntimeException(
        'RULE-0041: attese 42 Rule registrate, trovate '
        .$registeredRules
    );
}

echo "RULE-0041 TEST OK\n";
echo "themes          : ".implode(', ',$themes)."\n";
echo "registered_rules: ".$registeredRules."\n";
