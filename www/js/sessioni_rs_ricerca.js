/**
 * js/sessioni_rs_ricerca.js — Pannello sessioni salvate in ricerca.php e ricerca_rl.php
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * Mostra, per il soggetto scelto in #sel-soggetto, le sessioni salvate del tipo
 * della pagina (RS e RL sono distinte e separate, mai mescolate):
 *  - ricerca.php     → <div id="sessioni-rs-ricerca"></div>               → sessioni RS
 *  - ricerca_rl.php  → <div id="sessioni-rs-ricerca" data-tipo="rl"></div> → sessioni RL
 *
 * RS: stesse colonne e azioni di rs.php (richiama, leggi note, modifica note, elimina).
 * RL: stesse colonne di rl.php (richiama, leggi note, elimina). La modifica delle
 *     note RL non c'e' perche' l'API non ha "modifica_rl" (BL-0006).
 * Usa api/sessioni_api.php (lista_rs/lista_rl, modifica_rs, elimina_rs/elimina_rl)
 * senza modificarla.
 *
 * Usato SOLO dalle pagine di ricerca: rs.php e rl.php mantengono il proprio codice.
 * Richiede js/sicurezza.js (window.escHtml) caricato prima.
 * Id interni con prefisso "srr-" per non collidere con quelli delle pagine.
 */
