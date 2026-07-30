<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/SweCalc.php';
require_once __DIR__ . '/../includes/RuleEngine.php';
require_once __DIR__ . '/../includes/RicercaRSPlanetHouseAssigner.php';
require_once __DIR__ . '/../includes/RicercaRSThemeBuilder.php';
require_once __DIR__ . '/../includes/SoggettoRepository.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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


echo "\nValidazione registrazione pubblica\n";
echo "----------------------\n";

require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth($pdo);
$testSuffix = bin2hex(random_bytes(6));
$testUsername = 'reg_' . $testSuffix;
$testEmail = 'reg_' . $testSuffix . '@example.test';

try {
    $pdo->beginTransaction();

    $invalid = $auth->registraUtentePubblico('x', 'email-non-valida', 'breve');
    if ($invalid['ok'] !== false) {
        throw new RuntimeException('Validazione input non applicata');
    }

    $created = $auth->registraUtentePubblico($testUsername, $testEmail, 'Password123!');
    if (
        $created['ok'] !== true
        || empty($created['id'])
        || !is_string($created['verification_token'] ?? null)
        || strlen($created['verification_token']) !== 64
    ) {
        throw new RuntimeException('Creazione utente o token di verifica falliti');
    }

    $tokenStmt = $pdo->prepare(
        "SELECT purpose, token_hash, expires_at, used_at
         FROM token_sicurezza
         WHERE user_id = ?"
    );
    $tokenStmt->execute([(int)$created['id']]);
    $tokenRow = $tokenStmt->fetch(PDO::FETCH_ASSOC);

    if (
        !is_array($tokenRow)
        || $tokenRow['purpose'] !== 'email_verification'
        || $tokenRow['used_at'] !== null
        || !hash_equals(
            hash('sha256', $created['verification_token']),
            (string)$tokenRow['token_hash']
        )
        || (string)$tokenRow['token_hash'] === $created['verification_token']
    ) {
        throw new RuntimeException('Persistenza sicura del token non corretta');
    }

    $stmt = $pdo->prepare(
        "SELECT u.ruolo, u.account_status, p.code AS plan_code
         FROM utenti u
         JOIN piani p ON p.id = u.plan_id
         WHERE u.id = ?"
    );
    $stmt->execute([(int)$created['id']]);
    $utente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !is_array($utente)
        || $utente['ruolo'] !== 'user'
        || $utente['account_status'] !== 'pending_email'
        || $utente['plan_code'] !== 'free'
    ) {
        throw new RuntimeException('Privilegi o stato iniziale non corretti');
    }

    $_SESSION = [];

    ob_start();
    $loginPending = $auth->login($testUsername, 'Password123!');
    ob_end_clean();

    if (
        ($loginPending['ok'] ?? true) !== false
        || !str_contains(
            (string)($loginPending['errore'] ?? ''),
            'verificare'
        )
        || !empty($_SESSION['utente_id'])
    ) {
        throw new RuntimeException(
            'Login consentito prima della verifica email'
        );
    }

    $newTokenRequest = $auth->richiediNuovoTokenVerifica($testEmail);

    if (
        ($newTokenRequest['ok'] ?? false) !== true
        || !is_string($newTokenRequest['verification_token'] ?? null)
        || strlen($newTokenRequest['verification_token']) !== 64
    ) {
        throw new RuntimeException('Nuovo token di verifica non generato');
    }

    $oldTokenStmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM token_sicurezza
         WHERE user_id = ?
           AND purpose = 'email_verification'
           AND used_at IS NULL"
    );
    $oldTokenStmt->execute([(int)$created['id']]);

    if ((int)$oldTokenStmt->fetchColumn() !== 1) {
        throw new RuntimeException('Numero token verifica attivi errato');
    }

    if (
        hash_equals(
            hash('sha256', $created['verification_token']),
            hash('sha256', $newTokenRequest['verification_token'])
        )
    ) {
        throw new RuntimeException('Nuovo token uguale al precedente');
    }

    $verified = $auth->verificaEmailToken(
        $newTokenRequest['verification_token']
    );

    if (
        ($verified['ok'] ?? false) !== true
        || (int)($verified['user_id'] ?? 0) !== (int)$created['id']
    ) {
        throw new RuntimeException('Verifica email fallita');
    }

    $verifyStmt = $pdo->prepare(
        "SELECT account_status, email_verified_at
         FROM utenti
         WHERE id = ?"
    );
    $verifyStmt->execute([(int)$created['id']]);
    $verifiedUser = $verifyStmt->fetch(PDO::FETCH_ASSOC);

    if (
        !is_array($verifiedUser)
        || $verifiedUser['account_status'] !== 'active'
        || $verifiedUser['email_verified_at'] === null
    ) {
        throw new RuntimeException('Attivazione account non corretta');
    }

    $_SESSION = [];

    ob_start();
    $loginActive = $auth->login($testUsername, 'Password123!');
    ob_end_clean();

    if (
        ($loginActive['ok'] ?? false) !== true
        || (int)($loginActive['id'] ?? 0) !== (int)$created['id']
        || (int)($_SESSION['utente_id'] ?? 0) !== (int)$created['id']
    ) {
        throw new RuntimeException(
            'Login non riuscito dopo la verifica email'
        );
    }

    $_SESSION = [];

    $secondVerification = $auth->verificaEmailToken(
        $created['verification_token']
    );

    if (($secondVerification['ok'] ?? true) !== false) {
        throw new RuntimeException('Token di verifica riutilizzabile');
    }

    $resetRequest = $auth->richiediResetPassword($testEmail);

    if (
        ($resetRequest['ok'] ?? false) !== true
        || !is_string($resetRequest['reset_token'] ?? null)
        || strlen($resetRequest['reset_token']) !== 64
    ) {
        throw new RuntimeException('Richiesta reset password fallita');
    }

    $resetResult = $auth->confermaResetPassword(
        $resetRequest['reset_token'],
        'NuovaPassword123!'
    );

    if (
        ($resetResult['ok'] ?? false) !== true
        || (int)($resetResult['user_id'] ?? 0) !== (int)$created['id']
    ) {
        throw new RuntimeException('Conferma reset password fallita');
    }

    $_SESSION = [];

    ob_start();
    $loginNewPassword = $auth->login(
        $testUsername,
        'NuovaPassword123!'
    );
    ob_end_clean();

    if (($loginNewPassword['ok'] ?? false) !== true) {
        throw new RuntimeException('Login con nuova password fallito');
    }

    $secondReset = $auth->confermaResetPassword(
        $resetRequest['reset_token'],
        'TerzaPassword123!'
    );

    if (($secondReset['ok'] ?? true) !== false) {
        throw new RuntimeException('Token reset riutilizzabile');
    }

    $duplicate = $auth->registraUtentePubblico($testUsername, $testEmail, 'Password123!');
    if ($duplicate['ok'] !== false) {
        throw new RuntimeException('Duplicato accettato');
    }

    $pdo->rollBack();
    echo "✓ Registrazione pubblica: login bloccato prima della verifica, attivazione, login attivo, token monouso, piano free e duplicati OK\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "✗ Registrazione pubblica fallita: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nTest limite soggetti...\n";

$subjectsLimitCommand = escapeshellarg(PHP_BINARY)
    . ' '
    . escapeshellarg(__DIR__ . '/test_subjects_limit.php');

passthru($subjectsLimitCommand, $subjectsLimitExitCode);

if ($subjectsLimitExitCode !== 0) {
    exit(1);
}

echo "\nTest limite ricerche salvate...\n";

$savedSearchesLimitCommand = escapeshellarg(PHP_BINARY)
    . ' '
    . escapeshellarg(__DIR__ . '/test_saved_searches_limit.php');

passthru($savedSearchesLimitCommand, $savedSearchesLimitExitCode);

if ($savedSearchesLimitExitCode !== 0) {
    exit(1);
}

echo "\nTest completati.\n";
