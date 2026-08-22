<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/NascitaGmtHelper.php';
// ===== INIZIO PATCH AUTH MULTI-ASTROLOGO =====
session_start();
require_once 'includes/Auth.php';
$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();
$isAdmin        = $auth->isAdmin();
$username       = $auth->getCurrentUsername();
$userId         = $auth->getCurrentUserId();
$soggettoAttivo = $auth->getSoggettoAttivo();
$soggettoNome   = $auth->getSoggettoNome();
// ===== FINE PATCH AUTH MULTI-ASTROLOGO =====
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Permette di preselezionare il tipo di ricerca via URL (es. da dashboard.php)
$tipoLocalitaDefault = ($_GET['tipo'] ?? '') === 'localita' ? 'localita' : 'aeroporti';
// ---- QUERY SOGGETTI CON FILTRO PER UTENTE ----

require_once __DIR__ . '/includes/RicercaPageData.php';

?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ricerca Località — Astrologia Attiva</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php $paginaAttiva = 'ricerca'; include 'includes/header_nav.php'; ?>
<main>
<div class="page-title">
<h2>Ricerca Migliori Località RS</h2>
</div>
<!-- ══════════════════════════════════════════════════════════════════
BARRA CONTROLLI PRINCIPALE
═══════════════════════════════════════════════════════════════════════ -->
<div class="controlli" id="controlli-bar">
<div class="form-group">
<label>Soggetto</label>
<select id="sel-soggetto">
<option value="">— Seleziona —</option>
<?php foreach ($soggetti as $s): ?>
<option value="<?= $s['id'] ?>"
<?= $s['id'] == $soggettoId ? 'selected' : '' ?>>
<?= htmlspecialchars($s['nome']) ?>
(<?= date('d/m/Y', strtotime($s['data_nascita'])) ?>)
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label>Anno RS</label>
<select id="anno-rs">
<?php for ($y = 1960; $y <= $annoCorrente + 5; $y++): ?>
<option value="<?= $y ?>" <?= $y == $annoCorrente ? 'selected' : '' ?>><?= $y ?></option>
<?php endfor; ?>
</select>
</div>
<div class="form-group">
<label>Condizione</label>
<select id="condizione" onchange="onCondizioneChange(this.value)">
<?php foreach ($condizioni as $c): ?>
<option value="<?= $c ?>"><?= $c ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label>Tipo località</label>
<select id="tipo-localita" onchange="onTipoLocalitaChange(this.value)">
<option value="aeroporti" <?= $tipoLocalitaDefault === 'aeroporti' ? 'selected' : '' ?>>Aeroporti</option>
<option value="localita" <?= $tipoLocalitaDefault === 'localita' ? 'selected' : '' ?>>Località</option>
</select>
</div>
<div class="form-group" id="wrap-nazione-localita" style="display:none">
<label>Nazione</label>
<select id="nazione-localita">
<option value="">— Seleziona —</option>
</select>
</div>
<div class="form-group" id="wrap-numero-localita" style="display:none">
<label>Numero risultati</label>
<select id="numero-localita">
<option value="50">50</option>
<option value="100">100</option>
<option value="150">150</option>
<option value="200">200</option>
<option value="250">250</option>
<option value="500">500</option>
<option value="1000">1000</option>
</select>
</div>
<button class="filtri-avanzati-toggle" id="btn-toggle-avanzati" onclick="toggleAvanzati()">
⚙️ Filtri avanzati <em class="chevron">▼</em>
</button>
<!-- ⚠️ CHECKBOX MOSTRA ESCLUSE (UNICO) ⚠️ -->
<div class="checkbox-escluse" style="display: inline-flex; align-items: center; gap: 5px;">
<input type="checkbox" id="mostra-escluse" style="margin: 0;">
<label for="mostra-escluse" style="margin:0; cursor: pointer; font-size: 12px; white-space: nowrap;">
Mostra anche le RS escluse dal filtro
</label>
</div>
<!-- ⚠️ FINE CHECKBOX ⚠️ -->
<button class="btn-primary" id="btn-cerca">🔍 Cerca</button>
</div>
<!-- ══════════════════════════════════════════════════════════════════
SUB-PANNELLO: LONGITUDINE CUSPIDI
═══════════════════════════════════════════════════════════════════════ -->
<div id="pannello-cuspidi">
<div class="form-group">
<label>Casa (cuspide RS)</label>
<select id="cusp-casa">
<?php for ($c = 1; $c <= 12; $c++): ?>
<option value="<?= $c ?>">Casa <?= $c ?></option>
<?php endfor; ?>
</select>
</div>
<div class="form-group">
<label>Segno zodiacale</label>
<select id="cusp-segno">
<option value="0">— qualsiasi —</option>
<?php foreach ($segni as $n => $nome): ?>
<option value="<?= $n ?>"><?= $nome ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label>Gradi target</label>
<input type="number" id="cusp-gradi" min="0" max="29" value="0">
</div>
<div class="form-group">
<label>± Tolleranza °</label>
<input type="number" id="cusp-tol-gradi" min="0" max="30" value="1">
</div>
<div class="cuspidi-sep">e</div>
<div class="form-group">
<label>Minuti target</label>
<input type="number" id="cusp-minuti" min="0" max="59" value="0">
</div>
<div class="form-group">
<label>± Tolleranza ′</label>
<input type="number" id="cusp-tol-minuti" min="0" max="59" value="30">
</div>
<div style="align-self:flex-end;font-size:11px;color:#5A6A8A;padding-bottom:8px">
💡 Cerca nella RS dell'anno selezionato
</div>
</div>
<!-- ══════════════════════════════════════════════════════════════════
SUB-PANNELLO: ASTRI NELLE CASE
═══════════════════════════════════════════════════════════════════════ -->
<div id="pannello-astri">
<div class="astri-header">
<h4>⭐ Regole Astri nelle Case della RS</h4>
<button class="btn-reset-tutti" onclick="resetTutteRegole()">↺ Azzera tutte le regole</button>
</div>
<div class="aggiungi-regola">
<div class="form-group">
<label>Astro / Pianeta</label>
<select id="nuovo-astro-select">
<option value="0">☉ Sole</option>
<option value="1">☽ Luna</option>
<option value="2">☿ Mercurio</option>
<option value="3">♀ Venere</option>
<option value="4">♂ Marte</option>
<option value="5">♃ Giove</option>
<option value="6">♄ Saturno</option>
<option value="7">♅ Urano</option>
<option value="8">♆ Nettuno</option>
<option value="9">♇ Plutone</option>
<option value="ASC">↑ ASC (RS)</option>
</select>
</div>
<div class="form-group">
<label>Casa Astrologica</label>
<select id="nuova-casa-select">
<?php for ($c = 1; $c <= 12; $c++): ?>
<option value="<?= $c ?>">Casa <?= $c ?></option>
<?php endfor; ?>
</select>
</div>
<div class="form-group">
<label>Condizione</label>
<select id="nuova-condizione-select">
<option value="deve">✓ Lo VOGLIO in questa casa</option>
<option value="evita">✗ NON lo voglio in questa casa</option>
</select>
</div>
<div class="form-group">
<label>Vicinanza</label>
<select id="nuova-modalita-select" onchange="onModalitaRegolaChange(this)">
<option value="in_casa">Ovunque nella casa</option>
<option value="cuspide">In cuspide (Regola 32)</option>
</select>
</div>
<button class="btn-aggiungi" onclick="aggiungiRegola()">+ Aggiungi regola</button>
</div>
<div class="regole-lista" id="regole-lista">
<div id="regole-container">
<div class="regole-vuote">Nessuna regola attiva. Aggiungine una usando il pannello sopra.</div>
</div>
</div>
<div class="astri-sommario" id="astri-sommario">
<strong style="font-size:11px;color:#2C3E6B;text-transform:uppercase;letter-spacing:0.04em">
🔍 Filtro attivo:
</strong>
<span id="astri-sommario-tags"></span>
</div>
<div style="margin-top:12px;font-size:11px;color:#5A6A8A">
💡 Solo le condizioni impostate vengono usate come filtro (AND logico).<br>
Le stelline mostrate seguono la condizione tematica <strong>"Decima"</strong> per il ranking.
</div>
</div>
<!-- ══════════════════════════════════════════════════════════════════
PANNELLO FILTRI AVANZATI (comune a tutte le modalità)
═══════════════════════════════════════════════════════════════════════ -->
<div id="pannello-avanzati" class="avanzati-wrap">
<!-- ── Sezione 1: Filtri geografici ──────────────────────────── -->
<div class="avanzati-section-title">🌍 Filtri geografici</div>
<div class="avanzati-grid">
<!-- Filtro Macro-Area Geo-politica -->
<div class="avanzati-group">
<label>
Macro-Area / Regione
<span class="tooltip-wrap tip-right">
<i class="tooltip-icon">i</i>
<span class="tooltip-box">Esclude o limita la ricerca a una macro-area mondiale. Evita zone a rischio geopolitico o mete logisticamente irraggiungibili. Puoi scegliere "Solo Europa", "Solo America del Nord" ecc. I codici nazione ISO-2 corrispondenti vengono trasmessi all'API come filtro. Non ha effetto sulla Ricerca a Griglia (nessun dato nazione associato a una coordinata pura).</span>
</span>
</label>
<select id="filtro-macro-area" onchange="onMacroAreaChange(this.value)">
<option value="">— Tutto il mondo —</option>
<option value="europa">🇪🇺 Solo Europa</option>
<option value="americhe">🌎 Continente Americano</option>
<option value="nord_america">🌎 Solo Nord America</option>
<option value="centro_sud">🌎 Centro e Sud America</option>
<option value="africa">🌍 Solo Africa</option>
<option value="medio_oriente">🕌 Medio Oriente</option>
<option value="asia">🌏 Solo Asia</option>
<option value="oceania">🦘 Oceania / Pacifico</option>
</select>
</div>
<!-- Filtro Fascia Oraria -->
<div class="avanzati-group">
<label>
Fascia Oraria (Longitudine)
<span class="tooltip-wrap">
<i class="tooltip-icon">i</i>
<span class="tooltip-box">Limita la ricerca a un intervallo di longitudini geografiche, corrispondenti a fasce orarie. Utile per escludere mete con jet-lag eccessivo rispetto alla residenza del soggetto. Es. -30°/+30° attorno alla residenza per restare nello stesso fuso o nelle zone adiacenti. Nella Ricerca a Griglia questo campo funge anche da bbox di longitudine.</span>
</span>
</label>
<div class="avanzati-row">
<div>
<div style="font-size:10px;color:#999;margin-bottom:2px">Da lon°</div>
<input type="number" id="filt-lon-min" min="-180" max="180" step="1" placeholder="-180" style="width:72px">
</div>
<div style="align-self:flex-end;padding-bottom:4px;color:#999">→</div>
<div>
<div style="font-size:10px;color:#999;margin-bottom:2px">A lon°</div>
<input type="number" id="filt-lon-max" min="-180" max="180" step="1" placeholder="+180" style="width:72px">
</div>
</div>
<div style="font-size:10px;color:#888;margin-top:3px">Lascia vuoto = nessun filtro</div>
</div>
</div><!-- /avanzati-grid geografia -->
<hr class="avanzati-sep">
<!-- ── Sezione 2: Parametri tecnici ──────────────────────────── -->
<div class="avanzati-section-title">🔭 Parametri tecnici</div>
<div class="avanzati-grid">
<!-- Tolleranza Dinamica (solo Cuspidi — evidenziato in altre modalità) -->
<div class="avanzati-group" id="wrap-tolleranza-dinamica">
<label>
Tolleranza dinamica (Orbe)
<span class="tooltip-wrap">
<i class="tooltip-icon">i</i>
<span class="tooltip-box">Restringe o allarga l'orbe astrologico tollerato. In modalità Cuspidi aggiorna direttamente i campi "± Tolleranza" nel pannello sopra (funziona anche in combinazione con la Ricerca a Griglia).</span>
</span>
</label>
<select id="filt-orbe-preset">
<option value="">— Default (usa pannello) —</option>
<option value="stretto">Stretto: ±0° 15′</option>
<option value="normale">Normale: ±1° 0′</option>
<option value="largo">Largo: ±2° 0′</option>
<option value="larghissimo">Larghissimo: ±5° 0′</option>
</select>
</div>
<!-- Filtri aeroportuali -->
<div class="avanzati-group">
<label>Aeroporti</label>
<select id="tipo-ricerca">
<option value="large_medium">Grandi + Medi</option>
<option value="iata_only">Solo IATA</option>
<option value="tutti">Tutti (lento)</option>
</select>
</div>
<div class="avanzati-group">
<label>Militari</label>
<select id="escludi-militari">
<option value="1">Escludi</option>
<option value="0">Includi</option>
</select>
</div>
<!-- Stelline min — nascosta in modalità Cuspidi -->
<div class="avanzati-group" id="wrap-stelline-min">
<label>Stelline min.</label>
<select id="stelline-min-cerca">
<option value="0">Tutte</option>
<option value="3">≥ ★★★</option>
<option value="4">≥ ★★★★</option>
<option value="5">★★★★★</option>
</select>
</div>
<!-- Importanza Aeroporto -->
<div class="avanzati-group">
<label>
Importanza aeroporto
<span class="tooltip-wrap">
<i class="tooltip-icon">i</i>
<span class="tooltip-box">Limita la ricerca ai soli grandi Hub internazionali (large_airport con codice IATA) per mostrare solo mete facilmente raggiungibili con voli di linea. Sovrascrive il selettore "Aeroporti" nella barra principale se diverso. Non pertinente per la Ricerca a Griglia.</span>
</span>
</label>
<select id="filt-importanza">
<option value="">— Usa il selettore principale —</option>
<option value="solo_hub">Solo Hub (large + IATA)</option>
<option value="iata_only">Aeroporti IATA</option>
</select>
</div>
<!-- Allargamento Automatico Orbe (solo Cuspidi) -->
<div class="avanzati-group" id="wrap-espandi-orbe">
<label>
Allargamento automatico orbe
<span class="tooltip-wrap">
<i class="tooltip-icon">i</i>
<span class="tooltip-box">Se la ricerca con la tolleranza attuale restituisce 0 risultati, il sistema rilancia automaticamente con l'orbe raddoppiato (es. ±1° → ±2° → ±5°) fino a trovare risultati o raggiungere il massimo. Attivo solo in modalità Cuspidi (aeroporti o griglia).</span>
</span>
</label>
<select id="filt-espandi-orbe">
<option value="no">Disabilitato</option>
<option value="si">Abilitato (auto-expand)</option>
</select>
</div>
</div><!-- /avanzati-grid tecnici -->
<hr class="avanzati-sep">
<!-- ── Sezione 3: Ricerca avanzata ───────────────────────────── -->
<div class="avanzati-section-title">
📐 Ricerca avanzata
</div>
<div class="avanzati-grid">
<!-- Grid Search -->
<div class="avanzati-group">
    <label>
        Ricerca a Griglia Geometrica
        <span class="tooltip-wrap tip-right">
            <i class="tooltip-icon">i</i>
            <span class="tooltip-box">Scansiona le posizioni astrologiche su una griglia fissa di coordinate geografiche invece di limitarsi agli aeroporti. Utile quando la configurazione desiderata cade in zone remote. Supporta condizioni tematiche standard, Astri nelle Case e Longitudine Cuspidi (seleziona la condizione desiderata sopra prima di cercare). 0.5° può richiedere diversi minuti.</span>
        </span>
    </label>
    <select id="filt-grid-search">
        <option value="no">Disabilitata</option>
        <option value="2deg">Griglia 2° × 2°</option>
        <option value="1deg">Griglia 1° × 1°</option>
        <option value="0.5deg">Griglia 0.5° × 0.5° (lento)</option>
    </select>
