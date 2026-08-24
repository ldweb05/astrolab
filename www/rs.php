<?php
require_once __DIR__ . '/includes/bootstrap.php';
// ===== INIZIO PATCH AUTH MULTI-ASTROLOGO =====
session_start();
require_once 'includes/Auth.php';
 
$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();
 
$isAdmin       = $auth->isAdmin();
$username      = $auth->getCurrentUsername();
$soggettoAttivo = $auth->getSoggettoAttivo();
$soggettoNome  = $auth->getSoggettoNome();
 
$id = intval($_GET['id'] ?? $soggettoAttivo ?? 0);
if ($id > 0) { $auth->setSoggettoAttivo($id); $soggettoNome = $auth->getSoggettoNome(); }
// ===== FINE PATCH AUTH MULTI-ASTROLOGO =====
 
require_once 'includes/SweCalc.php';
require_once 'includes/RuleEngine.php';
 
$soggetto = null;
$defaultLat = 0;
$defaultLon = 0;
$defaultLuogo = '';
 
if ($id) {
    $soggetto = $auth->verificaSoggetto($id);
}
 
$latRS_Url        = isset($_GET['lat_rs'])    ? (float)$_GET['lat_rs']  : null;
$lonRS_Url        = isset($_GET['lon_rs'])    ? (float)$_GET['lon_rs']  : null;
$luogoRS_Url      = $_GET['luogo_rs']         ?? null;
$annoRS_Url       = isset($_GET['anno'])      ? (int)$_GET['anno']      : null;
$condizioneRS_Url = $_GET['condizione'] ?? null;
$condizioni_valide = ['Decima','Lavoro','Amore','Salute','Denaro','Denaro Low','Casa'];
if ($condizioneRS_Url !== null && !in_array($condizioneRS_Url, $condizioni_valide)) {
    $condizioneRS_Url = 'Decima';
}
 
$condizioni = [
    'Decima', 'Lavoro', 'Amore', 'Salute', 'Denaro', 'Denaro Low', 'Casa'
];
 
$annoCorrente = (int)date('Y');
 
require_once __DIR__ . '/includes/NascitaGmtHelper.php';

