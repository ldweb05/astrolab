/**
 * app.js — Logica frontend principale
 * Versione con gestione autenticazione e soggetto attivo
 */

// ── API KEY PER TIMEZONEDB ────────────────────────────────────────────────
const TIMEZONE_API_KEY = 'DCFYAQJXAI08';

// ── STATO SESSIONE ────────────────────────────────────────────────────────
let sessioneUtente = {
    utente_id:     null,
    username:      '',
    ruolo:         '',
    soggetto_id:   null,
    soggetto_nome: '',
};

/**
 * Carica stato sessione dal server.
 * Chiamare all'avvio di ogni pagina per avere i dati aggiornati.
 */
async function caricaStatoSessione() {
    try {
        const r    = await fetch('api/session_api.php?action=stato');
        const data = await r.json();
        if (data.ok) {
            sessioneUtente = data;
            aggiornaHeaderSoggetto();
        }
    } catch (e) {
        console.warn('Impossibile caricare stato sessione:', e);
    }
}

/**
 * Aggiorna eventuali elementi nell'header con soggetto attivo.
 * (Se l'header viene renderizzato server-side non è necessario,
 * ma serve per aggiornamento live dopo cambio soggetto via JS.)
 */
function aggiornaHeaderSoggetto() {
    const el = document.getElementById('header-soggetto-nome');
    if (el) {
        el.textContent = sessioneUtente.soggetto_nome
            ? '⭐ ' + sessioneUtente.soggetto_nome
            : '';
    }
}

// ── SOGGETTI ──────────────────────────────────────────────────────────────

function caricaSoggetti() {
    fetch('api/soggetti_api.php?action=lista')
        .then(r => r.json())
        .then(data => {
            const div = document.getElementById('lista-soggetti');
            if (!data.length) {
                div.innerHTML = '<p class="empty">Nessun soggetto inserito. Clicca "+ Nuovo Soggetto" per iniziare.</p>';
                return;
            }
            let html = `<table class="tabella-soggetti">
                <thead><tr>
                    <th>Codice</th><th>Nome</th><th>Data Nascita</th>
                    <th>Ora</th><th>Luogo</th><th>Azioni</th>
                </tr></thead><tbody>`;
            data.forEach(s => {
                const residenzaHtml = s.residenza_luogo
                    ? `<div style="font-size:11px;color:#5A7AB0;margin-top:4px">🏠 ${s.residenza_luogo}${s.residenza_nazione ? ', ' + s.residenza_nazione : ''}</div>`
                    : '';

                html += `<tr>
                    <td>${s.codice || '—'}</td>
                    <td><b>${s.nome}</b></td>
                    <td>${formatData(s.data_nascita)}</td>
                    <td>${s.ora_nascita}</td>
                    <td>
                        <div>${s.luogo_nascita || ''} ${s.nazione_nascita || ''}</div>
                        ${residenzaHtml}
                    </td>
                    <td><div class="azioni">
                        <button class="btn-icon" title="Tema Natale" onclick="apriTema(${s.id})">☉</button>
                        <button class="btn-icon" title="Rivoluzione Solare" onclick="apriRS(${s.id})">↺</button>
                        <button class="btn-icon" title="Modifica" onclick="modificaSoggetto(${s.id})">✏️</button>
                        <button class="btn-icon" title="Elimina" onclick="eliminaSoggetto(${s.id}, '${s.nome.replace(/'/g, "\\'")}')">🗑️</button>
                    </div></td>
                </tr>`;
            });
            html += '</tbody></table>';
            div.innerHTML = html;
        })
        .catch(e => {
            document.getElementById('lista-soggetti').innerHTML =
                '<p class="msg-error">Errore caricamento: ' + e.message + '</p>';
        });
}

