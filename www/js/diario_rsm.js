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
        timerRicerca: null,
        nazioni: [],              // [{iso, nome}] per i form (nomi italiani)
        schedaCorrente: null      // {iso, chiave, luogo, nazione}
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
        stato.schedaCorrente = { iso: iso, chiave: chiave, luogo: luogo, nazione: nazione };
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

        var azioni = nuovo('div', 'diario-carta-azioni');
        if (c.mio) {
            var mod = nuovo('button', 'diario-btn secondario piccolo', 'Modifica il mio contributo');
            mod.type = 'button';
            mod.addEventListener('click', function () { modificaContributo(c.id); });
            azioni.appendChild(mod);
        }
        if (stato.admin) {
            var vis = nuovo('button', 'diario-btn pericolo piccolo', c.visibile ? 'Nascondi (moderazione)' : 'Mostra di nuovo');
            vis.type = 'button';
            vis.addEventListener('click', function () { cambiaVisibilita(c.id, !c.visibile); });
            azioni.appendChild(vis);
        }
        if (azioni.firstChild) { carta.appendChild(azioni); }
        return carta;
    }

    function ricaricaScheda() {
        var sc = stato.schedaCorrente;
        if (sc) { apriScheda(sc.iso, sc.chiave, sc.luogo, sc.nazione); }
    }

    function cambiaVisibilita(id, visibile) {
        var domanda = visibile ? 'Rendere di nuovo visibile questo contributo?'
                               : 'Nascondere questo contributo a tutti gli utenti? L\'autore continuer\u00e0 a vederlo tra i suoi.';
        if (!window.confirm(domanda)) { return; }
        apiPost('contributo_visibilita', { id: id, visibile: visibile }).then(function (d) {
            if (!d.ok) { messaggio('scheda-msg', d.errore || 'Operazione non riuscita.', true); return; }
            ricaricaScheda();
        }).catch(function () { messaggio('scheda-msg', 'Errore di connessione. Riprova.', true); });
    }

    // ── Contributi: elenco e form ───────────────────────────────

    var CAMPI_FORM = {
        'cf-alloggio-nome': 'alloggio_nome', 'cf-alloggio-tipo': 'alloggio_tipo',
        'cf-alloggio-sito': 'alloggio_sito', 'cf-alloggio-fascia': 'alloggio_fascia_prezzo',
        'cf-costo': 'costo_alloggio_indicativo', 'cf-costo-rif': 'costo_alloggio_riferimento',
        'cf-alloggio-giudizio': 'alloggio_giudizio', 'cf-documenti': 'info_documenti',
        'cf-clima': 'info_clima', 'cf-lingua': 'info_lingua_valuta',
        'cf-connettivita': 'info_connettivita', 'cf-particolarita': 'info_particolarita',
        'cf-contatti': 'contatti_utili', 'cf-consigli': 'consigli'
    };
    var MAX_TRATTE = 15;

    function caricaNazioni() {
        if (stato.nazioni.length) { return Promise.resolve(); }
        return apiGet('nazioni').then(function (d) {
            stato.nazioni = d.nazioni || [];
            var dl = el('elenco-nazioni');
            svuota(dl);
            stato.nazioni.forEach(function (n) {
                var o = document.createElement('option');
                o.value = n.nome;
                dl.appendChild(o);
            });
        });
    }

    function isoDaNome(nome) {
        var cercato = String(nome || '').trim().toLowerCase();
        for (var i = 0; i < stato.nazioni.length; i++) {
            if (stato.nazioni[i].nome.toLowerCase() === cercato) { return stato.nazioni[i].iso; }
        }
        return null;
    }

    function nomeDaIso(iso) {
        for (var i = 0; i < stato.nazioni.length; i++) {
            if (stato.nazioni[i].iso === iso) { return stato.nazioni[i].nome; }
        }
        return '';
    }

    function elencoContributi(messaggioFinale) {
        var box = el('contrib-elenco');
        messaggio('contrib-msg', 'Caricamento...');
        apiGet('miei_contributi').then(function (d) {
            svuota(box);
            var lista = d.contributi || [];
            messaggio('contrib-msg', messaggioFinale || (lista.length ? '' : 'Non hai ancora scritto contributi.'));
            lista.forEach(function (c) {
                var voce = nuovo('div', 'diario-mio');
                var titolo = nuovo('div', 'diario-mio-titolo', c.luogo + ' \u2014 ' + c.nazione);
                if (!c.visibile) { titolo.appendChild(nuovo('span', 'diario-etichetta-stato', 'nascosto dall\'amministratore')); }
                voce.appendChild(titolo);
                voce.appendChild(nuovo('div', 'diario-mio-info', 'Viaggio: ' + periodo(c.anno_viaggio, c.mese_viaggio)));
                var az = nuovo('div', 'diario-carta-azioni');
                var bApri = nuovo('button', 'diario-btn secondario piccolo', 'Apri la scheda');
                bApri.type = 'button';
                bApri.addEventListener('click', function () {
                    mostraScheda('cerca');
                    apriScheda(c.iso_nazione, c.chiave, c.luogo, c.nazione);
                });
                var bMod = nuovo('button', 'diario-btn secondario piccolo', 'Modifica');
                bMod.type = 'button';
                bMod.addEventListener('click', function () { modificaContributo(c.id); });
                var bDel = nuovo('button', 'diario-btn pericolo piccolo', 'Elimina');
                bDel.type = 'button';
                bDel.addEventListener('click', function () { eliminaContributo(c.id, c.luogo); });
                az.appendChild(bApri); az.appendChild(bMod); az.appendChild(bDel);
                voce.appendChild(az);
                box.appendChild(voce);
            });
        }).catch(function () { messaggio('contrib-msg', 'Errore di caricamento. Riprova.', true); });
    }

    function rigaTratta(t) {
        t = t || {};
        var r = nuovo('div', 'diario-tratta');
        function campo(etichetta, input) {
            var c = nuovo('div', 'diario-campo');
            c.appendChild(nuovo('label', '', etichetta));
            c.appendChild(input);
            r.appendChild(c);
            return input;
        }
        var sel = document.createElement('select');
        [['aereo', 'Aereo'], ['nave', 'Nave'], ['treno', 'Treno'], ['bus', 'Bus'], ['auto', 'Auto'], ['altro', 'Altro']]
            .forEach(function (m) {
                var o = document.createElement('option'); o.value = m[0]; o.textContent = m[1]; sel.appendChild(o);
            });
        sel.value = t.mezzo || 'aereo';
        sel.className = 'tr-mezzo';
        campo('Mezzo', sel);
        function testo(cls, etichetta, max, valore, tipo) {
            var i = document.createElement('input');
            i.type = tipo || 'text'; i.className = cls; i.value = (valore === null || valore === undefined) ? '' : valore;
            if (max) { i.maxLength = max; }
            if (tipo === 'number') { i.min = '0'; i.step = '0.01'; }
            return campo(etichetta, i);
        }
        testo('tr-da', 'Da', 200, t.da_luogo);
        testo('tr-a', 'A', 200, t.a_luogo);
        testo('tr-compagnia', 'Compagnia', 150, t.compagnia);
        testo('tr-durata', 'Durata', 50, t.durata_indicativa);
        testo('tr-costo', 'Costo', 0, t.costo_indicativo, 'number');
        testo('tr-valuta', 'Valuta', 3, t.valuta || 'EUR');
        var p = nuovo('div', 'diario-tratta-pulsanti');
        [['\u2191', -1, 'Sposta su'], ['\u2193', 1, 'Sposta gi\u00f9']].forEach(function (x) {
            var b = nuovo('button', 'diario-btn secondario piccolo', x[0]);
            b.type = 'button'; b.title = x[2];
            b.addEventListener('click', function () {
                var fratello = x[1] < 0 ? r.previousElementSibling : r.nextElementSibling;
                if (!fratello) { return; }
                if (x[1] < 0) { r.parentNode.insertBefore(r, fratello); }
                else { r.parentNode.insertBefore(fratello, r); }
            });
            p.appendChild(b);
        });
        var del = nuovo('button', 'diario-btn pericolo piccolo', '\u2715');
        del.type = 'button'; del.title = 'Elimina la tratta';
        del.addEventListener('click', function () { r.parentNode.removeChild(r); });
        p.appendChild(del);
        r.appendChild(p);
        return r;
    }

    function aggiungiTratta(t) {
        var box = el('cf-tratte');
        if (box.children.length >= MAX_TRATTE) {
            messaggio('cf-msg', 'Puoi inserire al massimo ' + MAX_TRATTE + ' tratte.', true);
            return;
        }
        box.appendChild(rigaTratta(t));
    }

    function apriForm(c) {
        c = c || {};
        caricaNazioni().then(function () {
            mostraScheda('contributi');
            el('contrib-vista-elenco').style.display = 'none';
            el('contrib-form').style.display = '';
            el('contrib-form-titolo').textContent = c.id ? 'Modifica contributo' : 'Nuovo contributo';
            el('cf-id').value = c.id || '';
            el('cf-luogo').value = c.luogo || '';
            el('cf-nazione').value = c.iso_nazione ? nomeDaIso(c.iso_nazione) : '';
            el('cf-anno').value = c.anno_viaggio || new Date().getFullYear();
            el('cf-mese').value = c.mese_viaggio ? String(c.mese_viaggio) : '';
            el('cf-valuta').value = c.valuta || 'EUR';
            Object.keys(CAMPI_FORM).forEach(function (id) {
                var v = c[CAMPI_FORM[id]];
                el(id).value = (v === null || v === undefined) ? '' : v;
            });
            svuota(el('cf-tratte'));
            (c.tratte || []).forEach(function (t) { aggiungiTratta(t); });
            el('cf-dichiarazione').checked = !!c.id;
            messaggio('cf-msg', '');
            el('cf-luogo').focus();
        }).catch(function () { messaggio('contrib-msg', 'Errore di caricamento. Riprova.', true); });
    }

    function chiudiForm(messaggioFinale) {
        el('contrib-form').style.display = 'none';
        el('contrib-vista-elenco').style.display = '';
        elencoContributi(typeof messaggioFinale === 'string' ? messaggioFinale : '');
    }

    function modificaContributo(id) {
        apiGet('contributo', { id: id }).then(function (d) {
            if (d.errore) { window.alert(d.errore); return; }
            apriForm(d.contributo);
        }).catch(function () { window.alert('Errore di caricamento. Riprova.'); });
    }

    function eliminaContributo(id, luogo) {
        if (!window.confirm('Eliminare definitivamente il tuo contributo su ' + luogo + '?')) { return; }
        apiPost('contributo_elimina', { id: id }).then(function (d) {
            if (!d.ok) { messaggio('contrib-msg', d.errore || 'Operazione non riuscita.', true); return; }
            elencoContributi();
        }).catch(function () { messaggio('contrib-msg', 'Errore di connessione. Riprova.', true); });
    }

    function salvaContributo(ev) {
        ev.preventDefault();
        var iso = isoDaNome(el('cf-nazione').value);
        if (!el('cf-luogo').value.trim()) { messaggio('cf-msg', 'Indica la localit\u00e0.', true); return; }
        if (!iso) { messaggio('cf-msg', 'Scegli la nazione dall\'elenco dei nomi proposti.', true); return; }
        if (!el('cf-dichiarazione').checked) {
            messaggio('cf-msg', 'Conferma di non inserire dati personali di privati nei contatti.', true); return;
        }
        var dati = {
            luogo: el('cf-luogo').value,
            iso_nazione: iso,
            anno_viaggio: parseInt(el('cf-anno').value, 10) || 0,
            mese_viaggio: el('cf-mese').value ? parseInt(el('cf-mese').value, 10) : null,
            valuta: el('cf-valuta').value,
            dichiarazione_contatti: true,
            tratte: []
        };
        if (el('cf-id').value) { dati.id = parseInt(el('cf-id').value, 10); }
        Object.keys(CAMPI_FORM).forEach(function (id) {
            var v = el(id).value;
            dati[CAMPI_FORM[id]] = v === '' ? null : v;
        });
        Array.prototype.forEach.call(el('cf-tratte').children, function (r) {
            function v(cls) { var x = r.querySelector('.' + cls).value; return x === '' ? null : x; }
            dati.tratte.push({ mezzo: v('tr-mezzo'), da_luogo: v('tr-da'), a_luogo: v('tr-a'),
                compagnia: v('tr-compagnia'), durata_indicativa: v('tr-durata'),
                costo_indicativo: v('tr-costo'), valuta: v('tr-valuta') });
        });
        el('cf-salva').disabled = true;
        messaggio('cf-msg', 'Salvataggio...');
        apiPost('contributo_salva', dati).then(function (d) {
            el('cf-salva').disabled = false;
            if (!d.ok) { messaggio('cf-msg', d.errore || 'Salvataggio non riuscito.', true); return; }
            chiudiForm('Contributo salvato. Grazie!');
        }).catch(function () {
            el('cf-salva').disabled = false;
            messaggio('cf-msg', 'Errore di connessione. Riprova.', true);
        });
    }


    // ── Viaggi privati ──────────────────────────────────────────────

    var CAMPI_VIAGGIO = {
        'vf-luogo': 'luogo', 'vf-arrivo': 'data_arrivo', 'vf-partenza': 'data_partenza',
        'vf-albergo': 'albergo', 'vf-costo-alloggio': 'costo_alloggio',
        'vf-costo-trasporti': 'costo_trasporti', 'vf-valuta': 'valuta',
        'vf-trasporti': 'trasporti', 'vf-note': 'note_private',
        // Dati della RSM nel viaggio (D21, D23): coordinate dalla ricerca luoghi, anno e,
        // solo per l'astrologo, soggetto della RSM.
        'vf-lat': 'latitudine', 'vf-lon': 'longitudine', 'vf-anno-rsm': 'anno_rsm',
        'vf-soggetto-rsm': 'soggetto_rsm_id'
    };

    // ── Ricerca luoghi nel form del viaggio (OpenStreetMap) ────────────
    var timerLuogo = null;
    var soggettiRsmCaricati = false;

    function aggiornaCoordinate() {
        var box = el('vf-coordinate');
        if (!box) { return; }
        var lat = el('vf-lat').value, lon = el('vf-lon').value;
        if (lat && lon) {
            box.textContent = '\ud83d\udccd ' + lat + ', ' + lon + ' \u2014 luogo scelto dall\'elenco';
            box.className = 'diario-coordinate ok';
        } else {
            box.textContent = 'Per il grafico della RSM scegli il luogo dall\'elenco che compare mentre scrivi.';
            box.className = 'diario-coordinate';
        }
    }

    function chiudiRisultatiLuogo() {
        var div = el('vf-luogo-risultati');
        if (div) { div.classList.remove('visible'); svuota(div); }
    }

    function selezionaLuogoViaggio(r) {
        // Il nome viene dalla prima parte di display_name, che Nominatim restituisce nella
        // lingua richiesta (accept-language=it,en: italiano, altrimenti inglese, per i luoghi
        // che in OpenStreetMap non hanno il nome italiano): r.name e' invece il nome locale
        // (es. 仙台市 invece di Sendai).
        var nome = String(r.display_name || '').split(',')[0].trim() || r.name || '';
        el('vf-luogo').value = nome.trim();
        el('vf-lat').value = Number(r.lat).toFixed(4);
        el('vf-lon').value = Number(r.lon).toFixed(4);
        var iso = String((r.address && r.address.country_code) || '').toUpperCase();
        if (iso && nomeDaIso(iso)) { el('vf-nazione').value = nomeDaIso(iso); }
        chiudiRisultatiLuogo();
        aggiornaCoordinate();
    }

    function cercaLuogoViaggio() {
        var q = el('vf-luogo').value.trim();
        var div = el('vf-luogo-risultati');
        if (q.length < 3) { chiudiRisultatiLuogo(); return; }
        fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(q) +
              '&format=json&limit=8&addressdetails=1&accept-language=it,en')
            .then(function (r) { return r.json(); })
            .then(function (risultati) {
                if (el('vf-luogo').value.trim() !== q) { return; } // risposta superata
                svuota(div);
                (risultati || []).forEach(function (r) {
                    var voce = nuovo('div', 'dropdown-item', r.display_name);
                    voce.addEventListener('click', function () { selezionaLuogoViaggio(r); });
                    div.appendChild(voce);
                });
                if (!div.firstChild) { div.appendChild(nuovo('div', 'dropdown-item', 'Nessun risultato')); }
                div.classList.add('visible');
            })
            .catch(function () { chiudiRisultatiLuogo(); });
    }

    function caricaSoggettiRsm() {
        var sel = el('vf-soggetto-rsm');
        if (!sel || soggettiRsmCaricati) { return Promise.resolve(); }
        return fetch('api/soggetti_api.php?action=lista', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (lista) {
                (Array.isArray(lista) ? lista : []).forEach(function (s) {
                    if (s.accesso_gestibile === false) { return; } // solo i propri soggetti
                    var o = document.createElement('option');
                    o.value = String(s.id);
                    o.textContent = (s.codice ? s.codice + ' \u2014 ' : '') + s.nome;
                    sel.appendChild(o);
                });
                soggettiRsmCaricati = true;
            });
    }

    function dataIt(iso) {
        if (!iso) { return ''; }
        var p = String(iso).split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : String(iso);
    }

    function cartaViaggio(v) {
        var carta = nuovo('div', 'diario-mio');
        if (v.soggetto_codice && !v.modificabile) {
            carta.appendChild(nuovo('div', 'diario-viaggio-soggetto', v.soggetto_codice + ' \u00b7 ' + (v.soggetto_nome || '')));
        }
        carta.appendChild(nuovo('div', 'diario-mio-titolo', v.luogo + ' \u2014 ' + v.nazione));
        var date = [dataIt(v.data_arrivo), dataIt(v.data_partenza)].filter(function (x) { return x; });
        if (date.length) { carta.appendChild(nuovo('div', 'diario-mio-info', date.join(' \u2192 '))); }
        riga(carta, 'Albergo', v.albergo);
        if (v.costo_alloggio !== null && v.costo_alloggio !== undefined) { riga(carta, 'Costo alloggio', importo(v.costo_alloggio, v.valuta)); }
        if (v.costo_trasporti !== null && v.costo_trasporti !== undefined) { riga(carta, 'Costo trasporti', importo(v.costo_trasporti, v.valuta)); }
        riga(carta, 'Trasporti', v.trasporti, true);
        riga(carta, 'Note', v.note_private, true);
        if (stato.ruolo === 'astrologo' && v.sessione_anno) {
            carta.appendChild(nuovo('span', 'diario-sessione',
                'Sessione RS ' + v.sessione_anno + (v.sessione_luogo ? ' \u00b7 ' + v.sessione_luogo : '')));
        }
        var az = nuovo('div', 'diario-carta-azioni');
        if (v.ha_grafico) {
            var bGraf = nuovo('button', 'diario-btn piccolo', '\ud83d\udcc8 Vedi la RSM');
            bGraf.type = 'button';
            bGraf.addEventListener('click', function () { apriGraficoRsm(v); });
            az.appendChild(bGraf);
        }
        if (v.modificabile) {
            var bMod = nuovo('button', 'diario-btn secondario piccolo', 'Modifica');
            bMod.type = 'button';
            bMod.addEventListener('click', function () { apriFormViaggio(v); });
            var bDel = nuovo('button', 'diario-btn pericolo piccolo', 'Elimina');
            bDel.type = 'button';
            bDel.addEventListener('click', function () { eliminaViaggio(v.id, v.luogo); });
            az.appendChild(bMod); az.appendChild(bDel);
        }
        if (stato.ruolo === 'astrologo') {
            var bCol = nuovo('button', 'diario-btn secondario piccolo',
                v.sessione_rs_id ? 'Cambia sessione RS' : 'Collega a una sessione RS');
            bCol.type = 'button';
            bCol.addEventListener('click', function () { mostraCollega(v, carta, bCol); });
            az.appendChild(bCol);
        }
        carta.appendChild(az);
        if (!v.ha_grafico && v.modificabile) {
            carta.appendChild(nuovo('div', 'diario-nota-grafico',
                'Per vedere il grafico della RSM: Modifica, scegli il luogo dall\'elenco e indica l\'anno della RSM' +
                (stato.ruolo === 'astrologo' ? ' e il soggetto della RSM.' : '.')));
        }
        return carta;
    }

    // ── Grafico della RSM del viaggio (D21-D22) ───────────────────
    // Cielo natale e RS affiancati, calcolati dall'API su richiesta, disegnati con la
    // stessa ruota di rs.php.

    function chiudiGraficoRsm() {
        el('rsm-finestra').classList.remove('aperta');
        document.body.style.overflow = '';
    }

    function apriGraficoRsm(v) {
        var finestra = el('rsm-finestra');
        el('rsm-titolo').textContent = 'RSM ' + (v.anno_rsm || '') + ' \u2014 ' + v.luogo + ', ' + v.nazione;
        el('rsm-sotto').textContent = '';
        el('rsm-ruote').style.display = 'none';
        messaggio('rsm-msg', 'Calcolo in corso...');
        finestra.classList.add('aperta');
        document.body.style.overflow = 'hidden';
        apiGet('grafico_viaggio', { id: v.id }).then(function (d) {
            if (d.errore) { messaggio('rsm-msg', d.errore, true); return; }
            messaggio('rsm-msg', '');
            el('rsm-titolo').textContent = 'RSM ' + d.anno + ' \u2014 ' + d.luogo + ', ' + d.nazione;
            el('rsm-sotto').textContent = 'Rivoluzione solare: ' + d.rs_gmt;
            el('rsm-titolo-rs').textContent = '\u2609 RS ' + d.anno + ' \u2014 ' + d.luogo;
            el('rsm-ruote').style.display = '';
            ZodiacWheel.disegna('wheel-natale-viaggio', d.natale, { size: 480 });
            ZodiacWheel.disegna('wheel-rs-viaggio', d.rs, { size: 480 });
        }).catch(function () { messaggio('rsm-msg', 'Errore di calcolo. Riprova.', true); });
    }

    // ── Elenco compatto dei viaggi con ricerca (D20) ────────────────
    // Una riga per viaggio (anno, luogo, nazione); un clic apre il riquadro completo.

    var elenchiViaggi = { miei: [], soggetti: [] };

    function annoViaggio(v) {
        return v.data_arrivo ? String(v.data_arrivo).slice(0, 4) : '';
    }

    function normalizza(testo) {
        return String(testo || '').toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            // Lettere che non si scompongono con NFD (es. Troms\u00f8 si trova scrivendo "tromso").
            .replace(/\u00f8/g, 'o').replace(/\u00e6/g, 'ae').replace(/\u0153/g, 'oe')
            .replace(/\u00df/g, 'ss').replace(/\u0142/g, 'l').replace(/\u0111/g, 'd')
            .replace(/\s+/g, ' ').trim();
    }

    function corrisponde(v, filtro) {
        if (!filtro) { return true; }
        // "RSM2011" o "rsm 2011" cercano l'anno 2011.
        var f = normalizza(filtro).replace(/^rsm\s*/, '');
        if (!f) { return true; }
        var testo = normalizza([annoViaggio(v), v.luogo, v.nazione, v.soggetto_codice, v.soggetto_nome].join(' '));
        return f.split(' ').every(function (parola) { return testo.indexOf(parola) !== -1; });
    }

    function disegnaElencoViaggi(chiave, idBox, idMsg, idFiltro, msgVuoto) {
        var box = el(idBox);
        if (!box) { return; }
        var filtroEl = el(idFiltro);
        var filtro = filtroEl ? filtroEl.value : '';
        var tutti = elenchiViaggi[chiave];
        var visibili = tutti.filter(function (v) { return corrisponde(v, filtro); });
        svuota(box);
        visibili.forEach(function (v) {
            var riga = nuovo('button', 'diario-riga-viaggio');
            riga.type = 'button';
            riga.setAttribute('aria-expanded', 'false');
            riga.appendChild(nuovo('span', 'anno', annoViaggio(v) || '\u2014'));
            riga.appendChild(nuovo('span', 'luogo', v.luogo + ' \u2014 ' + v.nazione));
            if (chiave === 'soggetti' && v.soggetto_codice) {
                riga.appendChild(nuovo('span', 'chi', v.soggetto_codice));
            }
            riga.appendChild(nuovo('span', 'freccia', '\u25b8'));
            var dettaglio = nuovo('div', 'diario-dettaglio-viaggio');
            dettaglio.style.display = 'none';
            riga.addEventListener('click', function () {
                var aperto = riga.getAttribute('aria-expanded') === 'true';
                if (!aperto && !dettaglio.firstChild) { dettaglio.appendChild(cartaViaggio(v)); }
                dettaglio.style.display = aperto ? 'none' : '';
                riga.setAttribute('aria-expanded', aperto ? 'false' : 'true');
                riga.lastChild.textContent = aperto ? '\u25b8' : '\u25be';
            });
            box.appendChild(riga);
            box.appendChild(dettaglio);
        });
        var conteggio = nuovo('div', 'diario-conteggio',
            filtro.trim() ? visibili.length + ' di ' + tutti.length + ' viaggi' : tutti.length + (tutti.length === 1 ? ' viaggio' : ' viaggi'));
        if (tutti.length) { box.insertBefore(conteggio, box.firstChild); }
        if (!tutti.length) { messaggio(idMsg, msgVuoto); }
        else if (!visibili.length) { messaggio(idMsg, 'Nessun viaggio corrisponde alla ricerca.'); }
    }

    function elencoViaggi(messaggioFinale) {
        messaggio('viaggi-msg', 'Caricamento...');
        apiGet('miei_viaggi').then(function (d) {
            var viaggi = d.viaggi || [];
            elenchiViaggi.miei = viaggi.filter(function (v) { return v.modificabile; });
            elenchiViaggi.soggetti = viaggi.filter(function (v) { return !v.modificabile; });
            messaggio('viaggi-msg', messaggioFinale || '');
            disegnaElencoViaggi('miei', 'viaggi-elenco', 'viaggi-msg', 'viaggi-filtro', 'Non hai ancora registrato viaggi.');
            if (el('viaggi-soggetti-elenco')) {
                messaggio('viaggi-soggetti-msg', '');
                disegnaElencoViaggi('soggetti', 'viaggi-soggetti-elenco', 'viaggi-soggetti-msg', 'viaggi-soggetti-filtro',
                    'I tuoi soggetti non hanno ancora registrato viaggi.');
            }
        }).catch(function () { messaggio('viaggi-msg', 'Errore di caricamento. Riprova.', true); });
    }

    function mostraCollega(v, carta, pulsante) {
        var esistente = carta.querySelector('.diario-collega');
        if (esistente) { carta.removeChild(esistente); return; }
        var box = nuovo('div', 'diario-collega');
        var sel = document.createElement('select');
        var vuota = document.createElement('option');
        vuota.value = ''; vuota.textContent = 'Caricamento sessioni...';
        sel.appendChild(vuota);
        box.appendChild(sel);
        var bOk = nuovo('button', 'diario-btn piccolo', 'Collega');
        bOk.type = 'button';
        var bNo = nuovo('button', 'diario-btn pericolo piccolo', 'Scollega');
        bNo.type = 'button';
        box.appendChild(bOk);
        if (v.sessione_rs_id) { box.appendChild(bNo); }
        carta.appendChild(box);
        var parametri = v.soggetto_id ? { soggetto_id: v.soggetto_id } : {};
        apiGet('sessioni_rs', parametri).then(function (d) {
            svuota(sel);
            var sessioni = d.sessioni || [];
            if (!sessioni.length) {
                var o = document.createElement('option');
                o.value = ''; o.textContent = 'Nessuna sessione RS salvata';
                sel.appendChild(o);
                bOk.disabled = true;
                return;
            }
            sessioni.forEach(function (x) {
                var o = document.createElement('option');
                o.value = String(x.id);
                o.textContent = x.anno + ' \u00b7 ' + (x.luogo_rs || '?') +
                    (v.soggetto_id ? '' : ' \u00b7 ' + x.soggetto_codice) +
                    (x.condizione ? ' \u00b7 ' + x.condizione : '');
                if (String(v.sessione_rs_id) === o.value) { o.selected = true; }
                sel.appendChild(o);
            });
        }).catch(function () { vuota.textContent = 'Errore di caricamento'; });
        function invia(idSessione) {
            apiPost('viaggio_collega_sessione', { id: v.id, sessione_rs_id: idSessione }).then(function (d) {
                if (!d.ok) { window.alert(d.errore || 'Operazione non riuscita.'); return; }
                elencoViaggi(idSessione ? 'Sessione RS collegata.' : 'Sessione RS scollegata.');
            }).catch(function () { window.alert('Errore di connessione. Riprova.'); });
        }
        bOk.addEventListener('click', function () { if (sel.value) { invia(parseInt(sel.value, 10)); } });
        bNo.addEventListener('click', function () { invia(null); });
    }

    function apriFormViaggio(v) {
        v = v || {};
        Promise.all([caricaNazioni(), caricaSoggettiRsm()]).then(function () {
            el('viaggi-vista-elenco').style.display = 'none';
            el('viaggio-form').style.display = '';
            el('viaggio-form-titolo').textContent = v.id ? 'Modifica viaggio' : 'Nuovo viaggio';
            el('vf-id').value = v.id || '';
            Object.keys(CAMPI_VIAGGIO).forEach(function (id) {
                if (!el(id)) { return; }
                var x = v[CAMPI_VIAGGIO[id]];
                el(id).value = (x === null || x === undefined) ? '' : x;
            });
            ['vf-lat', 'vf-lon'].forEach(function (id) {
                if (el(id).value !== '') { el(id).value = Number(el(id).value).toFixed(4); }
            });
            chiudiRisultatiLuogo();
            aggiornaCoordinate();
            if (!el('vf-valuta').value) { el('vf-valuta').value = 'EUR'; }
            el('vf-nazione').value = v.iso_nazione ? nomeDaIso(v.iso_nazione) : '';
            messaggio('vf-msg', '');
            el('vf-luogo').focus();
        }).catch(function () { messaggio('viaggi-msg', 'Errore di caricamento. Riprova.', true); });
    }

    function chiudiFormViaggio(messaggioFinale) {
        el('viaggio-form').style.display = 'none';
        el('viaggi-vista-elenco').style.display = '';
        elencoViaggi(typeof messaggioFinale === 'string' ? messaggioFinale : '');
    }

    function eliminaViaggio(id, luogo) {
        if (!window.confirm('Eliminare definitivamente il viaggio a ' + luogo + '?')) { return; }
        apiPost('viaggio_elimina', { id: id }).then(function (d) {
            if (!d.ok) { messaggio('viaggi-msg', d.errore || 'Operazione non riuscita.', true); return; }
            elencoViaggi('Viaggio eliminato.');
        }).catch(function () { messaggio('viaggi-msg', 'Errore di connessione. Riprova.', true); });
    }

    function salvaViaggio(ev) {
        ev.preventDefault();
        var iso = isoDaNome(el('vf-nazione').value);
        if (!el('vf-luogo').value.trim()) { messaggio('vf-msg', 'Indica il luogo del viaggio.', true); return; }
        if (!iso) { messaggio('vf-msg', 'Scegli la nazione dall\'elenco dei nomi proposti.', true); return; }
        if (el('vf-arrivo').value && el('vf-partenza').value && el('vf-partenza').value < el('vf-arrivo').value) {
            messaggio('vf-msg', 'La data di partenza non pu\u00f2 precedere quella di arrivo.', true); return;
        }
        var dati = { iso_nazione: iso };
        if (el('vf-id').value) { dati.id = parseInt(el('vf-id').value, 10); }
        Object.keys(CAMPI_VIAGGIO).forEach(function (id) {
            if (!el(id)) { return; }
            var x = el(id).value;
            dati[CAMPI_VIAGGIO[id]] = x === '' ? null : x;
        });
        el('vf-salva').disabled = true;
        messaggio('vf-msg', 'Salvataggio...');
        apiPost('viaggio_salva', dati).then(function (d) {
            el('vf-salva').disabled = false;
            if (!d.ok) { messaggio('vf-msg', d.errore || 'Salvataggio non riuscito.', true); return; }
            chiudiFormViaggio('Viaggio salvato.');
        }).catch(function () {
            el('vf-salva').disabled = false;
            messaggio('vf-msg', 'Errore di connessione. Riprova.', true);
        });
    }

    // ── Avvio ────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        if (stato.avviato) { return; }   // protezione da doppia inizializzazione
        stato.avviato = true;
        document.querySelectorAll('.diario-scheda-btn').forEach(function (b) {
            b.addEventListener('click', function () { mostraScheda(b.getAttribute('data-scheda')); });
        });
        el('scheda-indietro').addEventListener('click', chiudiScheda);
        el('scheda-scrivi').addEventListener('click', function () {
            var sc = stato.schedaCorrente || {};
            apriForm({ luogo: sc.luogo, iso_nazione: sc.iso });
        });
        var meseSel = el('cf-mese');
        for (var m = 1; m <= 12; m++) {
            var o = document.createElement('option');
            o.value = String(m); o.textContent = MESI[m];
            meseSel.appendChild(o);
        }
        el('contrib-nuovo').addEventListener('click', function () { apriForm({}); });
        el('cf-annulla').addEventListener('click', chiudiForm);
        el('cf-tratta-aggiungi').addEventListener('click', function () { aggiungiTratta({}); });
        el('contrib-form').addEventListener('submit', salvaContributo);
        el('viaggi-nuovo').addEventListener('click', function () { apriFormViaggio({}); });
        el('rsm-chiudi').addEventListener('click', chiudiGraficoRsm);
        el('rsm-finestra').addEventListener('click', function (ev) {
            if (ev.target === el('rsm-finestra')) { chiudiGraficoRsm(); }
        });
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && el('rsm-finestra').classList.contains('aperta')) { chiudiGraficoRsm(); }
        });
        el('vf-luogo').addEventListener('input', function () {
            // Il luogo e' cambiato: le coordinate precedenti non valgono piu'.
            el('vf-lat').value = '';
            el('vf-lon').value = '';
            aggiornaCoordinate();
            clearTimeout(timerLuogo);
            timerLuogo = setTimeout(cercaLuogoViaggio, 450);
        });
        el('vf-arrivo').addEventListener('change', function () {
            if (!el('vf-anno-rsm').value && el('vf-arrivo').value) {
                el('vf-anno-rsm').value = el('vf-arrivo').value.slice(0, 4);
            }
        });
        document.addEventListener('click', function (ev) {
            if (!ev.target.closest || !ev.target.closest('.diario-campo-luogo')) { chiudiRisultatiLuogo(); }
        });
        el('viaggi-filtro').addEventListener('input', function () {
            messaggio('viaggi-msg', '');
            disegnaElencoViaggi('miei', 'viaggi-elenco', 'viaggi-msg', 'viaggi-filtro', 'Non hai ancora registrato viaggi.');
        });
        if (el('viaggi-soggetti-filtro')) {
            el('viaggi-soggetti-filtro').addEventListener('input', function () {
                messaggio('viaggi-soggetti-msg', '');
                disegnaElencoViaggi('soggetti', 'viaggi-soggetti-elenco', 'viaggi-soggetti-msg', 'viaggi-soggetti-filtro',
                    'I tuoi soggetti non hanno ancora registrato viaggi.');
            });
        }
        el('vf-annulla').addEventListener('click', chiudiFormViaggio);
        el('viaggio-form').addEventListener('submit', salvaViaggio);
        document.querySelector('[data-scheda="viaggi"]').addEventListener('click', function () {
            if (el('viaggio-form').style.display === 'none') { elencoViaggi(); }
        });
        document.querySelector('[data-scheda="contributi"]').addEventListener('click', function () {
            if (el('contrib-form').style.display === 'none') { elencoContributi(); }
        });
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
