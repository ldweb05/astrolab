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
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroLab — Soggetti</title>
    <link rel="stylesheet" href="css/style.css">
   
</head>
<body>

<!-- ── Header ──────────────────────────────────────────────────── -->
<header>
    <div class="header-inner">
        <h1>☉ AstroLab</h1>
        <button class="nav-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="main-nav"
                aria-label="Apri menu di navigazione">☰</button>

        <nav id="main-nav" class="main-nav">
            <a href="index.php" class="active">Soggetti</a>
            <a href="tema.php">Tema Natale</a>
            <a href="rs.php">Rivoluzione Solare</a>
            <a href="ricerca.php">Ricerca Località</a>
            <?php if ($isAdmin): ?>
            <a href="admin_utenti.php">⚙️ Utenti</a>
            <?php endif; ?>
        

            <hr class="nav-separator">

<div class="header-user">
            <span>👤 <?= htmlspecialchars($username) ?>
                <?php if ($isAdmin): ?><span class="header-role header-role-admin"> (admin)</span><?php endif; ?>
            </span>
            <?php if ($soggettoNome): ?>
            <span class="soggetto-attivo">⭐ <?= htmlspecialchars($soggettoNome) ?></span>
            <?php endif; ?>
            <a href="cambia_password.php" class="header-link">🔑 Password</a>
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
                    <label>Codice (opzionale)</label>
                    <input type="text" id="codice" placeholder="Es: MR001">
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

                html += `<tr class="${isAttivo ? 'riga-soggetto-attivo' : ''}">
                    <td>${s.codice || '—'}</td>
                    <td><b>${s.nome}</b>${isAttivo ? ' <span class="soggetto-attivo-label">⭐ attivo</span>' : ''}</td>
                    <td>${formatData(s.data_nascita)}</td>
                    <td>${s.ora_nascita}</td>
                    <td>
                        <div>${s.luogo_nascita || ''} ${s.nazione_nascita || ''}</div>
                        ${residenzaHtml}
                    </td>
                    ${tdProp}
                    <td><div class="azioni">
                        <button class="btn-icon" title="Imposta attivo" onclick="impostaSoggettoAttivo(${s.id})">⭐</button>
                        <button class="btn-icon" title="Tema Natale" onclick="apriTema(${s.id})">TN</button>
                        <button class="btn-icon" title="Rivoluzione Solare" onclick="apriRS(${s.id})">RS</button>
                        <button class="btn-icon" title="Modifica" onclick="modificaSoggetto(${s.id})">✏️</button>
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
</body>
</html>
