<?php
declare(strict_types=1);

function costruisciTemaRS(array $pianetiConCase, array $caseRS, float $latA, float $lonA): array
{
    return [
    'pianeti' => $pianetiConCase,
    'case'    => $caseRS,
    'lat'     => $latA,
    'lon'     => $lonA,
           ];
}