function mostraForm(dati = null) {
    document.getElementById('form-soggetto').style.display = 'block';
    document.getElementById('form-titolo').textContent =
        dati ? 'Modifica Soggetto' : 'Nuovo Soggetto';

    const campi = [
        'soggetto-id', 'nome', 'codice', 'ora-nascita', 'ora-gmt',
        'offset-gmt', 'latitudine', 'longitudine', 'nazione', 'timezone', 'note',
        'residenza-search', 'residenza-latitudine', 'residenza-longitudine', 'residenza-nazione'
    ];
    campi.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('luogo-search').value = '';
    document.getElementById('data-nascita').value = '';

    if (dati) {
        document.getElementById('soggetto-id').value  = dati.id;
        document.getElementById('nome').value         = dati.nome;
        document.getElementById('codice').value       = dati.codice || '';
        document.getElementById('data-nascita').value = dati.data_nascita?.substring(0,10);
        document.getElementById('ora-nascita').value  = dati.ora_nascita?.substring(0,5);
        document.getElementById('ora-gmt').value      = dati.ora_nascita_gmt?.substring(0,5);
        document.getElementById('offset-gmt').value   = dati.offset_gmt;
        document.getElementById('latitudine').value   = dati.latitudine;
        document.getElementById('longitudine').value  = dati.longitudine;
        document.getElementById('nazione').value      = dati.nazione_nascita || '';
        document.getElementById('timezone').value     = dati.timezone || '';
        document.getElementById('luogo-search').value = dati.luogo_nascita || '';
        document.getElementById('note').value         = dati.note || '';

        document.getElementById('residenza-search').value = dati.residenza_luogo
            ? dati.residenza_luogo + (dati.residenza_nazione ? ', ' + dati.residenza_nazione : '')
            : '';
        document.getElementById('residenza-latitudine').value  = dati.residenza_latitudine || '';
        document.getElementById('residenza-longitudine').value = dati.residenza_longitudine || '';
        document.getElementById('residenza-nazione').value     = dati.residenza_nazione || '';
    }

    document.getElementById('form-soggetto').scrollIntoView({behavior:'smooth'});
}

function nascondiForm() {
    document.getElementById('form-soggetto').style.display = 'none';
}

function salvaSoggetto() {
    const id       = document.getElementById('soggetto-id').value;
    const nome     = document.getElementById('nome').value.trim();
    const dataN    = document.getElementById('data-nascita').value;
    const oraN     = document.getElementById('ora-nascita').value;
    const oraGmt   = document.getElementById('ora-gmt').value;
    const offset   = document.getElementById('offset-gmt').value;
    const lat      = document.getElementById('latitudine').value;
    const lon      = document.getElementById('longitudine').value;
    const luogo    = document.getElementById('luogo-search').value;
    const nazione  = document.getElementById('nazione').value;
    const timezone = document.getElementById('timezone').value;
    const codice   = document.getElementById('codice').value.trim();
    const note     = document.getElementById('note').value.trim();

    const resLuogo = document.getElementById('residenza-search').value.trim()
                        .split(',')[0].trim();
    const resLat   = document.getElementById('residenza-latitudine').value;
    const resLon   = document.getElementById('residenza-longitudine').value;
    const resNaz   = document.getElementById('residenza-nazione').value;

    if (!nome || !dataN || !oraN) {
        alert('Compila almeno Nome, Data e Ora di nascita.');
        return;
    }

    const payload = {
        action: id ? 'modifica' : 'inserisci',
        id, nome, codice, data_nascita: dataN,
        ora_nascita: oraN, ora_nascita_gmt: oraGmt || oraN,
        offset_gmt: parseFloat(offset) || 0,
        latitudine: parseFloat(lat) || 0,
        longitudine: parseFloat(lon) || 0,
        luogo_nascita: luogo, nazione_nascita: nazione,
        timezone, note,
        residenza_luogo: resLuogo || null,
        residenza_latitudine: resLat ? parseFloat(resLat) : null,
        residenza_longitudine: resLon ? parseFloat(resLon) : null,
        residenza_nazione: resNaz || null
    };

    fetch('api/soggetti_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            nascondiForm();
            caricaSoggetti();
            mostraMessaggio('Soggetto salvato con successo.', 'success');
        } else {
            mostraMessaggio('Errore: ' + (data.errore || 'sconosciuto'), 'error');
        }
    });
}