(function () {
    'use strict';

    var API = 'api/sessioni_api.php';
    var MAX_NOTE = 500;

    var CONFIG = {
        rs: {
            titolo: '📂 Sessioni RS salvate per questo soggetto',
            lista: 'lista_rs',
            elimina: 'elimina_rs',
            modifica: 'modifica_rs',
            sigla: 'RSM',
            confermaElimina: 'Eliminare questa sessione salvata?',
            intestazioni: ['Anno', 'Luogo', 'Condizione', 'Stelle', 'VAL', 'Note', 'Salvata il', 'Azioni'],
            titoloRichiama: 'Richiama questa sessione in RS'
        },
        rl: {
            titolo: '☽ Sessioni RL salvate per questo soggetto',
            lista: 'lista_rl',
            elimina: 'elimina_rl',
            modifica: null, // l'API non ha modifica_rl (BL-0006)
            sigla: 'RL',
            confermaElimina: 'Eliminare questa sessione RL salvata?',
            intestazioni: ['Anno rif.', 'RL #', 'Luogo', 'Condizione', 'Stelle', 'VAL', 'Note', 'Salvata il', 'Azioni'],
            titoloRichiama: 'Richiama questa sessione in RL'
        }
    };

    var cfg = CONFIG.rs;
    var sessioni = new Map();
    var soggettoCorrente = '';
    var richiestaCorrente = 0;
    var ultimoCaricamento = 0;

    function esc(valore) {
        return window.escHtml ? window.escHtml(valore) : String(valore == null ? '' : valore);
    }

    function el(id) {
        return document.getElementById(id);
    }

    // ── Struttura HTML (card collassabile + modale note) ─────────────────
    function montaStruttura(contenitore) {
        contenitore.innerHTML =
            '<div class="card is-hidden" id="srr-card">' +
                '<h3 class="collapse-toggle" id="srr-toggle" role="button" tabindex="0" aria-expanded="false">' +
                    esc(cfg.titolo) + ' ' +
                    '<span class="sensib-chevron" id="srr-chevron">▶</span>' +
                '</h3>' +
                '<div id="srr-body" style="display:none">' +
                    '<div id="srr-lista"></div>' +
                '</div>' +
            '</div>' +
            '<div id="srr-modale" class="annual-report-modal is-hidden">' +
                '<div class="annual-report-window" style="width:min(620px,calc(100vw - 48px));min-height:280px;resize:both;overflow:auto;">' +
                    '<div class="annual-report-header">' +
                        '<div class="val-stringa" id="srr-modale-titolo"></div>' +
                        '<button type="button" data-srr="chiudi" title="Chiudi" aria-label="Chiudi" ' +
                                'class="annual-report-icon annual-report-close">×</button>' +
                    '</div>' +
                    '<div class="annual-report-content">' +
                        '<input type="hidden" id="srr-modale-id">' +
                        '<label for="srr-modale-testo">Note per ricordare le caratteristiche della sessione salvata</label>' +
                        '<textarea id="srr-modale-testo" maxlength="' + MAX_NOTE + '" rows="7" ' +
                                  'style="width:100%;box-sizing:border-box;margin-top:10px;padding:12px;font:inherit;resize:vertical;overflow-y:auto;"></textarea>' +
                        '<div style="margin-top:8px;text-align:right;">Caratteri disponibili: ' +
                            '<strong id="srr-modale-contatore">' + MAX_NOTE + '</strong></div>' +
                        '<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">' +
                            '<button type="button" id="srr-modale-annulla" data-srr="chiudi">Annulla</button>' +
                            '<button type="button" id="srr-modale-salva" class="btn-primary" data-srr="salva-note">💾 Salva Note</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
    }

    // ── Apri/chiudi pannello ─────────────────────────────────────────────
    function togglePannello() {
        var body = el('srr-body');
        var chevron = el('srr-chevron');
        var aperto = body.style.display !== 'none';
        body.style.display = aperto ? 'none' : 'block';
        chevron.classList.toggle('aperto', !aperto);
        el('srr-toggle').setAttribute('aria-expanded', aperto ? 'false' : 'true');
    }

    // ── Caricamento elenco ───────────────────────────────────────────────
    function nascondiCard() {
        sessioni = new Map();
        el('srr-card').classList.add('is-hidden');
        el('srr-lista').innerHTML = '';
    }

    function carica() {
        var sel = el('sel-soggetto');
        soggettoCorrente = sel ? String(sel.value || '') : '';
        var idRichiesta = ++richiestaCorrente;
        ultimoCaricamento = Date.now();

        if (!soggettoCorrente) {
            nascondiCard();
            return;
        }

        fetch(API + '?action=' + cfg.lista + '&soggetto_id=' + encodeURIComponent(soggettoCorrente))
            .then(function (r) { return r.json(); })
            .then(function (righe) {
                // Ignora risposte arrivate dopo un cambio di soggetto
                if (idRichiesta !== richiestaCorrente) return;
                if (!Array.isArray(righe) || righe.length === 0) {
                    nascondiCard();
                    return;
                }
                sessioni = new Map(righe.map(function (s) { return [Number(s.id), s]; }));
                el('srr-lista').innerHTML = costruisciTabella(righe);
                el('srr-card').classList.remove('is-hidden');
            })
            .catch(function () {
                if (idRichiesta === richiestaCorrente) nascondiCard();
            });
    }

    function urlRichiama(s) {
        if (cfg === CONFIG.rl) {
            return 'rl.php?id=' + encodeURIComponent(soggettoCorrente) +
                '&anno=' + encodeURIComponent(s.anno_rs || '') +
                '&lat_rl=' + encodeURIComponent(s.lat) + '&lon_rl=' + encodeURIComponent(s.lon) +
                '&luogo_rl=' + encodeURIComponent(s.luogo || '');
        }
        return 'rs.php?id=' + encodeURIComponent(soggettoCorrente) +
            '&anno=' + encodeURIComponent(s.anno) +
            '&lat_rs=' + encodeURIComponent(s.lat) + '&lon_rs=' + encodeURIComponent(s.lon) +
            '&luogo_rs=' + encodeURIComponent(s.luogo || '') +
            '&condizione=' + encodeURIComponent(s.condizione || '');
    }

    function costruisciTabella(righe) {
        var html = '<table class="tabella-soggetti"><thead><tr>' +
            cfg.intestazioni.map(function (t) { return '<th>' + esc(t) + '</th>'; }).join('') +
            '</tr></thead><tbody>';

        righe.forEach(function (s) {
            var id = parseInt(s.id, 10);
            var n = s.stelline != null ? Math.max(0, Math.min(5, Math.round(s.stelline))) : null;
            var stelle = n !== null ? '★'.repeat(n) + '☆'.repeat(5 - n) : '—';
            var dataSalv = s.creato_il
                ? new Date(s.creato_il).toLocaleString('it-IT',
                    {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'})
                : '—';

            var colonneIniziali = cfg === CONFIG.rl
                ? '<td>' + esc(s.anno_rs != null ? s.anno_rs : '—') + '</td>' +
                  '<td>☽ ' + esc(s.rl_index) + '</td>'
                : '<td>' + esc(s.anno) + '</td>';

            html += '<tr>' +
                colonneIniziali +
                '<td>' + esc(s.luogo || '—') + '</td>' +
                '<td>' + esc(s.condizione) + '</td>' +
                '<td class="stelle">' + stelle + '</td>' +
                '<td><span class="val-badge">' + esc(s.val || '—') + '</span></td>' +
                '<td class="session-note-cell">' +
                    '<div style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' +
                        esc(s.note || '') +
                    '</div>' +
                    (s.note
                        ? '<button type="button" class="btn-icon" style="margin-top:6px;font-size:12px;" ' +
                          'data-srr="leggi" data-id="' + id + '">Leggi tutto</button>'
                        : '') +
                '</td>' +
                '<td class="session-date-cell">' + esc(dataSalv) + '</td>' +
                '<td><div class="azioni">' +
                    '<a href="' + esc(urlRichiama(s)) + '" class="btn-icon" target="_blank" rel="noopener" title="' + esc(cfg.titoloRichiama) + '">↺</a>' +
                    (cfg.modifica
                        ? '<button type="button" class="btn-icon" title="Modifica note" data-srr="modifica" data-id="' + id + '">✏️</button>'
                        : '') +
                    '<button type="button" class="btn-icon" title="Cancella" data-srr="elimina" data-id="' + id + '">🗑️</button>' +
                '</div></td>' +
            '</tr>';
        });

        return html + '</tbody></table>';
    }

    // ── Modale note ──────────────────────────────────────────────────────
    function aggiornaContatore() {
        el('srr-modale-contatore').textContent = Math.max(0, MAX_NOTE - el('srr-modale-testo').value.length);
    }

    function apriModale(id, soloLettura) {
        if (!soloLettura && !cfg.modifica) return;
        var s = sessioni.get(Number(id));
        if (!s) {
            alert('Sessione non trovata.');
            return;
        }
        var campo = el('srr-modale-testo');
        el('srr-modale-id').value = soloLettura ? '' : Number(id);
        el('srr-modale-titolo').textContent = soloLettura
            ? '📖 Note Sessione ' + cfg.sigla
            : '✏️ Modifica Note Sessione ' + cfg.sigla;
        el('srr-modale-annulla').textContent = soloLettura ? 'Chiudi' : 'Annulla';
        el('srr-modale-salva').style.display = soloLettura ? 'none' : '';
        campo.readOnly = !!soloLettura;
        campo.value = s.note || '';
        aggiornaContatore();
        el('srr-modale').classList.remove('is-hidden');
        if (!soloLettura) campo.focus();
    }

    function chiudiModale() {
        el('srr-modale').classList.add('is-hidden');
        el('srr-modale-id').value = '';
        var campo = el('srr-modale-testo');
        campo.value = '';
        campo.readOnly = false;
        aggiornaContatore();
    }

    function salvaNote() {
        var id = Number(el('srr-modale-id').value);
        if (!id || !cfg.modifica) return;
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: cfg.modifica, id: id, note: el('srr-modale-testo').value})
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    chiudiModale();
                    carica();
                } else {
                    alert(data.errore || 'Errore durante la modifica delle Note.');
                }
            })
            .catch(function (e) { alert('Errore rete: ' + e.message); });
    }

    function elimina(id) {
        if (!confirm(cfg.confermaElimina)) return;
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: cfg.elimina, id: Number(id)})
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    carica();
                } else {
                    alert(data.errore || 'Errore durante l\'eliminazione.');
                }
            })
            .catch(function (e) { alert('Errore rete: ' + e.message); });
    }

    // ── Avvio ────────────────────────────────────────────────────────────
    function avvia() {
        var contenitore = el('sessioni-rs-ricerca');
        if (!contenitore) return;
        cfg = contenitore.getAttribute('data-tipo') === 'rl' ? CONFIG.rl : CONFIG.rs;
        montaStruttura(contenitore);

        var toggle = el('srr-toggle');
        toggle.addEventListener('click', togglePannello);
        toggle.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePannello();
            }
        });

        // Azioni con delega: data-srr + data-id (niente dati negli onclick)
        contenitore.addEventListener('click', function (e) {
            var bottone = e.target.closest('[data-srr]');
            if (!bottone || !contenitore.contains(bottone)) return;
            var azione = bottone.getAttribute('data-srr');
            var id = bottone.getAttribute('data-id');
            if (azione === 'leggi') apriModale(id, true);
            else if (azione === 'modifica') apriModale(id, false);
            else if (azione === 'elimina') elimina(id);
            else if (azione === 'chiudi') chiudiModale();
            else if (azione === 'salva-note') salvaNote();
        });

        el('srr-modale-testo').addEventListener('input', aggiornaContatore);

        var sel = el('sel-soggetto');
        if (sel) sel.addEventListener('change', carica);

        // Rientro sulla scheda/finestra (es. dopo aver salvato una sessione in
        // rs.php/rl.php aperta in un'altra scheda): ricarica l'elenco. Focus e
        // visibilita' possono scattare insieme: al massimo un caricamento ogni 1,5 s.
        function ricaricaAlRientro() {
            if (document.visibilityState !== 'visible') return;
            if (Date.now() - ultimoCaricamento < 1500) return;
            carica();
        }
        document.addEventListener('visibilitychange', ricaricaAlRientro);
        window.addEventListener('focus', ricaricaAlRientro);

        carica();
    }

    window.SessioniRSRicerca = {ricarica: carica};

    // Il listener e' registrato dopo quello della pagina, quindi parte dopo
    // ripristinaStatoRicerca() (che puo' reimpostare #sel-soggetto).
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', avvia);
    } else {
        avvia();
    }
})();
