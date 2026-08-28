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
    <title>2. Gestione Soggetti — Manuale AstroLab</title>
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
        <h1>📖 2. Gestione Soggetti</h1>
        <a class="back-link" href="javascript:window.close()">← Chiudi questa pagina</a>
    </div>

    <div class="help-section">
        <h2>Cos'è un Soggetto di Studio</h2>
        <p>Un Soggetto di Studio è la persona di cui calcoli tema natale, rivoluzioni solari e lunari, rilocazioni. Ogni Astrologo gestisce i propri soggetti in modo indipendente: nessun altro può vederli o modificarli, a meno che l'amministratore non intervenga dall'amministrazione.</p>
    </div>

    <div class="help-section">
        <h2>Inserimento di un nuovo soggetto</h2>
        <p>Dal pulsante "+ Nuovo Soggetto" compila:</p>
        <ul>
            <li><strong>Nome e Cognome</strong> — obbligatorio.</li>
            <li><strong>Codice</strong> (opzionale) — un tuo codice identificativo personale, es. "MR001".</li>
            <li><strong>Data di Nascita</strong> e <strong>Ora Locale</strong> — obbligatori. Puoi indicare anche Ora GMT / Offset GMT se li conosci già.</li>
            <li><strong>Luogo di Nascita</strong> — cerca la città: latitudine, longitudine, paese e fuso orario si compilano automaticamente.</li>
            <li><strong>Città di Residenza</strong> (opzionale) — stesso meccanismo di ricerca, utile per calcoli di rilocazione.</li>
            <li><strong>Note</strong> (opzionale) — annotazioni libere.</li>
        </ul>
    </div>

    <div class="help-section">
        <h2>Modifica di un soggetto esistente</h2>
        <p>Dalla tabella, l'icona ✏️ apre lo stesso modulo con i dati già compilati: modifica quello che serve e salva.</p>
    </div>

    <div class="help-section">
        <h2>Il soggetto attivo</h2>
        <p>In alto trovi il selettore "⭐ Soggetto attivo": scegli qui il soggetto su cui vuoi lavorare, e resterà impostato in tutte le pagine (Tema Natale, Rivoluzione Solare, ecc.) finché non lo cambi.</p>
    </div>

    <div class="help-section">
        <h2>Azioni sulla tabella</h2>
        <p>Per ogni soggetto: ⭐ imposta come attivo, <strong>TN</strong> calcola il Tema Natale, <strong>RS</strong> calcola la Rivoluzione Solare, ✏️ modifica, 🗑️ elimina.</p>
    </div>

    <div class="help-section">
        <h2>Limiti per piano</h2>
        <ul>
            <li><strong>Free</strong>: numero limitato di soggetti.</li>
            <li><strong>Supporter</strong>: limite più ampio, in base al piano concordato.</li>
        </ul>
        <p>Contatta l'amministratore se hai raggiunto il limite e vuoi valutare un aggiornamento del tuo piano.</p>
        <div class="help-note">💡 Se hai raggiunto il numero massimo di soggetti previsto dal tuo piano, non potrai aggiungerne altri finché non elimini un soggetto esistente o il tuo piano non viene aggiornato.</div>
    </div>
</div>
</body>
</html>
