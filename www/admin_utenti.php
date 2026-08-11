<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * admin_utenti.php — Gestione utenti (solo admin)
 * Astrologia Attiva — Ciro Discepolo
 *
 * Colonne tabella:
 *   Username | Email | Ruolo | Stato | Soggetti (link cliccabile) | Ultimo accesso | Azioni
 *
 * Azioni disponibili (admin):
 *   - Disattiva/Attiva utente
 *   - Reset password (modale)
 *   - Cambia ruolo (modale)
 *   - Elimina con trasferimento soggetti (modale)
 */
session_start();
require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediAdmin();   // reindirizza se non admin

$messaggio = '';
$tipoMsg   = '';

if (empty($_SESSION['admin_utenti_csrf'])) {
    $_SESSION['admin_utenti_csrf'] = bin2hex(random_bytes(32));
}

// ── Azioni POST ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf_token'] ?? '');

    if (!hash_equals((string)$_SESSION['admin_utenti_csrf'], $csrf)) {
        $messaggio = 'Sessione non valida. Ricarica la pagina e riprova.';
        $tipoMsg = 'error';
    } else {
        $azione = $_POST['azione'] ?? '';

        if ($azione === 'crea') {
            $result = $auth->creaUtente(
                trim($_POST['username']      ?? ''),
                trim($_POST['email']         ?? ''),
                $_POST['password']           ?? '',
                $_POST['ruolo']              ?? 'user',
                trim($_POST['nome_completo'] ?? ''),
                trim($_POST['telefono']      ?? ''),
                trim($_POST['note']          ?? '')
            );
            $messaggio = $result['ok'] ? 'Utente creato con successo.' : $result['errore'];
            $tipoMsg   = $result['ok'] ? 'success' : 'error';
        } elseif ($azione === 'modifica_anagrafica') {
            $id = intval($_POST['id'] ?? 0);
            $result = $auth->aggiornaUtente(
                $id,
                trim($_POST['email']         ?? ''),
                trim($_POST['nome_completo'] ?? ''),
                trim($_POST['telefono']      ?? ''),
                trim($_POST['note']          ?? '')
            );
            $messaggio = $result['ok'] ? 'Dati aggiornati con successo.' : $result['errore'];
            $tipoMsg   = $result['ok'] ? 'success' : 'error';
        } elseif ($azione === 'reset_password') {
            $id = intval($_POST['id'] ?? 0);
            $nuova = $_POST['nuova_password'] ?? '';
            $result = $auth->cambiaPassword($id, $nuova);
            $messaggio = $result['ok'] ? 'Password aggiornata.' : $result['errore'];
            $tipoMsg   = $result['ok'] ? 'success' : 'error';
        } elseif ($azione === 'toggle_attivo') {
            $id = intval($_POST['id'] ?? 0);
            $ok = $auth->toggleAttivo($id);
            $messaggio = $ok ? 'Stato aggiornato.' : 'Impossibile modificare il proprio account.';
            $tipoMsg   = $ok ? 'success' : 'error';
        } elseif ($azione === 'cambia_ruolo') {
            $id = intval($_POST['id'] ?? 0);
            $ruolo = $_POST['ruolo'] ?? '';
            $ok = $auth->aggiornaRuolo($id, $ruolo);
            $messaggio = $ok ? 'Ruolo aggiornato.' : 'Errore aggiornamento ruolo.';
            $tipoMsg   = $ok ? 'success' : 'error';
        } elseif ($azione === 'aggiorna_piano') {
            $id = intval($_POST['id'] ?? 0);
            $pianoCode = $_POST['piano_code'] ?? '';
            $donazione = ($_POST['donazione_importo'] ?? '') !== ''
                ? (float)$_POST['donazione_importo'] : null;
            $supporterInizio = ($_POST['supporter_inizio'] ?? '') !== ''
                ? $_POST['supporter_inizio'] : null;
            $supporterScadenza = ($_POST['supporter_scadenza'] ?? '') !== ''
                ? $_POST['supporter_scadenza'] : null;
            $override = ($_POST['subjects_limit_override'] ?? '') !== ''
                ? intval($_POST['subjects_limit_override']) : null;
            $accessoSpeciale = !empty($_POST['accesso_speciale_permanente']);
            $notePiano = trim($_POST['note_piano'] ?? '');

            $result = $auth->aggiornaPianoUtente(
                $id, $pianoCode, $donazione, $supporterInizio, $supporterScadenza,
                $override, $accessoSpeciale, $notePiano
            );
            $messaggio = $result['ok'] ? 'Piano aggiornato con successo.' : $result['errore'];
            $tipoMsg   = $result['ok'] ? 'success' : 'error';
        } elseif ($azione === 'verifica_manuale') {
            $id = intval($_POST['id'] ?? 0);
            $ok = $auth->verificaManualmente($id);
            $messaggio = $ok ? 'Utente verificato e attivato.' : 'Utente già verificato o non trovato.';
            $tipoMsg   = $ok ? 'success' : 'error';
        } elseif ($azione === 'elimina') {
            $id = intval($_POST['id'] ?? 0);
            $trasferisciA = intval($_POST['trasferisci_a'] ?? 1);
            $ok = $auth->eliminaUtente($id, $trasferisciA);
            $messaggio = $ok ? 'Utente eliminato e soggetti trasferiti.' : 'Impossibile eliminare il proprio account.';
            $tipoMsg   = $ok ? 'success' : 'error';
        }
    }
}

