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
$_riviActive = in_array($paginaAttiva ?? '', ['rs', 'rl', 'rilocazione', 'transiti', 'ricerca', 'ricerca_rl']);

// Modale Impostazioni (stessa logica/API di dashboard.php): cambio password + foto profilo
$_hnUserId = $auth->getCurrentUserId();
$_hnHasFotoProfilo = $auth->hasFeature('foto_profilo');
if (empty($_SESSION['dash_settings_csrf'])) {
    $_SESSION['dash_settings_csrf'] = bin2hex(random_bytes(32));
}
$_hnSettingsCsrf = $_SESSION['dash_settings_csrf'];
$_stmtHnFoto = $pdo->prepare('SELECT foto_profilo FROM utenti WHERE id = ?');
$_stmtHnFoto->execute([$_hnUserId]);
$_hnFotoProfilo = $_stmtHnFoto->fetchColumn() ?: null;
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap');
/* ── Dropdown nav "Rivoluzioni" ────────────────────────────────────────── */
.nav-dropdown {
    position: relative;
    display: inline-flex;
    align-items: center;
}

/* Trigger (stesso stile di nav a) */
.nav-dropdown-trigger {
    background: none;
    border: none;
    font-family: inherit;
    color: #6b5c4f;
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
.nav-dropdown-trigger:hover {
    background: rgba(44,62,107,0.08);
    color: #2C3E6B;
}
.nav-dropdown-trigger.active {
    background: #12A0D7;
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
    background: #FFFFFF;
    border: 1px solid rgba(44,62,107,0.15);
    border-radius: 6px;
    box-shadow: 0 8px 24px rgba(44,62,107,0.15);
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
    color: #6b5c4f;
    text-decoration: none;
    padding: 8px 18px;
    font-size: 13px;
    letter-spacing: 0.04em;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
.nav-dropdown-menu a:hover,
.nav-dropdown-menu a.active {
    background: rgba(44,62,107,0.08);
    color: #2C3E6B;
}
/* Separatore visivo tra le voci */
.nav-dropdown-menu a + a {
    border-top: 1px solid rgba(44,62,107,0.08);
}
/* ── Trigger Help ──────────────────────────────────────────────── */
.help-trigger {
    color: #6b5c4f !important;
    background: none;
    border: none;
}
.help-trigger:hover,
.help-trigger.active {
    color: #2C3E6B !important;
}
</style>

<header>
    <div class="header-inner">
        <h1><a href="<?= _navUrl('dashboard.php', $_navSoggettoId) ?>" class="header-logo">AstroLab</a></h1>
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
                    Ricerche
                </button>
                <div class="nav-dropdown-menu">
                    <a href="<?= _navUrl('rs.php', $_navSoggettoId) ?>"
                       <?= ($paginaAttiva??'') === 'rs' ? 'class="active"' : '' ?>>
                        ↺ Riv. Solare
                    </a>
                    <a href="<?= _navUrl('ricerca.php', $_navSoggettoId) ?>"
                       <?= ($paginaAttiva??'') === 'ricerca' ? 'class="active"' : '' ?>>
                        🔍 Località RS
                    </a>
                    <a href="<?= _navUrl('rl.php', $_navSoggettoId) ?>"
                       <?= ($paginaAttiva??'') === 'rl' ? 'class="active"' : '' ?>>
                        ☽ Riv. Lunare
                    </a>
                    <a href="<?= _navUrl('ricerca_rl.php', $_navSoggettoId) ?>"
                       <?= ($paginaAttiva??'') === 'ricerca_rl' ? 'class="active"' : '' ?>>
                        🔍 Località RL
                    </a>
                    <a href="<?= _navUrl('rilocazione.php', $_navSoggettoId) ?>"
                       <?= ($paginaAttiva??'') === 'rilocazione' ? 'class="active"' : '' ?>>
                        ☿ Rilocazione
                    </a>
                    <a href="<?= _navUrl('transiti.php', $_navSoggettoId) ?>"
                       <?= ($paginaAttiva??'') === 'transiti' ? 'class="active"' : '' ?>>
                        ☌ Transiti Planetari
                    </a>
                </div>
            </div>
            <!-- ── Fine dropdown ────────────────────────────────────── -->


            <!-- ── Dropdown: Help ─────────────────────────────────────── -->
            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-trigger help-trigger" aria-expanded="false">
                    Help
                </button>
                <div class="nav-dropdown-menu">
                    <a href="34_regole.html" target="_blank">1. Le 34 Regole</a>
                    <a href="help_account.php" target="_blank">2. Introduzione e Account</a>
                    <a href="help_soggetti.php" target="_blank">3. Gestione Soggetti</a>
                    <a href="help_calcoli.php" target="_blank">4. Calcoli e Analisi</a>
                    <a href="help_ricerca.php" target="_blank">5. Ricerca Geografica</a>
                    <a href="help_report.php" target="_blank">6. Report e Stampa</a>
                    <a href="help_comparatore.php" target="_blank">7. Comparatore e DSS</a>
                    <a href="help_interfaccia.php" target="_blank">8. Interfaccia e Visualizzazione</a>
                    <a href="help_faq.php" target="_blank">9. FAQ e Limiti</a>
                </div>
            </div>
            <!-- ── Fine dropdown ────────────────────────────────────── -->

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

            <!-- Bottone Impostazioni (modale: password + foto profilo) -->
            <button type="button" id="hn-btn-settings" class="header-icon-btn" title="Impostazioni">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">settings</span>
            </button>

            <!-- Avatar -->
            <div id="hn-avatar-wrap" class="header-avatar">
                <?php if ($_hnFotoProfilo): ?>
                <img id="hn-avatar-img" src="<?= htmlspecialchars($_hnFotoProfilo) ?>" alt="Foto profilo"/>
                <?php endif; ?>
            </div>

            <!-- Link: Logout -->
            <a href="logout.php"
               class="header-link">
                Esci
            </a>
        </div>

</nav>


    </div>
</header>

<!-- Modale Impostazioni: cambio password + foto profilo (stessa logica/API di dashboard.php) -->
<div id="hn-modale-overlay" class="settings-modal-overlay">
    <div class="settings-modal-box">
        <div class="settings-modal-header">
            <h2>Impostazioni</h2>
            <button type="button" onclick="hnChiudiModaleImpostazioni()" class="settings-modal-close">&times;</button>
        </div>

        <div class="settings-modal-section">
            <h3>🔑 Cambia Password</h3>
            <div id="hn-pwd-msg" class="settings-modal-msg"></div>
            <input id="hn-pwd-attuale" type="password" autocomplete="off" placeholder="Password attuale">
            <input id="hn-pwd-nuova" type="password" autocomplete="off" placeholder="Nuova password (min. 8 caratteri)">
            <input id="hn-pwd-conferma" type="password" autocomplete="off" placeholder="Conferma nuova password">
            <button type="button" onclick="hnCambiaPassword()" class="settings-btn-primary">Aggiorna Password</button>
        </div>

        <div class="settings-modal-divider"></div>

        <div class="settings-modal-section">
            <h3>🖼️ Foto Profilo</h3>
            <?php if ($_hnHasFotoProfilo): ?>
            <div id="hn-foto-msg" class="settings-modal-msg"></div>
            <input id="hn-foto-input" type="file" accept="image/jpeg,image/png,image/webp">
            <p class="settings-modal-hint">JPG, PNG o WEBP, max 2MB.</p>
            <button type="button" onclick="hnCaricaFoto()" class="settings-btn-primary">Carica Foto</button>
            <?php else: ?>
            <p class="settings-modal-hint">Disponibile solo per il piano <strong>Supporter</strong>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const HN_SETTINGS_CSRF = "<?= $_hnSettingsCsrf ?>";

function hnApriModaleImpostazioni() {
    document.getElementById('hn-modale-overlay').classList.add('is-open');
}
function hnChiudiModaleImpostazioni() {
    document.getElementById('hn-modale-overlay').classList.remove('is-open');
}

function hnCaricaFoto() {
    const input = document.getElementById('hn-foto-input');
    const msg = document.getElementById('hn-foto-msg');
    if (!input.files || !input.files[0]) {
        msg.className = 'settings-modal-msg err';
        msg.textContent = 'Seleziona prima un file.';
        return;
    }

    const formData = new FormData();
    formData.append('foto', input.files[0]);
    formData.append('csrf_token', HN_SETTINGS_CSRF);

    fetch('api/foto_profilo_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                msg.className = 'settings-modal-msg ok';
                msg.textContent = 'Foto aggiornata.';
                const wrap = document.getElementById('hn-avatar-wrap');
                wrap.innerHTML = '<img id="hn-avatar-img" src="' + data.url + '" alt="Foto profilo">';
            } else {
                msg.className = 'settings-modal-msg err';
                msg.textContent = data.errore || 'Errore imprevisto.';
            }
        })
        .catch(() => {
            msg.className = 'settings-modal-msg err';
            msg.textContent = 'Errore di connessione. Riprova.';
        });
}

function hnCambiaPassword() {
    const attuale  = document.getElementById('hn-pwd-attuale').value;
    const nuova    = document.getElementById('hn-pwd-nuova').value;
    const conferma = document.getElementById('hn-pwd-conferma').value;
    const msg = document.getElementById('hn-pwd-msg');

    fetch('api/cambia_password_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            csrf_token: HN_SETTINGS_CSRF,
            password_attuale: attuale,
            nuova_password: nuova,
            conferma_password: conferma
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            msg.className = 'settings-modal-msg ok';
            msg.textContent = 'Password aggiornata con successo.';
            document.getElementById('hn-pwd-attuale').value = '';
            document.getElementById('hn-pwd-nuova').value = '';
            document.getElementById('hn-pwd-conferma').value = '';
        } else {
            msg.className = 'settings-modal-msg err';
            msg.textContent = data.errore || 'Errore imprevisto.';
        }
    })
    .catch(() => {
        msg.className = 'settings-modal-msg err';
        msg.textContent = 'Errore di connessione. Riprova.';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const btnSettings = document.getElementById('hn-btn-settings');
    if (btnSettings) { btnSettings.addEventListener('click', hnApriModaleImpostazioni); }
});
</script>
<script src="js/header_nav.js" defer></script>
