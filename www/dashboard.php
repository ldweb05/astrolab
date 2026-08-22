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
<div class="flex items-center gap-4">
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
<div class="flex items-center gap-4">
<button class="text-on-surface-variant hover:text-on-surface hover:bg-surface-container p-2 rounded-full transition-colors">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">notifications</span>
</button>
<button class="text-on-surface-variant hover:text-on-surface hover:bg-surface-container p-2 rounded-full transition-colors">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">settings</span>
</button>
<div class="h-8 w-8 rounded-full bg-surface-variant overflow-hidden border border-outline-variant ml-2"></div>
</div>
</header>
<main class="p-container-padding-mobile md:p-container-padding-desktop min-h-[calc(100vh-64px)] flex items-center justify-center pb-32 md:pb-12 bg-white">
<div class="bg-white rounded-[2rem] w-full max-w-4xl p-8 flex flex-col gap-8 shadow-xl border border-outline-variant/30">
<!-- 1. Tabs Row -->
<div class="flex gap-6 border-b border-outline-variant">
<button class="px-4 py-3 border-b-2 border-primary text-primary font-label-caps text-label-caps transition-colors -mb-[1px]">
                    TEMA
                </button>
<a href="ricerca.php?tipo=localita" class="px-4 py-3 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface hover:border-outline font-label-caps text-label-caps transition-colors -mb-[1px]">
                    LOCALITÀ
                </a>
<a href="ricerca.php" class="px-4 py-3 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface hover:border-outline font-label-caps text-label-caps transition-colors -mb-[1px]">
                    AEROPORTI
                </a>
</div>
<!-- 2. Personal Info Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="flex flex-col gap-2">
<label class="font-label-caps text-label-caps text-on-surface-variant">Nome Cognome</label>
<input class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-shadow" readonly type="text" value="Mario Rossi"/>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-caps text-label-caps text-on-surface-variant">Data di Nascita</label>
<div class="relative">
<input class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full pr-10 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-shadow" readonly type="text" value="15 Maggio 1980"/>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">calendar_today</span>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-caps text-label-caps text-on-surface-variant">Ora di Nascita</label>
<div class="relative">
<input class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full pr-10 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-shadow" readonly type="text" value="14:30"/>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">schedule</span>
</div>
</div>
</div>
<!-- 3. Search Row -->
<div class="flex flex-col md:flex-row gap-6 items-end bg-surface-container p-6 rounded-2xl border border-outline-variant relative overflow-hidden">
<div class="flex flex-col gap-2 flex-1 relative z-10 w-full">
<label class="font-label-caps text-label-caps text-on-surface-variant">Scelta Anno</label>
<div class="relative">
<select class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full appearance-none pr-10 cursor-pointer transition-colors hover:border-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
<option>2023</option>
<option selected>2024</option>
<option>2025</option>
<option>2026</option>
</select>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">arrow_drop_down</span>
</div>
</div>
<div class="flex flex-col gap-2 flex-1 relative z-10 w-full">
<label class="font-label-caps text-label-caps text-on-surface-variant">Tipo Analisi</label>
<div class="relative">
<select class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 font-body-lg text-body-lg text-on-surface w-full appearance-none pr-10 cursor-pointer transition-colors hover:border-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
<option selected>Rivoluzione Solare</option>
<option>Rivoluzione Lunare</option>
</select>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">arrow_drop_down</span>
</div>
</div>
<button class="w-full md:w-auto px-8 py-3 bg-primary text-on-primary hover:bg-primary/90 rounded-lg font-title-md text-title-md transition-all shadow-md shadow-primary/20 relative z-10 flex items-center justify-center gap-2 group">
<span class="material-symbols-outlined group-hover:scale-110 transition-transform">search</span>
                    Cerca
                </button>
</div>
<!-- 4. Action Buttons Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<a href="<?= htmlspecialchars($transitiUrl) ?>" class="py-4 border border-primary/50 rounded-xl text-primary hover:bg-primary/5 font-title-md text-title-md transition-colors flex items-center justify-center gap-3 bg-white shadow-sm">
<span class="material-symbols-outlined">sync_alt</span>
                    Transiti
                </a>
