<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * admin_accessi.php — Registro accessi degli astrologi (solo admin)
 *
 * Legge la tabella registro_accessi (sql/016_registro_accessi.sql), scritta da
 * Auth::registraAccessoAstrologo() a ogni login di un astrologo.
 * Riepilogo, grafico degli ultimi 30 giorni, accessi per utente e registro
 * filtrabile e paginato. Date e ore sempre in ora italiana (Europe/Rome),
 * calcolate da PostgreSQL: bootstrap.php imposta UTC per i calcoli astronomici.
 *
 * Cancellazioni (solo POST, token CSRF, conferma nel browser): riga singola,
 * righe selezionate, tutte le righe dei filtri correnti, righe piu' vecchie di
 * N giorni, intero registro. Dopo ogni azione si torna alla pagina con i filtri
 * (Post/Redirect/Get) e un messaggio con il numero di righe eliminate.
 */
session_start();
require_once 'includes/Auth.php';

header('X-Robots-Tag: noindex, nofollow');

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediAdmin();   // reindirizza se non admin

// Variabili richieste da includes/header_nav.php (stessa convenzione di admin_utenti.php)
$soggettoNome = $auth->getSoggettoNome();
$username     = $auth->getCurrentUsername();
$isAdmin      = true;

const ACCESSI_PER_PAGINA = 50;
const TZ_ITALIA = 'Europe/Rome';

if (empty($_SESSION['admin_accessi_csrf'])) {
    $_SESSION['admin_accessi_csrf'] = bin2hex(random_bytes(32));
}

// ── Filtri (GET, oppure campi nascosti del POST) ────────────────
/** Filtri validati e clausola WHERE (alias r) da una sorgente GET/POST. */
function accessiLeggiFiltri(array $src): array
{
    $oggi = new DateTime('now', new DateTimeZone(TZ_ITALIA));
    $dataValida = function (string $v): ?string {
        $d = DateTime::createFromFormat('!Y-m-d', $v, new DateTimeZone(TZ_ITALIA));
        return ($d && $d->format('Y-m-d') === $v) ? $v : null;
    };
    $dal = $dataValida((string)($src['dal'] ?? '')) ?? (clone $oggi)->modify('-29 days')->format('Y-m-d');
    $al  = $dataValida((string)($src['al'] ?? ''))  ?? $oggi->format('Y-m-d');
    if ($dal > $al) { [$dal, $al] = [$al, $dal]; }
    $utente = (int)($src['utente'] ?? 0);              // 0 = tutti, -1 = username inesistenti
    $esito  = (string)($src['esito'] ?? '');
    if (!in_array($esito, ['', 'riuscito', 'fallito', 'bloccato'], true)) { $esito = ''; }

    $where  = ["(r.creato_il AT TIME ZONE '" . TZ_ITALIA . "')::date BETWEEN ?::date AND ?::date"];
    $params = [$dal, $al];
    if ($utente > 0)        { $where[] = 'r.utente_id = ?'; $params[] = $utente; }
    elseif ($utente === -1) { $where[] = 'r.utente_id IS NULL'; }
    if ($esito !== '')      { $where[] = 'r.esito = ?'; $params[] = $esito; }
    return [$dal, $al, $utente, $esito, implode(' AND ', $where), $params];
}

