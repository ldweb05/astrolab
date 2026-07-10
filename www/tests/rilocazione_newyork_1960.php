<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/SweCalc.php';

$swe = new SweCalc();
$tema = $swe->calcolaTema(5, 9, 1960, 16.783333333333335, 40.7128, -74.0060);

$attesi = [
    'ASC' => 'Scorpione 27° 04\' 25"',
    'MC'  => 'Vergine 11° 04\' 09"',
    'pianeti' => [
        0 => ['Vergine 13° 06\' 05"', 10],
        1 => ['Pesci 16° 07\' 53"', 4],
        2 => ['Vergine 18° 21\' 43"', 10],
        3 => ['Bilancia 3° 33\' 10"', 10],
        4 => ['Gemelli 21° 31\' 55"', 7],
        5 => ['Sagittario 24° 10\' 33"', 1],
        6 => ['Capricorno 11° 54\' 21"', 2],
        7 => ['Leone 22° 47\' 22"', 9],
        8 => ['Scorpione 7° 00\' 05"', 12],
        9 => ['Vergine 6° 08\' 05"', 9],
    ],
];

if (($tema['case']['ASC']['posizione']['stringa'] ?? '') !== $attesi['ASC']) {
    echo "✗ rilocazione New York: ASC atteso {$attesi['ASC']}, ottenuto " . ($tema['case']['ASC']['posizione']['stringa'] ?? '?') . "\n";
    exit(1);
}

if (($tema['case']['MC']['posizione']['stringa'] ?? '') !== $attesi['MC']) {
    echo "✗ rilocazione New York: MC atteso {$attesi['MC']}, ottenuto " . ($tema['case']['MC']['posizione']['stringa'] ?? '?') . "\n";
    exit(1);
}

foreach ($attesi['pianeti'] as $id => [$posizione, $casa]) {
    $ottenutaPosizione = $tema['pianeti'][$id]['posizione']['stringa'] ?? '';
    $ottenutaCasa = (int)($tema['pianeti'][$id]['casa'] ?? 0);

    if ($ottenutaPosizione !== $posizione || $ottenutaCasa !== $casa) {
        echo "✗ rilocazione New York: pianeta {$id} atteso {$posizione} casa {$casa}, ottenuto {$ottenutaPosizione} casa {$ottenutaCasa}\n";
        exit(1);
    }
}

echo "✓ rilocazione New York 1960: ASC/MC/pianeti/case OK\n";
