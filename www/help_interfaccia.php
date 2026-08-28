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
    <title>7. Interfaccia e Visualizzazione — Manuale AstroLab</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #F2EDE4; }
        .help-main { max-width: 780px; margin: 30px auto; padding: 0 20px 60px; }
        .help-header { border-bottom: 2px solid #2C3E6B; padding-bottom: 16px; margin-bottom: 28px; }
        .help-header h1 { color: #2C3E6B; font-size: 22px; margin: 0; }
        .help-header .back-link { font-size: 12px; color: #888; text-decoration: none; display: inline-block; margin-top: 6px; }
        .help-header .back-link:hover { color: #2C3E6B; }
        .help-section { margin-bottom: 32px; }
        .help-section h2 { color: #2C3E6B; font-size: 17px; margin-bottom: 10px; border-left: 3px solid #D4C9A8; padding-left: 12px; }
        .help-section p, .help-section li { color: #444; font-size: 14px; line-height: 1.7; }
        .help-section ul, .help-section ol { padding-left: 22px; }
        .help-section li { margin-bottom: 6px; }
        .help-note { background: #FFF8ED; border: 1px solid #E8DCC8; border-radius: 6px; padding: 14px 18px; font-size: 13px; color: #6B5D3E; margin-top: 12px; }
        .help-supporter { color: #6B5D3E; font-weight: 600; }
    </style>
    <script>document.addEventListener('contextmenu', function(e){ e.preventDefault(); });</script>
</head>
<body oncontextmenu="return false;">
<div class="help-main">
    <div class="help-header">
        <h1>📖 7. Interfaccia e Visualizzazione</h1>
        <a class="back-link" href="javascript:window.close()">← Chiudi questa pagina</a>
    </div>

    <div class="help-section">
        <h2>La Ruota Zodiacale e la codifica colore</h2>
        <p>Nella ruota (Tema Natale, RS, RL, Rilocazione) ogni pianeta è colorato in base al suo stato:</p>
        <ul>
            <li>🔴 <strong>Rosso</strong> — pianeta diretto.</li>
            <li>🔵 <strong>Blu</strong> — pianeta retrogrado.</li>
            <li>🟢 <strong>Verde</strong> — pianeta in cuspide (vicino al confine di una Casa).</li>
        </ul>
    </div>

    <div class="help-section">
        <h2>Navigazione globale, menu e responsive design</h2>
        <p>Il menu principale in alto ti porta a: <strong>Soggetti</strong>, <strong>Tema Natale</strong>, il menu a tendina <strong>Rivoluzioni</strong> (RS / RL / Rilocazione), <strong>Ricerca Località</strong>, il menu <strong>Help</strong> con le 8 sezioni del manuale, e — solo per l'amministratore — <strong>Gestione Utenti</strong>. In alto a destra trovi il tuo username, <strong>Password</strong> e <strong>Esci</strong>.</p>
        <p>L'interfaccia è responsive: si adatta automaticamente a schermi più piccoli (tablet e smartphone), riorganizzando colonne dei form e spaziatura del contenuto.</p>
    </div>
</div>
</body>
</html>