// ── Cancellazioni (POST) ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$filtroDal, $filtroAl, $filtroUtente, $filtroEsito, $whereSql, $params] = accessiLeggiFiltri($_POST);
    $esitoAzione = ['tipo' => 'error', 'testo' => 'Richiesta non valida. Ricarica la pagina e riprova.'];

    if (hash_equals((string)$_SESSION['admin_accessi_csrf'], (string)($_POST['csrf_token'] ?? ''))) {
        $azione = (string)($_POST['azione'] ?? '');
        $sql = null;
        $par = [];
        if (isset($_POST['elimina_id'])) {                    // pulsante 🗑️ di una riga
            $sql = 'DELETE FROM registro_accessi WHERE id = ?';
            $par = [(int)$_POST['elimina_id']];
        } elseif ($azione === 'selezionate') {
            $ids = array_values(array_unique(array_filter(
                array_map('intval', (array)($_POST['ids'] ?? [])), fn($i) => $i > 0
            )));
            if ($ids) {
                $sql = 'DELETE FROM registro_accessi WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
                $par = $ids;
            } else {
                $esitoAzione = ['tipo' => 'error', 'testo' => 'Nessuna riga selezionata.'];
            }
        } elseif ($azione === 'filtrate') {
            $sql = "DELETE FROM registro_accessi r WHERE $whereSql";
            $par = $params;
        } elseif ($azione === 'vecchie') {
            $giorniVecchie = (int)($_POST['giorni'] ?? 0);
            if ($giorniVecchie >= 1 && $giorniVecchie <= 3650) {
                $sql = "DELETE FROM registro_accessi WHERE creato_il < NOW() - (? * INTERVAL '1 day')";
                $par = [$giorniVecchie];
            } else {
                $esitoAzione = ['tipo' => 'error', 'testo' => 'Indica un numero di giorni tra 1 e 3650.'];
            }
        } elseif ($azione === 'tutto') {
            if ((string)($_POST['conferma_testo'] ?? '') === 'ELIMINA') {
                $sql = 'DELETE FROM registro_accessi';
            } else {
                $esitoAzione = ['tipo' => 'error', 'testo' => 'Per svuotare il registro scrivi ELIMINA nel campo di conferma.'];
            }
        }

        if ($sql !== null) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($par);
                $n = $stmt->rowCount();
                $esitoAzione = ['tipo' => 'success', 'testo' => $n === 1 ? 'Eliminata 1 riga dal registro.' : "Eliminate $n righe dal registro."];
            } catch (Throwable $e) {
                error_log('admin_accessi elimina: ' . $e->getMessage());
                $esitoAzione = ['tipo' => 'error', 'testo' => 'Eliminazione non riuscita.'];
            }
        }
    }

    $_SESSION['admin_accessi_msg'] = $esitoAzione;
    $ritorno = http_build_query(array_filter(
        ['dal' => $filtroDal, 'al' => $filtroAl, 'utente' => $filtroUtente, 'esito' => $filtroEsito],
        fn($v) => $v !== '' && $v !== 0
    ));
    header('Location: admin_accessi.php' . ($ritorno !== '' ? '?' . $ritorno : ''));
    exit;
}

$messaggio = $_SESSION['admin_accessi_msg'] ?? null;
unset($_SESSION['admin_accessi_msg']);

[$filtroDal, $filtroAl, $filtroUtente, $filtroEsito, $whereSql, $params] = accessiLeggiFiltri($_GET);
$pagina = max(1, (int)($_GET['pagina'] ?? 1));

// ── Riepilogo (indipendente dai filtri) ─────────────────────────
$riepilogo = $pdo->query(
    "SELECT
        COUNT(*) FILTER (WHERE esito = 'riuscito'
            AND (creato_il AT TIME ZONE '" . TZ_ITALIA . "')::date = (NOW() AT TIME ZONE '" . TZ_ITALIA . "')::date) AS oggi,
        COUNT(*) FILTER (WHERE esito = 'riuscito' AND creato_il >= NOW() - INTERVAL '7 days')  AS sette,
        COUNT(*) FILTER (WHERE esito = 'riuscito' AND creato_il >= NOW() - INTERVAL '30 days') AS trenta,
        COUNT(DISTINCT utente_id) FILTER (WHERE esito = 'riuscito' AND creato_il >= NOW() - INTERVAL '30 days') AS attivi,
        COUNT(*) FILTER (WHERE esito <> 'riuscito' AND creato_il >= NOW() - INTERVAL '7 days') AS non_riusciti,
        COUNT(*) AS totale
     FROM registro_accessi"
)->fetch(PDO::FETCH_ASSOC);