</div>

<!-- Bounding box griglia — visibile solo se griglia attiva -->
<div class="avanzati-group" id="wrap-griglia-bbox" style="display:none">
    <label>
        Area di scansione (Lat °)
        <span class="tooltip-wrap">
            <i class="tooltip-icon">i</i>
            <span class="tooltip-box">Limita la scansione a una fascia di latitudine per ridurre drasticamente il numero di punti calcolati. Default: -60°/+60° (oltre 60° è comunque veto per la regola 31). Usa anche "Fascia Oraria" sopra per limitare la longitudine.</span>
        </span>
    </label>
    <div class="avanzati-row">
        <input type="number" id="grid-lat-min" min="-60" max="60" step="0.5" value="-60" style="width:70px">
        <span style="align-self:center;color:#999">→</span>
        <input type="number" id="grid-lat-max" min="-60" max="60" step="0.5" value="60" style="width:70px">
    </div>
    <div style="font-size:10px;color:#888;margin-top:3px">La longitudine usa il campo "Fascia Oraria" qui sopra</div>
</div>
</div><!-- /avanzati-grid avanzata -->
</div><!-- /pannello-avanzati -->
<!-- ── Info RS ──────────────────────────────────────────────────────── -->
<div class="info-rs" id="info-rs">
<div><span>RS GMT: </span><b id="info-rs-gmt">—</b></div>
<div><span>Condizione: </span><b id="info-rs-cond">—</b></div>
<div><span>Aeroporti calcolati: </span><b id="info-rs-calcolati">—</b></div>
<div id="info-rs-esclusi-wrap" style="display:none">
<span>⚠️ Escluse dal filtro:
<span class="tooltip-wrap">
    <i class="tooltip-icon">i</i>
    <span class="tooltip-box">RS escluse perché presentano Sole/Marte in I/VI/XII RS, ASC RS in I/VI/XII natale, Saturno in X RS o uno stellium in qualsiasi casa RS. Questo è un filtro aggiuntivo di Astrolab, non parte delle 34 regole ufficiali dell'Astrologia Attiva: alle latitudini estreme può escludere risultati che altri software di riferimento (es. MyAstral.org) mostrano comunque, perché lì gli stellium sono più frequenti per compressione delle case.</span>
</span>
</span> <b id="info-rs-esclusi">—</b>
</div>
<div><span>Tempo: </span><b id="info-rs-tempo">—</b></div>
</div>
<!-- ── Progress ─────────────────────────────────────────────────────── -->
<div id="progress-area">
<div class="progress-header">
<span id="progress-label">Calcolo in corso...</span>
<span id="progress-perc">0%</span>
</div>
<div class="progress-bar-wrap">
<div class="progress-bar-fill" id="progress-fill"></div>
</div>
<div class="progress-sub" id="progress-sub">Inizializzazione...</div>
<div class="risultati-live" id="risultati-live"></div>
</div>
<!-- ── Area risultati ───────────────────────────────────────────────── -->
<div id="risultati-area">
<div class="empty-results">
Seleziona un soggetto e premi "Cerca" per avviare la ricerca.
</div>
</div>
</main>
<!-- ══════════════════════════════════════════════════════════════════════
JAVASCRIPT
═══════════════════════════════════════════════════════════════════════════ -->
<script>
// ── Dati soggetti dal PHP ──────────────────────────────────────────────────
const soggettiData = <?= json_encode(array_map(function($s) {
// Calcolo corretto data/ora GMT gestendo il cambio di giorno
$gmtData = calcolaDataOraGmtCorretta(
    $s['data_nascita'],
    $s['ora_nascita'],
    (float)($s['offset_gmt'] ?? 0)
);
$dateGmt = new DateTime($gmtData['data_gmt'] . ' ' . $gmtData['ora_gmt']);
$oraGmtParts = explode(':', $gmtData['ora_gmt']);
return [
'id'     => (int)$s['id'],
'giorno' => (int)$dateGmt->format('d'),
'mese'   => (int)$dateGmt->format('m'),
'anno'   => (int)$dateGmt->format('Y'),
'ora_gmt'=> (int)$oraGmtParts[0] + ((int)($oraGmtParts[1] ?? 0) / 60),
'lat'    => (float)$s['latitudine'],
'lon'    => (float)$s['longitudine'],
];
}, $soggetti)) ?>;
// Macro-aree: codici ISO-2 per filtro geografico (specchio di PHP)
const MACRO_AREE = <?= json_encode($MACRO_AREE) ?>;
const SEGNI_SIM = {
1:'♈',2:'♉',3:'♊',4:'♋',5:'♌',6:'♍',
7:'♎',8:'♏',9:'♐',10:'♑',11:'♒',12:'♓'
};
const ASTRO_SIMBOLI = {
0:'☉',1:'☽',2:'☿',3:'♀',4:'♂',5:'♃',6:'♄',7:'♅',8:'♆',9:'♇','ASC':'↑'
};
const ASTRO_NOMI = {
0:'Sole',1:'Luna',2:'Mercurio',3:'Venere',4:'Marte',
5:'Giove',6:'Saturno',7:'Urano',8:'Nettuno',9:'Plutone','ASC':'ASC (RS)'
};
const CONDIZIONE_CUSPIDI = '— Longitudine Cuspidi —';
const CONDIZIONE_ASTRI   = '— Astri nelle Case —';

const USER_FEATURES = {
    locality_search: <?= json_encode($auth->hasFeature('locality_search')) ?>,
    grid_search: <?= json_encode($auth->hasFeature('grid_search')) ?>,
    dynamic_orb: <?= json_encode($auth->hasFeature('dynamic_orb')) ?>,
    astri_in_cuspide: <?= json_encode($auth->hasFeature('astri_in_cuspide')) ?>
};

const SUPPORTER_MESSAGE = 'Questa funzione è riservata agli utenti del piano Supporter.';
const COMPARATOR_LIMIT = <?= json_encode($auth->getComparatorLimit()) ?>;
const COMPARATOR_LIMIT_MESSAGE = COMPARATOR_LIMIT < 3
    ? 'Il piano gratuito consente di confrontare fino a 2 risultati. Per confrontare 3 RSM è necessario il piano Supporter.'
    : `Puoi confrontare al massimo ${COMPARATOR_LIMIT} RSM.`;
// ── Stato ────────────────────────────────────────────────────────────────
let stato = {
tutti:        [],
modalita:     'standard',   // 'standard' | 'cuspidi' | 'astri' | 'griglia' | 'griglia-astri' | 'griglia-cuspidi'
filtroNaz:    '',
filtroStelle: 0,
pagina:       1,
perPagina:    50,
confronto:    [],
// Dati dell'ultimo "done" ricevuto dall'API, usati da espandi-orbe
ultimiParams: null,
offsetRicerca: 0,
analizzatiFinoA: 0,
ricercaCompletata: true,
};
let eventoCorrente = null;

const RICERCA_STORAGE_KEY = 'astrolabRicercaStato';

