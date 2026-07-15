# Astro-Val Roadmap

Documento di avanzamento del progetto.
Da mantenere aggiornato ad ogni milestone.

---

# Visione

Obiettivo finale:

Trasformare dati astronomici in una relazione professionale,
deterministica, spiegabile e conforme all'Astrologia Attiva di
Ciro Discepolo.

---

# Stato attuale

Data: 2026-07

Versione dominio:
V4.1

Architettura:
✅ Completata

Explainability:
✅ End-to-End

Regression:
✅ Completa

---

# KPI PRINCIPALE

Knowledge Coverage

Rule Engine

Implementate:
10

Possibili:
120

Coverage:
8.3%

Composite Rules:
2

---

# Timeline

## V3

✅ Architettura completata

✅ PlanetConditionEngine

✅ Rule Engine modulare

✅ Evidence Engine

✅ Theme Engine

✅ Narrative Engine

✅ Annual Report

✅ Explainability completa

---

## V4.1

✅ Rule Registry automatico

✅ Knowledge Coverage

✅ Dashboard dominio

🚧 Espansione della conoscenza astrologica

---

## V4.2

□ RULE-0011

□ RULE-0012

□ RULE-0013

□ RULE-0014

□ RULE-0015

---

## V4.3

□ Composite Rules avanzate

□ Meta Theme

□ Dominant Theme evoluto

□ Confidence

□ Protection

□ Exposure

---

## V4.4

□ Narrative Engine conforme alla specifica

□ Continuità narrativa

□ Meta-narrativa

□ Sintesi probabilistica

---

## V4.5

□ Annual Report professionale

1300–2000 parole

Capitoli completi

PDF professionale

---

# Regola di lavoro

Ogni milestone deve aggiornare:

- docs/ROADMAP.md
- docs/status/KNOWLEDGE_COVERAGE.md
- documentazione normativa interessata
- regressione

Prima si aggiorna la documentazione.

Poi il codice.

Mai il contrario.



---

# Architectural Decision Record

## ADR-001

Versione:
V4.1

Titolo:

Atlas = Single Source of Truth

Stato:

Accettata

Motivazione:

Separare definitivamente:

- conoscenza astrologica
- comportamento del dominio

Conseguenza:

Le future RULE-0011 → RULE-0120 utilizzeranno
l'Atlas come unica base della conoscenza.



-------------------------------------------------------------------------------

# DIRETTIVA OPERATIVA PERMANENTE (V4.2)

A partire dalla versione V4.2 l'architettura di Astro-Val è considerata
STABILE.

Non devono più essere introdotte modifiche architetturali,
refactoring generali o riprogettazioni del dominio, salvo la correzione
di bug documentati.

L'obiettivo esclusivo del progetto diventa il completamento della base
di conoscenza astrologica fino alla copertura totale dell'Atlas.

Le attività consentite sono esclusivamente:

- implementazione delle nuove Rule;
- implementazione delle Composite Rule previste;
- aggiunta di test automatici;
- esecuzione della Full Regression;
- aggiornamento della documentazione;
- commit Git.

Ogni attività completata DEVE essere registrata nel file:

docs/HANDOVER_OPERATIVO.md

L'Handover rappresenta il diario ufficiale del progetto.

Ogni sviluppatore dovrà poter riprendere il lavoro leggendo solamente:

1. HANDOVER_OPERATIVO.md
2. ROADMAP.md
3. KNOWLEDGE_COVERAGE.md
4. RULE_BACKLOG.md

Non è richiesto reinterpretare l'architettura.

È richiesto esclusivamente proseguire il completamento del progetto.

-------------------------------------------------------------------------------

-------------------------------------------------------------------------------

# V5 — Consolidamento Report Professionale

## Stato corrente

Milestone completate:

- deduplicazione dei paragrafi narrativi finali;
- Executive Summary strutturato;
- Executive Summary esposto nell'Annual Report;
- sezione narrativa Executive Summary;
- sezione narrativa Theme Summary;
- rimozione del codice obsoleto da AnnualReportDraftBuilder;
- validazione automatica delle sezioni narrative duplicate.

## Risultato corrente

- Annual Report: 12 sezioni;
- lunghezza regressione corrente: circa 1.230 parole;
- Full Regression: OK;
- Rule Engine: 120/120, invariato e in FREEZE;
- Working Tree: CLEAN.

## Prossimi obiettivi V5

- consolidamento Cross Dynamics;
- miglioramento della conclusione;
- riduzione delle ripetizioni semantiche;
- qualità editoriale del PDF;
- UX del report;
- validazione con casi reali.

## PDF professionale

Completato:

- continuità editoriale e controllo delle interruzioni di pagina;
- mantenimento della pipeline SVG → Canvas → PNG base64 → Dompdf;
- integrazione del Report Annuale narrativo nel PDF nativo;
- rendering sicuro di nota metodologica e sezioni narrative;
- test dedicato del renderer HTML;
- Full Regression stabile.

Commit di riferimento:

`ee27d89` — `feat(pdf): include annual narrative report in native PDF`

## Consolidamento finale V5

Completato:

- Report Annuale integrato nel PDF nativo Dompdf;
- Report Annuale integrato nell’anteprima e nella stampa browser;
- sanitizzazione strutturale del payload di stampa;
- Narrative Style Engine consolidato e coperto da test;
- validazione narrativa su tre scenari deterministici;
- controllo sezioni duplicate;
- smoke test Dompdf;
- Full Regression stabile;
- Rule Engine invariato a 120/120 e in FREEZE.

Commit di riferimento:

- `6443d1c` — browser preview;
- `7937c3d` — sanitizzazione payload PDF;
- `b3b5796` — Narrative Style Engine;
- `3406077` — validazione multi-scenario.

Prossimo obiettivo:

- verifica visiva ed editoriale con casi operativi reali;
- rifinitura UX;
- preparazione V6 — Hardening e Release 1.0.

## V6 — Hardening e Release 1.0

### Stato corrente

Completato:

- suite unica `www/tests/run_v6_hardening.sh`;
- comando release `www/tests/run_v6_release_check.sh`;
- lint completo PHP e Bash;
- diagnostica PHP rigorosa;
- validazione ambiente PHP 8.3, FFI, libswe e Dompdf;
- validazione PostgreSQL 16;
- integrità Composer;
- sicurezza configurazione;
- divieto di backend astronomici esterni;
- baseline crittografica del Rule Engine;
- 120 Rule in stato FREEZE;
- contratto API autenticato e non autenticato;
- contratto JSON del Forecast Engine;
- determinismo del Report Annuale;
- schema stabile delle 12 sezioni;
- determinismo strutturale PDF tramite Canvas Dompdf;
- test su casi reali e case estreme;
- budget prestazionale del Forecast Engine;
- Full Regression stabile.

### Release check corrente

- durata complessiva: circa 36 secondi;
- timeout massimo: 180 secondi;
- esito: `V6 RELEASE CHECK OK`;
- Working Tree: CLEAN.

### Prossimi obiettivi

- controllo visuale manuale browser/PDF;
- validazione backup e ripristino PostgreSQL;
- verifica configurazione production;
- preparazione checklist Release Candidate 1.