$jsData = null;
if ($soggetto) {
    $defaultLat  = $latRS_Url  !== null ? $latRS_Url
                 : ($soggetto['residenza_latitudine']  ?: ($soggetto['latitudine'] ?? 0));
    $defaultLon  = $lonRS_Url  !== null ? $lonRS_Url
                 : ($soggetto['residenza_longitudine'] ?: ($soggetto['longitudine'] ?? 0));
    $defaultLuogo = $luogoRS_Url !== null ? $luogoRS_Url
                 : ($soggetto['residenza_luogo']
                    ? $soggetto['residenza_luogo'] . ($soggetto['residenza_nazione'] ? ', '.$soggetto['residenza_nazione'] : '')
                    : ($soggetto['luogo_nascita'] ?? ''));
 
    $date = new DateTime($soggetto['data_nascita']); // Data locale per visualizzazione

    // Calcolo corretto data/ora GMT gestendo il cambio di giorno
    $gmtData = calcolaDataOraGmtCorretta(
        $soggetto['data_nascita'],
        $soggetto['ora_nascita'],
        (float)($soggetto['offset_gmt'] ?? 0)
    );

    $dateGmt = new DateTime($gmtData['data_gmt'] . ' ' . $gmtData['ora_gmt']);
    $oraGmtParts = explode(':', $gmtData['ora_gmt']);

    $jsData = [
        'id'         => $soggetto['id'],
        'nome'       => $soggetto['nome'],
        'giorno'     => (int)$dateGmt->format('d'),
        'mese'       => (int)$dateGmt->format('m'),
        'anno'       => (int)$dateGmt->format('Y'),
        'ora_gmt'    => (int)$oraGmtParts[0] + ((int)($oraGmtParts[1] ?? 0))/60,
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
    <title>Rivoluzione Solare</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/print.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..700;1,400..700&family=Manrope:wght@400..700&family=Noto+Symbols+2&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>
<body>
<?php $paginaAttiva = 'rs'; include 'includes/header_nav.php'; ?>
 
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

    <div id="print-header-rs" class="print-only-header">
    <div class="print-header-left">
        <div class="print-h-nome" id="print-h-nome"></div>
        <div id="print-h-nascita"></div>
        <div id="print-h-ora"></div>
    </div>
    <div class="print-header-right">
        <div class="print-h-gmt" id="print-h-gmt"></div>
        <div id="print-h-ora-locale"></div>
        <div id="print-h-luogo"></div>
    </div>
</div>
 
    <div class="controlli">
        <div class="form-group">
            <label>Anno RS</label>
            <select id="anno-rs">
                <?php for($y = 1960; $y <= $annoCorrente + 7; $y++): ?>
                <option value="<?= $y ?>" <?= ($annoRS_Url ?? $annoCorrente) == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <input type="hidden" id="condizione" value="Decima">
        <div class="form-group luogo-group">
            <label>Luogo RS</label>
            <div class="luogo-rs-wrap">
                <input type="text" id="luogo-rs-input" placeholder="Cerca città RS..."
                       value="<?= htmlspecialchars($defaultLuogo) ?>" class="flex-1">
                <button class="btn-search" onclick="cercaLuogoRS()">🔍 Cerca</button>
            </div>
            <div id="luogo-rs-risultati" class="dropdown-risultati"></div>
        </div>
        <div class="form-group coord-group">
            <label>Lat</label>
            <input type="number" id="rs-lat" step="0.0001" value="<?= htmlspecialchars($defaultLat) ?>">
        </div>
        <div class="form-group coord-group">
            <label>Lon</label>
            <input type="number" id="rs-lon" step="0.0001" value="<?= htmlspecialchars($defaultLon) ?>">
        </div>
        <div class="form-group check-group">
            <label>
                <input type="checkbox" id="mostra-banner-escluse" checked>
                Mostra avviso RS escluse
            </label>
        </div>
       <div class="rs-actions-row">
            <button class="btn-primary" onclick="calcolaRS()">↺ Calcola RS</button>
            <button class="btn-mappa is-hidden" id="btn-apri-mappa" onclick="toggleMappa()">🌍 Mappa</button>
            <button class="btn-mappa is-hidden" id="btn-previsione-annuale" onclick="togglePrevisioneAnnuale()">📖 Relazione Annuale</button>
            <button class="btn-mappa is-hidden" id="btn-correzione-tempo" onclick="toggleCorrezioneTempo()">⏱️ Correzione tempo ed ora</button>
            <button class="btn-stampa-diretta" onclick="prepareStampaRS()">🖨️ Stampa Rivoluzione Solare</button>
        </div>
    </div>
 
    <div class="header-rs is-hidden" id="header-rs">
        <div><span>RS: </span><b id="rs-anno-label"></b></div>
        <div><span>GMT: </span><b id="rs-gmt-label"></b></div>
        <div><span>Luogo: </span><b id="rs-luogo-label"></b></div>
        <div><span>Ora Locale RS: </span><b id="rs-ora-locale-label">--:--:--</b></div>
        <div><span>Fuso Orario: </span><b id="rs-fuso-label">GMT --</b></div>
        <div id="rs-giorno-succ-label" class="rs-next-day-label is-hidden">+1 Giorno</div>
        <div id="rs-link-viaggio" class="rs-travel-links"></div>
    </div>

    <div id="rs-btn-report-wrap" class="rs-report-wrap is-hidden">
        <a id="rs-btn-report" href="#" target="_blank" class="rs-report-link">
            📄 Stampa / PDF Report
        </a>
    </div>
    <div class="card is-hidden" id="card-salva-rs">
        <div class="rs-save-row">
            <div class="form-group rs-save-note-group">
                <label>Note per questa sessione</label>
                <input type="text" id="salva-rs-note" placeholder="Es: opzione preferita, da verificare con il cliente...">
            </div>
            <button class="btn-primary" id="btn-salva-rs" onclick="salvaSessioneRS()">💾 Salva questa RS</button>
        </div>
        <div id="salva-rs-msg" class="rs-save-message"></div>
    </div>
 
    <div class="card is-hidden" id="card-sessioni-rs">
        <h3 class="collapse-toggle" onclick="toggleCollapse('sessioni-rs')">
            📂 Sessioni RS salvate per questo soggetto
            <span class="sensib-chevron" id="collapse-chevron-sessioni-rs">▶</span>
        </h3>
        <div id="collapse-body-sessioni-rs" style="display:none">
            <div id="lista-sessioni-rs"></div>
        </div>
    </div>

    <div id="modifica-note-rs" class="annual-report-modal is-hidden">
        <div class="annual-report-window" style="width:min(620px,calc(100vw - 48px));min-height:280px;resize:both;overflow:auto;">
            <div class="annual-report-header">
                <div class="val-stringa" id="modifica-note-rs-titolo">✏️ Modifica Note Sessione RSM</div>
                <button type="button"
                        onclick="chiudiModificaNoteRS()"
                        title="Chiudi"
                        aria-label="Chiudi"
                        class="annual-report-icon annual-report-close">×</button>
            </div>
            <div class="annual-report-content">
                <input type="hidden" id="modifica-note-rs-id">

                <label for="modifica-note-rs-testo">
                    Note per ricordare le caratteristiche della sessione salvata
                </label>

                <textarea id="modifica-note-rs-testo"
                          maxlength="500"
                          rows="7"
                          style="width:100%;box-sizing:border-box;margin-top:10px;padding:12px;font:inherit;resize:vertical;overflow-y:auto;"
                          oninput="aggiornaContatoreNoteRS()"></textarea>

                <div style="margin-top:8px;text-align:right;">
                    Caratteri disponibili:
                    <strong id="modifica-note-rs-contatore">500</strong>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
                    <button type="button"
                            id="modifica-note-rs-annulla"
                            onclick="chiudiModificaNoteRS()">Annulla</button>
                    <button type="button"
                            id="modifica-note-rs-salva"
                            class="btn-primary"
                            onclick="salvaModificaNoteRS()">
                        💾 Salva Note
                    </button>
                </div>
            </div>
        </div>
    </div>
 
    <div id="correzione-tempo-modal" class="annual-report-modal is-hidden">
        <div class="annual-report-window" style="width:min(480px,calc(100vw - 48px));min-height:180px;">
            <div class="annual-report-header">
                <div class="val-stringa">⏱️ Correzione tempo ed ora</div>
                <button type="button"
                        onclick="toggleCorrezioneTempo()"
                        title="Chiudi"
                        aria-label="Chiudi"
                        class="annual-report-icon annual-report-close">×</button>
            </div>
            <div class="annual-report-content time-controls-modal-content">
                <div class="time-controls time-controls-modal">
                    <div class="time-btn-group">
                        <button class="time-btn" onclick="modificaOraRS(1)">▲</button>
                        <div class="time-label">ORA</div>
                        <div class="time-display" id="ora-corrente-display">--:--</div>
                        <button class="time-btn" onclick="modificaOraRS(-1)">▼</button>
                    </div>
                    <div class="time-btn-group">
                        <button class="time-btn" onclick="modificaMinutiRS(1)">▲</button>
                        <div class="time-label">MIN</div>
                        <div class="time-display" id="min-corrente-display">--</div>
                        <button class="time-btn" onclick="modificaMinutiRS(-1)">▼</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="rs-loading" class="is-hidden"><p>⟳ Calcolo in corso...</p></div>
 
    <div id="rs-alert-stellium"></div>

    <div id="previsione-annuale" class="annual-report-modal is-hidden">
        <div id="previsione-annuale-finestra" class="annual-report-window">
            <div class="annual-report-header">
                <div class="val-stringa">📖 Relazione Annuale</div>
                <div class="annual-report-actions">
                    <button type="button"
                            onclick="stampaPrevisioneAnnuale()"
                            title="Stampa Relazione Annuale"
                            aria-label="Stampa Relazione Annuale"
                            class="annual-report-icon annual-report-print">🖨️</button>
                    <button type="button"
                            onclick="togglePrevisioneAnnuale()"
                            title="Chiudi"
                            aria-label="Chiudi"
                            class="annual-report-icon annual-report-close">×</button>
                </div>
            </div>
            <div id="previsione-annuale-contenuto" class="annual-report-content"></div>
        </div>
    </div>
 
    <div class="valutazione is-hidden" id="valutazione">
        <h3 class="collapse-toggle" onclick="toggleCollapse('bonus-veti')">
            Bonus e Veti
            <span class="sensib-chevron" id="collapse-chevron-bonus-veti">▶</span>
        </h3>
        <div id="collapse-body-bonus-veti" style="display:none">
        <div id="rs-filtro-esclusione" class="val-item val-veto rs-filter-exclusion is-hidden"></div>
        <div class="val-header">
            <div class="stelle-grandi" id="val-stelle"></div>
            <div class="val-stringa"   id="val-stringa"></div>
            <div class="val-condizione" id="val-condizione"></div>
        </div>
        <div class="val-grid">
            <div class="val-section"><h4>✅ Bonus</h4><div id="val-bonus"></div></div>
            <div class="val-section"><h4>⚠️ Penalità / Note</h4><div id="val-penali"></div></div>
        </div>
        <div id="val-veti"></div>
        </div>
    </div>
        <div id="pannello-sensibilita" class="is-hidden">
        <div class="sensib-header" onclick="toggleSensibilita()">
            <span class="sensib-titolo">⏱ Analisi Sensibilità Oraria</span>
            <span class="sensib-sub" id="sensib-badge-header"></span>
            <span class="sensib-chevron" id="sensib-chevron">▶</span>
        </div>
        <div class="sensib-body is-hidden" id="sensib-body">
            <div class="sensib-controlli">
                <div class="form-group sensib-form-group">
                    <label>Passo (minuti)</label>
                    <select id="sensib-step">
                        <option value="5">Ogni 5′</option>
                        <option value="10">Ogni 10′</option>
                        <option value="15" selected>Ogni 15′</option>
                        <option value="30">Ogni 30′</option>
                    </select>
                </div>
                <div class="form-group sensib-form-group">
                    <label>Finestra (± minuti)</label>
                    <select id="sensib-range">
                        <option value="30">± 30′</option>
                        <option value="60" selected>± 60′</option>
                        <option value="90">± 90′</option>
                        <option value="120">± 120′</option>
                    </select>
                </div>
                <button class="btn-secondary btn-calcola-sensib" id="btn-calcola-sensib"
                        onclick="calcolaSensibilita()">
                    ↺ Calcola
                </button>
                <div id="sensib-loading"
                     class="sensib-loading is-hidden">
                    ⟳ Calcolo in corso…
                </div>
            </div>
 
            <div id="sensib-riepilogo" class="sensib-riepilogo is-hidden">
                <div class="sensib-badge-wrap">
                    <span id="sensib-badge" class="sensib-badge"></span>
                    <span id="sensib-perc"  class="sensib-perc"></span>
                </div>
                <p id="sensib-messaggio" class="sensib-messaggio"></p>
                <div id="sensib-alert-veto" class="sensib-alert is-hidden"></div>
            </div>
 
            <div id="sensib-tabella-wrap" class="sensib-table-wrap is-hidden">
                <table class="tabella-sensib" id="sensib-tabella">
                    <thead>
                        <tr>
                            <th title="Variazione applicata all'ora di nascita">Δ Ora</th>
                            <th title="Momento esatto della RS con questa ora di nascita">GMT della RS</th>
                            <th title="Ascendente della Rivoluzione Solare">ASC RS</th>
                            <th title="Casa natale in cui cade l'ASC della RS (I/VI/XII = veto)">Casa Natale ASC</th>
                            <th title="MC della Rivoluzione Solare">MC RS</th>
                            <th title="Valutazione stelline">Stelle</th>
                            <th title="Stringa VAL">VAL</th>
                            <th title="Veti attivi">Veti</th>
                        </tr>
                    </thead>
                    <tbody id="sensib-tbody"></tbody>
                </table>
            </div>
 
            <div class="sensib-legenda">
                <span class="sensib-leg-item sensib-row-base">● riga evidenziata = ora di nascita attuale</span>
                <span class="sensib-leg-item">🟢 = stabile rispetto a δ=0</span>
                <span class="sensib-leg-item">🟡 = stelle cambiano</span>
                <span class="sensib-leg-item">🔴 = casa ASC o veti cambiano</span>
            </div>
        </div>
    </div>
<div class="temi-wrapper is-hidden" id="temi-wrapper">
        <div class="tema-box">
            <div class="tema-box-header">
                <button class="btn-toggle-gradi" id="btn-toggle-cuspidi" onclick="toggleCuspidiCase()">Nascondi Cuspidi</button>
                <h3>Tema Natale</h3>
            </div>
            <svg id="wheel-natale" width="480" height="480" class="zodiac-wheel-responsive"></svg>
            <div class="tema-info-row">
                <p class="tema-info" id="info-natale"></p>
                <button class="btn-toggle-gradi" id="btn-toggle-dati-natale" onclick="toggleDatiTabella('natale')">▼ Mostra Dati</button>
            </div>
            <div id="dati-natale" class="is-hidden">
            <table class="tabella-pianeti" id="tab-natale"></table>
            <div class="aspetti-container">
                <h4 class="rs-table-section-title">📐 Aspetti nella Rivoluzione Solare</h4>
                <table class="tabella-aspetti">
                    <thead><tr><th>Pianeta 1</th><th></th><th>Pianeta 2</th><th>Aspetto</th><th>Orbe</th></tr></thead>
                    <tbody id="aspetti-rs-body"><tr><td colspan="5" class="table-empty-cell">Nessun aspetto rilevante</td></tr>
                </tbody>
            </table>
            </div>
            </div>
        </div>
 
        <div class="tema-box">
            <div class="tema-box-header">
                <h3 id="rs-titolo">Rivoluzione Solare</h3>
                <button class="btn-toggle-gradi" id="btn-toggle-gradi" onclick="toggleGradiPianeti()">Mostra Gradi</button>
            </div>
            <div class="map-loading-overlay" id="rs-mappa-loading">⟳ Ricalcolo RS...</div>
            <svg id="wheel-rs" width="480" height="480" class="zodiac-wheel-responsive"></svg>
            <div class="tema-info-row">
                <p class="tema-info" id="info-rs"></p>
                <button class="btn-toggle-gradi" id="btn-toggle-dati-rs" onclick="toggleDatiTabella('rs')">▼ Mostra Dati</button>
            </div>
            <div id="dati-rs" class="is-hidden">
            <table class="tabella-pianeti" id="tab-rs"></table>
            <div class="cuspidi-container">
                <h4 class="rs-table-section-title">🏠 Cuspidi Case RS</h4>
                <table class="tabella-cuspidi">
                    <thead><tr><th>Casa</th><th>Gradi Cuspide</th></tr></thead>
                    <tbody id="cuspidi-rs-body"><tr><td colspan="2" class="table-empty-cell">—</td></tr>
                </tbody>
            </table>
            </div>
            </div>
        </div>
    </div>
 
    <div id="print-aspetti-rs" class="print-only-aspetti"></div>
 
    <div id="mappa-float" class="map-float-win">
        <div class="map-float-header" id="mappa-drag-handle">
            <h3>🌍 Mappa RS — trascina il marker per ricalcolare</h3>
            <span class="map-float-coords" id="mappa-coords">—</span>
            <button class="btn-close-float" onclick="chiudiMappa()" title="Chiudi">✕</button>
        </div>
        <div class="map-leaflet-inner">
            <div class="map-loading-overlay" id="mappa-ricalcolo">⟳ Ricalcolo...</div>
            <div id="leaflet-map" class="map-fill"></div>
        </div>
        <div class="map-float-footer">
            <span class="info-drag">Trascina il marker · la RS si aggiorna in tempo reale</span>
            <button class="btn-usa-pos" onclick="usaPosizione()">✓ Usa questa posizione</button>
        </div>
    </div>
 
<?php endif; ?>
</main>
 
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/zodiac_wheel.js"></script>
<script src="js/svg_zoom.js"></script> 

<script src="js/app.js"></script>
<script src="js/rs_alert.js"></script>
<script>
<?php if ($soggetto && $jsData): ?>
const DS = <?= json_encode($jsData) ?>;
let temaNataleCache = null;
let oraNascitaCorrente = 0, minNascitaCorrente = 0, offsetRS = 0;
let ultimaDatiRS = null;
let ultimaPrevisioneAnnuale = null;

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function renderPrevisioneAnnuale(previsione) {
    const contenuto = document.getElementById('previsione-annuale-contenuto');
    if (!contenuto || !previsione) return;

    let html = '';

    if (previsione.methodological_note) {
        html += '<p class="report-methodological-note">'
            + escapeHtml(previsione.methodological_note)
            + '</p>';
    }

    if (Array.isArray(previsione.sections)) {
        for (const sezione of previsione.sections) {
            if (sezione.title) {
                html += '<h3>' + escapeHtml(sezione.title) + '</h3>';
            }

            if (sezione.text) {
                html += '<p>' + escapeHtml(sezione.text) + '</p>';
            }
        }
    } else {
        if (previsione.introduzione) {
            html += '<p>' + escapeHtml(previsione.introduzione) + '</p>';
        }

        for (const paragrafo of (previsione.paragrafi || [])) {
            html += '<p>' + escapeHtml(paragrafo.testo || '');

            if (Array.isArray(paragrafo.fonti) && paragrafo.fonti.length) {
                html += ' <strong>('
                    + paragrafo.fonti.map(escapeHtml).join('; ')
                    + ')</strong>';
            }

            html += '</p>';
        }
    }

    contenuto.innerHTML = html || '<p>Nessuna previsione disponibile.</p>';
}

function togglePrevisioneAnnuale() {
    const pannello = document.getElementById('previsione-annuale');
    if (!pannello || !ultimaPrevisioneAnnuale) return;

    const nascosto = pannello.style.display === 'none' || !pannello.style.display;
    pannello.style.display = nascosto ? 'block' : 'none';
}

function toggleCorrezioneTempo() {
    const pannello = document.getElementById('correzione-tempo-modal');
    if (!pannello) return;

    const nascosto = pannello.style.display === 'none' || !pannello.style.display;
    pannello.style.display = nascosto ? 'block' : 'none';

    if (!nascosto) {
        // sto chiudendo: resetta la posizione trascinata, cosi' riapre sempre al centro
        const win = pannello.querySelector('.annual-report-window');
        if (win) {
            win.style.position = '';
            win.style.left = '';
            win.style.top = '';
            win.style.margin = '';
        }
    }
}

// ── Trascinamento pannello "Correzione tempo ed ora" ─────────────────────
// Solo per #correzione-tempo-modal (scoped per id): non tocca dimensioni,
// colori o altre finestre che eventualmente riusassero le stesse classi.
(function initDragCorrezioneTempo() {
    const header = document.querySelector('#correzione-tempo-modal .annual-report-header');
    const win    = document.querySelector('#correzione-tempo-modal .annual-report-window');
    if (!header || !win) return;

    header.style.cursor = 'move';
    header.style.userSelect = 'none';

    let dragging = false, offsetX = 0, offsetY = 0;

    function onPointerDown(e) {
        if (e.target.closest('.annual-report-close')) return; // non trascinare cliccando su "chiudi"
        dragging = true;
        const rect  = win.getBoundingClientRect();
        const point = e.touches ? e.touches[0] : e;
        offsetX = point.clientX - rect.left;
        offsetY = point.clientY - rect.top;
        win.style.position = 'fixed';
        win.style.margin   = '0';
        win.style.left = rect.left + 'px';
        win.style.top  = rect.top  + 'px';
        document.addEventListener('mousemove', onPointerMove);
        document.addEventListener('mouseup', onPointerUp);
        document.addEventListener('touchmove', onPointerMove, { passive: false });
        document.addEventListener('touchend', onPointerUp);
    }

    function onPointerMove(e) {
        if (!dragging) return;
        e.preventDefault();
        const point = e.touches ? e.touches[0] : e;
        let newLeft = point.clientX - offsetX;
        let newTop  = point.clientY - offsetY;
        const maxLeft = window.innerWidth  - win.offsetWidth;
        const maxTop  = window.innerHeight - win.offsetHeight;
        newLeft = Math.max(0, Math.min(newLeft, maxLeft));
        newTop  = Math.max(0, Math.min(newTop, maxTop));
        win.style.left = newLeft + 'px';
        win.style.top  = newTop  + 'px';
    }

    function onPointerUp() {
        dragging = false;
        document.removeEventListener('mousemove', onPointerMove);
        document.removeEventListener('mouseup', onPointerUp);
        document.removeEventListener('touchmove', onPointerMove);
        document.removeEventListener('touchend', onPointerUp);
    }

    header.addEventListener('mousedown', onPointerDown);
    header.addEventListener('touchstart', onPointerDown, { passive: true });
})();

function stampaPrevisioneAnnuale() {
    const contenuto = document.getElementById('previsione-annuale-contenuto');
    if (!contenuto) return;

    const finestra = window.open('', '_blank', 'width=900,height=700');

    if (!finestra) {
        alert('Il browser ha bloccato la finestra di stampa.');
        return;
    }

    finestra.document.write(`<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Relazione Annuale</title>
    <style>
        body {
            font-family: Georgia, "Times New Roman", serif;
            color: #1f2a35;
            background: #ffffff;
            margin: 40px;
            line-height: 1.7;
        }

        h1 {
            color: #244a78;
            border-bottom: 1px solid #d9cdb8;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }

        p {
            margin: 0 0 18px;
        }

        strong {
            color: #244a78;
        }

        h3 {
            color: #244a78;
            font-size: 18px;
            margin: 26px 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #d9cdb8;
            page-break-after: avoid;
        }

        .report-methodological-note {
            background: #f4ead8;
            border-left: 4px solid #244a78;
            padding: 12px 14px;
            font-size: 13px;
            font-style: italic;
        }

        @page {
            margin: 18mm;
        }
    </style>
</head>
<body>
    <h1>Relazione Annuale</h1>
    ${contenuto.innerHTML}
</body>
</html>`);

    finestra.document.close();
    finestra.focus();

    let stampaCompletata = false;

    const chiudiFinestraStampa = () => {
        if (stampaCompletata || finestra.closed) {
            return;
        }

        stampaCompletata = true;
        finestra.close();
    };

    finestra.addEventListener(
        'afterprint',
        chiudiFinestraStampa,
        { once: true }
    );

    setTimeout(() => {
        if (finestra.closed) {
            return;
        }

        finestra.print();

        /*
         * Fallback per browser che non emettono afterprint
         * in modo affidabile sulla finestra secondaria.
         */
        setTimeout(chiudiFinestraStampa, 1500);
    }, 250);
}
 
// ── IPOTESI: toggleGradiPianeti disponibile ─────────────────────
console.log('RS: toggleGradiPianeti disponibile?', typeof toggleGradiPianeti === 'function');
 
// ── Mappa fluttuante ─────────────────────────────────────────────────────
let leafletMap    = null;
let mapMarker     = null;
let mappaAperta   = false;
let ricalcoloTimer= null;
let posIniziale   = null;
let posMappa      = null;
 
// ── Drag della finestra ──────────────────────────────────────────────────
(function initDrag() {
    const win    = document.getElementById('mappa-float');
    const handle = document.getElementById('mappa-drag-handle');
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
        if (leafletMap) leafletMap.invalidateSize();
    });
 
    document.addEventListener('mouseup', () => { dragging = false; });
})();
 