// ── Grafico: ultimi 30 giorni, riusciti / non riusciti ──────────
$giorni = $pdo->query(
    "SELECT to_char(g.giorno, 'YYYY-MM-DD') AS giorno,
            COUNT(r.id) FILTER (WHERE r.esito = 'riuscito')  AS ok,
            COUNT(r.id) FILTER (WHERE r.esito <> 'riuscito') AS ko
       FROM generate_series((NOW() AT TIME ZONE '" . TZ_ITALIA . "')::date - 29,
                            (NOW() AT TIME ZONE '" . TZ_ITALIA . "')::date,
                            INTERVAL '1 day') AS g(giorno)
       LEFT JOIN registro_accessi r
              ON (r.creato_il AT TIME ZONE '" . TZ_ITALIA . "')::date = g.giorno::date
      GROUP BY g.giorno
      ORDER BY g.giorno"
)->fetchAll(PDO::FETCH_ASSOC);
$maxGiorno = max(1, ...array_map(fn($g) => (int)$g['ok'] + (int)$g['ko'], $giorni));

// ── Accessi per utente (periodo filtrato, tutti gli utenti e gli esiti) ──
$stmt = $pdo->prepare(
    "SELECT COALESCE(u.username, r.username) AS username, r.utente_id,
            COUNT(*) FILTER (WHERE r.esito = 'riuscito')  AS riusciti,
            COUNT(*) FILTER (WHERE r.esito = 'fallito')   AS falliti,
            COUNT(*) FILTER (WHERE r.esito = 'bloccato')  AS bloccati,
            to_char(MAX(r.creato_il) FILTER (WHERE r.esito = 'riuscito') AT TIME ZONE '" . TZ_ITALIA . "',
                    'DD/MM/YYYY HH24:MI') AS ultimo_ok
       FROM registro_accessi r
       LEFT JOIN utenti u ON u.id = r.utente_id
      WHERE (r.creato_il AT TIME ZONE '" . TZ_ITALIA . "')::date BETWEEN ?::date AND ?::date
      GROUP BY COALESCE(u.username, r.username), r.utente_id
      ORDER BY riusciti DESC, username"
);
$stmt->execute([$filtroDal, $filtroAl]);
$perUtente = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Registro (filtrato e paginato) ──────────────────────────────
$stmt = $pdo->prepare("SELECT COUNT(*) FROM registro_accessi r WHERE $whereSql");
$stmt->execute($params);
$totaleRighe = (int)$stmt->fetchColumn();
$pagine = max(1, (int)ceil($totaleRighe / ACCESSI_PER_PAGINA));
$pagina = min($pagina, $pagine);

$stmt = $pdo->prepare(
    "SELECT r.id, r.utente_id, r.username, u.username AS username_attuale, r.esito, r.motivo,
            r.ip_hash, r.user_agent,
            to_char(r.creato_il AT TIME ZONE '" . TZ_ITALIA . "', 'DD/MM/YYYY HH24:MI:SS') AS quando
       FROM registro_accessi r
       LEFT JOIN utenti u ON u.id = r.utente_id
      WHERE $whereSql
      ORDER BY r.creato_il DESC, r.id DESC
      LIMIT " . ACCESSI_PER_PAGINA . " OFFSET " . (($pagina - 1) * ACCESSI_PER_PAGINA)
);
$stmt->execute($params);
$righe = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Utenti per il filtro
$utentiFiltro = $pdo->query("SELECT id, username FROM utenti ORDER BY LOWER(username)")->fetchAll(PDO::FETCH_ASSOC);

