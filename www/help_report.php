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
    <title>5. Report, Narrazione e Stampa — Manuale AstroLab</title>
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
        <h1>📖 5. Report, Narrazione e Stampa</h1>
        <a class="back-link" href="javascript:window.close()">← Chiudi questa pagina</a>
    </div>

    <div class="help-section">
        <h2>Come leggere l'Annual Report (Relazione Annuale)</h2>
        <p>Dalla pagina Rivoluzione Solare, dopo aver calcolato la RS, apri "📖 Relazione Annuale". Il report è organizzato in più sezioni tematiche, generate automaticamente evidenziando i temi più significativi dell'anno per il soggetto. Puoi stampare la Relazione Annuale singolarmente con l'icona 🖨️ nella finestra, oppure chiuderla con ×.</p>
    </div>

    <div class="help-section">
        <h2>Theme Engine e Narrative Engine</h2>
        <p>Dietro le quinte, AstroLab non si limita a calcolare le posizioni planetarie: valuta un ranking dei temi di vita più rilevanti per l'anno e li trasforma in un testo narrativo leggibile, organizzato in sezioni. Il testo è generato automaticamente a partire dalle regole e dalle evidenze astrologiche rilevate, senza bisogno di interpretazione manuale.</p>
    </div>

    <div class="help-section">
        <h2>Esportazione e Stampa PDF</h2>
        <p>Dalla pagina "📄 Report Astrologico — Stampa / PDF":</p>
        <ul>
            <li>Scegli i <strong>Moduli da includere</strong>: Tema Natale, Rivoluzione Solare (RSM), Rivoluzione Lunare, Rilocazione — ognuno con i propri parametri (anno, luogo, ecc.).</li>
            <li>Scegli il <strong>Formato pagina</strong> (A4/A3, verticale/orizzontale); se selezioni insieme Tema Natale e RS, il layout passa automaticamente in orizzontale per affiancare le due ruote.</li>
            <li><strong>🖨️ Stampa da Browser</strong>: usa la funzione di stampa del browser (imposta i margini su "Nessuno" per il layout ottimale).</li>
            <li><strong>⬇️ Scarica PDF</strong>: genera un PDF nativo lato server.</li>
        </ul>
        <div class="help-note">💎 Il piano Free include un numero limitato di esportazioni della Relazione Annuale al mese (stampa o PDF); il piano Supporter non ha questo limite.</div>
    </div>
</div>
</body>
</html>