new ResizeObserver(() => {
    if (leafletMap && mappaAperta) leafletMap.invalidateSize();
}).observe(document.getElementById('mappa-float'));
 
// ── Toggle mappa ─────────────────────────────────────────────────────────
function toggleMappa() {
    if (mappaAperta) { chiudiMappa(); return; }
    apriMappa();
}
 
function apriMappa() {
    const lat = parseFloat(document.getElementById('rs-lat').value) || 0;
    const lon = parseFloat(document.getElementById('rs-lon').value) || 0;
    posIniziale = {lat, lon};
    posMappa    = {lat, lon};
 
    const win = document.getElementById('mappa-float');
    win.classList.add('visible');
    mappaAperta = true;
 
    const btn = document.getElementById('btn-apri-mappa');
    if (btn) { btn.textContent = '🗺️ Chiudi Mappa'; btn.classList.add('active'); }
 
    if (!leafletMap) {
        setTimeout(() => {
            leafletMap = L.map('leaflet-map').setView([lat, lon], 5);
            L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18
            }).addTo(leafletMap);
            mapMarker = L.marker([lat, lon], {draggable: true}).addTo(leafletMap);
 
            mapMarker.on('drag', e => {
                const p = e.target.getLatLng();
                posMappa = {lat: p.lat, lon: p.lng};
                document.getElementById('mappa-coords').textContent =
                    p.lat.toFixed(4) + '°  ' + p.lng.toFixed(4) + '°';
            });
 
            mapMarker.on('dragend', e => {
                const p = e.target.getLatLng();
                posMappa = {lat: p.lat, lon: p.lng};
                document.getElementById('mappa-coords').textContent =
                    p.lat.toFixed(4) + '°  ' + p.lng.toFixed(4) + '°';
                clearTimeout(ricalcoloTimer);
                ricalcoloTimer = setTimeout(() => calcolaRS(p.lat, p.lng, true), 280);
            });
 
            leafletMap.invalidateSize();
        }, 80);
 
    } else {
        mapMarker.setLatLng([lat, lon]);
        leafletMap.setView([lat, lon], leafletMap.getZoom());
        setTimeout(() => leafletMap.invalidateSize(), 60);
    }
 
    document.getElementById('mappa-coords').textContent =
        lat.toFixed(4) + '°  ' + lon.toFixed(4) + '°';
}
 
