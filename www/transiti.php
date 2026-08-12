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
    <title>Transiti Planetari</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/print.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Symbols+2&display=swap" rel="stylesheet">
</head>
<body>
<?php $paginaAttiva = 'transiti'; include 'includes/header_nav.php'; ?>
 
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
        <div class="form-group coord-group" style="min-width:70px">
            <label>Giorno</label>
            <input type="number" id="transiti-giorno" min="1" max="31" value="<?= date('j') ?>" style="width:60px">
        </div>
        <div class="form-group coord-group" style="min-width:70px">
            <label>Mese</label>
            <input type="number" id="transiti-mese" min="1" max="12" value="<?= date('n') ?>" style="width:60px">
        </div>
        <div class="form-group coord-group" style="min-width:90px">
            <label>Anno</label>
            <input type="number" id="transiti-anno" value="<?= date('Y') ?>" style="width:80px">
        </div>
        <div class="form-group luogo-group">
            <label>Luogo Transito</label>
            <div class="luogo-rs-wrap">
                <input type="text" id="luogo-rs-input" placeholder="Cerca città..."
                       value="<?= htmlspecialchars($defaultLuogo) ?>" class="flex-1">
                <button class="btn-search" onclick="cercaLuogoRS()">🔍 Cerca</button>
            </div>
            <div id="luogo-rs-risultati" class="dropdown-risultati"></div>
        </div>
        <div class="form-group coord-group" style="min-width:70px">
            <label>Ore (locali)</label>
            <input type="number" id="transiti-ore" min="0" max="23" value="0" style="width:60px">
        </div>
        <div class="form-group coord-group" style="min-width:70px">
            <label>Minuti</label>
            <input type="number" id="transiti-minuti" min="0" max="59" value="0" style="width:60px">
        </div>
        <div class="form-group coord-group">
            <label>Lat</label>
            <input type="number" id="rs-lat" step="0.0001" value="<?= htmlspecialchars($defaultLat) ?>">
        </div>
        <div class="form-group coord-group">
            <label>Lon</label>
            <input type="number" id="rs-lon" step="0.0001" value="<?= htmlspecialchars($defaultLon) ?>">
        </div>
       <div class="rs-actions-row">
            <button class="btn-primary" onclick="calcolaTransiti()">↺ Calcola Transiti</button>
            <button class="btn-primary" onclick="impostaOraAttuale()">🕐 Oggi/Adesso</button>
        </div>
    </div>
 
    <div class="header-rs is-hidden" id="header-rs">
        <div><span>Transito: </span><b id="rs-anno-label"></b></div>
        <div><span>GMT: </span><b id="rs-gmt-label"></b></div>
        <div><span>Luogo: </span><b id="rs-luogo-label"></b></div>
        <div><span>Ora Locale: </span><b id="rs-ora-locale-label">--:--:--</b></div>
        <div><span>Fuso Orario: </span><b id="rs-fuso-label">GMT --</b></div>
        <div id="rs-giorno-succ-label" class="rs-next-day-label is-hidden">+1 Giorno</div>
    </div>

    <div id="rs-loading" class="is-hidden"><p>⟳ Calcolo in corso...</p></div>
 
<div class="temi-wrapper is-hidden" id="temi-wrapper">
        <div class="tema-box">
            <div class="tema-box-header">
                <button class="btn-toggle-gradi" id="btn-toggle-cuspidi" onclick="toggleCuspidiCase()">Nascondi Cuspidi</button>
                <h3>Tema Natale</h3>
                <button class="btn-toggle-gradi" id="btn-toggle-gradi" onclick="toggleGradiPianeti()">Mostra Gradi</button>
            </div>
            <svg id="wheel-natale" width="480" height="480" class="zodiac-wheel-responsive"></svg>
            <p class="tema-info" id="info-natale"></p>
            <table class="tabella-pianeti" id="tab-natale"></table>
            <div class="aspetti-container">
                <h4 class="rs-table-section-title">📐 Aspetti Transito → Natale</h4>
                <table class="tabella-aspetti">
                    <thead><tr><th>Natale</th><th>Aspetto</th><th>Transito</th><th>Orb</th></tr></thead>
                    <tbody id="aspetti-rs-body"><tr><td colspan="4" class="table-empty-cell">Nessun aspetto rilevante</td></tr>
                </tbody>
            </table>
            </div>
        </div>
 
        <div class="tema-box">
            <h3 id="rs-titolo">Transiti Planetari</h3>
            <svg id="wheel-rs" width="480" height="480" class="zodiac-wheel-responsive"></svg>
            <p class="tema-info" id="info-rs"></p>
            <table class="tabella-pianeti" id="tab-rs"></table>
            <div class="cuspidi-container">
                <h4 class="rs-table-section-title">🏠 Cuspidi Case Transito</h4>
                <table class="tabella-cuspidi">
                    <thead><tr><th>Casa</th><th>Gradi Cuspide</th></tr></thead>
                    <tbody id="cuspidi-rs-body"><tr><td colspan="2" class="table-empty-cell">—</td></tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
 
    <div id="print-aspetti-rs" class="print-only-aspetti"></div>
 