$utenti       = $auth->getListaUtenti();
$soggettoNome = $auth->getSoggettoNome();
$username     = $auth->getCurrentUsername();
$isAdmin      = true;

// Calcola conteggi totali per la riga sommario
$totAdmin     = count(array_filter($utenti, fn($u) => $u['ruolo'] === 'admin'));
$totUser = count(array_filter($utenti, fn($u) => $u['ruolo'] === 'user'));
$totAttivi    = count(array_filter($utenti, fn($u) => $u['attivo']));
$totSoggetti  = array_sum(array_column($utenti, 'n_soggetti'));

// Soggetti di studio raggruppati per astrologo, per la riga espandibile
$soggettiPerAstrologo = [];
$soggettiStmt = $pdo->query(
    "SELECT id, utente_id, codice, nome, data_nascita, ora_nascita,
            luogo_nascita, nazione_nascita, residenza_luogo, residenza_nazione
     FROM soggetti
     ORDER BY utente_id, nome"
);
foreach ($soggettiStmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
    $soggettiPerAstrologo[$s['utente_id']][] = $s;
}

$paginaAttiva = 'admin';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti — Astrologia Attiva</title>
    <link rel="stylesheet" href="css/style.css">
  
</head>
<body>

<?php include 'includes/header_nav.php'; ?>