// ── Ricalcolo con nuova ora locale ───────────────────────────────────────
function ricalcolaTuttoConNuovaOra() {
    let anno = document.getElementById('anno-rs').value;
    let cond = document.getElementById('condizione').value;
    let latRS = document.getElementById('rs-lat').value;
    let lonRS = document.getElementById('rs-lon').value;
    let luogoRS = document.getElementById('luogo-rs-input').value || DS.luogo;
 
    let oraGmtMin = oraNascitaCorrente * 60 + minNascitaCorrente - offsetRS * 60;
    let oraGmtFinale = ((oraGmtMin % 1440) + 1440) % 1440;
    let hGmt = Math.floor(oraGmtFinale / 60);
    let mGmt = oraGmtFinale % 60;
    let oraGmtDec = hGmt + mGmt / 60;
 
    const loadingEl = document.getElementById('rs-mappa-loading');
    if (loadingEl) loadingEl.classList.add('visible');
 
    fetch('api/tema_api.php?tipo=natale&g='+DS.giorno+'&m='+DS.mese+'&a='+DS.anno+
          '&ora_gmt='+oraGmtDec+'&lat='+DS.lat+'&lon='+DS.lon)
        .then(r => r.json())
        .then(tema => {
            ZodiacWheel.disegna('wheel-natale', tema, {size:480});
            initSvgZoom('wheel-natale');
            document.getElementById('info-natale').textContent =
                'ASC: '+(tema.case?.ASC?.posizione?.stringa??'?')+
                ' — MC: '+(tema.case?.MC?.posizione?.stringa??'?');
            popolaTabellaPianeti('tab-natale', tema);
        });
 
    fetch('api/rs_api.php?g='+DS.giorno+'&m='+DS.mese+'&a='+DS.anno+
          '&ora_gmt='+oraGmtDec+'&lat='+DS.lat+'&lon='+DS.lon+
          '&anno='+anno+'&lat_rs='+latRS+'&lon_rs='+lonRS+
          '&condizione='+encodeURIComponent(cond)+
          '&luogo_rs='+encodeURIComponent(luogoRS))
        .then(r => r.json())
        .then(data => {
            if (data.errore) {
                throw new Error(data.errore);
            }

            if (loadingEl) loadingEl.classList.remove('visible');
            document.getElementById('rs-gmt-label').textContent = data.rs_gmt;
            aggiornaFusoOrarioLocale(latRS, lonRS, data.rs_gmt);
            const luogoHome = DS.res_luogo
                ? DS.res_luogo + (DS.res_nazione ? ', '+DS.res_nazione : '')
                : DS.luogo + (DS.nazione ? ', '+DS.nazione : '');
            aggiornaLinkViaggio(luogoRS, luogoHome);
            let v = data.valutazione;

            ultimaDatiRS = data;
            ultimaPrevisioneAnnuale = data.relazione_annuale || data.previsione_annuale || null;

            const btnPrevisione = document.getElementById('btn-previsione-annuale');
            const pannelloPrevisione = document.getElementById('previsione-annuale');

            if (ultimaPrevisioneAnnuale) {
                renderPrevisioneAnnuale(ultimaPrevisioneAnnuale);
                if (btnPrevisione) btnPrevisione.style.display = 'inline-block';
            } else {
                if (btnPrevisione) btnPrevisione.style.display = 'none';
                if (pannelloPrevisione) pannelloPrevisione.style.display = 'none';
            }
            document.getElementById('val-stelle').textContent  = v.stelle_str;
            document.getElementById('val-stringa').textContent = v.val;
            aggiornaBannerEsclusione(data);
            
            ZodiacWheel.disegna('wheel-rs', data.tema_rs, {size:480});
            initSvgZoom('wheel-rs');
            document.getElementById('info-rs').textContent =
                'ASC: '+(data.tema_rs.case?.ASC?.posizione?.stringa??'?')+
                ' — MC: '+(data.tema_rs.case?.MC?.posizione?.stringa??'?');
            popolaTabellaPianeti('tab-rs', data.tema_rs);
            popolaTabellaAspetti(data.aspetti || []);
            popolaTabellaCuspidi('cuspidi-rs-body', data.tema_rs);
 
            aggiornaSensibilita(oraGmtDec, latRS, lonRS, anno, cond);
        })
        .catch(() => { if (loadingEl) loadingEl.classList.remove('visible'); });
}
 
function chiudiMappa() {
    const win = document.getElementById('mappa-float');
    win.classList.remove('visible');
    mappaAperta = false;
    clearTimeout(ricalcoloTimer);
 
    const btn = document.getElementById('btn-apri-mappa');
    if (btn) { btn.textContent = '🌍 Mappa'; btn.classList.remove('active'); }
}
 
function usaPosizione() {
    if (!posMappa) return;
    const latPos = posMappa.lat;
    const lonPos = posMappa.lon;
    document.getElementById('rs-lat').value = latPos.toFixed(4);
    document.getElementById('rs-lon').value = lonPos.toFixed(4);
    chiudiMappa();
    fetch('https://nominatim.openstreetmap.org/reverse?lat='+latPos+'&lon='+lonPos+'&format=json&addressdetails=1')
        .then(r => r.json())
        .then(r => {
            const nome = (r && !r.error) ? _estraiNomeLuogoNominatim(r) : '';
            document.getElementById('luogo-rs-input').value = nome || 'NaN';
        })
        .catch(() => {
            document.getElementById('luogo-rs-input').value = 'NaN';
        })
        .finally(() => {
            calcolaRS();
        });
}
 
// ── Ora nascita con controlli ─────────────────────────────────────────────
function initOraRS(oraLocale, offsetGmt) {
    let [hh, mm] = oraLocale.split(':').map(Number);
    oraNascitaCorrente = hh;
    minNascitaCorrente = mm;
    offsetRS = offsetGmt;
    document.getElementById('ora-corrente-display').textContent =
        String(hh).padStart(2,'0') + ':' + String(mm).padStart(2,'0');
    document.getElementById('min-corrente-display').textContent = String(mm).padStart(2,'0');
}
 
function modificaOraRS(delta) {
    oraNascitaCorrente = (oraNascitaCorrente + delta + 24) % 24;
    document.getElementById('ora-corrente-display').textContent =
        String(oraNascitaCorrente).padStart(2,'0') + ':' + String(minNascitaCorrente).padStart(2,'0');
    ricalcolaTuttoConNuovaOra();
}
 
function modificaMinutiRS(delta) {
    minNascitaCorrente += delta;
    if (minNascitaCorrente >= 60) { minNascitaCorrente -= 60; oraNascitaCorrente = (oraNascitaCorrente + 1) % 24; }
    if (minNascitaCorrente < 0)   { minNascitaCorrente += 60; oraNascitaCorrente = (oraNascitaCorrente - 1 + 24) % 24; }
    document.getElementById('ora-corrente-display').textContent =
        String(oraNascitaCorrente).padStart(2,'0') + ':' + String(minNascitaCorrente).padStart(2,'0');
    document.getElementById('min-corrente-display').textContent = String(minNascitaCorrente).padStart(2,'0');
    ricalcolaTuttoConNuovaOra();
}
 
