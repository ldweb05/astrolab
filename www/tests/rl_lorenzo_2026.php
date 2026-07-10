<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/SweCalc.php';

$swe = new SweCalc();
$rl = $swe->calcolaTutteRLLibsweCompatibileLunaApi(
    5, 9, 1960,
    16.783333333333335,
    2026
);

$attesi = [
    [22, 1, 2026, 12.416666666667],
    [18, 2, 2026, 19.166666666667],
    [18, 3, 2026, 4.0],
    [14, 4, 2026, 13.966666666667],
    [11, 5, 2026, 23.416666666667],
    [8, 6, 2026, 7.2],
    [5, 7, 2026, 13.3],
    [1, 8, 2026, 18.816666666667],
    [29, 8, 2026, 1.2333333333333],
    [25, 9, 2026, 9.35],
    [22, 10, 2026, 18.816666666667],
    [19, 11, 2026, 4.25],
    [16, 12, 2026, 12.2],
];

if (count($rl) !== count($attesi)) {
    echo "✗ RL Lorenzo 2026: attese " . count($attesi) . ", ottenute " . count($rl) . "\n";
    exit(1);
}

foreach ($attesi as $i => [$g, $m, $a, $ora]) {
    $r = $rl[$i] ?? null;
    if (!$r) {
        echo "✗ RL Lorenzo 2026: indice {$i} mancante\n";
        exit(1);
    }

    $okData = (int)$r['giorno'] === $g && (int)$r['mese'] === $m && (int)$r['anno'] === $a;
    $okOra = abs((float)$r['ora_gmt'] - $ora) < 0.0000001;

    if (!$okData || !$okOra) {
        echo "✗ RL Lorenzo 2026 indice {$i}: atteso {$g}/{$m}/{$a} {$ora}, ottenuto {$r['giorno']}/{$r['mese']}/{$r['anno']} {$r['ora_gmt']}\n";
        exit(1);
    }
}

echo "✓ RL Lorenzo 2026: 13 rivoluzioni lunari OK\n";
