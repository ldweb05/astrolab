<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * cambia_password.php — Cambio password per l'utente autenticato
 * Accessibile a tutti (admin e astrologi)
 * Richiede la password attuale come verifica di sicurezza
 */
session_start();
require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();

$isAdmin      = $auth->isAdmin();
$username     = $auth->getCurrentUsername();
$soggettoNome = $auth->getSoggettoNome();

$messaggio = '';
$tipoMsg   = '';

if (empty($_SESSION['cambia_password_csrf'])) {
    $_SESSION['cambia_password_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf_token'] ?? '');
    $vecchia  = $_POST['password_attuale']  ?? '';
    $nuova    = $_POST['nuova_password']    ?? '';
    $conferma = $_POST['conferma_password'] ?? '';

    if (!hash_equals((string)$_SESSION['cambia_password_csrf'], $csrf)) {
        $messaggio = 'Sessione non valida. Ricarica la pagina e riprova.';
        $tipoMsg = 'error';
    } elseif ($nuova !== $conferma) {
        $messaggio = 'La nuova password e la conferma non coincidono.';
        $tipoMsg   = 'error';
    } elseif (strlen($nuova) < 8) {
        $messaggio = 'La nuova password deve essere di almeno 8 caratteri.';
        $tipoMsg   = 'error';
    } else {
        $result    = $auth->cambiaPropriaPassword($vecchia, $nuova);
        $messaggio = $result['ok'] ? 'Password aggiornata con successo.' : $result['errore'];
        $tipoMsg   = $result['ok'] ? 'success' : 'error';
    }
}

$paginaAttiva = ''; // nessuna voce nav attiva
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambia Password — Astrologia Attiva</title>
    <link rel="stylesheet" href="css/style.css">
    
</head>
<body>

<?php include 'includes/header_nav.php'; ?>

<main class="pwd-page-main">
    <div class="pwd-box">
        <h2>🔑 Cambia Password</h2>
        <p class="subtitle">Utente: <strong><?= htmlspecialchars($username) ?></strong>
            <?php if ($isAdmin): ?><span style="color:#2C3E6B;font-size:10px"> (admin)</span><?php endif; ?>
        </p>

        <?php if ($messaggio): ?>
        <div class="<?= $tipoMsg === 'success' ? 'msg-success' : 'msg-error' ?>" style="margin-bottom:18px">
            <?= $tipoMsg === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($messaggio) ?>
        </div>
        <?php endif; ?>

        <?php if ($tipoMsg !== 'success'): ?>
        <form method="POST" action="cambia_password.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['cambia_password_csrf'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label>Password Attuale *</label>
                <input type="password" name="password_attuale"
                       required autocomplete="current-password"
                       placeholder="Inserisci la password corrente">
            </div>

            <hr class="pwd-separator">

            <div class="form-group">
                <label>Nuova Password *</label>
                <input type="password" name="nuova_password" id="nuova-pwd"
                       required autocomplete="new-password" minlength="8"
                       placeholder="Almeno 8 caratteri"
                       oninput="valutaForza(this.value)">
                <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
                <div class="strength-label" id="strength-label"></div>
            </div>

            <div class="form-group">
                <label>Conferma Nuova Password *</label>
                <input type="password" name="conferma_password" id="conferma-pwd"
                       required autocomplete="new-password"
                       placeholder="Ripeti la nuova password"
                       oninput="verificaConferma()">
                <div class="pwd-hint" id="conferma-hint"></div>
            </div>

            <div class="pwd-actions">
                <a href="index.php" class="btn-secondary" style="text-decoration:none;padding:8px 18px;display:inline-block">Annulla</a>
                <button type="submit" class="btn-primary">💾 Aggiorna Password</button>
            </div>
        </form>
        <?php else: ?>
        <div style="text-align:center;padding:16px 0">
            <a href="index.php" class="btn-primary" style="text-decoration:none">← Torna ai Soggetti</a>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
function valutaForza(pwd) {
    const fill  = document.getElementById('strength-fill');
    const label = document.getElementById('strength-label');
    if (!fill) return;

    let score = 0;
    if (pwd.length >= 8)  score++;
    if (pwd.length >= 12) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;

    const livelli = [
        { perc:  0,  colore: '#EDE8E0', testo: '' },
        { perc: 20,  colore: '#F44336', testo: 'Molto debole' },
        { perc: 40,  colore: '#FF9800', testo: 'Debole' },
        { perc: 60,  colore: '#FFC107', testo: 'Accettabile' },
        { perc: 80,  colore: '#4CAF50', testo: 'Buona' },
        { perc: 100, colore: '#2E7D32', testo: 'Ottima' },
    ];

    const lv = livelli[score] || livelli[0];
    fill.style.width      = lv.perc + '%';
    fill.style.background = lv.colore;
    label.textContent     = lv.testo;
    label.style.color     = lv.colore;
}

function verificaConferma() {
    const nuova    = document.getElementById('nuova-pwd')?.value     || '';
    const conferma = document.getElementById('conferma-pwd')?.value  || '';
    const hint     = document.getElementById('conferma-hint');
    if (!hint || !conferma) return;

    if (nuova === conferma) {
        hint.textContent = '✓ Le password coincidono';
        hint.style.color = '#4CAF50';
    } else {
        hint.textContent = '✗ Le password non coincidono';
        hint.style.color = '#F44336';
    }
}
</script>
</body>
</html>
