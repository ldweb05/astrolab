<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * api/rs_alert_api.php — Alert non bloccanti: attivazione stellium natale nella RS
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * LOGICA:
 *   1. Calcola tema natale del soggetto → trova le case con 3+ pianeti (stellium).
 *   2. Calcola il tema RS per il luogo scelto → ottiene la casa di ogni pianeta RS
 *      e la casa in cui cade l'ASC RS.
 *   3. Per ogni stellium natale in casa X:
 *      - Se un pianeta RS è in casa X → genera alert "[pianeta] in [X]ª casa RS"
 *      - Se l'ASC RS è in casa X     → genera alert "Ascendente RS in [X]ª casa RS"
 *
 * COLORI (per ID pianeta):
 *   ROSSO      → Marte (4), Saturno (6), Plutone (9), ASC
 *   ORANGERED  → Urano (7), Nettuno (8), Mercurio (2), Sole (0), Luna (1)
 *   VERDE      → Giove (5), Venere (3)
 *
 * GET params (stessi di rs_api.php):
 *   g, m, a, ora_gmt   → dati natali
 *   lat, lon           → coordinate nascita
 *   anno               → anno RS
 *   lat_rs, lon_rs     → coordinate luogo RS
 *
 * Risposta JSON:
 *   {
 *     ok: true,
 *     stellium_natale: [ { casa: int, pianeti: [{ id, nome, simbolo }] } ],
 *     alerts: [
 *       { messaggio: string, colore: string, casa: int, elemento: string }
 *     ]
 *   }
 */

// ── Auth ──────────────────────────────────────────────────────────────────
session_start();
require_once '../includes/Auth.php';

$pdo  = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autenticato.']);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');
set_time_limit(60);

require_once '../includes/SweCalc.php';

// ── Mappature ─────────────────────────────────────────────────────────────

/** Nomi estesi pianeti (allineati con SweCalc::PIANETI) */
const ALERT_NOMI = [
    0 => 'Sole',      1 => 'Luna',      2 => 'Mercurio',
    3 => 'Venere',    4 => 'Marte',     5 => 'Giove',
    6 => 'Saturno',   7 => 'Urano',     8 => 'Nettuno',
    9 => 'Plutone',  11 => 'Nodo Nord',
];

/** Simboli Unicode pianeti */
const ALERT_SIMBOLI = [
    0 => '☉', 1 => '☽', 2 => '☿', 3 => '♀',  4 => '♂',
    5 => '♃', 6 => '♄', 7 => '♅', 8 => '♆',  9 => '♇',
   11 => '☊',
];

/**
 * Restituisce il colore CSS dell'alert in base all'ID pianeta.
 * 'ASC' → rosso (trattato come elemento angolare pericoloso).
 */
function alertColore(string $idOrAsc): string {
    if ($idOrAsc === 'ASC') return '#dc3545';
    $id = (int)$idOrAsc;
    // Rosso: Marte (4), Saturno (6), Plutone (9)
    if (in_array($id, [4, 6, 9], true)) return '#dc3545';
    // Verde: Giove (5), Venere (3)
    if (in_array($id, [3, 5], true))    return '#28a745';
    // OrangeRed: tutto il resto (0=Sole, 1=Luna, 2=Mercurio, 7=Urano, 8=Nettuno, 11=Nodo)
    return '#ff4500';
}

/** Converte numero casa in romano (1→I, 6→VI, …) */
function casaRomana(int $n): string {
    $map = [
        1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
        7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII',
    ];
    return $map[$n] ?? (string)$n;
}

// ── Parametri ─────────────────────────────────────────────────────────────
$g      = intval($_GET['g']         ?? 1);
$m      = intval($_GET['m']         ?? 1);
$a      = intval($_GET['a']         ?? 1990);
$oraGmt = floatval($_GET['ora_gmt'] ?? 12.0);
$lat    = floatval($_GET['lat']     ?? 41.9);
$lon    = floatval($_GET['lon']     ?? 12.48);
$anno   = intval($_GET['anno']      ?? (int)date('Y'));
$latRS  = floatval($_GET['lat_rs']  ?? $lat);
$lonRS  = floatval($_GET['lon_rs']  ?? $lon);