<?php endif; ?>
</main>
 
<script src="js/zodiac_wheel.js"></script>
<script src="js/svg_zoom.js"></script> 

<script src="js/app.js"></script>
<script src="js/rs_alert.js"></script>
<script>
<?php if ($soggetto && $jsData): ?>
const DS = <?= json_encode($jsData) ?>;
let temaNataleCache = null;

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
            if (data.status !== 'OK') return;
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
        .catch(() => {});
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
 
// ── Calcola Transiti (input Ore/Minuti in ora LOCALE, DST inclusa) ───────
function calcolaTransiti(latOvr, lonOvr) {
    let g      = parseInt(document.getElementById('transiti-giorno').value) || 1;
    let m      = parseInt(document.getElementById('transiti-mese').value) || 1;
    let a      = parseInt(document.getElementById('transiti-anno').value) || new Date().getFullYear();
    let oreLoc = parseInt(document.getElementById('transiti-ore').value) || 0;
    let minLoc = parseInt(document.getElementById('transiti-minuti').value) || 0;
    let latT   = (latOvr !== undefined) ? latOvr : document.getElementById('rs-lat').value;
    let lonT   = (lonOvr !== undefined) ? lonOvr : document.getElementById('rs-lon').value;
    let luogoT = document.getElementById('luogo-rs-input').value || DS.luogo;

    document.getElementById('rs-loading').style.display = 'block';
    document.getElementById('temi-wrapper').style.display = 'none';
    document.getElementById('header-rs').style.display    = 'none';

    const tsGuess = Date.UTC(a, m - 1, g, oreLoc, minLoc, 0);
    const apiKey  = TIMEZONE_API_KEY;

    fetch(`https://api.timezonedb.com/v2.1/get-time-zone?key=${apiKey}&format=json&by=position&lat=${latT}&lng=${lonT}&time=${Math.floor(tsGuess/1000)}`)
        .then(r => r.json())
        .then(tz => {
            const offsetSec = (tz.status === 'OK') ? tz.gmtOffset : 0;
            const dUtc = new Date(tsGuess - offsetSec * 1000);
            eseguiCalcoloTransiti(
                dUtc.getUTCDate(), dUtc.getUTCMonth() + 1, dUtc.getUTCFullYear(),
                dUtc.getUTCHours() + dUtc.getUTCMinutes() / 60,
                latT, lonT, luogoT
            );
        })
        .catch(() => {
            eseguiCalcoloTransiti(g, m, a, oreLoc + minLoc / 60, latT, lonT, luogoT);
        });
}

function eseguiCalcoloTransiti(g, m, a, oraGmtDec, latT, lonT, luogoT) {
    fetch('api/tema_api.php?tipo=transito&g='+g+'&m='+m+'&a='+a+
          '&ora_gmt='+oraGmtDec+'&lat='+latT+'&lon='+lonT)
        .then(r => r.json())
        .then(data => {
            if (data.errore) {
                throw new Error(data.errore);
            }

            document.getElementById('rs-loading').style.display = 'none';

            const dataStr = String(g).padStart(2,'0')+'/'+String(m).padStart(2,'0')+'/'+a;
            document.getElementById('rs-anno-label').textContent  = dataStr;
            document.getElementById('rs-gmt-label').textContent   = data.transito_gmt;
            document.getElementById('rs-luogo-label').textContent =
                luogoT + ' (Long: ' + parseFloat(lonT).toFixed(4) + '°, Lat: ' + parseFloat(latT).toFixed(4) + '°)';
            document.getElementById('header-rs').style.display    = 'flex';

            aggiornaFusoOrarioLocale(latT, lonT, data.transito_gmt);

            document.getElementById('rs-titolo').textContent = 'Transiti del '+dataStr+' — '+luogoT;

            ZodiacWheel.disegna('wheel-rs', data, {size:480});
            initSvgZoom('wheel-rs');
            document.getElementById('info-rs').textContent =
                'ASC: '+(data.case?.ASC?.posizione?.stringa??'?')+
                ' — MC: '+(data.case?.MC?.posizione?.stringa??'?');
            popolaTabellaPianeti('tab-rs', data);
            popolaTabellaCuspidi('cuspidi-rs-body', data);

            if (temaNataleCache) {
                popolaTabellaAspetti(calcolaAspettiTransitoNatale(data, temaNataleCache));
            } else {
                popolaTabellaAspetti([]);
            }

            document.getElementById('temi-wrapper').style.display = 'flex';
        })
        .catch(e => alert('Errore calcolo Transiti: '+e.message));
}