<main>
    <div class="page-title">
        <h2>Gestione Utenti</h2>
        <button class="btn-primary" onclick="apriModaleNuovoUtente()">+ Nuovo Utente</button>
    </div>

    <?php if ($messaggio): ?>
    <div class="<?= $tipoMsg === 'success' ? 'msg-success' : 'msg-error' ?>" style="margin-bottom:14px">
        <?= $tipoMsg === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($messaggio) ?>
    </div>
    <?php endif; ?>

    <!-- ── Sommario statistiche ─────────────────────────────────────────── -->
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-num"><?= count($utenti) ?></span>
            <span class="stat-lbl">Utenti totali</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat-item">
            <span class="stat-num" style="color:#2C3E6B"><?= $totAdmin ?></span>
            <span class="stat-lbl">Admin</span>
        </div>
        <div class="stat-item">
            <span class="stat-num" style="color:#5A7AB0"><?= $totUser ?></span>
            <span class="stat-lbl">Utenti</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat-item">
            <span class="stat-num" style="color:#4CAF50"><?= $totAttivi ?></span>
            <span class="stat-lbl">Attivi</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat-item">
            <span class="stat-num" style="color:#C8960C"><?= $totSoggetti ?></span>
            <span class="stat-lbl">Soggetti totali</span>
        </div>
    </div>

    <div class="card">
        <table class="tabella-soggetti">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Nome / Contatto</th>
                    <th>Ruolo</th>
                    <th>Stato</th>
                    <th title="Soggetti di studio associati">Soggetti di studio</th>
                    <th>Ultimo accesso</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($utenti as $u):
                $isCurrentUser = ($u['id'] === $auth->getCurrentUserId());
                $nSoggetti     = (int)$u['n_soggetti'];
            ?>
            <tr class="<?= $isCurrentUser ? 'riga-corrente' : '' ?>">

                <!-- Username -->
                <td>
                    <?php $nSoggettiRiga = count($soggettiPerAstrologo[$u['id']] ?? []); ?>
                    <?php if ($nSoggettiRiga > 0): ?>
                    <span class="soggetti-toggle" style="cursor:pointer;user-select:none"
                          onclick="toggleSoggettiAstrologo(<?= $u['id'] ?>)">
                        <span id="arrow-<?= $u['id'] ?>" style="display:inline-block;width:12px;color:#888">▸</span>
                        <b><?= htmlspecialchars($u['username']) ?></b>
                        <span style="font-size:11px;color:#888">(<?= $nSoggettiRiga ?>)</span>
                    </span>
                    <?php else: ?>
                    <span style="display:inline-block;width:12px"></span>
                    <b><?= htmlspecialchars($u['username']) ?></b>
                    <?php endif; ?>
                    <?php if ($isCurrentUser): ?>
                    <span style="font-size:10px;color:#888;margin-left:4px">(tu)</span>
                    <?php endif; ?>
                </td>

                <!-- Nome completo + email + telefono -->
                <td style="font-size:12px">
                    <?php if ($u['nome_completo']): ?>
                    <div style="font-weight:500;color:#2C2C2C"><?= htmlspecialchars($u['nome_completo']) ?></div>
                    <?php endif; ?>
                    <div style="color:#666"><?= htmlspecialchars($u['email'] ?: '—') ?></div>
                    <?php if ($u['telefono']): ?>
                    <div style="color:#888;font-size:11px">📞 <?= htmlspecialchars($u['telefono']) ?></div>
                    <?php endif; ?>
                </td>

                <!-- Ruolo -->
                <td>
                    <?php if ($u['ruolo'] === 'admin'): ?>
                        <span class="badge-admin">⚙️ Admin</span>
                    <?php else: ?>
                        <span class="badge-astro">👤 Utente</span>
                    <?php endif; ?>
                </td>

                <!-- Stato -->
                <td>
                    <?php if ($u['account_status'] === 'pending_email'): ?>
                        <span class="badge-off" title="In attesa di verifica email">⏳ Da verificare</span>
                    <?php elseif ($u['attivo']): ?>
                        <span class="badge-attivo">● Attivo</span>
                    <?php else: ?>
                        <span class="badge-off">○ Disattivo</span>
                    <?php endif; ?>
                </td>

                <!-- Soggetti di studio -->
                <td>
                    <?php if ($nSoggetti > 0): ?>
                    <a href="index.php?utente_id=<?= $u['id'] ?>"
                       class="soggetti-link"
                       title="Visualizza i <?= $nSoggetti ?> soggetti di <?= htmlspecialchars($u['username']) ?>">
                        📋 <?= $nSoggetti ?> soggett<?= $nSoggetti === 1 ? 'o' : 'i' ?>
                    </a>
                    <?php else: ?>
                    <span class="soggetti-link zero">— nessuno</span>
                    <?php endif; ?>
                </td>

                <!-- Ultimo accesso -->
                <td style="font-size:11px;color:#888;white-space:nowrap">
                    <?= $u['ultimo_accesso']
                        ? date('d/m/Y H:i', strtotime($u['ultimo_accesso']))
                        : '— mai —' ?>
                </td>

                <!-- Azioni -->
                <td>
                    <div class="azioni">

                        <?php if ($u['account_status'] === 'pending_email'): ?>
                        <!-- Verifica manuale -->
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_utenti_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="azione" value="verifica_manuale">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn-icon" title="Verifica manualmente e attiva l'account">✅</button>
                        </form>
                        <?php endif; ?>

                        <?php if (!$isCurrentUser): ?>
                        <!-- Toggle attivo/disattivo -->
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_utenti_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="azione" value="toggle_attivo">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn-icon"
                                    title="<?= $u['attivo'] ? 'Disattiva utente' : 'Attiva utente' ?>">
                                <?= $u['attivo'] ? '🔒' : '🔓' ?>
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Modifica anagrafica (nome, email, telefono, note) -->
                        <button class="btn-icon" title="Modifica dati anagrafici"
                            onclick="apriModaleAnagrafica(
                                <?= $u['id'] ?>,
                                '<?= htmlspecialchars($u['username'],     ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($u['email']         ?? '', ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($u['nome_completo'] ?? '', ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($u['telefono']      ?? '', ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($u['note']          ?? '', ENT_QUOTES) ?>'
                            )">
                            ✏️
                        </button>

                        <!-- Reset password (admin su tutti, incluso se stesso) -->
                        <button class="btn-icon" title="Reimposta password"
                            onclick="apriModaleResetPwd(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')">
                            🔑
                        </button>

                        <!-- Gestione piano (piano, donazione, scadenza, override, accesso speciale) -->
                        <button class="btn-icon" title="Gestisci piano"
                            onclick="apriModalePiano(
                                <?= $u['id'] ?>,
                                '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($u['piano'] ?? 'free', ENT_QUOTES) ?>',
                                '<?= htmlspecialchars((string)($u['donazione_importo'] ?? ''), ENT_QUOTES) ?>',
                                '<?= htmlspecialchars((string)($u['supporter_inizio'] ?? ''), ENT_QUOTES) ?>',
                                '<?= htmlspecialchars((string)($u['supporter_scadenza'] ?? ''), ENT_QUOTES) ?>',
                                '<?= htmlspecialchars((string)($u['subjects_limit_override'] ?? ''), ENT_QUOTES) ?>',
                                <?= $u['accesso_speciale_permanente'] ? 'true' : 'false' ?>,
                                '<?= htmlspecialchars($u['note_piano'] ?? '', ENT_QUOTES) ?>'
                            )">
                            💎
                        </button>

                        <?php if (!$isCurrentUser): ?>
                        <!-- Cambia ruolo -->
                        <button class="btn-icon" title="Cambia ruolo"
                            onclick="apriModaleCambiaRuolo(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>', '<?= $u['ruolo'] ?>')">
                            ⚙️
                        </button>

                        <!-- Elimina -->
                        <?php if ($nSoggetti === 0): ?>
                        <form method="POST" style="display:inline"
                              onsubmit="return confirm('Eliminare definitivamente <?= htmlspecialchars($u['username'], ENT_QUOTES) ?>?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_utenti_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="azione" value="elimina">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="trasferisci_a" value="1">
                            <button type="submit" class="btn-icon" title="Elimina utente">🗑️</button>
                        </form>
                        <?php else: ?>
                        <button class="btn-icon" title="Elimina (trasferisci i <?= $nSoggetti ?> soggetti)"
                            onclick="apriModaleElimina(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>', <?= $nSoggetti ?>)">
                            🗑️
                        </button>
                        <?php endif; ?>
                        <?php endif; ?>

                    </div>
                </td>
            </tr>
            <tr id="soggetti-row-<?= $u['id'] ?>" style="display:none">
                <td colspan="7" style="background:#FAF7F1;padding:12px 20px">
                    <?php $soggettiRiga = $soggettiPerAstrologo[$u['id']] ?? []; ?>
                    <?php if (empty($soggettiRiga)): ?>
                    <p style="font-size:12px;color:#888;margin:0">Nessun soggetto di studio inserito.</p>
                    <?php else: ?>
                    <table class="tabella-soggetti" style="width:100%">
                        <thead>
                            <tr>
                                <th>Codice</th><th>Nome</th><th>Data Nascita</th>
                                <th>Ora</th><th>Luogo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($soggettiRiga as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['codice'] ?? '—') ?></td>
                            <td><b><?= htmlspecialchars($s['nome']) ?></b></td>
                            <td><?= $s['data_nascita'] ? date('d/m/Y', strtotime($s['data_nascita'])) : '—' ?></td>
                            <td><?= htmlspecialchars($s['ora_nascita'] ?? '—') ?></td>
                            <td>
                                <?= htmlspecialchars($s['luogo_nascita'] ?? '') ?>
                                <?= $s['nazione_nascita'] ? ' ' . htmlspecialchars($s['nazione_nascita']) : '' ?>
                                <?php if ($s['residenza_luogo']): ?>
                                <div style="color:#888;font-size:11px">🏠 <?= htmlspecialchars($s['residenza_luogo']) ?><?= $s['residenza_nazione'] ? ', ' . htmlspecialchars($s['residenza_nazione']) : '' ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p style="font-size:11px;color:#aaa;text-align:right;margin-top:8px">
        💡 I <strong>soggetti di studio</strong> sono le persone di cui si calcola il tema natale e la rivoluzione solare.
        Ogni utente gestisce i propri soggetti in modo indipendente.
    </p>
