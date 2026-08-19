<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/NascitaGmtHelper.php';
/**
 * stampa.php — Report Astrologico / Stampa e PDF
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * Permette di selezionare i moduli da includere nel report,
 * scegliere formato (A4/A3) e orientamento, quindi:
 *   a) Stampare dal browser (window.print con CSS @media print)
 *   b) Scaricare un PDF nativo generato server-side via Dompdf
 *
 * NOTA SVG: le ruote zodiacali vengono disegnate lato client da
 * zodiac_wheel.js. Prima della stampa/PDF, il JS serializza gli SVG
 * e li inietta nel form POST verso stampa_pdf_api.php.
 */
session_start();
require_once 'includes/Auth.php';

$pdo  = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();

$isAdmin        = $auth->isAdmin();
$username       = $auth->getCurrentUsername();
$soggettoAttivo = $auth->getSoggettoAttivo();
$soggettoNome   = $auth->getSoggettoNome();

// Parametri URL: possono arrivare da rs.php, rl.php, rilocazione.php
$id             = intval($_GET['id']            ?? $soggettoAttivo ?? 0);
$annoRS_Url     = isset($_GET['anno'])          ? (int)$_GET['anno']         : (int)date('Y');
$latRS_Url      = isset($_GET['lat_rs'])        ? (float)$_GET['lat_rs']     : null;
$lonRS_Url      = isset($_GET['lon_rs'])        ? (float)$_GET['lon_rs']     : null;
$luogoRS_Url    = $_GET['luogo_rs']             ?? '';
$condizione_Url = $_GET['condizione']           ?? 'Decima';
$rlIndex_Url    = isset($_GET['rl_index'])      ? (int)$_GET['rl_index']     : 0;
$latRL_Url      = isset($_GET['lat_rl'])        ? (float)$_GET['lat_rl']     : null;
$lonRL_Url      = isset($_GET['lon_rl'])        ? (float)$_GET['lon_rl']     : null;
$luogoRL_Url    = $_GET['luogo_rl']             ?? '';
$latRiloc_Url   = isset($_GET['lat_riloc'])     ? (float)$_GET['lat_riloc']  : null;
$lonRiloc_Url   = isset($_GET['lon_riloc'])     ? (float)$_GET['lon_riloc']  : null;
$luogoRiloc_Url = $_GET['luogo_riloc']          ?? '';

if ($id > 0) {
    $auth->setSoggettoAttivo($id);
    $soggettoNome = $auth->getSoggettoNome();
}

$soggetto = null;
if ($id) {
    $soggetto = $auth->verificaSoggetto($id);
}

$paginaAttiva = ''; // nessuna voce nav attiva

