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
    <title>1. Introduzione e Account — Manuale AstroLab</title>
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
        <h1>📖 1. Introduzione e Account</h1>
        <a class="back-link" href="javascript:window.close()">← Chiudi questa pagina</a>
    </div>

    <div class="help-section">
        <h2>Benvenuto in AstroLab</h2>
        <p>AstroLab è la piattaforma professionale di astrologia attiva ispirata al metodo Ciro Discepolo. Qui puoi calcolare temi natali, rivoluzioni solari e lunari, rilocazioni, effettuare ricerche geografiche avanzate e utilizzare il comparatore per il supporto decisionale.</p>
    </div>

    <div class="help-section">
        <h2>Piani di abbonamento</h2>
        <ul>
            <li><strong>Free</strong>: accesso base alle funzionalità principali, con limiti sul numero di soggetti salvati e ricerche effettuabili.</li>
            <li><strong>Supporter</strong>: accesso completo, soggetti illimitati, ricerche avanzate, comparatore e priorità di supporto.</li>
        </ul>
        <div class="help-note">💡 Per aggiornare il tuo piano o gestire l'abbonamento, contatta l'amministratore.</div>
    </div>

    <div class="help-section">
        <h2>Accesso (Login)</h2>
        <p>Per accedere inserisci le tue credenziali nella pagina di login:</p>
        <ul>
            <li><strong>Username</strong>: il nome utente scelto in fase di registrazione.</li>
            <li><strong>Password</strong>: la password segreta associata al tuo account.</li>
        </ul>
        <p>Dopo l'accesso verrai reindirizzato alla dashboard principale. Se hai dimenticato la password, contatta l'amministratore per il ripristino.</p>
        <div class="help-note">🔒 <strong>Sicurezza</strong>: dopo 10 tentativi falliti in 15 minuti, l'accesso viene temporaneamente bloccato per proteggere il tuo account.</div>
    </div>

    <div class="help-section">
        <h2>Registrazione nuovo account</h2>
        <p>Per creare un nuovo account compila il modulo di registrazione con:</p>
        <ul>
            <li><strong>Username</strong>: scegli un nome utente unico (minimo 3 caratteri).</li>
            <li><strong>Email</strong>: un indirizzo email valido, necessario per la verifica dell'account.</li>
            <li><strong>Password</strong>: almeno 8 caratteri. Confermala nel campo sottostante.</li>
        </ul>
        <p>Dopo la registrazione riceverai una email di verifica. Clicca sul link contenuto per attivare il tuo account.</p>
        <div class="help-note">⏱️ <strong>Limiti</strong>: massimo 5 tentativi di registrazione per ora dallo stesso indirizzo IP.</div>
    </div>

    <div class="help-section">
        <h2>Cambio password</h2>
        <p>Per modificare la tua password (accessibile dal menu utente dopo il login):</p>
        <ol>
            <li>Inserisci la <strong>password attuale</strong> per verificare la tua identità.</li>
            <li>Inserisci la <strong>nuova password</strong> (minimo 8 caratteri).</li>
            <li>Conferma la nuova password nel campo sottostante.</li>
        </ol>
        <p>Se la modifica va a buon fine, vedrai un messaggio di conferma verde. La nuova password sarà attiva immediatamente.</p>
        <div class="help-note">🛡️ <strong>Consiglio di sicurezza</strong>: usa una password robusta, diversa da quelle usate su altri servizi.</div>
    </div>

    <div class="help-section">
        <h2>Sicurezza delle sessioni</h2>
        <p>La tua sessione rimane attiva finché non clicchi su "Esci" nel menu. Per protezione, la sessione scade automaticamente dopo un periodo di inattività prolungata.</p>
    </div>
</div>
</body>
</html>
