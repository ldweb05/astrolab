<?php
declare(strict_types=1);
function assegnaCaseAiPianeti(array $pianetiRS, array $caseRS, $swe): array
{
    $pianetiConCase = $pianetiRS;
    foreach ($pianetiConCase as $id => $p) 
        {
            $pianetiConCase[$id]['casa'] = $swe->trovaCasaPublic
            (
                $p['longitudine'],
                $caseRS
            );
        }
        return $pianetiConCase;
}