// ── Fuso orario locale (TimeZoneDB) ──────────────────────────────────────
function aggiornaFusoOrarioLocale(lat, lon, gmtString) {
    let parti = gmtString.split(' ');
    let dp = parti[0].split('/');
    let op = parti[1].split(':');
    let dataUtc = new Date(Date.UTC(
        parseInt(dp[2]), parseInt(dp[1]) - 1, parseInt(dp[0]),
        parseInt(op[0]), parseInt(op[1]), parseInt(op[2])
    ));
    let ts  = Math.floor(dataUtc.getTime() / 1000);
    const apiKey = TIMEZONE_API_KEY; // definita in app.js, caricato prima di questo script
    fetch(`https://api.timezonedb.com/v2.1/get-time-zone?key=${apiKey}&format=json&by=position&lat=${lat}&lng=${lon}&time=${ts}`)
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'OK') {
                const elOra  = document.getElementById('rs-ora-locale-label');
                const elFuso = document.getElementById('rs-fuso-label');
                if (elOra)  { elOra.textContent  = 'N/D'; elOra.title  = 'Servizio fuso orario non disponibile'; }
                if (elFuso) { elFuso.textContent = 'N/D'; elFuso.title = 'Servizio fuso orario non disponibile'; }
                return;
            }
            let oraLocaleStr = data.formatted;
            let partiLocale  = oraLocaleStr.split(' ');
            document.getElementById('rs-ora-locale-label').textContent = partiLocale[1];
            let offsetOre = data.gmtOffset / 3600;
            document.getElementById('rs-fuso-label').textContent = 'GMT ' + (offsetOre >= 0 ? '+' : '') + offsetOre;
            let giornoGmt   = parseInt(dp[0]);
            let giornoLocale= parseInt(partiLocale[0].split('-')[2]);
            let el = document.getElementById('rs-giorno-succ-label');
            if (giornoLocale > giornoGmt)       { el.textContent = '+1 Giorno'; el.style.display = 'inline-block'; }
            else if (giornoLocale < giornoGmt)  { el.textContent = '-1 Giorno'; el.style.display = 'inline-block'; }
            else                                { el.style.display = 'none'; }
        })
        .catch(() => {
            const elOra  = document.getElementById('rs-ora-locale-label');
            const elFuso = document.getElementById('rs-fuso-label');
            if (elOra)  { elOra.textContent  = 'N/D'; elOra.title  = 'Errore di rete verso servizio fuso orario'; }
            if (elFuso) { elFuso.textContent = 'N/D'; elFuso.title = 'Errore di rete verso servizio fuso orario'; }
        });
}
 
// ── Link viaggio ─────────────────────────────────────────────────────────
function formatCittaUrl(str) {
    if (!str) return '';
    return str.replace(/\(.*?\)/g,'').replace(/,.*$/,'').trim()
              .replace(/[\s\-]+/g,'-').replace(/^-+|-+$/g,'');
}
 
function aggiornaLinkViaggio(luogoRS, luogoHome) {
    const container = document.getElementById('rs-link-viaggio');
    if (!container) return;
    const dest   = formatCittaUrl(luogoRS);
    const origin = formatCittaUrl(luogoHome);
    if (!dest) { container.innerHTML = ''; return; }
    const urlRome2Rio = origin
        ? `https://www.rome2rio.com/it/map/${origin}/${dest}`
        : `https://www.rome2rio.com/it/map/${dest}`;
    container.innerHTML = `
        <span class="rs-travel-label">Come arrivare:</span>
        <a href="${urlRome2Rio}" target="_blank" rel="noopener"
           class="rs-travel-link">🗺️ Rome2Rio</a>`;
}
 
// ── Carica tema natale iniziale ──────────────────────────────────────────
fetch('api/tema_api.php?tipo=natale&g='+DS.giorno+'&m='+DS.mese+'&a='+DS.anno+
      '&ora_gmt='+DS.ora_gmt+'&lat='+DS.lat+'&lon='+DS.lon)
    .then(r => r.json())
    .then(tema => {
        temaNataleCache = tema;
        ZodiacWheel.disegna('wheel-natale', tema, {size:480});
        initSvgZoom('wheel-natale');
        document.getElementById('info-natale').textContent =
            'ASC: '+(tema.case?.ASC?.posizione?.stringa??'?')+
            ' — MC: '+(tema.case?.MC?.posizione?.stringa??'?');
        popolaTabellaPianeti('tab-natale', tema);
    });
 
initOraRS(DS.ora_loc_str, DS.offset);
 
// ── Calcola RS ───────────────────────────────────────────────────────────
function calcolaRS(latOvr, lonOvr, soloGrafico) {
    let anno  = document.getElementById('anno-rs').value;
    let cond  = document.getElementById('condizione').value;
    let latRS = (latOvr !== undefined) ? latOvr : document.getElementById('rs-lat').value;
    let lonRS = (lonOvr !== undefined) ? lonOvr : document.getElementById('rs-lon').value;
    let luogoRS = document.getElementById('luogo-rs-input').value || DS.luogo;
 
    if (!soloGrafico) {
        document.getElementById('rs-loading').style.display = 'block';
        document.getElementById('temi-wrapper').style.display = 'none';
        document.getElementById('valutazione').style.display  = 'none';
        document.getElementById('header-rs').style.display    = 'none';
    } else {
        const loadingEl = document.getElementById('rs-mappa-loading');
        if (loadingEl) loadingEl.classList.add('visible');
        const mapLoading = document.getElementById('mappa-ricalcolo');
        if (mapLoading) mapLoading.classList.add('visible');
    }
 
    fetch('api/rs_api.php?g='+DS.giorno+'&m='+DS.mese+'&a='+DS.anno+
          '&ora_gmt='+DS.ora_gmt+'&lat='+DS.lat+'&lon='+DS.lon+
          '&anno='+anno+'&lat_rs='+latRS+'&lon_rs='+lonRS+
          '&condizione='+encodeURIComponent(cond)+
          '&luogo_rs='+encodeURIComponent(luogoRS))
        .then(r => r.json())
        .then(data => {
            if (data.errore) {
                throw new Error(data.errore);
            }

            document.getElementById('rs-loading').style.display = 'none';
            const loadingEl = document.getElementById('rs-mappa-loading');
            if (loadingEl) loadingEl.classList.remove('visible');
            const mapLoading = document.getElementById('mappa-ricalcolo');
            if (mapLoading) mapLoading.classList.remove('visible');
 
            document.getElementById('rs-anno-label').textContent  = anno;
            document.getElementById('rs-gmt-label').textContent   = data.rs_gmt;
            
            // FIX 1 APPLICATO: Corretto lo swap tra Long e Lat
            document.getElementById('rs-luogo-label').textContent = luogoRS + ' (Long: ' + parseFloat(lonRS).toFixed(4) + '°, Lat: ' + parseFloat(latRS).toFixed(4) + '°)';
            
            document.getElementById('header-rs').style.display    = 'flex';
 
            aggiornaFusoOrarioLocale(latRS, lonRS, data.rs_gmt);
 
            const lieuHome = DS.res_luogo
                ? DS.res_luogo + (DS.res_nazione ? ', '+DS.res_nazione : '')
                : DS.luogo + (DS.nazione ? ', '+DS.nazione : '');
            aggiornaLinkViaggio(luogoRS, lieuHome);
 
            let v = data.valutazione;

            ultimaDatiRS = data;
            ultimaPrevisioneAnnuale = data.relazione_annuale || data.previsione_annuale || null;

            const btnPrevisione = document.getElementById('btn-previsione-annuale');
            const pannelloPrevisione = document.getElementById('previsione-annuale');

            if (ultimaPrevisioneAnnuale) {
                renderPrevisioneAnnuale(ultimaPrevisioneAnnuale);
                if (btnPrevisione) btnPrevisione.style.display = 'inline-block';
            } else {
                if (btnPrevisione) btnPrevisione.style.display = 'none';
                if (pannelloPrevisione) pannelloPrevisione.style.display = 'none';
            }
            document.getElementById('val-stelle').textContent    = v.stelle_str;
            document.getElementById('val-stringa').textContent   = v.val;
            document.getElementById('val-condizione').textContent= 'Condizione: '+v.condizione;
 
            document.getElementById('val-veti').innerHTML = v.veti.length
                ? v.veti.map(t => '<div class="val-item val-veto">⛔ '+t+'</div>').join('') : '';
            document.getElementById('val-bonus').innerHTML = v.bonus.length
                ? v.bonus.map(b => '<div class="val-item val-bonus"><b>'+b.codice+'</b> '+b.nota+'</div>').join('')
                : '<div class="val-empty-message">Nessun bonus significativo</div>';
            const penHtml = [
                ...v.penalita.map(p => '<div class="val-item val-penali"><b>'+p.codice+'</b> '+p.nota+'</div>'),
                ...v.note.map(n => '<div class="val-item val-note"><b>'+n.codice+'</b> '+n.nota+'</div>')
            ].join('');
            document.getElementById('val-penali').innerHTML = penHtml
                || '<div class="val-empty-message">Nessuna penalità</div>';
 
            aggiornaBannerEsclusione(data);
            ultimaDatiRS = data;

            if (typeof RSAlert !== 'undefined') {
                RSAlert.aggiorna({
                    g:       DS.giorno,
                    m:       DS.mese,
                    a:       DS.anno,
                    ora_gmt: DS.ora_gmt,
                    lat:     DS.lat,
                    lon:     DS.lon,
                    anno:    anno,
                    lat_rs:  latRS,
                    lon_rs:  lonRS,
                });
            }
 
            document.getElementById('valutazione').style.display = 'block';
            document.getElementById('rs-titolo').textContent = 'RS '+anno+' — '+luogoRS;
 
            ZodiacWheel.disegna('wheel-rs', data.tema_rs, {size:480});
            initSvgZoom('wheel-rs');
            document.getElementById('info-rs').textContent =
                'ASC: '+(data.tema_rs.case?.ASC?.posizione?.stringa??'?')+
                ' — MC: '+(data.tema_rs.case?.MC?.posizione?.stringa??'?');
            popolaTabellaPianeti('tab-rs', data.tema_rs);
            popolaTabellaAspetti(data.aspetti || []);
            popolaTabellaCuspidi('cuspidi-rs-body', data.tema_rs);
 
            ultimaRSCalcolata = {
                anno:       parseInt(anno),
                condizione: cond,
                lat:        parseFloat(latRS),
                lon:        parseFloat(lonRS),
                luogo:      luogoRS,
                rs_gmt:     data.rs_gmt,
                stelline:   v.stelline,
                val:        v.val,
            };
            document.getElementById('card-salva-rs').style.display = 'block';
 
            document.getElementById('temi-wrapper').style.display = 'flex';
            document.getElementById('btn-apri-mappa').style.display = 'inline-block';
            document.getElementById('btn-correzione-tempo').style.display = 'inline-block';
            if (!soloGrafico) document.getElementById('temi-wrapper').scrollIntoView({behavior:'smooth'});
 
            if (!soloGrafico) {
                aggiornaSensibilita(DS.ora_gmt, latRS, lonRS, anno, cond);
            }
        })
        .catch(e => alert('Errore calcolo RS: '+e.message));
}
 
