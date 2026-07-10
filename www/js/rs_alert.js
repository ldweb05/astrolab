/**
 * js/rs_alert.js — Alert non bloccanti: attivazione stellium natale nella RS
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * API pubblica:
 *   RSAlert.aggiorna(params)  → chiama rs_alert_api.php e aggiorna il DOM
 *   RSAlert.nascondi()        → svuota e nasconde il contenitore degli alert
 *
 * Il modulo è completamente autonomo: non tocca nessuna variabile esistente
 * in rs.php e non interferisce con il RuleEngine o il flusso rs_api.php.
 *
 * Dipendenze: nessuna (Vanilla JS puro).
 */

'use strict';

const RSAlert = (function () {

    // ID del contenitore degli alert nel DOM (inserito da rs.php)
    const CONTAINER_ID = 'rs-alert-stellium';

    // Pallini colorati (testo) per i tre livelli di severità
    const PALLINI = {
        '#dc3545': '🔴',
        '#ff4500': '🟠',
        '#28a745': '🟢',
    };

    /**
     * Chiama l'API, poi aggiorna (o svuota) il contenitore degli alert.
     *
     * @param {object} params — stessi parametri di rs_api.php:
     *   { g, m, a, ora_gmt, lat, lon, anno, lat_rs, lon_rs }
     */
    function aggiorna(params) {
        const el = document.getElementById(CONTAINER_ID);
        if (!el) return;

        // Reset visivo immediato (evita di mostrare alert della RS precedente
        // mentre la nuova è ancora in calcolo)
        el.innerHTML = '';
        el.style.display = 'none';

        const url = 'api/rs_alert_api.php?' + new URLSearchParams({
            g:       params.g,
            m:       params.m,
            a:       params.a,
            ora_gmt: params.ora_gmt,
            lat:     params.lat,
            lon:     params.lon,
            anno:    params.anno,
            lat_rs:  params.lat_rs,
            lon_rs:  params.lon_rs,
        }).toString();

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok || !data.alerts || data.alerts.length === 0) {
                    nascondi();
                    return;
                }
                _render(el, data.alerts);
            })
            .catch(function (err) {
                // Silenzioso: gli alert non devono mai bloccare il flusso principale
                console.warn('[RSAlert] errore fetch:', err.message);
            });
    }

    /**
     * Svuota e nasconde il contenitore.
     */
    function nascondi() {
        const el = document.getElementById(CONTAINER_ID);
        if (!el) return;
        el.innerHTML = '';
        el.style.display = 'none';
    }

    /**
     * Costruisce l'HTML degli alert e lo inietta nel DOM.
     * @private
     */
    function _render(el, alerts) {
        var html = '<div class="rs-alert-header">⚠️ Stellium Natale — Possibili attivazioni nella RS</div>';

        alerts.forEach(function (alert) {
            var pallino = PALLINI[alert.colore] || '●';
            html += '<div class="rs-alert-item" style="border-left-color:' + alert.colore + '">' +
                        '<span class="rs-alert-pallino" style="color:' + alert.colore + '">' + pallino + '</span>' +
                        '<span class="rs-alert-msg">' + _esc(alert.messaggio) + '</span>' +
                    '</div>';
        });

        el.innerHTML = html;
        el.style.display = 'block';
    }

    /**
     * Escaping HTML minimale (no librerie esterne).
     * @private
     */
    function _esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── API pubblica ──────────────────────────────────────────────────────
    return {
        aggiorna: aggiorna,
        nascondi: nascondi,
    };

})();
