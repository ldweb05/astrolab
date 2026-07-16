# Astro-Val Roadmap

Documento di avanzamento del progetto.
Da mantenere aggiornato a ogni milestone.

---

# Visione

Trasformare dati astronomici in una relazione professionale,
deterministica, spiegabile e conforme all'Astrologia Attiva di
Ciro Discepolo.

---

# Stato attuale

Data: 2026-07-16

Versione stabile:
V6 — tag `v6.0.0`

Versione di lavoro:
V6.1

Branch di sviluppo:
`feature/v6.1`

Architettura:
✅ Completata e stabile

Rule Engine:
✅ 120/120 Rule
✅ Knowledge Coverage 100%
✅ FREEZE attivo

Explainability:
✅ End-to-End

Full Regression:
✅ Completa

---

# Timeline

## V1 — Motore astronomico

✅ Completata

- Swiss Ephemeris tramite PHP FFI;
- calcolo pianeti e case;
- Rivoluzioni Solari;
- Rivoluzioni Lunari.

---

## V2 — Base Interpretation

✅ Completata

---

## V3 — Forecast Engine

✅ Completata

- Planet Condition Engine;
- Evidence Engine;
- Theme Engine;
- Narrative Engine;
- Annual Report;
- explainability completa.

---

## V4 — Rule Engine

✅ Completata

- Rule Registry centralizzato;
- 120 Rule implementate;
- Knowledge Coverage 100%;
- Full Regression superata;
- Rule Engine congelato.

---

## V5 — Report Professionale

✅ Completata

- Report Annuale narrativo;
- Executive Summary;
- Theme Summary;
- Cross Dynamics;
- Conclusion;
- stampa browser;
- PDF Dompdf deterministico.

---

## V6 — Hardening e Release stabile

✅ Completata

- Hardening Suite;
- Release Check;
- RC1;
- RC2;
- backup e restore PostgreSQL;
- ambienti Development e Production separati;
- verifica browser e PDF;
- configurazione PHP consolidata;
- release stabile taggata `v6.0.0`.

---

## V6.1 — Consolidamento UX/UI

🚧 In corso

### Completato

- header responsive condiviso;
- menu hamburger mobile;
- sottomenu Rivoluzioni;
- navigazione utente integrata nel menu mobile;
- test desktop e iPhone;
- centralizzazione degli stili in `www/css/style.css`;
- rimozione degli inline style statici da:
  - `www/index.php`;
  - `www/tema.php`;
  - `www/rs.php`;
  - `www/rl.php`.

Commit principali:

- `2b471fa` — header responsive e menu mobile;
- `a824d80` — refactoring UI pagina soggetti;
- `fb22cd6` — refactoring UI Tema Natale;
- `8aa5b6c` — refactoring UI Rivoluzione Solare;
- `c411ee3` — refactoring UI Rivoluzione Lunare.

### Prossimi obiettivi

1. rifiniture UX/UI finali;
2. controllo degli stili dinamici JavaScript;
3. validazione completa desktop e mobile;
4. consolidamento del CSS condiviso;
5. aggiornamento finale della documentazione;
6. preparazione della release V6.1.

---

# Direttiva operativa permanente

L'architettura e il Rule Engine sono considerati stabili.

Il Rule Engine non deve essere riaperto salvo:

- bug documentati;
- incompatibilità tecniche;
- refactoring che non alterino il comportamento;
- decisione esplicita e documentata.

Ogni milestone deve aggiornare:

- `docs/README.md`;
- `docs/START_HERE.md`;
- `docs/ROADMAP.md`;
- `docs/HANDOVER_OPERATIVO.md`.

Ogni attività completata deve essere registrata cronologicamente in:

`docs/HANDOVER_OPERATIVO.md`