// ── Helper di presentazione ─────────────────────────────────────
/** Browser e sistema operativo leggibili dallo user-agent (riconoscimento semplice). */
function accessiDescriviBrowser(?string $ua): string
{
    if ($ua === null || $ua === '') { return '—'; }
    $browser = 'Altro browser';
    if (preg_match('#Edg(e|A|iOS)?/#', $ua))           { $browser = 'Edge'; }
    elseif (preg_match('#OPR/|Opera#', $ua))           { $browser = 'Opera'; }
    elseif (preg_match('#SamsungBrowser/#', $ua))      { $browser = 'Samsung Internet'; }
    elseif (preg_match('#Firefox/|FxiOS/#', $ua))      { $browser = 'Firefox'; }
    elseif (preg_match('#Chrome/|CriOS/#', $ua))       { $browser = 'Chrome'; }
    elseif (preg_match('#Safari/#', $ua))              { $browser = 'Safari'; }
    elseif (preg_match('#curl|python|bot|spider#i', $ua)) { $browser = 'Script / bot'; }

    $sistema = '';
    if (preg_match('#iPhone#', $ua))                   { $sistema = 'iPhone'; }
    elseif (preg_match('#iPad#', $ua))                 { $sistema = 'iPad'; }
    elseif (preg_match('#Android#', $ua))              { $sistema = 'Android'; }
    elseif (preg_match('#Windows#', $ua))              { $sistema = 'Windows'; }
    elseif (preg_match('#Mac OS X|Macintosh#', $ua))   { $sistema = 'macOS'; }
    elseif (preg_match('#CrOS#', $ua))                 { $sistema = 'ChromeOS'; }
    elseif (preg_match('#Linux#', $ua))                { $sistema = 'Linux'; }

    return $sistema !== '' ? "$browser · $sistema" : $browser;
}

$etichetteMotivo = [
    'credenziali'          => 'Credenziali errate',
    'email_non_verificata' => 'Email non verificata',
    'account_non_attivo'   => 'Account non attivo',
    'account_bloccato'     => 'Account bloccato (troppi errori)',
    'ip_bloccato'          => 'IP bloccato (troppi errori)',
];

