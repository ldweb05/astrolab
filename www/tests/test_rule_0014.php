<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/forecast/AARuleEngine.php';

$result=(new AARuleEngine())->evaluate([
'sole'=>[
'condition_id'=>'COND_TEST',
'planet'=>'sole',
'house'=>4,
'strength'=>1.0,
]]);

$e=array_values(array_filter(
$result['evidences'],
fn($x)=>($x['rule_id']??'')==='RULE-0014'
));

if(count($e)!==2){
throw new RuntimeException('RULE0014');
}

$t=array_map(fn($x)=>$x['theme'],$e);
sort($t);

if($t!==['casa','famiglia']){
throw new RuntimeException('THEMES');
}

echo "RULE-0014 TEST OK\n";
