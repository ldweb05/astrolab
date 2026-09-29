<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * index.php — Gestione Soggetti con autenticazione
 * Astrologia Attiva — Ciro Discepolo
 */
session_start();
require_once 'includes/Auth.php';
require_once 'includes/SweCalc.php';

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();

$isAdmin      = $auth->isAdmin();
$userId       = $auth->getCurrentUserId();
$username     = $auth->getCurrentUsername();
$soggettoId   = $auth->getSoggettoAttivo();
$soggettoNome = $auth->getSoggettoNome();

// Modale impostazioni (stessa logica di dashboard.php): cambio password (tutti) + foto profilo (solo Supporter/admin)
$idxHasFotoProfilo = $auth->hasFeature('foto_profilo');
if (empty($_SESSION['dash_settings_csrf'])) {
    $_SESSION['dash_settings_csrf'] = bin2hex(random_bytes(32));
}
$idxSettingsCsrf = $_SESSION['dash_settings_csrf'];

$stmtIdxFoto = $pdo->prepare('SELECT foto_profilo FROM utenti WHERE id = ?');
$stmtIdxFoto->execute([$userId]);
$idxFotoProfilo = $stmtIdxFoto->fetchColumn() ?: null;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soggetti — AstroLab</title>
    <link rel="stylesheet" href="css/style.css">
   
</head>
<body>

<!-- ── Header ──────────────────────────────────────────────────── -->
<header>
    <div class="header-inner">
        <h1><a href="<?= 'dashboard.php' . ($soggettoId > 0 ? '?id=' . (int)$soggettoId : '') ?>" class="header-logo">AstroLab</a></h1>
        <button class="nav-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="main-nav"
                aria-label="Apri menu di navigazione">☰</button>

        <nav id="main-nav" class="main-nav">
<div class="header-user">
            <span>👤 <?= htmlspecialchars($username) ?>
                <?php if ($isAdmin): ?><span class="header-role header-role-admin"> (admin)</span><?php endif; ?>
            </span>
            <button type="button" id="idx-btn-settings" class="header-icon-btn" title="Impostazioni">⚙️</button>
            <div id="idx-avatar-wrap" class="header-avatar">
                <?php if ($idxFotoProfilo): ?>
                <img id="idx-avatar-img" src="<?= htmlspecialchars($idxFotoProfilo) ?>" alt="Foto profilo"/>
                <?php endif; ?>
            </div>
            <a href="logout.php" class="header-link">Esci</a>
        </div>

</nav>
        
    </div>
</header>

