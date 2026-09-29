<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * cambio_password_soggetto.php - Cambio password del soggetto
 * docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md, blocco B (C11).
 * Obbligatorio al primo accesso (password provvisoria dell'astrologo).
 */
require_once __DIR__ . '/includes/Auth.php';

$pdo  = db_connect();
$auth = new Auth($pdo);
$soggetto = $auth->richiediLoginSoggetto(true);

header('X-Robots-Tag: noindex, nofollow');

if (empty($_SESSION['csrf_soggetto'])) {
    $_SESSION['csrf_soggetto'] = bin2hex(random_bytes(32));
}

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attuale  = (string)($_POST['password_attuale'] ?? '');
    $nuova    = (string)($_POST['password_nuova'] ?? '');
    $conferma = (string)($_POST['password_conferma'] ?? '');

    if (!hash_equals($_SESSION['csrf_soggetto'], (string)($_POST['csrf_token'] ?? ''))) {
        $errore = 'Sessione scaduta: ricarica la pagina e riprova.';
    } elseif ($nuova !== $conferma) {
        $errore = 'Le nuove password non coincidono.';
    } else {
        $result = $auth->cambiaPasswordSoggetto((int)$soggetto['soggetto_id'], $attuale, $nuova);
        if ($result['ok']) {
            header('Location: area_soggetto.php?password=1');
            exit;
        }
        $errore = $result['errore'];
    }
}

$primoAccesso = !empty($soggetto['deve_cambiare_password']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Cambio password - AstroLab</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #FFFFFF; }
        .sogg-box { background: #fff; border-radius: 10px; box-shadow: 0 4px 24px rgba(44,62,107,0.15); padding: 36px 44px; width: 100%; max-width: 420px; }
        .sogg-box h1 { font-family: 'Eb Garamond', Georgia, serif; color: #2C3E6B; font-size: 30px; margin: 0 0 6px; text-align: center; }
        .sogg-sub { text-align: center; color: #555; font-size: 14px; margin-bottom: 22px; }
        .sogg-errore { background: #FDECEA; color: #8A1C1C; border-radius: 6px; padding: 10px 12px; font-size: 14px; margin-bottom: 16px; }
        .sogg-box .form-group { margin-bottom: 14px; }
        .sogg-box input { width: 100%; box-sizing: border-box; }
        .sogg-btn { width: 100%; padding: 11px; border: 0; border-radius: 6px; background: #2C3E6B; color: #fff; font-size: 15px; cursor: pointer; }
        .sogg-link { text-align: center; font-size: 13px; margin-top: 18px; }
    </style>
</head>
<body>
    <div class="sogg-box">
        <h1>Cambio password</h1>
        <div class="sogg-sub">
            <?= htmlspecialchars($soggetto['codice'], ENT_QUOTES, 'UTF-8') ?>
            <?php if ($primoAccesso): ?>
                <br>Primo accesso: scegli una tua password personale.
            <?php endif; ?>
        </div>

        <?php if ($errore): ?>
        <div class="sogg-errore"><?= htmlspecialchars($errore, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="cambio_password_soggetto.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_soggetto'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="password_attuale"><?= $primoAccesso ? 'Password provvisoria' : 'Password attuale' ?></label>
                <input id="password_attuale" type="password" name="password_attuale" autocomplete="current-password" required autofocus>
            </div>
            <div class="form-group">
                <label for="password_nuova">Nuova password (almeno 8 caratteri)</label>
                <input id="password_nuova" type="password" name="password_nuova" autocomplete="new-password" minlength="8" required>
            </div>
            <div class="form-group">
                <label for="password_conferma">Conferma nuova password</label>
                <input id="password_conferma" type="password" name="password_conferma" autocomplete="new-password" minlength="8" required>
            </div>
            <button type="submit" class="sogg-btn">Salva la nuova password</button>
        </form>

        <div class="sogg-link">
            <?php if (!$primoAccesso): ?><a href="area_soggetto.php">Torna alla tua area</a> &middot; <?php endif; ?>
            <a href="logout.php">Esci</a>
        </div>
    </div>
</body>
</html>