</main>

<!-- ════════════════════════════════════════════════════════════════ -->
<!--  MODALI                                                         -->
<!-- ════════════════════════════════════════════════════════════════ -->

<!-- ── Nuovo utente ────────────────────────────────────────────── -->
<div class="modal-bg" id="modal-nuovo">
    <div class="modal-box" style="max-width:520px">
        <h3>➕ Nuovo Utente</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_utenti_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="azione" value="crea">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
                <div class="form-group" style="grid-column:1/-1">
                    <label>Username *</label>
                    <input type="text" name="username" required autocomplete="off"
                           placeholder="Usato per il login">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Email *</label>
                    <input type="email" name="email" required autocomplete="off">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Password * (min 8 caratteri)</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           oninput="valutaForzaModal(this.value,'nuovo')">
                    <div class="pwd-strength"><div class="pwd-strength-fill" id="strength-nuovo"></div></div>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Ruolo</label>
                    <select name="ruolo">
                        <option value="user">👤 Utente</option>
                        <option value="admin">⚙️ Admin</option>
                    </select>
                </div>
            </div>

            <hr style="border:none;border-top:1px solid #EDE8E0;margin:12px 0">
            <div style="font-size:11px;color:#888;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.04em">
                Dati anagrafici — opzionali
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
                <div class="form-group" style="grid-column:1/-1">
                    <label>Nome Completo</label>
                    <input type="text" name="nome_completo" autocomplete="off"
                           placeholder="Es: Lorenzo Diana">
                </div>
                <div class="form-group">
                    <label>Telefono</label>
                    <input type="tel" name="telefono" autocomplete="off"
                           placeholder="+39 333 ...">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Note</label>
                    <textarea name="note" rows="2"
                              style="width:100%;border:1px solid #D0C8BC;border-radius:4px;
                                     padding:6px 10px;font-size:13px;font-family:inherit;
                                     resize:vertical"
                              placeholder="Note interne opzionali..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="chiudiModali()">Annulla</button>
                <button type="submit" class="btn-primary">Crea Utente</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Modifica anagrafica ──────────────────────────────────────── -->