<main>
    <div class="page-title">
        <h2>Gestione Soggetti</h2>
        <button class="btn-primary" onclick="mostraForm()">+ Nuovo Soggetto</button>
    </div>

    <!-- Banner soggetto attivo -->
    <div class="soggetto-banner <?= $soggettoId ? 'attivo' : '' ?>" id="soggetto-banner">
        <label>⭐ Soggetto attivo:</label>
        <select id="sel-soggetto-attivo" onchange="impostaSoggettoAttivo(this.value)">
            <option value="">— Nessuno selezionato —</option>
            <!-- Popolato da JS -->
        </select>
        <?php if ($soggettoId): ?>
        <span class="info-attivo" id="info-soggetto-attivo">
            Tema natale, RS e ricerca useranno: <b><?= htmlspecialchars($soggettoNome) ?></b>
        </span>
        <?php else: ?>
        <span class="info-soggetto-inattivo" id="info-soggetto-attivo">
            Seleziona un soggetto per usarlo in tutte le pagine.
        </span>
        <?php endif; ?>
        <?php if ($soggettoId): ?>
        <button class="btn-secondary btn-cambia-soggetto"
                onclick="cambiaSoggetto()">↺ Cambia soggetto</button>
        <?php endif; ?>
    </div>

    <!-- Form inserimento/modifica -->
    <div id="form-soggetto" class="card is-hidden">
        <h3 id="form-titolo">Nuovo Soggetto</h3>
        <form id="frm-soggetto">
            <input type="hidden" id="soggetto-id">

            <div class="form-grid">
                <div class="form-group">
                    <label>Nome e Cognome *</label>
                    <input type="text" id="nome" placeholder="Es: Mario Rossi" required>
                </div>
                <div class="form-group">
                    <label>Codice</label>
                    <input type="text" id="codice" readonly tabindex="-1" class="readonly-field"
                           placeholder="Assegnato automaticamente al salvataggio"
                           title="Il codice viene assegnato automaticamente e non è modificabile">
                </div>
            </div>

            <div class="form-grid form-grid-4">
                <div class="form-group">
                    <label>Data di Nascita *</label>
                    <input type="date" id="data-nascita" required>
                </div>
                <div class="form-group">
                    <label>Ora Locale *</label>
                    <div class="time-input-row">
                        <input type="time" id="ora-nascita" step="60" required class="time-input-field">
                        <div class="time-step-controls">
                            <button type="button" class="btn-time" onclick="modificaOra(1)">▲</button>
                            <button type="button" class="btn-time" onclick="modificaOra(-1)">▼</button>
                        </div>
                        <div class="time-step-controls">
                            <button type="button" class="btn-time" onclick="modificaMinuti(1)">▲</button>
                            <button type="button" class="btn-time" onclick="modificaMinuti(-1)">▼</button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Ora GMT</label>
                    <div class="time-input-row">
                        <input type="time" id="ora-gmt" step="60" readonly class="time-input-field readonly-field">
                        <div class="time-step-controls">
                            <button type="button" class="btn-time" onclick="modificaOraGMT(1)">▲</button>
                            <button type="button" class="btn-time" onclick="modificaOraGMT(-1)">▼</button>
                        </div>
                        <div class="time-step-controls">
                            <button type="button" class="btn-time" onclick="modificaMinutiGMT(1)">▲</button>
                            <button type="button" class="btn-time" onclick="modificaMinutiGMT(-1)">▼</button>
                        </div>
                        <span id="gmt-giorno-label" class="rs-next-day-label is-hidden"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Offset GMT</label>
                    <div class="offset-gmt-row">
                        <input type="number" id="offset-gmt" step="0.5" placeholder="Es: 1" class="offset-gmt-input">
                        <button type="button" id="btn-ricalcola-offset"
                                onclick="ricalcolaOffset()"
                                title="Ricalcola offset GMT da TimeZoneDB (usa lat/lon e data)"
                                class="btn-ricalcola-offset">
                            🔄
                        </button>
                        <span id="offset-loading"
                              class="offset-loading is-hidden">
                            ⟳ calcolo...
                        </span>
                    </div>
                    <div class="offset-gmt-hint">
                        🔄 = ricalcola da TimeZoneDB (richiede luogo e data)
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Luogo di Nascita *</label>
                <div class="input-search-wrap">
                    <input type="text" id="luogo-search" placeholder="Cerca città..." autocomplete="off">
                    <button type="button" class="btn-search" onclick="cercaLuogo()">🔍 Cerca</button>
                </div>
                <div id="luogo-risultati" class="dropdown-risultati"></div>
            </div>

            <div class="form-grid form-grid-4">
                <div class="form-group">
                    <label>Latitudine</label>
                    <input type="number" id="latitudine" step="0.0001" readonly class="readonly-field">
                </div>
                <div class="form-group">
                    <label>Longitudine</label>
                    <input type="number" id="longitudine" step="0.0001" readonly class="readonly-field">
                </div>
                <div class="form-group">
                    <label>Paese</label>
                    <input type="text" id="nazione" readonly class="readonly-field">
                </div>
                <div class="form-group">
                    <label>Timezone</label>
                    <input type="text" id="timezone" readonly class="readonly-field">
                </div>
            </div>

            <!-- Residenza -->
            <div class="card residenza-card">
                <h3 class="residenza-card-title">🏠 Luogo di Residenza
                    <span class="residenza-card-subtitle">— opzionale, usato come default per le RS</span>
                </h3>
                <div class="form-group">
                    <label>Città di Residenza</label>
                    <div class="input-search-wrap">
                        <input type="text" id="residenza-search" placeholder="Cerca città di residenza..." autocomplete="off">
                        <button type="button" class="btn-search" onclick="cercaLuogoResidenza()">🔍 Cerca</button>
                    </div>
                    <div id="residenza-risultati" class="dropdown-risultati"></div>
                </div>
                <div class="form-grid form-grid-4">
                    <div class="form-group"><label>Latitudine</label>
                        <input type="number" id="residenza-latitudine" step="0.0001" readonly class="readonly-field"></div>
                    <div class="form-group"><label>Longitudine</label>
                        <input type="number" id="residenza-longitudine" step="0.0001" readonly class="readonly-field"></div>
                    <div class="form-group"><label>Paese</label>
                        <input type="text" id="residenza-nazione" readonly class="readonly-field"></div>
                    <div class="form-group form-group-end">
                        <button type="button" class="btn-secondary btn-cancella-residenza" onclick="cancellaResidenza()">✕ Cancella</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Note</label>
                <textarea id="note" rows="2" placeholder="Note opzionali..."></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="nascondiForm()">Annulla</button>
                <button type="button" class="btn-primary" onclick="salvaSoggetto()">💾 Salva</button>
            </div>
        </form>
    </div>

    <!-- Lista soggetti -->
    <div class="card">
        <div id="lista-soggetti">
            <p class="loading">Caricamento...</p>
        </div>
    </div>