/** Query string dei filtri correnti, con eventuali valori sostituiti. */
function accessiQuery(array $sostituisci = []): string
{
    global $filtroDal, $filtroAl, $filtroUtente, $filtroEsito;
    $q = array_merge(
        ['dal' => $filtroDal, 'al' => $filtroAl, 'utente' => $filtroUtente, 'esito' => $filtroEsito],
        $sostituisci
    );
    return http_build_query(array_filter($q, fn($v) => $v !== '' && $v !== 0));
}

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
// Filtri correnti come campi nascosti: i form POST li ripassano per tornare alla stessa vista
$campiFiltri = '';
foreach (['dal' => $filtroDal, 'al' => $filtroAl, 'utente' => $filtroUtente, 'esito' => $filtroEsito] as $k => $v) {
    $campiFiltri .= '<input type="hidden" name="' . $k . '" value="' . $h($v) . '">';
}
$paginaAttiva = 'accessi';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Registro Accessi — AstroLab</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Eb+Garamond:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <style>
        /* Stili solo di questa pagina (nessuna modifica a style.css) */
        .acc-sezione-titolo { font-size: 14px; color: #2C3E6B; margin: 0 0 10px; font-weight: 600; letter-spacing: .03em; }
        .acc-nota { font-size: 11px; color: #888; margin-top: 6px; }
        .acc-grafico { display: flex; align-items: flex-end; gap: 2px; height: 180px; padding: 8px 0 0; border-bottom: 1px solid #DDD; }
        .acc-col { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; gap: 2px; cursor: default; position: relative; }
        .acc-col:hover { background: rgba(44, 62, 107, 0.06); }
        .acc-bar-ok { background: #4CAF50; border-radius: 4px 4px 0 0; }
        .acc-bar-ko { background: #C0392B; border-radius: 4px 4px 0 0; }
        .acc-assi { display: flex; justify-content: space-between; font-size: 10px; color: #888; margin-top: 4px; }
        .acc-legenda { display: flex; gap: 16px; font-size: 11px; color: #555; margin-top: 8px; }
        .acc-legenda span::before { content: ''; display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin-right: 5px; vertical-align: -1px; }
        .acc-legenda .ok::before { background: #4CAF50; }
        .acc-legenda .ko::before { background: #C0392B; }
        .acc-tooltip { display: none; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 6px;
                       background: #2C2C2C; color: #FFF; font-size: 11px; padding: 5px 8px; border-radius: 4px; white-space: nowrap; z-index: 5; }
        .acc-col:hover .acc-tooltip { display: block; }
        .acc-filtri { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        .acc-filtri label { display: flex; flex-direction: column; gap: 4px; font-size: 11px; color: #666; }
        .acc-filtri input, .acc-filtri select { padding: 6px 8px; border: 1px solid #CCC; border-radius: 6px; font-size: 13px; font-family: inherit; }
        .acc-esito { display: inline-block; padding: 2px 9px; border-radius: 12px; font-size: 11px; white-space: nowrap; }
        .acc-esito-riuscito { background: #E8F5E9; color: #2E7D32; }
        .acc-esito-fallito  { background: #FDECEA; color: #B03A2E; }
        .acc-esito-bloccato { background: #FFF3E0; color: #A0522D; }
        .acc-paginazione { display: flex; gap: 8px; align-items: center; justify-content: center; margin-top: 12px; font-size: 13px; }
        .acc-paginazione a { color: #2C3E6B; }
        .acc-ip { font-family: monospace; font-size: 11px; color: #888; }
        .acc-vuoto { text-align: center; color: #888; padding: 18px; font-size: 13px; }
        .acc-griglia { display: grid; grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); gap: 16px; margin-bottom: 16px; align-items: start; }
        @media (max-width: 900px) { .acc-griglia { grid-template-columns: minmax(0, 1fr); } }
        .acc-griglia .card { margin-bottom: 0; }
        .acc-tabella-scroll { overflow-x: auto; }
        .acc-pulizia { display: flex; flex-wrap: wrap; gap: 10px 22px; align-items: flex-end; }
        .acc-pulizia form { display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap; margin: 0; }
        .acc-pulizia label { display: flex; flex-direction: column; gap: 4px; font-size: 11px; color: #666; }
        .acc-pulizia input[type=number], .acc-pulizia input[type=text] { padding: 6px 8px; border: 1px solid #CCC; border-radius: 6px; font-size: 13px; font-family: inherit; width: 90px; }
        .acc-btn-elimina { background: #FFF; color: #B03A2E; border: 1px solid #E3B4AE; border-radius: 6px; padding: 7px 12px; font-size: 12px; cursor: pointer; font-family: inherit; }
        .acc-btn-elimina:hover { background: #FDECEA; }
        .acc-btn-elimina:disabled { opacity: .45; cursor: default; background: #FFF; }
        .acc-btn-riga { background: none; border: none; cursor: pointer; font-size: 14px; padding: 2px 4px; opacity: .6; }
        .acc-btn-riga:hover { opacity: 1; }
        .acc-barra-selezione { display: flex; gap: 12px; align-items: center; margin-bottom: 10px; font-size: 12px; color: #666; }
    </style>
</head>
<body>

<?php include 'includes/header_nav.php'; ?>

<main>
    <div class="page-title">
        <h2>Registro Accessi</h2>
        <a class="btn-secondary" href="admin_utenti.php" style="text-decoration:none">← Gestione Utenti</a>
    </div>

    <?php if ($messaggio): ?>
    <div class="<?= $messaggio['tipo'] === 'success' ? 'msg-success' : 'msg-error' ?>" style="margin-bottom:14px">
        <?= $messaggio['tipo'] === 'success' ? '✅' : '⚠️' ?> <?= $h($messaggio['testo']) ?>
    </div>
    <?php endif; ?>

    <!-- ── Riepilogo ─────────────────────────────────────────────── -->
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-num" style="color:#4CAF50"><?= (int)$riepilogo['oggi'] ?></span>
            <span class="stat-lbl">Accessi oggi</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?= (int)$riepilogo['sette'] ?></span>
            <span class="stat-lbl">Ultimi 7 giorni</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?= (int)$riepilogo['trenta'] ?></span>
            <span class="stat-lbl">Ultimi 30 giorni</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat-item">
            <span class="stat-num" style="color:#5A7AB0"><?= (int)$riepilogo['attivi'] ?></span>
            <span class="stat-lbl">Astrologi attivi (30 gg)</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat-item">
            <span class="stat-num" style="color:#C0392B"><?= (int)$riepilogo['non_riusciti'] ?></span>
            <span class="stat-lbl">Falliti / bloccati (7 gg)</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat-item">
            <span class="stat-num" style="color:#888"><?= (int)$riepilogo['totale'] ?></span>
            <span class="stat-lbl">Righe nel registro</span>
        </div>
    </div>

    <div class="acc-griglia">
        <!-- ── Grafico 30 giorni ──────────────────────────────────── -->
        <div class="card">
            <h3 class="acc-sezione-titolo">Accessi degli ultimi 30 giorni</h3>
            <div class="acc-grafico" role="img" aria-label="Accessi riusciti e non riusciti per giorno, ultimi 30 giorni">
                <?php foreach ($giorni as $g):
                    $ok = (int)$g['ok']; $ko = (int)$g['ko'];
                    $dataIt = DateTime::createFromFormat('Y-m-d', $g['giorno'])->format('d/m/Y');
                ?>
                <div class="acc-col">
                    <span class="acc-tooltip"><?= $h($dataIt) ?> — riusciti <?= $ok ?>, non riusciti <?= $ko ?></span>
                    <?php if ($ko > 0): ?><div class="acc-bar-ko" style="height:<?= round($ko / $maxGiorno * 100, 2) ?>%"></div><?php endif; ?>
                    <?php if ($ok > 0): ?><div class="acc-bar-ok" style="height:<?= round($ok / $maxGiorno * 100, 2) ?>%"></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="acc-assi">
                <span><?= $h(DateTime::createFromFormat('Y-m-d', $giorni[0]['giorno'])->format('d/m')) ?></span>
                <span>max <?= $maxGiorno ?> al giorno</span>
                <span>oggi</span>
            </div>
            <div class="acc-legenda"><span class="ok">Riusciti</span><span class="ko">Falliti / bloccati</span></div>
        </div>

        <!-- ── Accessi per utente ─────────────────────────────────── -->
        <div class="card">
            <h3 class="acc-sezione-titolo">Accessi per astrologo (periodo selezionato)</h3>
            <?php if (!$perUtente): ?>
                <div class="acc-vuoto">Nessun accesso nel periodo.</div>
            <?php else: ?>
            <div class="acc-tabella-scroll">
            <table class="tabella-soggetti">
                <thead><tr><th>Username</th><th>Riusciti</th><th>Falliti</th><th>Bloccati</th><th>Ultimo accesso</th></tr></thead>
                <tbody>
                <?php foreach ($perUtente as $pu): ?>
                    <tr>
                        <td><b><?= $h($pu['username']) ?></b><?= $pu['utente_id'] === null ? ' <span style="font-size:10px;color:#888">(inesistente)</span>' : '' ?></td>
                        <td><?= (int)$pu['riusciti'] ?></td>
                        <td><?= (int)$pu['falliti'] ?></td>
                        <td><?= (int)$pu['bloccati'] ?></td>
                        <td style="font-size:11px;color:#666;white-space:nowrap"><?= $h($pu['ultimo_ok'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Filtri ────────────────────────────────────────────────── -->
    <div class="card">
        <form method="GET" class="acc-filtri">
            <label>Dal <input type="date" name="dal" value="<?= $h($filtroDal) ?>"></label>
            <label>Al <input type="date" name="al" value="<?= $h($filtroAl) ?>"></label>
            <label>Astrologo
                <select name="utente">
                    <option value="0">Tutti</option>
                    <?php foreach ($utentiFiltro as $uf): ?>
                    <option value="<?= (int)$uf['id'] ?>" <?= $filtroUtente === (int)$uf['id'] ? 'selected' : '' ?>><?= $h($uf['username']) ?></option>
                    <?php endforeach; ?>
                    <option value="-1" <?= $filtroUtente === -1 ? 'selected' : '' ?>>— Username inesistenti —</option>
                </select>
            </label>
            <label>Esito
                <select name="esito">
                    <option value="">Tutti</option>
                    <option value="riuscito" <?= $filtroEsito === 'riuscito' ? 'selected' : '' ?>>Riuscito</option>
                    <option value="fallito"  <?= $filtroEsito === 'fallito'  ? 'selected' : '' ?>>Fallito</option>
                    <option value="bloccato" <?= $filtroEsito === 'bloccato' ? 'selected' : '' ?>>Bloccato</option>
                </select>
            </label>
            <button type="submit" class="btn-primary">Filtra</button>
            <a href="admin_accessi.php" style="font-size:12px;color:#2C3E6B;align-self:center">Azzera filtri</a>
        </form>
    </div>

    <!-- ── Registro ──────────────────────────────────────────────── -->
    <div class="card">
        <h3 class="acc-sezione-titolo">Registro (<?= $totaleRighe ?> righe nel periodo)</h3>
        <?php if (!$righe): ?>
            <div class="acc-vuoto">Nessun accesso con questi filtri.</div>
        <?php else: ?>
        <form method="POST" id="acc-form-selezione">
        <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['admin_accessi_csrf']) ?>">
        <input type="hidden" name="azione" value="selezionate">
        <?= $campiFiltri ?>
        <div class="acc-barra-selezione">
            <button type="submit" class="acc-btn-elimina" id="acc-btn-selezionate" disabled
                    onclick="return confirm('Eliminare dal registro le ' + accSelezionate() + ' righe selezionate?')">🗑️ Elimina selezionate (<span id="acc-n-sel">0</span>)</button>
        </div>
        <div class="acc-tabella-scroll">
        <table class="tabella-soggetti">
            <thead>
                <tr>
                    <th style="width:28px"><input type="checkbox" id="acc-sel-tutte" title="Seleziona tutte le righe di questa pagina"></th>
                    <th>Data e ora</th>
                    <th>Username</th>
                    <th>Esito</th>
                    <th>Dettaglio</th>
                    <th>Browser e dispositivo</th>
                    <th title="Impronta dell'indirizzo IP (hash): righe con la stessa impronta vengono dallo stesso IP">IP (impronta)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($righe as $r): ?>
                <tr>
                    <td><input type="checkbox" class="acc-sel" name="ids[]" value="<?= (int)$r['id'] ?>"></td>
                    <td style="white-space:nowrap;font-size:12px"><?= $h($r['quando']) ?></td>
                    <td>
                        <b><?= $h($r['username']) ?></b>
                        <?php if ($r['utente_id'] === null): ?>
                            <span style="font-size:10px;color:#888">(inesistente)</span>
                        <?php elseif ($r['username_attuale'] !== null && strcasecmp($r['username_attuale'], $r['username']) !== 0): ?>
                            <span style="font-size:10px;color:#888">(ora <?= $h($r['username_attuale']) ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="acc-esito acc-esito-<?= $h($r['esito']) ?>"><?= $h(ucfirst($r['esito'])) ?></span></td>
                    <td style="font-size:12px;color:#666"><?= $h($r['motivo'] !== null ? ($etichetteMotivo[$r['motivo']] ?? $r['motivo']) : '—') ?></td>
                    <td style="font-size:12px" title="<?= $h($r['user_agent'] ?? '') ?>"><?= $h(accessiDescriviBrowser($r['user_agent'])) ?></td>
                    <td class="acc-ip"><?= $h(substr($r['ip_hash'], 0, 8)) ?></td>
                    <td><button type="submit" class="acc-btn-riga" name="elimina_id" value="<?= (int)$r['id'] ?>" title="Elimina questa riga"
                                onclick="return confirm('Eliminare questa riga dal registro?')">🗑️</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        </form>
        <?php if ($pagine > 1): ?>
        <div class="acc-paginazione">
            <?php if ($pagina > 1): ?><a href="?<?= $h(accessiQuery(['pagina' => $pagina - 1])) ?>">← Precedente</a><?php endif; ?>
            <span>Pagina <?= $pagina ?> di <?= $pagine ?></span>
            <?php if ($pagina < $pagine): ?><a href="?<?= $h(accessiQuery(['pagina' => $pagina + 1])) ?>">Successiva →</a><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <p class="acc-nota">Sono registrati solo i login degli astrologi. L'IP è conservato solo come impronta (hash), mai in chiaro. Le righe più vecchie di 6 mesi vengono eliminate automaticamente.</p>
    </div>

    <!-- ── Pulizia del registro ──────────────────────────────────── -->
    <div class="card">
        <h3 class="acc-sezione-titolo">Pulizia del registro</h3>
        <div class="acc-pulizia">
            <form method="POST" onsubmit="return confirm('Eliminare tutte le <?= $totaleRighe ?> righe che corrispondono ai filtri correnti?')">
                <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['admin_accessi_csrf']) ?>">
                <input type="hidden" name="azione" value="filtrate">
                <?= $campiFiltri ?>
                <button type="submit" class="acc-btn-elimina" <?= $totaleRighe === 0 ? 'disabled' : '' ?>>🗑️ Elimina le <?= $totaleRighe ?> righe dei filtri correnti</button>
            </form>
            <form method="POST" onsubmit="return confirm('Eliminare tutte le righe più vecchie di ' + this.giorni.value + ' giorni?')">
                <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['admin_accessi_csrf']) ?>">
                <input type="hidden" name="azione" value="vecchie">
                <?= $campiFiltri ?>
                <label>Più vecchie di (giorni) <input type="number" name="giorni" min="1" max="3650" value="90" required></label>
                <button type="submit" class="acc-btn-elimina">🗑️ Elimina</button>
            </form>
            <form method="POST" onsubmit="return confirm('Svuotare COMPLETAMENTE il registro accessi? L\'operazione non si può annullare.')">
                <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['admin_accessi_csrf']) ?>">
                <input type="hidden" name="azione" value="tutto">
                <?= $campiFiltri ?>
                <label>Scrivi ELIMINA per confermare <input type="text" name="conferma_testo" autocomplete="off" required pattern="ELIMINA"></label>
                <button type="submit" class="acc-btn-elimina" <?= (int)$riepilogo['totale'] === 0 ? 'disabled' : '' ?>>🗑️ Svuota tutto il registro</button>
            </form>
        </div>
        <p class="acc-nota">Le righe eliminate non si possono recuperare.</p>
    </div>
</main>

<script>
// Selezione delle righe del registro: abilita il pulsante e aggiorna il conteggio
function accSelezionate() {
    return document.querySelectorAll('.acc-sel:checked').length;
}
function accAggiornaSelezione() {
    const n = accSelezionate();
    const btn = document.getElementById('acc-btn-selezionate');
    const lbl = document.getElementById('acc-n-sel');
    if (btn) { btn.disabled = n === 0; }
    if (lbl) { lbl.textContent = n; }
    const tutte = document.getElementById('acc-sel-tutte');
    const tot = document.querySelectorAll('.acc-sel').length;
    if (tutte) { tutte.checked = tot > 0 && n === tot; tutte.indeterminate = n > 0 && n < tot; }
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.acc-sel').forEach(function (cb) {
        cb.addEventListener('change', accAggiornaSelezione);
    });
    const tutte = document.getElementById('acc-sel-tutte');
    if (tutte) {
        tutte.addEventListener('change', function () {
            document.querySelectorAll('.acc-sel').forEach(function (cb) { cb.checked = tutte.checked; });
            accAggiornaSelezione();
        });
    }
});
</script>

</body>
</html>
