<?php
require_once __DIR__ . '/includes/bootstrap.php';
session_start();
require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();

$isAdmin        = $auth->isAdmin();
$username       = $auth->getCurrentUsername();
$soggettoAttivo = $auth->getSoggettoAttivo();
$soggettoNome   = $auth->getSoggettoNome();

// Stessa convenzione di _navUrl() in header_nav.php: ?id= solo se c'è un soggetto attivo
$transitiUrl    = $soggettoAttivo > 0 ? 'transiti.php?id=' . (int)$soggettoAttivo : 'transiti.php';
$rilocazioneUrl = $soggettoAttivo > 0 ? 'rilocazione.php?id=' . (int)$soggettoAttivo : 'rilocazione.php';
$temaUrl        = $soggettoAttivo > 0 ? 'tema.php?id=' . (int)$soggettoAttivo : 'tema.php';
$rsmUrl         = $soggettoAttivo > 0 ? 'rs.php?id=' . (int)$soggettoAttivo : 'rs.php';

// Elenco soggetti dell'astrologo (stessa logica di RicercaPageData.php)
$userId = $auth->getCurrentUserId();
if ($isAdmin) {
    $dashSoggetti = $pdo->query("SELECT id, nome, data_nascita, ora_nascita, latitudine, longitudine, residenza_latitudine, residenza_longitudine, residenza_luogo FROM soggetti ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmtDashSoggetti = $pdo->prepare("SELECT id, nome, data_nascita, ora_nascita, latitudine, longitudine, residenza_latitudine, residenza_longitudine, residenza_luogo FROM soggetti WHERE utente_id = ? ORDER BY nome");
    $stmtDashSoggetti->execute([$userId]);
    $dashSoggetti = $stmtDashSoggetti->fetchAll(PDO::FETCH_ASSOC);
}
$dashSoggettoUnicoId = count($dashSoggetti) === 1 ? (int)$dashSoggetti[0]['id'] : 0;

// Modale impostazioni: cambio password (tutti) + foto profilo (solo Supporter/admin)
$hasFotoProfilo = $auth->hasFeature('foto_profilo');
if (empty($_SESSION['dash_settings_csrf'])) {
    $_SESSION['dash_settings_csrf'] = bin2hex(random_bytes(32));
}
$dashSettingsCsrf = $_SESSION['dash_settings_csrf'];

// Mappa id -> {data, ora} per riempire i campi via JS quando c'è più di un soggetto
// (stesso formato di tema.php: d/m/Y e ora locale, non GMT)
$dashSoggettiDatiJs = [];
foreach ($dashSoggetti as $ds) {
    $resLat = $ds['residenza_latitudine']  ?: ($ds['latitudine']  ?? null);
    $resLon = $ds['residenza_longitudine'] ?: ($ds['longitudine'] ?? null);
    $dashSoggettiDatiJs[(int)$ds['id']] = [
        'data' => $ds['data_nascita'] ? date('d/m/Y', strtotime($ds['data_nascita'])) : '',
        'ora'  => $ds['ora_nascita'] ? substr($ds['ora_nascita'], 0, 5) : '',
        'lat'  => $resLat !== null ? (float)$resLat : null,
        'lon'  => $resLon !== null ? (float)$resLon : null,
        'luogo'=> $ds['residenza_luogo'] ?: null,
    ];
}

// Range anni per la ricerca RS/RL: 1960 -> anno corrente + 7
$annoCorrente = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>AstroLab Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Eb+Garamond:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        "colors": {
                "on-surface": "#3a302a",
                "surface-container": "#f2ece4",
                "surface-container-lowest": "#ffffff",
                "on-tertiary-container": "#3a2020",
                "tertiary-fixed": "#fce0e0",
                "on-background": "#3a302a",
                "surface-bright": "#faf5ee",
                "surface-container-highest": "#e6e0d6",
                "primary-fixed": "#fbe8d8",
                "on-error-container": "#7a1a10",
                "inverse-on-surface": "#faf5ee",
                "surface-tint": "#c2652a",
                "on-tertiary": "#ffffff",
                "on-surface-variant": "#605850",
                "on-primary": "#ffffff",
                "primary-container": "#e08850",
                "surface-variant": "#ece6dc",
                "inverse-surface": "#3a302a",
                "tertiary": "#8c3c3c",
                "secondary-fixed": "#eae2da",
                "outline-variant": "#d8d0c8",
                "error": "#c0392b",
                "surface-container-low": "#f6f0e8",
                "on-secondary-fixed-variant": "#504840",
                "primary-fixed-dim": "#f0a878",
                "surface-container-high": "#ece6dc",
                "on-secondary-container": "#605850",
                "on-secondary-fixed": "#2a2420",
                "tertiary-fixed-dim": "#e8a0a0",
                "background": "#faf5ee",
                "surface-dim": "#dcd6cc",
                "on-primary-fixed-variant": "#8a4518",
                "on-primary-container": "#fbe8d8",
                "on-secondary": "#ffffff",
                "on-error": "#ffffff",
                "surface": "#faf5ee",
                "error-container": "#fce4e0",
                "on-tertiary-fixed": "#2e1515",
                "outline": "#9a9088",
                "on-primary-fixed": "#401a08",
                "secondary": "#78706a",
                "on-tertiary-fixed-variant": "#6e3030",
                "inverse-primary": "#f0a878",
                "tertiary-container": "#d47070",
                "secondary-fixed-dim": "#cec6be",
                "primary": "#c2652a",
                "secondary-container": "#eae2da"
        },
        "borderRadius": {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
        },
        "spacing": {
                "container-padding-mobile": "16px",
                "base": "8px",
                "stack-lg": "48px",
                "stack-md": "24px",
                "stack-sm": "12px",
                "gutter": "24px",
                "container-padding-desktop": "32px"
        },
        "fontFamily": {
                "headline": ["Eb Garamond"],
                "display": ["Eb Garamond"],
                "body": ["Manrope"],
                "label": ["Manrope"],
                "title-md": ["Manrope"],
                "body-lg": ["Manrope"],
                "label-caps": ["Manrope"],
                "headline-lg-mobile": ["Eb Garamond"],
                "display-lg": ["Eb Garamond"]
        },
        "fontSize": {
                "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.1em", "fontWeight": "600", "textTransform": "uppercase"}],
                "title-md": ["20px", {"lineHeight": "28px", "fontWeight": "500"}],
                "headline-lg-mobile": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}]
        }
},
    },
  }
