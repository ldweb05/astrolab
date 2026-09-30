<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * area_soggetto.php - Area riservata del soggetto (accesso con il CODICE)
 * docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md, blocco B (B4) e
 * docs/roadmaps/ROADMAP_DIARIO_RSM.md, Fase 3d: pagina iniziale del soggetto con
 * l'accesso al Diario RSM (diario.php). Nessun dato di nascita o astrologico.
 */
require_once __DIR__ . '/includes/Auth.php';

$pdo  = db_connect();
$auth = new Auth($pdo);
$soggetto = $auth->richiediLoginSoggetto();

header('X-Robots-Tag: noindex, nofollow');

$passwordCambiata = isset($_GET['password']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>La tua area - AstroLab</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #FFFFFF; }
        .sogg-box { background: #fff; border-radius: 10px; box-shadow: 0 4px 24px rgba(44,62,107,0.15); padding: 36px 44px; width: 100%; max-width: 460px; text-align: center; }
        .sogg-box h1 { font-family: 'Eb Garamond', Georgia, serif; color: #2C3E6B; font-size: 32px; margin: 0 0 8px; }
        .sogg-codice { font-size: 20px; font-weight: 600; color: #2C3E6B; letter-spacing: 1px; margin-bottom: 18px; }
        .sogg-ok { background: #E8F5E9; color: #1B5E20; border-radius: 6px; padding: 10px 12px; font-size: 14px; margin-bottom: 16px; }
        .sogg-testo { color: #444; font-size: 15px; line-height: 1.5; margin-bottom: 22px; }
        .sogg-link { font-size: 13px; }
        .sogg-btn { display: inline-block; background: #2C3E6B; color: #fff; text-decoration: none; border-radius: 6px;
            padding: 12px 22px; font-size: 16px; margin-bottom: 22px; }
        .sogg-btn:hover { background: #22325A; }
    </style>
</head>
<body>
    <div class="sogg-box">
        <h1>Benvenuto</h1>
        <div class="sogg-codice"><?= htmlspecialchars($soggetto['codice'], ENT_QUOTES, 'UTF-8') ?></div>

        <?php if ($passwordCambiata): ?>
        <div class="sogg-ok">Password aggiornata.</div>
        <?php endif; ?>

        <div class="sogg-testo">
            Nel tuo Diario RSM puoi salvare i viaggi fatti per le tue Rivoluzioni Solari Mirate
            e consultare i consigli di viaggio condivisi dagli altri.
        </div>

        <a href="diario.php" class="sogg-btn">&#9992;&#65039; Apri il tuo Diario RSM</a>

        <div class="sogg-link">
            <a href="cambio_password_soggetto.php">Cambia password</a> &middot;
            <a href="logout.php">Esci</a>
        </div>
    </div>
</body>
</html>
