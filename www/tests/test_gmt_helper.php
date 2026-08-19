<?php
require_once __DIR__ . '/../includes/NascitaGmtHelper.php';

function testCase($desc, $data, $ora, $offset, $expected_data, $expected_ora) {
    $result = calcolaDataOraGmtCorretta($data, $ora, $offset);
    $pass = ($result['data_gmt'] === $expected_data && $result['ora_gmt'] === $expected_ora);
    $status = $pass ? 'PASS' : 'FAIL';
    echo "[$status] $desc\n";
    echo "  Input: $data $ora (Offset: $offset)\n";
    echo "  Expected: $expected_data $expected_ora\n";
    echo "  Got:      {$result['data_gmt']} {$result['ora_gmt']}\n\n";
    return $pass;
}

$all_pass = true;

// Caso Sinner: 16/08/2001 00:52, offset +2 -> 15/08/2001 22:52
$all_pass &= testCase("Sinner (cambio giorno indietro)", "2001-08-16", "00:52", 2.0, "2001-08-15", "22:52:00");

// Offset negativo (USA): 16/08/2001 23:00, offset -5 -> 17/08/2001 04:00
$all_pass &= testCase("Offset negativo (cambio giorno avanti)", "2001-08-16", "23:00", -5.0, "2001-08-17", "04:00:00");

// Mezzanotte esatta: 16/08/2001 00:00, offset +2 -> 15/08/2001 22:00
$all_pass &= testCase("Mezzanotte esatta", "2001-08-16", "00:00", 2.0, "2001-08-15", "22:00:00");

// Offset 0: nessun cambiamento
$all_pass &= testCase("Offset 0", "2001-08-16", "12:30", 0.0, "2001-08-16", "12:30:00");

// Caso normale: 16/08/2001 15:00, offset +2 -> 16/08/2001 13:00
$all_pass &= testCase("Caso normale (nessun cambio giorno)", "2001-08-16", "15:00", 2.0, "2001-08-16", "13:00:00");

// Offset frazionario positivo (India +5.5): 16/08/2001 02:00, offset +5.5 -> 15/08/2001 20:30
$all_pass &= testCase("Offset frazionario India (cambio giorno indietro)", "2001-08-16", "02:00", 5.5, "2001-08-15", "20:30:00");

// Offset frazionario negativo (Terranova -3.5): 16/08/2001 22:00, offset -3.5 -> 17/08/2001 01:30
$all_pass &= testCase("Offset frazionario Terranova (cambio giorno avanti)", "2001-08-16", "22:00", -3.5, "2001-08-17", "01:30:00");

echo $all_pass ? "\nTUTTI I TEST SONO PASSATI.\n" : "\nALCUNI TEST SONO FALLITI.\n";