</script>
<style>
        body {
            background: #ffffff;
            min-height: 100vh;
        }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.05); }
        ::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.15); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, 0.25); }
    </style>
</head>
<body class="text-on-surface font-body-lg overflow-x-hidden pt-16 bg-surface">
<!-- TopAppBar -->
<header class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-xl border-b border-outline-variant shadow-sm flex justify-between items-center px-container-desktop h-16 transition-all duration-300 ease-in-out">
<div class="flex items-center gap-4 pl-2">
<button class="md:hidden text-on-surface-variant hover:text-on-surface transition-colors">
<span class="material-symbols-outlined">menu</span>
</button>
<h1 class="font-display-lg text-display-lg font-bold text-primary tracking-tighter">AstroLab</h1>
</div>
<nav class="hidden md:flex items-center gap-8">
<a class="text-primary border-b-2 border-primary pb-1 hover:bg-surface-container font-title-md text-[16px] leading-6 font-medium" href="#">Utenti</a>
<div class="relative group inline-flex items-center h-full">
<button type="button" class="text-on-surface-variant hover:bg-surface-container font-title-md text-[16px] leading-6 font-medium px-1 py-2 flex items-center gap-1">
HELP <span class="material-symbols-outlined text-sm">expand_more</span>
</button>
<div class="hidden group-hover:block absolute top-full left-0 min-w-[250px] bg-white border border-outline-variant rounded-lg shadow-sm py-1 z-50">
<a href="34_regole.html" target="_blank" class="block px-4 py-2 text-sm whitespace-nowrap text-on-surface-variant hover:bg-surface-container hover:text-on-surface">1. Le 34 Regole</a>
<a href="help_account.php" target="_blank" class="block px-4 py-2 text-sm whitespace-nowrap text-on-surface-variant hover:bg-surface-container hover:text-on-surface">2. Introduzione e Account</a>
<a href="help_soggetti.php" target="_blank" class="block px-4 py-2 text-sm whitespace-nowrap text-on-surface-variant hover:bg-surface-container hover:text-on-surface">3. Gestione Soggetti</a>
<a href="help_calcoli.php" target="_blank" class="block px-4 py-2 text-sm whitespace-nowrap text-on-surface-variant hover:bg-surface-container hover:text-on-surface">4. Calcoli e Analisi</a>
<a href="help_ricerca.php" target="_blank" class="block px-4 py-2 text-sm whitespace-nowrap text-on-surface-variant hover:bg-surface-container hover:text-on-surface">5. Ricerca Geografica</a>
<a href="help_report.php" target="_blank" class="block px-4 py-2 text-sm whitespace-nowrap text-on-surface-variant hover:bg-surface-container hover:text-on-surface">6. Report e Stampa</a>
<a href="help_comparatore.php" target="_blank" class="block px-4 py-2 text-sm whitespace-nowrap text-on-surface-variant hover:bg-surface-container hover:text-on-surface">7. Comparatore e DSS</a>
<a href="help_interfaccia.php" target="_blank" class="block px-4 py-2 text-sm whitespace-nowrap text-on-surface-variant hover:bg-surface-container hover:text-on-surface">8. Interfaccia e Visualizzazione</a>
<a href="help_faq.php" target="_blank" class="block px-4 py-2 text-sm whitespace-nowrap text-on-surface-variant hover:bg-surface-container hover:text-on-surface">9. FAQ e Limiti</a>
</div>
</div>
<span class="text-on-surface-variant font-title-md text-[16px] leading-6 font-medium">&#128100; <?= htmlspecialchars($username) ?></span>
<?php if ($soggettoNome): ?>
<span class="text-on-surface-variant font-title-md text-[16px] leading-6 font-medium" title="Soggetto attivo — vai a Soggetti per cambiarlo">&#11088; <?= htmlspecialchars($soggettoNome) ?></span>
<?php endif; ?>
<a class="text-on-surface-variant hover:text-on-surface transition-colors hover:bg-surface-container font-title-md text-[16px] leading-6 font-medium" href="#">|</a>
<a class="text-on-surface-variant hover:text-on-surface transition-colors hover:bg-surface-container font-title-md text-[16px] leading-6 font-medium" href="cambia_password.php">&#128273; Password</a>
<a class="text-on-surface-variant hover:text-on-surface transition-colors hover:bg-surface-container font-title-md text-[16px] leading-6 font-medium" href="logout.php">Esci</a>
</nav>
<div class="flex items-center gap-4 pr-2">
<button class="text-on-surface-variant hover:text-on-surface hover:bg-surface-container p-2 rounded-full transition-colors">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">notifications</span>
</button>
<button id="dash-btn-settings" class="text-on-surface-variant hover:text-on-surface hover:bg-surface-container p-2 rounded-full transition-colors">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">settings</span>
</button>
<div class="h-10 w-10 rounded-full bg-surface-variant overflow-hidden border border-outline-variant ml-2"></div>
</div>
</header>
<main class="p-container-padding-mobile md:p-container-padding-desktop min-h-[calc(100vh-64px)] flex items-center justify-center pb-32 md:pb-12 bg-white">
<div class="bg-white rounded-[2rem] w-full max-w-3xl p-8 flex flex-col gap-8 shadow-xl border border-outline-variant/30">
<!-- 1. Tabs Row -->
<div class="flex gap-6 border-b border-outline-variant">
<a id="dash-link-tema" href="<?= htmlspecialchars($temaUrl) ?>" class="px-4 py-3 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface hover:border-outline font-label-caps text-label-caps transition-colors -mb-[1px]">
                    TEMA
                </a>
