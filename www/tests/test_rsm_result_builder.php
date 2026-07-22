<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/RicercaRSResultBuilder.php';

$val = [
    'stelline' => 4,
    'stelle_str' => '****',
    'val' => 10,
    'is_valida' => true,
    'veti' => [],
];

$comuni = [
    $val,
    [],
    false,
    false,
    [],
    'Amore',
    false,
    null,
    null,
    false,
];

$aeroporto = costruisciRisultatoRicercaRS(
    [
        'icao_code' => 'LIRF',
        'iata_code' => 'FCO',
        'nome' => 'Roma Fiumicino',
        'citta' => 'Roma',
        'nazione' => 'IT',
        'tipo' => 'large_airport',
        'popolazione' => null,
        'aeroporto_associato' => 'Roma Fiumicino',
    ],
    41.8003,
    12.2389,
    ...$comuni
);

foreach ([
    'tipo' => 'large_airport',
    'popolazione' => null,
    'aeroporto_associato' => 'Roma Fiumicino',
] as $campo => $atteso) {
    if (!array_key_exists($campo, $aeroporto) || $aeroporto[$campo] !== $atteso) {
        fwrite(STDERR, "Campo aeroporto non valido: {$campo}\n");
        exit(1);
    }
}

$legacy = costruisciRisultatoRicercaRS(
    [
        'icao_code' => 'LIMC',
        'iata_code' => 'MXP',
        'nome' => 'Milano Malpensa',
        'citta' => 'Milano',
        'nazione' => 'IT',
    ],
    45.6306,
    8.7281,
    ...$comuni
);

foreach (['tipo', 'popolazione', 'aeroporto_associato'] as $campo) {
    if (!array_key_exists($campo, $legacy) || $legacy[$campo] !== null) {
        fwrite(STDERR, "Compatibilità legacy non valida: {$campo}\n");
        exit(1);
    }
}

echo "RSM RESULT BUILDER TEST OK\n";