function modificaSoggetto(id) {
    fetch('api/soggetti_api.php?action=get&id=' + id)
        .then(r => r.json())
        .then(dati => mostraForm(dati));
}

function eliminaSoggetto(id, nome) {
    if (!confirm('Eliminare ' + nome + '?')) return;
    fetch('api/soggetti_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'elimina', id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) { caricaSoggetti(); mostraMessaggio('Soggetto eliminato.', 'success'); }
    });
}

function apriTema(id) {
    window.location.href = 'tema.php?id=' + id;
}

function apriRS(id) {
    window.location.href = 'rs.php?id=' + id;
}

// ── GEOCODING OSM + TIMEZONE ──────────────────────────────────────────────

let geocodeTimer = null;

function cercaLuogo() {
    const q = document.getElementById('luogo-search').value.trim();
    if (q.length < 3) return;

    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=8&addressdetails=1`)
        .then(r => r.json())
        .then(risultati => {
            const div = document.getElementById('luogo-risultati');
            if (!risultati.length) {
                div.innerHTML = '<div class="dropdown-item">Nessun risultato</div>';
                div.classList.add('visible');
                return;
            }
            div.innerHTML = risultati.map(r => `
                <div class="dropdown-item" onclick="selezionaLuogo(
                    ${r.lat}, ${r.lon},
                    '${r.display_name.replace(/'/g,"\\'")}',
                    '${(r.address?.country_code || '').toUpperCase()}'
                )">${r.display_name}</div>
            `).join('');
            div.classList.add('visible');
        });
}

/**
 * Ottieni offset GMT storico da TimeZoneDB.
 *
 * PROBLEMA RISOLTO: passare solo la data a mezzogiorno UTC era sbagliato
 * per date vicine ai cambi ora legale (es. ultima domenica di marzo/ottobre).
 * Es: nascita ore 00:30 il 26/10 (cambio ora) → offset +2 (CEST),
 * ma a mezzogiorno UTC = 12:00 UTC = 13:00 CET → TimeZoneDB risponde +1 (CET). ERRORE.
 *
 * FIX: usiamo data + ora locale come base per il timestamp.
 * Poiché non conosciamo ancora l'offset (è quello che stiamo cercando),
 * usiamo l'ora locale "as if UTC" (±1h di errore max) → abbastanza preciso
 * per determinare se siamo in ora legale o solare, che cambiano di 1h.
 * In caso estremo (nascita esattamente nell'ora ambigua), l'utente può
 * correggere manualmente con il pulsante 🔄 e modificando l'offset.
 *
 * @param {number} lat
 * @param {number} lon
 * @param {string} dataNascita  - formato YYYY-MM-DD
 * @param {string} [oraLocale]  - formato HH:MM (opzionale, default "12:00")
 */
async function ottieniOffsetTimeZone(lat, lon, dataNascita, oraLocale) {
    try {
        let timestamp = '';
        if (dataNascita) {
            // Usa l'ora locale se disponibile, altrimenti mezzogiorno
            // Trattiamo l'ora locale come "approssimazione UTC" (errore max ±14h,
            // ma per il DST è sufficiente: cambia di 1h e non a mezzanotte)
            const oraStr = oraLocale && /^\d{2}:\d{2}$/.test(oraLocale)
                ? oraLocale + ':00'
                : '12:00:00';
            const d = new Date(dataNascita + 'T' + oraStr + 'Z');
            if (!isNaN(d.getTime())) {
                timestamp = '&time=' + Math.floor(d.getTime() / 1000);
            }
        }

        const url = `https://api.timezonedb.com/v2.1/get-time-zone?key=${TIMEZONE_API_KEY}&format=json&by=position&lat=${lat}&lng=${lon}${timestamp}`;
        const response = await fetch(url);
        const data = await response.json();
        if (data.status === 'OK' && data.gmtOffset !== undefined) {
            const offset = data.gmtOffset / 3600;
            console.log(`TimeZoneDB OK: offset=${offset}h, dst=${data.dst}, tz=${data.zoneName}`);
            return offset;
        }
        console.warn('TimeZoneDB risposta non valida:', data);
        throw new Error('TimeZoneDB: ' + (data.message || 'risposta non valida'));
    } catch(e) {
        console.warn('TimeZoneDB fallback (stima da longitudine):', e.message);
        let offset = Math.round(lon / 15 * 2) / 2;
        offset = Math.max(-12, Math.min(12, offset));
        return offset;
    }
}