// ── Tabelle pianeti / aspetti ─────────────────────────────────────────────
function popolaTabellaPianeti(tabId, tema) {
    const nomi = {0:'☉ Sole',1:'☽ Luna',2:'☿ Mercurio',3:'♀ Venere',4:'♂ Marte',
                  5:'♃ Giove',6:'♄ Saturno',7:'♅ Urano',8:'♆ Nettuno',9:'♇ Plutone',
                  11:'☊ Nodo N.'};
    let html = '<table class="tabella-pianeti"><thead><tr><th>Pianeta</th><th>Posizione</th><th>Casa</th><th></th></tr></thead><tbody>';
    Object.values(tema.pianeti).forEach(p => {
        html += '<tr><td>'+(nomi[p.id]??p.nome)+'</td><td>'+p.posizione.stringa+'</td>'+
                '<td>'+p.casa+'</td><td>'+(p.retrogrado?'<span class="retro">R</span>':'')+'</td></tr>';
    });
    html += '</tbody></table>';
    document.getElementById(tabId).innerHTML = html;
}
 
function popolaTabellaAspetti(aspetti) {
    const tbody = document.getElementById('aspetti-rs-body');
    if (!tbody) return;
    if (!aspetti || aspetti.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="table-empty-cell">Nessun aspetto rilevante</td></tr>';
        return;
    }
    const simboli = {0:'☉',1:'☽',2:'☿',3:'♀',4:'♂',5:'♃',6:'♄',7:'♅',8:'♆',9:'♇'};
    const nomi    = {0:'Sole',1:'Luna',2:'Mercurio',3:'Venere',4:'Marte',5:'Giove',
                     6:'Saturno',7:'Urano',8:'Nettuno',9:'Plutone'};
    const tipoMap = {
        'Trigono':{sim:'△',cls:'aspetto-trigono'},'trine':{sim:'△',cls:'aspetto-trigono'},
        'Quadrato':{sim:'□',cls:'aspetto-quadrato'},'square':{sim:'□',cls:'aspetto-quadrato'},
        'Opposizione':{sim:'☍',cls:'aspetto-opposizione'},'opposition':{sim:'☍',cls:'aspetto-opposizione'},
        'Sestile':{sim:'⚹',cls:'aspetto-sestile'},'sextile':{sim:'⚹',cls:'aspetto-sestile'},
        'Congiunzione':{sim:'☌',cls:'aspetto-altro'},'conjunction':{sim:'☌',cls:'aspetto-altro'}
    };
    tbody.innerHTML = aspetti.map(a => {
        const ti = tipoMap[a.aspetto || a.tipo] || {sim:'•',cls:'aspetto-altro'};
        return `<tr>
            <td>${simboli[a.pianeta_a]??''} ${nomi[a.pianeta_a]??a.nome_a??'?'}</td>
            <td class="aspect-arrow">→</td>
            <td>${simboli[a.pianeta_b]??''} ${nomi[a.pianeta_b]??a.nome_b??'?'}</td>
            <td class="${ti.cls}">${ti.sim} ${a.aspetto||a.tipo}</td>
            <td>${a.scarto?.toFixed(1)??'?'}°</td>
        </tr>`;
    }).join('');
}

// ── Tabella cuspidi case ───────────────────────────────────────────────────
function popolaTabellaCuspidi(tbodyId, tema) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody || !tema?.case) return;

    const CASE_LABEL = {
        1:'I o ASC', 2:'II', 3:'III', 4:'IV o FC', 5:'V', 6:'VI',
        7:'VII o DSC', 8:'VIII', 9:'IX', 10:'X o MC', 11:'XI', 12:'XII'
    };
    const ANGOLARI = new Set([1, 4, 7, 10]);

    let html = '';
    for (let c = 1; c <= 12; c++) {
        const casa = tema.case[c];
        if (!casa) continue;
        const label   = CASE_LABEL[c] || String(c);
        const stringa = casa.posizione?.stringa ?? '—';
        const angularClass = ANGOLARI.has(c) ? ' cuspide-angolare' : '';
        html += `<tr>
            <td class="cuspide-label${angularClass}">${label}</td>
            <td class="${angularClass.trim()}">${stringa}</td>
        </tr>`;
    }
    tbody.innerHTML = html || '<tr><td colspan="2" class="table-empty-cell">—</td></tr>';
}
 
function aggiornaBannerEsclusione(data) {
    const el = document.getElementById('rs-filtro-esclusione');
    if (!el) return;
    const motifs = data.escluso_filtro || [];
    const showBanner = document.getElementById('mostra-banner-escluse')?.checked !== false;
    if (motifs.length === 0 || !showBanner) {
        el.style.display = 'none';
        el.innerHTML = '';
        return;
    }
    el.innerHTML = '⚠️ <b>Questa RS non viene elencata nei risultati di ricerca</b> ' +
        'perché presenta le seguenti configurazioni escluse:<br>' +
        motifs.map(m => '• ' + m).join('<br>');
    el.style.display = 'block';
}
 
// ── Analisi Sensibilità Oraria ───────────────────────────────────────────
let _sensibParams = null; 
 
function aggiornaSensibilita(oraGmtUsata, latRS, lonRS, anno, cond) {
    _sensibParams = { oraGmtUsata, latRS, lonRS, anno, cond };
    document.getElementById('pannello-sensibilita').style.display = 'block';
    document.getElementById('sensib-badge-header').textContent =
        '— clicca ▶ per analizzare la robustezza oraria';
    _chiudiSensibBody();
}
 
function toggleSensibilita() {
    const body    = document.getElementById('sensib-body');
    const chevron = document.getElementById('sensib-chevron');
    const aperto  = body.style.display !== 'none';
    if (aperto) {
        _chiudiSensibBody();
    } else {
        body.style.display = 'block';
        chevron.classList.add('aperto');
    }
}
 
function _chiudiSensibBody() {
    document.getElementById('sensib-body').style.display = 'none';
    document.getElementById('sensib-chevron').classList.remove('aperto');
}
 
function calcolaSensibilita() {
    if (!_sensibParams) return;
 
    const step  = document.getElementById('sensib-step').value;
    const range = document.getElementById('sensib-range').value;
 
    const loadingEl  = document.getElementById('sensib-loading');
    const btnCalc    = document.getElementById('btn-calcola-sensib');
    const riepilogo  = document.getElementById('sensib-riepilogo');
    const tabellaWrap= document.getElementById('sensib-tabella-wrap');
 
    loadingEl.style.display  = 'inline';
    btnCalc.disabled         = true;
    riepilogo.style.display  = 'none';
    tabellaWrap.style.display= 'none';
 
    const p = _sensibParams;
    const url = 'api/sensibilita_api.php?' + new URLSearchParams({
        g:           DS.giorno,
        m:           DS.mese,
        a:           DS.anno,
        ora_gmt:     p.oraGmtUsata,
        lat:         DS.lat,
        lon:         DS.lon,
        anno:        p.anno,
        lat_rs:      p.latRS,
        lon_rs:      p.lonRS,
        condizione:  p.cond,
        delta_step:  step,
        delta_range: range,
    });
 
    fetch(url)
        .then(r => r.json())
        .then(data => {
            loadingEl.style.display = 'none';
            btnCalc.disabled        = false;
            if (!data.ok) {
                _mostraErroreSensib(data.errore || 'Errore nel calcolo sensibilità.');
                return;
            }
            _renderSensibilita(data);
        })
        .catch(e => {
            loadingEl.style.display = 'none';
            btnCalc.disabled        = false;
            _mostraErroreSensib('Errore di rete: ' + e.message);
        });
}
 