function salvaStatoRicerca() {
    if (!stato.tutti.length) return;

    const payload = {
        stato: {
            tutti: stato.tutti,
            modalita: stato.modalita,
            filtroNaz: stato.filtroNaz,
            filtroStelle: stato.filtroStelle,
            pagina: stato.pagina,
            perPagina: stato.perPagina,
            confronto: stato.confronto,
            offsetRicerca: stato.offsetRicerca,
            analizzatiFinoA: stato.analizzatiFinoA,
            ricercaCompletata: stato.ricercaCompletata
        },
        controlli: {
            soggetto: document.getElementById('sel-soggetto').value,
            anno: document.getElementById('anno-rs').value,
            condizione: document.getElementById('condizione').value
        },
        scrollY: window.scrollY
    };

    try {
        sessionStorage.setItem(RICERCA_STORAGE_KEY, JSON.stringify(payload));
    } catch (errore) {
        console.warn('Impossibile salvare lo stato della ricerca:', errore);
    }
}

function ripristinaStatoRicerca() {
    const json = sessionStorage.getItem(RICERCA_STORAGE_KEY);
    if (!json) return;

    try {
        const payload = JSON.parse(json);
        sessionStorage.removeItem(RICERCA_STORAGE_KEY);
        if (!payload?.stato?.tutti?.length) return;

        Object.assign(stato, payload.stato);

        if (payload.controlli) {
            document.getElementById('sel-soggetto').value = payload.controlli.soggetto || '';
            document.getElementById('anno-rs').value = payload.controlli.anno || '';
            document.getElementById('condizione').value = payload.controlli.condizione || '';
            onCondizioneChange(document.getElementById('condizione').value);
        }

        renderTabella();

        requestAnimationFrame(() => {
            window.scrollTo(0, Number(payload.scrollY) || 0);
        });
    } catch (errore) {
        console.warn('Stato della ricerca non valido:', errore);
        sessionStorage.removeItem(RICERCA_STORAGE_KEY);
    }
}

// ════════════════════════════════════════════════════════════════════════
//  TOGGLE PANNELLI
// ════════════════════════════════════════════════════════════════════════
function toggleAvanzati() {
const panel = document.getElementById('pannello-avanzati');
const btn   = document.getElementById('btn-toggle-avanzati');
const vis   = panel.classList.toggle('visibile');
btn.classList.toggle('aperto', vis);
}
function onTipoLocalitaChange(val) {
const select = document.getElementById('tipo-localita');

if (val === 'localita' && !USER_FEATURES.locality_search) {
alert(SUPPORTER_MESSAGE);
select.value = 'aeroporti';
val = 'aeroporti';
}

const ricercaLocalita = val === 'localita';
document.getElementById('wrap-nazione-localita').style.display = ricercaLocalita ? '' : 'none';
document.getElementById('wrap-numero-localita').style.display = ricercaLocalita ? '' : 'none';
}

function onModalitaRegolaChange(select) {
if (select.value === 'cuspide' && !USER_FEATURES.astri_in_cuspide) {
alert(SUPPORTER_MESSAGE);
select.value = 'in_casa';
}
}

function applicaRestrizioniInterfaccia() {
const localitaOption = document.querySelector('#tipo-localita option[value="localita"]');
if (localitaOption && !USER_FEATURES.locality_search) {
localitaOption.disabled = true;
localitaOption.textContent = 'Località (Supporter)';
}

const gridSelect = document.getElementById('filt-grid-search');
if (gridSelect && !USER_FEATURES.grid_search) {
Array.from(gridSelect.options).forEach(option => {
if (option.value !== 'no') {
option.disabled = true;
option.textContent += ' (Supporter)';
}
});
}

const dynamicOrbOption = document.querySelector('#filt-espandi-orbe option[value="si"]');
if (dynamicOrbOption && !USER_FEATURES.dynamic_orb) {
dynamicOrbOption.disabled = true;
dynamicOrbOption.textContent = 'Abilitato (Supporter)';
}

const modalitaCuspideOption = document.querySelector('#nuova-modalita-select option[value="cuspide"]');
if (modalitaCuspideOption && !USER_FEATURES.astri_in_cuspide) {
modalitaCuspideOption.disabled = true;
modalitaCuspideOption.textContent += ' (Supporter)';
}
}

function localizzaNomiNazioni() {
if (typeof Intl === 'undefined' || typeof Intl.DisplayNames !== 'function') {
return;
}

const nomiRegioni = new Intl.DisplayNames(['it'], { type: 'region' });

document.querySelectorAll('#nazione-localita option[data-iso-nazione]').forEach(option => {
const iso = option.dataset.isoNazione;
const nomeItaliano = nomiRegioni.of(iso);

if (nomeItaliano && nomeItaliano !== iso) {
option.textContent = nomeItaliano;
}
});
}

let caricamentoNazioniPromise = null;

function caricaNazioniLocalita() {
if (caricamentoNazioniPromise) {
return caricamentoNazioniPromise;
}

const select = document.getElementById('nazione-localita');

caricamentoNazioniPromise = fetch('api/nazioni_localita_api.php')
.then(response => {
if (!response.ok) {
throw new Error('Caricamento nazioni non riuscito');
}
return response.json();
})
.then(nazioni => {
nazioni.forEach(nazione => {
const option = document.createElement('option');
option.value = nazione.iso_nazione;
option.dataset.isoNazione = nazione.iso_nazione;
option.textContent = nazione.nome_nazione || nazione.iso_nazione;
select.appendChild(option);
});

localizzaNomiNazioni();
})
.catch(error => {
caricamentoNazioniPromise = null;
console.error(error);
});

return caricamentoNazioniPromise;
}

function onCondizioneChange(val) {
const isCuspidi = val === CONDIZIONE_CUSPIDI;
const isAstri   = val === CONDIZIONE_ASTRI;
const pannC     = document.getElementById('pannello-cuspidi');
const pannA     = document.getElementById('pannello-astri');
const wrapSt    = document.getElementById('wrap-stelline-min');
const ctrlBar   = document.getElementById('controlli-bar');
const wrapEsp   = document.getElementById('wrap-espandi-orbe');
pannC.classList.remove('visibile');
pannA.classList.remove('visibile');
if (isCuspidi) {
pannC.classList.add('visibile');
wrapSt.style.display = 'none';
wrapEsp.style.display = '';        // allargamento orbe utile in cuspidi
} else if (isAstri) {
pannA.classList.add('visibile');
wrapSt.style.display = '';
wrapEsp.style.display = 'none';
} else {
wrapSt.style.display = '';
wrapEsp.style.display = 'none';
}
// Bordo-radius controlli-bar
const hasSub = isCuspidi || isAstri;
ctrlBar.style.borderRadius = hasSub ? '8px 8px 0 0' : '8px';
ctrlBar.style.marginBottom = hasSub ? '0' : '';
// Pannello avanzati mantiene stato aperto/chiuso, aggiorna solo
// la visibilità del gruppo "tolleranza dinamica" e "espandi orbe"
document.getElementById('wrap-tolleranza-dinamica').style.opacity = isCuspidi ? '1' : '0.5';
document.getElementById('wrap-espandi-orbe').style.display = isCuspidi ? '' : 'none';
}
/**
* Applica preset orbe ai campi cuspidi se la modalità è Cuspidi.
*/
function applicaOrbePreset(preset) {
const condizione = document.getElementById('condizione').value;
if (condizione !== CONDIZIONE_CUSPIDI) return; // solo in modalità cuspidi
const presets = {
'stretto':     { tol_gradi: 0, tol_minuti: 15 },
'normale':     { tol_gradi: 1, tol_minuti: 0  },
'largo':       { tol_gradi: 2, tol_minuti: 0  },
'larghissimo': { tol_gradi: 5, tol_minuti: 0  },
};
const p = presets[preset];
if (!p) return;
document.getElementById('cusp-tol-gradi').value  = p.tol_gradi;
document.getElementById('cusp-tol-minuti').value = p.tol_minuti;
}
/**
* Calcola tipo_ricerca effettivo tenendo conto dell'override "importanza".
*/
function getTipoRicercaEffettivo() {
const override = document.getElementById('filt-importanza').value;
if (override === 'solo_hub')    return 'large_medium';
if (override === 'iata_only')   return 'iata_only';
return document.getElementById('tipo-ricerca').value;
}
// ════════════════════════════════════════════════════════════════════════
//  GESTIONE REGOLE ASTRI
// ════════════════════════════════════════════════════════════════════════
function aggiungiRegola() {
const pianeta = document.getElementById('nuovo-astro-select').value;
const casa    = parseInt(document.getElementById('nuova-casa-select').value);
const vuole   = document.getElementById('nuova-condizione-select').value === 'deve';
const modalitaSelect = document.getElementById('nuova-modalita-select');
let modalita = modalitaSelect.value;
if (modalita === 'cuspide' && !USER_FEATURES.astri_in_cuspide) {
alert(SUPPORTER_MESSAGE);
modalitaSelect.value = 'in_casa';
modalita = 'in_casa';
}
const esiste = regoleAstri.some(r => String(r.pianeta) === String(pianeta));
if (esiste) {
alert('Esiste già una regola per ' + ASTRO_NOMI[pianeta] + '. Rimuovila prima di aggiungerne una nuova.');
return;
}
regoleAstri.push({ pianeta: pianeta === 'ASC' ? 'ASC' : parseInt(pianeta), casa, vuole, modalita });
aggiornaListaRegole();
aggiornaSommarioAstri();
}
function rimuoviRegola(index) {
regoleAstri.splice(index, 1);
aggiornaListaRegole();
aggiornaSommarioAstri();
}
function resetTutteRegole() {
if (regoleAstri.length > 0 && confirm('Rimuovere tutte le regole impostate?')) {
regoleAstri = [];
aggiornaListaRegole();
aggiornaSommarioAstri();
}
}
function aggiornaListaRegole() {
const container = document.getElementById('regole-container');
if (regoleAstri.length === 0) {
container.innerHTML = '<div class="regole-vuote">Nessuna regola attiva.</div>';
return;
}
let html = '';
regoleAstri.forEach((r, idx) => {
const pKey   = String(r.pianeta);
const sim    = ASTRO_SIMBOLI[pKey] || '★';
const nome   = ASTRO_NOMI[pKey] || pKey;
const azione = r.vuole ? '✓ VOGLIO in' : '✗ NON VOGLIO in';
const cls    = r.vuole ? 'deve' : 'evita';
const modalitaLabel = r.modalita === 'cuspide' ? 'Cuspide' : 'Casa';
html += `<div class="regola-item ${cls}">
<div class="regola-info">
<span class="astro-simbolo">${sim}</span>
<span class="astro-nome">${nome}</span>
</div>
<div class="regola-azione ${cls}">${azione}</div>
<div class="casa-numero">${modalitaLabel} ${r.casa}</div>
<button class="btn-rimuovi" onclick="rimuoviRegola(${idx})">✕</button>
</div>`;
});
container.innerHTML = html;
}
function aggiornaSommarioAstri() {
const sommario = document.getElementById('astri-sommario');
const tags     = document.getElementById('astri-sommario-tags');
if (regoleAstri.length === 0) { sommario.classList.remove('visibile'); return; }
tags.innerHTML = regoleAstri.map(r => {
const pKey = String(r.pianeta);
const sim  = ASTRO_SIMBOLI[pKey] || '★';
const nome = ASTRO_NOMI[pKey] || pKey;
const cls  = r.vuole ? 'tag-deve' : 'tag-evita';
const modalitaLabel = r.modalita === 'cuspide' ? 'Cuspide' : 'Casa';
const txt  = r.vuole ? `→ ${modalitaLabel} ${r.casa}` : `✗ ${modalitaLabel} ${r.casa}`;
return `<span class="tag-regola ${cls}">${sim} ${nome} ${txt}</span>`;
}).join('');
sommario.classList.add('visibile');
}
function buildAstriInCasaParam() {
return regoleAstri.map(r => ({ pianeta: r.pianeta, casa: r.casa, vuole: r.vuole, modalita: r.modalita || 'in_casa' }));
}
// ════════════════════════════════════════════════════════════════════════
//  HELPERS GENERALI
// ════════════════════════════════════════════════════════════════════════
function getSoggetto() {
const id = parseInt(document.getElementById('sel-soggetto').value);
return soggettiData.find(s => s.id === id) || null;
}
function getMostraEscluse() {
return document.getElementById('mostra-escluse')?.checked ? '1' : '0';
}
function stelleHtml(n) {
n = Math.max(0, Math.min(5, n || 0));
return '<span class="stelle-piene">' + '★'.repeat(n) + '</span>' +
'<span class="stelle-vuote">' + '☆'.repeat(5 - n) + '</span>';
}
function formatDistanza(r) {
const g = r.distanza || 0;
if (g === 0) return '';
if (g < 1) return '~' + Math.round(g * 60) + '′ dal target';
const gi = Math.floor(g);
const mf = Math.round((g - gi) * 60);
return '~' + gi + '°' + (mf ? mf + '′' : '') + ' dal target';
}
function setProgress(perc, label) {
const percentuale = Number(perc);
document.getElementById('progress-fill').style.width = percentuale + '%';
document.getElementById('progress-perc').textContent = percentuale.toFixed(2) + '%';
if (label) document.getElementById('progress-label').textContent = label;
}
/**
* Mostra/nasconde il conteggio delle RS escluse dal filtro di esclusione
* (Sole/Marte in I/VI/XII RS, ASC RS in I/VI/XII natale, Saturno in X RS,
* stellium in qualsiasi casa RS). Non è una delle 34 regole Discepolo:
* è un filtro di visualizzazione aggiuntivo applicato lato server.
*/
function aggiornaInfoEsclusiFiltro(d) {
const wrap = document.getElementById('info-rs-esclusi-wrap');
const val  = document.getElementById('info-rs-esclusi');
if (!wrap || !val) return;
const n = d.totale_esclusi_filtro || 0;
if (n > 0) {
val.textContent = n.toLocaleString();
wrap.style.display = '';
} else {
wrap.style.display = 'none';
}
}
// ════════════════════════════════════════════════════════════════════════
//  AVVIA RICERCA
// ════════════════════════════════════════════════════════════════════════
document.getElementById('btn-cerca').addEventListener('click', avviaRicerca);
function avviaRicerca(espansioneOrbe) {
    const s = getSoggetto();
    if (!s) { alert('Seleziona prima un soggetto.'); return; }

    const tipoLocalita = document.getElementById('tipo-localita').value;
    const nazioneLocalita = document.getElementById('nazione-localita').value;
    if (tipoLocalita === 'localita' && nazioneLocalita === '') {
        alert('Seleziona una nazione per la ricerca delle località.');
        document.getElementById('nazione-localita').focus();
        return;
    }

    if (eventoCorrente) { eventoCorrente.close(); eventoCorrente = null; }

    const condizione   = document.getElementById('condizione').value;
    const modalitaBase = condizione === CONDIZIONE_ASTRI   ? 'astri'   :
                          condizione === CONDIZIONE_CUSPIDI ? 'cuspidi' : 'standard';

    stato.tutti        = [];
    stato.filtroNaz    = '';
    stato.filtroStelle = 0;
    stato.pagina       = 1;
    stato.confronto    = [];
    stato.offsetRicerca = 0;
    stato.analizzatiFinoA = 0;
    stato.ricercaCompletata = true;

    // Applica preset orbe se selezionato (solo cuspidi)
    const orbePreset = document.getElementById('filt-orbe-preset').value;
    if (orbePreset && modalitaBase === 'cuspidi') applicaOrbePreset(orbePreset);

    document.getElementById('progress-area').style.display = 'block';
    document.getElementById('info-rs').style.display       = 'none';
    document.getElementById('risultati-area').innerHTML    = '';
    const espBtnWrap = document.getElementById('btn-espandi-orbe-wrap');
    if (espBtnWrap) espBtnWrap.style.display = 'none';
    setProgress(0, 'Calcolo momento RS...');

    const gridMode = document.getElementById('filt-grid-search').value;

    // Ricerca a Griglia: dispatch verso la variante corrispondente alla
    // condizione selezionata (standard / astri / cuspidi). Tutte e tre
    // sono supportate da api/ricerca_griglia_api.php (parametro modalita).
    if (gridMode !== 'no') {
        if (modalitaBase === 'cuspidi') {
            stato.modalita = 'griglia-cuspidi';
            avviaRicercaGrigliaCuspidi(s, espansioneOrbe || false);
        } else if (modalitaBase === 'astri') {
            stato.modalita = 'griglia-astri';
            avviaRicercaGrigliaAstri(s);
        } else {
            stato.modalita = 'griglia';
            avviaRicercaGriglia(s);
        }
        return;
    }

    stato.modalita = modalitaBase;
    if (modalitaBase === 'cuspidi') {
        avviaRicercaCuspidi(s, espansioneOrbe || false);
    } else if (modalitaBase === 'astri') {
        avviaRicercaAstri(s);
    } else {
        avviaRicercaStandard(s);
    }
}

