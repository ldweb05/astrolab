const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  HeadingLevel, AlignmentType, BorderStyle, WidthType, ShadingType,
  PageOrientation, LevelFormat
} = require('docx');
const fs = require('fs');

// ── Colori ──────────────────────────────────────────────────────────────────
const BLU     = '2C3E6B';
const BLU_CH  = 'E8F0FF';
const VERDE   = '1B5E20';
const VERDE_CH= 'E8F5E9';
const ARANCIO = 'BF360C';
const ARAN_CH = 'FFF3E0';
const ROSSO   = 'B71C1C';
const ROSSO_CH= 'FFEBEE';
const GRIGIO  = 'F5F0E8';
const GRIGIO_B= 'D0C8BC';
const NERO    = '2C2C2C';

const border1 = { style: BorderStyle.SINGLE, size: 1, color: GRIGIO_B };
const borders = { top: border1, bottom: border1, left: border1, right: border1 };
const noBorder = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };
const noBorders = { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder };

// ── Helpers ──────────────────────────────────────────────────────────────────
function h1(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    spacing: { before: 320, after: 120 },
    children: [new TextRun({ text, bold: true, size: 28, color: BLU, font: 'Arial' })]
  });
}

function h2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    spacing: { before: 240, after: 80 },
    children: [new TextRun({ text, bold: true, size: 24, color: BLU, font: 'Arial' })]
  });
}

function h3(text) {
  return new Paragraph({
    spacing: { before: 160, after: 60 },
    children: [new TextRun({ text, bold: true, size: 22, color: NERO, font: 'Arial' })]
  });
}

function p(text, opts = {}) {
  return new Paragraph({
    spacing: { before: 60, after: 60 },
    children: [new TextRun({
      text,
      size: opts.size || 20,
      color: opts.color || NERO,
      bold: opts.bold || false,
      italics: opts.italic || false,
      font: 'Arial'
    })]
  });
}

function bullet(text, level = 0) {
  return new Paragraph({
    numbering: { reference: 'bullets', level },
    spacing: { before: 40, after: 40 },
    children: [new TextRun({ text, size: 20, color: NERO, font: 'Arial' })]
  });
}

function hr() {
  return new Paragraph({
    spacing: { before: 120, after: 120 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: GRIGIO_B, space: 1 } },
    children: []
  });
}

function cell(text, opts = {}) {
  return new TableCell({
    borders: opts.noBorder ? noBorders : borders,
    width: opts.width ? { size: opts.width, type: WidthType.DXA } : undefined,
    shading: opts.bg ? { fill: opts.bg, type: ShadingType.CLEAR } : undefined,
    margins: { top: 80, bottom: 80, left: 120, right: 120 },
    children: [new Paragraph({
      alignment: opts.center ? AlignmentType.CENTER : AlignmentType.LEFT,
      children: [new TextRun({
        text,
        bold: opts.bold || false,
        size: opts.size || 18,
        color: opts.color || NERO,
        font: 'Arial'
      })]
    })]
  });
}

function row(...cells) {
  return new TableRow({ children: cells });
}

function tableW(rows, colWidths) {
  return new Table({
    width: { size: colWidths.reduce((a,b) => a+b, 0), type: WidthType.DXA },
    columnWidths: colWidths,
    rows
  });
}