</main>

<script src="js/header_nav.js" defer></script>
<script src="js/zodiac_wheel.js"></script>
<script src="js/app.js"></script>
<script>
// ── Soggetto attivo ────────────────────────────────────────────────────────
let soggettoAttivoId = <?= $soggettoId ? (int)$soggettoId : 'null' ?>;
const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;

function impostaSoggettoAttivo(id) {
    if (!id) return;
    fetch('api/soggetti_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'set_attivo', id: parseInt(id)})
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            soggettoAttivoId = data.soggetto_id;
            // Aggiorna info banner
            const infoEl = document.getElementById('info-soggetto-attivo');
            infoEl.innerHTML = 'Tema natale, RS e ricerca useranno: <b>' +
                data.soggetto_nome.replace(/</g,'&lt;') + '</b>';
            document.getElementById('soggetto-banner').classList.add('attivo');
            mostraMessaggio('Soggetto attivo: ' + data.soggetto_nome, 'success');
        }
    });
}

function cambiaSoggetto() {
    fetch('api/session_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'clear_soggetto'})
    }).then(() => location.reload());
}

// Popola il dropdown soggetto attivo dopo il caricamento lista
function popolaDropdownAttivo(soggetti) {
    const sel = document.getElementById('sel-soggetto-attivo');
    sel.innerHTML = '<option value="">— Nessuno selezionato —</option>';
    soggetti.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.nome + ' (' + formatData(s.data_nascita) + ')';
        if (s.id == soggettoAttivoId) opt.selected = true;
        sel.appendChild(opt);
    });
}

// Override caricaSoggetti per popolare anche il dropdown
const _caricaSoggettiOrig = caricaSoggetti;
// Ridefinisci qui per aggiungere il dropdown (dopo il caricamento base)
function caricaSoggettiConDropdown() {
    fetch('api/soggetti_api.php?action=lista')
        .then(r => r.json())
        .then(data => {
            // Popola dropdown attivo
            popolaDropdownAttivo(data);

            // Popola lista
            const div = document.getElementById('lista-soggetti');
            if (!data.length) {
                div.innerHTML = '<p class="empty">Nessun soggetto inserito. Clicca "+ Nuovo Soggetto" per iniziare.</p>';
                return;
            }

            // Colonna Proprietario — solo per admin
            const thProp = IS_ADMIN
                ? '<th class="th-nowrap">Proprietario</th>'
                : '';

            let html = `<table class="tabella-soggetti">
                <thead><tr>
                    <th>Codice</th><th>Nome</th><th>Data Nascita</th>
                    <th>Ora</th><th>Luogo</th>${thProp}<th>Azioni</th>
                </tr></thead><tbody>`;

            data.forEach(s => {
                const isAttivo = s.id == soggettoAttivoId;
                const residenzaHtml = s.residenza_luogo
                    ? `<div class="soggetto-residenza">🏠 ${s.residenza_luogo}${s.residenza_nazione ? ', ' + s.residenza_nazione : ''}</div>`
                    : '';

                // Badge "Soggetto di: [Nome Completo o username]" — solo per admin
                let tdProp = '';
                if (IS_ADMIN) {
                    // Preferisce nome_completo, fallback su username
                    const nomeDisplay = (s.astrologo_nome_completo || s.astrologo_username || '?')
                        .replace(/</g, '&lt;');
                    const username = (s.astrologo_username || '').replace(/</g, '&lt;');
                    const tooltip  = username ? `Utente: ${username}` : '';

                    tdProp = `<td>
                        <span class="soggetto-proprietario" title="${tooltip}">
                            <span class="soggetto-proprietario-label">Soggetto di:</span>
                            <strong>${nomeDisplay}</strong>
                        </span>
                    </td>`;
                }

                // Pulsante accesso del soggetto (Fase B3): solo per i propri soggetti.
                const accStati  = { nessuno: 'Accesso non abilitato', attivo: 'Accesso attivo', disattivato: 'Accesso disattivato' };
                const accStato  = accStati[s.accesso_stato] ? s.accesso_stato : 'nessuno';
                const accCodice = String(s.codice || '').replace(/[^A-Za-z0-9]/g, '');
                const accBtn    = (s.accesso_gestibile && accCodice)
                    ? `<button type="button" class="btn-icon btn-accesso accesso-${accStato}" title="${accStati[accStato]}" data-accesso-id="${parseInt(s.id, 10)}" data-accesso-codice="${accCodice}" data-accesso-stato="${accStato}">🔑</button>`
                    : '';

                html += `<tr class="${isAttivo ? 'riga-soggetto-attivo' : ''}">
                    <td>${s.codice || '—'}</td>
                    <td><b><a onclick="apriDashboard(${s.id})" style="cursor:pointer;color:inherit;text-decoration:none;">${s.nome}</a></b>${isAttivo ? ' <span class="soggetto-attivo-label">⭐ attivo</span>' : ''}</td>
                    <td>${formatData(s.data_nascita)}</td>
                    <td>${s.ora_nascita}</td>
                    <td>
                        <div>${s.luogo_nascita || ''} ${s.nazione_nascita || ''}</div>
                        ${residenzaHtml}
                    </td>
                    ${tdProp}
                    <td><div class="azioni">
                        <button class="btn-icon" title="Imposta attivo" onclick="impostaSoggettoAttivo(${s.id})">⭐</button>
                        <button class="btn-icon" title="Modifica" onclick="modificaSoggetto(${s.id})">✏️</button>
                        ${accBtn}
                        <button class="btn-icon" title="Elimina" onclick="eliminaSoggetto(${s.id}, '${s.nome.replace(/'/g, "\\'")}')">🗑️</button>
                    </div></td>
                </tr>`;
            });
            html += '</tbody></table>';
            div.innerHTML = html;
        })
        .catch(e => {
            document.getElementById('lista-soggetti').innerHTML =
                '<p class="msg-error">Errore caricamento: ' + e.message + '</p>';
        });
}