// ── GRIGLIA GEOMETRICA — STANDARD ───────────────────────────────────────
function avviaRicercaGriglia(s) {
    const step   = document.getElementById('filt-grid-search').value;
    const latMin = document.getElementById('grid-lat-min').value || -60;
    const latMax = document.getElementById('grid-lat-max').value || 60;

    const params = new URLSearchParams({
        g: s.giorno, m: s.mese, a: s.anno,
        ora_gmt: s.ora_gmt, lat: s.lat, lon: s.lon,
        anno:           document.getElementById('anno-rs').value,
        modalita:       'standard',
        condizione:     document.getElementById('condizione').value,
        griglia:        step,
        lat_min:        latMin,
        lat_max:        latMax,
        stelline_min:   document.getElementById('stelline-min-cerca').value,
        mostra_escluse: getMostraEscluse(),
    });
    aggiungiParamsGeografici(params); // lon_min/lon_max fascia oraria = bbox longitudine griglia

    eventoCorrente = new EventSource('api/ricerca_griglia_api.php?' + params.toString());

    eventoCorrente.addEventListener('start', e => {
        const d = JSON.parse(e.data);
        document.getElementById('progress-sub').textContent =
            'Griglia ' + d.step + '° · ' + d.totale.toLocaleString() + ' punti da calcolare · RS: ' + d.rs_gmt;
        document.getElementById('info-rs-gmt').textContent  = d.rs_gmt;
        document.getElementById('info-rs-cond').textContent = 'Griglia ' + d.step + '° — ' + d.condizione;
    });

    eventoCorrente.addEventListener('progress', e => {
        const d = JSON.parse(e.data);
        setProgress(Math.max(1, d.perc),
            'Calcolati ' + d.processed.toLocaleString() + ' / ' + d.totale.toLocaleString() + ' punti · trovati: ' + d.trovati);
    });

    eventoCorrente.addEventListener('result', e => {
        const r = JSON.parse(e.data);
        stato.tutti.push(r);
        document.getElementById('risultati-live').textContent =
            '★'.repeat(r.stelline) + ' Lat ' + r.lat.toFixed(2) + '° Lon ' + r.lon.toFixed(2) + '° — ' + r.val;
    });

    eventoCorrente.addEventListener('done', e => {
        const d = JSON.parse(e.data);
        eventoCorrente.close(); eventoCorrente = null;
        stato.tutti = d.risultati;
console.log('ATL_DOPO_DONE', stato.tutti.find(r => r.icao === 'KATL' || r.iata === 'ATL'));
        document.getElementById('progress-area').style.display = 'none';
        document.getElementById('risultati-live').textContent  = '';
        const infoEl = document.getElementById('info-rs');
        infoEl.style.display = 'flex';
        document.getElementById('info-rs-calcolati').textContent =
            d.totale_calcolati.toLocaleString() + ' (su ' + d.totale_originale.toLocaleString() + ' punti griglia)';
        document.getElementById('info-rs-tempo').textContent = (d.elapsed_ms / 1000).toFixed(1) + 's';
        aggiornaInfoEsclusiFiltro(d);
        stato.pagina = 1;
        renderTabella();
    });

    eventoCorrente.addEventListener('error', e => {
        eventoCorrente.close(); eventoCorrente = null;
        document.getElementById('progress-area').style.display = 'none';
        let msg = 'Errore durante il calcolo a griglia.';
        try { msg = JSON.parse(e.data).message; } catch(x) {}
        document.getElementById('risultati-area').innerHTML =
            '<div class="msg-error-box">❌ ' + msg + '</div>';
    });
}

// ── GRIGLIA GEOMETRICA — ASTRI NELLE CASE ───────────────────────────────
function avviaRicercaGrigliaAstri(s) {
    const regole = buildAstriInCasaParam();
    if (regole.length === 0) {
        alert('Nessuna regola impostata. Aggiungi almeno una regola nel pannello "Astri nelle Case".');
        document.getElementById('progress-area').style.display = 'none';
        return;
    }

    const step   = document.getElementById('filt-grid-search').value;
    const latMin = document.getElementById('grid-lat-min').value || -60;
    const latMax = document.getElementById('grid-lat-max').value || 60;

    const params = new URLSearchParams({
        g: s.giorno, m: s.mese, a: s.anno,
        ora_gmt: s.ora_gmt, lat: s.lat, lon: s.lon,
        anno:           document.getElementById('anno-rs').value,
        modalita:       'astri',
        griglia:        step,
        lat_min:        latMin,
        lat_max:        latMax,
        astri_in_casa:  JSON.stringify(regole),
        stelline_min:   document.getElementById('stelline-min-cerca').value,
        mostra_escluse: getMostraEscluse(),
    });
    aggiungiParamsGeografici(params);

    eventoCorrente = new EventSource('api/ricerca_griglia_api.php?' + params.toString());

    eventoCorrente.addEventListener('start', e => {
        const d = JSON.parse(e.data);
        const descR = regole.map(r => {
            const sim  = ASTRO_SIMBOLI[String(r.pianeta)] || '★';
            const nome = ASTRO_NOMI[String(r.pianeta)] || String(r.pianeta);
            return sim + ' ' + nome + (r.vuole ? ' → Casa ' : ' ✗ Casa ') + r.casa;
        }).join(' | ');
        document.getElementById('progress-sub').textContent =
            'Griglia ' + d.step + '° · ' + d.totale.toLocaleString() + ' punti da calcolare · RS: ' + d.rs_gmt;
        document.getElementById('info-rs-gmt').textContent  = d.rs_gmt;
        document.getElementById('info-rs-cond').textContent = 'Griglia ' + d.step + '° — Astri: ' + descR;
    });

    eventoCorrente.addEventListener('progress', e => {
        const d = JSON.parse(e.data);
        setProgress(Math.max(1, d.perc),
            'Calcolati ' + d.processed.toLocaleString() + ' / ' + d.totale.toLocaleString() + ' punti · trovati: ' + d.trovati);
    });

    eventoCorrente.addEventListener('result', e => {
        const r = JSON.parse(e.data);
        stato.tutti.push(r);
        document.getElementById('risultati-live').textContent =
            '★'.repeat(r.stelline) + ' Lat ' + r.lat.toFixed(2) + '° Lon ' + r.lon.toFixed(2) + '° — ' + r.val;
    });

    eventoCorrente.addEventListener('done', e => {
        const d = JSON.parse(e.data);
        eventoCorrente.close(); eventoCorrente = null;
        stato.tutti = d.risultati;
console.log('ATL_DOPO_DONE', stato.tutti.find(r => r.icao === 'KATL' || r.iata === 'ATL'));
        document.getElementById('progress-area').style.display = 'none';
        document.getElementById('risultati-live').textContent  = '';
        const infoEl = document.getElementById('info-rs');
        infoEl.style.display = 'flex';
        document.getElementById('info-rs-calcolati').textContent =
            d.totale_calcolati.toLocaleString() + ' (su ' + d.totale_originale.toLocaleString() + ' punti griglia)';
        document.getElementById('info-rs-tempo').textContent = (d.elapsed_ms / 1000).toFixed(1) + 's';
        aggiornaInfoEsclusiFiltro(d);
        stato.pagina = 1;
        renderTabella();
    });

    eventoCorrente.addEventListener('error', e => {
        eventoCorrente.close(); eventoCorrente = null;
        document.getElementById('progress-area').style.display = 'none';
        let msg = 'Errore durante il calcolo a griglia.';
        try { msg = JSON.parse(e.data).message; } catch(x) {}
        document.getElementById('risultati-area').innerHTML =
            '<div class="msg-error-box">❌ ' + msg + '</div>';
    });
}

