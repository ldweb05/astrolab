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

// Usa soggetto attivo di sessione se non c'è ?id= nell'URL
// NON ridefinire $id più sotto: questa è la fonte di verità
$id = intval($_GET['id'] ?? $soggettoAttivo ?? 0);
// Se stiamo caricando un soggetto esplicito, aggiorna la sessione
if ($id > 0) { $auth->setSoggettoAttivo($id); $soggettoNome = $auth->getSoggettoNome(); }
// ===== FINE PATCH AUTH MULTI-ASTROLOGO =====
require_once 'includes/SweCalc.php';

$soggetto = null;
if ($id) {
    $soggetto = $auth->verificaSoggetto($id);
}

// Prepara i dati per JavaScript
$jsData = null;
if ($soggetto) {
    $date = new DateTime($soggetto['data_nascita']);
    $oraGmt = explode(':', $soggetto['ora_nascita_gmt']);
    $oraGmtDec = $oraGmt[0] + $oraGmt[1]/60;

    $jsData = [
        'giorno' => (int)$date->format('d'),
        'mese'   => (int)$date->format('m'),
        'anno'   => (int)$date->format('Y'),
        'ora_gmt'=> $oraGmtDec,
        'lat'    => (float)$soggetto['latitudine'],
        'lon'    => (float)$soggetto['longitudine'],
        'nome'   => htmlspecialchars($soggetto['nome'], ENT_QUOTES, 'UTF-8')
    ];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tema Natale</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/print.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Symbols+2&display=swap" rel="stylesheet">
    <script src="js/app.js"></script>
    <script src="js/svg_zoom.js"></script>
    <style>
        .tema-box.tema-box-full { max-width: 100%; min-width: 0; width: 100%; background: #F2EDE4; box-shadow: none; }
        .tema-col { display: flex; flex-direction: column; gap: 16px; flex: 1; min-width: 460px; max-width: 540px; }
        .tema-col .tema-box { min-width: 0; max-width: none; }
        .tema-box-full .tema-box-header { justify-content: center; gap: 14px; }
        .tema-box-full .tema-box-header h3 { flex: 0 0 auto; }
    </style>
</head>
<body>
<?php $paginaAttiva = 'tema'; include 'includes/header_nav.php'; ?>

<main>
<?php if (!$soggetto): ?>
    <div class="card">
        <p class="empty">Seleziona un soggetto dalla <a href="index.php">lista soggetti</a>.</p>
    </div>
<?php else: ?>

    <div class="header-soggetto">
        <div><span>Soggetto: </span><b><?= htmlspecialchars($soggetto['nome']) ?></b></div>
        <div><span>Nato il: </span><b><?= date('d/m/Y', strtotime($soggetto['data_nascita'])) ?></b></div>
        <div><span>Ore: </span><b><?= substr($soggetto['ora_nascita'],0,5) ?> (loc.) - <?= substr($soggetto['ora_nascita_gmt'],0,5) ?> GMT</b></div>
        <div><span>Luogo: </span><b><?= htmlspecialchars($soggetto['luogo_nascita'] . ' ' . $soggetto['nazione_nascita']) ?></b></div>
    </div>

    <!-- NUOVO BLOCCO: pulsante stampa -->
    <div class="page-title page-title-compact">
        <button class="btn-stampa-diretta" onclick="stampaPagina('print-tema')">🖨️ Stampa Tema Natale</button>
    </div>

    <div class="tema-box tema-box-full">
        <div class="tema-box-header">
            <button class="btn-toggle-gradi" id="btn-toggle-cuspidi"
                    onclick="toggleCuspidiCase()">Nascondi Cuspidi</button>
            <h3>Tema Natale</h3>
            <button class="btn-toggle-gradi" id="btn-toggle-gradi"
                    onclick="toggleGradiPianeti()">Mostra Gradi</button>
        </div>
        <svg id="wheel-natale" width="700" height="700" class="zodiac-wheel-responsive"></svg>
        <p class="tema-info" id="info-natale">Caricamento...</p>
    </div>

    <div class="temi-wrapper">
        <div class="tema-col">
            <div class="tema-box">
                <h3>Pianeta, Posizione e Casa</h3>
                <div id="tab-natale"></div>
            </div>
            <div class="tema-box">
                <h3>Aspetti tra i Pianeti</h3>
                <table class="tabella-aspetti">
                    <thead><tr><th>Pianeta 1</th><th>Aspetto</th><th>Pianeta 2</th><th>Orb</th></tr></thead>
                    <tbody id="aspetti-natale-body"><tr><td colspan="4" class="table-empty-cell">Nessun aspetto rilevante</td></tr></tbody>
                </table>
            </div>
        </div>
        <div class="tema-box">
            <h3>Case (Placido)</h3>
            <div id="tab-case"></div>
        </div>
    </div>

<?php endif; ?>
</main>

<script src="js/zodiac_wheel.js"></script>
<script>
<?php if ($soggetto && $jsData): ?>
const datiSoggetto = <?= json_encode($jsData) ?>;

console.log("Dati soggetto:", datiSoggetto);

fetch('api/tema_api.php?tipo=natale' +
    '&g=' + datiSoggetto.giorno +
    '&m=' + datiSoggetto.mese +
    '&a=' + datiSoggetto.anno +
    '&ora_gmt=' + datiSoggetto.ora_gmt +
    '&lat=' + datiSoggetto.lat +
    '&lon=' + datiSoggetto.lon)
.then(r => r.json())
.then(tema => {
    console.log("Tema ricevuto, pianeti:", Object.keys(tema.pianeti).length);
    ZodiacWheel.disegna('wheel-natale', tema, {size: 700});
    initSvgZoom('wheel-natale');
    document.getElementById('info-natale').textContent =
        'ASC: ' + (tema.case?.ASC?.posizione?.stringa ?? '?') +
        ' - MC: ' + (tema.case?.MC?.posizione?.stringa ?? '?');

    const nomi = {0:'☉ Sole',1:'☽ Luna',2:'☿ Mercurio',3:'♀ Venere',
                  4:'♂ Marte',5:'♃ Giove',6:'♄ Saturno',7:'♅ Urano',
                  8:'♆ Nettuno',9:'♇ Plutone',11:'☊ Nodo N.'};
    let html = '<table class="tabella-pianeti"><tr><th>Pianeta</th><th>Posizione</th><th>Casa</th><th></th></tr>';
    Object.values(tema.pianeti).forEach(p => {
        html += '<tr>' +
            '<td>' + (nomi[p.id] ?? p.nome) + '</td>' +
            '<td>' + p.posizione.stringa + '</td>' +
            '<td>' + p.casa + '</td>' +
            '<td>' + (p.retrogrado ? '<span class="retro">R</span>' : '') + '</td>' +
        '</tr>';
    });
    html += '</table>';
    document.getElementById('tab-natale').innerHTML = html;

    let htmlC = '<table class="tabella-pianeti"><tr><th>Casa</th><th>Cuspide</th></tr>';
    for (let c = 1; c <= 12; c++) {
        if (tema.case[c]) {
            htmlC += '<tr><td><b>' + c + '</b></td><td>' + tema.case[c].posizione.stringa + '</td></tr>';
        }
    }
    ['ASC','MC'].forEach(k => {
        if (tema.case[k])
            htmlC += '<tr><td><b>' + k + '</b></td><td>' + tema.case[k].posizione.stringa + '</td></tr>';
    });
    htmlC += '</table>';
    document.getElementById('tab-case').innerHTML = htmlC;

    popolaTabellaAspettiNatale(calcolaAspettiNatali(tema));
})
.catch(err => {
    console.error("Errore:", err);
    document.getElementById('info-natale').textContent = "Errore: " + err.message;
});

// ── Aspetti tra i pianeti natali (riusa ZodiacWheel.ASPETTI / _trovaAspetto) ──
function calcolaAspettiNatali(tema) {
    const risultati = [];
    if (!tema.pianeti) return risultati;
    const punti = Object.values(tema.pianeti);
    for (let i = 0; i < punti.length; i++) {
        for (let j = i + 1; j < punti.length; j++) {
            let diff = Math.abs(punti[i].longitudine - punti[j].longitudine) % 360;
            if (diff > 180) diff = 360 - diff;
            const asp = ZodiacWheel._trovaAspetto(diff);
            if (!asp) continue;
            risultati.push({
                pianeta_a: punti[i].id,
                pianeta_b: punti[j].id,
                aspetto:   asp.nome,
                scarto:    Math.abs(diff - asp.angolo)
            });
        }
    }
    return risultati;
}

function popolaTabellaAspettiNatale(aspetti) {
    const tbody = document.getElementById('aspetti-natale-body');
    if (!tbody) return;
    if (!aspetti || aspetti.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="table-empty-cell">Nessun aspetto rilevante</td></tr>';
        return;
    }
    const simboliA = {0:'☉',1:'☽',2:'☿',3:'♀',4:'♂',5:'♃',6:'♄',7:'♅',8:'♆',9:'♇'};
    const nomiA    = {0:'Sole',1:'Luna',2:'Mercurio',3:'Venere',4:'Marte',5:'Giove',
                       6:'Saturno',7:'Urano',8:'Nettuno',9:'Plutone'};
    const tipoMap  = {
        'trigono':{sim:'△',cls:'aspetto-trigono'},
        'quadratura':{sim:'□',cls:'aspetto-quadrato'},
        'opposizione':{sim:'☍',cls:'aspetto-opposizione'},
        'sestile':{sim:'⚹',cls:'aspetto-sestile'},
        'congiunzione':{sim:'☌',cls:'aspetto-altro'}
    };
    tbody.innerHTML = aspetti.map(a => {
        const ti = tipoMap[a.aspetto] || {sim:'•',cls:'aspetto-altro'};
        return `<tr>
            <td>${simboliA[a.pianeta_a]??''} ${nomiA[a.pianeta_a]??'?'}</td>
            <td class="${ti.cls}">${ti.sim} ${a.aspetto}</td>
            <td>${simboliA[a.pianeta_b]??''} ${nomiA[a.pianeta_b]??'?'}</td>
            <td>${a.scarto?.toFixed(1)??'?'}°</td>
        </tr>`;
    }).join('');
}
<?php endif; ?>
</script>
</body>
</html>