async function selezionaLuogo(lat, lon, displayName, paese) {
    const parti = displayName.split(',');
    const citta = parti[0].trim();

    document.getElementById('luogo-search').value = citta + (paese ? ', ' + paese : '');
    document.getElementById('latitudine').value   = parseFloat(lat).toFixed(4);
    document.getElementById('longitudine').value  = parseFloat(lon).toFixed(4);
    document.getElementById('nazione').value      = paese;
    document.getElementById('luogo-risultati').classList.remove('visible');

    const offsetField = document.getElementById('offset-gmt');
    offsetField.value = '...';

    // Passa sia la data sia l'ora locale per un timestamp preciso (fix DST)
    const dataNascita = document.getElementById('data-nascita').value;
    const oraLocale   = document.getElementById('ora-nascita').value;
    const offset = await ottieniOffsetTimeZone(lat, lon, dataNascita, oraLocale);
    offsetField.value = offset;
    aggiornaOraGmt();
}

function aggiornaOraGmt() {
    const oraN   = document.getElementById('ora-nascita').value;
    const offset = parseFloat(document.getElementById('offset-gmt').value) || 0;
    if (!oraN) return;

    const [hh, mm] = oraN.split(':').map(Number);
    let gmtMin = hh * 60 + mm - offset * 60;
    gmtMin = ((gmtMin % 1440) + 1440) % 1440;
    const hGmt = Math.floor(gmtMin / 60);
    const mGmt = gmtMin % 60;
    document.getElementById('ora-gmt').value =
        String(hGmt).padStart(2,'0') + ':' + String(mGmt).padStart(2,'0');
}

/**
 * Ricalcola offset GMT usando TimeZoneDB in base a lat/lon, data e ora locali.
 * Chiamato sia dal cambio data/ora che dal pulsante "🔄" manuale.
 */
async function ricalcolaOffset() {
    const lat  = parseFloat(document.getElementById('latitudine').value);
    const lon  = parseFloat(document.getElementById('longitudine').value);
    const data = document.getElementById('data-nascita').value;
    // FIX: passiamo anche l'ora locale per precisione DST
    const oraLocale = document.getElementById('ora-nascita')?.value || '';

    if (isNaN(lat) || isNaN(lon) || !lat || !lon) return; // nessuna coordinata, nulla da fare

    const offsetField  = document.getElementById('offset-gmt');
    const btnRicalcola = document.getElementById('btn-ricalcola-offset');
    const indicatore   = document.getElementById('offset-loading');

    if (offsetField)  offsetField.style.opacity = '0.5';
    if (btnRicalcola) { btnRicalcola.disabled = true; btnRicalcola.textContent = '⟳'; }
    if (indicatore)   indicatore.style.display = 'inline';

    const offset = await ottieniOffsetTimeZone(lat, lon, data, oraLocale);

    if (offsetField)  { offsetField.value = offset; offsetField.style.opacity = '1'; }
    if (btnRicalcola) { btnRicalcola.disabled = false; btnRicalcola.textContent = '🔄'; }
    if (indicatore)   indicatore.style.display = 'none';

    aggiornaOraGmt();
}

// ── RESIDENZA ──────────────────────────────────────────────────────────────

let geocodeResidenzaTimer = null;

