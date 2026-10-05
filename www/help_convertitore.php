<?php
require_once __DIR__ . '/includes/bootstrap.php';
session_start();
/**
 * help_convertitore.php - Help, voce 11: convertitore di unita' di misura.
 * Pagina del committente (prima www/converter.html, pubblica), portata nella grafica
 * delle pagine Help e sotto login il 03-10-2026. Tutti i calcoli avvengono nel browser:
 * nessuna chiamata al server o a siti esterni.
 */
require_once 'includes/Auth.php';
$auth = new Auth(db_connect());
$auth->richiediLogin();
header('X-Robots-Tag: noindex, nofollow');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>11. Convertitore &mdash; Manuale AstroLab</title>
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
        .help-section p { color: #444; font-size: 14px; line-height: 1.7; }
        .help-note { background: #FFF8ED; border: 1px solid #E8DCC8; border-radius: 6px; padding: 14px 18px; font-size: 13px; color: #6B5D3E; margin-top: 12px; }

        .conv-schede { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 18px; }
        .conv-scheda { background: #fff; border: 1px solid #D4C9A8; border-radius: 16px; padding: 6px 13px;
            font-size: 13px; color: #2C3E6B; cursor: pointer; }
        .conv-scheda:hover { background: #F7F3EA; }
        .conv-scheda.attiva { background: #2C3E6B; border-color: #2C3E6B; color: #fff; }
        .conv-box { background: #fff; border: 1px solid #E4DED0; border-radius: 8px; padding: 18px 20px; }
        .conv-riga { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
        .conv-campo label { display: block; font-size: 12px; color: #6B5D3E; margin-bottom: 4px; }
        .conv-campo input, .conv-campo select { width: 100%; box-sizing: border-box; padding: 8px 10px; font-size: 14px;
            border: 1px solid #C9C2B2; border-radius: 6px; background: #fff; color: #333; }
        .conv-scambia { background: #fff; border: 1px solid #2C3E6B; color: #2C3E6B; border-radius: 6px;
            padding: 7px 14px; font-size: 13px; cursor: pointer; }
        .conv-scambia:hover { background: #EEF2FA; }
        .conv-risultato { margin-top: 14px; background: #FFF8ED; border: 1px solid #E8DCC8; border-radius: 6px;
            padding: 12px 16px; font-size: 15px; color: #2C3E6B; font-weight: 600; min-height: 22px; }
        @media (max-width: 560px) { .conv-riga { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="help-main">
    <div class="help-header">
        <h1>&#128214; 11. Convertitore</h1>
        <a class="back-link" href="dashboard.php">&larr; Torna alla dashboard</a>
    </div>

    <div class="help-section">
        <h2>Convertitore di unit&agrave; di misura</h2>
        <p>Scegli la grandezza, scrivi il valore e le unit&agrave; di partenza e di arrivo: il risultato si aggiorna mentre scrivi. Con <strong>&#8644; Scambia</strong> inverti le due unit&agrave;.</p>
    </div>

    <div class="help-section">
        <div class="conv-schede" id="conv-schede"></div>
        <div class="conv-box">
            <h2 id="conv-titolo" style="margin-top:0">Lunghezza</h2>
            <div class="conv-riga">
                <div class="conv-campo"><label for="conv-valore">Valore</label>
                    <input type="number" id="conv-valore" step="any" placeholder="Inserisci un valore"></div>
                <div class="conv-campo" style="display:flex;align-items:flex-end">
                    <button type="button" class="conv-scambia" id="conv-scambia">&#8644; Scambia</button></div>
            </div>
            <div class="conv-riga">
                <div class="conv-campo"><label for="conv-da">Da</label><select id="conv-da"></select></div>
                <div class="conv-campo"><label for="conv-a">A</label><select id="conv-a"></select></div>
            </div>
            <div class="conv-risultato" id="conv-risultato" aria-live="polite">Il risultato apparir&agrave; qui</div>
        </div>
        <div class="help-note">&#128161; I calcoli avvengono tutti nel tuo browser. Nella grandezza <em>Dati</em> le unit&agrave; KB, MB, GB, TB e PB sono decimali (1 KB = 1000 byte), come per i dischi e la rete.</div>
    </div>
</div>

<script>
(function () {
    'use strict';

    // Fattori rispetto all'unita' base di ogni grandezza (dalla pagina del committente).
    // Dati: unita' decimali, 1 KB = 1000 byte (correzione del 03-10-2026).
    var GRANDEZZE = [
        { id: 'length', nome: '\ud83d\udccf Lunghezza', titolo: 'Lunghezza', da: 'cm', a: 'in', unita: [
            ['mm', 'Millimetri (mm)', 0.001], ['cm', 'Centimetri (cm)', 0.01], ['m', 'Metri (m)', 1],
            ['km', 'Chilometri (km)', 1000], ['in', 'Pollici (in)', 0.0254], ['ft', 'Piedi (ft)', 0.3048],
            ['yd', 'Iarde (yd)', 0.9144], ['mi', 'Miglia (mi)', 1609.344]] },
        { id: 'weight', nome: '\u2696\ufe0f Peso', titolo: 'Peso e massa', da: 'g', a: 'oz', unita: [
            ['mg', 'Milligrammi (mg)', 0.001], ['g', 'Grammi (g)', 1], ['kg', 'Chilogrammi (kg)', 1000],
            ['oz', 'Once (oz)', 28.3495], ['lb', 'Libbre (lb)', 453.592], ['st', 'Stone (st)', 6350.29],
            ['t', 'Tonnellate (t)', 1000000]] },
        { id: 'temp', nome: '\ud83c\udf21\ufe0f Temperatura', titolo: 'Scala termica', da: 'c', a: 'f', unita: [
            ['c', 'Celsius (\u00b0C)'], ['f', 'Fahrenheit (\u00b0F)'], ['k', 'Kelvin (K)']] },
        { id: 'speed', nome: '\ud83d\ude80 Velocit\u00e0', titolo: 'Velocit\u00e0', da: 'kmh', a: 'mph', unita: [
            ['ms', 'm/s', 1], ['kmh', 'km/h', 0.277778], ['mph', 'Miglia/ora (mph)', 0.44704],
            ['kn', 'Nodi (kn)', 0.514444], ['fts', 'Piedi/secondo (ft/s)', 0.3048]] },
        { id: 'time', nome: '\u23f1\ufe0f Tempo', titolo: 'Durata', da: 'h', a: 'd', unita: [
            ['ms', 'Millisecondi', 0.001], ['s', 'Secondi', 1], ['min', 'Minuti', 60], ['h', 'Ore', 3600],
            ['d', 'Giorni', 86400], ['w', 'Settimane', 604800], ['mo', 'Mesi (30 giorni)', 2592000],
            ['y', 'Anni (365 giorni)', 31536000]] },
        { id: 'pressure', nome: '\ud83d\udd27 Pressione', titolo: 'Pressione', da: 'psi', a: 'mmhg', unita: [
            ['pa', 'Pascal (Pa)', 1], ['kpa', 'Kilopascal (kPa)', 1000], ['bar', 'Bar', 100000],
            ['atm', 'Atmosfere (atm)', 101325], ['psi', 'PSI', 6894.76], ['mmhg', 'mmHg', 133.322]] },
        { id: 'data', nome: '\ud83d\udcbe Dati', titolo: 'Dati digitali', da: 'MB', a: 'GB', unita: [
            ['b', 'Bit', 1], ['B', 'Byte', 8], ['KB', 'Kilobyte (KB)', 8e3], ['MB', 'Megabyte (MB)', 8e6],
            ['GB', 'Gigabyte (GB)', 8e9], ['TB', 'Terabyte (TB)', 8e12], ['PB', 'Petabyte (PB)', 8e15]] }
    ];

    var corrente = GRANDEZZE[0];
    function el(id) { return document.getElementById(id); }

    function simbolo(g, codice) {
        if (g.id === 'temp') { return codice === 'k' ? 'K' : '\u00b0' + codice.toUpperCase(); }
        return codice;
    }

    function fattore(g, codice) {
        for (var i = 0; i < g.unita.length; i++) { if (g.unita[i][0] === codice) { return g.unita[i][2]; } }
        return NaN;
    }

    function converti() {
        var g = corrente, v = parseFloat(el('conv-valore').value), da = el('conv-da').value, a = el('conv-a').value;
        var out = el('conv-risultato');
        if (isNaN(v)) { out.textContent = el('conv-valore').value === '' ? 'Il risultato apparir\u00e0 qui' : 'Inserisci un valore valido'; return; }
        var r;
        if (g.id === 'temp') {
            var c = da === 'c' ? v : da === 'f' ? (v - 32) * 5 / 9 : v - 273.15;
            r = a === 'c' ? c : a === 'f' ? (c * 9 / 5) + 32 : c + 273.15;
            out.textContent = v + ' ' + simbolo(g, da) + ' = ' + r.toFixed(2) + ' ' + simbolo(g, a);
            return;
        }
        r = (v * fattore(g, da)) / fattore(g, a);
        out.textContent = v + ' ' + da + ' = ' + r.toFixed(6) + ' ' + a;
    }

    function riempiSelect(sel, g, scelto) {
        sel.textContent = '';
        g.unita.forEach(function (u) {
            var o = document.createElement('option');
            o.value = u[0];
            o.textContent = u[1];
            if (u[0] === scelto) { o.selected = true; }
            sel.appendChild(o);
        });
    }

    function mostra(g) {
        corrente = g;
        el('conv-titolo').textContent = g.titolo;
        riempiSelect(el('conv-da'), g, g.da);
        riempiSelect(el('conv-a'), g, g.a);
        Array.prototype.forEach.call(el('conv-schede').children, function (b) {
            b.classList.toggle('attiva', b.dataset.id === g.id);
        });
        converti();
    }

    GRANDEZZE.forEach(function (g) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'conv-scheda';
        b.dataset.id = g.id;
        b.textContent = g.nome;
        b.addEventListener('click', function () { mostra(g); });
        el('conv-schede').appendChild(b);
    });

    el('conv-valore').addEventListener('input', converti);
    el('conv-da').addEventListener('change', converti);
    el('conv-a').addEventListener('change', converti);
    el('conv-scambia').addEventListener('click', function () {
        var da = el('conv-da').value;
        el('conv-da').value = el('conv-a').value;
        el('conv-a').value = da;
        converti();
    });

    mostra(GRANDEZZE[0]);
})();
</script>
</body>
</html>