document.addEventListener('DOMContentLoaded', caricaSoggettiConDropdown);
</script>
<!-- Modale Impostazioni: cambio password + foto profilo (stessa logica/API di dashboard.php) -->
<div id="idx-modale-overlay" class="idx-modal-overlay">
    <div class="idx-modal-box">
        <div class="idx-modal-header">
            <h2>Impostazioni</h2>
            <button type="button" onclick="idxChiudiModaleImpostazioni()" class="idx-modal-close">&times;</button>
        </div>

        <div class="idx-modal-section">
            <h3>🔑 Cambia Password</h3>
            <div id="idx-pwd-msg" class="idx-modal-msg"></div>
            <input id="idx-pwd-attuale" type="password" autocomplete="off" placeholder="Password attuale">
            <input id="idx-pwd-nuova" type="password" autocomplete="off" placeholder="Nuova password (min. 8 caratteri)">
            <input id="idx-pwd-conferma" type="password" autocomplete="off" placeholder="Conferma nuova password">
            <button type="button" onclick="idxCambiaPassword()" class="idx-btn-primary">Aggiorna Password</button>
        </div>

        <div class="idx-modal-divider"></div>

        <div class="idx-modal-section">
            <h3>🖼️ Foto Profilo</h3>
            <?php if ($idxHasFotoProfilo): ?>
            <div id="idx-foto-msg" class="idx-modal-msg"></div>
            <input id="idx-foto-input" type="file" accept="image/jpeg,image/png,image/webp">
            <p class="idx-modal-hint">JPG, PNG o WEBP, max 2MB.</p>
            <button type="button" onclick="idxCaricaFoto()" class="idx-btn-primary">Carica Foto</button>
            <?php else: ?>
            <p class="idx-modal-hint">Disponibile solo per il piano <strong>Supporter</strong>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const IDX_SETTINGS_CSRF = "<?= $idxSettingsCsrf ?>";

function idxApriModaleImpostazioni() {
    document.getElementById('idx-modale-overlay').classList.add('is-open');
}
function idxChiudiModaleImpostazioni() {
    document.getElementById('idx-modale-overlay').classList.remove('is-open');
}

