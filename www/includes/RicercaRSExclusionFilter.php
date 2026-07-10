<?php
declare(strict_types=1);

function valutaEsclusioneFiltroRS(array $pianetiConCase, array $caseRS, array $temaNatale): array
{
    $motiviEsclusioneFiltro = verificaEsclusioneRS(
    $pianetiConCase,
    $caseRS,
    $temaNatale
);

return [
    'esclusa' => !empty($motiviEsclusioneFiltro),
    'motivi'  => $motiviEsclusioneFiltro,
];
}