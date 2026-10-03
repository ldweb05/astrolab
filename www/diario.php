<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * diario.php - Diario RSM (astrologi e soggetti)
 * docs/roadmaps/ROADMAP_DIARIO_RSM.md, blocco C, Fase 3.
 *
 * Accessibile all'astrologo loggato (con il menu dell'astrologo) e al soggetto
 * loggato con il proprio CODICE (barra semplice, senza menu dell'astrologo).
 * Tutti i dati arrivano da api/diario_rsm_api.php; la logica e' in
 * js/diario_rsm.js (nessun handler inline, testi inseriti con textContent).
 */
require_once __DIR__ . '/includes/Auth.php';

$pdo  = db_connect();
$auth = new Auth($pdo);

if ($auth->isLoggedIn()) {
    $ruoloDiario   = 'astrologo';
    $isAdmin       = $auth->isAdmin();
    $username      = $auth->getCurrentUsername();
    $soggettoNome  = $auth->getSoggettoNome();
    $nomeVisibile  = $username;
} elseif ($auth->isLoggedInSoggetto()) {
    $soggettoLoggato = $auth->richiediLoginSoggetto();
    $ruoloDiario   = 'soggetto';
    $isAdmin       = false;
    $nomeVisibile  = $soggettoLoggato['codice'];
} else {
    header('Location: login.php?next=' . urlencode('/diario.php'));
    exit;
}

header('X-Robots-Tag: noindex, nofollow');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Diario RSM - AstroLab</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .diario-main { max-width: 1100px; margin: 0 auto; padding: 20px 24px 60px; }
        .diario-barra-soggetto { display: flex; justify-content: space-between; align-items: center;
            padding: 14px 24px; border-bottom: 2px solid #2C3E6B; background: #fff; }
        .diario-barra-soggetto .logo { font-family: 'Eb Garamond', Georgia, serif; font-size: 28px;
            color: #B85C38; font-weight: 700; text-decoration: none; }
        .diario-barra-soggetto .utente { font-size: 14px; color: #2C3E6B; }
        .diario-barra-soggetto .utente a { margin-left: 12px; }
        .diario-titolo { font-family: 'Eb Garamond', Georgia, serif; color: #2C3E6B; font-size: 30px; margin: 10px 0 4px; }
        .diario-sottotitolo { color: #555; font-size: 14px; margin-bottom: 18px; }
        .diario-schede { display: flex; gap: 6px; border-bottom: 1px solid #D8D2C4; margin-bottom: 18px; flex-wrap: wrap; }
        .diario-scheda-btn { background: none; border: 0; border-bottom: 3px solid transparent; padding: 10px 14px;
            font-size: 15px; color: #2C3E6B; cursor: pointer; }
        .diario-scheda-btn.attiva { border-bottom-color: #2C3E6B; font-weight: 600; }
        .diario-pannello { display: none; }
        .diario-pannello.attivo { display: block; }
        .diario-ricerca { display: flex; gap: 8px; margin-bottom: 16px; }
        .diario-ricerca input { flex: 1; padding: 10px 12px; font-size: 16px; border: 1px solid #C9C2B2; border-radius: 6px; }
        .diario-msg { color: #555; font-size: 14px; margin: 10px 0; }
        .diario-msg.err { color: #8A1C1C; }
        .diario-sezione-titolo { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #7A6F5A; margin: 18px 0 8px; }
        .diario-elenco { list-style: none; padding: 0; margin: 0; }
        .diario-elenco li { margin: 0; }
        .diario-voce { display: flex; justify-content: space-between; align-items: center; width: 100%; text-align: left;
            background: #fff; border: 1px solid #E4DED0; border-radius: 6px; padding: 10px 14px; margin-bottom: 6px;
            cursor: pointer; font-size: 15px; color: #2C3E6B; }
        .diario-voce:hover { background: #F7F3EA; }
        .diario-voce .nota { color: #7A6F5A; font-size: 13px; margin-left: 8px; }
        .diario-voce .conteggio { background: #2C3E6B; color: #fff; border-radius: 10px; padding: 1px 9px; font-size: 12px; }
        .diario-nazione-blocco { margin-bottom: 14px; }
        .diario-nazione-nome { font-weight: 600; color: #2C3E6B; margin: 6px 0; }
        .diario-indietro { background: none; border: 0; color: #2C3E6B; cursor: pointer; padding: 0; margin-bottom: 10px; font-size: 14px; }
        .diario-scheda-titolo { font-family: 'Eb Garamond', Georgia, serif; font-size: 26px; color: #2C3E6B; margin: 4px 0 14px; }
        .diario-contributo { background: #fff; border: 1px solid #E4DED0; border-radius: 8px; padding: 16px 18px; margin-bottom: 14px; }
        .diario-contributo.nascosto { opacity: 0.55; border-style: dashed; }
        .diario-contributo-testa { display: flex; justify-content: space-between; color: #7A6F5A; font-size: 13px; margin-bottom: 10px; }
        .diario-riga { margin: 6px 0; font-size: 14px; line-height: 1.5; }
        .diario-riga .etichetta { font-weight: 600; color: #2C3E6B; }
        .diario-testo-lungo { white-space: pre-line; }
        .diario-tratte { margin: 8px 0; padding-left: 0; list-style: none; font-size: 14px; }
        .diario-tratte li { margin: 3px 0; }
        .diario-in-arrivo { background: #F7F3EA; border-radius: 8px; padding: 18px; color: #555; }
        .diario-azioni { display: flex; gap: 8px; flex-wrap: wrap; margin: 10px 0 14px; }
        .diario-btn { background: #2C3E6B; color: #fff; border: 0; border-radius: 6px; padding: 8px 14px; font-size: 14px; cursor: pointer; }
        .diario-btn.secondario { background: #fff; color: #2C3E6B; border: 1px solid #2C3E6B; }
        .diario-btn.pericolo { background: #fff; color: #8A1C1C; border: 1px solid #8A1C1C; }
        .diario-btn.piccolo { padding: 4px 9px; font-size: 13px; }
        .diario-carta-azioni { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
        .diario-mio { background: #FFFDF6; border: 1px solid #E4DED0; border-radius: 8px; padding: 12px 14px; margin-bottom: 8px; }
        .diario-mio-titolo { font-weight: 600; color: #2C3E6B; }
        .diario-mio-info { color: #7A6F5A; font-size: 13px; margin: 2px 0 8px; }
        .diario-etichetta-stato { background: #FDECEA; color: #8A1C1C; border-radius: 10px; padding: 1px 8px; font-size: 12px; margin-left: 6px; }
        .diario-form { background: #fff; border: 1px solid #E4DED0; border-radius: 8px; padding: 18px; margin-top: 12px; }
        .diario-form fieldset { border: 0; border-top: 1px solid #EFE9DC; margin: 14px 0 0; padding: 12px 0 0; }
        .diario-form legend { font-weight: 600; color: #2C3E6B; padding-right: 8px; }
        .diario-griglia { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px 14px; }
        .diario-campo label { display: block; font-size: 13px; color: #555; margin-bottom: 3px; }
        .diario-campo input, .diario-campo select, .diario-campo textarea { width: 100%; box-sizing: border-box;
            padding: 7px 9px; border: 1px solid #C9C2B2; border-radius: 5px; font-size: 14px; font-family: inherit; }
        .diario-campo textarea { min-height: 70px; resize: vertical; }
        .diario-campo.largo { grid-column: 1 / -1; }
        .diario-tratta { display: grid; grid-template-columns: 110px 1fr 1fr 1fr 110px 90px 70px auto; gap: 6px; align-items: end;
            background: #FAF7F0; border-radius: 6px; padding: 8px; margin-bottom: 6px; }
        .diario-tratta .diario-campo label { font-size: 12px; }
        .diario-tratta-pulsanti { display: flex; gap: 4px; }
        .diario-dichiarazione { display: flex; gap: 8px; align-items: flex-start; font-size: 14px; margin-top: 14px; }
        .diario-nota-privacy { font-size: 12px; color: #7A6F5A; margin-top: 4px; }
        @media (max-width: 800px) { .diario-tratta { grid-template-columns: 1fr 1fr; } }
        .diario-viaggio-soggetto { color: #B85C38; font-size: 13px; font-weight: 600; }
        .diario-collega { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; margin-top: 8px; }
        .diario-collega select { padding: 5px 8px; border: 1px solid #C9C2B2; border-radius: 5px; font-size: 13px; max-width: 100%; }
        .diario-sessione { color: #2C3E6B; font-size: 13px; background: #EEF2FA; border-radius: 10px; padding: 2px 9px; display: inline-block; margin-top: 4px; }
        .diario-filtro-viaggi { width: 100%; max-width: 420px; box-sizing: border-box; padding: 8px 11px; font-size: 14px;
            border: 1px solid #C9C2B2; border-radius: 6px; margin: 4px 0 6px; }
        .diario-conteggio { color: #7A6F5A; font-size: 12px; margin-bottom: 8px; }
        .diario-riga-viaggio { display: flex; align-items: center; gap: 12px; width: 100%; text-align: left; background: #fff;
            border: 1px solid #E4DED0; border-radius: 6px; padding: 9px 12px; margin-bottom: 6px; cursor: pointer;
            font-size: 14px; color: #2C3E6B; }
        .diario-riga-viaggio:hover { background: #F7F3EA; }
        .diario-riga-viaggio[aria-expanded="true"] { border-color: #2C3E6B; }
        .diario-riga-viaggio .anno { font-weight: 700; min-width: 3.2em; }
        .diario-riga-viaggio .luogo { flex: 1; }
        .diario-riga-viaggio .chi { color: #B85C38; font-size: 12px; font-weight: 600; }
        .diario-riga-viaggio .freccia { color: #7A6F5A; }
        .diario-dettaglio-viaggio { margin: -2px 0 10px 14px; }
        .diario-campo-luogo { position: relative; }
        .diario-campo-luogo .dropdown-risultati { min-width: 100%; }
        .diario-coordinate { font-size: 12px; color: #7A6F5A; margin-top: 3px; }
        .diario-coordinate.ok { color: #2E7D32; }
        .diario-nota-grafico { font-size: 12px; color: #7A6F5A; margin-top: 6px; }
        .rsm-finestra { position: fixed; inset: 0; background: rgba(30, 24, 12, 0.55); z-index: 1000;
            display: none; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 24px 12px; }
        .rsm-finestra.aperta { display: flex; }
        .rsm-finestra-box { background: #FBF8F1; border-radius: 10px; max-width: 1080px; width: 100%;
            padding: 18px 20px 22px; box-shadow: 0 10px 40px rgba(0,0,0,0.25); }
        .rsm-finestra-testa { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .rsm-finestra-testa h3 { margin: 0 0 4px; color: #2C3E6B; font-size: 19px; }
        .rsm-finestra-sotto { color: #7A6F5A; font-size: 13px; }
        .rsm-chiudi { background: none; border: none; font-size: 26px; line-height: 1; cursor: pointer; color: #7A6F5A; }
        .rsm-ruote { display: flex; flex-wrap: wrap; gap: 18px; justify-content: center; margin-top: 14px; }
        .rsm-ruota { flex: 1 1 420px; max-width: 500px; text-align: center; }
        .rsm-ruota h4 { margin: 0 0 6px; color: #2C3E6B; font-size: 15px; }
    </style>
</head>
<body data-ruolo="<?= htmlspecialchars($ruoloDiario, ENT_QUOTES, 'UTF-8') ?>" data-admin="<?= $isAdmin ? '1' : '0' ?>">
<?php if ($ruoloDiario === 'astrologo'): ?>
<?php $paginaAttiva = 'diario'; include 'includes/header_nav.php'; ?>
<?php else: ?>
<header class="diario-barra-soggetto">
    <a href="area_soggetto.php" class="logo">AstroLab</a>
    <span class="utente">
        <?= htmlspecialchars($nomeVisibile, ENT_QUOTES, 'UTF-8') ?>
        <a href="help_diario.php" target="_blank">Guida</a>
        <a href="cambio_password_soggetto.php">Cambia password</a>
        <a href="logout.php">Esci</a>
    </span>
</header>
<?php endif; ?>

<main class="diario-main">
    <h1 class="diario-titolo">Diario RSM</h1>
    <div class="diario-sottotitolo">I viaggi fatti per le Rivoluzioni Solari Mirate e i consigli di viaggio condivisi.</div>

    <nav class="diario-schede" aria-label="Sezioni del Diario">
        <button type="button" class="diario-scheda-btn attiva" data-scheda="cerca">&#128270; Cerca localit&agrave;</button>
        <button type="button" class="diario-scheda-btn" data-scheda="viaggi">&#9992;&#65039; I miei viaggi</button>
        <button type="button" class="diario-scheda-btn" data-scheda="contributi">&#128221; I miei contributi</button>
    </nav>

    <section id="pannello-cerca" class="diario-pannello attivo">
        <div id="cerca-vista-ricerca">
            <div class="diario-ricerca">
                <input type="search" id="cerca-testo" placeholder="Cerca una nazione o una localit&agrave; (es. Norvegia, Australia, Tokyo)" autocomplete="off" maxlength="100">
            </div>
            <div id="cerca-msg" class="diario-msg"></div>
            <div id="cerca-risultati"></div>
        </div>
        <div id="cerca-vista-scheda" style="display:none">
            <button type="button" class="diario-indietro" id="scheda-indietro">&larr; Torna alla ricerca</button>
            <h2 class="diario-scheda-titolo" id="scheda-titolo"></h2>
            <div id="scheda-msg" class="diario-msg"></div>
            <div id="scheda-contributi"></div>
            <div class="diario-azioni">
                <button type="button" class="diario-btn" id="scheda-scrivi">Scrivi anche tu un contributo per questa localit&agrave;</button>
            </div>
        </div>
    </section>

    <section id="pannello-viaggi" class="diario-pannello">
        <div id="viaggi-vista-elenco">
            <div class="diario-sottotitolo">I tuoi viaggi RSM: sono privati e restano visibili solo a te<?= $ruoloDiario === 'soggetto' ? ' e al tuo astrologo' : '' ?>.</div>
            <div class="diario-azioni">
                <button type="button" class="diario-btn" id="viaggi-nuovo">+ Nuovo viaggio</button>
            </div>
            <input type="search" id="viaggi-filtro" class="diario-filtro-viaggi" maxlength="60" autocomplete="off"
                   placeholder="Cerca per anno o luogo (es. 2011, RSM2011, Tromsø)">
            <div id="viaggi-msg" class="diario-msg"></div>
            <div id="viaggi-elenco"></div>
<?php if ($ruoloDiario === 'astrologo'): ?>
            <div class="diario-sezione-titolo">Viaggi dei miei soggetti</div>
            <div class="diario-sottotitolo">Scritti dai tuoi soggetti con il loro accesso: puoi consultarli e collegarli a una sessione RS, ma non modificarli.</div>
            <input type="search" id="viaggi-soggetti-filtro" class="diario-filtro-viaggi" maxlength="60" autocomplete="off"
                   placeholder="Cerca per anno, luogo o soggetto (es. 2011, LD001)">
            <div id="viaggi-soggetti-msg" class="diario-msg"></div>
            <div id="viaggi-soggetti-elenco"></div>
<?php endif; ?>
        </div>

        <form id="viaggio-form" class="diario-form" style="display:none" novalidate>
            <h2 class="diario-scheda-titolo" id="viaggio-form-titolo">Nuovo viaggio</h2>
            <input type="hidden" id="vf-id">
            <div class="diario-griglia">
                <div class="diario-campo diario-campo-luogo"><label for="vf-luogo">Luogo *</label>
                    <input type="text" id="vf-luogo" maxlength="200" required autocomplete="off" placeholder="Scrivi e scegli dall'elenco (es. Longyearbyen)">
                    <div id="vf-luogo-risultati" class="dropdown-risultati"></div>
                    <input type="hidden" id="vf-lat"><input type="hidden" id="vf-lon">
                    <div id="vf-coordinate" class="diario-coordinate"></div></div>
                <div class="diario-campo"><label for="vf-nazione">Nazione *</label>
                    <input type="text" id="vf-nazione" list="elenco-nazioni" required autocomplete="off" placeholder="Scrivi e scegli dall'elenco"></div>
                <div class="diario-campo"><label for="vf-arrivo">Arrivo</label>
                    <input type="date" id="vf-arrivo"></div>
                <div class="diario-campo"><label for="vf-partenza">Partenza</label>
                    <input type="date" id="vf-partenza"></div>
                <div class="diario-campo"><label for="vf-anno-rsm">Anno della RSM</label>
                    <input type="number" id="vf-anno-rsm" min="1900" max="2100" placeholder="es. 2022"></div>
<?php if ($ruoloDiario === 'astrologo'): ?>
                <div class="diario-campo"><label for="vf-soggetto-rsm">Soggetto della RSM</label>
                    <select id="vf-soggetto-rsm"><option value="">&mdash;</option></select></div>
<?php endif; ?>
                <div class="diario-campo"><label for="vf-albergo">Albergo</label>
                    <input type="text" id="vf-albergo" maxlength="200"></div>
                <div class="diario-campo"><label for="vf-costo-alloggio">Costo alloggio</label>
                    <input type="number" id="vf-costo-alloggio" min="0" step="0.01"></div>
                <div class="diario-campo"><label for="vf-costo-trasporti">Costo trasporti</label>
                    <input type="number" id="vf-costo-trasporti" min="0" step="0.01"></div>
                <div class="diario-campo"><label for="vf-valuta">Valuta</label>
                    <input type="text" id="vf-valuta" maxlength="3" value="EUR"></div>
                <div class="diario-campo largo"><label for="vf-trasporti">Trasporti usati</label>
                    <textarea id="vf-trasporti" maxlength="4000" placeholder="es. Roma-Oslo in aereo, Oslo-Longyearbyen in aereo"></textarea></div>
                <div class="diario-campo largo"><label for="vf-note">Note personali</label>
                    <textarea id="vf-note" maxlength="4000"></textarea></div>
            </div>
            <div id="vf-msg" class="diario-msg"></div>
            <div class="diario-azioni">
                <button type="submit" class="diario-btn" id="vf-salva">Salva viaggio</button>
                <button type="button" class="diario-btn secondario" id="vf-annulla">Annulla</button>
            </div>
        </form>
    </section>

    <section id="pannello-contributi" class="diario-pannello">
        <div id="contrib-vista-elenco">
            <div class="diario-sottotitolo">I consigli di viaggio che hai condiviso. Sono visibili a tutti gli astrologi e ai soggetti di AstroLab, con il tuo nome utente (o il tuo codice) come autore.</div>
            <div class="diario-azioni">
                <button type="button" class="diario-btn" id="contrib-nuovo">+ Nuovo contributo</button>
            </div>
            <div id="contrib-msg" class="diario-msg"></div>
            <div id="contrib-elenco"></div>
        </div>

        <form id="contrib-form" class="diario-form" style="display:none" novalidate>
            <h2 class="diario-scheda-titolo" id="contrib-form-titolo">Nuovo contributo</h2>
            <input type="hidden" id="cf-id">
            <div class="diario-griglia">
                <div class="diario-campo"><label for="cf-luogo">Localit&agrave; *</label>
                    <input type="text" id="cf-luogo" maxlength="200" required placeholder="es. Longyearbyen"></div>
                <div class="diario-campo"><label for="cf-nazione">Nazione *</label>
                    <input type="text" id="cf-nazione" list="elenco-nazioni" required autocomplete="off" placeholder="Scrivi e scegli dall'elenco"></div>
                <div class="diario-campo"><label for="cf-anno">Anno del viaggio *</label>
                    <input type="number" id="cf-anno" min="1900" max="2100" required></div>
                <div class="diario-campo"><label for="cf-mese">Mese del viaggio</label>
                    <select id="cf-mese"><option value="">&mdash;</option></select></div>
            </div>
            <div class="diario-nota-privacy">Per la tua privacy si indicano solo anno e mese: la data esatta di una RSM rivelerebbe il giorno del compleanno.</div>

            <fieldset><legend>Come arrivare (tratte)</legend>
                <div id="cf-tratte"></div>
                <button type="button" class="diario-btn secondario piccolo" id="cf-tratta-aggiungi">+ Aggiungi tratta</button>
            </fieldset>

            <fieldset><legend>Alloggio</legend>
                <div class="diario-griglia">
                    <div class="diario-campo"><label for="cf-alloggio-nome">Nome</label>
                        <input type="text" id="cf-alloggio-nome" maxlength="200"></div>
                    <div class="diario-campo"><label for="cf-alloggio-tipo">Tipo</label>
                        <select id="cf-alloggio-tipo">
                            <option value="">&mdash;</option><option value="hotel">Hotel</option><option value="bb">B&amp;B</option>
                            <option value="appartamento">Appartamento</option><option value="guesthouse">Guesthouse</option>
                            <option value="campeggio">Campeggio</option><option value="altro">Altro</option>
                        </select></div>
                    <div class="diario-campo"><label for="cf-alloggio-sito">Sito ufficiale</label>
                        <input type="url" id="cf-alloggio-sito" maxlength="500" placeholder="https://..."></div>
                    <div class="diario-campo"><label for="cf-alloggio-fascia">Fascia di prezzo</label>
                        <input type="text" id="cf-alloggio-fascia" maxlength="50" placeholder="es. medio-alta"></div>
                    <div class="diario-campo"><label for="cf-costo">Spesa indicativa</label>
                        <input type="number" id="cf-costo" min="0" step="0.01"></div>
                    <div class="diario-campo"><label for="cf-costo-rif">Riferimento</label>
                        <input type="text" id="cf-costo-rif" maxlength="50" placeholder="es. per notte"></div>
                    <div class="diario-campo"><label for="cf-valuta">Valuta</label>
                        <input type="text" id="cf-valuta" maxlength="3" value="EUR"></div>
                    <div class="diario-campo largo"><label for="cf-alloggio-giudizio">Giudizio sull'alloggio</label>
                        <textarea id="cf-alloggio-giudizio" maxlength="4000"></textarea></div>
                </div>
            </fieldset>

            <fieldset><legend>Informazioni pratiche</legend>
                <div class="diario-griglia">
                    <div class="diario-campo"><label for="cf-documenti">Documenti e visti</label><textarea id="cf-documenti" maxlength="4000"></textarea></div>
                    <div class="diario-campo"><label for="cf-clima">Clima nel periodo</label><textarea id="cf-clima" maxlength="4000"></textarea></div>
                    <div class="diario-campo"><label for="cf-lingua">Lingua e valuta</label><textarea id="cf-lingua" maxlength="4000"></textarea></div>
                    <div class="diario-campo"><label for="cf-connettivita">Connettivit&agrave;</label><textarea id="cf-connettivita" maxlength="4000"></textarea></div>
                    <div class="diario-campo largo"><label for="cf-particolarita">Particolarit&agrave; locali</label><textarea id="cf-particolarita" maxlength="4000"></textarea></div>
                    <div class="diario-campo largo"><label for="cf-contatti">Contatti utili (solo strutture, agenzie, guide professionali)</label><textarea id="cf-contatti" maxlength="4000"></textarea></div>
                    <div class="diario-campo largo"><label for="cf-consigli">Consigli</label><textarea id="cf-consigli" maxlength="4000"></textarea></div>
                </div>
            </fieldset>

            <label class="diario-dichiarazione"><input type="checkbox" id="cf-dichiarazione">
                <span>Confermo che nei contatti non inserisco dati personali di privati (solo strutture, agenzie e guide professionali). *</span></label>

            <div id="cf-msg" class="diario-msg"></div>
            <div class="diario-azioni">
                <button type="submit" class="diario-btn" id="cf-salva">Salva contributo</button>
                <button type="button" class="diario-btn secondario" id="cf-annulla">Annulla</button>
            </div>
        </form>
        <datalist id="elenco-nazioni"></datalist>
    </section>
</main>

<!-- Grafico della RSM del viaggio (D21-D22): cielo natale e RS affiancati, calcolati su richiesta -->
<div id="rsm-finestra" class="rsm-finestra" role="dialog" aria-modal="true" aria-labelledby="rsm-titolo">
    <div class="rsm-finestra-box">
        <div class="rsm-finestra-testa">
            <div>
                <h3 id="rsm-titolo">RSM</h3>
                <div id="rsm-sotto" class="rsm-finestra-sotto"></div>
            </div>
            <button type="button" class="rsm-chiudi" id="rsm-chiudi" aria-label="Chiudi">&times;</button>
        </div>
        <div id="rsm-msg" class="diario-msg"></div>
        <div class="rsm-ruote" id="rsm-ruote">
            <div class="rsm-ruota"><h4>&#9737; Cielo natale</h4>
                <svg id="wheel-natale-viaggio" width="480" height="480" class="zodiac-wheel-responsive"></svg></div>
            <div class="rsm-ruota"><h4 id="rsm-titolo-rs">&#9737; RS</h4>
                <svg id="wheel-rs-viaggio" width="480" height="480" class="zodiac-wheel-responsive"></svg></div>
        </div>
    </div>
</div>

<script src="js/zodiac_wheel.js"></script>
<script src="js/diario_rsm.js"></script>
</body>
</html>
