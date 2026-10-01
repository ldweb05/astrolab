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
 *   collegaScelta(menu, funzione) -> gestore di clic unico per le voci data-scelta di un menu.
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

    /**
     * Collega una sola volta al menu un gestore di clic: alla scelta di una voce con
     * l'attributo data-scelta chiama funzione(dataset della voce). Serve ai menu dei
     * risultati (es. ricerca luoghi) al posto degli onclick con i dati dentro.
     */
    function collegaScelta(contenitore, funzione) {
        if (!contenitore || contenitore.dataset.sceltaCollegata === '1') {
            return;
        }
        contenitore.dataset.sceltaCollegata = '1';
        contenitore.addEventListener('click', function (ev) {
            var voce = ev.target.closest ? ev.target.closest('[data-scelta]') : null;
            if (voce && contenitore.contains(voce)) {
                funzione(voce.dataset);
            }
        });
    }

    window.escHtml = escHtml;
    window.urlSicuro = urlSicuro;
    window.collegaScelta = collegaScelta;
})();