function _renderSensibilita(data) {
    const badgeEl  = document.getElementById('sensib-badge');
    const percEl   = document.getElementById('sensib-perc');
    const msgEl    = document.getElementById('sensib-messaggio');
    const alertEl  = document.getElementById('sensib-alert-veto');
    const headerSub= document.getElementById('sensib-badge-header');
    const riepilogo= document.getElementById('sensib-riepilogo');
 
    const labelRob = {
        alta:    '✅ Robusta',
        media:   '⚠️ Mediamente robusta',
        bassa:   '⚠️ Instabile',
        critica: '🔴 Critica',
    };
 
    badgeEl.className   = 'sensib-badge ' + data.robustezza;
    badgeEl.textContent = labelRob[data.robustezza] || data.robustezza;
    percEl.textContent  = data.perc_stabile + '% punti stabili';
    msgEl.textContent   = data.messaggio;
 
    headerSub.textContent = labelRob[data.robustezza] + ' — ' + data.perc_stabile + '% stabile';
 
    const alertParts = [];
    if (data.veto_compare)   alertParts.push('⛔ Con alcune variazioni orarie compaiono VETI che a δ=0 non esistono.');
    if (data.veto_scompare)  alertParts.push('⚠️ Con alcune variazioni orarie i veti presenti a δ=0 scompaiono.');
    if (alertParts.length > 0) {
        alertEl.innerHTML = alertParts.join('<br>');
        alertEl.style.display = 'block';
    } else {
        alertEl.style.display = 'none';
    }
 
    riepilogo.style.display = 'block';
 
    const tbody   = document.getElementById('sensib-tbody');
    let html = '';
 
    const base = data.punti.find(p => p.is_punto_base);
 
    data.punti.forEach(p => {
        let rigaCls = '';
        if (p.is_punto_base) {
            rigaCls = 'sensib-row-base';
        } else if (!p.is_valida || (base && p.casa_natale_asc !== base.casa_natale_asc)) {
            rigaCls = 'sensib-row-critica';
        } else if (base && p.stelline !== base.stelline) {
            rigaCls = 'sensib-row-stelle-cambiano';
        } else {
            rigaCls = 'sensib-row-ok';
        }
 
        const dSign   = p.delta_min < 0 ? 'neg' : p.delta_min === 0 ? 'zero' : 'pos';
        const dLabel  = p.is_punto_base
            ? `<span class="delta-label zero">0′ ★</span>`
            : `<span class="delta-label ${dSign}">${p.delta_label}</span>`;
 
        const casaNum  = p.casa_natale_asc;
        const casaVeto = [1, 6, 12].includes(casaNum);
        const casaWarn = base && casaNum !== base.casa_natale_asc;
        const casaCls  = casaVeto ? 'veto' : casaWarn ? 'warn' : 'ok';
        const casaBadge= `<span class="casa-badge ${casaCls}">${casaNum}ª</span>`;
 
        let icona = '';
        if (!p.is_punto_base && base) {
            if (!p.is_valida && base.is_valida)        icona = '🔴';
            else if (p.is_valida && !base.is_valida)   icona = '🟢';
            else if (p.casa_natale_asc !== base.casa_natale_asc) icona = '🔴';
            else if (p.stelline !== base.stelline)     icona = '🟡';
            else                                       icona = '🟢';
        }
 
        const stelle = p.stelline > 0
            ? `<span class="stelle-sensib">${'★'.repeat(p.stelline)}${'☆'.repeat(5-p.stelline)}</span>`
            : `<span class="sensib-zero-stars">0 ⛔</span>`;
 
        const vetiHtml = p.veti && p.veti.length > 0
            ? `<span class="veto-count" title="${p.veti.join('\n')}">${p.veti.length} veto${p.veti.length>1?'i':''}</span>`
            : `<span class="sensib-no-veto">nessuno</span>`;
 
        const valStr = p.val ? p.val.replace(/^\*+/, '') : '—';
 
        html += `<tr class="${rigaCls}">
            <td>${dLabel} ${icona}</td>
            <td class="sensib-mono-small">${p.rs_gmt}</td>
            <td class="sensib-text-small">${p.asc_rs_str}</td>
            <td class="table-center">${casaBadge}</td>
            <td class="sensib-muted-small">${p.mc_rs_str}</td>
            <td>${stelle}</td>
            <td class="sensib-val-cell">${valStr}</td>
            <td>${vetiHtml}</td>
        </tr>`;
    });
 
    tbody.innerHTML = html;
    document.getElementById('sensib-tabella-wrap').style.display = 'block';
}
 
function _mostraErroreSensib(msg) {
    const riepilogo = document.getElementById('sensib-riepilogo');
    riepilogo.innerHTML = `<p class="sensib-error-message">❌ ${msg}</p>`;
    riepilogo.style.display = 'block';
}
 
// ── Geocoding luogo RS ────────────────────────────────────────────────────
// Estrae un nome sintetico affidabile dai campi strutturati di Nominatim
// (address.city/town/village/... + state), indipendente dal formato con cui
// il singolo paese scrive gli indirizzi (a differenza dello split per virgola
// sul display_name, che porta il numero civico in USA e altri formati altrove).
function _estraiNomeLuogoNominatim(r) {
    const a = (r && r.address) || {};
    const loc = a.city || a.town || a.village || a.municipality || a.hamlet || a.county;
    const stato = a.state || a.region;
    if (loc && stato && loc !== stato) return loc + ', ' + stato;
    if (loc) return loc;
    if (stato) return stato;
    if (a.country) return a.country;
    return (r.display_name || '').split(',')[0].trim();
}
function cercaLuogoRS() {
    const q = document.getElementById('luogo-rs-input').value.trim();
    if (q.length < 3) return;
    fetch('https://nominatim.openstreetmap.org/search?q='+encodeURIComponent(q)+'&format=json&limit=6&addressdetails=1')
        .then(r => r.json())
        .then(ris => {
            const div = document.getElementById('luogo-rs-risultati');
            div.innerHTML = ris.map(r => {
                const nomeBreve = _estraiNomeLuogoNominatim(r).replace(/'/g,"\\'");
                return `<div class="dropdown-item" onclick="selezionaLuogoRS(${r.lat},${r.lon},'${r.display_name.replace(/'/g,"\\'")}','${nomeBreve}')">
                    ${r.display_name}
                </div>`;
            }).join('');
            div.classList.add('visible');
        });
}
 
function selezionaLuogoRS(lat, lon, nome, nomeBreve) {
    document.getElementById('rs-lat').value = parseFloat(lat).toFixed(4);
    document.getElementById('rs-lon').value = parseFloat(lon).toFixed(4);
    document.getElementById('luogo-rs-input').value = nomeBreve || nome.split(',')[0].trim();
    document.getElementById('luogo-rs-risultati').classList.remove('visible');
    if (leafletMap && mapMarker) {
        mapMarker.setLatLng([parseFloat(lat), parseFloat(lon)]);
        leafletMap.setView([parseFloat(lat), parseFloat(lon)], leafletMap.getZoom());
    }
}
 
// ── Init DOM ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', e => {
        if (!e.target.closest('.luogo-rs-wrap'))
            document.getElementById('luogo-rs-risultati')?.classList.remove('visible');
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && mappaAperta) chiudiMappa();
    });
    
    const toggleCheckbox = document.getElementById('mostra-banner-escluse');
    if (toggleCheckbox) {
        toggleCheckbox.addEventListener('change', () => {
            if (window.ultimaDatiRS) aggiornaBannerEsclusione(window.ultimaDatiRS);
        });
    }
    
    calcolaRS();
});
// ══════════════════════════════════════════════════════════════════════
//  SALVATAGGIO SESSIONI RS 
// ══════════════════════════════════════════════════════════════════════
 
let ultimaRSCalcolata = null;
let sessioniRSSalvate = new Map();
 
