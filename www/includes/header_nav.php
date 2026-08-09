<?php
/**
 * header_nav.php — Header navigazione comune a tutte le pagine protette
 *
 * Richiede che siano definiti:
 *   $auth, $isAdmin, $username, $soggettoNome
 *   $paginaAttiva → 'soggetti' | 'tema' | 'rs' | 'rl' | 'rilocazione' | 'ricerca' | 'admin'
 *
 * Il soggetto attivo viene propagato nei link del nav tramite ?id=
 * così la navigazione tra Tema / RS / Ricerca mantiene il soggetto corrente.
 * Il soggetto cambia SOLO tornando alla pagina Soggetti (index.php).
 *
 * Il gruppo "Rivoluzioni" (RS + RL + Rilocazione) è un menu a tendina CSS puro:
 * hover sul trigger → dropdown con le tre voci. Il trigger si illumina come
 * voce attiva se una delle tre pagine figlie è quella corrente.
 */

// Determina l'ID soggetto da propagare nei link:
// 1. Priorità a ?id= nella URL corrente (stiamo già guardando quel soggetto)
// 2. Fallback al soggetto attivo in sessione
$_navSoggettoId = intval($_GET['id'] ?? 0);
if (!$_navSoggettoId) {
    $_navSoggettoId = $auth->getSoggettoAttivo() ?? 0;
}

// Aggiorna la sessione se stiamo navigando con un ?id= esplicito
// (così il nome nel header è corretto anche se la sessione è vecchia)
if (isset($_GET['id']) && intval($_GET['id']) > 0) {
    $auth->setSoggettoAttivo(intval($_GET['id']));
    $soggettoNome = $auth->getSoggettoNome(); // rinfreschiamo per il display
}

// Helper: aggiunge ?id= a un URL se abbiamo un soggetto
function _navUrl(string $base, int $soggettoId, array $extra = []): string {
    if ($soggettoId <= 0) return $base;
    $params = array_merge(['id' => $soggettoId], $extra);
    return $base . '?' . http_build_query($params);
}

// Il trigger del dropdown è "attivo" se siamo in una delle tre pagine figlie
$_riviActive = in_array($paginaAttiva ?? '', ['rs', 'rl', 'rilocazione']);
?>
<style>
/* ── Dropdown nav "Rivoluzioni" ────────────────────────────────────────── */
.nav-dropdown {
    position: relative;
    display: inline-flex;
    align-items: center;
}

/* Trigger (stesso stile di nav a) */
.nav-dropdown-trigger {
    color: #A8B8D8;
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 4px;
    font-size: 13px;
    letter-spacing: 0.05em;
    transition: all 0.2s;
    cursor: default;
    user-select: none;
    display: flex;
    align-items: center;
    gap: 5px;
    height: 100%;
}
.nav-dropdown-trigger:hover,
.nav-dropdown-trigger.active {
    background: rgba(255,255,255,0.15);
    color: white;
}

/* Freccia ▾ */
.nav-dropdown-trigger::after {
    content: '▾';
    font-size: 10px;
    opacity: 0.7;
}

/* Menu a tendina */
.nav-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    min-width: 180px;
    background: #1E2E5A;
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 6px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.35);
    z-index: 9000;
    padding: 4px 0;
    /* piccolo gap invisibile per non perdere il hover passando dal trigger al menu */
    margin-top: -2px;
}

.nav-dropdown:hover .nav-dropdown-menu,
.nav-dropdown-menu:hover {
    display: block;
}

