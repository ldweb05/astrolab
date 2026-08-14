/**
rl.js — Modulo JavaScript per la pagina Rivoluzioni Lunari (rl.php)
Astrologia Attiva — Scuola Ciro Discepolo
v2.0 — Mappa trasformata in finestra fluttuante (nessun backdrop/overlay).
Dipendenze:
zodiac_wheel.js (ZodiacWheel)
app.js (TIMEZONE_API_KEY)
Leaflet.js (caricato dalla pagina)
svg_zoom.js (initSvgZoom)
API pubblica del modulo:
RLModule.init(ds, lat, lon, luogo, callbackMappaReady)
RLModule.calcolaListaRL()
RLModule.calcolaRL(latOvr?, lonOvr?)
RLModule.cercaLuogoRL()
RLModule.selezionaLuogo(lat, lon, nome)
RLModule.usaPosizioneCorrente()
RLModule.onMappaAperta(mapDivId, coordsDivId, loadingDivId)
RLModule._invalidateMap()
RLModule.salvaSessioneRL()
RLModule.eliminaSessioneRL(id)
*/
'use strict';
const RLModule = (function () {
// ── Stato interno ────────────────────────────────────────────────────
 let _ds             = null;
 let _latRL          = 0;
 let _lonRL          = 0;
 let _luogoRL        = '';
 let _rlIndex        = 0;
 let _rlList         = [];
 let _annoRS         = new Date().getFullYear();
 let _condizione     = 'Decima';
 let _ricalcoloTimer = null;
 let _cbMappaReady   = null;   // callback chiamata dopo il primo render completo
 let _ultimaRLCalcolata = null;
 let _sessioniRSCache   = [];
 // Mappa Leaflet — lazy init
 let _leafletMap      = null;
 let _mapMarker       = null;
 let _mapDivId        = 'leaflet-map-rl';
 let _coordsDivId     = 'mappa-rl-coords';
 let _loadingDivId    = 'mappa-rl-ricalcolo';
 let _mappaInizializzata = false;
 // ── Dizionari ────────────────────────────────────────────────────────
 const NOMI_PIANETI = {
     0:'☉ Sole',1:'☽ Luna',2:'☿ Mercurio',3:'♀ Venere',4:'♂ Marte',
     5:'♃ Giove',6:'♄ Saturno',7:'♅ Urano',8:'♆ Nettuno',9:'♇ Plutone',
     11:'☊ Nodo N.',
 };
 const SIMBOLI_PIANETI = {
     0:'☉',1:'☽',2:'☿',3:'♀',4:'♂',5:'♃',6:'♄',7:'♅',8:'♆',9:'♇',
 };
 const TIPO_ASPETTO = {
     'Trigono':    {sim:'△',cls:'aspetto-trigono'},
     'trine':      {sim:'△',cls:'aspetto-trigono'},
     'Quadrato':   {sim:'□',cls:'aspetto-quadrato'},
     'square':     {sim:'□',cls:'aspetto-quadrato'},
     'Opposizione':{sim:'☍',cls:'aspetto-opposizione'},
     'opposition': {sim:'☍',cls:'aspetto-opposizione'},
     'Sestile':    {sim:'⚹',cls:'aspetto-sestile'},
     'sextile':    {sim:'⚹',cls:'aspetto-sestile'},
     'Congiunzione':{sim:'☌',cls:'aspetto-altro'},
     'conjunction':{sim:'☌',cls:'aspetto-altro'},
 };
 // ════════════════════════════════════════════════════════════════════════
 //  INIT
 // ════════════════════════════════════════════════════════════════════════
 function init(ds, latDefault, lonDefault, luogoDefault, callbackMappaReady) {
     _ds           = ds;
     _latRL        = latDefault  || ds.lat;
     _lonRL        = lonDefault  || ds.lon;
     _luogoRL      = luogoDefault || ds.luogo || '';
     _cbMappaReady = callbackMappaReady || null;
     _annoRS       = parseInt(document.getElementById('anno-rs')?.value) || new Date().getFullYear();
     _legaEventi();
     caricaSessioniRS();
     caricaSessioniRL();
     calcolaListaRL();
 }
 // ════════════════════════════════════════════════════════════════════════
 //  EVENTI DOM
 // ════════════════════════════════════════════════════════════════════════
 function _legaEventi() {
     const selAnno = document.getElementById('anno-rs');
     if (selAnno) selAnno.addEventListener('change', () => {
         _annoRS = parseInt(selAnno.value);
         calcolaListaRL();
     });
     const selRL = document.getElementById('sel-rl');
     if (selRL) selRL.addEventListener('change', () => {
         _rlIndex = parseInt(selRL.value);
         _aggiornaChip(_rlIndex);
         calcolaRL();
     });
     const inputLuogo = document.getElementById('luogo-rl-input');
     if (inputLuogo) inputLuogo.addEventListener('keydown', e => {
         if (e.key === 'Enter') cercaLuogoRL();
     });
     document.addEventListener('click', e => {
         if (!e.target.closest('.luogo-wrap')) {
             const d = document.getElementById('luogo-rl-risultati');
             if (d) d.classList.remove('visible');
         }
     });
 }
 // ════════════════════════════════════════════════════════════════════════
 //  HELPER: INDICE RL CORRENTE
 // ════════════════════════════════════════════════════════════════════════
 /**
  * Trova l'indice della RL "corrente" rispetto alla data odierna:
  * l'ultima RL della lista con data <= oggi. Se tutte le RL sono
  * future, ritorna 0 (prima RL disponibile).
  */
 function _trovaIndiceCorrente(rlList) {
     if (!rlList || rlList.length === 0) return 0;
     const oggiTs = Date.now();
     let idx = 0;
     for (let i = 0; i < rlList.length; i++) {
         const rl = rlList[i];
         const ore = Math.floor(rl.ora_gmt);
         const min = Math.round((rl.ora_gmt - ore) * 60);
         const rlTs = Date.UTC(rl.anno, rl.mese - 1, rl.giorno, ore, min);
         if (rlTs <= oggiTs) {
             idx = i;
         } else {
             break;
         }
     }
     return idx;
 }
 // ════════════════════════════════════════════════════════════════════════
 //  CALCOLA LISTA RL
 // ════════════════════════════════════════════════════════════════════════
 function calcolaListaRL() {
     _annoRS = parseInt(document.getElementById('anno-rs')?.value) || _annoRS;
     const loadingEl = document.getElementById('rl-loading');
     if (loadingEl) loadingEl.style.display = 'block';
     _setSelectDisabled(true);
     const params = new URLSearchParams({
         action:      'lista',
         soggetto_id: _ds.id,
         anno_rs:     _annoRS,
         lat:         _latRL,
         lon:         _lonRL,
     });
     fetch('api/rl_api.php?' + params.toString())
         .then(r => r.json())
         .then(data => {
             if (loadingEl) loadingEl.style.display = 'none';
             if (!data.ok) { _mostraErrore(data.errore || 'Errore calcolo RL.'); return; }
             _rlList  = data.rl_list || [];
             const _rlIndexUrl = new URLSearchParams(window.location.search).get('rl_index');
            _rlIndex = (_rlIndexUrl !== null && _rlList[parseInt(_rlIndexUrl)]) ? parseInt(_rlIndexUrl) : _trovaIndiceCorrente(_rlList);
             _popolaSelect(data);
             _costruisciTimeline(data);
             _setSelectDisabled(false);
             if (_rlList.length > 0) calcolaRL();
         })
         .catch(e => {
             if (loadingEl) loadingEl.style.display = 'none';
             _mostraErrore('Errore rete: ' + e.message);
         });
 }
 // ════════════════════════════════════════════════════════════════════════
 //  POPOLA SELECT + TIMELINE
 // ════════════════════════════════════════════════════════════════════════
 function _popolaSelect(data) {
     const sel = document.getElementById('sel-rl');
     if (!sel) return;
     const MESI = ['','Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
     sel.innerHTML = '';
     if (data.rl_list.length === 0) {
         sel.innerHTML = '<option value="">Nessuna RL trovata</option>';
         return;
     }
     data.rl_list.forEach((rl, idx) => {
         const opt = document.createElement('option');
         opt.value = idx;
         const mese  = MESI[rl.mese] || rl.mese;
         const oraF  = _formatOraGmt(rl.ora_gmt);
         opt.textContent = `☽ RL ${idx + 1} — ${rl.giorno} ${mese} ${rl.anno}  ${oraF} GMT`;
         sel.appendChild(opt);
     });
     sel.value = String(_rlIndex);
 }
 function _costruisciTimeline(data) {
     const timelineEl = document.getElementById('rl-timeline');
     const chipsEl    = document.getElementById('rl-chips');
     if (!timelineEl || !chipsEl) return;
     const MESI = ['','Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
     chipsEl.innerHTML = '';
     data.rl_list.forEach((rl, idx) => {
         const chip = document.createElement('div');
         chip.className = 'rl-chip' + (idx === _rlIndex ? ' attiva' : '');
         chip.dataset.index = idx;
         chip.textContent = `☽${idx+1} · ${rl.giorno} ${MESI[rl.mese] || rl.mese}`;
         chip.title = rl.gmt_str;
         chip.addEventListener('click', () => {
             _rlIndex = idx;
             const sel = document.getElementById('sel-rl');
             if (sel) sel.value = idx;
             _aggiornaChip(idx);
             calcolaRL();
         });
         chipsEl.appendChild(chip);
     });
     timelineEl.style.display = 'block';
 }
 function _aggiornaChip(rlIndex) {
     document.querySelectorAll('.rl-chip').forEach(c => {
         c.classList.toggle('attiva', parseInt(c.dataset.index) === rlIndex);
     });
 }
 // ════════════════════════════════════════════════════════════════════════
 //  CALCOLA SINGOLA RL
 // ════════════════════════════════════════════════════════════════════════
 function calcolaRL(latOvr, lonOvr) {
     const lat = (latOvr !== undefined) ? latOvr
         : parseFloat(document.getElementById('rl-lat')?.value || _latRL);
     const lon = (lonOvr !== undefined) ? lonOvr
         : parseFloat(document.getElementById('rl-lon')?.value || _lonRL);
     const overlay = document.getElementById('rl-overlay');
     if (overlay) overlay.classList.add('visible');
     // Loading anche nella finestra mappa se aperta
     const mapLoading = document.getElementById(_loadingDivId);
     if (mapLoading) mapLoading.classList.add('visible');
     const params = new URLSearchParams({
         action:      'calcola',
         soggetto_id: _ds.id,
         anno_rs:     _annoRS,
         rl_index:    _rlIndex,
         lat:         lat,
         lon:         lon,
         condizione:  _condizione,
     });
     fetch('api/rl_api.php?' + params.toString())
         .then(r => r.json())
         .then(data => {
             if (overlay)    overlay.classList.remove('visible');
             if (mapLoading) mapLoading.classList.remove('visible');
             if (!data.ok) { _mostraErrore(data.errore || 'Errore.'); return; }
             _renderRisultato(data, lat, lon);
         })
         .catch(e => {
             if (overlay)    overlay.classList.remove('visible');
             if (mapLoading) mapLoading.classList.remove('visible');
             _mostraErrore('Errore rete: ' + e.message);
         });
 }
 // ════════════════════════════════════════════════════════════════════════
 //  SALVATAGGIO SESSIONI RL
 // ════════════════════════════════════════════════════════════════════════
 function caricaSessioniRS() {
     fetch('api/sessioni_api.php?action=lista_rs&soggetto_id=' + _ds.id)
         .then(r => r.json())
         .then(rows => {
             _sessioniRSCache = Array.isArray(rows) ? rows : [];
             const sel  = document.getElementById('salva-rl-sessione-rs');
             const wrap = document.getElementById('wrap-collega-rs');
             if (!sel || !wrap) return;
             if (_sessioniRSCache.length === 0) {
                 wrap.style.display = 'none';
                 return;
             }
             wrap.style.display = '';
             sel.innerHTML = '<option value="">— Nessuna —</option>' +
                 _sessioniRSCache.map(s =>
                     `<option value="${s.id}">RS ${s.anno} — ${s.luogo || '?'} (${s.val||''})</option>`
                 ).join('');
         })
         .catch(() => {});
 }
 function caricaSessioniRL() {
     fetch('api/sessioni_api.php?action=lista_rl&soggetto_id=' + _ds.id)
         .then(r => r.json())
         .then(rows => {
             const card = document.getElementById('card-sessioni-rl');
             const div  = document.getElementById('lista-sessioni-rl');
             if (!card || !div) return;
             if (!Array.isArray(rows) || rows.length === 0) {
                 card.style.display = 'none';
                 return;
             }
             card.style.display = 'block';
             let html = '<table class="tabella-soggetti"><thead><tr>' +
                 '<th>Anno rif.</th><th>RL #</th><th>Luogo</th><th>Condizione</th>' +
                 '<th>Stelle</th><th>VAL</th><th>Note</th><th>Salvata il</th><th>Azioni</th>' +
                 '</table></thead><tbody>';
             rows.forEach(s => {
                 const stelle = s.stelline != null
                     ? '★'.repeat(Math.round(s.stelline)) + '☆'.repeat(5 - Math.round(s.stelline))
                     : '—';
                 const dataSalv = new Date(s.creato_il).toLocaleString('it-IT',
                     {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
                 const url = 'rl.php?id=' + _ds.id +
                     '&anno=' + (s.anno_rs || '') +
                     '&lat_rl=' + s.lat + '&lon_rl=' + s.lon +
                     '&luogo_rl=' + encodeURIComponent(s.luogo || '');
                 html += `<tr>
                     <td>${s.anno_rs ?? '—'}</td>
                     <td>☽ ${s.rl_index}</td>
                     <td>${s.luogo || '—'}</td>
                     <td>${s.condizione}</td>
                     <td class="stelle">${stelle}</td>
                     <td><span class="val-badge">${s.val || '—'}</span></td>
                     <td class="session-note-cell">${s.note || ''}</td>
                     <td class="session-date-cell">${dataSalv}</td>
                     <td><div class="azioni">
                         <a href="${url}" class="btn-icon" title="Richiama questa sessione">↺</a>
                         <button class="btn-icon" title="Elimina" onclick="RLModule.eliminaSessioneRL(${s.id})">🗑️</button>
                     </div></td>
                 </tr>`;
             });
             html += '</tbody></table>';
             div.innerHTML = html;
         })
         .catch(() => {});
 }
 function salvaSessioneRL() {
     if (!_ultimaRLCalcolata) {
         alert('Calcola prima una RL prima di salvarla.');
         return;
     }
     const note  = document.getElementById('salva-rl-note')?.value.trim() || '';
     const selRS = document.getElementById('salva-rl-sessione-rs');
     const sessioneRsId = selRS && selRS.value ? parseInt(selRS.value) : null;
     const btn = document.getElementById('btn-salva-rl');
     const msg = document.getElementById('salva-rl-msg');
     btn.disabled = true;
     btn.textContent = '⟳ Salvataggio...';
     fetch('api/sessioni_api.php', {
         method: 'POST',
         headers: {'Content-Type': 'application/json'},
         body: JSON.stringify({
             action:         'salva_rl',
             soggetto_id:    _ds.id,
             sessione_rs_id: sessioneRsId,
             anno_rs:        _annoRS,
             rl_index:       _rlIndex,
             condizione:     _ultimaRLCalcolata.condizione,
             lat:            _ultimaRLCalcolata.lat,
             lon:            _ultimaRLCalcolata.lon,
             luogo:          _ultimaRLCalcolata.luogo,
             rl_gmt:         _ultimaRLCalcolata.rl_gmt,
             stelline:       _ultimaRLCalcolata.stelline,
             val:            _ultimaRLCalcolata.val,
             note:           note,
         })
     })
     .then(r => r.json())
     .then(data => {
         btn.disabled = false;
         btn.textContent = '💾 Salva questa RL';
         if (data.ok) {
             msg.innerHTML = '<span class="message-success-inline">✅ Sessione RL salvata.</span>';
             document.getElementById('salva-rl-note').value = '';
             caricaSessioniRL();
             setTimeout(() => { msg.innerHTML = ''; }, 3000);
         } else {
             msg.innerHTML = '<span class="message-error-inline">⚠️ ' + (data.errore || 'Errore salvataggio') + '</span>';
         }
     })
     .catch(e => {
         btn.disabled = false;
         btn.textContent = '💾 Salva questa RL';
         msg.innerHTML = '<span class="message-error-inline">⚠️ Errore rete: ' + e.message + '</span>';
     });
 }
 function eliminaSessioneRL(id) {
     if (!confirm('Eliminare questa sessione RL salvata?')) return;
     fetch('api/sessioni_api.php', {
         method: 'POST',
         headers: {'Content-Type': 'application/json'},
         body: JSON.stringify({action: 'elimina_rl', id})
     })
     .then(r => r.json())
     .then(data => { if (data.ok) caricaSessioniRL(); });
 }
 // ════════════════════════════════════════════════════════════════════════
 //  RENDER RISULTATO - MODIFICATO CON ZOOM
 // ════════════════════════════════════════════════════════════════════════
 function _renderRisultato(data, lat, lon) {
     const luogoRL = document.getElementById('luogo-rl-input')?.value || _luogoRL;
     // ── Header RL ──────────────────────────────────────────────────
     const headerRL = document.getElementById('header-rl');
     if (headerRL) headerRL.style.display = 'flex';
     _setText('rl-indice-label', `${_rlIndex + 1} di ${_rlList.length} (RS ${_annoRS})`);
     _setText('rl-gmt-label',    data.rl_gmt);
     _setText('rl-luogo-label',  luogoRL || `${lat.toFixed(4)}°, ${lon.toFixed(4)}°`);
     _setText('rl-asc-label',    data.tema_rl?.case?.ASC?.posizione?.stringa ?? '—');
     _setText('rl-mc-label',     data.tema_rl?.case?.MC?.posizione?.stringa  ?? '—');
     // ── Ruote SVG con ZOOM ──────────────────────────────────────────
     if (data.tema_natale) {
         ZodiacWheel.disegna('wheel-natale', data.tema_natale, {size:480});
         // ATTIVA ZOOM per il tema natale
         if (typeof initSvgZoom === 'function') {
             initSvgZoom('wheel-natale');
         }
         _setText('info-natale',
             'ASC: '+(data.tema_natale.case?.ASC?.posizione?.stringa??'?')+
             ' — MC: '+(data.tema_natale.case?.MC?.posizione?.stringa??'?'));
         _popolaTabellaPianeti('tab-natale', data.tema_natale);
     }
     if (data.tema_rl) {
         ZodiacWheel.disegna('wheel-rl', data.tema_rl, {size:480});
         // ATTIVA ZOOM per la RL
         if (typeof initSvgZoom === 'function') {
             initSvgZoom('wheel-rl');
         }
         _setText('info-rl',
             'ASC: '+(data.tema_rl.case?.ASC?.posizione?.stringa??'?')+
             ' — MC: '+(data.tema_rl.case?.MC?.posizione?.stringa??'?'));
         _setText('rl-titolo', `☽ RL ${_rlIndex+1} — ${luogoRL || 'Selezionata'}`);
         _popolaTabellaPianeti('tab-rl', data.tema_rl);
         _popolaTabellaCuspidi('cuspidi-rl-body', data.tema_rl);
     }
     // ── Valutazione ────────────────────────────────────────────────
     if (data.valutazione) {
         const v = data.valutazione;
         const valEl = document.getElementById('valutazione');
         if (valEl) valEl.style.display = 'block';
         _setText('val-stelle',    v.stelle_str || '');
         _setText('val-stringa',   v.val || '');
         _setText('val-condizione','☽ Condizione: ' + v.condizione);
         const vetiEl = document.getElementById('val-veti');
         if (vetiEl) vetiEl.innerHTML = (v.veti||[]).map(t =>
             `<div class="val-item val-veto">⛔ ${t}</div>`).join('');
         const bonusEl = document.getElementById('val-bonus');
         if (bonusEl) bonusEl.innerHTML = (v.bonus||[]).length
             ? v.bonus.map(b=>`<div class="val-item val-bonus"><b>${b.codice}</b> ${b.nota||''}</div>`).join('')
             : '<div class="val-empty-message">Nessun bonus significativo</div>';
         const penEl = document.getElementById('val-penali');
         if (penEl) {
             const html = [
                 ...(v.penalita||[]).map(p=>`<div class="val-item val-penali"><b>${p.codice}</b> ${p.nota||''}</div>`),
                 ...(v.note||[]).map(n=>`<div class="val-item val-note"><b>${n.codice}</b> ${n.nota||''}</div>`),
             ].join('');
             penEl.innerHTML = html || '<div class="val-empty-message">Nessuna penalità</div>';
         }
     }
     // ── Aspetti ────────────────────────────────────────────────────
     _popolaTabellaAspetti(data.aspetti || []);
     // ── Mostra temi + bottone mappa ────────────────────────────────
     const temiEl = document.getElementById('temi-wrapper');
     if (temiEl) temiEl.style.display = 'flex';
     if (_cbMappaReady) { _cbMappaReady(); _cbMappaReady = null; }
     // ── Memorizza dati per il pulsante "Salva sessione" ──
     _ultimaRLCalcolata = {
         condizione: _condizione,
         lat:        lat,
         lon:        lon,
         luogo:      luogoRL,
         rl_gmt:     data.rl_gmt,
         stelline:   data.valutazione?.stelline,
         val:        data.valutazione?.val,
     };
     const cardSalvaRL = document.getElementById('card-salva-rl');
     if (cardSalvaRL) cardSalvaRL.style.display = 'block';
     // Aggiorna marker se la mappa è già aperta
     if (_leafletMap && _mapMarker) {
         _mapMarker.setLatLng([lat, lon]);
         _leafletMap.setView([lat, lon], _leafletMap.getZoom());
     }
     // ── Ora locale / fuso (TimeZoneDB) ─────────────────────────────
     _aggiornaFusoOrario(lat, lon, data.rl_gmt);
 }
 // ════════════════════════════════════════════════════════════════════════
 //  MAPPA LEAFLET — lazy init su chiamata esplicita da rl.php
 // ════════════════════════════════════════════════════════════════════════
 function onMappaAperta(mapDivId, coordsDivId, loadingDivId) {
     _mapDivId     = mapDivId     || _mapDivId;
     _coordsDivId  = coordsDivId  || _coordsDivId;
     _loadingDivId = loadingDivId || _loadingDivId;
     const lat = parseFloat(document.getElementById('rl-lat')?.value || _latRL);
     const lon = parseFloat(document.getElementById('rl-lon')?.value || _lonRL);
     if (!_mappaInizializzata) {
         setTimeout(() => _initLeaflet(lat, lon), 80);
     } else {
         _mapMarker.setLatLng([lat, lon]);
         _leafletMap.setView([lat, lon], _leafletMap.getZoom());
         setTimeout(() => _leafletMap.invalidateSize(), 60);
     }
     const coordsEl = document.getElementById(_coordsDivId);
     if (coordsEl) coordsEl.textContent = lat.toFixed(4) + '°  ' + lon.toFixed(4) + '°';
 }
 function _initLeaflet(lat, lon) {
     const mapDiv = document.getElementById(_mapDivId);
     if (!mapDiv) return;
     _leafletMap = L.map(_mapDivId).setView([lat, lon], 5);
     L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', {
         attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
         maxZoom: 18
     }).addTo(_leafletMap);
     _mapMarker = L.marker([lat, lon], {draggable: true}).addTo(_leafletMap);
     _mapMarker.on('drag', e => {
         const p = e.target.getLatLng();
         const coordsEl = document.getElementById(_coordsDivId);
         if (coordsEl) coordsEl.textContent = p.lat.toFixed(4) + '°  ' + p.lng.toFixed(4) + '°';
     });
     _mapMarker.on('dragend', e => {
         const p   = e.target.getLatLng();
         const lat = p.lat, lon = p.lng;
         const coordsEl = document.getElementById(_coordsDivId);
         if (coordsEl) coordsEl.textContent = lat.toFixed(4) + '°  ' + lon.toFixed(4) + '°';
         _sincronizzaCoordinate(lat, lon);
         clearTimeout(_ricalcoloTimer);
         _ricalcoloTimer = setTimeout(() => calcolaRL(lat, lon), 280);
     });
     _leafletMap.on('click', e => {
         const lat = e.latlng.lat, lon = e.latlng.lng;
         _mapMarker.setLatLng([lat, lon]);
         const coordsEl = document.getElementById(_coordsDivId);
         if (coordsEl) coordsEl.textContent = lat.toFixed(4) + '°  ' + lon.toFixed(4) + '°';
         _sincronizzaCoordinate(lat, lon);
         clearTimeout(_ricalcoloTimer);
         _ricalcoloTimer = setTimeout(() => calcolaRL(lat, lon), 280);
     });
     _leafletMap.invalidateSize();
     _mappaInizializzata = true;
 }
 function _invalidateMap() {
     if (_leafletMap && _mappaInizializzata) _leafletMap.invalidateSize();
 }
 // ════════════════════════════════════════════════════════════════════════
 //  GEOCODING LUOGO RL
 // ════════════════════════════════════════════════════════════════════════
 function cercaLuogoRL() {
     const q = document.getElementById('luogo-rl-input')?.value?.trim();
     if (!q || q.length < 3) return;
     fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=6&addressdetails=1`)
         .then(r => r.json())
         .then(ris => {
             const div = document.getElementById('luogo-rl-risultati');
             if (!div) return;
             div.innerHTML = ris.map(r => {
                 const nomeBreve = _estraiNomeLuogoNominatim(r).replace(/'/g,"\\'");
                 return `<div class="dropdown-item"
                       onclick="RLModule.selezionaLuogo(${r.lat},${r.lon},'${r.display_name.replace(/'/g,"\\'")}','${nomeBreve}')">
                     ${r.display_name}
                 </div>`;
             }).join('');
             div.classList.add('visible');
         });
 }
 function _estraiNomeLuogoNominatim(r) {
     const a = (r && r.address) || {};
     const loc = a.city || a.town || a.village || a.municipality || a.hamlet || a.county;
     const stato = a.state || a.region;
     if (loc && stato && loc !== stato) return loc + ', ' + stato;
     if (loc) return loc;
     if (stato) return stato;
     if (a.country) return a.country;
     return (r.display_name || '').split(',')[0].trim();
 }

 function selezionaLuogo(lat, lon, nome, nomeBreve) {
     const citta = nomeBreve || nome.split(',')[0].trim();
     _luogoRL = citta;
     const inpLuogo = document.getElementById('luogo-rl-input');
     if (inpLuogo) inpLuogo.value = citta;
     _sincronizzaCoordinate(parseFloat(lat), parseFloat(lon));
     if (_leafletMap && _mapMarker) {
         _mapMarker.setLatLng([parseFloat(lat), parseFloat(lon)]);
         _leafletMap.setView([parseFloat(lat), parseFloat(lon)], 8);
     }
     const div = document.getElementById('luogo-rl-risultati');
     if (div) div.classList.remove('visible');
     calcolaRL(parseFloat(lat), parseFloat(lon));
 }
 function usaPosizioneCorrente() {
     if (!_mapMarker) return;
     const p = _mapMarker.getLatLng();
     _sincronizzaCoordinate(p.lat, p.lng);
 }
 function _sincronizzaCoordinate(lat, lon) {
     _latRL = lat; _lonRL = lon;
     const inpLat = document.getElementById('rl-lat');
     const inpLon = document.getElementById('rl-lon');
     if (inpLat) inpLat.value = lat.toFixed(4);
     if (inpLon) inpLon.value = lon.toFixed(4);
 }
 // ════════════════════════════════════════════════════════════════════════
 //  FUSO ORARIO (TimeZoneDB)
 // ════════════════════════════════════════════════════════════════════════
 function _aggiornaFusoOrario(lat, lon, gmtStr) {
     try {
         const parti = gmtStr.replace(' GMT','').split(' ');
         const dp  = parti[0].split('-');
         const op  = parti[1].split(':');
         const utcMs = Date.UTC(
             parseInt(dp[0]), parseInt(dp[1])-1, parseInt(dp[2]),
             parseInt(op[0]), parseInt(op[1]), parseInt(op[2]||0)
         );
         const ts  = Math.floor(utcMs / 1000);
         const key = TIMEZONE_API_KEY; // definita in app.js, caricato prima di rl.js
         fetch(`https://api.timezonedb.com/v2.1/get-time-zone?key=${key}&format=json&by=position&lat=${lat}&lng=${lon}&time=${ts}`)
             .then(r => r.json())
             .then(tz => {
                 if (tz.status !== 'OK') return;
                 const oraLocale    = tz.formatted.split(' ')[1] || '—';
                 const hrlEl = document.getElementById('header-rl');
                 if (!hrlEl) return;
                 let oraLocEl = document.getElementById('rl-ora-locale-wrap');
                 if (!oraLocEl) {
                     oraLocEl = document.createElement('div');
                     oraLocEl.id = 'rl-ora-locale-wrap';
                     hrlEl.appendChild(oraLocEl);
                 }
                 const offsetH  = Math.round(tz.gmtOffset / 3600 * 10) / 10;
                 const segno    = offsetH >= 0 ? '+' : '';
                 oraLocEl.innerHTML =
                     `<span class="rl-time-label">Ora locale: </span>` +
                     `<b class="rl-time-value">${oraLocale}</b>` +
                     `<span class="rl-time-label rl-time-label-spaced">Fuso: </span>` +
                     `<b class="rl-time-value">GMT ${segno}${offsetH}</b>`;
             })
             .catch(() => {});
     } catch(e) {}
 }
 // ════════════════════════════════════════════════════════════════════════
 //  RENDER TABELLE
 // ════════════════════════════════════════════════════════════════════════
 function _popolaTabellaPianeti(tabId, tema) {
     const el = document.getElementById(tabId);
     if (!el || !tema?.pianeti) return;

     let html = '<thead><tr>'
         + '<th>Pianeta</th><th>Posizione</th><th>Casa</th><th></th>'
         + '</tr></thead><tbody>';

     Object.values(tema.pianeti).forEach(p => {
         html += `<tr>
             <td>${NOMI_PIANETI[p.id] ?? p.nome}</td>
             <td>${p.posizione?.stringa ?? '?'}</td>
             <td>${p.casa}</td>
             <td>${p.retrogrado ? '<span class="retro">R</span>' : ''}</td>
         </tr>`;
     });

     html += '</tbody>';
     el.innerHTML = html;
 }
 function _popolaTabellaAspetti(aspetti) {
     const tbody = document.getElementById('aspetti-rl-body');
     if (!tbody) return;
     if (!aspetti || aspetti.length === 0) {
         tbody.innerHTML = '<tr><td colspan="5" class="table-empty-cell">Nessun aspetto rilevante</td></tr>';
         return;
     }
     tbody.innerHTML = aspetti.map(a => {
         const ti = TIPO_ASPETTO[a.aspetto || a.tipo] || {sim:'•', cls:'aspetto-altro'};
         return `<tr>
             <td>${SIMBOLI_PIANETI[a.pianeta_a]??''} ${NOMI_PIANETI[a.pianeta_a]||a.nome_a||'?'}</td>
             <td class="aspect-arrow">→</td>
             <td>${SIMBOLI_PIANETI[a.pianeta_b]??''} ${NOMI_PIANETI[a.pianeta_b]||a.nome_b||'?'}</td>
             <td class="${ti.cls}">${ti.sim} ${a.aspetto||a.tipo}</td>
             <td>${a.scarto?.toFixed(1)??'?'}°</td>
         </tr>`;
     }).join('');
 }
 function _popolaTabellaCuspidi(tbodyId, tema) {
     const tbody = document.getElementById(tbodyId);
     if (!tbody || !tema?.case) return;
     const CASE_LABEL = {
         1:'I o ASC', 2:'II', 3:'III', 4:'IV o FC', 5:'V', 6:'VI',
         7:'VII o DSC', 8:'VIII', 9:'IX', 10:'X o MC', 11:'XI', 12:'XII'
     };
     const ANGOLARI = new Set([1, 4, 7, 10]);
     let html = '';
     for (let c = 1; c <= 12; c++) {
         const casa = tema.case[c];
         if (!casa) continue;
         const label   = CASE_LABEL[c] || String(c);
         const stringa = casa.posizione?.stringa ?? '—';
         const angularClass = ANGOLARI.has(c) ? ' cuspide-angolare-rl' : '';
         html += `<tr>
             <td class="cuspide-label${angularClass}">${label}</td>
             <td class="${angularClass.trim()}">${stringa}</td>
         </tr>`;
     }
     tbody.innerHTML = html || '<tr><td colspan="2" class="table-empty-cell">—</td></tr>';
 }
 // ════════════════════════════════════════════════════════════════════════
 //  UTILITIES
 // ════════════════════════════════════════════════════════════════════════
 function _setText(id, txt) {
     const el = document.getElementById(id);
     if (el) el.textContent = txt;
 }
 function _setSelectDisabled(dis) {
     const sel = document.getElementById('sel-rl');
     if (sel) sel.disabled = dis;
 }
 function _mostraErrore(msg) {
     const loadingEl = document.getElementById('rl-loading');
     if (loadingEl) {
         loadingEl.style.display = 'block';
         loadingEl.innerHTML = `<p class="message-error-inline">❌ ${msg}</p>`;
     }
     console.error('[RLModule]', msg);
 }
 function _formatOraGmt(oraDecimale) {
     const h = Math.floor(oraDecimale);
     const m = Math.round((oraDecimale - h) * 60);
     return String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0');
 }
 // ════════════════════════════════════════════════════════════════════════
 //  API PUBBLICA
 // ════════════════════════════════════════════════════════════════════════
 return {
     init,
     calcolaListaRL,
     calcolaRL,
     cercaLuogoRL,
     selezionaLuogo,
     usaPosizioneCorrente,
     onMappaAperta,
     _invalidateMap,
     salvaSessioneRL,
     eliminaSessioneRL,
 };
})(); // fine IIFE RLModule