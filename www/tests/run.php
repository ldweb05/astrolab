<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/SweCalc.php';
require_once __DIR__ . '/../includes/RuleEngine.php';
require_once __DIR__ . '/../includes/RicercaRSPlanetHouseAssigner.php';
require_once __DIR__ . '/../includes/RicercaRSThemeBuilder.php';
require_once __DIR__ . '/../includes/SoggettoRepository.php';

echo "Astrologia Attiva — Test Runner\n";
echo "================================\n\n";

try {
    $pdo = db_connect();
    echo "✓ Connessione database OK\n";
} catch (Throwable $e) {
    echo "✗ Connessione database fallita: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $calc = new SweCalc();
    echo "✓ SweCalc inizializzato\n";
} catch (Throwable $e) {
    echo "✗ SweCalc fallito: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $engine = new RuleEngine();
    echo "✓ RuleEngine inizializzato\n";
} catch (Throwable $e) {
    echo "✗ RuleEngine fallito: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nValidazione backend\n";
echo "--------------------------\n";

try {
    $calcTest = new SweCalc();

    $backendLuoghi = [
        'Roma' => [41.902782, 12.496366],
        'Tokyo' => [35.676200, 139.650300],
        'New York' => [40.712800, -74.006000],
    ];

    foreach ($backendLuoghi as $nomeLuogo => [$latTest, $lonTest]) {
        $temaCalc = $calcTest->calcolaTema(20, 1, 2026, 0.0, $latTest, $lonTest);

        if (count($temaCalc['pianeti']) !== 10) {
            echo "✗ {$nomeLuogo}: numero pianeti inatteso\n";
            exit(1);
        }

        foreach ([1,2,3,4,5,6,7,8,9,10,11,12,'ASC','MC'] as $casaId) {
            if (!isset($temaCalc['case'][$casaId])) {
                echo "✗ {$nomeLuogo}: casa {$casaId} mancante\n";
                exit(1);
            }
        }

        echo "✓ {$nomeLuogo}: libswe OK\n";
    }

    $caseLongyearbyen = $calcTest->calcolaCasePlacido(
        1,
        1,
        2000,
        12.0,
        78.2232,
        15.6464
    );

    $atteseLongyearbyen = [
        1 => 337.9055934,
        2 => 23.3390713,
        3 => 68.7725493,
        4 => 114.2060272,
        5 => 128.7725493,
        6 => 143.3390713,
        7 => 157.9055934,
        8 => 203.3390713,
        9 => 248.7725493,
        10 => 294.2060272,
        11 => 308.7725493,
        12 => 323.3390713,
        'ASC' => 337.9055934,
        'MC' => 294.2060272,
    ];

    foreach ($atteseLongyearbyen as $id => $attesa) {
        $ottenuta = (float)($caseLongyearbyen[$id]['longitudine'] ?? NAN);

        if (!is_finite($ottenuta) || abs($ottenuta - $attesa) > 0.0000001) {
            echo "✗ Longyearbyen: valore inatteso per {$id}: {$ottenuta}\n";
            exit(1);
        }
    }

    echo "✓ Longyearbyen: fallback polare compatibile con swetest\n";
} catch (Throwable $e) {
    echo "✗ Validazione backend fallita: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nValidazione casi JSON\n";
echo "----------------------\n";

$casesDir = __DIR__ . '/cases';
$caseFiles = glob($casesDir . '/*.json') ?: [];

if (empty($caseFiles)) {
    echo "✗ Nessun caso JSON trovato in tests/cases\n";
    exit(1);
}

$requiredFields = ['tipo', 'soggetto_id', 'anno', 'luogo', 'lat', 'lon', 'condizione'];

foreach ($caseFiles as $caseFile) {
    $caseName = basename($caseFile);
    $raw = file_get_contents($caseFile);

    if ($raw === false) {
        echo "✗ {$caseName}: impossibile leggere il file\n";
        exit(1);
    }

    $data = json_decode($raw, true);

    if (!is_array($data)) {
        echo "✗ {$caseName}: JSON non valido\n";
        exit(1);
    }

    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $data)) {
            echo "✗ {$caseName}: campo mancante '{$field}'\n";
            exit(1);
        }
    }

    if ($data['tipo'] !== 'RS') {
        echo "✗ {$caseName}: tipo non supportato '{$data['tipo']}'\n";
        exit(1);
    }

    if (!is_int($data['soggetto_id']) || $data['soggetto_id'] <= 0) {
        echo "✗ {$caseName}: soggetto_id non valido\n";
        exit(1);
    }

    if (!is_int($data['anno']) || $data['anno'] < 1900 || $data['anno'] > 2100) {
        echo "✗ {$caseName}: anno non valido\n";
        exit(1);
    }

    if (!is_string($data['luogo']) || trim($data['luogo']) === '') {
        echo "✗ {$caseName}: luogo non valido\n";
        exit(1);
    }

    if (!is_numeric($data['lat']) || $data['lat'] < -90 || $data['lat'] > 90) {
        echo "✗ {$caseName}: lat non valida\n";
        exit(1);
    }

    if (!is_numeric($data['lon']) || $data['lon'] < -180 || $data['lon'] > 180) {
        echo "✗ {$caseName}: lon non valida\n";
        exit(1);
    }

    if (!is_string($data['condizione']) || trim($data['condizione']) === '') {
        echo "✗ {$caseName}: condizione non valida\n";
        exit(1);
    }

    $soggetto = caricaSoggettoById($pdo, (int)$data['soggetto_id']);

    if (!$soggetto) {
        echo "✗ {$caseName}: soggetto_id {$data['soggetto_id']} non trovato\n";
        exit(1);
    }

    $date = new DateTime($soggetto['data_nascita']);
    $oraParts = explode(':', $soggetto['ora_nascita_gmt'] ?? '12:00');
    $oraGmt = (int)$oraParts[0] + ((int)($oraParts[1] ?? 0) / 60);

    $g = (int)$date->format('d');
    $m = (int)$date->format('m');
    $a = (int)$date->format('Y');
    $lat = (float)$soggetto['latitudine'];
    $lon = (float)$soggetto['longitudine'];

    $rs = $calc->calcolaRS($g, $m, $a, $oraGmt, (int)$data['anno']);
    $temaNatale = $calc->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);
    $pianetiRS = $calc->calcolaPianeti($rs['giorno'], $rs['mese'], $rs['anno'], $rs['ora_gmt']);
    $caseRS = $calc->calcolaCasePlacido(
        $rs['giorno'],
        $rs['mese'],
        $rs['anno'],
        $rs['ora_gmt'],
        (float)$data['lat'],
        (float)$data['lon']
    );

    $pianetiConCase = assegnaCaseAiPianeti($pianetiRS, $caseRS, $calc);
    $temaRS = costruisciTemaRS(
        $pianetiConCase,
        $caseRS,
        (float)$data['lat'],
        (float)$data['lon']
    );

    $valutazione = $engine->valuta($temaNatale, $temaRS, (string)$data['condizione']);

    if (isset($data['atteso']['stelline'])) {
        $attese = (int)$data['atteso']['stelline'];
        $ottenute = (int)$valutazione['stelline'];

        if ($ottenute !== $attese) {
            echo "✗ {$caseName}: stelline attese {$attese}, ottenute {$ottenute}\n";
            exit(1);
        }
    }

    if (isset($data['atteso']['veto'])) {
        $attesoVeto = (bool)$data['atteso']['veto'];
        $ottenutoVeto = !empty($valutazione['veti']);

        if ($ottenutoVeto !== $attesoVeto) {
            echo "✗ {$caseName}: veto atteso " . ($attesoVeto ? 'true' : 'false') .
                 ", ottenuto " . ($ottenutoVeto ? 'true' : 'false') . "\n";
            exit(1);
        }
    }

    if (isset($data['atteso']['is_valida'])) {
        $attesaValida = (bool)$data['atteso']['is_valida'];
        $ottenutaValida = (bool)$valutazione['is_valida'];

        if ($ottenutaValida !== $attesaValida) {
            echo "✗ {$caseName}: is_valida attesa " . ($attesaValida ? 'true' : 'false') .
                 ", ottenuta " . ($ottenutaValida ? 'true' : 'false') . "\n";
            exit(1);
        }
    }

    if (isset($data['atteso']['val'])) {
        $attesaVal = (string)$data['atteso']['val'];
        $ottenutaVal = (string)$valutazione['val'];

        if ($ottenutaVal !== $attesaVal) {
            echo "✗ {$caseName}: val attesa '{$attesaVal}', ottenuta '{$ottenutaVal}'\n";
            exit(1);
        }
    }

    echo "✓ {$caseName}: valido — stelline {$valutazione['stelline']} | val {$valutazione['val']}\n";

    if (!empty($valutazione['veti'])) {
        foreach ($valutazione['veti'] as $veto) {
            echo "    {$veto}\n";
        }
    }
}


echo "\nValidazione rivoluzioni lunari\n";
echo "----------------------\n";

passthru(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/rl_lorenzo_2026.php'), $code);
if ($code !== 0) {
    echo "✗ Test RL fallito\n";
    exit(1);
}

echo "\nValidazione rilocazione\n";
echo "----------------------\n";

passthru(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/rilocazione_newyork_1960.php'), $code);
if ($code !== 0) {
    echo "✗ Test rilocazione fallito\n";
    exit(1);
}

echo "\nValidazione ricerca API\n";
echo "----------------------\n";

$searchTests = glob(__DIR__ . '/search/*.php') ?: [];
sort($searchTests);

foreach ($searchTests as $searchTest) {
    passthru(PHP_BINARY . ' ' . escapeshellarg($searchTest), $code);
    if ($code !== 0) {
        echo "✗ Test ricerca fallito: " . basename($searchTest) . "\n";
        exit(1);
    }
}

echo "\nTest completati.\n";