function cercaLuogoResidenza() {
    const q = document.getElementById('residenza-search').value.trim();
    if (q.length < 3) return;

    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=8&addressdetails=1`)
        .then(r => r.json())
        .then(risultati => {
            const div = document.getElementById('residenza-risultati');
            if (!div) return;
            if (!risultati.length) {
                div.innerHTML = '<div class="dropdown-item">Nessun risultato</div>';
                div.classList.add('visible');
                return;
            }
            div.innerHTML = risultati.map(r => `
                <div class="dropdown-item" onclick="selezionaResidenza(
                    '${r.display_name.replace(/'/g,"\\'")}',
                    ${r.lat}, ${r.lon},
                    '${(r.address?.country_code || '').toUpperCase()}'
                )">${r.display_name}</div>
            `).join('');
            div.classList.add('visible');
        });
}

function selezionaResidenza(displayName, lat, lon, paese) {
    const parti = displayName.split(',');
    const citta = parti[0].trim();

    document.getElementById('residenza-search').value = citta + (paese ? ', ' + paese : '');
    document.getElementById('residenza-latitudine').value = parseFloat(lat).toFixed(4);
    document.getElementById('residenza-longitudine').value = parseFloat(lon).toFixed(4);
    document.getElementById('residenza-nazione').value = paese;
    document.getElementById('residenza-risultati').classList.remove('visible');
}

function cancellaResidenza() {
    document.getElementById('residenza-search').value = '';
    document.getElementById('residenza-latitudine').value = '';
    document.getElementById('residenza-longitudine').value = '';
    document.getElementById('residenza-nazione').value = '';
}

// ── EVENT LISTENERS ────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('luogo-search');
    if (input) {
        input.addEventListener('input', () => {
            clearTimeout(geocodeTimer);
            geocodeTimer = setTimeout(cercaLuogo, 500);
        });
    }

    const inputRes = document.getElementById('residenza-search');
    if (inputRes) {
        inputRes.addEventListener('input', () => {
            clearTimeout(geocodeResidenzaTimer);
            geocodeResidenzaTimer = setTimeout(cercaLuogoResidenza, 500);
        });
    }

    const oraNascita  = document.getElementById('ora-nascita');
    const offsetGmt   = document.getElementById('offset-gmt');
    const dataNascita = document.getElementById('data-nascita');

    // FIX: quando cambia l'ora locale, ricalcola l'offset se abbiamo le coordinate.
    // Questo copre il caso di nascita nell'ora ambigua del cambio ora legale
    // (es. 01:30 del 26/10 può essere CEST o CET a seconda dell'anno).
    if (oraNascita) {
        oraNascita.addEventListener('change', () => {
            const lat = document.getElementById('latitudine').value;
            const lon = document.getElementById('longitudine').value;
            if (lat && lon && parseFloat(lat) !== 0) {
                // Ricalcola offset con la nuova ora (async, aggiorna GMT dopo)
                ricalcolaOffset();
            } else {
                // Nessuna coordinata: aggiorna solo la conversione ora GMT
                aggiornaOraGmt();
            }
        });
    }
    if (offsetGmt)   offsetGmt.addEventListener('change', aggiornaOraGmt);

    // Quando cambia la data, ricalcola l'offset storico (DST / cambio fuso storico)
    // solo se abbiamo già delle coordinate (luogo già selezionato)
    if (dataNascita) {
        dataNascita.addEventListener('change', () => {
            const lat = document.getElementById('latitudine').value;
            const lon = document.getElementById('longitudine').value;
            if (lat && lon && parseFloat(lat) !== 0) {
                ricalcolaOffset();
            }
        });
    }

    document.addEventListener('click', e => {
        if (!e.target.closest('#luogo-search') &&
            !e.target.closest('#luogo-risultati')) {
            document.getElementById('luogo-risultati')?.classList.remove('visible');
        }
        if (!e.target.closest('#residenza-search') &&
            !e.target.closest('#residenza-risultati')) {
            document.getElementById('residenza-risultati')?.classList.remove('visible');
        }
    });
});

// ── UTILITY ───────────────────────────────────────────────────────────────

function formatData(dataStr) {
    if (!dataStr) return '';
    const d = new Date(dataStr);
    return d.toLocaleDateString('it-IT', {day:'2-digit',month:'2-digit',year:'numeric'});
}

function mostraMessaggio(testo, tipo) {
    const div = document.createElement('div');
    div.className = tipo === 'success' ? 'msg-success' : 'msg-error';
    div.textContent = testo;
    document.querySelector('main').insertBefore(div, document.querySelector('main').firstChild);
    setTimeout(() => div.remove(), 4000);
}

// ── FINE TUNING ORA DI NASCITA ───────────────────────────────────────────

function modificaOra(delta) {
    const inputOra = document.getElementById('ora-nascita');
    if (!inputOra) return;
    let [hh, mm] = inputOra.value.split(':').map(Number);
    if (isNaN(hh)) hh = 12;
    if (isNaN(mm)) mm = 0;
    hh = (hh + delta + 24) % 24;
    inputOra.value = String(hh).padStart(2, '0') + ':' + String(mm).padStart(2, '0');
    aggiornaOraGmt();
    ricalcolaTemaSeNecessario();
}

function modificaMinuti(delta) {
    const inputOra = document.getElementById('ora-nascita');
    if (!inputOra) return;
    let [hh, mm] = inputOra.value.split(':').map(Number);
    if (isNaN(hh)) hh = 12;
    if (isNaN(mm)) mm = 0;
    mm = mm + delta;
    if (mm >= 60) { mm -= 60; hh = (hh + 1) % 24; }
    if (mm < 0)   { mm += 60; hh = (hh - 1 + 24) % 24; }
    inputOra.value = String(hh).padStart(2, '0') + ':' + String(mm).padStart(2, '0');
    aggiornaOraGmt();
    ricalcolaTemaSeNecessario();
}

function modificaOraGMT(delta) {
    const inputGmt = document.getElementById('ora-gmt');
    if (!inputGmt) return;
    let [hh, mm] = inputGmt.value.split(':').map(Number);
    if (isNaN(hh)) hh = 12;
    if (isNaN(mm)) mm = 0;
    hh = (hh + delta + 24) % 24;
    inputGmt.value = String(hh).padStart(2, '0') + ':' + String(mm).padStart(2, '0');
    const offset = parseFloat(document.getElementById('offset-gmt').value) || 0;
    let localMin = hh * 60 + mm + offset * 60;
    localMin = ((localMin % 1440) + 1440) % 1440;
    const hLocal = Math.floor(localMin / 60);
    const mLocal = localMin % 60;
    const inputOra = document.getElementById('ora-nascita');
    if (inputOra) inputOra.value = String(hLocal).padStart(2,'0') + ':' + String(mLocal).padStart(2,'0');
    ricalcolaTemaSeNecessario();
}

function modificaMinutiGMT(delta) {
    const inputGmt = document.getElementById('ora-gmt');
    if (!inputGmt) return;
    let [hh, mm] = inputGmt.value.split(':').map(Number);
    if (isNaN(hh)) hh = 12;
    if (isNaN(mm)) mm = 0;
    mm = mm + delta;
    if (mm >= 60) { mm -= 60; hh = (hh + 1) % 24; }
    if (mm < 0)   { mm += 60; hh = (hh - 1 + 24) % 24; }
    inputGmt.value = String(hh).padStart(2,'0') + ':' + String(mm).padStart(2,'0');
    const offset = parseFloat(document.getElementById('offset-gmt').value) || 0;
    let localMin = hh * 60 + mm + offset * 60;
    localMin = ((localMin % 1440) + 1440) % 1440;
    const hLocal = Math.floor(localMin / 60);
    const mLocal = localMin % 60;
    const inputOra = document.getElementById('ora-nascita');
    if (inputOra) inputOra.value = String(hLocal).padStart(2,'0') + ':' + String(mLocal).padStart(2,'0');
    ricalcolaTemaSeNecessario();
}

function ricalcolaTemaSeNecessario() {
    if (typeof datiSoggetto !== 'undefined' && document.getElementById('wheel-natale')) {
        const gmtInput = document.getElementById('ora-gmt');
        if (gmtInput && gmtInput.value) {
            const [hh, mm] = gmtInput.value.split(':').map(Number);
            const oraGmtDec = hh + mm / 60;
            fetch('api/tema_api.php?tipo=natale' +
                '&g=' + datiSoggetto.giorno +
                '&m=' + datiSoggetto.mese +
                '&a=' + datiSoggetto.anno +
                '&ora_gmt=' + oraGmtDec +
                '&lat=' + datiSoggetto.lat +
                '&lon=' + datiSoggetto.lon)
                .then(r => r.json())
                .then(tema => {
                    ZodiacWheel.disegna('wheel-natale', tema, {size: 500});
                    document.getElementById('info-natale').textContent =
                        'ASC: ' + (tema.case?.ASC?.posizione?.stringa ?? '?') +
                        ' - MC: ' + (tema.case?.MC?.posizione?.stringa ?? '?');
                    const nomi = {0:'☉ Sole',1:'☽ Luna',2:'☿ Mercurio',3:'♀ Venere',
                                  4:'♂ Marte',5:'♃ Giove',6:'♄ Saturno',7:'♅ Urano',
                                  8:'♆ Nettuno',9:'♇ Plutone',11:'☊ Nodo N.'};
                    let html = '<table class="tabella-pianeti"><tr><th>Pianeta</th><th>Posizione</th><th>Casa</th><th></th></tr>';
                    Object.values(tema.pianeti).forEach(p => {
                        html += '<tr><td>' + (nomi[p.id] ?? p.nome) + '</td>' +
                                '<td>' + p.posizione.stringa + '</td>' +
                                '<td>' + p.casa + '</td>' +
                                '<td>' + (p.retrogrado ? '<span class="retro">R</span>' : '') + '</td></tr>';
                    });
                    html += '</table>';
                    document.getElementById('tab-natale').innerHTML = html;
                });
        }
    }
    if (typeof calcolaRS === 'function') {
        calcolaRS();
    }
}

// ── STAMPA DIRETTA (ESPOSTA GLOBALMENTE) ──────────────────────────────────
/**
 * Stampa diretta della pagina corrente.
 * Aggiunge una classe body.print-XXX che attiva le regole @media print
 * specifiche in print.css, lancia window.print(), poi ripulisce.
 * * Versione esposta esplicitamente su window per garantire che gli onclick
 * inline nei file tema.php, rs.php, rl.php, rilocazione.php funzionino
 * sempre, anche in caso di scope conflict.
 */
window.stampaPagina = function(classeStampa) {
    document.body.classList.add(classeStampa);
    // Piccolo timeout per dare al browser il tempo di applicare il reflow
    // prima di aprire il dialog di stampa (utile su Firefox).
    setTimeout(() => {
        window.print();
        setTimeout(() => document.body.classList.remove(classeStampa), 300);
    }, 50);
};

// ── Trasposizione tabella aspetti (verticale → orizzontale) per la stampa ──
window.buildAspettiOrizzontale = function(bodyId, targetId) {
    const tbody  = document.getElementById(bodyId);
    const target = document.getElementById(targetId);
    if (!tbody || !target) return;
    const righe = Array.from(tbody.querySelectorAll('tr'))
        .filter(r => r.querySelectorAll('td').length >= 5);
    if (righe.length === 0) { target.innerHTML = ''; return; }

    let p1  = '<tr><th>Pianeta 1</th>';
    let p2  = '<tr><th>Pianeta 2</th>';
    let asp = '<tr><th>Aspetto</th>';
    let orb = '<tr><th>Orbe</th>';

    righe.forEach(r => {
        const c = r.querySelectorAll('td');
        p1  += '<td>' + c[0].innerHTML + '</td>';
        p2  += '<td>' + c[2].innerHTML + '</td>';
        asp += '<td>' + c[3].innerHTML + '</td>';
        orb += '<td>' + c[4].innerHTML + '</td>';
    });
    p1 += '</tr>'; p2 += '</tr>'; asp += '</tr>'; orb += '</tr>';

    target.innerHTML = '<table class="tabella-aspetti-orizzontale">' + p1 + p2 + asp + orb + '</table>';
};