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
    <title>3. Calcoli e Analisi — Manuale AstroLab</title>
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
    </style>
    <script>document.addEventListener('contextmenu', function(e){ e.preventDefault(); });</script>
</head>
<body oncontextmenu="return false;">
<div class="help-main">
    <div class="help-header">
        <h1>📖 3. Calcoli e Analisi</h1>
        <a class="back-link" href="javascript:window.close()">← Chiudi questa pagina</a>
    </div>

    <div class="help-section">
        <h2>Tema Natale</h2>
        <p>Seleziona il soggetto attivo (dalla pagina Soggetti o dal selettore in alto), poi apri "Tema Natale" dal menu. La pagina mostra automaticamente la ruota zodiacale e le Case (metodo Placido), insieme ai dati di nascita del soggetto. I pulsanti "Mostra/Nascondi Cuspidi" e "Mostra/Nascondi Gradi" personalizzano la visualizzazione. "🖨️ Stampa Tema Natale" esporta la pagina.</p>
    </div>

    <div class="help-section">
        <h2>Rivoluzione Solare (RS)</h2>
        <ul>
            <li>Scegli <strong>Anno RS</strong> e <strong>Luogo RS</strong> (cerca la città o modifica manualmente Lat/Lon), poi "↺ Calcola RS".</li>
            <li><strong>🌍 Mappa</strong>: trascina il marker per ricalcolare la RS in tempo reale su una posizione diversa.</li>
            <li><strong>📖 Relazione Annuale</strong>: genera la narrazione testuale dell'anno.</li>
            <li><strong>💾 Salva questa RS</strong>: salva la sessione con una nota personale; la ritrovi in "📂 Sessioni RS salvate per questo soggetto" (modificabile o cancellabile).</li>
            <li><strong>🖨️ Stampa / PDF Report</strong> per l'esportazione.</li>
        </ul>
    </div>

    <div class="help-section">
        <h2>Rivoluzione Lunare (RL)</h2>
        <ul>
            <li>Scegli l'<strong>Anno RS di riferimento</strong> e il <strong>Luogo RL</strong>, poi "☽ Calcola RL".</li>
            <li>Puoi collegare la RL a una sessione RS già salvata (opzionale).</li>
            <li>Stessa logica della Rivoluzione Solare per mappa, salvataggio con nota e stampa.</li>
        </ul>
    </div>

    <div class="help-section">
        <h2>Rilocazioni</h2>
        <ul>
            <li>Cerca la <strong>Città di Rilocazione</strong> (o inserisci Lat/Lon manualmente), poi "☿ Calcola Rilocazione".</li>
            <li><strong>Confronto Case — Natale vs Rilocazione</strong>: confronta le case natali con quelle rilocate.</li>
            <li><strong>♀♃ Cerca Luoghi — Venere o Giove sulle Cuspidi Angolari</strong>: ricerca automatica di località dove questi pianeti cadono sulle cuspidi angolari, con filtri (tolleranza in gradi, aeroporti, basi militari, nazione).</li>
            <li><strong>🌍 Mappa</strong>: trascina il marker per ricalcolare in tempo reale.</li>
            <li><strong>🖨️ Stampa Rilocazione</strong> per l'esportazione.</li>
        </ul>
        <div class="help-note">💡 In tutte queste pagine il Tema Natale del soggetto resta visibile accanto al nuovo calcolo, per un confronto immediato.</div>
    </div>
</div>
</body>
</html>