// ── Aspetti Transito → Natale (riusa ZodiacWheel.ASPETTI / _trovaAspetto) ──
function calcolaAspettiTransitoNatale(temaTransito, temaNatale) {
    const risultati = [];
    if (!temaTransito?.pianeti || !temaNatale?.pianeti) return risultati;
    Object.values(temaTransito.pianeti).forEach(pt => {
        Object.values(temaNatale.pianeti).forEach(pn => {
            let diff = Math.abs(pt.longitudine - pn.longitudine) % 360;
            if (diff > 180) diff = 360 - diff;
            const asp = ZodiacWheel._trovaAspetto(diff);
            if (!asp) return;
            risultati.push({
                pianeta_a: pt.id,
                pianeta_b: pn.id,
                aspetto:   asp.nome,
                scarto:    Math.abs(diff - asp.angolo)
            });
        });
    });
    return risultati;
}

// ── Imposta Ore/Minuti/Data all'ora di sistema attuale (GMT) ─────────────
function impostaOraAttuale() {
    const now = new Date();
    document.getElementById('transiti-giorno').value  = now.getDate();
    document.getElementById('transiti-mese').value    = now.getMonth() + 1;
    document.getElementById('transiti-anno').value    = now.getFullYear();
    document.getElementById('transiti-ore').value      = now.getHours();
    document.getElementById('transiti-minuti').value   = now.getMinutes();
    calcolaTransiti();
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
        tbody.innerHTML = '<tr><td colspan="4" class="table-empty-cell">Nessun aspetto rilevante</td></tr>';
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
        'Congiunzione':{sim:'☌',cls:'aspetto-altro'},'conjunction':{sim:'☌',cls:'aspetto-altro'},
        'trigono':{sim:'△',cls:'aspetto-trigono'},'quadratura':{sim:'□',cls:'aspetto-quadrato'},
        'opposizione':{sim:'☍',cls:'aspetto-opposizione'},'sestile':{sim:'⚹',cls:'aspetto-sestile'},
        'congiunzione':{sim:'☌',cls:'aspetto-altro'}
    };
    tbody.innerHTML = aspetti.map(a => {
        const ti = tipoMap[a.aspetto || a.tipo] || {sim:'•',cls:'aspetto-altro'};
        return `<tr>
            <td>${simboli[a.pianeta_b]??''} ${nomi[a.pianeta_b]??a.nome_b??'?'}</td>
            <td class="${ti.cls}">${ti.sim} ${a.aspetto||a.tipo}</td>
            <td>${simboli[a.pianeta_a]??''} ${nomi[a.pianeta_a]??a.nome_a??'?'}</td>
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
 
// ── Geocoding luogo RS ────────────────────────────────────────────────────
function cercaLuogoRS() {
    const q = document.getElementById('luogo-rs-input').value.trim();
    if (q.length < 3) return;
    fetch('https://nominatim.openstreetmap.org/search?q='+encodeURIComponent(q)+'&format=json&limit=6&addressdetails=1')
        .then(r => r.json())
        .then(ris => {
            const div = document.getElementById('luogo-rs-risultati');
            div.innerHTML = ris.map(r =>
                `<div class="dropdown-item" onclick="selezionaLuogoRS(${r.lat},${r.lon},'${r.display_name.replace(/'/g,"\\'")}')">
                    ${r.display_name}
                </div>`
            ).join('');
            div.classList.add('visible');
        });
}
 
function selezionaLuogoRS(lat, lon, nome) {
    document.getElementById('rs-lat').value = parseFloat(lat).toFixed(4);
    document.getElementById('rs-lon').value = parseFloat(lon).toFixed(4);
    document.getElementById('luogo-rs-input').value = nome.split(',')[0].trim();
    document.getElementById('luogo-rs-risultati').classList.remove('visible');
}
 
// ── Init DOM ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', e => {
        if (!e.target.closest('.luogo-rs-wrap'))
            document.getElementById('luogo-rs-risultati')?.classList.remove('visible');
    });

    // Data di default presa dal browser dell'utente (non dal server), ore/minuti restano 00:00
    const oggi = new Date();
    document.getElementById('transiti-giorno').value = oggi.getDate();
    document.getElementById('transiti-mese').value    = oggi.getMonth() + 1;
    document.getElementById('transiti-anno').value    = oggi.getFullYear();

    calcolaTransiti();
});
<?php endif; ?>
</script>
</body>
</html>
