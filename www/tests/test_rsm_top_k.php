<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/RicercaRSTopK.php';

$input = [
    ['id' => 'a', 'stelline' => 2],
    ['id' => 'b', 'stelline' => 5],
    ['id' => 'c', 'stelline' => 3],
    ['id' => 'd', 'stelline' => 5],
    ['id' => 'e', 'stelline' => 4],
    ['id' => 'f', 'stelline' => 5],
    ['id' => 'g', 'stelline' => 1],
    ['id' => 'h', 'stelline' => 4],
];

$attesi = $input;
usort(
    $attesi,
    static fn(array $a, array $b): int =>
        $b['stelline'] <=> $a['stelline']
);
$attesi = array_slice($attesi, 0, 4);

$ottenuti = [];
foreach ($input as $risultato) {
    aggiungiRisultatoTopK($ottenuti, $risultato, 4);

    if (count($ottenuti) > 4) {
        fwrite(STDERR, "Top-K ha superato il limite in memoria\n");
        exit(1);
    }
}

usort(
    $ottenuti,
    static fn(array $a, array $b): int =>
        $b['stelline'] <=> $a['stelline']
);

if ($ottenuti !== $attesi) {
    fwrite(STDERR, "Top-K non equivale a ordinamento completo più slice\n");
    exit(1);
}

$tutti = [];
foreach ($input as $risultato) {
    aggiungiRisultatoTopK($tutti, $risultato, null);
}

if ($tutti !== $input) {
    fwrite(STDERR, "Modalità senza limite modificata\n");
    exit(1);
}

$pari = [];
foreach ([
    ['id' => 'primo', 'stelline' => 3],
    ['id' => 'secondo', 'stelline' => 3],
    ['id' => 'terzo', 'stelline' => 3],
] as $risultato) {
    aggiungiRisultatoTopK($pari, $risultato, 2);
}

if (array_column($pari, 'id') !== ['primo', 'secondo']) {
    fwrite(STDERR, "Ordine dei pareggi non preservato\n");
    exit(1);
}

echo "RSM TOP-K TEST OK\n";
