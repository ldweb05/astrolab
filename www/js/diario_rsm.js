/**
 * diario_rsm.js - Pagina Diario RSM (diario.php)
 * docs/roadmaps/ROADMAP_DIARIO_RSM.md, blocco C, Fase 3.
 *
 * Nessun handler inline: un solo script, eventi collegati da qui.
 * Tutti i dati scritti dagli utenti sono inseriti con textContent,
 * mai con innerHTML. I link esterni hanno rel="noopener noreferrer nofollow ugc".
 */
(function () {
    'use strict';

    var API = 'api/diario_rsm_api.php';
    var MESI = ['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio',
                'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];
    var MEZZI = { aereo: '\u2708\ufe0f', nave: '\u26f4\ufe0f', treno: '\ud83d\ude86', bus: '\ud83d\ude8c',
                  auto: '\ud83d\ude97', altro: '\u27a1\ufe0f' };
    var TIPI_ALLOGGIO = { hotel: 'Hotel', bb: 'B&B', appartamento: 'Appartamento',
                          guesthouse: 'Guesthouse', campeggio: 'Campeggio', altro: 'Altro' };

    var stato = {
        ruolo: document.body.getAttribute('data-ruolo') || '',
        admin: document.body.getAttribute('data-admin') === '1',
        token: null,
        timerRicerca: null
    };

    // ── Utilita' ─────────────────────────────────────────────────

    function el(id) { return document.getElementById(id); }

    function nuovo(tag, classe, testo) {
        var e = document.createElement(tag);
        if (classe) { e.className = classe; }
        if (testo !== undefined && testo !== null) { e.textContent = String(testo); }
        return e;
    }

    function svuota(e) { while (e.firstChild) { e.removeChild(e.firstChild); } }

    function messaggio(id, testo, errore) {
        var m = el(id);
        m.textContent = testo || '';
        m.className = 'diario-msg' + (errore ? ' err' : '');
    }

    function apiGet(azione, parametri) {
        var qs = new URLSearchParams(Object.assign({ action: azione }, parametri || {}));
        return fetch(API + '?' + qs.toString(), { credentials: 'same-origin' })
            .then(function (r) {
                if (r.status === 401) { window.location.href = 'login.php'; throw new Error('401'); }
                return r.json();
            });
    }

    function apiPost(azione, dati) {
        return fetch(API, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': stato.token || '' },
            body: JSON.stringify(Object.assign({ action: azione }, dati || {}))
        }).then(function (r) {
            if (r.status === 401) { window.location.href = 'login.php'; throw new Error('401'); }
            return r.json();
        });
    }

    function periodo(anno, mese) {
        return mese ? MESI[mese] + ' ' + anno : String(anno);
    }

    function importo(valore, valuta) {
        if (valore === null || valore === undefined || valore === '') { return ''; }
        var n = Number(valore);
        return (isNaN(n) ? String(valore) : n.toLocaleString('it-IT', { maximumFractionDigits: 2 })) +
               ' ' + (valuta || '');
    }

    function riga(contenitore, etichetta, testo, lungo) {
        if (testo === null || testo === undefined || testo === '') { return; }
        var r = nuovo('div', 'diario-riga');
        r.appendChild(nuovo('span', 'etichetta', etichetta + ': '));
        r.appendChild(nuovo('span', lungo ? 'diario-testo-lungo' : '', testo));
        contenitore.appendChild(r);
    }

    // ── Schede della pagina ─────────────────────────────────────

    function mostraScheda(nome) {
        document.querySelectorAll('.diario-scheda-btn').forEach(function (b) {
            b.classList.toggle('attiva', b.getAttribute('data-scheda') === nome);
        });
        document.querySelectorAll('.diario-pannello').forEach(function (p) {
            p.classList.toggle('attivo', p.id === 'pannello-' + nome);
        });
    }

    // ── Ricerca ──────────────────────────────────────────────────

    function vociLocalita(localita, contenitore) {
        var ul = nuovo('ul', 'diario-elenco');
        localita.forEach(function (l) {
            var li = nuovo('li');
            var b = nuovo('button', 'diario-voce');
            b.type = 'button';
            var nome = nuovo('span', '', l.luogo);
            if (l.nazione) { nome.appendChild(nuovo('span', 'nota', l.nazione)); }
            b.appendChild(nome);
            b.appendChild(nuovo('span', 'conteggio', l.contributi));
            b.addEventListener('click', function () { apriScheda(l.iso, l.chiave, l.luogo, l.nazione); });
            li.appendChild(b);
            ul.appendChild(li);
        });
        contenitore.appendChild(ul);
    }

    function elencoNazioni() {
        var ris = el('cerca-risultati');
        messaggio('cerca-msg', 'Caricamento...');
        apiGet('cerca').then(function (d) {
            svuota(ris);
            var nazioni = d.nazioni_con_contributi || [];
            if (!nazioni.length) {
                messaggio('cerca-msg', 'Non ci sono ancora contributi: scrivi il primo dalla sezione "I miei contributi".');
                return;
            }
            messaggio('cerca-msg', '');
            ris.appendChild(nuovo('div', 'diario-sezione-titolo', 'Nazioni con contributi'));
            var ul = nuovo('ul', 'diario-elenco');
            nazioni.forEach(function (n) {
                var li = nuovo('li');
                var b = nuovo('button', 'diario-voce');
                b.type = 'button';
                var nome = nuovo('span', '', n.nome);
                if (n.appartiene_a) { nome.appendChild(nuovo('span', 'nota', '(' + n.appartiene_a + ')')); }
                b.appendChild(nome);
                b.appendChild(nuovo('span', 'conteggio', n.contributi));
                b.addEventListener('click', function () { apriNazione(n.iso); });
                li.appendChild(b);
                ul.appendChild(li);
            });
            ris.appendChild(ul);
        }).catch(function () { messaggio('cerca-msg', 'Errore di caricamento. Riprova.', true); });
    }

    function cerca(testo) {
        var ris = el('cerca-risultati');
        if (testo.length < 2) {
            if (testo.length === 0) { elencoNazioni(); }
            return;
        }
        apiGet('cerca', { q: testo }).then(function (d) {
            if (el('cerca-testo').value.trim() !== testo) { return; } // risposta superata
            svuota(ris);
            if (d.errore) { messaggio('cerca-msg', d.errore, true); return; }
            var nazioni = d.nazioni || [];
            var localita = d.localita || [];
            if (!nazioni.length && !localita.length) {
                messaggio('cerca-msg', 'Nessun risultato per "' + testo + '".');
                return;
            }
            messaggio('cerca-msg', '');
            if (nazioni.length) {
                ris.appendChild(nuovo('div', 'diario-sezione-titolo', 'Nazioni'));
                nazioni.forEach(function (n) {
                    var blocco = nuovo('div', 'diario-nazione-blocco');
                    blocco.appendChild(nuovo('div', 'diario-nazione-nome', n.nome));
                    if (n.localita && n.localita.length) {
                        vociLocalita(n.localita, blocco);
                    } else {
                        blocco.appendChild(nuovo('div', 'diario-msg', 'Nessun contributo per questa nazione.'));
                    }
                    ris.appendChild(blocco);
                });
            }
            if (localita.length) {
                ris.appendChild(nuovo('div', 'diario-sezione-titolo', 'Localit\u00e0'));
                vociLocalita(localita, ris);
            }
        }).catch(function () { messaggio('cerca-msg', 'Errore di ricerca. Riprova.', true); });
    }

    function apriNazione(iso) {
        apiGet('nazione', { iso: iso }).then(function (d) {
            var ris = el('cerca-risultati');
            svuota(ris);
            if (d.errore) { messaggio('cerca-msg', d.errore, true); return; }
            messaggio('cerca-msg', '');
            var indietro = nuovo('button', 'diario-indietro', '\u2190 Tutte le nazioni');
            indietro.type = 'button';
            indietro.addEventListener('click', function () { el('cerca-testo').value = ''; elencoNazioni(); });
            ris.appendChild(indietro);
            ris.appendChild(nuovo('div', 'diario-nazione-nome', d.nazione.nome));
            if (d.nazione.localita.length) {
                vociLocalita(d.nazione.localita, ris);
            } else {
                ris.appendChild(nuovo('div', 'diario-msg', 'Nessun contributo per questa nazione.'));
            }
        }).catch(function () { messaggio('cerca-msg', 'Errore di caricamento. Riprova.', true); });
    }

    // ── Scheda della localita' ───────────────────────────────────

    function apriScheda(iso, chiave, luogo, nazione) {
        el('cerca-vista-ricerca').style.display = 'none';
        el('cerca-vista-scheda').style.display = '';
        el('scheda-titolo').textContent = luogo + (nazione ? ' \u2014 ' + nazione : '');
        svuota(el('scheda-contributi'));
        messaggio('scheda-msg', 'Caricamento...');
        apiGet('scheda', { iso: iso, chiave: chiave }).then(function (d) {
            if (d.errore) { messaggio('scheda-msg', d.errore, true); return; }
            var contributi = d.contributi || [];
            messaggio('scheda-msg', contributi.length ? '' : 'Nessun contributo visibile per questa localit\u00e0.');
            contributi.forEach(function (c) { el('scheda-contributi').appendChild(cartaContributo(c)); });
        }).catch(function () { messaggio('scheda-msg', 'Errore di caricamento. Riprova.', true); });
    }

    function chiudiScheda() {
        el('cerca-vista-scheda').style.display = 'none';
        el('cerca-vista-ricerca').style.display = '';
    }

    function cartaContributo(c) {
        var carta = nuovo('article', 'diario-contributo' + (c.visibile ? '' : ' nascosto'));
        var testa = nuovo('div', 'diario-contributo-testa');
        testa.appendChild(nuovo('span', '', 'Viaggio: ' + periodo(c.anno_viaggio, c.mese_viaggio)));
        testa.appendChild(nuovo('span', '', 'di ' + c.autore + (c.visibile ? '' : ' \u00b7 nascosto')));
        carta.appendChild(testa);

        if (c.tratte && c.tratte.length) {
            var t = nuovo('div', 'diario-riga');
            t.appendChild(nuovo('span', 'etichetta', 'Come arrivare:'));
            var ul = nuovo('ul', 'diario-tratte');
            c.tratte.forEach(function (tr) {
                var parti = [];
                if (tr.da_luogo || tr.a_luogo) { parti.push((tr.da_luogo || '?') + ' \u2192 ' + (tr.a_luogo || '?')); }
                if (tr.compagnia) { parti.push(tr.compagnia); }
                if (tr.durata_indicativa) { parti.push(tr.durata_indicativa); }
                if (tr.costo_indicativo !== null && tr.costo_indicativo !== '') { parti.push(importo(tr.costo_indicativo, tr.valuta)); }
                var li = nuovo('li', '', (MEZZI[tr.mezzo] || '') + ' ' + parti.join(' \u00b7 '));
                if (tr.note) { li.appendChild(nuovo('div', 'diario-testo-lungo nota', tr.note)); }
                ul.appendChild(li);
            });
            t.appendChild(ul);
            carta.appendChild(t);
        }

        if (c.alloggio_nome || c.alloggio_tipo || c.alloggio_sito) {
            var a = nuovo('div', 'diario-riga');
            a.appendChild(nuovo('span', 'etichetta', 'Alloggio: '));
            var descr = [c.alloggio_nome, TIPI_ALLOGGIO[c.alloggio_tipo], c.alloggio_fascia_prezzo]
                .filter(function (x) { return x; }).join(' \u00b7 ');
            a.appendChild(nuovo('span', '', descr));
            if (c.alloggio_sito && /^https?:\/\//i.test(c.alloggio_sito)) {
                var link = nuovo('a', '', ' sito');
                link.href = c.alloggio_sito;
                link.target = '_blank';
                link.rel = 'noopener noreferrer nofollow ugc';
                a.appendChild(document.createTextNode(' \u00b7'));
                a.appendChild(link);
            }
            carta.appendChild(a);
        }
        riga(carta, 'Giudizio sull\'alloggio', c.alloggio_giudizio, true);
        if (c.costo_alloggio_indicativo !== null && c.costo_alloggio_indicativo !== '') {
            riga(carta, 'Spesa indicativa alloggio',
                importo(c.costo_alloggio_indicativo, c.valuta) +
                (c.costo_alloggio_riferimento ? ' (' + c.costo_alloggio_riferimento + ')' : ''));
        }
        riga(carta, 'Documenti e visti', c.info_documenti, true);
        riga(carta, 'Clima', c.info_clima, true);
        riga(carta, 'Lingua e valuta', c.info_lingua_valuta, true);
        riga(carta, 'Connettivit\u00e0', c.info_connettivita, true);
        riga(carta, 'Particolarit\u00e0 locali', c.info_particolarita, true);
        riga(carta, 'Contatti utili', c.contatti_utili, true);
        riga(carta, 'Consigli', c.consigli, true);
        return carta;
    }

    // ── Avvio ────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.diario-scheda-btn').forEach(function (b) {
            b.addEventListener('click', function () { mostraScheda(b.getAttribute('data-scheda')); });
        });
        el('scheda-indietro').addEventListener('click', chiudiScheda);
        el('cerca-testo').addEventListener('input', function () {
            clearTimeout(stato.timerRicerca);
            var testo = el('cerca-testo').value.trim();
            stato.timerRicerca = setTimeout(function () { cerca(testo); }, 300);
        });

        apiGet('token').then(function (d) { stato.token = d.token || null; }).catch(function () {});
        elencoNazioni();
    });

    // Esposto per le fasi successive (contributi, viaggi).
    window.DiarioRsm = { stato: stato, apiGet: apiGet, apiPost: apiPost, mostraScheda: mostraScheda,
                         apriScheda: apriScheda, nuovo: nuovo, svuota: svuota, messaggio: messaggio };
})();
