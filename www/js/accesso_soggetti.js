/**
 * accesso_soggetti.js - Gestione dell'accesso dei soggetti dalla lista soggetti
 * docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md, Fase B3 (C11).
 *
 * Il pulsante di ogni riga ha classe "btn-accesso" e gli attributi
 * data-accesso-id / data-accesso-codice / data-accesso-stato.
 * Un solo gestore di clic delegato, nessun handler inline; i testi sono
 * inseriti con textContent (mai innerHTML con dati).
 */
(function () {
    'use strict';

    var STATI = {
        nessuno:     'Accesso non abilitato',
        attivo:      'Accesso attivo',
        disattivato: 'Accesso disattivato'
    };

    var corrente = null; // { id, codice, stato }

    function el(id) { return document.getElementById(id); }

    function mostra(elemento, visibile) {
        if (elemento) { elemento.style.display = visibile ? '' : 'none'; }
    }

    function messaggio(testo, tipo) {
        var m = el('acc-msg');
        m.textContent = testo || '';
        m.className = 'idx-modal-msg' + (tipo ? ' ' + tipo : '');
    }

    function aggiornaPulsanti() {
        var stato = corrente.stato;
        el('acc-stato').textContent = STATI[stato] || STATI.nessuno;
        mostra(el('acc-btn-abilita'), stato === 'nessuno');
        mostra(el('acc-btn-rigenera'), stato === 'attivo');
        mostra(el('acc-btn-riattiva'), stato === 'disattivato');
        mostra(el('acc-btn-disattiva'), stato === 'attivo');
    }

    function apri(id, codice, stato) {
        corrente = { id: id, codice: codice, stato: stato };
        el('acc-codice').textContent = codice;
        mostra(el('acc-risultato'), false);
        el('acc-password').textContent = '';
        messaggio('');
        aggiornaPulsanti();
        el('acc-modale-overlay').classList.add('is-open');
    }

    function chiudi() {
        el('acc-modale-overlay').classList.remove('is-open');
        el('acc-password').textContent = '';
        corrente = null;
        if (typeof caricaSoggettiConDropdown === 'function') {
            caricaSoggettiConDropdown();
        }
    }

    function chiamaApi(azione) {
        return fetch('api/soggetti_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: azione, id: corrente.id })
        }).then(function (r) { return r.json(); });
    }

    function genera() {
        if (!corrente) { return; }
        var domanda = corrente.stato === 'nessuno'
            ? 'Abilitare l\'accesso di ' + corrente.codice + '?'
            : 'Generare una nuova password provvisoria per ' + corrente.codice +
              '? Quella attuale smettera\' di funzionare.';
        if (!window.confirm(domanda)) { return; }
        messaggio('');
        chiamaApi('accesso_genera').then(function (data) {
            if (!data.ok) {
                messaggio(data.errore || 'Operazione non riuscita.', 'err');
                return;
            }
            corrente.stato = 'attivo';
            aggiornaPulsanti();
            el('acc-password').textContent = data.password;
            mostra(el('acc-risultato'), true);
            messaggio('Accesso attivo. Password provvisoria generata.', 'ok');
        }).catch(function () {
            messaggio('Errore di connessione. Riprova.', 'err');
        });
    }

    function disattiva() {
        if (!corrente) { return; }
        if (!window.confirm('Disattivare l\'accesso di ' + corrente.codice +
                '? Il soggetto non potra\' piu\' entrare finche\' non lo riattivi.')) {
            return;
        }
        chiamaApi('accesso_disattiva').then(function (data) {
            if (!data.ok) {
                messaggio(data.errore || 'Operazione non riuscita.', 'err');
                return;
            }
            corrente.stato = 'disattivato';
            mostra(el('acc-risultato'), false);
            el('acc-password').textContent = '';
            aggiornaPulsanti();
            messaggio('Accesso disattivato.', 'ok');
        }).catch(function () {
            messaggio('Errore di connessione. Riprova.', 'err');
        });
    }

    function copia() {
        var testo = el('acc-password').textContent;
        if (!testo) { return; }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(testo).then(function () {
                messaggio('Password copiata negli appunti.', 'ok');
            }, function () {
                messaggio('Copia non riuscita: selezionala e copiala a mano.', 'err');
            });
        } else {
            messaggio('Copia automatica non disponibile: selezionala e copiala a mano.', 'err');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var lista = el('lista-soggetti');
        if (lista) {
            lista.addEventListener('click', function (ev) {
                var btn = ev.target.closest('.btn-accesso');
                if (!btn) { return; }
                apri(parseInt(btn.getAttribute('data-accesso-id'), 10),
                     btn.getAttribute('data-accesso-codice') || '',
                     btn.getAttribute('data-accesso-stato') || 'nessuno');
            });
        }
        el('acc-btn-chiudi').addEventListener('click', chiudi);
        el('acc-btn-abilita').addEventListener('click', genera);
        el('acc-btn-rigenera').addEventListener('click', genera);
        el('acc-btn-riattiva').addEventListener('click', genera);
        el('acc-btn-disattiva').addEventListener('click', disattiva);
        el('acc-btn-copia').addEventListener('click', copia);
    });
})();
