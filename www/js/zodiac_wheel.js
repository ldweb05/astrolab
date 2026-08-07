/**
 * zodiac_wheel.js — Rendering SVG ruota zodiacale
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * v2.4 — Sistema di Collision Avoidance Bidimensionale (Angolare + Radiale Multi-Livello)
 * 1. Gli assi angolari (ASC, FC, DSC, MC) agiscono da barriere grafiche fisse insuperabili.
 * 2. Gli oggetti vicino agli angoli o ammassati scalano automaticamente su 3 orbite radiali differenti.
 * 3. Risolto bug di posizionamento delle etichette delle cuspidi delle case.
 */

const ZodiacWheel = {

    SEGNI_GLIFI: ['♈︎','♉︎','♊︎','♋︎','♌︎','♍︎','♎︎','♏︎','♐︎','♑︎','♒︎','♓︎'],

    SEGNI_COLORI: [
        '#CC3333','#228833','#3355CC','#CC3333',
        '#CC3333','#228833','#3355CC','#CC3333',
        '#CC3333','#228833','#3355CC','#CC3333'
    ],

    PIANETI_GLIFI: {
        0:'☉', 1:'☽', 2:'☿', 3:'♀', 4:'♂', 5:'♃',
        6:'♄', 7:'♅', 8:'♆', 9:'♇', 11:'☊',
    },

    PIANETI_COLORI: {
        0:'#CC8800', 1:'#4444AA', 2:'#CC4400', 3:'#AA2266',
        4:'#CC2222', 5:'#2244AA', 6:'#666622', 7:'#228888',
        8:'#222288', 9:'#662266', 11:'#226622',
    },

    ASPETTI: [
        { angolo:   0, nome: 'congiunzione', colore: '#555555', orbe: 8, dash: ''    },
        { angolo: 180, nome: 'opposizione',  colore: '#CC2222', orbe: 8, dash: ''    },
        { angolo: 120, nome: 'trigono',      colore: '#228822', orbe: 7, dash: ''    },
        { angolo:  90, nome: 'quadratura',   colore: '#CC2222', orbe: 7, dash: '4,3' },
        { angolo:  60, nome: 'sestile',      colore: '#2255AA', orbe: 5, dash: '3,3' },
    ],

    _gradiVisibili: false,
    _cuspidiVisibili: true,

    _coloreSemantico: function(p, houses) {
        const rosso = '#CC0000'; // Diretto
        const blu   = '#0000CC'; // Retrogrado
        const verde = '#00AA00'; // In cuspide

        // Orbite differenziate per pianeta e casa
        const SOGLIA_BASE = 2.5;       // Tutti i pianeti, tutte le case
        const SOGLIA_ANGOLI = 10.0;    // Saturno e Marte su I/ASC e X/MC
        const PIANETI_ANGOLI = [4, 6]; // Marte (4), Saturno (6)
        const CASE_ANGOLI = ['1', 'ASC', '10', 'MC']; // I=ASC, X=MC

        if (houses) {
            for (const k in houses) {
                const cuspide = houses[k];
                if (!cuspide || typeof cuspide.longitudine !== 'number') continue;

                let diff = Math.abs(p.longitudine - cuspide.longitudine) % 360;
                if (diff > 180) diff = 360 - diff;

                // Determina la soglia applicabile
                const isPianetaAngoli = PIANETI_ANGOLI.includes(p.id);
                const isCasaAngolo = CASE_ANGOLI.includes(String(k));
                const soglia = (isPianetaAngoli && isCasaAngolo) ? SOGLIA_ANGOLI : SOGLIA_BASE;

                if (diff <= soglia) return verde;
            }
        }

        if (p.retrogrado) return blu;
        return rosso;
    },

    disegna: function(svgId, tema, opzioni) {
        opzioni = opzioni || {};
        const svg = document.getElementById(svgId);
        if (!svg) return;

        const size   = opzioni.size || 500;
        const margin = opzioni.margin !== undefined ? opzioni.margin : size * 0.12;
        const vbSize = size + margin * 2;
        const cx     = vbSize / 2;
        const cy     = vbSize / 2;

        const rZodE  = size * 0.44;   
        const rZodI  = size * 0.355;  
        const rCaseE = size * 0.345;  
        const rCaseI = size * 0.18;   
        const rPiaE  = size * 0.505; // Raggio di base per l'orbita dei pianeti

        svg.setAttribute('viewBox', '0 0 ' + vbSize + ' ' + vbSize);
        svg.setAttribute('style', 'background:white');
        svg.innerHTML = '';

        // 1) Sfondo rettangolare con classe print-nofill
        const bg = this._createElement('rect');
        bg.setAttribute('width', vbSize);
        bg.setAttribute('height', vbSize);
        bg.setAttribute('fill', '#FFFFFF');
        bg.setAttribute('class', 'print-nofill');
        svg.appendChild(bg);

        // Cerchio esterno delle case con classe print-nofill
        const cerchioCase = this._circle(cx, cy, rCaseE, '#E8F0FF', '#AABBDD', 0.8);
        cerchioCase.setAttribute('class', 'print-nofill');
        svg.appendChild(cerchioCase);

        this._disegnaZodiaco(svg, cx, cy, rZodE, rZodI, tema);
        this._disegnaCase(svg, cx, cy, rCaseE, rCaseI, tema);

        // 2) Cerchio interno centrale con classe print-nofill
        const cerchioInterno = this._circle(cx, cy, rCaseI, '#FFFFFF', '#AABBDD', 1);
        cerchioInterno.setAttribute('class', 'print-nofill');
        svg.appendChild(cerchioInterno);

        this._disegnaCuspidiCase(svg, cx, cy, rCaseI, rCaseE, tema);
        this._disegnaAspetti(svg, cx, cy, rCaseI, tema);

        // MOTORE DI CALCOLO AVANZATO (ANGOLARE + RADIALE CONCENTRICO)
        const mappaDisplay = this._calcolaPosizioniDisplayAvanzato(Object.values(tema.pianeti || {}), rPiaE, tema, size);

        // Disegno delle linee guida e dei glifi basati sulla nuova mappa bidimensionale
        this._disegnaLineePianetiModificato(svg, cx, cy, rZodI, tema, mappaDisplay);
        this._disegnaPianetiModificato(svg, cx, cy, rZodE, tema, mappaDisplay);
        
        // Gli angoli cardinali vengono disegnati sopra per mantenere la leggibilità geometrica pura
        this._disegnaAngoli(svg, cx, cy, rZodE, rCaseI, tema);
        svg.appendChild(this._circle(cx, cy, rZodE, 'none', '#8899BB', 1.5));
        
        this._applicaStatoGradi();
        this._applicaStatoCuspidi();
    },

    _disegnaAspetti: function(svg, cx, cy, rCerchio, tema) {
        if (!tema.pianeti || !tema.case) return;
        const mcLon = (tema.case.MC ? tema.case.MC.longitudine : 0);
        const punti = [];
        Object.values(tema.pianeti).forEach(p => { punti.push({ lon: p.longitudine, label: p.nome }); });
        if (tema.case.ASC) punti.push({ lon: tema.case.ASC.longitudine, label: 'ASC' });
        if (tema.case.MC)  punti.push({ lon: tema.case.MC.longitudine,  label: 'MC'  });

        const rLinea = rCerchio * 0.92;

        for (let i = 0; i < punti.length; i++) {
            for (let j = i + 1; j < punti.length; j++) {
                const lonA = punti[i].lon;
                const lonB = punti[j].lon;
                let diff = Math.abs(lonA - lonB) % 360;
                if (diff > 180) diff = 360 - diff;

                const aspect = this._trovaAspetto(diff);
                if (!aspect) continue;

                const radA = this._lon2rad(lonA, mcLon);
                const radB = this._lon2rad(lonB, mcLon);

                const x1 = cx + rLinea * Math.cos(radA);
                const y1 = cy + rLinea * Math.sin(radA);
                const x2 = cx + rLinea * Math.cos(radB);
                const y2 = cy + rLinea * Math.sin(radB);

                const linea = this._createElement('line');
                linea.setAttribute('x1', x1); linea.setAttribute('y1', y1);
                linea.setAttribute('x2', x2); linea.setAttribute('y2', y2);
                linea.setAttribute('stroke', aspect.colore);
                const spessore = aspect.nome === 'congiunzione' ? 1.5 :
                                 aspect.nome === 'trigono'      ? 1.2 :
                                 aspect.nome === 'sestile'      ? 0.7 : 1.0;
                linea.setAttribute('stroke-width', spessore);
                linea.setAttribute('opacity', '0.75');
                if (aspect.dash) linea.setAttribute('stroke-dasharray', aspect.dash);
                svg.appendChild(linea);
            }
        }

        Object.values(tema.pianeti).forEach(p => {
            const haAspetti = this._pianetaHaAspetti(p.longitudine, punti);
            if (!haAspetti) return;
            const rad = this._lon2rad(p.longitudine, mcLon);
            const xP  = cx + rLinea * Math.cos(rad);
            const yP  = cy + rLinea * Math.sin(rad);
            const dot = this._createElement('circle');
            dot.setAttribute('cx', xP); dot.setAttribute('cy', yP); dot.setAttribute('r', 2.5);
            dot.setAttribute('fill', this._coloreSemantico(p, tema.case));
            dot.setAttribute('opacity', '0.9');
            svg.appendChild(dot);
        });
    },

    _trovaAspetto: function(diff) {
        for (const asp of this.ASPETTI) {
            if (Math.abs(diff - asp.angolo) <= asp.orbe) return asp;
        }
        return null;
    },

    _pianetaHaAspetti: function(lon, tuttiPunti) {
        for (const altro of tuttiPunti) {
            if (altro.lon === lon) continue;
            let diff = Math.abs(lon - altro.lon) % 360;
            if (diff > 180) diff = 360 - diff;
            if (this._trovaAspetto(diff)) return true;
        }
        return false;
    },

    _disegnaZodiaco: function(svg, cx, cy, rEst, rInt, tema) {
        const mcLon = (tema.case && tema.case.MC ? tema.case.MC.longitudine : 0);
        
        // 3) Cerchio esterno dello zodiaco con classe print-nofill
        const cerchioZodEst = this._circle(cx, cy, rEst, '#F0F4FF', '#AABBDD', 0.5);
        cerchioZodEst.setAttribute('class', 'print-nofill');
        svg.appendChild(cerchioZodEst);

        const bgColori = ['#FFF0F0','#F0FFF0','#F0F0FF','#FFF8F0','#FFF0F0','#F0FFF0',
                          '#F0F0FF','#FFF8F0','#FFF0F0','#F0FFF0','#F0F0FF','#FFF8F0'];

        for (let s = 0; s < 12; s++) {
            const lonI = s * 30;
            const lonF = lonI + 30;
            
            // 4) Singoli settori zodiacali con classe print-nofill
            const path = this._settore(cx, cy, rInt, rEst, lonI, lonF, mcLon);
            path.setAttribute('fill', bgColori[s]);
            path.setAttribute('stroke', '#AABBDD');
            path.setAttribute('stroke-width', '0.5');
            path.setAttribute('class', 'print-nofill');
            svg.appendChild(path);

            const midLon = lonI + 15;
            const angRad = this._lon2rad(midLon, mcLon);
            const rMid   = (rInt + rEst) / 2;
            const x = cx + rMid * Math.cos(angRad);
            const y = cy + rMid * Math.sin(angRad);
            svg.appendChild(this._text(x, y, this.SEGNI_GLIFI[s], {
                size: rEst * 0.100, fill: this.SEGNI_COLORI[s],
                anchor: 'middle', baseline: 'central', bold: false
            }));
        }

        for (let s = 0; s < 12; s++) {
            const rad = this._lon2rad(s * 30, mcLon);
            svg.appendChild(this._line(
                cx + rInt * Math.cos(rad), cy + rInt * Math.sin(rad),
                cx + rEst * Math.cos(rad), cy + rEst * Math.sin(rad),
                '#AABBDD', 0.8
            ));
        }

        this._disegnaScalaGradi(svg, cx, cy, rInt, mcLon);
        svg.appendChild(this._circle(cx, cy, rInt, 'none', '#AABBDD', 1));
        svg.appendChild(this._circle(cx, cy, rEst, 'none', '#AABBDD', 1));
    },

    _disegnaScalaGradi: function(svg, cx, cy, rZodI, mcLon) {
        const lungCorta  = rZodI * 0.055;  
        const lungLunga  = rZodI * 0.110;  
        const colore     = '#8899BB';
        const spessCorta = 0.4;
        const spessLunga = 0.8;

        for (let g = 0; g < 360; g++) {
            if (g % 30 === 0) continue;
            const isQuinta = (g % 5 === 0);
            const lung     = isQuinta ? lungLunga : lungCorta;
            const spess    = isQuinta ? spessLunga : spessCorta;
            const rad  = this._lon2rad(g, mcLon);
            svg.appendChild(this._line(
                cx + rZodI * Math.cos(rad), cy + rZodI * Math.sin(rad),
                cx + (rZodI - lung) * Math.cos(rad), cy + (rZodI - lung) * Math.sin(rad),
                colore, spess
            ));
        }
    },

    _disegnaCase: function(svg, cx, cy, rEst, rInt, tema) {
        if (!tema.case) return;
        const mcLon = (tema.case.MC ? tema.case.MC.longitudine : 0);

        for (let c = 1; c <= 12; c++) {
            if (!tema.case[c]) continue;
            const lonC    = tema.case[c].longitudine;
            const lonNext = tema.case[(c % 12) + 1] ? tema.case[(c % 12) + 1].longitudine : lonC + 30;
            const rad   = this._lon2rad(lonC, mcLon);
            const isAng = (c === 1 || c === 4 || c === 7 || c === 10);

            svg.appendChild(this._line(
                cx + rInt * Math.cos(rad), cy + rInt * Math.sin(rad),
                cx + rEst * Math.cos(rad), cy + rEst * Math.sin(rad),
                isAng ? '#334477' : '#7788AA', isAng ? 1.5 : 0.7
            ));

            const midLon = this._midAng(lonC, lonNext);
            const radM   = this._lon2rad(midLon, mcLon);
            const rMid   = (rInt + rEst) * 0.52;
            svg.appendChild(this._text(
                cx + rMid * Math.cos(radM), cy + rMid * Math.sin(radM),
                String(c), {
                    size: rEst * 0.1, fill: '#334477',
                    anchor: 'middle', baseline: 'central', bold: isAng
                }
            ));
        }
    },

    _calcolaPosizioniDisplayAvanzato: function(pianeti, rPia, tema, size) {
        const mcLon = (tema.case && tema.case.MC ? tema.case.MC.longitudine : 0);
        const glyfSize = size * 0.048;          
        const sepMinima = glyfSize * 1.3 / rPia;

        const angoliRad = [];
        if (tema.case) {
            if (tema.case.ASC) angoliRad.push(this._lon2rad(tema.case.ASC.longitudine, mcLon));
            if (tema.case[7])   angoliRad.push(this._lon2rad(tema.case[7].longitudine, mcLon));
            if (tema.case.MC)  angoliRad.push(this._lon2rad(tema.case.MC.longitudine, mcLon));
            if (tema.case[4])   angoliRad.push(this._lon2rad(tema.case[4].longitudine, mcLon));
        }

        let items = pianeti.map(p => ({
            id: p.id,
            radVero: this._lon2rad(p.longitudine, mcLon),
            radDisp: this._lon2rad(p.longitudine, mcLon),
            rDisp: rPia,
            livello: 0
        }));

        items.sort((a, b) => a.radDisp - b.radDisp);

        for (let pass = 0; pass < 10; pass++) {
            let moved = false;
            for (let i = 0; i < items.length; i++) {
                const j = (i + 1) % items.length;
                let diff = items[j].radDisp - items[i].radDisp;
                while (diff > Math.PI) diff -= 2 * Math.PI;
                while (diff < -Math.PI) diff += 2 * Math.PI;

                if (Math.abs(diff) < sepMinima) {
                    const spinta = (sepMinima - Math.abs(diff)) / 2;
                    items[i].radDisp -= spinta;
                    items[j].radDisp += spinta;
                    moved = true;
                }
            }
            if (!moved) break;
        }

        items.sort((a, b) => a.radDisp - b.radDisp);
        for (let i = 0; i < items.length; i++) {
            for (const angRad of angoliRad) {
                let diff = items[i].radDisp - angRad;
                while (diff > Math.PI) diff -= 2 * Math.PI;
                while (diff < -Math.PI) diff += 2 * Math.PI;

                if (Math.abs(diff) < sepMinima * 0.8) {
                    items[i].livello = (i % 2 === 0) ? 1 : 2;
                }
            }
        }

        for (let i = 0; i < items.length; i++) {
            const j = (i + 1) % items.length;
            let diff = items[j].radDisp - items[i].radDisp;
            while (diff > Math.PI) diff -= 2 * Math.PI;
            while (diff < -Math.PI) diff += 2 * Math.PI;

            if (Math.abs(diff) < sepMinima * 0.9 && items[i].livello === items[j].livello) {
                items[j].livello = (items[i].livello + 1) % 3;
            }
        }

        const deltaR = size * 0.045;
        items.forEach(it => {
            if (it.livello === 1) {
                it.rDisp = rPia + deltaR;
            } else if (it.livello === 2) {
                it.rDisp = rPia - deltaR;
            }
        });

        const mappaFinale = {};
        items.forEach(it => {
            mappaFinale[it.id] = { rad: it.radDisp, r: it.rDisp };
        });
        return mappaFinale;
    },

    _disegnaLineePianetiModificato: function(svg, cx, cy, rZodI, tema, mappaDisplay) {
        if (!tema.pianeti) return;
        const mcLon = (tema.case && tema.case.MC ? tema.case.MC.longitudine : 0);

        Object.values(tema.pianeti).forEach(p => {
            const colore = this._coloreSemantico(p, tema.case);
            const radVero = this._lon2rad(p.longitudine, mcLon);
            const infoDisp = mappaDisplay[p.id];
            const radDisplay = infoDisp ? infoDisp.rad : radVero;
            const rDisplay = infoDisp ? infoDisp.r : (cx * 2 * 0.505);

            const xGrado = cx + (rZodI + 1) * Math.cos(radVero);
            const yGrado = cy + (rZodI + 1) * Math.sin(radVero);
            const xGlifo = cx + rDisplay * Math.cos(radDisplay);
            const yGlifo = cy + rDisplay * Math.sin(radDisplay);

            const linea = this._createElement('line');
            linea.setAttribute('x1', xGrado); linea.setAttribute('y1', yGrado);
            linea.setAttribute('x2', xGlifo); linea.setAttribute('y2', yGlifo);
            linea.setAttribute('stroke', colore);
            linea.setAttribute('stroke-width', '0.7');
            linea.setAttribute('opacity', '0.45');
            svg.appendChild(linea);

            const dot = this._createElement('circle');
            dot.setAttribute('cx', xGrado); dot.setAttribute('cy', yGrado);
            dot.setAttribute('r', 1.8);
            dot.setAttribute('fill', colore);
            dot.setAttribute('opacity', '0.7');
            svg.appendChild(dot);
        });
    },

    _disegnaPianetiModificato: function(svg, cx, cy, rZodE, tema, mappaDisplay) {
        if (!tema.pianeti) return;
        const mcLon = (tema.case && tema.case.MC ? tema.case.MC.longitudine : 0);
        const size  = cx * 2;

        Object.values(tema.pianeti).forEach(p => {
            const colore     = this._coloreSemantico(p, tema.case);
            const glifo      = this.PIANETI_GLIFI[p.id]  || '?';
            const radVero    = this._lon2rad(p.longitudine, mcLon);
            const infoDisp   = mappaDisplay[p.id];
            const radDisplay = infoDisp ? infoDisp.rad : radVero;
            const rDisplay   = infoDisp ? infoDisp.r : (size * 0.505);

            const xZ1 = cx + (rZodE - 1) * Math.cos(radVero);
            const yZ1 = cy + (rZodE - 1) * Math.sin(radVero);
            const xZ2 = cx + (rZodE + 7) * Math.cos(radVero);
            const yZ2 = cy + (rZodE + 7) * Math.sin(radVero);
            svg.appendChild(this._line(xZ1, yZ1, xZ2, yZ2, colore, 1.0));

            const xP = cx + rDisplay * Math.cos(radDisplay);
            const yP = cy + rDisplay * Math.sin(radDisplay);

            svg.appendChild(this._text(xP, yP, glifo, {
                size:     size * 0.085,
                fill:     colore,
                anchor:   'middle',
                baseline: 'central',
                bold:     colore === '#00AA00',
                cls:      'simbolo-pianeta'
            }));

            if (p.posizione && p.posizione.gradi !== undefined) {
                const labelGradi = p.posizione.gradi + '°' + String(p.posizione.minuti).padStart(2, '0') + '′';
                const rLabel = rDisplay + size * 0.055;
                const xL = cx + rLabel * Math.cos(radDisplay);
                const yL = cy + rLabel * Math.sin(radDisplay);
                svg.appendChild(this._text(xL, yL, labelGradi, {
                    size:     size * 0.026,
                    fill:     colore,
                    anchor:   'middle',
                    baseline: 'central',
                    bold:     false,
                    cls:      'grado-pianeta'
                }));
            }

            if (p.retrogrado) {
                svg.appendChild(this._text(xP + size * 0.021, yP - size * 0.021, 'r', {
                    size:     size * 0.020,
                    fill:     colore,
                    anchor:   'start',
                    baseline: 'central',
                    bold:     false
                }));
            }
        });
    },

    _disegnaAngoli: function(svg, cx, cy, rZodE, rInt, tema) {
        if (!tema.case) return;
        const mcLon = (tema.case.MC ? tema.case.MC.longitudine : 0);
        const size  = cx * 2;

        const angoli = {
            'AS': tema.case.ASC  ? tema.case.ASC.longitudine  : null,
            'DS': tema.case[7]   ? tema.case[7].longitudine   : null,
            'MC': tema.case.MC   ? tema.case.MC.longitudine   : null,
            'FC': tema.case[4]   ? tema.case[4].longitudine   : null
        };

        for (const nome in angoli) {
            const lon = angoli[nome];
            if (lon === null) continue;
            const rad = this._lon2rad(lon, mcLon);
            const x1  = cx + rInt  * Math.cos(rad);
            const y1  = cy + rInt  * Math.sin(rad);
            const x2  = cx + (rZodE + 2) * Math.cos(rad);
            const y2  = cy + (rZodE + 2) * Math.sin(rad);
            svg.appendChild(this._line(x1, y1, x2, y2, '#2244AA', 1.5));

            const xT = cx + (rZodE + size * 0.055) * Math.cos(rad);
            const yT = cy + (rZodE + size * 0.055) * Math.sin(rad);
            svg.appendChild(this._text(xT, yT, nome, {
                size: size * 0.032, fill: '#2244AA',
                anchor: 'middle', baseline: 'central', bold: true
            }));
        }
    },

    _lon2rad: function(lon, mcLon) {
        const gradi = 270 - (lon - mcLon);
        return gradi * Math.PI / 180;
    },

    _diffAng: function(a, b) {
        let d = ((a - b) % 360 + 360) % 360;
        return d > 180 ? d - 360 : d;
    },

    _midAng: function(a, b) {
        let diff = ((b - a) % 360 + 360) % 360;
        return a + diff / 2;
    },

    _settore: function(cx, cy, rInt, rEst, lonI, lonF, refLon) {
        const r1  = this._lon2rad(lonI, refLon);
        const r2  = this._lon2rad(lonF, refLon);
        const x1e = cx + rEst * Math.cos(r1); const y1e = cy + rEst * Math.sin(r1);
        const x2e = cx + rEst * Math.cos(r2); const y2e = cy + rEst * Math.sin(r2);
        const x1i = cx + rInt * Math.cos(r1); const y1i = cy + rInt * Math.sin(r1);
        const x2i = cx + rInt * Math.cos(r2); const y2i = cy + rInt * Math.sin(r2);
        const p = this._createElement('path');
        p.setAttribute('d',
            'M ' + x1e + ' ' + y1e +
            ' A ' + rEst + ' ' + rEst + ' 0 0 0 ' + x2e + ' ' + y2e +
            ' L ' + x2i + ' ' + y2i +
            ' A ' + rInt + ' ' + rInt + ' 0 0 1 ' + x1i + ' ' + y1i + ' Z'
        );
        return p;
    },

    _createElement: function(tag) { return document.createElementNS('http://www.w3.org/2000/svg', tag); },
    _circle: function(cx, cy, r, fill, stroke, sw) {
        sw = sw !== undefined ? sw : 1;
        const c = this._createElement('circle');
        c.setAttribute('cx', cx); c.setAttribute('cy', cy); c.setAttribute('r', r); c.setAttribute('fill', fill);
        if (stroke) { c.setAttribute('stroke', stroke); c.setAttribute('stroke-width', sw); }
        return c;
    },

    _line: function(x1, y1, x2, y2, stroke, sw) {
        sw = sw !== undefined ? sw : 1;
        const l = this._createElement('line');
        l.setAttribute('x1', x1); l.setAttribute('y1', y1);
        l.setAttribute('x2', x2); l.setAttribute('y2', y2);
        l.setAttribute('stroke', stroke); l.setAttribute('stroke-width', sw);
        return l;
    },

    _text: function(x, y, content, opts) {
        opts = opts || {};
        const t = this._createElement('text');
        t.setAttribute('x', x); t.setAttribute('y', y);
        t.setAttribute('font-size', opts.size || 12);
        t.setAttribute('fill', opts.fill || '#000000');
        t.setAttribute('text-anchor', opts.anchor || 'middle');
        t.setAttribute('dominant-baseline', opts.baseline || 'auto');
        t.setAttribute('font-family', '"Segoe UI Symbol", "Apple Symbols", "Symbol", serif');
        if (opts.bold) t.setAttribute('font-weight', 'bold');
        if (opts.cls)  t.setAttribute('class', opts.cls);
        t.textContent = content;
        return t;
    },

    _disegnaCuspidiCase: function(svg, cx, cy, rCaseI, rCaseE, tema) {
        if (!tema.case) return;
        const mcLon = (tema.case.MC ? tema.case.MC.longitudine : 0);
        const size  = cx * 2;
        const rTesto = rCaseI + (rCaseE - rCaseI) * 0.30;

        for (let c = 1; c <= 12; c++) {
            if (!tema.case[c] || !tema.case[c].posizione) continue;
            const lonC       = tema.case[c].longitudine;
            const pos        = tema.case[c].posizione;
            const isAngulare = (c === 1 || c === 4 || c === 7 || c === 10);
            const labelCusp = pos.gradi + '°' + String(pos.minuti).padStart(2, '0') + '′';
            const radC = this._lon2rad(lonC + 3, mcLon);

            const xT = cx + rTesto * Math.cos(radC);
            const yT = cy + rTesto * Math.sin(radC);

            svg.appendChild(this._text(xT, yT, labelCusp, {
                size:     size * 0.020,
                fill:     isAngulare ? '#1A3A7A' : '#5566AA',
                anchor:   'middle',
                baseline: 'central',
                bold:     false,
                cls:      'grado-cuspide'
            }));
        }
    },

    _applicaStatoGradi: function() {
        document.querySelectorAll('.grado-pianeta').forEach(el => {
            el.classList.toggle('is-hidden', !this._gradiVisibili);
        });
    },

    toggleGradi: function() {
        this._gradiVisibili = !this._gradiVisibili;
        this._applicaStatoGradi();
        const btn = document.getElementById('btn-toggle-gradi');
        if (btn) {
            btn.textContent = this._gradiVisibili ? 'Nascondi Gradi' : 'Mostra Gradi';
            btn.classList.toggle('attivo', this._gradiVisibili);
        }
    },

    _applicaStatoCuspidi: function() {
        document.querySelectorAll('.grado-cuspide').forEach(el => {
            el.classList.toggle('is-hidden', !this._cuspidiVisibili);
        });
    },

    toggleCuspidi: function() {
        this._cuspidiVisibili = !this._cuspidiVisibili;
        this._applicaStatoCuspidi();
        const btn = document.getElementById('btn-toggle-cuspidi');
        if (btn) {
            btn.textContent = this._cuspidiVisibili ? 'Nascondi Cuspidi' : 'Mostra Cuspidi';
            btn.classList.toggle('attivo', !this._cuspidiVisibili);
        }
    }
};

window.toggleGradiPianeti = function() { ZodiacWheel.toggleGradi(); };
window.toggleCuspidiCase = function() { ZodiacWheel.toggleCuspidi(); };