.nav-dropdown-menu a {
    display: block;
    color: #A8B8D8;
    text-decoration: none;
    padding: 8px 18px;
    font-size: 13px;
    letter-spacing: 0.04em;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
.nav-dropdown-menu a:hover,
.nav-dropdown-menu a.active {
    background: rgba(255,255,255,0.12);
    color: white;
}
/* Separatore visivo tra le voci */
.nav-dropdown-menu a + a {
    border-top: 1px solid rgba(255,255,255,0.06);
}


/* ── Trigger Aiuto (stesso colore della stellina utente) ─────────── */
.help-trigger {
    color: #D4C9A8 !important;
}
.help-trigger:hover {
    color: white !important;
}

/* ── Help Dropdown Menu ─────────────────────────────────────────── */
.help-dropdown {
    position: relative;
    display: inline-block;
}
.help-dropdown-content {
    display: none;
    position: absolute;
    background: #1E2E5A;
    min-width: 280px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 6px;
    z-index: 10001;
    top: 100%;
    left: 0;
    margin-top: 8px;
    padding: 8px 0;
}
.help-dropdown:hover .help-dropdown-content,
.help-dropdown.active .help-dropdown-content {
    display: block;
}
.help-dropdown-item {
    color: #A8B8D8;
    padding: 10px 20px;
    text-decoration: none;
    display: block;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.2s;
}
.help-dropdown-item:hover {
    background: rgba(255,255,255,0.1);
    color: white;
}
/* ── Help Modal ──────────────────────────────────────────────────── */
.help-modal-overlay {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(2px);
}
.help-modal-box {
    background: #1E2E5A;
    margin: 8vh auto;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 8px;
    width: 90%;
    max-width: 700px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    color: #A8B8D8;
    font-size: 14px;
    line-height: 1.6;
}
.help-modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.05);
    border-radius: 8px 8px 0 0;
}
.help-modal-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: white;
}
.help-modal-close {
    color: #A8B8D8;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
    padding: 0 5px;
}
.help-modal-close:hover { color: white; }
.help-modal-body {
    padding: 20px;
    overflow-y: auto;
}
.help-modal-body h3 {
    color: white;
    margin-top: 0;
    font-size: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 8px;
}

</style>

<header>
    <div class="header-inner">
        <h1><a href="index.php" class="header-logo">☉ AstroLab</a></h1>
        <button class="nav-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="main-nav"
                aria-label="Apri menu di navigazione">☰</button>

        <nav id="main-nav" class="main-nav">
            <!-- Soggetti: va SEMPRE a index.php senza ?id= per permettere la scelta -->
            <a href="index.php"
               <?= ($paginaAttiva??'') === 'soggetti' ? 'class="active"' : '' ?>>Soggetti</a>

            <!-- Tema Natale -->
            <a href="<?= _navUrl('tema.php', $_navSoggettoId) ?>"
               <?= ($paginaAttiva??'') === 'tema' ? 'class="active"' : '' ?>>Tema Natale</a>

            <!-- ── Dropdown: Rivoluzioni & Rilocazione ──────────────── -->
            <div class="nav-dropdown">
                <button type="button"
                        class="nav-dropdown-trigger<?= $_riviActive ? ' active' : '' ?>"
                        aria-expanded="false">
                    Rivoluzioni
                </button>
                <div class="nav-dropdown-menu">
                    <a href="<?= _navUrl('rs.php', $_navSoggettoId) ?>"
                       <?= ($paginaAttiva??'') === 'rs' ? 'class="active"' : '' ?>>
                        ↺ Riv. Solare
                    </a>
                    <a href="<?= _navUrl('rl.php', $_navSoggettoId) ?>"
                       <?= ($paginaAttiva??'') === 'rl' ? 'class="active"' : '' ?>>
                        ☽ Riv. Lunare
                    </a>
                    <a href="<?= _navUrl('rilocazione.php', $_navSoggettoId) ?>"
                       <?= ($paginaAttiva??'') === 'rilocazione' ? 'class="active"' : '' ?>>
                        ☿ Rilocazione
                    </a>
                </div>
            </div>
            <!-- ── Fine dropdown ────────────────────────────────────── -->

            <!-- Ricerca Località -->
            <a href="<?= _navUrl('ricerca.php', $_navSoggettoId) ?>"
               <?= ($paginaAttiva??'') === 'ricerca' ? 'class="active"' : '' ?>>Ricerca Località</a>

            <!-- Aiuto Dropdown -->
            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-trigger help-trigger" aria-expanded="false">
                    ? Aiuto
                </button>
                <div class="nav-dropdown-menu">
                    <a href="help_account.php" target="_blank">1. Introduzione e Account</a>
                    <a href="help_soggetti.php" target="_blank">2. Gestione Soggetti</a>
                    <a href="help_calcoli.php" target="_blank">3. Calcoli e Analisi</a>
                    <a href="help_ricerca.php" target="_blank">4. Ricerca Geografica</a>
                    <a href="help_report.php" target="_blank">5. Report e Stampa</a>
                    <a href="help_comparatore.php" target="_blank">6. Comparatore e DSS</a>
                    <a href="help_interfaccia.php" target="_blank">7. Interfaccia e Visualizzazione</a>
                    <a href="help_faq.php" target="_blank">8. FAQ e Limiti</a>
                </div>
            </div>
            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-trigger help-trigger" aria-expanded="false">
                    ? Aiuto
                </button>
                <div class="nav-dropdown-menu">
                    <a href="#" onclick="window.openHelpSection && window.openHelpSection(1); return false;">1. Introduzione e Account</a>
                    <a href="#" onclick="window.openHelpSection && window.openHelpSection(2); return false;">2. Gestione Soggetti</a>
                    <a href="#" onclick="window.openHelpSection && window.openHelpSection(3); return false;">3. Calcoli e Analisi</a>
                    <a href="#" onclick="window.openHelpSection && window.openHelpSection(4); return false;">4. Ricerca Geografica</a>
                    <a href="#" onclick="window.openHelpSection && window.openHelpSection(5); return false;">5. Report e Stampa</a>
                    <a href="#" onclick="window.openHelpSection && window.openHelpSection(6); return false;">6. Comparatore e DSS</a>
                    <a href="#" onclick="window.openHelpSection && window.openHelpSection(7); return false;">7. Interfaccia e Visualizzazione</a>
                    <a href="#" onclick="window.openHelpSection && window.openHelpSection(8); return false;">8. FAQ e Limiti</a>
                </div>
            </div>


            <?php if ($isAdmin): ?>
            <a href="admin_utenti.php"
               <?= ($paginaAttiva??'') === 'admin' ? 'class="active"' : '' ?>>⚙️ Utenti</a>
            <?php endif; ?>


            <hr class="nav-separator">