function idxCaricaFoto() {
    const input = document.getElementById('idx-foto-input');
    const msg = document.getElementById('idx-foto-msg');
    if (!input.files || !input.files[0]) {
        msg.className = 'idx-modal-msg err';
        msg.textContent = 'Seleziona prima un file.';
        return;
    }

    const formData = new FormData();
    formData.append('foto', input.files[0]);
    formData.append('csrf_token', IDX_SETTINGS_CSRF);

    fetch('api/foto_profilo_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                msg.className = 'idx-modal-msg ok';
                msg.textContent = 'Foto aggiornata.';
                const wrap = document.getElementById('idx-avatar-wrap');
                wrap.innerHTML = '<img id="idx-avatar-img" src="' + data.url + '" alt="Foto profilo">';
            } else {
                msg.className = 'idx-modal-msg err';
                msg.textContent = data.errore || 'Errore imprevisto.';
            }
        })
        .catch(() => {
            msg.className = 'idx-modal-msg err';
            msg.textContent = 'Errore di connessione. Riprova.';
        });
}

function idxCambiaPassword() {
    const attuale  = document.getElementById('idx-pwd-attuale').value;
    const nuova    = document.getElementById('idx-pwd-nuova').value;
    const conferma = document.getElementById('idx-pwd-conferma').value;
    const msg = document.getElementById('idx-pwd-msg');

    fetch('api/cambia_password_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            csrf_token: IDX_SETTINGS_CSRF,
            password_attuale: attuale,
            nuova_password: nuova,
            conferma_password: conferma
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            msg.className = 'idx-modal-msg ok';
            msg.textContent = 'Password aggiornata con successo.';
            document.getElementById('idx-pwd-attuale').value = '';
            document.getElementById('idx-pwd-nuova').value = '';
            document.getElementById('idx-pwd-conferma').value = '';
        } else {
            msg.className = 'idx-modal-msg err';
            msg.textContent = data.errore || 'Errore imprevisto.';
        }
    })
    .catch(() => {
        msg.className = 'idx-modal-msg err';
        msg.textContent = 'Errore di connessione. Riprova.';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const btnSettings = document.getElementById('idx-btn-settings');
    if (btnSettings) { btnSettings.addEventListener('click', idxApriModaleImpostazioni); }
});
</script>
<!-- Modale Accesso soggetto (Fase B3, docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md) -->
<style>
.btn-accesso.accesso-nessuno { opacity: 0.45; }
.btn-accesso.accesso-attivo { background: #E8F5E9; }
.btn-accesso.accesso-disattivato { opacity: 0.45; text-decoration: line-through; }
.acc-riga { margin: 6px 0 14px; font-size: 14px; color: #444; }
.acc-password { font-family: monospace; font-size: 22px; letter-spacing: 2px; background: #F5F5F5; border: 1px dashed #2C3E6B; border-radius: 6px; padding: 10px; text-align: center; margin: 8px 0; user-select: all; }
.acc-avviso { font-size: 13px; color: #8A5A00; margin-bottom: 10px; }
.acc-pulsanti { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.acc-btn-secondario { background: #fff; color: #8A1C1C; border: 1px solid #8A1C1C; border-radius: 6px; padding: 8px 12px; cursor: pointer; }
</style>
<div id="acc-modale-overlay" class="idx-modal-overlay">
    <div class="idx-modal-box">
        <div class="idx-modal-header">
            <h2>🔑 Accesso soggetto</h2>
            <button type="button" id="acc-btn-chiudi" class="idx-modal-close">&times;</button>
        </div>
        <div class="idx-modal-section">
            <div class="acc-riga">Codice: <strong id="acc-codice"></strong> &middot; <span id="acc-stato"></span></div>
            <div id="acc-msg" class="idx-modal-msg"></div>
            <div id="acc-risultato" style="display:none">
                <div class="acc-password" id="acc-password"></div>
                <div class="acc-avviso">Comunica codice e password al soggetto: la password non verrà più mostrata. Al primo accesso dovrà cambiarla.</div>
                <button type="button" id="acc-btn-copia" class="idx-btn-primary">Copia password</button>
            </div>
            <div class="acc-pulsanti">
                <button type="button" id="acc-btn-abilita" class="idx-btn-primary">Abilita accesso</button>
                <button type="button" id="acc-btn-rigenera" class="idx-btn-primary">Genera nuova password</button>
                <button type="button" id="acc-btn-riattiva" class="idx-btn-primary">Riattiva (nuova password)</button>
                <button type="button" id="acc-btn-disattiva" class="acc-btn-secondario">Disattiva accesso</button>
            </div>
        </div>
    </div>
</div>
<script src="js/accesso_soggetti.js"></script>
</body>
</html>