// ── GRIGLIA GEOMETRICA — LONGITUDINE CUSPIDI ────────────────────────────
function avviaRicercaGrigliaCuspidi(s, espansioneOrbe) {
    let tolGradi  = parseInt(document.getElementById('cusp-tol-gradi').value)  || 0;
    let tolMinuti = parseInt(document.getElementById('cusp-tol-minuti').value) || 0;
    if (espansioneOrbe) {
        tolGradi  = Math.min(30, tolGradi  * 2 || 2);
        tolMinuti = Math.min(59, tolMinuti * 2 || 30);
        setProgress(0, '🔍 Espandendo orbe a ±' + tolGradi + '° ' + tolMinuti + '′...');
    }

    const step   = document.getElementById('filt-grid-search').value;
    const latMin = document.getElementById('grid-lat-min').value || -60;
    const latMax = document.getElementById('grid-lat-max').value || 60;

    const params = new URLSearchParams({
        g: s.giorno, m: s.mese, a: s.anno,
        ora_gmt: s.ora_gmt, lat: s.lat, lon: s.lon,
        anno:            document.getElementById('anno-rs').value,
        modalita:        'cuspidi',
        griglia:         step,
        lat_min:         latMin,
        lat_max:         latMax,
        casa:            document.getElementById('cusp-casa').value,
        segno:           document.getElementById('cusp-segno').value,
        gradi:           document.getElementById('cusp-gradi').value,
        tol_gradi:       tolGradi,
        minuti:          document.getElementById('cusp-minuti').value,
        tol_minuti:      tolMinuti,
        mostra_escluse:  getMostraEscluse(),
    });
    aggiungiParamsGeografici(params);
    if (espansioneOrbe) {
        params.set('espansione_orbe', '1');
    }
    stato.ultimiParams = params;

    eventoCorrente = new EventSource('api/ricerca_griglia_api.php?' + params.toString());

    eventoCorrente.addEventListener('start', e => {
        const d = JSON.parse(e.data);
        document.getElementById('progress-sub').textContent =
            'RS: ' + d.rs_gmt + ' · ' + d.target_str + ' · Griglia ' + d.step + '° · ' + d.totale.toLocaleString() + ' punti';
        document.getElementById('info-rs-gmt').textContent  = d.rs_gmt;
        document.getElementById('info-rs-cond').textContent = d.target_str;
    });

    eventoCorrente.addEventListener('progress', e => {
        const d = JSON.parse(e.data);
        setProgress(Math.max(1, d.perc),
            'Calcolati ' + d.processed.toLocaleString() + ' / ' + d.totale.toLocaleString() + ' punti · trovati: ' + d.trovati);
    });

    eventoCorrente.addEventListener('result', e => {
        const r = JSON.parse(e.data);
        stato.tutti.push(r);
        document.getElementById('risultati-live').textContent =
            (SEGNI_SIM[r.segno_num]||'') + ' ' + r.cuspide_str + ' — Lat ' + r.lat.toFixed(2) + '° Lon ' + r.lon.toFixed(2) + '°';
    });

    eventoCorrente.addEventListener('done', e => {
        const d = JSON.parse(e.data);
        eventoCorrente.close(); eventoCorrente = null;
        stato.tutti = d.risultati;
console.log('ATL_DOPO_DONE', stato.tutti.find(r => r.icao === 'KATL' || r.iata === 'ATL'));
        document.getElementById('progress-area').style.display = 'none';
        document.getElementById('risultati-live').textContent  = '';
        const infoEl = document.getElementById('info-rs');
        infoEl.style.display = 'flex';
        document.getElementById('info-rs-calcolati').textContent =
            d.totale_calcolati.toLocaleString() + ' (su ' + d.totale_originale.toLocaleString() + ' punti griglia)';
        document.getElementById('info-rs-tempo').textContent = (d.elapsed_ms / 1000).toFixed(1) + 's';
        aggiornaInfoEsclusiFiltro(d);
        stato.pagina = 1;
        renderTabella();

        if (d.risultati.length === 0 && document.getElementById('filt-espandi-orbe').value === 'si') {
            const tolA = parseInt(document.getElementById('cusp-tol-gradi').value)  || 0;
            const tolB = parseInt(document.getElementById('cusp-tol-minuti').value) || 0;
            if (tolA < 5 || tolB < 59) {
                mostraBottoneEspandi();
                avviaRicerca(true);
            }
        } else if (d.risultati.length === 0) {
            mostraBottoneEspandi();
        }
    });

    eventoCorrente.addEventListener('error', e => {
        eventoCorrente.close(); eventoCorrente = null;
        document.getElementById('progress-area').style.display = 'none';
        let msg = 'Errore durante il calcolo a griglia.';
        try { msg = JSON.parse(e.data).message; } catch(x) {}
        document.getElementById('risultati-area').innerHTML =
            '<div class="msg-error-box">❌ ' + msg + '</div>';
    });
}

