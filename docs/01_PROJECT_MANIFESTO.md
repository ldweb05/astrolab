# Astro-Val — Project Manifesto

Versione: 1.0
Stato: Authoritative

## Cos'è Astro-Val

Astro-Val è un software professionale per l'interpretazione simbolica
della Rivoluzione Solare secondo l'Astrologia Attiva.

Non è un oroscopo e non produce previsioni certe.

## Obiettivo

Trasformare dati astronomici in una relazione professionale,
deterministica, spiegabile e verificabile.

## Pipeline ufficiale

SweCalc → PlanetResolver → PlanetConditionEngine → AARuleEngine
→ EvidenceEngine → ThemeEngine → NarrativeEngine
→ AnnualReportGenerator → API JSON

La pipeline è unidirezionale. È vietato saltare layer.

## Principi obbligatori

- Domain First
- Single Responsibility
- Single Source of Truth
- Determinismo
- Explainability end-to-end
- Linguaggio probabilistico
- Regressione obbligatoria
- Documentazione continua

## Single Source of Truth

- PlanetResolver: nomi planetari
- PlanetConditionEngine: condizioni
- Atlas: configurazioni, temi e pesi astrologici
- Rule Engine: comportamento ed explainability
- Theme Engine: aggregazione
- Narrative Engine: linguaggio naturale
- Annual Report Generator: struttura editoriale

Le Rule non devono duplicare la conoscenza contenuta nell'Atlas.

## Metodo di lavoro

Ogni milestone segue questo ordine:

1. documentazione;
2. roadmap e timeline;
3. implementazione;
4. test specifici;
5. regressione completa;
6. aggiornamento di coverage e backlog;
7. commit Git.

Una milestone non è conclusa se codice, test e documentazione
non sono coerenti.

## Documenti principali

- 02_ASTROLOGY.md
- 03_ARCHITECTURE.md
- 05_NARRATIVE.md
- 10_THEME_ENGINE.md
- 11_ANNUAL_REPORT_SPEC.md
- ROADMAP.md
- status/KNOWLEDGE_COVERAGE.md
- status/RULE_BACKLOG.md

## Stato V4.2

- Architettura V3 completata
- Explainability completa
- RuleRegistry automatico
- 10 Rule su 120 configurazioni
- 2 Composite Rule
- Knowledge Coverage: 8,3%
- Espansione del dominio in corso

## Timeline

### 2026-07 — V3

Architettura consolidata e regressione stabile.

### 2026-07 — V4.1

Introdotti roadmap, coverage, backlog e ADR-001.

### 2026-07 — V4.2

Avviata la costruzione degli strumenti per generare,
validare e documentare le nuove Rule.



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
