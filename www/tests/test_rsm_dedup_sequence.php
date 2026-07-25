<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/RicercaRSAirportRepository.php';
require __DIR__ . '/../includes/RicercaRSDeduplicator.php';
$pdo=db_connect();
$pdo->exec("SET statement_timeout = '120s'");
$where=['attivo = true',"tipo IN ('large_airport','medium_airport','small_airport')",'nazione IN (?,?)','longitudine >= ?','longitudine <= ?'];
$params=['IT','FR',-6.0,19.0];
$bucketLat=0.3;
$bucketLon=0.5;
$grezzi=recuperaAeroporti($pdo,$where,$params);
$php=deduplicaAeroporti($grezzi,$bucketLat,$bucketLon);
$sql=recuperaAeroportiDeduplicati($pdo,$where,$params,$bucketLat,$bucketLon)['aeroporti'];
$norm=static fn(array $r):array=>[$r['icao_code']??null,$r['iata_code']??null,$r['nome']??null,$r['citta']??null,$r['nazione']??null,(string)$r['latitudine'],(string)$r['longitudine']];
$bucket=static fn(array $r):string=>round((float)$r['latitudine']/$bucketLat).':'.round((float)$r['longitudine']/$bucketLon);
$limit=min(count($php),count($sql));
for($i=0;$i<$limit;$i++){
 if($norm($php[$i])!==$norm($sql[$i])){
  echo "PRIMA DIVERGENZA: indice {$i}\n";
  echo "PHP bucket: ".$bucket($php[$i])."\n";
  echo json_encode($norm($php[$i]),JSON_UNESCAPED_UNICODE)."\n";
  echo "SQL bucket: ".$bucket($sql[$i])."\n";
  echo json_encode($norm($sql[$i]),JSON_UNESCAPED_UNICODE)."\n";
  exit(1);
 }
}
echo "Sequenze identiche; PHP=".count($php)." SQL=".count($sql)."\n";
