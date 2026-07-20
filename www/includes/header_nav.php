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
</header>
<script src="js/header_nav.js" defer></script>
