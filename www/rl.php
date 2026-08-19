<?php
require_once __DIR__ . '/includes/bootstrap.php';
// ===== INIZIO PATCH AUTH MULTI-ASTROLOGO =====
session_start();
require_once 'includes/Auth.php';
 
$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();
 
$isAdmin        = $auth->isAdmin();
$username       = $auth->getCurrentUsername();
$soggettoAttivo = $auth->getSoggettoAttivo();
$soggettoNome   = $auth->getSoggettoNome();
 
$id = intval($_GET['id'] ?? $soggettoAttivo ?? 0);
if ($id > 0) { $auth->setSoggettoAttivo($id); $soggettoNome = $auth->getSoggettoNome(); }
// ===== FINE PATCH AUTH MULTI-ASTROLOGO =====
 
require_once 'includes/SweCalc.php';
 
$soggetto = null;
if ($id) {
    $soggetto = $auth->verificaSoggetto($id);
}
 
$latRL_Url   = isset($_GET['lat_rl'])  ? (float)$_GET['lat_rl']  : null;
$lonRL_Url   = isset($_GET['lon_rl'])  ? (float)$_GET['lon_rl']  : null;
$luogoRL_Url = $_GET['luogo_rl']       ?? null;
$annoRS_Url  = isset($_GET['anno'])    ? (int)$_GET['anno']       : null;
 
$annoCorrente = (int)date('Y');
$defaultLat   = 0;
$defaultLon   = 0;
$defaultLuogo = '';
 