<a id="dash-link-rsm" href="<?= htmlspecialchars($rsmUrl) ?>" class="px-4 py-3 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface hover:border-outline font-label-caps text-label-caps transition-colors -mb-[1px]">
                    RSM
                </a>
<a href="ricerca.php?tipo=localita" class="px-4 py-3 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface hover:border-outline font-label-caps text-label-caps transition-colors -mb-[1px]">
                    LOCALITÀ
                </a>
<a href="ricerca.php" class="px-4 py-3 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface hover:border-outline font-label-caps text-label-caps transition-colors -mb-[1px]">
                    AEROPORTI
                </a>
</div>
<!-- 2. Personal Info Row -->
<div class="grid grid-cols-1 md:grid-cols-[1.8fr_1fr_0.8fr] gap-6">
<div class="flex flex-col gap-2">
<label class="font-label-caps text-label-caps text-on-surface-variant">Nome Cognome</label>
<?php if (count($dashSoggetti) === 1): ?>
<input class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-shadow" readonly type="text" value="<?= htmlspecialchars($dashSoggetti[0]['nome']) ?>"/>
<?php elseif (count($dashSoggetti) > 1): ?>
<select id="dash-soggetto" onchange="aggiornaSoggettoSelezionato(this.value)" class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full appearance-none cursor-pointer transition-colors hover:border-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Seleziona Soggetto</option>
<?php foreach ($dashSoggetti as $ds): ?>
<option value="<?= (int)$ds['id'] ?>"><?= htmlspecialchars($ds['nome']) ?></option>
<?php endforeach; ?>
</select>
<?php else: ?>
<input class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface-variant w-full" readonly type="text" value="Nessun soggetto salvato"/>
<?php endif; ?>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-caps text-label-caps text-on-surface-variant">Data di Nascita</label>
<div class="relative">
<input id="dash-data-nascita" class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full pr-10 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-shadow" readonly type="text" value="<?= count($dashSoggetti) === 1 ? htmlspecialchars($dashSoggettiDatiJs[$dashSoggettoUnicoId]['data']) : '' ?>"/>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">calendar_today</span>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-caps text-label-caps text-on-surface-variant whitespace-nowrap">Ora di Nascita <span class="text-[11px] normal-case font-normal" style="color:orangered">(Local)</span></label>
<div class="relative">
<input id="dash-ora-nascita" class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full pr-10 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-shadow" readonly type="text" value="<?= count($dashSoggetti) === 1 ? htmlspecialchars($dashSoggettiDatiJs[$dashSoggettoUnicoId]['ora']) : '' ?>"/>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">schedule</span>
</div>
</div>
</div>
<!-- 3. Search Row -->
<div class="flex flex-col md:flex-row gap-6 items-end bg-surface-container p-6 rounded-2xl border border-outline-variant relative overflow-hidden">
<div class="flex flex-col gap-2 w-full md:w-32 shrink-0 relative z-10">
<label class="font-label-caps text-label-caps text-on-surface-variant">Scelta Anno</label>
<div class="relative">
<select id="dash-anno" class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full appearance-none pr-10 cursor-pointer transition-colors hover:border-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
<?php for ($y = 1960; $y <= $annoCorrente + 7; $y++): ?>
<option value="<?= $y ?>" <?= $y === $annoCorrente ? 'selected' : '' ?>><?= $y ?></option>
<?php endfor; ?>
</select>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">arrow_drop_down</span>
</div>
</div>
<div class="flex flex-col gap-2 flex-1 relative z-10 w-full">
<label class="font-label-caps text-label-caps text-on-surface-variant">Tipo Analisi</label>
<div class="relative">
<select id="dash-tipo-analisi" disabled class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full appearance-none pr-10 cursor-pointer transition-colors hover:border-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed">
<option selected>Rivoluzione Solare</option>
<option>Rivoluzione Lunare</option>
</select>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">arrow_drop_down</span>
</div>
</div>
<button type="button" onclick="eseguiCercaDashboard()" class="w-full md:w-auto px-8 py-3 bg-primary text-on-primary hover:bg-primary/90 rounded-lg font-title-md text-title-md transition-all shadow-md shadow-primary/20 relative z-10 flex items-center justify-center gap-2 group">
<span class="material-symbols-outlined group-hover:scale-110 transition-transform">search</span>
                    Cerca
                </button>