// ── STANDARD ──────────────────────────────────────────────────────────────
function avviaRicercaStandard(s) {
const params = new URLSearchParams({
g: s.giorno, m: s.mese, a: s.anno,
ora_gmt: s.ora_gmt, lat: s.lat, lon: s.lon,
anno:            document.getElementById('anno-rs').value,
condizione:      document.getElementById('condizione').value,
tipo_ricerca:    getTipoRicercaEffettivo(),
tipo_localita:   document.getElementById('tipo-localita').value,
nazione_localita:document.getElementById('nazione-localita').value,
numero_localita: document.getElementById('numero-localita').value,
offset_ricerca:  0,
limite_ricerca:  30000,
escludi_militari:document.getElementById('escludi-militari').value,
stelline_min:    document.getElementById('stelline-min-cerca').value,
mostra_escluse: getMostraEscluse(),
});
aggiungiParamsGeografici(params);
stato.ultimiParams = new URLSearchParams(params.toString());
eventoCorrente = new EventSource('api/ricerca_stream_api.php?' + params.toString());
collegaHandlerStandard(eventoCorrente, params);
}
// ── ASTRI NELLE CASE ──────────────────────────────────────────────────────
function avviaRicercaAstri(s) {
const regole = buildAstriInCasaParam();
if (regole.length === 0) {
alert('Nessuna regola impostata. Aggiungi almeno una regola nel pannello "Astri nelle Case".');
document.getElementById('progress-area').style.display = 'none';
return;
}
const params = new URLSearchParams({
g: s.giorno, m: s.mese, a: s.anno,
ora_gmt: s.ora_gmt, lat: s.lat, lon: s.lon,
anno:            document.getElementById('anno-rs').value,
condizione:      'Decima',
tipo_ricerca:    getTipoRicercaEffettivo(),
escludi_militari:document.getElementById('escludi-militari').value,
stelline_min:    document.getElementById('stelline-min-cerca').value,
astri_in_casa:   JSON.stringify(regole),
mostra_escluse: getMostraEscluse(),
});
aggiungiParamsGeografici(params);
stato.ultimiParams = new URLSearchParams(params.toString());
eventoCorrente = new EventSource('api/ricerca_stream_api.php?' + params.toString());
eventoCorrente.addEventListener('start', e => {
const d = JSON.parse(e.data);
const descR = regole.map(r => {
const sim  = ASTRO_SIMBOLI[String(r.pianeta)] || '★';
const nome = ASTRO_NOMI[String(r.pianeta)] || String(r.pianeta);
return sim + ' ' + nome + (r.vuole ? ' → Casa ' : ' ✗ Casa ') + r.casa;
}).join(' | ');
document.getElementById('progress-sub').textContent =
'Deduplicazione ' + d.totale_aeroporti.toLocaleString() + ' aeroporti... RS: ' + d.rs_gmt;
document.getElementById('info-rs-gmt').textContent  = d.rs_gmt;
document.getElementById('info-rs-cond').textContent = 'Astri: ' + descR;
});
collegaHandlerProgressEDone(eventoCorrente);
}
// ── CUSPIDI ───────────────────────────────────────────────────────────────
function avviaRicercaCuspidi(s, espansioneOrbe) {
// Se espansioneOrbe=true raddoppia tolleranza
let tolGradi  = parseInt(document.getElementById('cusp-tol-gradi').value)  || 0;
let tolMinuti = parseInt(document.getElementById('cusp-tol-minuti').value) || 0;
if (espansioneOrbe) {
tolGradi  = Math.min(30, tolGradi  * 2 || 2);
tolMinuti = Math.min(59, tolMinuti * 2 || 30);
setProgress(0, '🔍 Espandendo orbe a ±' + tolGradi + '° ' + tolMinuti + '′...');
}
const params = new URLSearchParams({
g: s.giorno, m: s.mese, a: s.anno,
ora_gmt: s.ora_gmt, lat: s.lat, lon: s.lon,
anno:            document.getElementById('anno-rs').value,
casa:            document.getElementById('cusp-casa').value,
segno:           document.getElementById('cusp-segno').value,
gradi:           document.getElementById('cusp-gradi').value,
tol_gradi:       tolGradi,
minuti:          document.getElementById('cusp-minuti').value,
tol_minuti:      tolMinuti,
tipo_ricerca:    getTipoRicercaEffettivo(),
escludi_militari:document.getElementById('escludi-militari').value,
mostra_escluse: getMostraEscluse(),
});
aggiungiParamsGeografici(params);
if (espansioneOrbe) {
params.set('espansione_orbe', '1');
}
// Salva params per eventuale espansione
stato.ultimiParams = params;
eventoCorrente = new EventSource('api/cuspidi_search_api.php?' + params.toString());
eventoCorrente.addEventListener('start', e => {
const d = JSON.parse(e.data);
document.getElementById('progress-sub').textContent =
'RS: ' + d.rs_gmt + ' · ' + d.target_str + ' · ' + d.totale.toLocaleString() + ' aeroporti';
document.getElementById('info-rs-gmt').textContent  = d.rs_gmt;
document.getElementById('info-rs-cond').textContent = d.target_str;
});
eventoCorrente.addEventListener('progress', e => {
const d = JSON.parse(e.data);
if (d.fase === 'dedup_done') {
document.getElementById('progress-sub').textContent =
'Deduplicati: ' + d.totale.toLocaleString() + ' su ' + d.totale_originale.toLocaleString() + ' aeroporti';
setProgress(1, 'Ricerca cuspide in corso...');
return;
}
setProgress(Math.max(2, d.perc),
'Calcolati ' + d.processed.toLocaleString() + ' / ' + d.totale.toLocaleString() + ' · trovati: ' + d.trovati);
});
eventoCorrente.addEventListener('result', e => {
const r = JSON.parse(e.data);
stato.tutti.push(r);
document.getElementById('risultati-live').textContent =
(SEGNI_SIM[r.segno_num]||'') + ' ' + r.cuspide_str + ' — ' +
(r.iata||r.icao||'—') + ' ' + r.citta + ' (' + r.nazione + ')';
});
eventoCorrente.addEventListener('done', e => {
const d = JSON.parse(e.data);
eventoCorrente.close(); eventoCorrente = null;
stato.tutti = d.risultati;
console.log('ATL_DOPO_DONE', stato.tutti.find(r => r.icao === 'KATL' || r.iata === 'ATL'));
document.getElementById('progress-area').style.display = 'none';
document.getElementById('risultati-live').textContent  = '';
const infoEl = document.getElementById('info-rs');
infoEl.style.display = 'flex';
document.getElementById('info-rs-calcolati').textContent =
d.totale_calcolati.toLocaleString() + ' (su ' + d.totale_originale.toLocaleString() + ' totali)';
document.getElementById('info-rs-tempo').textContent =
(d.elapsed_ms / 1000).toFixed(1) + 's';
aggiornaInfoEsclusiFiltro(d);
stato.pagina = 1;
renderTabella();
// Espandi orbe automaticamente se abilitato e 0 risultati
if (d.risultati.length === 0 && document.getElementById('filt-espandi-orbe').value === 'si') {
const tolA = parseInt(document.getElementById('cusp-tol-gradi').value)  || 0;
const tolB = parseInt(document.getElementById('cusp-tol-minuti').value) || 0;
if (tolA < 5 || tolB < 59) {
// Mostra pulsante espandi manuale anche se auto è off
mostraBottoneEspandi();
// Auto-espansione
avviaRicerca(true);
}
} else if (d.risultati.length === 0) {
mostraBottoneEspandi();
}
});
eventoCorrente.addEventListener('error', e => {
eventoCorrente.close(); eventoCorrente = null;
document.getElementById('progress-area').style.display = 'none';
let msg = 'Errore durante il calcolo.';
try { msg = JSON.parse(e.data).message; } catch(x) {}
document.getElementById('risultati-area').innerHTML =
'<div class="msg-error-box">❌ ' + msg + '</div>';
});
}
function aggiungiRisultatoTopKFrontend(risultati, nuovoRisultato, limite) {
if (risultati.length < limite) { risultati.push(nuovoRisultato); return; }
let stellineMinime = Infinity;
let ultimoIndiceMinimo = null;
risultati.forEach((risultato, indice) => {
const stelline = Number(risultato.stelline || 0);
if (stelline < stellineMinime) { stellineMinime = stelline; ultimoIndiceMinimo = indice; }
else if (stelline === stellineMinime) { ultimoIndiceMinimo = indice; }
});
if (Number(nuovoRisultato.stelline || 0) <= stellineMinime || ultimoIndiceMinimo === null) return;
risultati.splice(ultimoIndiceMinimo, 1);
risultati.push(nuovoRisultato);
}
// ── Handler SSE comune (progress + done) per standard e astri ─────────────
function collegaHandlerProgressEDone(ev, params = null, risultatiPrecedenti = []) {
ev.addEventListener('progress', e => {
const d = JSON.parse(e.data);
const offsetTranche = Number(params?.get('offset_ricerca') || 0);
const processatiCumulativi = Math.min(d.totale, offsetTranche + Number(d.processed || 0));
const percentualeCumulativa = d.totale > 0
? Math.min(100, processatiCumulativi / d.totale * 100)
: 0;
if (d.fase === 'dedup_done') {
setProgress(percentualeCumulativa,
'Calcolate ' + processatiCumulativi.toLocaleString() + ' / ' + d.totale.toLocaleString() + ' località...');
return;
}
setProgress(percentualeCumulativa,
'Calcolate ' + processatiCumulativi.toLocaleString() + ' / ' + d.totale.toLocaleString() + ' località...');
});
ev.addEventListener('result', e => {
const r = JSON.parse(e.data);
stato.tutti.push(r);
document.getElementById('risultati-live').textContent =
'★'.repeat(r.stelline) + ' ' + (r.iata||r.icao) + ' ' + r.citta + ' (' + r.nazione + ') — ' + r.val;
});
ev.addEventListener('done', e => {
const d = JSON.parse(e.data);
ev.close(); eventoCorrente = null;
stato.offsetRicerca = d.offset_ricerca || 0;
stato.analizzatiFinoA = d.analizzati_fino_a || 0;
stato.ricercaCompletata = d.ricerca_completata !== false;
stato.tutti = d.risultati;
if (params && risultatiPrecedenti.length > 0) {
const limite = Number(params.get('numero_localita') || 0);
if (limite > 0) {
stato.tutti = [...risultatiPrecedenti];
d.risultati.forEach(r => aggiungiRisultatoTopKFrontend(stato.tutti, r, limite));
}
}
console.log('ATL_DOPO_DONE', stato.tutti.find(r => r.icao === 'KATL' || r.iata === 'ATL'));
document.getElementById('progress-area').style.display = 'block';
if (stato.ricercaCompletata) {
setProgress(100, 'Ricerca completata');
}
document.getElementById('risultati-live').textContent  = '';
const infoEl = document.getElementById('info-rs');
infoEl.style.display = 'flex';
document.getElementById('info-rs-calcolati').textContent =
d.totale_calcolati.toLocaleString() + ' (su ' + d.totale_originale.toLocaleString() + ' totali)';
document.getElementById('info-rs-tempo').textContent =
(d.elapsed_ms / 1000).toFixed(1) + 's';
aggiornaInfoEsclusiFiltro(d);
stato.pagina = 1;
renderTabella();
if (params && !stato.ricercaCompletata) {
mostraBottoneProseguiRicerca(params);
}
});
ev.addEventListener('error', e => {
ev.close(); eventoCorrente = null;
document.getElementById('progress-area').style.display = 'none';
let msg = 'Errore durante il calcolo.';
try { msg = JSON.parse(e.data).message; } catch(x) {}
document.getElementById('risultati-area').innerHTML =
'<div class="msg-error-box">❌ ' + msg + '</div>';
});
}
function avviaTrancheSuccessiva(params) {
const wrap = document.getElementById('btn-prosegui-ricerca-wrap');
if (wrap) wrap.remove();
document.getElementById('progress-area').style.display = 'block';
const prossimiParams = new URLSearchParams(params.toString());
prossimiParams.set('offset_ricerca', stato.analizzatiFinoA);
eventoCorrente = new EventSource('api/ricerca_stream_api.php?' + prossimiParams.toString());
collegaHandlerStandard(eventoCorrente, prossimiParams);
}
function mostraBottoneProseguiRicerca(params) {
const precedente = document.getElementById('btn-prosegui-ricerca-wrap');
if (precedente) precedente.remove();
const area = document.getElementById('risultati-area');
const wrap = document.createElement('div');
wrap.id = 'btn-prosegui-ricerca-wrap';
wrap.className = 'espandi-wrap';
const limite = Number(params.get('limite_ricerca') || 30000);
const inizio = stato.analizzatiFinoA + 1;
const fine = stato.analizzatiFinoA + limite;
const paramsSalvati = new URLSearchParams(params.toString());
const bottone = document.createElement('button');
bottone.type = 'button';
bottone.textContent = '▶ Prosegui la Ricerca';
bottone.style.cssText =
'background:#1976D2;color:white;border:none;border-radius:4px;' +
'padding:8px 20px;font-size:13px;cursor:pointer;';
bottone.addEventListener('click', () => avviaTrancheSuccessiva(paramsSalvati));
wrap.appendChild(bottone);
area.prepend(wrap);
}
function collegaHandlerStandard(ev, params) {
ev.addEventListener('start', e => {
const d = JSON.parse(e.data);
document.getElementById('progress-sub').textContent =
'Deduplicazione ' + d.totale_aeroporti.toLocaleString() + ' aeroporti... RS: ' + d.rs_gmt;
document.getElementById('info-rs-gmt').textContent  = d.rs_gmt;
document.getElementById('info-rs-cond').textContent = d.condizione;
});
collegaHandlerProgressEDone(ev, params, [...stato.tutti]);
}
// ── Pulsante espandi orbe (fallback manuale) ──────────────────────────────
function mostraBottoneEspandi() {
const area = document.getElementById('risultati-area');
const precedente = document.getElementById('btn-espandi-orbe-wrap');
if (precedente) precedente.remove();

const wrap = document.createElement('div');
wrap.id = 'btn-espandi-orbe-wrap';
wrap.className = 'espandi-wrap';

if (!USER_FEATURES.dynamic_orb) {
wrap.innerHTML = `
<div style="color:#888;font-size:12px;margin-bottom:8px">
Nessun risultato trovato con la tolleranza attuale.
</div>
<div class="msg-error-box">${SUPPORTER_MESSAGE}</div>`;
area.prepend(wrap);
return;
}

wrap.innerHTML = `
<div style="color:#888;font-size:12px;margin-bottom:8px">
Nessun risultato trovato con la tolleranza attuale.
</div>
<button onclick="avviaRicerca(true)"
style="background:#FF9800;color:white;border:none;border-radius:4px;
padding:8px 20px;font-size:13px;cursor:pointer;">
🔍 Espandi orbe e riprova
</button>`;
area.prepend(wrap);
}
// ════════════════════════════════════════════════════════════════════════
//  RENDER TABELLE
// ════════════════════════════════════════════════════════════════════════
function renderTabella() {
    if (stato.modalita === 'cuspidi') renderTabellaCuspidi();
    else if (stato.modalita === 'griglia' || stato.modalita === 'griglia-astri') renderTabellaGriglia();
    else if (stato.modalita === 'griglia-cuspidi') renderTabellaGrigliaCuspidi();
    else renderTabellaStandard();
}