$jsData = null;
if ($soggetto) {
    $defaultLat  = $latRL_Url  !== null ? $latRL_Url
                 : ($soggetto['residenza_latitudine']  ?: ($soggetto['latitudine'] ?? 0));
    $defaultLon  = $lonRL_Url  !== null ? $lonRL_Url
                 : ($soggetto['residenza_longitudine'] ?: ($soggetto['longitudine'] ?? 0));
    $defaultLuogo = $luogoRL_Url !== null ? $luogoRL_Url
                 : ($soggetto['residenza_luogo']
                    ? $soggetto['residenza_luogo'] . ($soggetto['residenza_nazione'] ? ', '.$soggetto['residenza_nazione'] : '')
                    : ($soggetto['luogo_nascita'] ?? ''));
 
    $date   = new DateTime($soggetto['data_nascita']);
    $oraGmt = explode(':', $soggetto['ora_nascita_gmt']);
    $jsData = [
        'id'         => $soggetto['id'],
        'nome'       => $soggetto['nome'],
        'giorno'     => (int)$date->format('d'),
        'mese'       => (int)$date->format('m'),
        'anno'       => (int)$date->format('Y'),
        'ora_gmt'    => (int)$oraGmt[0] + (int)$oraGmt[1]/60,
        'ora_loc'    => substr($soggetto['ora_nascita'], 0, 5),
        'lat'        => (float)$soggetto['latitudine'],
        'lon'        => (float)$soggetto['longitudine'],
        'luogo'      => $soggetto['luogo_nascita'],
        'nazione'    => $soggetto['nazione_nascita'],
        'offset'     => (float)($soggetto['offset_gmt'] ?? 0),
        'res_lat'    => $soggetto['residenza_latitudine']  ? (float)$soggetto['residenza_latitudine']  : null,
        'res_lon'    => $soggetto['residenza_longitudine'] ? (float)$soggetto['residenza_longitudine'] : null,
        'res_luogo'  => $soggetto['residenza_luogo']  ?: null,
        'res_nazione'=> $soggetto['residenza_nazione'] ?: null,
        'data_str'   => $date->format('d/m/Y'),
        'ora_loc_str'=> substr($soggetto['ora_nascita'],0,5),
        'ora_gmt_str'=> substr($soggetto['ora_nascita_gmt'],0,5),
    ];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rivoluzione Lunare — Astrologia Attiva</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/print.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Symbols+2&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>
<body>
<?php $paginaAttiva = 'rl'; include 'includes/header_nav.php'; ?>
 
<main>
<?php if (!$soggetto): ?>
    <div class="card">
        <p class="empty">Seleziona un soggetto dalla <a href="index.php">lista soggetti</a>.</p>
    </div>
<?php else: ?>
 
    <div class="header-soggetto">
        <div><span>Soggetto: </span><b><?= htmlspecialchars($soggetto['nome']) ?></b></div>
        <div><span>Nato il: </span><b><?= date('d/m/Y', strtotime($soggetto['data_nascita'])) ?></b></div>
        <div><span>Ore: </span><b><?= substr($soggetto['ora_nascita'],0,5) ?> (loc.) — <?= substr($soggetto['ora_nascita_gmt'],0,5) ?> GMT</b></div>
        <div><span>Nascita: </span><b><?= htmlspecialchars($soggetto['luogo_nascita'].' '.$soggetto['nazione_nascita']) ?></b></div>
        <?php if ($soggetto['residenza_luogo']): ?>
        <div><span>🏠 Residenza: </span><b><?= htmlspecialchars($soggetto['residenza_luogo'].($soggetto['residenza_nazione'] ? ', '.$soggetto['residenza_nazione'] : '')) ?></b></div>
        <?php endif; ?>
    </div>

    <div id="print-header-rl" class="print-only-header">
        <div class="print-header-left">
            <div class="print-h-nome" id="print-h-nome-rl"></div>
            <div id="print-h-nascita-rl"></div>
            <div id="print-h-ora-rl"></div>
        </div>
        <div class="print-header-right">
            <div class="print-h-gmt" id="print-h-gmt-rl"></div>
            <div id="print-h-ora-locale-rl"></div>
            <div id="print-h-luogo-rl"></div>
        </div>
    </div>
 
    <div class="controlli">
        <div class="form-group">
            <label>Anno RS di riferimento</label>
            <select id="anno-rs">
                <?php for($y = $annoCorrente - 2; $y <= $annoCorrente + 5; $y++): ?>
                <option value="<?= $y ?>" <?= ($annoRS_Url ?? $annoCorrente) == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="label-rl">☽ Rivoluzione Lunare</label>
            <select id="sel-rl" disabled>
                <option value="">— Calcola prima le RL —</option>
            </select>
        </div>
        <div class="form-group rl-location-group">
            <label>Luogo RL</label>
            <div class="luogo-wrap">
                <input type="text" id="luogo-rl-input" placeholder="Cerca città..."
                       value="<?= htmlspecialchars($defaultLuogo) ?>" class="flex-1">
                <button class="btn-search" onclick="RLModule.cercaLuogoRL()">🔍 Cerca</button>
            </div>
            <div id="luogo-rl-risultati" class="dropdown-risultati"></div>
        </div>
        <div class="form-group">
            <label>Lat</label>
            <input type="number" id="rl-lat" step="0.0001" value="<?= htmlspecialchars($defaultLat) ?>" class="rl-coordinate-input">
        </div>
        <div class="form-group">
            <label>Lon</label>
            <input type="number" id="rl-lon" step="0.0001" value="<?= htmlspecialchars($defaultLon) ?>" class="rl-coordinate-input">
        </div>
        <div class="rl-actions-row">
            <button class="btn-primary" id="btn-calcola-rl" onclick="RLModule.calcolaListaRL()">☽ Calcola RL</button>
            <button class="btn-mappa-rl is-hidden" id="btn-apri-mappa-rl" onclick="toggleMappaRL()">🌍 Mappa</button>
        </div>
    </div>
 
    <div class="header-rl" id="header-rl">
        <span class="rl-badge">☽ RL</span>
        <div><span>RL: </span><b id="rl-indice-label"></b></div>
        <div><span>GMT: </span><b id="rl-gmt-label"></b></div>
        <div><span>Luogo: </span><b id="rl-luogo-label"></b></div>
        <div><span>ASC: </span><b id="rl-asc-label">—</b></div>
        <div><span>MC: </span><b id="rl-mc-label">—</b></div>
    </div>
 
    <div class="page-title page-title-compact">
        <button class="btn-stampa-diretta" onclick="prepareStampaRL()">🖨️ Stampa Rivoluzione Lunare</button>
    </div>

    <div class="card is-hidden" id="card-salva-rl">
        <div class="rs-save-row">
            <div class="form-group rl-link-rs-group is-hidden" id="wrap-collega-rs">
                <label>Collega a sessione RS (opzionale)</label>
                <select id="salva-rl-sessione-rs">
                    <option value="">— Nessuna —</option>
                </select>
            </div>
            <div class="form-group rs-save-note-group">
                <label>Note per questa RL</label>
                <input type="text" id="salva-rl-note" placeholder="Es: mese più favorevole per...">
            </div>
            <button class="btn-rl" id="btn-salva-rl" onclick="RLModule.salvaSessioneRL()">💾 Salva questa RL</button>
        </div>
        <div id="salva-rl-msg" class="rs-save-message"></div>
    </div>
 
    <div class="card is-hidden" id="card-sessioni-rl">
        <h3 class="collapse-toggle" onclick="toggleCollapse('sessioni-rl')">
            ☽ Sessioni RL salvate per questo soggetto
            <span class="sensib-chevron" id="collapse-chevron-sessioni-rl">▶</span>
        </h3>
        <div id="collapse-body-sessioni-rl" style="display:none">
            <div id="lista-sessioni-rl"></div>
        </div>
    </div>
 
    <div class="rl-timeline" id="rl-timeline">
        <h4>☽ Tutte le Rivoluzioni Lunari dell'anno RS — click per selezionare</h4>
        <div class="rl-timeline-grid" id="rl-chips"></div>
    </div>
 
    <div id="rl-loading"><p>⟳ Calcolo in corso...</p></div>
 
    <div class="valutazione" id="valutazione">
        <h3 class="collapse-toggle" onclick="toggleCollapse('bonus-veti')">
            Bonus e Veti
            <span class="sensib-chevron" id="collapse-chevron-bonus-veti">▶</span>
        </h3>
        <div id="collapse-body-bonus-veti" style="display:none">
        <div class="val-header">
            <div class="stelle-grandi" id="val-stelle"></div>
            <div class="val-stringa"   id="val-stringa"></div>
            <div class="val-condizione" id="val-condizione">☽ Valutazione Rivoluzione Lunare</div>
        </div>
        <div class="val-grid">
            <div class="val-section"><h4>✅ Bonus</h4>    <div id="val-bonus"></div></div>
            <div class="val-section"><h4>⚠️ Penalità</h4> <div id="val-penali"></div></div>
        </div>
        <div id="val-veti"></div>
        </div>
    </div>
 
    <div class="temi-wrapper is-hidden" id="temi-wrapper">
        <div class="tema-box">
            <div class="tema-box-header">
                <button class="btn-toggle-gradi" id="btn-toggle-cuspidi"
                        onclick="toggleCuspidiCase()">Nascondi Cuspidi</button>
                <h3>Tema Natale</h3>
                <button class="btn-toggle-gradi" id="btn-toggle-gradi"
                        onclick="toggleGradiPianeti()">Mostra Gradi</button>
            </div>
            <svg id="wheel-natale" width="480" height="480" class="zodiac-wheel-responsive"></svg>
            <div class="tema-info-row">
                <p class="tema-info" id="info-natale">—</p>
                <button class="btn-toggle-gradi" id="btn-toggle-dati-natale" onclick="toggleDatiTabella('natale')">▼ Mostra Dati</button>
            </div>
            <div id="dati-natale" class="is-hidden">
            <table class="tabella-pianeti" id="tab-natale"></table>
            <div class="aspetti-container">
                <h4 class="rl-table-section-title">📐 Aspetti nella Rivoluzione Lunare</h4>
                <table class="tabella-aspetti">
                    <thead><tr><th>Pianeta 1</th><th></th><th>Pianeta 2</th><th>Aspetto</th><th>Orbe</th></tr></thead>
                    <tbody id="aspetti-rl-body">
                        <tr><td colspan="5" class="table-empty-cell">Nessun aspetto rilevante</td></tr>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
        <div class="tema-box">
            <h3 id="rl-titolo">Rivoluzione Lunare</h3>
            <div class="rl-loading-overlay" id="rl-overlay">⟳ Ricalcolo RL...</div>
            <svg id="wheel-rl" width="480" height="480" class="zodiac-wheel-responsive"></svg>
            <div class="tema-info-row">
                <p class="tema-info" id="info-rl">—</p>
                <button class="btn-toggle-gradi" id="btn-toggle-dati-rl" onclick="toggleDatiTabella('rl')">▼ Mostra Dati</button>
            </div>
            <div id="dati-rl" class="is-hidden">
            <table class="tabella-pianeti" id="tab-rl"></table>
            <div class="cuspidi-container">
                <h4 class="rl-table-section-title">🏠 Cuspidi Case RL</h4>
                <table class="tabella-cuspidi">
                    <thead><tr><th>Casa</th><th>Gradi Cuspide</th></tr></thead>
                    <tbody id="cuspidi-rl-body"><tr><td colspan="2" class="table-empty-cell">—</td></tr>
                </tbody>
            </table>
            </div>
            </div>
        </div>
    </div>

    <div id="print-aspetti-rl" class="print-only-aspetti"></div>
 
    <div id="mappa-rl-float" class="map-float-win">
        <div class="map-float-header" id="mappa-rl-drag-handle">
            <h3>🌍 Mappa RL — trascina il marker per ricalcolare in tempo reale</h3>
            <span class="map-float-coords" id="mappa-rl-coords">—</span>
            <button class="btn-close-float" onclick="chiudiMappaRL()" title="Chiudi">✕</button>
        </div>
        <div class="map-leaflet-inner">
            <div class="map-loading-overlay" id="mappa-rl-ricalcolo">⟳ Ricalcolo...</div>
            <div id="leaflet-map-rl" class="map-fill"></div>
        </div>
        <div class="map-float-footer">
            <span class="info-drag">Trascina il marker · la RL si aggiorna in tempo reale</span>
            <button class="btn-usa-pos" onclick="RLModule.usaPosizioneCorrente()">✓ Usa questa posizione</button>
        </div>
    </div>
 
<?php endif; ?>
</main>
 
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/zodiac_wheel.js"></script>
<script src="js/app.js"></script>
<script src="js/svg_zoom.js"></script>
<script src="js/rl.js"></script>
 
<?php if ($soggetto && $jsData): ?>
<script>
const DS           = <?= json_encode($jsData) ?>;
const DEFAULT_LAT  = <?= (float)$defaultLat ?>;
const DEFAULT_LON  = <?= (float)$defaultLon ?>;
const DEFAULT_LUOGO= <?= json_encode($defaultLuogo) ?>;
 
// Modifica 3: Funzione preparatoria per la stampa dei dati RL
function prepareStampaRL() {
    document.getElementById('print-h-nome-rl').textContent    = DS.nome;
    document.getElementById('print-h-nascita-rl').textContent = 'Nato il ' + DS.data_str;
    document.getElementById('print-h-ora-rl').textContent     = 'Ore ' + DS.ora_loc_str + ' (loc.) — ' + DS.ora_gmt_str + ' GMT';

    const indice = document.getElementById('rl-indice-label')?.textContent      || '';
    const gmt    = document.getElementById('rl-gmt-label')?.textContent         || '';
    const luogo  = document.getElementById('rl-luogo-label')?.textContent       || '';
    const oraLoc = document.getElementById('rl-ora-locale-wrap')?.textContent   || '';

    document.getElementById('print-h-gmt-rl').textContent        = 'RL ' + indice + ' — ' + gmt;
    document.getElementById('print-h-ora-locale-rl').textContent = oraLoc;

    const latRL = parseFloat(document.getElementById('rl-lat')?.value) || 0;
    const lonRL = parseFloat(document.getElementById('rl-lon')?.value) || 0;
    document.getElementById('print-h-luogo-rl').textContent =
        'Luogo: ' + luogo + '   Long ' + lonRL.toFixed(4) + '°   Lat ' + latRL.toFixed(4) + '°';

    buildAspettiOrizzontale('aspetti-rl-body', 'print-aspetti-rl');
    stampaPagina('print-rl');
}

// ── Drag finestra RL ──────────────────────────────────────────────────────
(function initDragRL() {
    const win    = document.getElementById('mappa-rl-float');
    const handle = document.getElementById('mappa-rl-drag-handle');
    let dragging = false, offX = 0, offY = 0;
 
    handle.addEventListener('mousedown', e => {
        if (e.target.classList.contains('btn-close-float')) return;
        dragging = true;
        const rect = win.getBoundingClientRect();
        if (win.style.position !== 'fixed') {
            win.style.position = 'fixed';
            win.style.right    = 'auto';
        }
        win.style.left = rect.left + 'px';
        win.style.top  = rect.top  + 'px';
        offX = e.clientX - rect.left;
        offY = e.clientY - rect.top;
        e.preventDefault();
    });
 
    document.addEventListener('mousemove', e => {
        if (!dragging) return;
        win.style.left = (e.clientX - offX) + 'px';
        win.style.top  = (e.clientY - offY) + 'px';
        RLModule._invalidateMap();
    });
 
    document.addEventListener('mouseup', () => { dragging = false; });
})();
 
new ResizeObserver(() => RLModule._invalidateMap())
    .observe(document.getElementById('mappa-rl-float'));
 
// ── Toggle mappa RL ───────────────────────────────────────────────────────
let mappaRLAperta = false;
 
function toggleMappaRL() {
    if (mappaRLAperta) { chiudiMappaRL(); } else { apriMappaRL(); }
}
 
function apriMappaRL() {
    const win = document.getElementById('mappa-rl-float');
    win.classList.add('visible');
    mappaRLAperta = true;
    const btn = document.getElementById('btn-apri-mappa-rl');
    if (btn) { btn.textContent = '🗺️ Chiudi Mappa'; btn.classList.add('active'); }
    RLModule.onMappaAperta('leaflet-map-rl', 'mappa-rl-coords', 'mappa-rl-ricalcolo');
}
 
function chiudiMappaRL() {
    const win = document.getElementById('mappa-rl-float');
    win.classList.remove('visible');
    mappaRLAperta = false;
    const btn = document.getElementById('btn-apri-mappa-rl');
    if (btn) { btn.textContent = '🌍 Mappa'; btn.classList.remove('active'); }
}
 
function mostraBtnMappa() {
    const btn = document.getElementById('btn-apri-mappa-rl');
    if (btn) btn.style.display = 'inline-block';
}
 
document.addEventListener('DOMContentLoaded', () => {
    RLModule.init(DS, DEFAULT_LAT, DEFAULT_LON, DEFAULT_LUOGO, mostraBtnMappa);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && mappaRLAperta) chiudiMappaRL();
    });
});
</script>
<?php endif; ?>
</body>
</html>