</div>
<!-- 4. Action Buttons Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<a id="dash-link-transiti" href="<?= htmlspecialchars($transitiUrl) ?>" class="py-4 border border-primary/50 rounded-xl text-primary hover:bg-primary/5 font-title-md text-title-md transition-colors flex items-center justify-center gap-3 bg-white shadow-sm">
<span class="material-symbols-outlined">sync_alt</span>
                    Transiti
                </a>
<a id="dash-link-rilocazione" href="<?= htmlspecialchars($rilocazioneUrl) ?>" class="py-4 border border-outline-variant rounded-xl text-on-surface hover:bg-surface-container hover:border-outline font-title-md text-title-md transition-colors flex items-center justify-center gap-3 bg-white shadow-sm">
<span class="material-symbols-outlined">map</span>
                    Rilocazione
                </a>
</div>
<!-- 5. Mappa decorativa residenza (3:1, marker celeste centrato) -->
<div id="dash-mappa-wrap" class="hidden w-full aspect-[3/1] rounded-2xl overflow-hidden border border-outline-variant">
<div id="dash-mappa" class="w-full h-full"></div>
</div>

</div>
</main>
<script>
const DASH_LINK_BASES = {
    'dash-link-tema': 'tema.php',
    'dash-link-rsm': 'rs.php',
    'dash-link-transiti': 'transiti.php',
    'dash-link-rilocazione': 'rilocazione.php'
};
let dashSoggettoSelezionatoId = <?= (int)$dashSoggettoUnicoId ?>;
const DASH_SOGGETTI_DATI = <?= json_encode($dashSoggettiDatiJs, JSON_FORCE_OBJECT) ?>;