function renderTabellaGriglia() {
    let ris = [...stato.tutti];

console.log('ATL_POS', ris.find(r => r.icao === 'KATL' || r.iata === 'ATL'));
    const totale    = ris.length;
    const totPagine = Math.max(1, Math.ceil(totale / stato.perPagina));
    const pagina    = Math.min(stato.pagina, totPagine);
    const offset    = (pagina - 1) * stato.perPagina;
    const pagRis    = ris.slice(offset, offset + stato.perPagina);
console.log('ATL_RENDER', { indice: ris.findIndex(r => r.icao === 'KATL' || r.iata === 'ATL'), pagina: pagina, offset: offset, presenteNellaPagina: pagRis.some(r => r.icao === 'KATL' || r.iata === 'ATL'), totale: ris.length });

    const ppOpt = [25,50,100].map(n =>
        `<option value="${n}" ${stato.perPagina===n?'selected':''}>${n}</option>`).join('');

    const sogg = getSoggetto();
    const anno = document.getElementById('anno-rs').value;
    const cond = document.getElementById('condizione').value;

    const righe = pagRis.map((r, idx) => {
        const luogoStr = 'Lat ' + r.lat.toFixed(2) + '° Lon ' + r.lon.toFixed(2) + '°';
        const rsUrl = 'rs.php?id=' + (sogg?.id||'') +
            '&lat_rs=' + r.lat + '&lon_rs=' + r.lon +
            '&luogo_rs=' + encodeURIComponent(luogoStr) +
            '&anno=' + anno + '&condizione=' + encodeURIComponent(cond);
        const cls = r.stelline>=5?'stelle-5':r.stelline>=4?'stelle-4':'';

        const escluso  = r.esclusa_filtro;
        const rigaCls  = escluso ? (cls + ' riga-esclusa').trim() : cls;
        const badgeEsclusa = escluso
            ? `<span class="badge-esclusa" title="${(r.motivi_esclusione||[]).join(' · ').replace(/"/g,'&quot;')}">⚠️ esclusa</span>`
            : '';

        const hasVeti  = r.veti && r.veti.length > 0;
        const panelId  = 'gvp-' + idx;
        const badgeVeti = hasVeti
            ? `<span class="badge-veto-count" onclick="toggleVetiPanel('${panelId}')">⛔ ${r.veti.length} veto${r.veti.length>1?'i':''}</span>`
            : '';
        const vetiRighe = hasVeti
            ? r.veti.map(v => `<div class="veto-panel-riga"><span class="veto-ico">⛔</span><span>${v.replace(/</g,'&lt;')}</span></div>`).join('')
            : '';
        const pannelloVeti = hasVeti
            ? `<div class="veto-panel" id="${panelId}">` +
                `<div class="veto-panel-titolo">⚖️ Perché questo punto non è valido</div>` +
                vetiRighe +
                `<div class="veto-panel-fonte">Regole scuola Ciro Discepolo — Astrologia Attiva</div>` +
              `</div>`
            : '';

        const hasVicinanza = r.vicinanza_gradi !== null && r.vicinanza_gradi !== undefined;
        const vicinanzaHtml = hasVicinanza
            ? `<div class="dist-badge">✨ ${r.vicinanza_pianeta} a ${r.vicinanza_gradi}° dalla cuspide ${r.vicinanza_casa}</div>`
            : '';

        return `<tr class="${rigaCls}">
            <td style="color:#999;font-size:11px">${offset+idx+1}</td>
            <td>${stelleHtml(r.stelline)}</td>
            <td><div class="td-val-wrap"><div><span class="val-badge">${r.val||'—'}</span>${badgeEsclusa}${badgeVeti}</div>${vicinanzaHtml}${pannelloVeti}</div></td>
            <td style="color:#888">${r.lat.toFixed(3)}</td>
            <td style="color:#888">${r.lon.toFixed(3)}</td>
            <td><a href="${rsUrl}" class="btn-usa">↺ Usa</a></td>
        </tr>`;
    }).join('');

    document.getElementById('risultati-area').innerHTML = `
        <div class="filtri-bar">
            <div class="form-group"><label>Per pagina</label>
                <select onchange="setPerPagina(this.value)">${ppOpt}</select>
            </div>
            <div class="totale-label">${totale.toLocaleString()} punti validi · pag. ${pagina} / ${totPagine}</div>
        </div>
        <div class="tabella-risultati-wrap">
            <table class="tabella-risultati">
                <thead><tr>
                    <th>#</th><th>Stelle</th><th>VAL</th><th>Lat</th><th>Lon</th><th>RS</th>
                </tr></thead>
                <tbody>${righe||'<tr><td colspan="6" class="empty-results">Nessun punto trovato.</td></tr>'}</tbody>
            </table>
        </div>
        ${buildPaginazione(pagina, totPagine)}`;
}

function renderTabellaGrigliaCuspidi() {
    let ris = [...stato.tutti];

console.log('ATL_POS', ris.find(r => r.icao === 'KATL' || r.iata === 'ATL'));
    const totale    = ris.length;
    const totPagine = Math.max(1, Math.ceil(totale / stato.perPagina));
    const pagina    = Math.min(stato.pagina, totPagine);
    const offset    = (pagina - 1) * stato.perPagina;
    const pagRis    = ris.slice(offset, offset + stato.perPagina);
console.log('ATL_RENDER', { indice: ris.findIndex(r => r.icao === 'KATL' || r.iata === 'ATL'), pagina: pagina, offset: offset, presenteNellaPagina: pagRis.some(r => r.icao === 'KATL' || r.iata === 'ATL'), totale: ris.length });

    const ppOpt = [25,50,100].map(n =>
        `<option value="${n}" ${stato.perPagina===n?'selected':''}>${n}</option>`).join('');

    const sogg = getSoggetto();
    const anno = document.getElementById('anno-rs').value;
    const casa = document.getElementById('cusp-casa').value;

    const righe = pagRis.map((r, idx) => {
        const luogoStr = 'Lat ' + r.lat.toFixed(2) + '° Lon ' + r.lon.toFixed(2) + '°';
        const rsUrl = 'rs.php?id=' + (sogg?.id||'') +
            '&lat_rs=' + r.lat + '&lon_rs=' + r.lon +
            '&luogo_rs=' + encodeURIComponent(luogoStr) +
            '&anno=' + anno;
        const esatta   = r.distanza === 0;
        const badgeCls = esatta ? 'cuspide-badge esatta' : 'cuspide-badge';
        const distStr  = formatDistanza(r);
        const escluso  = r.esclusa_filtro;
        const rigaCls  = escluso ? 'riga-esclusa' : (esatta ? 'top-match' : '');
        const badgeEsclusa = escluso
            ? `<span class="badge-esclusa" title="${(r.motivi_esclusione||[]).join(' · ').replace(/"/g,'&quot;')}">⚠️ esclusa</span>`
            : '';
        const segSim = SEGNI_SIM[r.segno_num] || '';

        return `<tr class="${rigaCls}">
            <td style="color:#999;font-size:11px">${offset+idx+1}</td>
            <td>
                <span class="${badgeCls}">${segSim} ${r.cuspide_str}</span>${badgeEsclusa}
                ${distStr ? '<div class="dist-badge">'+distStr+'</div>' : ''}
            </td>
            <td style="color:#888">${r.lat.toFixed(3)}</td>
            <td style="color:#888">${r.lon.toFixed(3)}</td>
            <td><a href="${rsUrl}" class="btn-usa">↺ RS</a></td>
        </tr>`;
    }).join('');

    document.getElementById('risultati-area').innerHTML = `
        <div class="filtri-bar">
            <div class="form-group"><label>Per pagina</label>
                <select onchange="setPerPagina(this.value)">${ppOpt}</select>
            </div>
            <div class="totale-label">${totale.toLocaleString()} punti trovati · pag. ${pagina} / ${totPagine}</div>
        </div>
        <div class="tabella-risultati-wrap">
            <table class="tabella-risultati">
                <thead><tr>
                    <th>#</th><th>Casa ${casa} (cuspide RS)</th><th>Lat</th><th>Lon</th><th>RS</th>
                </tr></thead>
                <tbody>${righe||'<tr><td colspan="5" class="empty-results">Nessun punto trovato. Prova ad aumentare la tolleranza.</td></tr>'}</tbody>
            </table>
        </div>
        ${buildPaginazione(pagina, totPagine)}`;
}