<a href="<?= htmlspecialchars($rilocazioneUrl) ?>" class="py-4 border border-outline-variant rounded-xl text-on-surface hover:bg-surface-container hover:border-outline font-title-md text-title-md transition-colors flex items-center justify-center gap-3 bg-white shadow-sm">
<span class="material-symbols-outlined">map</span>
                    Rilocazione
                </a>
</div>
<!-- 5. Bottom Split Area (Charts) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
<div class="bg-surface-container rounded-2xl p-6 border border-outline-variant flex flex-col gap-4 relative overflow-hidden group">
<div class="flex justify-between items-center relative z-10">
<h3 class="font-headline-lg-mobile text-headline-lg-mobile text-primary tracking-tight">CIELO NATALE</h3>
<span class="font-label-caps text-label-caps text-on-surface-variant bg-white border border-outline-variant px-2 py-1 rounded">Base</span>
</div>
<div class="relative w-full aspect-square bg-white rounded-xl border border-outline-variant flex items-center justify-center overflow-hidden z-10 p-4">
<div class="w-full h-full rounded-full border border-outline-variant relative flex items-center justify-center group-hover:border-primary/50 transition-colors duration-700">
<div class="w-[80%] h-[80%] rounded-full border border-surface-container absolute"></div>
<div class="w-[60%] h-[60%] rounded-full border-dashed border-outline-variant absolute animate-[spin_120s_linear_infinite]"></div>
<div class="absolute inset-0 flex items-center justify-center opacity-40 group-hover:opacity-80 transition-opacity duration-500">
<span class="material-symbols-outlined text-[120px] text-primary/50 font-thin">all_inclusive</span>
</div>
<div class="absolute top-4 right-1/4 bg-white px-2 py-1 rounded text-[10px] font-label-caps tracking-widest text-secondary border border-secondary/50 shadow-sm">SUN</div>
<div class="absolute bottom-1/4 left-4 bg-white px-2 py-1 rounded text-[10px] font-label-caps tracking-widest text-primary border border-primary/50 shadow-sm">MOON</div>
<div class="absolute top-1/3 left-8 bg-white px-2 py-1 rounded text-[10px] font-label-caps tracking-widest text-error border border-error/50 shadow-sm">MARS</div>
</div>
</div>
</div>
<div class="bg-surface-container rounded-2xl p-6 border border-outline-variant flex flex-col gap-4 relative overflow-hidden group">
<div class="flex justify-between items-center relative z-10">
<h3 class="font-headline-lg-mobile text-headline-lg-mobile text-secondary tracking-tight">RS per residenza</h3>
<span class="font-label-caps text-label-caps text-on-surface-variant bg-white border border-outline-variant px-2 py-1 rounded">Roma, IT</span>
</div>
<div class="relative w-full aspect-square bg-white rounded-xl border border-outline-variant flex items-center justify-center overflow-hidden z-10 p-4">
<div class="w-full h-full rounded-full border border-outline-variant relative flex items-center justify-center group-hover:border-secondary/50 transition-colors duration-700">
<div class="w-[80%] h-[80%] rounded-full border border-surface-container absolute"></div>
<div class="w-[60%] h-[60%] rounded-full border-dashed border-outline-variant absolute animate-[spin_90s_linear_infinite_reverse]"></div>
<div class="absolute inset-0 flex items-center justify-center opacity-40 group-hover:opacity-80 transition-opacity duration-500">
<span class="material-symbols-outlined text-[120px] text-secondary/50 font-thin">blur_circular</span>
</div>
<div class="absolute top-8 left-1/4 bg-white px-2 py-1 rounded text-[10px] font-label-caps tracking-widest text-tertiary border border-tertiary/50 shadow-sm">VENUS</div>
<div class="absolute bottom-1/3 right-8 bg-white px-2 py-1 rounded text-[10px] font-label-caps tracking-widest text-primary border border-primary/50 shadow-sm">JUPITER</div>
</div>
</div>
</div>
</div>
</div>
</main>
</body>
</html>
