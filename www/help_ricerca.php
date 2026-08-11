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
        <h1>📖 4. Ricerca Geografica Avanzata</h1>
        <a class="back-link" href="javascript:window.close()">← Chiudi questa pagina</a>
    </div>

    <div class="help-section">
        <h2>Ricerca Migliori Località (RSM v3)</h2>
        <p>Dalla pagina "Ricerca Località" imposta <strong>Soggetto</strong>, <strong>Anno RS</strong>, <strong>Condizione</strong> (il tema su cui valutare stelline e punteggio) e <strong>Tipo località</strong>:</p>
        <ul>
            <li><strong>Aeroporti</strong> — disponibile per tutti i piani.</li>
            <li><strong>Località</strong> (ricerca libera per nazione, non limitata agli aeroporti) — <span class="help-supporter">riservata al piano Supporter</span>.</li>
        </ul>
        <p>La ricerca ha tre modalità:</p>
        <ul>
            <li><strong>Standard</strong>: valuta la condizione tematica generale sulle diverse località candidate.</li>
            <li><strong>Longitudine Cuspidi</strong>: cerca dove una specifica Casa della RS cade su un segno/grado/minuto target, con tolleranza configurabile (± gradi e ± minuti).</li>
            <li><strong>Astri nelle Case</strong>: costruisci le tue regole (es. "Voglio Venere in Casa 7", "Non voglio Marte in Casa 12") — puoi combinarne più di una, applicate tutte insieme.</li>
        </ul>
    </div>

    <div class="help-section">
        <h2>Filtri avanzati</h2>
        <p>Apri il pannello "⚙️ Filtri avanzati" per affinare la ricerca:</p>
        <ul>
            <li><strong>Geografici</strong> — Macro-Area/Regione (limita a un continente), Fascia Oraria/Longitudine (per contenere il jet-lag).</li>
            <li><strong>Tecnici</strong> — tipo Aeroporti (Grandi+Medi / Solo IATA / Tutti), esclusione basi Militari, Stelline minime, Importanza aeroporto, Allargamento automatico dell'orbe se la ricerca non trova risultati.</li>
            <li><strong>Tolleranza dinamica dell'Orbe</strong> — <span class="help-supporter">riservata al piano Supporter</span>.</li>
        </ul>
    </div>

    <div class="help-section">
        <h2>Ricerca a Griglia Geometrica <span class="help-supporter">(Supporter)</span></h2>
        <p>Invece di limitarsi ad aeroporti o località esistenti, scansiona una griglia fissa di coordinate geografiche — utile quando la configurazione desiderata cade in zone remote. Supporta tutte e tre le modalità (Standard, Cuspidi, Astri nelle Case).</p>
        <div class="help-note">⏱️ Un passo di griglia molto fine (es. 0.5°) può richiedere diversi minuti di calcolo.</div>
    </div>

    <div class="help-section">
        <h2>Risultati e confronto</h2>
        <p>Ogni risultato mostra stelline e punteggio (VAL). Puoi selezionare più località e premere "Confronta le selezioni" per metterle a confronto diretto.</p>
        <div class="help-note">💎 Il piano Free consente di confrontare fino a 2 risultati contemporaneamente. Con il piano Supporter puoi arrivare a confrontarne 3. Trovi tutti i dettagli sul Comparatore nella Sezione 6.</div>
    </div>
</div>
</body>
</html>