<div class="modal-bg" id="modal-anagrafica">
    <div class="modal-box" style="max-width:520px">
        <h3 id="anagrafica-titolo">✏️ Modifica Dati Anagrafici</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_utenti_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="azione" value="modifica_anagrafica">
            <input type="hidden" name="id" id="anagrafica-id">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
                <div class="form-group" style="grid-column:1/-1">
                    <label>Email *</label>
                    <input type="email" name="email" id="anagrafica-email" required>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Nome Completo</label>
                    <input type="text" name="nome_completo" id="anagrafica-nome"
                           placeholder="Es: Lorenzo Diana">
                </div>
                <div class="form-group">
                    <label>Telefono</label>
                    <input type="tel" name="telefono" id="anagrafica-telefono"
                           placeholder="+39 333 ...">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Note</label>
                    <textarea name="note" id="anagrafica-note" rows="2"
                              style="width:100%;border:1px solid #D0C8BC;border-radius:4px;
                                     padding:6px 10px;font-size:13px;font-family:inherit;
                                     resize:vertical"
                              placeholder="Note interne opzionali..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="chiudiModali()">Annulla</button>
                <button type="submit" class="btn-primary">💾 Salva</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Reset password (admin) ──────────────────────────────────── -->
<div class="modal-bg" id="modal-reset-pwd">
    <div class="modal-box">
        <h3 id="reset-pwd-titolo">🔑 Reset Password</h3>
        <p style="font-size:12px;color:#888;margin-bottom:14px">
            Stai impostando una nuova password per conto dell'utente.<br>
            L'utente dovrà poi cambiarla dalla pagina "Cambia Password".
        </p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_utenti_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="azione" value="reset_password">
            <input type="hidden" name="id" id="reset-pwd-id">
            <div class="form-group">
                <label>Nuova password * (min 8 caratteri)</label>
                <input type="password" name="nuova_password" id="nuova-pwd-input"
                       required autocomplete="new-password"
                       oninput="valutaForzaModal(this.value,'reset')">
                <div class="pwd-strength"><div class="pwd-strength-fill" id="strength-reset"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="chiudiModali()">Annulla</button>
                <button type="submit" class="btn-primary">Aggiorna Password</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Cambia ruolo ─────────────────────────────────────────────── -->