$condizioni = ['Decima','Lavoro','Amore','Salute','Denaro','Denaro Low','Casa'];
$annoCorrente = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Astrologico — Astrologia Attiva</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/print.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Symbols+2&display=swap" rel="stylesheet">

    <style>
        /* ── Pannello controlli ────────────────────────────────────── */
        .stampa-controlli {
            background: white;
            border-radius: 8px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            margin-bottom: 16px;
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            align-items: flex-start;
        }
        .controlli-col {
            flex: 1;
            min-width: 220px;
        }
        .controlli-col h3 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #667;
            margin-bottom: 12px;
            font-weight: normal;
            border-bottom: 1px solid #EDE8E0;
            padding-bottom: 6px;
        }

        /* ── Checkbox moduli ──────────────────────────────────────── */
        .modulo-check {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #E0D8CC;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.15s;
            background: #FAF8F5;
        }
        .modulo-check:hover { background: #F0EDE8; border-color: #C8BFB3; }
        .modulo-check.attivo { background: #EEF3FF; border-color: #6B8AC8; }
        .modulo-check input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }
        .modulo-check-info { flex: 1; }
        .modulo-check-info strong { font-size: 13px; color: #2C3E6B; display: block; }
        .modulo-check-info span   { font-size: 11px; color: #888; }
        .modulo-icon { font-size: 20px; }

        /* ── Select formato / orientamento ───────────────────────── */
        .formato-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
        }
        .formato-option {
            border: 2px solid #D0C8BC;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            text-align: center;
            background: #FAF8F5;
            transition: all 0.15s;
        }
        .formato-option:hover { border-color: #2C3E6B; }
        .formato-option.selezionato { border-color: #2C3E6B; background: #EEF3FF; }
        .formato-option input { display: none; }
        .formato-icon { font-size: 28px; display: block; margin-bottom: 4px; }
        .formato-label { font-size: 12px; color: #2C3E6B; font-weight: bold; display: block; }
        .formato-sub   { font-size: 10px; color: #888; }

        /* ── Bottoni azione ───────────────────────────────────────── */
        .stampa-azioni {
            display: flex;
            gap: 10px;
            flex-direction: column;
        }
        .btn-stampa-browser {
            background: #2C3E6B;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 14px;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-stampa-browser:hover { background: #3A5090; }
        .btn-stampa-pdf {
            background: #B71C1C;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 14px;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-stampa-pdf:hover { background: #C62828; }
        .btn-stampa-pdf:disabled { opacity: 0.5; cursor: not-allowed; }

        /* ── Nota informativa ────────────────────────────────────── */
        .nota-stampa {
            background: #FFF8E1;
            border: 1px solid #FFD54F;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 12px;
            color: #5D4037;
            margin-top: 10px;
            line-height: 1.6;
        }

        /* ── Anteprima ───────────────────────────────────────────── */
        .stampa-anteprima {
            background: #EDE8E0;
            border-radius: 8px;
            padding: 20px;
            min-height: 200px;
            text-align: center;
            color: #999;
            font-style: italic;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .anteprima-attiva {
            background: white;
            display: block;
            text-align: left;
            font-style: normal;
            color: inherit;
        }

        /* ── Spinner PDF ────────────────────────────────────────── */
        #pdf-spinner {
            display: none;
            text-align: center;
            padding: 20px;
            color: #B71C1C;
            font-size: 13px;
        }

        /* ── Sezione parametri aggiuntivi ────────────────────────── */
        .param-group {
            background: #FAF8F5;
            border: 1px solid #E0D8CC;
            border-radius: 6px;
            padding: 10px 14px;
            margin-top: 8px;
        }
        .param-group label {
            font-size: 11px;
            color: #667;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: block;
            margin-bottom: 3px;
        }
        .param-group select,
        .param-group input {
            width: 100%;
            border: 1px solid #D0C8BC;
            border-radius: 4px;
            padding: 5px 8px;
            font-size: 13px;
            font-family: inherit;
            background: white;
            margin-bottom: 8px;
        }
        .param-group select:last-child,
        .param-group input:last-child { margin-bottom: 0; }

        /* ─────────────────────────────────────────────────────────
           AREA DI STAMPA — visibile solo @media print e come
           anteprima JS. Al di fuori della stampa è nascosta.
           Le classi .print-page, .ruote-affiancate ecc. vengono
           iniettate dinamicamente da generaAnteprima().
        ───────────────────────────────────────────────────────── */
        #area-stampa { display: none; }

        @media print {
            #area-stampa { display: block !important; }
        }
    </style>
</head>
<body>
<?php include 'includes/header_nav.php'; ?>

<main>
    <div class="page-title">
        <h2>📄 Report Astrologico — Stampa / PDF</h2>
        <?php if ($soggetto): ?>
        <span style="font-size:13px;color:#667">
            Soggetto: <strong><?= htmlspecialchars($soggetto['nome']) ?></strong>
        </span>
        <?php endif; ?>
    </div>

    <?php if (!$soggetto): ?>
    <div class="card">
        <p class="empty">Seleziona un soggetto dalla <a href="index.php">lista soggetti</a>.</p>
    </div>
    <?php else: ?>

    <!-- ══════════════════════════════════════════════════════════
         PANNELLO CONTROLLI
    ══════════════════════════════════════════════════════════ -->
    <div class="stampa-controlli">

        <!-- Colonna 1: Selezione moduli -->
        <div class="controlli-col" style="flex:2">
            <h3>Moduli da includere</h3>

            <label class="modulo-check" id="check-natale-wrap">
                <span class="modulo-icon">☉</span>
                <div class="modulo-check-info">
                    <strong>Tema Natale</strong>
                    <span>Ruota zodiacale + tabella pianeti e case</span>
                </div>
                <input type="checkbox" id="check-natale" checked onchange="aggiornaCheckStyle(this)">
            </label>

            <label class="modulo-check attivo" id="check-rs-wrap">
                <span class="modulo-icon">↺</span>
                <div class="modulo-check-info">
                    <strong>Rivoluzione Solare (RSM)</strong>
                    <span>Ruota RS + valutazione stelline + VAL</span>
                </div>
                <input type="checkbox" id="check-rs" checked onchange="aggiornaCheckStyle(this)">
            </label>

            <div class="param-group" id="params-rs" style="margin-left:10px;margin-bottom:8px">
                <label>Anno RS</label>
                <input type="number" id="anno-rs-print"
                       value="<?= htmlspecialchars($annoRS_Url) ?>"
                       min="1900" max="2100">
                <label>Luogo RS</label>
                <input type="text" id="luogo-rs-print"
                       value="<?= htmlspecialchars($luogoRS_Url) ?>"
                       placeholder="Città, Paese">
                <input type="hidden" id="lat-rs-print"  value="<?= htmlspecialchars($latRS_Url ?? '') ?>">
                <input type="hidden" id="lon-rs-print"  value="<?= htmlspecialchars($lonRS_Url ?? '') ?>">
                <label>Condizione</label>
                <select id="condizione-print">
                    <?php foreach ($condizioni as $c): ?>
                    <option value="<?= $c ?>"
                        <?= $c === $condizione_Url ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label class="modulo-check" id="check-rl-wrap">
                <span class="modulo-icon">☽</span>
                <div class="modulo-check-info">
                    <strong>Rivoluzione Lunare</strong>
                    <span>Ruota RL + valutazione + tabella aspetti</span>
                </div>
                <input type="checkbox" id="check-rl" onchange="aggiornaCheckStyle(this)">
            </label>

            <div class="param-group" id="params-rl"
                 style="margin-left:10px;margin-bottom:8px;display:none">
                <label>Indice RL (0-12)</label>
                <input type="number" id="rl-index-print"
                       value="<?= htmlspecialchars($rlIndex_Url) ?>"
                       min="0" max="12">
                <label>Luogo RL</label>
                <input type="text" id="luogo-rl-print"
                       value="<?= htmlspecialchars($luogoRL_Url) ?>"
                       placeholder="Città, Paese">
                <input type="hidden" id="lat-rl-print"  value="<?= htmlspecialchars($latRL_Url ?? '') ?>">
                <input type="hidden" id="lon-rl-print"  value="<?= htmlspecialchars($lonRL_Url ?? '') ?>">
            </div>

            <label class="modulo-check" id="check-riloc-wrap">
                <span class="modulo-icon">☿</span>
                <div class="modulo-check-info">
                    <strong>Rilocazione</strong>
                    <span>Tema natale rilocato + confronto case</span>
                </div>
                <input type="checkbox" id="check-riloc" onchange="aggiornaCheckStyle(this)">
            </label>

            <div class="param-group" id="params-riloc"
                 style="margin-left:10px;margin-bottom:8px;display:none">
                <label>Città di Rilocazione</label>
                <input type="text" id="luogo-riloc-print"
                       value="<?= htmlspecialchars($luogoRiloc_Url) ?>"
                       placeholder="Città, Paese">
                <input type="hidden" id="lat-riloc-print"  value="<?= htmlspecialchars($latRiloc_Url ?? '') ?>">
                <input type="hidden" id="lon-riloc-print"  value="<?= htmlspecialchars($lonRiloc_Url ?? '') ?>">
            </div>
        </div>

        <!-- Colonna 2: Formato e orientamento -->
        <div class="controlli-col">
            <h3>Formato pagina</h3>
            <div class="formato-grid">
                <label class="formato-option selezionato" id="opt-a4-portrait">
                    <input type="radio" name="formato" value="a4-portrait" checked>
                    <span class="formato-icon">📄</span>
                    <span class="formato-label">A4 Verticale</span>
                    <span class="formato-sub">210 × 297 mm</span>
                </label>
                <label class="formato-option" id="opt-a4-landscape">
                    <input type="radio" name="formato" value="a4-landscape">
                    <span class="formato-icon">📋</span>
                    <span class="formato-label">A4 Orizzontale</span>
                    <span class="formato-sub">297 × 210 mm</span>
                </label>
                <label class="formato-option" id="opt-a3-portrait">
                    <input type="radio" name="formato" value="a3-portrait">
                    <span class="formato-icon">📃</span>
                    <span class="formato-label">A3 Verticale</span>
                    <span class="formato-sub">297 × 420 mm</span>
                </label>
                <label class="formato-option" id="opt-a3-landscape">
                    <input type="radio" name="formato" value="a3-landscape">
                    <span class="formato-icon">🗒️</span>
                    <span class="formato-label">A3 Orizzontale</span>
                    <span class="formato-sub">420 × 297 mm</span>
                </label>
            </div>

            <div class="nota-stampa">
                💡 <strong>Confronto Natale + RS:</strong> quando entrambi i moduli sono selezionati,
                le due ruote vengono sempre affiancate in modalità landscape per la
                comparazione visiva tipica della scuola di Discepolo.
            </div>
        </div>

        <!-- Colonna 3: Azioni -->
        <div class="controlli-col" style="min-width:180px;max-width:220px">
            <h3>Azioni</h3>
            <div class="stampa-azioni">
                <button class="btn-stampa-browser" onclick="stampaDaBrowser()">
                    🖨️ Stampa da Browser
                </button>
                <button class="btn-stampa-pdf" id="btn-pdf" onclick="generaPDF()">
                    ⬇️ Scarica PDF
                </button>
            </div>

            <div id="pdf-spinner">
                ⟳ Generazione PDF in corso…<br>
                <small>Può richiedere alcuni secondi</small>
            </div>

            <div class="nota-stampa" style="margin-top:12px;font-size:11px">
                <strong>Stampa browser:</strong> usa "Salva come PDF" nella finestra di stampa.
                Imposta i margini su "Nessuno" per il layout ottimale.<br><br>
                <strong>PDF nativo:</strong> richiede Dompdf installato nel container
                (<code>composer require dompdf/dompdf</code>).
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         AREA ANTEPRIMA SCHERMO
    ══════════════════════════════════════════════════════════ -->
    <div class="card" id="card-anteprima">
        <h3 style="display:flex;justify-content:space-between;align-items:center">
            Anteprima Report
            <button class="btn-secondary" style="font-size:11px;padding:4px 12px"
                    onclick="generaAnteprima()">↺ Aggiorna anteprima</button>
        </h3>
        <div class="stampa-anteprima" id="anteprima-placeholder">
            Configura i moduli e premi "Aggiorna anteprima" per vedere il report.
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         AREA DI STAMPA (visibile solo in @media print)
         Questo div viene popolato da JS prima di window.print()
    ══════════════════════════════════════════════════════════ -->
    <div id="area-stampa"></div>

    <?php endif; ?>
</main>

<!-- Nessun form nascosto: il PDF viene richiesto via fetch() JSON con PNG base64 -->

<script src="js/zodiac_wheel.js"></script>
<script src="js/app.js"></script>
<script>
'use strict';

// ── Dati soggetto PHP → JS ──────────────────────────────────────────────
// Calcolo corretto data/ora GMT gestendo il cambio di giorno (una sola volta,
// riusato per giorno/mese/anno/ora_gmt cosi' che restino sempre coerenti tra loro)
$gmtDataStampa = $soggetto ? calcolaDataOraGmtCorretta(
    $soggetto['data_nascita'],
    $soggetto['ora_nascita'],
    (float)($soggetto['offset_gmt'] ?? 0)
) : null;
$dateGmtStampa = $gmtDataStampa ? new DateTime($gmtDataStampa['data_gmt'] . ' ' . $gmtDataStampa['ora_gmt']) : null;
$oraGmtPartsStampa = $gmtDataStampa ? explode(':', $gmtDataStampa['ora_gmt']) : null;

const DS_PRINT = <?= json_encode([
    'id'      => $soggetto ? (int)$soggetto['id'] : 0,
    'nome'    => $soggetto ? $soggetto['nome'] : '',
    'giorno'  => $soggetto ? (int)$dateGmtStampa->format('d') : 0,
    'mese'    => $soggetto ? (int)$dateGmtStampa->format('m') : 0,
    'anno'    => $soggetto ? (int)$dateGmtStampa->format('Y') : 0,
    'ora_gmt' => $soggetto ? (int)$oraGmtPartsStampa[0] + (int)($oraGmtPartsStampa[1] ?? 0) / 60 : 0,
    'lat'     => $soggetto ? (float)$soggetto['latitudine']  : 0,
    'lon'     => $soggetto ? (float)$soggetto['longitudine'] : 0,
    'luogo'   => $soggetto ? $soggetto['luogo_nascita']      : '',
    'nazione' => $soggetto ? $soggetto['nazione_nascita']    : '',
    'ora_loc' => $soggetto ? substr($soggetto['ora_nascita'], 0, 5) : '',
    'data_str'=> $soggetto ? (new DateTime($soggetto['data_nascita']))->format('d/m/Y') : '',
]) ?>;

// ── Dati tema natale in cache (caricato al DOMContentLoaded) ────────────
let _temaNataleCache = null;
let _temaRSCache     = null;
let _temaRLCache     = null;
let _temaRilocCache  = null;

// ── Stato formato ────────────────────────────────────────────────────────
let _formatoCorrente = 'a4-portrait';

// ════════════════════════════════════════════════════════════════════════
//  UI — Checkbox e radio
// ════════════════════════════════════════════════════════════════════════

function aggiornaCheckStyle(cb) {
    const wrap = cb.closest('.modulo-check');
    if (wrap) wrap.classList.toggle('attivo', cb.checked);

    // Mostra/nascondi pannello parametri associato
    const idMod = cb.id.replace('check-', 'params-');
    const panel = document.getElementById(idMod);
    if (panel) panel.style.display = cb.checked ? 'block' : 'none';
}

// Inizializza stili checkbox
document.querySelectorAll('.modulo-check input[type="checkbox"]').forEach(cb => {
    if (cb.checked) cb.closest('.modulo-check')?.classList.add('attivo');
});

// Radio formato
document.querySelectorAll('input[name="formato"]').forEach(radio => {
    radio.addEventListener('change', () => {
        _formatoCorrente = radio.value;
        document.querySelectorAll('.formato-option').forEach(o => o.classList.remove('selezionato'));
        radio.closest('.formato-option')?.classList.add('selezionato');
    });
});

// ════════════════════════════════════════════════════════════════════════
//  CARICAMENTO DATI ASTRONOMICI
// ════════════════════════════════════════════════════════════════════════

async function caricaTemaNatale() {
    if (!DS_PRINT.id) return null;
    const url = 'api/tema_api.php?tipo=natale' +
        '&g='       + DS_PRINT.giorno +
        '&m='       + DS_PRINT.mese   +
        '&a='       + DS_PRINT.anno   +
        '&ora_gmt=' + DS_PRINT.ora_gmt +
        '&lat='     + DS_PRINT.lat    +
        '&lon='     + DS_PRINT.lon;
    const r = await fetch(url);
    return r.json();
}

async function caricaTemaRS() {
    const anno  = document.getElementById('anno-rs-print').value;
    const latRS = document.getElementById('lat-rs-print').value || DS_PRINT.lat;
    const lonRS = document.getElementById('lon-rs-print').value || DS_PRINT.lon;
    const cond  = document.getElementById('condizione-print').value;
    const url = 'api/rs_api.php' +
        '?g='          + DS_PRINT.giorno  +
        '&m='          + DS_PRINT.mese    +
        '&a='          + DS_PRINT.anno    +
        '&ora_gmt='    + DS_PRINT.ora_gmt +
        '&lat='        + DS_PRINT.lat     +
        '&lon='        + DS_PRINT.lon     +
        '&anno='       + anno             +
        '&lat_rs='     + latRS            +
        '&lon_rs='     + lonRS            +
        '&condizione=' + encodeURIComponent(cond);
    const r = await fetch(url);
    return r.json();
}

async function caricaTemaRL() {
    const rlIdx = document.getElementById('rl-index-print').value;
    const latRL = document.getElementById('lat-rl-print').value || DS_PRINT.lat;
    const lonRL = document.getElementById('lon-rl-print').value || DS_PRINT.lon;
    const cond  = document.getElementById('condizione-print').value;
    const url = 'api/rl_api.php' +
        '?action=calcola' +
        '&soggetto_id=' + DS_PRINT.id     +
        '&anno_rs='     + document.getElementById('anno-rs-print').value +
        '&rl_index='    + rlIdx           +
        '&lat='         + latRL           +
        '&lon='         + lonRL           +
        '&condizione='  + encodeURIComponent(cond);
    const r = await fetch(url);
    return r.json();
}

async function caricaTemaRiloc() {
    const latRiloc = document.getElementById('lat-riloc-print').value || DS_PRINT.lat;
    const lonRiloc = document.getElementById('lon-riloc-print').value || DS_PRINT.lon;
    const url = 'api/tema_api.php?tipo=natale' +
        '&g='       + DS_PRINT.giorno  +
        '&m='       + DS_PRINT.mese    +
        '&a='       + DS_PRINT.anno    +
        '&ora_gmt=' + DS_PRINT.ora_gmt +
        '&lat='     + latRiloc         +
        '&lon='     + lonRiloc;
    const r = await fetch(url);
    return r.json();
}

// ════════════════════════════════════════════════════════════════════════
//  RENDERING SVG CON ZODIAC WHEEL
// ════════════════════════════════════════════════════════════════════════

// ════════════════════════════════════════════════════════════════════════
//  RENDERING RUOTE → PNG BASE64
// ════════════════════════════════════════════════════════════════════════

/**
 * Disegna una ruota zodiacale e la converte in PNG base64 via Canvas.
 * Restituisce una stringa "data:image/png;base64,..." pronta per <img src>.
 *
 * Questo approccio bypassa completamente i problemi di Dompdf con SVG inline:
 *  - Dompdf gestisce <img src="data:image/png;base64,..."> in modo nativo e affidabile.
 *  - Nessun html-escaping: il base64 è trasmesso via JSON senza alterazioni.
 */
function serializzaPNG(svgId, tema, size) {
    size = size || 480;

    return new Promise((resolve) => {
        // 1. Disegna l'SVG in un div temporaneo fuori schermo
        const tmp = document.createElement('div');
        tmp.style.cssText = 'position:fixed;left:-9999px;top:-9999px;opacity:0;pointer-events:none';
        const margin = Math.round(size * 0.12);
        const vbSize = size + margin * 2;
        tmp.innerHTML = `<svg id="${svgId}" width="${vbSize}" height="${vbSize}" viewBox="0 0 ${vbSize} ${vbSize}" xmlns="http://www.w3.org/2000/svg"></svg>`;
        document.body.appendChild(tmp);

        ZodiacWheel.disegna(svgId, tema, {size: size});

        const svgEl = document.getElementById(svgId);
        void svgEl.getBoundingClientRect();

        setTimeout(() => {
            // 2. Serializza l'SVG in stringa
            const serializer = new XMLSerializer();
            const svgStr = serializer.serializeToString(svgEl);
            document.body.removeChild(tmp);

            // 3. Crea un'immagine dall'SVG usando Blob URL
            const blob = new Blob([svgStr], {type: 'image/svg+xml;charset=utf-8'});
            const url  = URL.createObjectURL(blob);
            const img  = new Image();

            img.onload = () => {
                // 4. Disegna su Canvas e converti in PNG base64
                const canvas = document.createElement('canvas');
                canvas.width  = vbSize * 2; // @2x per qualità stampa
                canvas.height = vbSize * 2;
                const ctx = canvas.getContext('2d');
                ctx.scale(2, 2);
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, vbSize, vbSize);
                ctx.drawImage(img, 0, 0, vbSize, vbSize);
                URL.revokeObjectURL(url);
                resolve(canvas.toDataURL('image/png'));
            };

            img.onerror = () => {
                URL.revokeObjectURL(url);
                console.warn('[stampa] Errore conversione SVG→PNG per', svgId);
                resolve(''); // restituisce stringa vuota, non blocca il flusso
            };

            img.src = url;
        }, 200);
    });
}

// ════════════════════════════════════════════════════════════════════════
//  COSTRUZIONE HTML DEL REPORT
// ════════════════════════════════════════════════════════════════════════

const NOMI_PIA = {
    0:'☉ Sole',1:'☽ Luna',2:'☿ Mercurio',3:'♀ Venere',4:'♂ Marte',
    5:'♃ Giove',6:'♄ Saturno',7:'♅ Urano',8:'♆ Nettuno',9:'♇ Plutone',
    11:'☊ Nodo N.'
};
const NOMI_CASE = {
    1:'I',2:'II',3:'III',4:'IV',5:'V',6:'VI',
    7:'VII',8:'VIII',9:'IX',10:'X',11:'XI',12:'XII'
};

function buildHeaderReport() {
    const anno   = document.getElementById('anno-rs-print').value;
    const luogoRS = document.getElementById('luogo-rs-print').value;
    const cond   = document.getElementById('condizione-print').value;
    return `
    <div class="report-header">
        <div class="report-header-sinistra">
            <div class="report-titolo">☉ Astrologia Attiva</div>
            <div class="report-sottotitolo">Scuola di Ciro Discepolo — Rivoluzioni Solari Mirate</div>
        </div>
        <div class="report-header-destra">
            <div class="report-soggetto">${DS_PRINT.nome}</div>
            <div class="report-dati">
                Nato/a il ${DS_PRINT.data_str} — ${DS_PRINT.ora_loc} (loc.)
            </div>
            <div class="report-dati">Luogo nascita: ${DS_PRINT.luogo}, ${DS_PRINT.nazione}</div>
            ${luogoRS ? `<div class="report-dati">RS ${anno} — ${luogoRS} — Cond.: ${cond}</div>` : ''}
        </div>
    </div>`;
}

function buildTabellaPianeti(pianeti, title) {
    if (!pianeti) return '';
    let rows = '';
    Object.values(pianeti).forEach(p => {
        rows += `<tr>
            <td>${NOMI_PIA[p.id] ?? p.nome}</td>
            <td>${p.posizione?.stringa ?? '?'}</td>
            <td>${NOMI_CASE[p.casa] ?? p.casa}</td>
            <td>${p.retrogrado ? '<span style="color:#CC3333">℞</span>' : ''}</td>
        </tr>`;
    });
    return `
    <div class="report-tabella-wrap">
        <div class="report-tabella-title">${title}</div>
        <table class="report-tabella-pianeti">
            <thead><tr><th>Pianeta</th><th>Posizione</th><th>Casa</th><th></th></tr></thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

function buildTabellaCase(temaCase, title) {
    if (!temaCase) return '';
    let rows = '';
    for (let c = 1; c <= 12; c++) {
        if (!temaCase[c]) continue;
        const isAng = [1,4,7,10].includes(c);
        rows += `<tr ${isAng ? 'style="font-weight:bold;color:#2C3E6B"' : ''}>
            <td>${NOMI_CASE[c] ?? c}</td>
            <td>${temaCase[c].posizione?.stringa ?? '?'}</td>
        </tr>`;
    }
    return `
    <div class="report-tabella-wrap">
        <div class="report-tabella-title">${title}</div>
        <table class="report-tabella-pianeti">
            <thead><tr><th>Casa</th><th>Cuspide</th></tr></thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

function buildValutazione(val) {
    if (!val) return '';
    const stelle = (val.stelle_str || '').replace(/★/g,'<span style="color:#C8960C">★</span>');
    const veti = (val.veti || []).map(v =>
        `<div class="val-item-print val-veto-print">⛔ ${v}</div>`).join('');
    const bonus = (val.bonus || []).map(b =>
        `<div class="val-item-print val-bonus-print"><b>${b.codice}</b> ${b.nota ?? ''}</div>`).join('');
    const pen = (val.penalita || []).map(p =>
        `<div class="val-item-print val-pen-print"><b>${p.codice}</b> ${p.nota ?? ''}</div>`).join('');
    return `
    <div class="report-valutazione">
        <div class="val-header-print">
            <span class="stelle-print">${stelle}</span>
            <span class="val-str-print">${val.val ?? ''}</span>
            <span class="val-cond-print">Cond.: ${val.condizione ?? ''}</span>
        </div>
        <div class="val-body-print">
            ${veti}
            <div class="val-cols-print">
                <div>${bonus || '<span style="color:#999;font-size:10px">—</span>'}</div>
                <div>${pen  || '<span style="color:#999;font-size:10px">—</span>'}</div>
            </div>
        </div>
    </div>`;
}

/**
 * Costruisce l'HTML del report usando PNG base64 (<img>) per le ruote.
 * @param {boolean} perStampa — se true inserisce i div page-break
 */
async function buildReportHTML(perStampa) {
    const modNatale = document.getElementById('check-natale').checked;
    const modRS     = document.getElementById('check-rs').checked;
    const modRL     = document.getElementById('check-rl').checked;
    const modRiloc  = document.getElementById('check-riloc').checked;

    if (!modNatale && !modRS && !modRL && !modRiloc) {
        return '<p style="text-align:center;color:#999;padding:40px">Seleziona almeno un modulo.</p>';
    }

    const imgTag = (png) => png
        ? `<img src="${png}" style="max-width:100%;height:auto;display:block;margin:0 auto">`
        : '';

    let html = buildHeaderReport();

    if (modNatale || modRS) {
        const hasAffiancati = modNatale && modRS;
        html += `<div class="${hasAffiancati ? 'ruote-affiancate' : 'ruota-singola'}">`;

        if (modNatale) {
            if (!_temaNataleCache) _temaNataleCache = await caricaTemaNatale();
            const png = await serializzaPNG('_tmp_natale_svg', _temaNataleCache, 420);
            html += `
            <div class="ruota-col">
                <div class="ruota-title">☉ Tema Natale — ${DS_PRINT.nome}</div>
                <div class="ruota-svg-wrap">${imgTag(png)}</div>
                <div class="ruota-info">
                    ASC: ${_temaNataleCache.case?.ASC?.posizione?.stringa ?? '?'}
                    &nbsp;·&nbsp; MC: ${_temaNataleCache.case?.MC?.posizione?.stringa ?? '?'}
                </div>
                ${buildTabellaPianeti(_temaNataleCache.pianeti, 'Pianeti natali')}
                ${buildTabellaCase(_temaNataleCache.case, 'Case (Placido) — nascita')}
            </div>`;
        }

        if (modRS) {
            if (!_temaRSCache) _temaRSCache = await caricaTemaRS();
            const png  = await serializzaPNG('_tmp_rs_svg', _temaRSCache.tema_rs, 420);
            const anno = document.getElementById('anno-rs-print').value;
            const luogo= document.getElementById('luogo-rs-print').value || '—';
            html += `
            <div class="ruota-col">
                <div class="ruota-title">↺ RS ${anno} — ${luogo}</div>
                <div class="ruota-svg-wrap">${imgTag(png)}</div>
                <div class="ruota-info">
                    ASC: ${_temaRSCache.tema_rs?.case?.ASC?.posizione?.stringa ?? '?'}
                    &nbsp;·&nbsp; MC: ${_temaRSCache.tema_rs?.case?.MC?.posizione?.stringa ?? '?'}
                    &nbsp;·&nbsp; GMT: ${_temaRSCache.rs_gmt ?? '?'}
                </div>
                ${buildValutazione(_temaRSCache.valutazione)}
                ${buildTabellaPianeti(_temaRSCache.tema_rs?.pianeti, 'Pianeti RS')}
                ${buildTabellaCase(_temaRSCache.tema_rs?.case, 'Case (Placido) — RS')}
            </div>`;
        }

        html += '</div>';

        if (modRS && _temaRSCache?.relazione_annuale) {
            html += buildAnnualReportHTML(
                _temaRSCache.relazione_annuale
            );
        } else if (perStampa && (modRL || modRiloc)) {
            html += '<div class="page-break"></div>';
        }
    }

    if (modRL) {
        if (!_temaRLCache) _temaRLCache = await caricaTemaRL();
        const rlData = _temaRLCache;
        if (rlData?.ok && rlData.tema_rl) {
            const png     = await serializzaPNG('_tmp_rl_svg', rlData.tema_rl, 380);
            const luogoRL = document.getElementById('luogo-rl-print')?.value || '—';
            html += `
            <div class="report-section">
                <div class="report-section-title">☽ RL ${(parseInt(document.getElementById('rl-index-print')?.value||'0')+1)} — ${luogoRL}</div>
                <div class="ruote-affiancate">
                    <div class="ruota-col">
                        <div class="ruota-title">☽ Tema RL</div>
                        <div class="ruota-svg-wrap">${imgTag(png)}</div>
                        <div class="ruota-info">GMT: ${rlData.rl_gmt ?? '?'}</div>
                        ${buildValutazione(rlData.valutazione)}
                    </div>
                    <div class="ruota-col">
                        ${buildTabellaPianeti(rlData.tema_rl.pianeti, 'Pianeti RL')}
                        ${buildTabellaCase(rlData.tema_rl.case, 'Case (Placido) — RL')}
                    </div>
                </div>
            </div>`;
        }
        if (perStampa && modRiloc) html += '<div class="page-break"></div>';
    }

    if (modRiloc) {
        if (!_temaRilocCache) _temaRilocCache = await caricaTemaRiloc();
        if (!_temaNataleCache) _temaNataleCache = await caricaTemaNatale();
        const pngNat   = await serializzaPNG('_tmp_riloc_nat_svg', _temaNataleCache, 380);
        const pngRiloc = await serializzaPNG('_tmp_riloc_svg', _temaRilocCache, 380);
        const luogoRiloc = document.getElementById('luogo-riloc-print')?.value || '—';
        html += `
        <div class="report-section">
            <div class="report-section-title">☿ Rilocazione — ${luogoRiloc}</div>
            <div class="ruote-affiancate">
                <div class="ruota-col">
                    <div class="ruota-title">Natale (nascita)</div>
                    <div class="ruota-svg-wrap">${imgTag(pngNat)}</div>
                </div>
                <div class="ruota-col">
                    <div class="ruota-title">Natale rilocato — ${luogoRiloc}</div>
                    <div class="ruota-svg-wrap">${imgTag(pngRiloc)}</div>
                    <div class="ruota-info">
                        ASC: ${_temaRilocCache.case?.ASC?.posizione?.stringa ?? '?'}
                        · MC: ${_temaRilocCache.case?.MC?.posizione?.stringa ?? '?'}
                    </div>
                </div>
            </div>
            ${buildTabellaCase(_temaRilocCache.case, 'Case rilocate (Placido)')}
        </div>`;
    }

    html += `
    <div class="report-footer">
        Astrologia Attiva · Scuola di Ciro Discepolo · Generato il ${new Date().toLocaleDateString('it-IT')}
    </div>`;

    return html;
}

/**
 * Esegue l'escape HTML dei contenuti narrativi ricevuti dall'API.
 */
function escapeReportHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

/**
 * Converte un testo narrativo in paragrafi HTML sicuri.
 */
function reportParagraphs(text) {
    return String(text ?? '')
        .trim()
        .split(/\n\s*\n/)
        .map(paragraph => paragraph.trim())
        .filter(Boolean)
        .map(paragraph => `<p>${escapeReportHtml(paragraph)}</p>`)
        .join('');
}

/**
 * Renderizza il Report Annuale nell'anteprima e nella stampa browser.
 */
function buildAnnualReportHTML(report) {
    if (!report || !Array.isArray(report.sections) || report.sections.length === 0) {
        return '';
    }

    const title = escapeReportHtml(
        report.title || 'Rivoluzione Solare'
    );

    const methodologicalNote = String(
        report.methodological_note || ''
    ).trim();

    const sections = report.sections
        .filter(section => section && String(section.text || '').trim() !== '')
        .map(section => {
            const sectionTitle = String(section.title || '').trim();
            const titleHtml = sectionTitle !== ''
                ? `<div class="annual-report-section-title">${escapeReportHtml(sectionTitle)}</div>`
                : '';

            return `
                <div class="annual-report-section">
                    ${titleHtml}
                    <div class="annual-report-section-text">
                        ${reportParagraphs(section.text)}
                    </div>
                </div>`;
        })
        .join('');

    const noteHtml = methodologicalNote !== ''
        ? `<div class="annual-report-note">${reportParagraphs(methodologicalNote)}</div>`
        : '';

    return `
        <div class="page-break"></div>
        <div class="report-section annual-report-print">
            <div class="report-section-title">${title}</div>
            ${noteHtml}
            ${sections}
        </div>`;
}

// ════════════════════════════════════════════════════════════════════════
//  ANTEPRIMA
// ════════════════════════════════════════════════════════════════════════

async function generaAnteprima() {
    const placeholder = document.getElementById('anteprima-placeholder');
    if (placeholder) {
        placeholder.outerHTML = '<div id="anteprima-placeholder" class="stampa-anteprima anteprima-attiva">⟳ Caricamento…</div>';
    }
    _temaRSCache = null; _temaRLCache = null; _temaRilocCache = null;
    const html = await buildReportHTML(false);
    const wrap = document.getElementById('anteprima-placeholder');
    if (wrap) {
        wrap.classList.add('anteprima-attiva');
        wrap.innerHTML = `<div class="report-preview-inner ${_formatoCorrente}">${html}</div>`;
    }
}

// ════════════════════════════════════════════════════════════════════════
//  STAMPA DA BROWSER
// ════════════════════════════════════════════════════════════════════════

async function stampaDaBrowser() {
    _temaRSCache = null; _temaRLCache = null; _temaRilocCache = null;
    const html = await buildReportHTML(true);
    const area = document.getElementById('area-stampa');
    area.innerHTML = `<div class="report-print-root ${_formatoCorrente}">${html}</div>`;
    area.style.display = 'block';
    document.body.className = document.body.className.replace(/\bfmt-\S+/g, '').trim();
    document.body.classList.add('fmt-' + _formatoCorrente);
    window.print();
    setTimeout(() => {
        area.style.display = 'none';
        area.innerHTML = '';
        document.body.classList.remove('fmt-' + _formatoCorrente);
    }, 1000);
}

// ════════════════════════════════════════════════════════════════════════
//  GENERAZIONE PDF — fetch() JSON + Blob download
// ════════════════════════════════════════════════════════════════════════

async function generaPDF() {
    const btn  = document.getElementById('btn-pdf');
    const spin = document.getElementById('pdf-spinner');
    btn.disabled = true;
    spin.style.display = 'block';

    _temaRSCache = null; _temaRLCache = null; _temaRilocCache = null;

    const modNatale = document.getElementById('check-natale').checked;
    const modRS     = document.getElementById('check-rs').checked;
    const modRL     = document.getElementById('check-rl').checked;
    const modRiloc  = document.getElementById('check-riloc').checked;

    let pngNatale = '', pngRS = '', pngRL = '', pngRiloc = '';

    try {
        if (modNatale) {
            _temaNataleCache = await caricaTemaNatale();
            pngNatale = await serializzaPNG('_pdf_natale_svg', _temaNataleCache, 420);
        }
        if (modRS) {
            _temaRSCache = await caricaTemaRS();
            if (_temaRSCache?.tema_rs)
                pngRS = await serializzaPNG('_pdf_rs_svg', _temaRSCache.tema_rs, 420);
        }
        if (modRL) {
            _temaRLCache = await caricaTemaRL();
            if (_temaRLCache?.tema_rl)
                pngRL = await serializzaPNG('_pdf_rl_svg', _temaRLCache.tema_rl, 380);
        }
        if (modRiloc) {
            _temaRilocCache = await caricaTemaRiloc();
            if (_temaRilocCache)
                pngRiloc = await serializzaPNG('_pdf_riloc_svg', _temaRilocCache, 380);
        }
    } catch (err) {
        alert('Errore generazione grafici: ' + err.message);
        btn.disabled = false; spin.style.display = 'none';
        return;
    }

    const moduli = [
        modNatale ? 'natale' : '',
        modRS     ? 'rs'     : '',
        modRL     ? 'rl'     : '',
        modRiloc  ? 'riloc'  : '',
    ].filter(Boolean).join(',');

    const payload = {
        soggetto_id: DS_PRINT.id,
        formato:     _formatoCorrente,
        moduli,
        anno_rs:     document.getElementById('anno-rs-print').value,
        luogo_rs:    document.getElementById('luogo-rs-print').value,
        lat_rs:      document.getElementById('lat-rs-print').value,
        lon_rs:      document.getElementById('lon-rs-print').value,
        condizione:  document.getElementById('condizione-print').value,
        rl_index:    document.getElementById('rl-index-print')?.value  ?? '0',
        lat_rl:      document.getElementById('lat-rl-print')?.value    ?? '',
        lon_rl:      document.getElementById('lon-rl-print')?.value    ?? '',
        lat_riloc:   document.getElementById('lat-riloc-print')?.value ?? '',
        lon_riloc:   document.getElementById('lon-riloc-print')?.value ?? '',
        luogo_riloc: document.getElementById('luogo-riloc-print')?.value ?? '',
        png_natale:  pngNatale,
        png_rs:      pngRS,
        png_rl:        pngRL,
        png_riloc:     pngRiloc,
        annual_report: _temaRSCache?.relazione_annuale ?? {},
    };

    try {
        const resp = await fetch('api/stampa_pdf_api.php', {
            method:      'POST',
            credentials: 'same-origin',
            headers:     {'Content-Type': 'application/json'},
            body:        JSON.stringify(payload),
        });

        if (!resp.ok) {
            const txt = await resp.text();
            throw new Error(txt.substring(0, 300));
        }

        const blob = await resp.blob();
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = 'AstrologiaAttiva_' + DS_PRINT.nome.replace(/[^a-zA-Z0-9]/g,'_')
                     + '_' + new Date().toISOString().slice(0,10) + '.pdf';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 5000);

    } catch (err) {
        alert('Errore PDF: ' + err.message);
    } finally {
        btn.disabled = false;
        spin.style.display = 'none';
    }
}

// ── Init ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Pre-carica il tema natale in background
    if (DS_PRINT.id) {
        caricaTemaNatale().then(t => { _temaNataleCache = t; });
    }

    // Mostra/nascondi pannelli params iniziali
    ['rs','rl','riloc'].forEach(mod => {
        const cb = document.getElementById('check-' + mod);
        const panel = document.getElementById('params-' + mod);
        if (cb && panel) panel.style.display = cb.checked ? 'block' : 'none';
    });
});
</script>
</body>
</html>