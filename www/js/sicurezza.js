/**
 * sicurezza.js - Funzioni comuni di protezione contro l'inserimento di codice (XSS).
 * docs/roadmaps/ROADMAP.md, "BUG APERTO - Lista soggetti", passo 2 (decisioni del
 * committente del 01-10-2026: funzione in un file comune; dati negli attributi data-
 * invece che dentro gli onclick).
 *
 * Uso:
 *   escHtml(testo)  -> testo sicuro da inserire in un template HTML, sia nel contenuto
 *                      sia nel valore di un attributo tra virgolette (es. data-nome="...").
 *   urlSicuro(url)  -> l'URL se inizia con http:// o https://, altrimenti stringa vuota.
 *
 * Regola: i dati scritti dagli utenti o arrivati da servizi esterni non vanno mai messi
 * dentro un attributo onclick; si mettono in un attributo data- (protetto con escHtml)
 * e si leggono da JavaScript con dataset al clic.
 */
(function () {
    'use strict';

    var SOSTITUZIONI = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '`': '&#96;' };

    function escHtml(valore) {
        if (valore === null || valore === undefined) {
            return '';
        }
        return String(valore).replace(/[&<>"'`]/g, function (c) { return SOSTITUZIONI[c]; });
    }

    function urlSicuro(url) {
        var testo = String(url === null || url === undefined ? '' : url).trim();
        return /^https?:\/\//i.test(testo) ? testo : '';
    }

    window.escHtml = escHtml;
    window.urlSicuro = urlSicuro;
})();
