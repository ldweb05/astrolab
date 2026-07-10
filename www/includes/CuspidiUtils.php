<?php

/** Converte longitudine decimale (0-360) in [segno(1-12), gradi(0-29), minuti(0-59), secondi] */
function lon2Sgm(float $lon): array {
    $lon   = fmod($lon + 360.0, 360.0);
    $segno = intval($lon / 30) + 1;
    $resto = fmod($lon, 30.0);
    $g     = intval($resto);
    $minF  = ($resto - $g) * 60.0;
    $min   = intval($minF);
    $sec   = intval(round(($minF - $min) * 60.0));
    if ($sec >= 60) { $sec = 0; $min++; }
    if ($min >= 60) { $min = 0; $g++;  }

    return ['segno'=>$segno, 'gradi'=>$g, 'minuti'=>$min, 'secondi'=>$sec];
}

function distanzaDalTarget(array $pos, int $gTarget, int $mTarget): float {
    return abs($pos['gradi'] - $gTarget) + abs($pos['minuti'] - $mTarget) / 60.0;
}
