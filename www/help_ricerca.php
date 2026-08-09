<?php
require_once __DIR__ . '/includes/bootstrap.php';
session_start();
require_once 'includes/Auth.php';
$auth = new Auth(db_connect());
$auth->richiediLogin();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4. Ricerca Geografica Avanzata — Manuale AstroLab</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #F2EDE4; }
        .help-main { max-width: 780px; margin: 30px auto; padding: 0 20px 60px; }
        .help-header { border-bottom: 2px solid #2C3E6B; padding-bottom: 16px; margin-bottom: 28px; }
        .help-header h1 { color: #2C3E6B; font-size: 22px; margin: 0; }
        .help-header .back-link { font-size: 12px; color: #888; text-decoration: none; display: inline-block; margin-top: 6px; }
        .help-header .back-link:hover { color: #2C3E6B; }
        .help-section { margin-bottom: 32px; }
        .help-section p { color: #444; font-size: 14px; line-height: 1.7; }
        .help-placeholder { background: #FFF8ED; border: 1px solid #E8DCC8; border-radius: 6px; padding: 24px; text-align: center; color: #6B5D3E; font-size: 15px; }
    </style>
    <script>document.addEventListener('contextmenu', function(e){ e.preventDefault(); });</script>
</head>
<body oncontextmenu="return false;">
<div class="help-main">
    <div class="help-header">
        <h1>📖 4. Ricerca Geografica Avanzata</h1>
        <a class="back-link" href="javascript:window.close()">← Chiudi questa pagina</a>
    </div>
    <div class="help-section">
        <div class="help-placeholder">
            🚧 Questa sezione è in fase di redazione.<br><br>
            Ricerca RSM v3 (Aeroporti e Località mondiali) e ricerca "Astri nelle Case".<br><br>
            Torna più tardi per consultare la guida completa.
        </div>
    </div>
</div>
</body>
</html>
