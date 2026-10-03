<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * help_diario.php - Manuale: Diario RSM
 * docs/roadmaps/ROADMAP_DIARIO_RSM.md, Fase 5.
 * Leggibile sia dall'astrologo sia dal soggetto loggato con il proprio CODICE.
 */
require_once 'includes/Auth.php';
$auth = new Auth(db_connect());
if (!$auth->isLoggedIn()) {
    $auth->richiediLoginSoggetto();
}
$eSoggetto = !$auth->isLoggedIn();
header('X-Robots-Tag: noindex, nofollow');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>10. Diario RSM &mdash; Manuale AstroLab</title>
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
</head>
<body>
<div class="help-main">
    <div class="help-header">
        <h1>&#128214; 10. Diario RSM</h1>
        <a class="back-link" href="diario.php">&larr; Vai al Diario RSM</a>
    </div>

    <div class="help-section">
        <h2>A cosa serve</h2>
        <p>Il Diario RSM raccoglie i viaggi fatti per le Rivoluzioni Solari Mirate e i consigli pratici per raggiungere le localit&agrave;: come arrivarci, dove alloggiare, quanto si spende, cosa sapere prima di partire. Si apre dalla voce <strong>Diario RSM</strong> del menu (per l'astrologo) o dal pulsante <strong>Apri il tuo Diario RSM</strong> (per il soggetto).</p>
        <p>Il Diario ha tre sezioni:</p>
        <ul>
            <li><strong>&#128270; Cerca localit&agrave;</strong> &mdash; i consigli di viaggio condivisi da tutti.</li>
            <li><strong>&#9992;&#65039; I miei viaggi</strong> &mdash; i tuoi viaggi RSM, privati.</li>
            <li><strong>&#128221; I miei contributi</strong> &mdash; i consigli che hai condiviso con gli altri.</li>
        </ul>
    </div>

    <div class="help-section">
        <h2>Cercare una localit&agrave;</h2>
        <p>Scrivi il nome di una nazione o di una localit&agrave; (es. "Norvegia", "Australia", "Tokyo"). La ricerca ignora maiuscole e accenti e tollera piccoli errori di battitura ("norvgia" trova la Norvegia). Senza scrivere nulla vedi l'elenco delle nazioni che hanno almeno un contributo, con il loro numero.</p>
        <ul>
            <li>Clicca una <strong>nazione</strong> per vedere le sue localit&agrave; con contributi, poi una <strong>localit&agrave;</strong> per aprirne la scheda.</li>
            <li>Le nazioni compaiono sempre con il nome italiano. Anche i nomi inglesi e alcuni nomi comuni funzionano (es. "Norway", "Olanda", "Inghilterra", "Malvinas").</li>
            <li>I <strong>territori</strong> con un nome proprio compaiono anche sotto la nazione a cui appartengono: cercando "Norvegia" trovi anche Longyearbyen, nelle Svalbard; cercando "Danimarca" trovi anche la Groenlandia.</li>
        </ul>
    </div>

    <div class="help-section">
        <h2>La scheda di una localit&agrave;</h2>
        <p>La scheda mostra tutti i contributi per quella localit&agrave;, dal viaggio pi&ugrave; recente: il percorso a tratte (&#9992;&#65039; aereo, &#9972;&#65039; nave, &#128646; treno&hellip;), l'alloggio con il suo sito, la spesa indicativa, le informazioni pratiche, i contatti utili e i consigli. Ogni contributo riporta l'autore (nome utente dell'astrologo o codice del soggetto) e il periodo del viaggio.</p>
        <p>Con <strong>Scrivi anche tu un contributo per questa localit&agrave;</strong> apri il modulo con localit&agrave; e nazione gi&agrave; compilate.</p>
    </div>

    <div class="help-section">
        <h2>Scrivere un contributo</h2>
        <p>Da <strong>I miei contributi &rarr; + Nuovo contributo</strong>:</p>
        <ul>
            <li><strong>Localit&agrave;, nazione, anno e mese</strong> del viaggio. La nazione si sceglie dall'elenco dei nomi proposti mentre scrivi.</li>
            <li><strong>Come arrivare</strong> &mdash; aggiungi una tratta per ogni passaggio (es. Roma &rarr; Oslo, Oslo &rarr; Troms&oslash;, Troms&oslash; &rarr; Longyearbyen), con mezzo, compagnia, durata e costo; le frecce &uarr; &darr; cambiano l'ordine, &#10005; elimina la tratta.</li>
            <li><strong>Alloggio</strong> &mdash; nome, tipo, sito ufficiale (deve iniziare con https:// o http://), fascia di prezzo, spesa indicativa con il suo riferimento (es. "per notte") e un giudizio.</li>
            <li><strong>Informazioni pratiche</strong> &mdash; documenti e visti, clima nel periodo, lingua e valuta, connettivit&agrave;, particolarit&agrave; locali, contatti utili e consigli.</li>
        </ul>
        <div class="help-note">&#128161; Per la tua privacy il contributo indica solo <strong>anno e mese</strong> del viaggio: la data esatta di una RSM rivelerebbe il giorno del compleanno. Nei <strong>contatti</strong> inserisci solo strutture, agenzie e guide professionali, mai dati personali di privati: per salvare devi confermarlo con l'apposita spunta.</div>
        <p>Dall'elenco dei tuoi contributi puoi aprirne la scheda, modificarli o eliminarli. Solo l'autore pu&ograve; modificare o eliminare un contributo.</p>
    </div>

    <div class="help-section">
        <h2>I tuoi viaggi privati</h2>
        <p>Da <strong>I miei viaggi &rarr; + Nuovo viaggio</strong> registri luogo, nazione, date di arrivo e partenza, albergo, costi, trasporti usati e note personali. Qui le date sono quelle esatte, perch&eacute; i viaggi <strong>non sono condivisi</strong>: li vedi solo tu<?= $eSoggetto ? ' e il tuo astrologo' : '' ?>.</p>
        <p>I viaggi compaiono in un <strong>elenco compatto</strong>, una riga per viaggio (anno, luogo, nazione) dal pi&ugrave; recente: un clic sulla riga apre il riquadro completo, un secondo clic lo richiude. Con la casella di ricerca trovi un viaggio per anno, luogo o nazione: per esempio <em>2011</em>, <em>RSM2011</em> o <em>tromso</em> (anche senza accenti); puoi combinare pi&ugrave; parole.</p>
<?php if (!$eSoggetto): ?>
        <p>Nella sezione <strong>Viaggi dei miei soggetti</strong> trovi i viaggi registrati dai tuoi soggetti con il loro accesso: puoi consultarli ma non modificarli.</p>
        <p>Con <strong>Collega a una sessione RS</strong> associ un viaggio (tuo o di un tuo soggetto) a una sessione RS salvata: vengono proposte solo le tue sessioni RS di quel soggetto. Il soggetto non vede mai le sessioni RS. Il collegamento &egrave; facoltativo: il grafico della RSM non dipende dalle sessioni, che puoi cancellare quando vuoi.</p>
<?php endif; ?>
    </div>

    <div class="help-section">
        <h2>Il grafico della RSM del viaggio</h2>
        <p>Ogni viaggio pu&ograve; mostrare il grafico della sua RSM: il <strong>cielo natale</strong> e la <strong>RS dell'anno nel luogo del viaggio</strong>, affiancati, calcolati nello stesso modo di Riv. Solare. Il grafico si calcola al momento dai dati del viaggio: niente viene salvato e nessun file viene creato.</p>
        <p>Perch&eacute; il grafico sia disponibile, nel form del viaggio servono:</p>
        <ul>
            <li><strong>Luogo scelto dall'elenco</strong>: mentre scrivi il luogo compare l'elenco dei luoghi; scegliendo una voce si compilano da soli nome, nazione e coordinate, e sotto il campo appare la riga verde &laquo;&#128205; &hellip; luogo scelto dall'elenco&raquo;. Se il nome viene solo scritto a mano, le coordinate non ci sono.</li>
            <li><strong>Anno della RSM</strong>: si precompila dall'anno della data di arrivo; puoi correggerlo.</li>
<?php if (!$eSoggetto): ?>
            <li><strong>Soggetto della RSM</strong> (solo per i viaggi scritti da te): il soggetto da cui prendere il cielo natale, scelto tra i tuoi soggetti; per i tuoi viaggi personali scegli la tua scheda soggetto.</li>
<?php endif; ?>
        </ul>
        <p>Con questi dati, nel riquadro del viaggio compare il pulsante <strong>&#128200; Vedi la RSM</strong>: si apre una finestra con le due ruote; si chiude con la &times;, con un clic fuori o con il tasto Esc. Se un dato manca, al posto del pulsante trovi l'indicazione su cosa completare. I viaggi registrati prima di questa funzione vanno aperti una volta con <strong>Modifica</strong> per scegliere il luogo dall'elenco.</p>
<?php if ($eSoggetto): ?>
        <div class="help-note">&#128161; Vedi il grafico dei tuoi viaggi; note, valutazioni e sessioni del tuo astrologo restano riservate a lui.</div>
<?php else: ?>
        <div class="help-note">&#128161; Il grafico &egrave; visibile solo al soggetto del viaggio e a te; il soggetto vede le due ruote dei suoi viaggi, ma non le tue note, le valutazioni o le sessioni RS.</div>
<?php endif; ?>
    </div>

<?php if (!$eSoggetto): ?>
    <div class="help-section">
        <h2>Far usare il Diario ai tuoi soggetti</h2>
        <p>Un soggetto pu&ograve; scrivere i propri viaggi e contributi entrando con il suo <strong>Codice</strong>. Per abilitarlo usa il pulsante &#128273; nella lista soggetti (vedi <a href="help_soggetti.php">3. Gestione Soggetti</a>). Tu invece usi il Diario direttamente con il tuo account di astrologo: non serve entrare come soggetto.</p>
        <div class="help-note">&#128161; L'amministratore pu&ograve; nascondere un contributo non appropriato: il contributo sparisce dalla ricerca e l'autore lo vede nei suoi con l'indicazione "nascosto dall'amministratore".</div>
    </div>
<?php endif; ?>

    <div class="help-section">
        <h2>Limiti</h2>
        <ul>
            <li>Al massimo 10 contributi e 20 viaggi nuovi al giorno per autore.</li>
            <li>Al massimo 15 tratte per contributo e 4000 caratteri per ogni testo.</li>
        </ul>
    </div>
</div>
</body>
</html>