function renderTabellaStandard() {
let ris = [...stato.tutti];
if (stato.filtroNaz)    ris = ris.filter(r => (r.nazione||'').toUpperCase() === stato.filtroNaz.toUpperCase());
if (stato.filtroStelle > 0) ris = ris.filter(r => r.stelline >= stato.filtroStelle);
console.log('ATL_POS', ris.find(r => r.icao === 'KATL' || r.iata === 'ATL'));
const totale    = ris.length;
const confrontoToolbar = stato.confronto.length >= 2
? `<div class="confronto-toolbar">
<button type="button" id="btn-confronta-selezioni">
Confronta le ${stato.confronto.length} selezioni
</button>
<span style="font-size:12px;color:#666">
${stato.confronto.length}/${COMPARATOR_LIMIT} selezionate
</span>
</div>`
: '';
const totPagine = Math.max(1, Math.ceil(totale / stato.perPagina));
const pagina    = Math.min(stato.pagina, totPagine);
const offset    = (pagina - 1) * stato.perPagina;
const pagRis    = ris.slice(offset, offset + stato.perPagina);
console.log('ATL_RENDER', { indice: ris.findIndex(r => r.icao === 'KATL' || r.iata === 'ATL'), pagina: pagina, offset: offset, presenteNellaPagina: pagRis.some(r => r.icao === 'KATL' || r.iata === 'ATL'), totale: ris.length });
const nazioniSet = [...new Set(stato.tutti.map(r=>r.nazione).filter(Boolean))].sort();
const nazioniOpt = nazioniSet.map(n =>
`<option value="${n}" ${stato.filtroNaz===n?'selected':''}>${n}</option>`).join('');
const stelleOpt = [0,1,2,3,4,5].map(n =>
`<option value="${n}" ${stato.filtroStelle===n?'selected':''}>${n===0?'Tutte':'≥ '+'★'.repeat(n)}</option>`).join('');
const ppOpt = [25,50,100].map(n =>
`<option value="${n}" ${stato.perPagina===n?'selected':''}>${n}</option>`).join('');
const sogg = getSoggetto();
const anno = document.getElementById('anno-rs').value;
const cond = stato.modalita === 'astri' ? 'Decima' : document.getElementById('condizione').value;
const righe = pagRis.map((r, idx) => {
const isLocalita = r.origine_punto === 'localita';
const nomePunto = r.nome || r.citta || '—';
const luogoRs = isLocalita
? [nomePunto, r.nazione].filter(Boolean).join(', ')
: [r.citta || nomePunto, r.nazione].filter(Boolean).join(', ');
const tipoPunto = r.tipo
? String(r.tipo).replace(/_/g, ' ')
: (isLocalita ? 'località' : 'aeroporto');
const popolazione = isLocalita && Number(r.popolazione) > 0
? `<div style="color:#999;font-size:10px">${Number(r.popolazione).toLocaleString('it-IT')} abitanti</div>`
: '';
const codicePunto = isLocalita
? ((r.iata || r.icao)
    ? `<strong>${r.iata||'—'}</strong><br><span style="color:#999;font-size:10px">${r.icao||'aeroporto associato'}</span>`
    : `<strong>Località</strong><br><span style="color:#999;font-size:10px">${tipoPunto}</span>`)
: `<strong>${r.iata||'—'}</strong><br><span style="color:#999;font-size:10px">${r.icao||tipoPunto}</span>`;
const confrontoKey = [
r.lat,
r.lon,
r.iata || '',
r.icao || '',
r.nome || ''
].join('|');
const rsUrl = 'rs.php?id=' + (sogg?.id||'') +
'&lat_rs=' + r.lat + '&lon_rs=' + r.lon +
'&luogo_rs=' + encodeURIComponent(luogoRs) +
'&anno=' + anno + '&condizione=' + encodeURIComponent(cond);
const cls     = r.stelline>=5?'stelle-5':r.stelline>=4?'stelle-4':'';
const hasVeti = r.veti && r.veti.length > 0;
const valCls  = hasVeti ? 'val-badge veto' : 'val-badge';
const escluso  = r.esclusa_filtro;
const rigaCls  = escluso ? (cls + ' riga-esclusa').trim() : cls;
const badgeEsclusa = escluso
? `<span class="badge-esclusa" title="${(r.motivi_esclusione||[]).join(' · ').replace(/"/g,'&quot;')}">⚠️ esclusa</span>`
: '';
// Badge veti cliccabile: mostra/nasconde il pannello inline con
// la spiegazione esatta del rifiuto secondo le 34 regole di Discepolo.
const panelId   = 'vp-' + idx;
const badgeVeti = hasVeti
? `<span class="badge-veto-count" onclick="toggleVetiPanel('${panelId}')">`
+ `⛔ ${r.veti.length} veto${r.veti.length > 1 ? 'i' : ''}</span>`
: '';
const vetiRighe = hasVeti
? r.veti.map(v => `<div class="veto-panel-riga"><span class="veto-ico">⛔</span><span>${v.replace(/</g,'&lt;')}</span></div>`).join('')
: '';
const pannelloVeti = hasVeti
? `<div class="veto-panel" id="${panelId}">` +
`<div class="veto-panel-titolo">⚖️ Perché questa RS non è valida</div>` +
vetiRighe +
`<div class="veto-panel-fonte">Regole scuola Ciro Discepolo — Astrologia Attiva</div>` +
`</div>`
: '';
return `<tr class="${rigaCls}">
<td style="color:#999;font-size:11px">${offset+idx+1}</td>
<td>${stelleHtml(r.stelline)}</td>
<td><div class="td-val-wrap"><div><span class="${valCls}">${r.val||'—'}</span>${badgeEsclusa}${badgeVeti}</div>${pannelloVeti}</div></td>
<td>${codicePunto}</td>
<td style="max-width:200px"><strong>${nomePunto}</strong>${popolazione}</td>
<td>${r.citta||''}</td>
<td>${r.nazione||''}</td>
<td style="color:#888">${parseFloat(r.lat||0).toFixed(2)}</td>
<td style="color:#888">${parseFloat(r.lon||0).toFixed(2)}</td>
<td><a href="${rsUrl}" class="btn-usa">↺ Usa</a></td>
<td style="text-align:center">
<input
type="checkbox"
class="confronto-checkbox"
data-confronto-key="${confrontoKey}"
${stato.confronto.includes(confrontoKey) ? 'checked' : ''}>
</td>
</tr>`;
}).join('');
document.getElementById('risultati-area').innerHTML = `
<div class="filtri-bar">
<div class="form-group"><label>Nazione</label>
<select onchange="setFiltroNaz(this.value)">
<option value="">Tutte</option>${nazioniOpt}
</select>
</div>
<div class="form-group"><label>Stelline min.</label>
<select onchange="setFiltroStelle(this.value)">${stelleOpt}</select>
</div>
<div class="form-group"><label>Per pagina</label>
<select onchange="setPerPagina(this.value)">${ppOpt}</select>
</div>
<div class="totale-label">${totale.toLocaleString()} risultati · pag. ${pagina} / ${totPagine}</div>
</div>
${confrontoToolbar}
<div class="tabella-risultati-wrap">
<table class="tabella-risultati">
<thead><tr>
<th>#</th><th>Stelle</th><th>VAL</th>
<th>Codice / Tipo</th><th>Punto geografico</th>
<th>Città</th><th>Naz.</th><th>Lat</th><th>Lon</th><th>RS</th><th>Confronta</th>
</tr></thead>
<tbody>${righe||'<tr><td colspan="11" class="empty-results">Nessun risultato.</td></tr>'}</tbody>
</table>
</div>
${buildPaginazione(pagina, totPagine)}`;
}
function renderTabellaCuspidi() {
let ris = [...stato.tutti];
if (stato.filtroNaz) ris = ris.filter(r => (r.nazione||'').toUpperCase() === stato.filtroNaz.toUpperCase());
console.log('ATL_POS', ris.find(r => r.icao === 'KATL' || r.iata === 'ATL'));
const totale    = ris.length;
const totPagine = Math.max(1, Math.ceil(totale / stato.perPagina));
const pagina    = Math.min(stato.pagina, totPagine);
const offset    = (pagina - 1) * stato.perPagina;
const pagRis    = ris.slice(offset, offset + stato.perPagina);
console.log('ATL_RENDER', { indice: ris.findIndex(r => r.icao === 'KATL' || r.iata === 'ATL'), pagina: pagina, offset: offset, presenteNellaPagina: pagRis.some(r => r.icao === 'KATL' || r.iata === 'ATL'), totale: ris.length });
const nazioniSet = [...new Set(stato.tutti.map(r=>r.nazione).filter(Boolean))].sort();
const nazioniOpt = nazioniSet.map(n =>
`<option value="${n}" ${stato.filtroNaz===n?'selected':''}>${n}</option>`).join('');
const ppOpt = [25,50,100].map(n =>
`<option value="${n}" ${stato.perPagina===n?'selected':''}>${n}</option>`).join('');
const sogg = getSoggetto();
const anno = document.getElementById('anno-rs').value;
const casa = document.getElementById('cusp-casa').value;
const righe = pagRis.map((r, idx) => {
const rsUrl = 'rs.php?id=' + (sogg?.id||'') +
'&lat_rs=' + r.lat + '&lon_rs=' + r.lon +
'&luogo_rs=' + encodeURIComponent((r.citta||'')+', '+(r.nazione||'')) +
'&anno=' + anno;
const esatta  = r.distanza === 0;
const badgeCls = esatta ? 'cuspide-badge esatta' : 'cuspide-badge';
const distStr  = formatDistanza(r);
const escluso  = r.esclusa_filtro;
const rigaCls  = escluso ? 'riga-esclusa' : (esatta ? 'top-match' : '');
const badgeEsclusa = escluso
? `<span class="badge-esclusa" title="${(r.motivi_esclusione||[]).join(' · ').replace(/"/g,'&quot;')}">⚠️ esclusa</span>`
: '';
const segSim   = SEGNI_SIM[r.segno_num] || '';
return `<tr class="${rigaCls}">
<td style="color:#999;font-size:11px">${offset+idx+1}</td>
<td>
<span class="${badgeCls}">${segSim} ${r.cuspide_str}</span>${badgeEsclusa}
${distStr ? '<div class="dist-badge">'+distStr+'</div>' : ''}
</td>
<td><strong>${r.iata||'—'}</strong><br><span style="color:#999;font-size:10px">${r.icao||''}</span></td>
<td style="max-width:200px">${r.nome||''}</td>
<td>${r.citta||''}</td>
<td>${r.nazione||''}</td>
<td style="color:#888;font-size:11px">${parseFloat(r.lat||0).toFixed(3)}</td>
<td style="color:#888;font-size:11px">${parseFloat(r.lon||0).toFixed(3)}</td>
<td><a href="${rsUrl}" class="btn-usa">↺ RS</a></td>
</tr>`;
}).join('');
document.getElementById('risultati-area').innerHTML = `
<div class="filtri-bar">
<div class="form-group"><label>Nazione</label>
<select onchange="setFiltroNaz(this.value)">
<option value="">Tutte</option>${nazioniOpt}
</select>
</div>
<div class="form-group"><label>Per pagina</label>
<select onchange="setPerPagina(this.value)">${ppOpt}</select>
</div>
<div class="totale-label">${totale.toLocaleString()} risultati · pag. ${pagina} / ${totPagine}</div>
</div>
<div class="tabella-risultati-wrap">
<table class="tabella-risultati">
<thead><tr>
<th>#</th><th>Casa ${casa} (cuspide RS)</th>
<th>IATA / ICAO</th><th>Aeroporto</th>
<th>Città</th><th>Naz.</th><th>Lat</th><th>Lon</th><th>RS</th>
</tr></thead>
<tbody>${righe||'<tr><td colspan="9" class="empty-results">Nessun risultato. Prova ad aumentare la tolleranza.</td></tr>'}</tbody>
</table>
</div>
${buildPaginazione(pagina, totPagine)}`;
}
// ── Comparator ────────────────────────────────────────────────────────────────

function getRisultatiConfronto() {
    return stato.confronto.map(key => {
        return stato.tutti.find(r => [
            r.lat,
            r.lon,
            r.iata || '',
            r.icao || ''
        ].join('|') === key);
    }).filter(Boolean);
}

// ── Filtri Risultati ─────────────────────────────────────────────────────────────

function setFiltroNaz(v)    { stato.filtroNaz = v; stato.pagina = 1; renderTabella(); }
function setFiltroStelle(v) { stato.filtroStelle = parseInt(v)||0; stato.pagina = 1; renderTabella(); }

// ── Init ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
applicaRestrizioniInterfaccia();
aggiornaListaRegole();
aggiornaSommarioAstri();
onCondizioneChange(document.getElementById('condizione').value);
caricaNazioniLocalita();
onTipoLocalitaChange(document.getElementById('tipo-localita').value);
ripristinaStatoRicerca();

document.getElementById('risultati-area').addEventListener('change', function(event) {
    const checkbox = event.target.closest('.confronto-checkbox');
    if (!checkbox) return;

    const key = checkbox.dataset.confrontoKey;

    if (checkbox.checked) {
        if (stato.confronto.includes(key)) return;

        if (stato.confronto.length >= COMPARATOR_LIMIT) {
            checkbox.checked = false;
            alert(COMPARATOR_LIMIT_MESSAGE);
            return;
        }

        stato.confronto.push(key);
    } else {
        stato.confronto = stato.confronto.filter(item => item !== key);
    }

    renderTabella();
});

document.getElementById('risultati-area').addEventListener('click', function(event) {
    const linkRs = event.target.closest('a.btn-usa');
    if (linkRs) {
        salvaStatoRicerca();
        return;
    }

    const button = event.target.closest('#btn-confronta-selezioni');
    if (!button) return;

    const risultati = getRisultatiConfronto();
    if (risultati.length < 2) {
        alert('Seleziona almeno 2 RS o rilocazioni da confrontare.');
        return;
    }

    const soggetto = getSoggetto();
    const modalitaConfronto = stato.modalita === 'astri' ? 'astri' : 'standard';
    const payload = {
        soggetto,
        anno: document.getElementById('anno-rs').value,
        modalita: modalitaConfronto,
        condizione: modalitaConfronto === 'astri'
            ? 'Decima'
            : document.getElementById('condizione').value,
        astri_in_casa: modalitaConfronto === 'astri'
            ? buildAstriInCasaParam()
            : [],
        risultati
    };

    sessionStorage.setItem('astroDssConfrontoRs', JSON.stringify(payload));
    window.location.href = 'compare_rs.php';
});

// Preset orbe sincronizza con pannello cuspidi in tempo reale
document.getElementById('filt-orbe-preset').addEventListener('change', function() {
applicaOrbePreset(this.value);
});

// Mostra/nasconde bbox lat quando si attiva la griglia
document.getElementById('filt-grid-search').addEventListener('change', function() {
    if (this.value !== 'no' && !USER_FEATURES.grid_search) {
        alert(SUPPORTER_MESSAGE);
        this.value = 'no';
    }

    document.getElementById('wrap-griglia-bbox').style.display =
        this.value === 'no' ? 'none' : 'flex';
});

document.getElementById('filt-espandi-orbe').addEventListener('change', function() {
    if (this.value === 'si' && !USER_FEATURES.dynamic_orb) {
        alert(SUPPORTER_MESSAGE);
        this.value = 'no';
    }
});
});
// ── Pannello veti inline ──────────────────────────────────────────────────
// Apre/chiude il pannello di spiegazione dei veti Discepolo sotto la riga
// della tabella risultati. Ogni click chiude gli altri pannelli aperti
// (uno solo aperto alla volta).
function toggleVetiPanel(id) {
const target = document.getElementById(id);
if (!target) return;
const eraAperto = target.classList.contains('aperto');
// Chiudi tutti i pannelli aperti
document.querySelectorAll('.veto-panel.aperto').forEach(p => p.classList.remove('aperto'));
// Apri quello cliccato solo se era chiuso
if (!eraAperto) target.classList.add('aperto');
}
</script>
<script src="js/ricerca_astri.js"></script>
<script src="js/ricerca_filtri_geo.js"></script>
<script src="js/ricerca_paginazione.js"></script>
</body>
</html>