try {
    $swe = new SweCalc();

    // ── Step 1: Tema natale → individua stellium ──────────────────────────
    $temaNatale = $swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);
    $pianetiNat = $temaNatale['pianeti']; // id → [nome, casa, longitudine, …]

    // Raggruppa pianeti natali per casa
    $perCasa = [];
    foreach ($pianetiNat as $id => $p) {
        $casa = (int)$p['casa'];
        if ($casa < 1 || $casa > 12) continue;
        $perCasa[$casa][] = [
            'id'     => $id,
            'nome'   => ALERT_NOMI[$id]   ?? $p['nome'],
            'simbolo'=> ALERT_SIMBOLI[$id] ?? '?',
        ];
    }

    // Filtra: solo case con 3+ pianeti = stellium natale
    $stelliumNatale = [];
    foreach ($perCasa as $casa => $lista) {
        if (count($lista) >= 3) {
            $stelliumNatale[] = ['casa' => $casa, 'pianeti' => $lista];
        }
    }

    // Se non c'è nessuno stellium natale, rispondi subito senza chiamare swetest
    if (empty($stelliumNatale)) {
        echo json_encode([
            'ok'             => true,
            'stellium_natale'=> [],
            'alerts'         => [],
        ]);
        exit;
    }

    // Raccoglie le case con stellium in un set per lookup rapido
    $caseStellium = array_column($stelliumNatale, 'casa');

    // ── Step 2: Tema RS → casa di ogni pianeta RS e casa dell'ASC RS ──────
    $rs     = $swe->calcolaRS($g, $m, $a, $oraGmt, $anno);
    $temaRS = $swe->calcolaTema(
        $rs['giorno'], $rs['mese'], $rs['anno'],
        $rs['ora_gmt'], $latRS, $lonRS
    );

    $pianetiRS = $temaRS['pianeti']; // id → [casa, nome, …]
    $caseRS    = $temaRS['case'];

    // Casa RS dell'ASC (ASC è sempre casa 1 della RS, ma qui ci interessa
    // la CASA NATALE in cui cade l'ASC della RS → usiamo trovaCasaNatale
    // Poiché SweCalc::trovaCasaNatale è privato, lo reimplementiamo qui
    // direttamente usando le case natali già calcolate sopra).
    $ascRSLon      = $caseRS['ASC']['longitudine'] ?? null;
    $casaNataleASC = null;
    if ($ascRSLon !== null) {
        $casaNataleASC = _alertTrovaCasaNatale((float)$ascRSLon, $temaNatale['case']);
    }

    // ── Step 3: Genera alert — UN SOLO messaggio per casa con stellium ───────
    //
    // Logica: per ogni casa natale che contiene uno stellium, raccolgo TUTTI
    // gli attivatori presenti nella RS (pianeti RS in quella casa + ASC RS se
    // cade in quella casa natale). Poi scelgo il colore del più pericoloso tra
    // di loro (ASC e malevoli > neutri > benefici) e produco un unico alert.
    // In questo modo se Saturno, Marte e l'ASC cadono tutti nella stessa casa
    // dello stellium compare un solo messaggio rosso, non tre righe separate.

    // Priorità colore: 0 = rosso (più pericoloso), 1 = arancio, 2 = verde
    $prioritaColore = function(string $c): int {
        if ($c === '#dc3545') return 0;
        if ($c === '#ff4500') return 1;
        return 2;
    };

    // Accumula attivatori per casa: casa → ['colore_peggiore', 'n_attivatori']
    $perCasaAttivatori = []; // casa(int) → ['colore' => string, 'n' => int]

    // 3a. Pianeti RS in case con stellium natale
    foreach ($pianetiRS as $id => $p) {
        $casaRs = (int)$p['casa'];
        if (!in_array($casaRs, $caseStellium, true)) continue;
        $colore = alertColore((string)$id);
        if (!isset($perCasaAttivatori[$casaRs])) {
            $perCasaAttivatori[$casaRs] = ['colore' => $colore, 'n' => 0];
        }
        // Tieni il colore del più pericoloso (priorità più bassa = più grave)
        if ($prioritaColore($colore) < $prioritaColore($perCasaAttivatori[$casaRs]['colore'])) {
            $perCasaAttivatori[$casaRs]['colore'] = $colore;
        }
        $perCasaAttivatori[$casaRs]['n']++;
    }

    // 3b. ASC RS — è sempre il più pericoloso (rosso), sovrascrive tutto
    if ($casaNataleASC !== null && in_array($casaNataleASC, $caseStellium, true)) {
        $casaAsc = $casaNataleASC;
        if (!isset($perCasaAttivatori[$casaAsc])) {
            $perCasaAttivatori[$casaAsc] = ['colore' => alertColore('ASC'), 'n' => 0];
        } else {
            // ASC è sempre rosso → sovrascrive qualsiasi colore precedente
            $perCasaAttivatori[$casaAsc]['colore'] = alertColore('ASC');
        }
        $perCasaAttivatori[$casaAsc]['n']++;
    }

    // 3c. Costruisce un alert per ogni casa attivata
    $alerts = [];
    foreach ($perCasaAttivatori as $casa => $info) {
        $alerts[] = [
            'messaggio' => "Uno o più elementi possono attivare lo stellium natale in " . casaRomana($casa) . " casa",
            'colore'    => $info['colore'],
            'casa'      => $casa,
            'n'         => $info['n'],
        ];
    }

    // Ordina per priorità colore (rosso prima), poi per numero casa
    usort($alerts, function($a, $b) use ($prioritaColore) {
        $pc = $prioritaColore($a['colore']) <=> $prioritaColore($b['colore']);
        return $pc !== 0 ? $pc : $a['casa'] <=> $b['casa'];
    });

    echo json_encode([
        'ok'             => true,
        'stellium_natale'=> $stelliumNatale,
        'alerts'         => $alerts,
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'errore'=> $e->getMessage(),
    ]);
}

// ── Helper: trova casa natale per una longitudine ──────────────────────────
// (replica locale di RuleEngine::trovaCasaNatale, senza dipendenza da classe)
function _alertTrovaCasaNatale(float $lon, array $caseNatale): int {
    $lon = fmod($lon + 360.0, 360.0);
    for ($c = 1; $c <= 12; $c++) {
        if (!isset($caseNatale[$c])) continue;
        $ini  = fmod($caseNatale[$c]['longitudine'] + 360.0, 360.0);
        $fine = fmod($caseNatale[($c % 12) + 1]['longitudine'] + 360.0, 360.0);
        if ($ini <= $fine) {
            if ($lon >= $ini && $lon < $fine) return $c;
        } else {
            if ($lon >= $ini || $lon < $fine) return $c;
        }
    }
    return 1;
}