<div class="header-user">

            <!-- Utente corrente + ruolo -->
            <span>
                👤 <?= htmlspecialchars($username) ?>
                <?php if ($isAdmin): ?>
                <span class="header-role header-role-admin"> (admin)</span>
                <?php else: ?>
                <span class="header-role header-role-user"> (astrologo)</span>
                <?php endif; ?>
            </span>

            <!-- Soggetto attivo (se presente) -->
            <?php if ($soggettoNome): ?>
            <span class="soggetto-attivo"
                  title="Soggetto attivo — vai a Soggetti per cambiarlo">
                ⭐ <?= htmlspecialchars($soggettoNome) ?>
            </span>
            <?php endif; ?>

            <!-- Link: Password -->
            <a href="cambia_password.php"
               class="header-link"
               title="Cambia la tua password">
                🔑 Password
            </a>

            <!-- Link: Logout -->
            <a href="logout.php"
               class="header-link">
                Esci
            </a>
        </div>

</nav>


    </div>

<!-- ── Help Modal (condiviso in tutte le pagine) ────────────────── -->
<div id="help-modal-overlay" class="help-modal-overlay">
    <div class="help-modal-box">
        <div class="help-modal-header">
            <h2 class="help-modal-title">❓ Manuale d'uso</h2>
            <span id="help-modal-close" class="help-modal-close">&times;</span>
        </div>
        <div class="help-modal-body" id="help-modal-body">
            <p><em>Contenuto di aiuto contestuale. La versione completa del manuale è in fase di redazione in <code>docs/roadmap_aiuto.md</code>.</em></p>
            <p>Sezione corrente: <strong id="help-modal-section">Generico</strong></p>
        </div>
    </div>
</div>
</header>
<script src="js/help_modal.js" defer></script>
<script src="js/header_nav.js" defer></script>
