<?php
declare(strict_types=1);
function deduplicaAeroporti(array $aeroporti, float $bucketLat, float $bucketLon): array
{
    $buckets = [];
    $selezionati = [];
    foreach ($aeroporti as $aero) 
        {
            $bLat = round(floatval($aero['latitudine']) / $bucketLat);
            $bLon = round(floatval($aero['longitudine']) / $bucketLon);
            $key = "{$bLat}:{$bLon}";
            if (!isset($buckets[$key])) 
                {
            $buckets[$key] = true;
            $selezionati[] = $aero;
                }
        }
    return $selezionati;
}