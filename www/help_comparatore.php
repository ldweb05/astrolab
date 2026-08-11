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
    <title>6. Comparatore e DSS — Manuale AstroLab</title>
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
        <h1>📖 6. Comparatore e DSS</h1>
        <a class="back-link" href="javascript:window.close()">← Chiudi questa pagina</a>
    </div>

    <div class="help-section">
        <h2>Comparatore (Comparator RS e Rilocazioni)</h2>
        <p>Dalla pagina "Ricerca Località", seleziona più risultati (2 o 3 RSM) e premi "Confronta le selezioni": si apre il Comparatore, che affianca in tempo reale le ruote (RS o Rilocazione) delle località scelte, con le rispettive tabelle, per visualizzare subito le differenze tra le RSM messe a confronto.</p>
        <div class="help-note">💎 Il piano Free consente di confrontare fino a 2 risultati contemporaneamente. Con il piano Supporter puoi arrivare a confrontarne 3.</div>
    </div>

    <div class="help-section">
        <h2>Comprendere Rule ed Evidenze (stelline e veto)</h2>
        <p>Ogni risultato di ricerca mostra un punteggio a stelline (★): più stelle indicano una configurazione più favorevole secondo le regole dell'Astrologia Attiva (metodo Ciro Discepolo). Quando un risultato presenta condizioni sfavorevoli, compare un badge "⛔ N veto": cliccalo per aprire il pannello "⚖️ Perché questo punto non è valido", che elenca nel dettaglio le regole che hanno determinato l'esclusione.</p>
    </div>
</div>
</body>
</html>
