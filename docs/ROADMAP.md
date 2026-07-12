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