// ── DOCUMENTO ─────────────────────────────────────────────────────────────────
const doc = new Document({
  numbering: {
    config: [
      { reference: 'bullets', levels: [
        { level: 0, format: LevelFormat.BULLET, text: '•',
          alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 480, hanging: 240 } } } },
        { level: 1, format: LevelFormat.BULLET, text: '◦',
          alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 840, hanging: 240 } } } },
      ]},
      { reference: 'numbers', levels: [
        { level: 0, format: LevelFormat.DECIMAL, text: '%1.',
          alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 480, hanging: 240 } } } },
      ]}
    ]
  },
  styles: {
    default: { document: { run: { font: 'Arial', size: 20, color: NERO } } },
    paragraphStyles: [
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 28, bold: true, font: 'Arial', color: BLU },
        paragraph: { spacing: { before: 320, after: 120 }, outlineLevel: 0 } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 24, bold: true, font: 'Arial', color: BLU },
        paragraph: { spacing: { before: 240, after: 80 }, outlineLevel: 1 } },
    ]
  },
  sections: [{
    properties: {
      page: {
        size: { width: 11906, height: 16838 },
        margin: { top: 1134, right: 1134, bottom: 1134, left: 1134 }
      }
    },
    children: [

      // ── COPERTINA ────────────────────────────────────────────────────────
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 480, after: 120 },
        children: [new TextRun({ text: '☉ Astrologia Attiva', bold: true, size: 40, color: BLU, font: 'Arial' })]
      }),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 80 },
        children: [new TextRun({ text: 'Software Rivoluzioni Solari Mirate', size: 28, color: '667788', font: 'Arial' })]
      }),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 480 },
        children: [new TextRun({ text: 'Project Overview — Architettura, Stato e Roadmap', size: 22, color: '999999', italics: true, font: 'Arial' })]
      }),
      hr(),

      // ── 1. OVERVIEW ARCHITETTURA ─────────────────────────────────────────
      h1('1. Overview Architettura'),
      p('Il progetto è un\'applicazione web astrologica che gira su un Raspberry Pi 4 (sviluppo) con deployment finale su VPS Hetzner. Il sistema è interamente containerizzato via Docker e non richiede installazioni sul sistema host.'),
      new Paragraph({ spacing: { before: 120, after: 80 }, children: [] }),

      h2('Stack tecnologico'),
      tableW([
        row(
          cell('Livello', { bold: true, bg: BLU, color: 'FFFFFF', width: 2200 }),
          cell('Tecnologia', { bold: true, bg: BLU, color: 'FFFFFF', width: 3000 }),
          cell('Versione', { bold: true, bg: BLU, color: 'FFFFFF', width: 1800 }),
          cell('Note', { bold: true, bg: BLU, color: 'FFFFFF', width: 2700 })
        ),
        row(
          cell('Web Server', { bg: BLU_CH, bold: true, width: 2200 }),
          cell('Apache 2.4.67', { width: 3000 }),
          cell('2.4.67', { width: 1800 }),
          cell('Container Docker php:8.3-apache', { width: 2700 })
        ),
        row(
          cell('Backend', { bg: BLU_CH, bold: true, width: 2200 }),
          cell('PHP 8.3', { width: 3000 }),
          cell('8.3.31', { width: 1800 }),
          cell('Con estensioni PDO, pgsql, zip', { width: 2700 })
        ),
        row(
          cell('Database', { bg: BLU_CH, bold: true, width: 2200 }),
          cell('PostgreSQL 16', { width: 3000 }),
          cell('16.10', { width: 1800 }),
          cell('Container Docker postgres:16-alpine', { width: 2700 })
        ),
        row(
          cell('Calcoli astronomici', { bg: BLU_CH, bold: true, width: 2200 }),
          cell('Swiss Ephemeris v2.10.03', { width: 3000 }),
          cell('2.10.03', { width: 1800 }),
          cell('Compilato da sorgente, licenza AGPL', { width: 2700 })
        ),
        row(
          cell('Frontend', { bg: BLU_CH, bold: true, width: 2200 }),
          cell('JavaScript ES6 + SVG', { width: 3000 }),
          cell('—', { width: 1800 }),
          cell('No framework, vanilla JS', { width: 2700 })
        ),
        row(
          cell('Mappe', { bg: BLU_CH, bold: true, width: 2200 }),
          cell('OpenStreetMap + Nominatim', { width: 3000 }),
          cell('—', { width: 1800 }),
          cell('Geocoding gratuito per uso personale', { width: 2700 })
        ),
        row(
          cell('Infrastruttura', { bg: BLU_CH, bold: true, width: 2200 }),
          cell('Docker Compose', { width: 3000 }),
          cell('—', { width: 1800 }),
          cell('2 container: astro-web + astro-db', { width: 2700 })
        ),
      ], [2200, 3000, 1800, 2700]),

      new Paragraph({ spacing: { before: 160, after: 80 }, children: [] }),

      h2('Architettura container'),
      p('Il sistema usa Docker Compose con due container isolati in una rete privata astro-net:'),
      bullet('astro-web — Apache + PHP 8.3 + Swiss Ephemeris, porta 8080 (sviluppo) / 80 (produzione)'),
      bullet('astro-db — PostgreSQL 16, porta 5433 interna (non esposta all\'esterno)'),
      p('I file del progetto sono in /home/lorenzo/astro/www/ e montati come volume nel container, quindi le modifiche sono immediate senza rebuild.'),

      hr(),

      // ── 2. MAPPA FILE ────────────────────────────────────────────────────
      h1('2. Mappa File e Responsabilità'),

      h2('Struttura cartelle'),
      tableW([
        row(
          cell('Percorso', { bold: true, bg: BLU, color: 'FFFFFF', width: 3200 }),
          cell('Responsabilità', { bold: true, bg: BLU, color: 'FFFFFF', width: 6500 })
        ),
        row(cell('www/', { bold: true, bg: GRIGIO, width: 3200 }), cell('Root del web server — file accessibili via browser', { width: 6500 })),
        row(cell('www/index.php', { width: 3200 }), cell('Pagina principale — gestione soggetti (lista, inserimento, modifica, elimina)', { width: 6500 })),
        row(cell('www/tema.php', { width: 3200 }), cell('Visualizzazione tema natale con ruota zodiacale SVG e tabelle pianeti/case', { width: 6500 })),
        row(cell('www/rs.php', { width: 3200 }), cell('Rivoluzione Solare — doppio tema affiancato, valutazione 34 regole, selezione luogo', { width: 6500 })),
        row(cell('www/ricerca.php', { width: 3200 }), cell('[TODO] Ricerca massiva su database aeroporti con ranking stelline', { width: 6500 })),
        row(cell('www/includes/', { bold: true, bg: GRIGIO, width: 3200 }), cell('Classi PHP backend — non accessibili direttamente via browser', { width: 6500 })),
        row(cell('www/includes/SweCalc.php', { width: 3200 }), cell('Wrapper Swiss Ephemeris: calcola pianeti, case Placido, RS, RL, conversioni Julian Day', { width: 6500 })),
        row(cell('www/includes/RuleEngine.php', { width: 3200 }), cell('Motore 34 regole Discepolo: valuta RS/RL, calcola stelline, genera stringa VAL, testi interpretativi', { width: 6500 })),
        row(cell('www/includes/search_engine.php', { width: 3200 }), cell('Motore di ricerca batch su aeroporti: calcola RS per ogni aeroporto e ordina per stelline', { width: 6500 })),
        row(cell('www/api/', { bold: true, bg: GRIGIO, width: 3200 }), cell('Endpoint API JSON chiamati dal frontend JavaScript', { width: 6500 })),
        row(cell('www/api/tema.php', { width: 3200 }), cell('API: calcola tema natale o RS per coordinate/data e restituisce JSON', { width: 6500 })),
        row(cell('www/api/rs.php', { width: 3200 }), cell('API: calcola RS completa con valutazione RuleEngine e restituisce JSON', { width: 6500 })),
        row(cell('www/api/soggetti.php', { width: 3200 }), cell('API CRUD soggetti: lista, get, inserisci, modifica, elimina', { width: 6500 })),
        row(cell('www/js/', { bold: true, bg: GRIGIO, width: 3200 }), cell('Codice JavaScript frontend', { width: 6500 })),
        row(cell('www/js/zodiac_wheel.js', { width: 3200 }), cell('Rendering SVG ruota zodiacale: segni, case Placido, pianeti esterni, angoli AS/DS/MC/FC', { width: 6500 })),
        row(cell('www/js/app.js', { width: 3200 }), cell('Logica frontend: CRUD soggetti, geocoding OSM, calcolo ora GMT, messaggi UI', { width: 6500 })),
        row(cell('www/css/', { bold: true, bg: GRIGIO, width: 3200 }), cell('Fogli di stile', { width: 6500 })),
        row(cell('www/css/style.css', { width: 3200 }), cell('Stile globale: layout, header, form, tabelle, bottoni, messaggi, tema crema/blu', { width: 6500 })),
      ], [3200, 6500]),

      new Paragraph({ spacing: { before: 160, after: 80 }, children: [] }),

      h2('Database PostgreSQL — Tabelle'),
      tableW([
        row(
          cell('Tabella', { bold: true, bg: BLU, color: 'FFFFFF', width: 2500 }),
          cell('Contenuto', { bold: true, bg: BLU, color: 'FFFFFF', width: 7200 })
        ),
        row(cell('soggetti', { bg: BLU_CH, bold: true, width: 2500 }), cell('Anagrafica soggetti: nome, data/ora nascita, luogo, coordinate, timezone, offset GMT', { width: 7200 })),
        row(cell('aeroporti', { bg: BLU_CH, bold: true, width: 2500 }), cell('84.616 aeroporti mondiali (OurAirports.com): IATA, ICAO, nome, città, nazione, lat/lon, tipo, militare', { width: 7200 })),
        row(cell('sessioni_rs', { bg: BLU_CH, bold: true, width: 2500 }), cell('Rivoluzioni Solari salvate per soggetto: anno, data GMT, luogo RS, condizione, stelline, VAL', { width: 7200 })),
        row(cell('sessioni_rl', { bg: BLU_CH, bold: true, width: 2500 }), cell('Rivoluzioni Lunari salvate: mese, data GMT, luogo RL, condizione, stelline, VAL, collegamento a RS', { width: 7200 })),
        row(cell('preferiti', { bg: BLU_CH, bold: true, width: 2500 }), cell('Luoghi preferiti per soggetto: nome, coordinate, note', { width: 7200 })),
        row(cell('log_calcoli', { bg: BLU_CH, bold: true, width: 2500 }), cell('Log debug calcoli: parametri, risultati JSON, durata ms — per validazione e ottimizzazione', { width: 7200 })),
      ], [2500, 7200]),

      hr(),

      // ── 3. FLUSSO DATI ───────────────────────────────────────────────────
      h1('3. Flusso Dati Completo'),

      h2('Calcolo Rivoluzione Solare — Input → Output'),

      h3('Step 1: Input utente'),
      bullet('Soggetto selezionato dal database (dati natali: data, ora GMT, lat/lon)'),
      bullet('Anno RS scelto'),
      bullet('Luogo RS scelto (coordinate lat/lon via geocoding OSM o mappa)'),
      bullet('Condizione tematica selezionata (Decima, Amore, Salute, ecc.)'),

      h3('Step 2: Calcolo astronomico (SweCalc.php)'),
      bullet('Calcola longitudine solare natale via swetest (Swiss Ephemeris)'),
      bullet('Ricerca iterativa per bisezione del momento esatto RS (±0.0001°) — circa 50 iterazioni'),
      bullet('Calcola posizioni di tutti i pianeti al momento RS per le coordinate del luogo RS'),
      bullet('Calcola cuspidi 12 case sistema Placido per lat/lon del luogo RS'),
      bullet('Assegna ogni pianeta alla sua casa RS'),

      h3('Step 3: Valutazione 34 regole (RuleEngine.php)'),
      bullet('Fase 1 — Veti assoluti: controlla ASC RS in I/VI/XII natale, Marte in I/VI/XII, stellium, angoli, latitudine'),
      bullet('Se scatta un veto: 0 stelle, destinazione non valida, termina'),
      bullet('Fase 2 — Punteggio: applica matrice pianeta×casa con pesi e moltiplicatori condizione tematica'),
      bullet('Fase 3 — Filtri Astri in Casa (personalizzati)'),
      bullet('Fase 4 — Stelline per sottrazione: parte da 5, scende per Marte/Saturno/Urano in case sensibili'),
      bullet('Fase 5 — Genera stringa VAL: stelline + pianeti notevoli con casa (es. ****/MA7/PLU1/SO8)'),

      h3('Step 4: Output'),
      bullet('JSON con: tema natale, tema RS (pianeti+case), valutazione (stelline, VAL, bonus, penalità, note, veti)'),
      bullet('Frontend JS: disegna due ruote SVG affiancate, mostra tabelle pianeti, mostra valutazione con colori'),

      new Paragraph({ spacing: { before: 120, after: 80 }, children: [] }),

      h2('Calcolo Rivoluzione Lunare — differenze vs RS'),
      bullet('Invece del Sole, si cerca il momento in cui la Luna torna al grado natale'),
      bullet('Periodo di ricerca ~27.3 giorni invece di ~365 giorni'),
      bullet('Stesse 34 regole, stesso RuleEngine, stessa stringa VAL'),
      bullet('Visualizzazione default: luogo della RS attiva per quell\'anno'),
      bullet('Ricerca on-demand: stesso motore della RS con filtro distanza'),

      hr(),

      // ── 4. STATO FUNZIONALITÀ ────────────────────────────────────────────
      h1('4. Stato Funzionalità'),

      tableW([
        row(
          cell('Funzionalità', { bold: true, bg: BLU, color: 'FFFFFF', width: 3500 }),
          cell('Stato', { bold: true, bg: BLU, color: 'FFFFFF', width: 1500 }),
          cell('Note', { bold: true, bg: BLU, color: 'FFFFFF', width: 4700 })
        ),
        // COMPLETATE
        row(cell('Motore calcolo Swiss Ephemeris', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Validato contro Astro.com e programma Discepolo. Differenza <32 sec sul momento RS', { width: 4700 })),
        row(cell('Tema natale completo', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Pianeti + case Placido. Coincidenza al secondo d\'arco con Discepolo e Astro.com', { width: 4700 })),
        row(cell('Calcolo RS — momento esatto GMT', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Validato su 2 soggetti reali con RSM a Napoli, Athens GA, Lanzarote', { width: 4700 })),
        row(cell('Calcolo RL — momento esatto GMT', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Algoritmo implementato, da testare su casi reali', { width: 4700 })),
        row(cell('RuleEngine — 34 regole Discepolo', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Validato su Lanzarote: 4 stelle, VAL ****/MA7/PLU1/SO8/GI7 = identico ad Aladino', { width: 4700 })),
        row(cell('Stelline per sottrazione', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Logica calibrata su casi reali. Filosofia documentata per file HELP futuro', { width: 4700 })),
        row(cell('Database aeroporti', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('84.616 aeroporti da OurAirports.com importati in PostgreSQL con indici', { width: 4700 })),
        row(cell('Motore ricerca batch aeroporti', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Testato su aeroporti ES/PT/MA/CV/MR. Risultati coerenti con Aladino', { width: 4700 })),
        row(cell('Ruota zodiacale SVG', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Tema chiaro, MC in alto, pianeti esterni, segni colorati, angoli AS/DS/MC/FC', { width: 4700 })),
        row(cell('Gestione soggetti (CRUD)', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Inserimento, modifica, elimina. Geocoding via Nominatim OSM. Calcolo ora GMT automatico', { width: 4700 })),
        row(cell('Pagina tema natale', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Ruota SVG + tabella pianeti + tabella case. Header informativo', { width: 4700 })),
        row(cell('Pagina RS con valutazione', { width: 3500 }), cell('✅ FATTO', { bg: VERDE_CH, bold: true, color: VERDE, width: 1500 }), cell('Doppio tema affiancato, selezione anno/luogo/condizione, stelline, VAL, bonus/penalità', { width: 4700 })),
        // IN SVILUPPO
        row(cell('Pagina ricerca località', { width: 3500 }), cell('🔄 IN CORSO', { bg: ARAN_CH, bold: true, color: ARANCIO, width: 1500 }), cell('SearchEngine implementato e testato. Manca interfaccia grafica con lista risultati', { width: 4700 })),
        // TODO
        row(cell('Mappa OSM interattiva (Mondo Virtuale)', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Leaflet.js + aggiornamento RS in tempo reale al click sulla mappa', { width: 4700 })),
        row(cell('Rivoluzioni Lunari — interfaccia', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Menu RL con 12 rivoluzioni annuali, visualizzazione immediata, ricerca on-demand', { width: 4700 })),
        row(cell('Aspetti nel grafico', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Linee aspetti dentro cerchio interno (trigoni verde, quadrati rosso, ecc.)', { width: 4700 })),
        row(cell('Gradi pianeti nella cintura', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Posizione in gradi/minuti nella cintura zodiacale come in Discepolo', { width: 4700 })),
        row(cell('Filtro Astri in Casa', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Popup selezione pianeti/ASC/MC da volere o evitare in case specifiche', { width: 4700 })),
        row(cell('Salvataggio sessioni RS/RL in DB', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Memorizzazione RS/RL per soggetto con possibilità di richiamarle', { width: 4700 })),
        row(cell('Export PDF', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Stampa doppio tema su A4/A3', { width: 4700 })),
        row(cell('Transiti su tema natale', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Fase 9 — funzione avanzata', { width: 4700 })),
        row(cell('Progressioni secondarie', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Fase 9 — funzione avanzata', { width: 4700 })),
        row(cell('Sinastria e tema composito', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Fase 9 — funzione avanzata', { width: 4700 })),
        row(cell('File HELP con filosofia stelline', { width: 3500 }), cell('⭕ TODO', { width: 1500 }), cell('Documentazione per utenti: stelline, VAL, regole Discepolo, condizioni tematiche', { width: 4700 })),
      ], [3500, 1500, 4700]),

      hr(),

      // ── 5. ROADMAP ───────────────────────────────────────────────────────
      h1('5. Roadmap Futura'),

      tableW([
        row(
          cell('Fase', { bold: true, bg: BLU, color: 'FFFFFF', width: 1200 }),
          cell('Titolo', { bold: true, bg: BLU, color: 'FFFFFF', width: 3000 }),
          cell('Stato', { bold: true, bg: BLU, color: 'FFFFFF', width: 1400 }),
          cell('Prossimi step', { bold: true, bg: BLU, color: 'FFFFFF', width: 4100 })
        ),
        row(cell('0', { bg: VERDE_CH, width: 1200 }), cell('Briefing e requisiti', { width: 3000 }), cell('✅ Completo', { bg: VERDE_CH, bold: true, color: VERDE, width: 1400 }), cell('34 regole codificate, matrice pianeta×casa, condizioni tematiche, filosofia RL', { width: 4100 })),
        row(cell('1', { bg: VERDE_CH, width: 1200 }), cell('Infrastruttura VPS', { width: 3000 }), cell('✅ Completo (Rasp.)', { bg: VERDE_CH, bold: true, color: VERDE, width: 1400 }), cell('Raspberry Pi operativo. VPS Hetzner da creare per produzione finale', { width: 4100 })),
        row(cell('2', { bg: VERDE_CH, width: 1200 }), cell('Database schema', { width: 3000 }), cell('✅ Completo', { bg: VERDE_CH, bold: true, color: VERDE, width: 1400 }), cell('6 tabelle create. Import aeroporti completato', { width: 4100 })),
        row(cell('3', { bg: VERDE_CH, width: 1200 }), cell('Motore calcolo', { width: 3000 }), cell('✅ Validato', { bg: VERDE_CH, bold: true, color: VERDE, width: 1400 }), cell('Validato su casi reali contro Discepolo e Astro.com', { width: 4100 })),
        row(cell('4', { bg: VERDE_CH, width: 1200 }), cell('34 regole Discepolo', { width: 3000 }), cell('✅ Validato', { bg: VERDE_CH, bold: true, color: VERDE, width: 1400 }), cell('Stelline calibrate su casi reali. VAL identica ad Aladino', { width: 4100 })),
        row(cell('5', { bg: ARAN_CH, width: 1200 }), cell('Ricerca località', { width: 3000 }), cell('🔄 In corso', { bg: ARAN_CH, bold: true, color: ARANCIO, width: 1400 }), cell('Costruire ricerca.php con lista risultati, filtri, mappa OSM', { width: 4100 })),
        row(cell('6', { bg: ARAN_CH, width: 1200 }), cell('Grafica ruote', { width: 3000 }), cell('🔄 In corso', { bg: ARAN_CH, bold: true, color: ARANCIO, width: 1400 }), cell('Aggiungere aspetti, gradi pianeti, doppio tema RS con header completo', { width: 4100 })),
        row(cell('7', { width: 1200 }), cell('Mappa OSM interattiva', { width: 3000 }), cell('⭕ TODO', { width: 1400 }), cell('Leaflet.js, click → ricalcola RS in tempo reale, marker con popup VAL', { width: 4100 })),
        row(cell('8', { width: 1200 }), cell('Interfaccia completa', { width: 3000 }), cell('⭕ TODO', { width: 1400 }), cell('RL con menu 12 rivoluzioni, filtro Astri in Casa, preferiti, salvataggio sessioni', { width: 4100 })),
        row(cell('9', { width: 1200 }), cell('Export e funzioni avanzate', { width: 3000 }), cell('⭕ TODO', { width: 1400 }), cell('PDF stampa, transiti, progressioni, sinastria, file HELP', { width: 4100 })),
        row(cell('10', { width: 1200 }), cell('Validazione finale e go-live', { width: 3000 }), cell('⭕ TODO', { width: 1400 }), cell('Confronto sistematico con Aladino su 4+ soggetti. Migrazione su VPS Hetzner', { width: 4100 })),
      ], [1200, 3000, 1400, 4100]),

      new Paragraph({ spacing: { before: 160, after: 80 }, children: [] }),

      h2('Prossimi 3 step immediati'),
      new Paragraph({
        numbering: { reference: 'numbers', level: 0 },
        spacing: { before: 40, after: 40 },
        children: [new TextRun({ text: 'Costruire ricerca.php — interfaccia grafica per la ricerca massiva su aeroporti con lista risultati ordinata per stelline, filtri per nazione/tipo, colori per qualità', size: 20, font: 'Arial' })]
      }),
      new Paragraph({
        numbering: { reference: 'numbers', level: 0 },
        spacing: { before: 40, after: 40 },
        children: [new TextRun({ text: 'Aggiungere aspetti nella ruota SVG — linee interne colorate (trigono verde, quadrato/opposizione rosso, sestile blu)', size: 20, font: 'Arial' })]
      }),
      new Paragraph({
        numbering: { reference: 'numbers', level: 0 },
        spacing: { before: 40, after: 40 },
        children: [new TextRun({ text: 'Integrare Leaflet.js per la mappa OSM interattiva nella pagina RS — click sulla mappa → ricalcola in tempo reale', size: 20, font: 'Arial' })]
      }),

      hr(),

      // ── 6. RISCHI TECNICI ────────────────────────────────────────────────
      h1('6. Punti Critici e Rischi Tecnici'),

      tableW([
        row(
          cell('Rischio', { bold: true, bg: BLU, color: 'FFFFFF', width: 2800 }),
          cell('Livello', { bold: true, bg: BLU, color: 'FFFFFF', width: 1200 }),
          cell('Mitigazione', { bold: true, bg: BLU, color: 'FFFFFF', width: 5700 })
        ),
        row(
          cell('Timezone storici errati', { width: 2800 }),
          cell('⚠️ Alto', { bg: ARAN_CH, bold: true, color: ARANCIO, width: 1200 }),
          cell('Gestione manuale offset GMT nel form soggetti. Per produzione: integrare database IANA timezone storici. Esempio: Italia 1960 = UTC+1 (no ora legale)', { width: 5700 })
        ),
        row(
          cell('Performance ricerca batch (70k aeroporti)', { width: 2800 }),
          cell('⚠️ Alto', { bg: ARAN_CH, bold: true, color: ARANCIO, width: 1200 }),
          cell('Implementare calcolo asincrono con progress bar (AJAX streaming). Filtrare per tipo (large/medium) per default. Cache risultati RS per lo stesso momento GMT', { width: 5700 })
        ),
        row(
          cell('Swiss Ephemeris — file effemeridi nel container', { width: 2800 }),
          cell('⚠️ Medio', { bg: ARAN_CH, bold: true, color: ARANCIO, width: 1200 }),
          cell('I file .se1 sono scaricati al build del container. Se il container viene ricostruito senza cache, si riscaricano. Soluzione: volume Docker per /opt/swisseph/ephe', { width: 5700 })
        ),
        row(
          cell('Validazione calcoli RL su casi reali', { width: 2800 }),
          cell('⚠️ Medio', { bg: ARAN_CH, bold: true, color: ARANCIO, width: 1200 }),
          cell('L\'algoritmo RL è implementato ma non ancora validato sistematicamente come la RS. Validare con programma Aladino prima del go-live', { width: 5700 })
        ),
        row(
          cell('Geocoding Nominatim — limiti API', { width: 2800 }),
          cell('ℹ️ Basso', { bg: BLU_CH, width: 1200 }),
          cell('Nominatim ha rate limit per uso personale (1 req/sec). Sufficiente per 2-3 utenti. Non usare per ricerche batch su aeroporti', { width: 5700 })
        ),
        row(
          cell('Backup dati PostgreSQL', { width: 2800 }),
          cell('ℹ️ Basso', { bg: BLU_CH, width: 1200 }),
          cell('Volume Docker postgres-data persiste tra restart. Aggiungere backup automatico pg_dump su cron. Su VPS Hetzner: snapshot automatici attivi', { width: 5700 })
        ),
        row(
          cell('Migrazione Raspberry → Hetzner VPS', { width: 2800 }),
          cell('ℹ️ Basso', { bg: BLU_CH, width: 1200 }),
          cell('Docker Compose identico. Export DB con pg_dump → import su VPS. Stesso Dockerfile. Stimato: 2-3 ore di lavoro', { width: 5700 })
        ),
      ], [2800, 1200, 5700]),

      new Paragraph({ spacing: { before: 200, after: 80 }, children: [] }),

      h2('Note importanti sul RuleEngine'),
      p('Il motore implementa ESCLUSIVAMENTE le 34 regole della scuola di Ciro Discepolo. Nessuna regola personale influenza il punteggio stelline.', { bold: true }),
      bullet('La preferenza personale di separare due malefici vicini a una cuspide è implementata come nota visiva separata, NON come penalità nel punteggio'),
      bullet('La filosofia delle stelline (per sottrazione da 5) è documentata e differisce leggermente da Aladino per scelta consapevole'),
      bullet('La differenza di 1 stella su casi limite rispetto ad Aladino è accettabile — dipende da coordinate aeroporto leggermente diverse'),

      hr(),

      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 160, after: 0 },
        children: [new TextRun({
          text: 'Documento generato automaticamente — Progetto Astrologia Attiva — Uso personale/familiare',
          size: 16, color: '999999', italics: true, font: 'Arial'
        })]
      }),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 40, after: 0 },
        children: [new TextRun({
          text: 'Fonte regole: Ciro Discepolo, "Trattato dell\'Astrologia Attiva" — Swiss Ephemeris licenza AGPL',
          size: 16, color: '999999', italics: true, font: 'Arial'
        })]
      }),

    ]
  }]
});

Packer.toBuffer(doc).then(buf => {
  fs.writeFileSync('//home/lorenzo/astro/tools/progetto_astrologia_overview.docx', buf);
  console.log('Done');
});