function aggiornaSoggettoSelezionato(id) {
    dashSoggettoSelezionatoId = parseInt(id, 10) || 0;
    Object.keys(DASH_LINK_BASES).forEach(function (elId) {
        const el = document.getElementById(elId);
        if (!el) return;
        const base = DASH_LINK_BASES[elId];
        el.href = dashSoggettoSelezionatoId > 0 ? (base + '?id=' + dashSoggettoSelezionatoId) : base;
    });
    const tipoAnalisi = document.getElementById('dash-tipo-analisi');
    if (tipoAnalisi) { tipoAnalisi.disabled = dashSoggettoSelezionatoId <= 0; }

    const datiCampo = document.getElementById('dash-data-nascita');
    const oraCampo = document.getElementById('dash-ora-nascita');
    const dati = DASH_SOGGETTI_DATI[dashSoggettoSelezionatoId];
    if (datiCampo) { datiCampo.value = dati ? dati.data : ''; }
    if (oraCampo) { oraCampo.value = dati ? dati.ora : ''; }
    aggiornaMappaResidenza(dati);
}

function eseguiCercaDashboard() {
    const anno = document.getElementById('dash-anno').value;
    const tipo = document.getElementById('dash-tipo-analisi').value;
    const pagina = (tipo === 'Rivoluzione Lunare') ? 'rl.php' : 'rs.php';
    let url = pagina + '?anno=' + encodeURIComponent(anno);
    if (dashSoggettoSelezionatoId > 0) { url += '&id=' + dashSoggettoSelezionatoId; }
    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', function () {
    aggiornaSoggettoSelezionato(dashSoggettoSelezionatoId);
});
</script>
<!-- Modale Impostazioni: cambio password + foto profilo -->
<div id="dash-modale-overlay" class="hidden fixed inset-0 bg-black/40 z-[999] flex items-center justify-center p-4">
<div class="bg-white rounded-2xl w-full max-w-md p-7 flex flex-col gap-6 shadow-xl">
<div class="flex justify-between items-center">
<h2 class="font-headline-lg-mobile text-headline-lg-mobile text-primary">Impostazioni</h2>
<button type="button" onclick="chiudiModaleImpostazioni()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container">
<span class="material-symbols-outlined">close</span>
</button>
</div>

<!-- Sezione cambio password (in arrivo: collegamento funzionale) -->
<div class="flex flex-col gap-3">
<h3 class="font-title-md text-title-md text-on-surface">🔑 Cambia Password</h3>
<p class="text-sm text-on-surface-variant">Modulo in costruzione.</p>
</div>

<div class="border-t border-outline-variant/60"></div>

<!-- Sezione foto profilo -->
<div class="flex flex-col gap-3">
<h3 class="font-title-md text-title-md text-on-surface">🖼️ Foto Profilo</h3>
<?php if ($hasFotoProfilo): ?>
<p class="text-sm text-on-surface-variant">Modulo in costruzione (piano Supporter).</p>
<?php else: ?>
<p class="text-sm text-on-surface-variant">Disponibile solo per il piano <span class="text-primary font-medium">Supporter</span>.</p>
<?php endif; ?>
</div>
</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function apriModaleImpostazioni() {
    document.getElementById('dash-modale-overlay').classList.remove('hidden');
}
function chiudiModaleImpostazioni() {
    document.getElementById('dash-modale-overlay').classList.add('hidden');
}
document.addEventListener('DOMContentLoaded', function () {
    const btnSettings = document.getElementById('dash-btn-settings');
    if (btnSettings) { btnSettings.addEventListener('click', apriModaleImpostazioni); }
});

let dashLeafletMap = null;
let dashLeafletMarker = null;

function aggiornaMappaResidenza(dati) {
    const wrap = document.getElementById('dash-mappa-wrap');
    if (!dati || dati.lat === null || dati.lon === null) {
        wrap.classList.add('hidden');
        return;
    }
    wrap.classList.remove('hidden');
    if (!dashLeafletMap) {
        dashLeafletMap = L.map('dash-mappa', {
            zoomControl: true,
            dragging: true,
            scrollWheelZoom: true,
            doubleClickZoom: true,
            boxZoom: true,
            keyboard: true,
            touchZoom: true
        }).setView([dati.lat, dati.lon], 9);
        L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(dashLeafletMap);
        dashLeafletMarker = L.circleMarker([dati.lat, dati.lon], {
            radius: 10,
            color: '#0284c7',
            weight: 2,
            fillColor: '#38bdf8',
            fillOpacity: 0.9
        }).addTo(dashLeafletMap);
    } else {
        dashLeafletMap.setView([dati.lat, dati.lon], 9);
        dashLeafletMarker.setLatLng([dati.lat, dati.lon]);
    }
    setTimeout(function () {
        dashLeafletMap.invalidateSize();
        dashLeafletMap.setView([dati.lat, dati.lon], 9);
    }, 100);
}
</script>
</body>
</html>