function caricaSessioniRS() {
    fetch('api/sessioni_api.php?action=lista_rs&soggetto_id=' + DS.id)
        .then(r => r.json())
        .then(rows => {
            sessioniRSSalvate = new Map(
                Array.isArray(rows) ? rows.map(s => [Number(s.id), s]) : []
            );
            const card = document.getElementById('card-sessioni-rs');
            const div  = document.getElementById('lista-sessioni-rs');
            if (!Array.isArray(rows) || rows.length === 0) {
                card.style.display = 'none';
                return;
            }
            card.style.display = 'block';
 
            let html = '<table class="tabella-soggetti"><thead><tr>' +
                '<th>Anno</th><th>Luogo</th><th>Condizione</th><th>Stelle</th>' +
                '<th>VAL</th><th>Note</th><th>Salvata il</th><th>Azioni</th>' +
                '</tr></thead><tbody>';
 
            rows.forEach(s => {
                const stelle = s.stelline != null
                    ? '★'.repeat(Math.round(s.stelline)) + '☆'.repeat(5 - Math.round(s.stelline))
                    : '—';
                const dataSalv = new Date(s.creato_il).toLocaleString('it-IT',
                    {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
                const url = 'rs.php?id=' + DS.id +
                    '&anno=' + s.anno +
                    '&lat_rs=' + s.lat + '&lon_rs=' + s.lon +
                    '&luogo_rs=' + encodeURIComponent(s.luogo || '') +
                    '&condizione=' + encodeURIComponent(s.condizione);
 
                html += `<tr>
                    <td>${s.anno}</td>
                    <td>${s.luogo || '—'}</td>
                    <td>${s.condizione}</td>
                    <td class="stelle">${stelle}</td>
                    <td><span class="val-badge">${s.val || '—'}</span></td>
                    <td class="session-note-cell">
                        <div style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                            ${s.note || ''}
                        </div>
                        ${s.note
                            ? `<button type="button"
                                       class="btn-icon"
                                       style="margin-top:6px;font-size:12px;"
                                       onclick="leggiNoteSessioneRS(${s.id})">Leggi tutto</button>`
                            : ''}
                    </td>
                    <td class="session-date-cell">${dataSalv}</td>
                    <td><div class="azioni">
                        <a href="${url}" class="btn-icon" title="Richiama questa sessione">↺</a>
                        <button class="btn-icon" title="Modifica" onclick="modificaSessioneRS(${s.id})">✏️</button>
                        <button class="btn-icon" title="Cancella" onclick="eliminaSessioneRS(${s.id})">🗑️</button>
                    </div></td>
                </tr>`;
            });
 
            html += '</tbody></table>';
            div.innerHTML = html;
        })
        .catch(() => {});
}
 
function salvaSessioneRS() {
    if (!ultimaRSCalcolata) {
        alert('Calcola prima una RS prima di salvarla.');
        return;
    }
 
    const note = document.getElementById('salva-rs-note').value.trim();
    const btn  = document.getElementById('btn-salva-rs');
    const msg  = document.getElementById('salva-rs-msg');
 
    btn.disabled = true;
    btn.textContent = '⟳ Salvataggio...';
 
    fetch('api/sessioni_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action:     'salva_rs',
            soggetto_id: DS.id,
            anno:        ultimaRSCalcolata.anno,
            condizione:  ultimaRSCalcolata.condizione,
            lat:         ultimaRSCalcolata.lat,
            lon:         ultimaRSCalcolata.lon,
            luogo:       ultimaRSCalcolata.luogo,
            rs_gmt:      ultimaRSCalcolata.rs_gmt,
            stelline:    ultimaRSCalcolata.stelline,
            val:         ultimaRSCalcolata.val,
            note:        note,
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = '💾 Salva questa RS';
        if (data.ok) {
            msg.innerHTML = '<span class="message-success-inline">✅ Sessione salvata.</span>';
            document.getElementById('salva-rs-note').value = '';
            caricaSessioniRS();
            setTimeout(() => { msg.innerHTML = ''; }, 3000);
        } else {
            msg.innerHTML = '<span class="message-error-inline">⚠️ ' + (data.errore || 'Errore salvataggio') + '</span>';
        }
    })
    .catch(e => {
        btn.disabled = false;
        btn.textContent = '💾 Salva questa RS';
        msg.innerHTML = '<span class="message-error-inline">⚠️ Errore rete: ' + e.message + '</span>';
    });
}
 

function leggiNoteSessioneRS(id) {
    const sessione = sessioniRSSalvate.get(Number(id));
    if (!sessione) {
        alert('Sessione non trovata.');
        return;
    }

    const campo = document.getElementById('modifica-note-rs-testo');

    document.getElementById('modifica-note-rs-id').value = '';
    document.getElementById('modifica-note-rs-titolo').textContent = '📖 Note Sessione RSM';
    document.getElementById('modifica-note-rs-annulla').textContent = 'Chiudi';
    document.getElementById('modifica-note-rs-salva').style.display = 'none';

    campo.value = sessione.note || '';
    campo.readOnly = true;

    aggiornaContatoreNoteRS();
    document.getElementById('modifica-note-rs').classList.remove('is-hidden');
}

function modificaSessioneRS(id) {
    const sessione = sessioniRSSalvate.get(Number(id));
    if (!sessione) {
        alert('Sessione non trovata.');
        return;
    }

    const campo = document.getElementById('modifica-note-rs-testo');

    document.getElementById('modifica-note-rs-id').value = Number(id);
    document.getElementById('modifica-note-rs-titolo').textContent = '✏️ Modifica Note Sessione RSM';
    document.getElementById('modifica-note-rs-annulla').textContent = 'Annulla';
    document.getElementById('modifica-note-rs-salva').style.display = '';
    campo.readOnly = false;
    campo.value = sessione.note || '';
    aggiornaContatoreNoteRS();
    document.getElementById('modifica-note-rs').classList.remove('is-hidden');
    document.getElementById('modifica-note-rs-testo').focus();
}

function aggiornaContatoreNoteRS() {
    const campo = document.getElementById('modifica-note-rs-testo');
    const contatore = document.getElementById('modifica-note-rs-contatore');
    contatore.textContent = Math.max(0, 500 - campo.value.length);
}

function chiudiModificaNoteRS() {
    document.getElementById('modifica-note-rs').classList.add('is-hidden');
    document.getElementById('modifica-note-rs-id').value = '';
    const campo = document.getElementById('modifica-note-rs-testo');
    campo.value = '';
    campo.readOnly = false;
    document.getElementById('modifica-note-rs-titolo').textContent = '✏️ Modifica Note Sessione RSM';
    document.getElementById('modifica-note-rs-annulla').textContent = 'Annulla';
    document.getElementById('modifica-note-rs-salva').style.display = '';
    document.getElementById('modifica-note-rs-contatore').textContent = '500';
}

function salvaModificaNoteRS() {
    const id = Number(document.getElementById('modifica-note-rs-id').value);
    const note = document.getElementById('modifica-note-rs-testo').value;

    fetch('api/sessioni_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'modifica_rs',
            id: id,
            note: note
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            chiudiModificaNoteRS();
            caricaSessioniRS();
        } else {
            alert(data.errore || 'Errore durante la modifica delle Note.');
        }
    })
    .catch(e => {
        alert('Errore rete: ' + e.message);
    });
}


function eliminaSessioneRS(id) {
    if (!confirm('Eliminare questa sessione salvata?')) return;
    fetch('api/sessioni_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'elimina_rs', id})
    })
    .then(r => r.json())
    .then(data => { if (data.ok) caricaSessioniRS(); });
}
 
caricaSessioniRS();

// FIX 2 APPLICATO: Sostituito prepareStampaRS per ripulire le coordinate già incluse prima di ristamparle
function prepareStampaRS() {
    document.getElementById('print-h-nome').textContent    = DS.nome;
    document.getElementById('print-h-nascita').textContent = 'Nato il ' + DS.data_str;
    document.getElementById('print-h-ora').textContent     = 'Ore ' + DS.ora_loc_str + ' (loc.) — ' + DS.ora_gmt_str + ' GMT';

    const anno   = document.getElementById('rs-anno-label')?.textContent        || '';
    const gmt    = document.getElementById('rs-gmt-label')?.textContent         || '';
    const oraLoc = document.getElementById('rs-ora-locale-label')?.textContent  || '';
    const fuso   = document.getElementById('rs-fuso-label')?.textContent        || '';

    // Rimuove le coordinate già embeddate in rs-luogo-label per evitare la doppia stampa
    let luogo = document.getElementById('rs-luogo-label')?.textContent || '';
    luogo = luogo.split(' (')[0].trim();

    document.getElementById('print-h-gmt').textContent        = 'RS ' + anno + ' — ' + gmt;
    document.getElementById('print-h-ora-locale').textContent = 'Ora Locale ' + oraLoc + ' — Fuso ' + fuso;
    
    const latRS = parseFloat(document.getElementById('rs-lat')?.value) || 0;
    const lonRS = parseFloat(document.getElementById('rs-lon')?.value) || 0;
    document.getElementById('print-h-luogo').textContent =
        'Luogo: ' + luogo + '   Long ' + lonRS.toFixed(4) + '°   Lat ' + latRS.toFixed(4) + '°';

    buildAspettiOrizzontale('aspetti-rs-body', 'print-aspetti-rs');
    stampaPagina('print-rs');
}
<?php endif; ?>
// ── Aggiorna link Report dopo ogni calcolo RS ─────────────────────────
(function() {
    const _origCalcolaRS = window.calcolaRS;
    window.calcolaRS = function(latOvr, lonOvr, soloGrafico) {
        _origCalcolaRS.apply(this, arguments);
        setTimeout(_aggiornaLinkReportRS, 600);
    };

    function _aggiornaLinkReportRS() {
        const wrap  = document.getElementById('rs-btn-report-wrap');
        const link  = document.getElementById('rs-btn-report');
        if (!wrap || !link || !window.DS) return;

        const anno   = document.getElementById('anno-rs')?.value        || new Date().getFullYear();
        const latRS  = document.getElementById('rs-lat')?.value         || DS.lat;
        const lonRS  = document.getElementById('rs-lon')?.value         || DS.lon;
        const luogo  = document.getElementById('luogo-rs-input')?.value || '';
        const cond   = document.getElementById('condizione')?.value     || 'Decima';

        const params = new URLSearchParams({
            id:         DS.id,
            anno:       anno,
            lat_rs:     latRS,
            lon_rs:     lonRS,
            luogo_rs:   luogo,
            condizione: cond,
        });
        link.href = 'stampa.php?' + params.toString();
        wrap.style.display = 'block';
    }
})();
</script>
</body>
</html>