<div class="modal-bg" id="modal-ruolo">
    <div class="modal-box">
        <h3 id="ruolo-titolo">⚙️ Cambia Ruolo</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_utenti_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="azione" value="cambia_ruolo">
            <input type="hidden" name="id" id="ruolo-id">
            <div class="form-group">
                <label>Nuovo ruolo</label>
                <select name="ruolo" id="ruolo-select">
                    <option value="user">👤 Utente</option>
                    <option value="admin">⚙️ Admin</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="chiudiModali()">Annulla</button>
                <button type="submit" class="btn-primary">Salva Ruolo</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Gestione piano (Free / Supporter) ────────────────────────── -->
<div class="modal-bg" id="modal-piano">
    <div class="modal-box" style="max-width:620px">
        <h3 id="piano-titolo">💎 Gestione Piano</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_utenti_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="azione" value="aggiorna_piano">
            <input type="hidden" name="id" id="piano-id">

            <div class="form-group" style="margin-bottom:10px">
                <label>Piano</label>
                <select name="piano_code" id="piano-code">
                    <option value="free">Free</option>
                    <option value="supporter">Supporter</option>
                </select>
            </div>

            <hr style="border:none;border-top:1px solid #EDE8E0;margin:12px 0">
            <div style="font-size:11px;color:#888;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.04em">
                Ciclo Supporter
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:10px">
                <div class="form-group">
                    <label>Donazione (€)</label>
                    <input type="number" step="0.01" min="0" name="donazione_importo" id="piano-donazione">
                </div>
                <div class="form-group">
                    <label>Inizio</label>
                    <input type="date" name="supporter_inizio" id="piano-inizio">
                </div>
                <div class="form-group">
                    <label>Scadenza</label>
                    <input type="date" name="supporter_scadenza" id="piano-scadenza">
                </div>
            </div>

            <hr style="border:none;border-top:1px solid #EDE8E0;margin:12px 0">
            <div style="font-size:11px;color:#888;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.04em">
                Personalizzazioni
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
                <div class="form-group">
                    <label>Limite soggetti personalizzato</label>
                    <input type="number" min="0" name="subjects_limit_override" id="piano-override"
                           placeholder="Vuoto = limite standard del piano">
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:8px">
                    <label style="display:flex;align-items:center;gap:6px;text-transform:none;font-size:13px;color:#444">
                        <input type="checkbox" name="accesso_speciale_permanente" id="piano-accesso-speciale" value="1">
                        Accesso completo, gratuito e permanente
                    </label>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Note amministrative</label>
                    <textarea name="note_piano" id="piano-note" rows="2"
                              style="width:100%;border:1px solid #D0C8BC;border-radius:4px;
                                     padding:6px 10px;font-size:13px;font-family:inherit;
                                     resize:vertical"
                              placeholder="Es: donazione ricevuta il ..., motivo accesso speciale..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="chiudiModali()">Annulla</button>
                <button type="submit" class="btn-primary">💾 Salva Piano</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Elimina con trasferimento ────────────────────────────────── -->
