<?php
declare(strict_types=1);

/**
 * Test diagnostico per la condizione Casa (UX-0021, fasce UX-0022).
 *
 * Riproduce fedelmente il percorso reale di api/ricerca_stream_api.php per
 * il soggetto "Rossella Fumai", anno RS 2026, condizione Casa, aeroporti
 * large/medium (comportamento di default) - per verificare concretamente,
 * su dati reali, se MYASTRAL_ALIGNMENT_MODE è letto correttamente in questo
 * contesto, se calcolaLivelloCasa() viene invocata, e come vengono ordinati
 * i risultati.
 *
 * Uso: cd ~/astrolab && docker compose exec -T astrolab-web php tests/test_casa.php
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/NascitaGmtHelper.php';
require_once __DIR__ . '/../includes/SweCalc.php';
require_once __DIR__ . '/../includes/RuleEngine.php';
require_once __DIR__ . '/../includes/RicercaRSFilters.php';
require_once __DIR__ . '/../includes/RicercaRSPlanetHouseAssigner.php';
require_once __DIR__ . '/../includes/RicercaRSThemeBuilder.php';

echo "MYASTRAL_ALIGNMENT_MODE (come letta da bootstrap.php): "
    . (MYASTRAL_ALIGNMENT_MODE ? 'TRUE' : 'FALSE') . "\n";

if (!MYASTRAL_ALIGNMENT_MODE) {
    echo "✗ Il flag risulta FALSE in questo contesto PHP - questa è la causa.\n";
    exit(1);
}

require_once __DIR__ . '/../includes/RuleEngineExtended.php';

$pdo = db_connect();

// ── 1. Recupero soggetto ────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM soggetti WHERE nome ILIKE ? LIMIT 1");
$stmt->execute(['%Rossella Fumai%']);
$soggetto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$soggetto) {
    echo "✗ Soggetto 'Rossella Fumai' non trovato nel database.\n";
    exit(1);
}

echo "Soggetto trovato: {$soggetto['nome']} (id {$soggetto['id']})\n";
echo "Nascita: {$soggetto['data_nascita']} {$soggetto['ora_nascita']} "
    . "(offset {$soggetto['offset_gmt']}), lat {$soggetto['latitudine']}, lon {$soggetto['longitudine']}\n";

$gmtData = calcolaDataOraGmtCorretta(
    $soggetto['data_nascita'],
    $soggetto['ora_nascita'],
    (float)($soggetto['offset_gmt'] ?? 0)
);
$dateGmt = new DateTime($gmtData['data_gmt'] . ' ' . $gmtData['ora_gmt']);
$oraGmtParts = explode(':', $gmtData['ora_gmt']);

$g = (int)$dateGmt->format('d');
$m = (int)$dateGmt->format('m');
$a = (int)$dateGmt->format('Y');
$oraGmt = (int)$oraGmtParts[0] + ((int)($oraGmtParts[1] ?? 0) / 60);
$lat = (float)$soggetto['latitudine'];
$lon = (float)$soggetto['longitudine'];

$anno = 2026;
$condizione = 'Casa';

echo "Parametri natali GMT: g={$g} m={$m} a={$a} ora_gmt={$oraGmt}, anno RS={$anno}, condizione={$condizione}\n\n";

// ── 2. Calcolo RS e tema natale ─────────────────────────────────────────
$swe = new SweCalc();
$engine = new RuleEngine();
$engineExt = new RuleEngineExtended();
$engine = new RuleEngine();
require_once __DIR__ . '/../includes/StellineV2Calculator.php';
$v2Calc = new StellineV2Calculator();

$rs = $swe->calcolaRS($g, $m, $a, $oraGmt, $anno);
$oraGmtRS = $rs['ora_gmt'];
$giornoRS = $rs['giorno'];
$meseRS = $rs['mese'];
$annoRSeff = $rs['anno'];

$temaNatale = $swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);
$pianetiRS = $swe->calcolaPianeti($giornoRS, $meseRS, $annoRSeff, $oraGmtRS);

echo "RS calcolata: {$rs['stringa']}\n\n";

// ── 3. Recupero aeroporti (default: large/medium, non militari) ────────
$stmtAero = $pdo->query(
    "SELECT icao_code, iata_code, nome, citta, nazione, latitudine, longitudine
     FROM aeroporti
     WHERE attivo = true AND militare = false
       AND tipo IN ('large_airport','medium_airport')
     ORDER BY icao_code
     LIMIT 300"
);
$aeroporti = $stmtAero->fetchAll(PDO::FETCH_ASSOC);
echo "Aeroporti recuperati per il test: " . count($aeroporti) . "\n\n";

// ── 4. Loop diagnostico ──────────────────────────────────────────────────
$righe = [];
$nEscluse = 0;
$nMostrate = 0;

foreach ($aeroporti as $aero) {
    $latA = (float)$aero['latitudine'];
    $lonA = (float)$aero['longitudine'];

    try {
        $caseRS = $swe->calcolaCasePlacido($giornoRS, $meseRS, $annoRSeff, $oraGmtRS, $latA, $lonA);
        $pianetiConCase = assegnaCaseAiPianeti($pianetiRS, $caseRS, $swe);

        $rilevamento = verificaCondizioneCasa($pianetiConCase, $caseRS);
        $pianetiInCasa = $rilevamento['pianeti_in_casa'];

        $temaRS = costruisciTemaRS($pianetiConCase, $caseRS, $latA, $lonA);
        $livelloCasa = $engineExt->calcolaLivelloCasa($temaRS);
        $val = $engineExt->generaValCasa($temaRS);

        // UX-0023 (v2): replica del controllo di produzione - solo i veti
        // UFFICIALI delle 34 regole (Regola 4/5/31/34) escludono la RSM per
        // Casa. Il veto "astrolab-angoli" non e' ufficiale e non esclude.
        $valutazione = $engine->valuta($temaNatale, $temaRS, 'Casa', []);
        $vetiUfficialiCasa = array_filter(
            $valutazione['veti'] ?? [],
            static fn(string $v): bool => strpos($v, 'astrolab-angoli') === false
        );

        $pianetiRS_v2 = [];
        foreach ($temaRS['pianeti'] as $_pid => $_p) {
            $pianetiRS_v2[$_pid] = ['casa' => $_p['casa'], 'longitudine' => $_p['longitudine']];
        }
        $caseRS_v2 = $temaRS['case'] ?? [];
        $valV2 = $v2Calc->calcola($pianetiRS_v2, $caseRS_v2, 'Casa', $temaNatale);

        if ($aero['icao_code'] === 'AGGH') {
            fwrite(STDERR, "--- DEBUG HONIARA ---\n");
            fwrite(STDERR, "livelloCasa: " . json_encode($livelloCasa) . "\n");
            fwrite(STDERR, "veti: " . json_encode($vetiUfficialiCasa) . "\n");
            fwrite(STDERR, "alert_stellium_misto: " . json_encode($valV2['alert_stellium_misto'] ?? null) . "\n");
            fwrite(STDERR, "valV2 completo: " . json_encode($valV2) . "\n");
        }

        $escludi = ($livelloCasa['escludi'] ?? false) || !empty($vetiUfficialiCasa);
        $livello = $livelloCasa['livello'] ?? null;
        if ($livelloCasa !== null && !$escludi
            && (!empty($vetiUfficialiCasa) || ($valV2['alert_stellium_misto'] ?? false))) {
            $livello = ($livello ?? 0) + 10;
        }

        $nomiPianeti = array_map(
            fn($id) => RuleEngine::VAL_NOMI[$id] ?? (string)$id,
            $pianetiInCasa
        );

        if ($escludi) {
            $nEscluse++;
        } else {
            $nMostrate++;
        }

        $righe[] = [
            'icao'     => $aero['icao_code'],
            'nome'     => $aero['nome'],
            'pianeti'  => implode(',', $nomiPianeti) ?: '(nessuno)',
            'val'      => $val,
            'livello'  => $livello,
            'escludi'  => $escludi ? 'SI' : 'no',
        ];
    } catch (Throwable $e) {
        // aeroporto saltato (comportamento identico a produzione)
        continue;
    }
}

echo "Totale processati: " . count($righe) . " | mostrati: {$nMostrate} | esclusi: {$nEscluse}\n\n";

// Ordina come farebbe usort() in ricerca_stream_api.php: per livello
// crescente (le RSM senza livello - non applicabile qui, tutte hanno
// condizione Casa - restano in fondo).
usort($righe, function ($a, $b) {
    return ($a['livello'] ?? 999) <=> ($b['livello'] ?? 999);
});

// Filtra le escluse, come farebbe la produzione (non entrano in $risultati)
$mostrate = array_values(array_filter($righe, fn($r) => $r['escludi'] === 'no'));

if (empty($mostrate)) {
    echo "Nessun risultato mostrato - messaggio atteso: "
        . "\"Nessun Risultato trovato per la condizione scelta\"\n";
    exit(0);
}

echo str_pad('ICAO', 6) . str_pad('LIVELLO', 9) . str_pad('VAL', 12) . "NOME\n";
echo str_repeat('-', 70) . "\n";

$primi = array_slice($mostrate, 0, 15);
$ultimi = array_slice($mostrate, -15);

foreach ($primi as $r) {
    echo str_pad($r['icao'], 6) . str_pad((string)$r['livello'], 9) . str_pad($r['val'], 12) . $r['nome'] . "\n";
}
echo "... (" . (count($mostrate) - 30 > 0 ? count($mostrate) - 30 : 0) . " righe omesse) ...\n";
foreach ($ultimi as $r) {
    echo str_pad($r['icao'], 6) . str_pad((string)$r['livello'], 9) . str_pad($r['val'], 12) . $r['nome'] . "\n";
}

// Verifica di coerenza: il livello del primo risultato deve essere <= a
// quello dell'ultimo (ordine crescente = fascia 1 prima di fascia 2).
$primoLivello = $mostrate[0]['livello'];
$ultimoLivello = end($mostrate)['livello'];

echo "\n";
if ($primoLivello <= $ultimoLivello) {
    echo "✓ Ordinamento coerente: primo livello {$primoLivello} <= ultimo livello {$ultimoLivello}\n";
} else {
    echo "✗ ANOMALIA: primo livello {$primoLivello} > ultimo livello {$ultimoLivello} - ordinamento invertito!\n";
    exit(1);
}