<div class="modal-bg" id="modal-elimina">
    <div class="modal-box">
        <h3 id="elimina-titolo">🗑️ Elimina Utente</h3>
        <p id="elimina-info" style="font-size:13px;color:#666;margin-bottom:16px"></p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_utenti_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="azione" value="elimina">
            <input type="hidden" name="id" id="elimina-id">
            <div class="form-group">
                <label>Trasferisci i soggetti a</label>
                <select name="trasferisci_a">
                    <?php foreach ($utenti as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="chiudiModali()">Annulla</button>
                <button type="submit" class="btn-primary" style="background:#C62828">Elimina definitivamente</button>
            </div>
        </form>
    </div>
</div>

<script>
function apriModaleNuovoUtente() {
    document.getElementById('modal-nuovo').classList.add('vis');
}
function apriModaleAnagrafica(id, username, email, nomeCompleto, telefono, note) {
    document.getElementById('anagrafica-titolo').textContent = '✏️ Modifica — ' + username;
    document.getElementById('anagrafica-id').value       = id;
    document.getElementById('anagrafica-email').value    = email;
    document.getElementById('anagrafica-nome').value     = nomeCompleto;
    document.getElementById('anagrafica-telefono').value = telefono;
    document.getElementById('anagrafica-note').value     = note;
    document.getElementById('modal-anagrafica').classList.add('vis');
}
function apriModaleResetPwd(id, nome) {
    document.getElementById('reset-pwd-titolo').textContent = '🔑 Reset Password — ' + nome;
    document.getElementById('reset-pwd-id').value = id;
    document.getElementById('nuova-pwd-input').value = '';
    document.getElementById('strength-reset').style.width = '0';
    document.getElementById('modal-reset-pwd').classList.add('vis');
}
function toggleSoggettiAstrologo(id) {
    const riga = document.getElementById('soggetti-row-' + id);
    const arrow = document.getElementById('arrow-' + id);
    if (!riga) return;
    const aperta = riga.style.display !== 'none';
    riga.style.display = aperta ? 'none' : 'table-row';
    if (arrow) arrow.textContent = aperta ? '▸' : '▾';
}
function apriModalePiano(id, username, piano, donazione, inizio, scadenza, override, accessoSpeciale, note) {
    document.getElementById('piano-titolo').textContent = '💎 Gestione Piano — ' + username;
    document.getElementById('piano-id').value = id;
    document.getElementById('piano-code').value = piano || 'free';
    document.getElementById('piano-donazione').value = donazione || '';
    document.getElementById('piano-inizio').value = inizio || '';
    document.getElementById('piano-scadenza').value = scadenza || '';
    document.getElementById('piano-override').value = override || '';
    document.getElementById('piano-accesso-speciale').checked = !!accessoSpeciale;
    document.getElementById('piano-note').value = note || '';
    document.getElementById('modal-piano').classList.add('vis');
}
function apriModaleCambiaRuolo(id, nome, ruoloAttuale) {
    document.getElementById('ruolo-titolo').textContent = '⚙️ Cambia Ruolo — ' + nome;
    document.getElementById('ruolo-id').value = id;
    document.getElementById('ruolo-select').value = ruoloAttuale;
    document.getElementById('modal-ruolo').classList.add('vis');
}
function apriModaleElimina(id, nome, nSoggetti) {
    document.getElementById('elimina-titolo').textContent = '🗑️ Elimina — ' + nome;
    document.getElementById('elimina-info').innerHTML =
        '<strong>' + nome + '</strong> ha <strong>' + nSoggetti + '</strong> soggett' +
        (nSoggetti === 1 ? 'o' : 'i') + ' di studio.' +
        '<br>Prima di eliminare l\'utente, scegli a chi trasferire i soggetti.';
    document.getElementById('elimina-id').value = id;
    document.getElementById('modal-elimina').classList.add('vis');
}
function chiudiModali() {
    document.querySelectorAll('.modal-bg').forEach(m => m.classList.remove('vis'));
}

// Chiudi cliccando fuori dal box
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-bg')) chiudiModali();
});

// Indicatore forza password
function valutaForzaModal(pwd, id) {
    const fill = document.getElementById('strength-' + id);
    if (!fill) return;
    let score = 0;
    if (pwd.length >= 8)  score++;
    if (pwd.length >= 12) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;
    const colori = ['#EDE8E0','#F44336','#FF9800','#FFC107','#4CAF50','#2E7D32'];
    fill.style.width      = (score * 20) + '%';
    fill.style.background = colori[score] || '#EDE8E0';
}
</script>
</body>
</html>
