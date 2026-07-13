

# Regola permanente

Da questo punto in avanti ogni attività eseguita sul progetto deve essere
registrata cronologicamente in questo documento.

Ogni voce deve riportare almeno:

- data;
- componente modificato;
- obiettivo;
- risultato;
- test eseguiti;
- commit Git;
- passo successivo.

Questo documento costituisce il punto di ripartenza ufficiale per ogni
futuro sviluppatore.


# Astro-Val — Handover operativo

Ultimo aggiornamento: 2026-07-12

## Regola principale

L'architettura V3 è congelata.

Non introdurre:

- nuove varianti architetturali;
- nuovi layer;
- nuovi refactoring non necessari;
- nuovi generatori o strumenti di governance;
- modifiche speculative.

Da questo momento il progetto deve essere completato usando
l'architettura esistente.

## Pipeline congelata

SweCalc
→ PlanetResolver
→ PlanetConditionEngine
→ AARuleEngine
→ EvidenceEngine
→ ThemeEngine
→ NarrativeEngine
→ AnnualReportGenerator
→ API JSON

## Stato completato

- PlanetConditionEngine integrato.
- condition_id deterministici.
- Rule Engine modulare.
- RuleRegistry automatico.
- Evidence explainable.
- Composite Evidence operative.
- Theme ranking operativo.
- Annual Report con 10 sezioni.
- Explainability end-to-end.
- Evidence Trace.
- Rule Trace.
- Condition Trace.
- Theme Links.
- Frontend collegato ad annual_report.
- Regressione completa stabile.
- 10 Rule planetarie implementate.
- 2 Composite Rule implementate.
- 10 condizioni planetarie propagate.
- 34 evidenze nella regressione corrente.
- 15 temi nella regressione corrente.

## Rule implementate

- RULE-0001: Giove in X
- RULE-0002: Saturno in XII
- RULE-0003: Marte in VI
- RULE-0004: Venere in V
- RULE-0005: Mercurio in III
- RULE-0006: Luna in IV
- RULE-0007: Urano in XI
- RULE-0008: Nettuno in IX
- RULE-0009: Plutone in VIII
- RULE-0010: Sole in X

## Composite implementate

- Sole + Giove nella stessa casa.
- Marte in VI + Saturno in XII.

## Modifiche documentali già eseguite

- Creato docs/ROADMAP.md.
- Creato docs/01_PROJECT_MANIFESTO.md.
- Creato docs/ADR_INDEX.md.
- Creato docs/status/KNOWLEDGE_COVERAGE.md.
- Creato docs/status/RULE_BACKLOG.md.
- Documentato Atlas come Single Source of Truth.
- Documentata la procedura di aggiornamento continuo.

## Modifiche tecniche recenti

- RuleRegistry usa discovery automatico delle classi Rule.
- AARuleEngine::evidence() supporta metadata V4 opzionali:
  secondary_themes, polarity, confidence, summary, requirements.
- ThemeProfileBuilder espone:
  protection, exposure, confidence, summary, metadata.
- Nessuna delle nuove chiavi deve rompere la compatibilità V3.

## File creati ma non prioritari

- www/tools/generate_rule.php

Il Rule Generator è un prototipo incompleto.

Non svilupparlo ulteriormente durante la fase di completamento.
Le Rule possono essere creato direttamente seguendo le classi esistenti.

## Prossimi passi obbligatori

1. Implementare RULE-0011: Sole in Casa I.
2. Aggiungere il test specifico per RULE-0011.
3. Eseguire la regressione completa.
4. Continuare con le Rule successive in gruppi coerenti.
5. Completare protection, exposure, confidence e summary nel Theme Engine.
6. Completare il Narrative Engine usando esclusivamente Theme/Profile.
7. Portare l'Annual Report a 1.300–2.000 parole.
8. Verificare i capitoli obbligatori della specifica.
9. Consolidare API e PDF solo dopo il completamento del dominio e del report.

## Metodo operativo da seguire

Per ogni iterazione:

1. implementare una funzionalità concreta;
2. aggiungere o aggiornare il test;
3. eseguire la regressione;
4. aggiungere una breve nota nella timeline di questo documento;
5. passare immediatamente alla funzionalità successiva.

## Divieti

- Non leggere pianeti nel Narrative Engine.
- Non leggere pianeti nel Theme Engine.
- Non generare testo nel dominio.
- Non duplicare conoscenza astrologica.
- Non modificare moduli stabili senza un bug concreto.
- Non creare nuove versioni architetturali.
- Non interrompere lo sviluppo funzionale per introdurre nuovi strumenti.

## Punto esatto da cui continuare

Creare:

www/includes/forecast/rules/Rule0011_Sun1.php

La Rule deve:

- leggere solo planetConditions['sole'];
- verificare house === 1;
- preservare condition_id;
- usare AARuleEngine::evidence();
- produrre evidenze coerenti con SunAtlas Casa I;
- essere scoperta automaticamente dal RuleRegistry;
- avere un test dedicato;
- mantenere FULL REGRESSION OK.

## Timeline semplice

### 2026-07-12

- Architettura dichiarata congelata.
- Interrotti i refactoring non necessari.
- Avviata la fase finale di completamento funzionale.
- Prossimo lavoro: RULE-0011 Sole in Casa I.

### 2026-07-12 — RULE-0011

- Implementata RULE-0011: Sole in Casa I.
- Evidenze prodotte: identita, iniziative, salute.
- condition_id propagata correttamente.
- RuleRegistry: 11 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Prossimo passo: RULE-0012, Sole in Casa II.

### 2026-07-12 — Consolidamento repository

- Consolidate le modifiche documentali già completate.
- Consolidato Evidence Contract retrocompatibile.
- Consolidati i campi ThemeProfile già introdotti.
- Rimossi backup e prototipi temporanei non necessari.
- Architettura congelata: nessun ulteriore refactoring.
- Prossimo passo: RULE-0012, Sole in Casa II.\n\n### 2026-07-12 — RULE-0012

- Implementata RULE-0012: Sole in Casa II.
- Evidenze prodotte: denaro, patrimonio.
- condition_id propagata correttamente.
- RuleRegistry: 12 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0012 Sun House 2.
- Prossimo passo: RULE-0013, Sole in Casa III.\n

### 2026-07-12 — Direttiva operativa permanente

- Congelata definitivamente l'architettura attuale.
- Vietate nuove versioni, riprogettazioni e refactoring non necessari.
- Obiettivo esclusivo: completare il progetto con i contratti esistenti.
- Aggiornati tutti i documenti operativi e normativi.
- Reso obbligatorio registrare ogni passaggio in HANDOVER_OPERATIVO.md.
- Test richiesti: nessuno, modifica esclusivamente documentale.
- Commit: docs: enforce frozen architecture and mandatory handover.
- Prossimo passo: RULE-0013, Sole in Casa III.

### 2026-07-12 — RULE-0013

- Obiettivo: implementare Sole in Casa III.
- Creato: www/includes/forecast/rules/Rule0013_Sun3.php.
- Creato: www/tests/test_rule_0013.php.
- Evidenze: comunicazione, studio.
- condition_id propagata correttamente.
- RuleRegistry: 13 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0013 Sun House 3.
- Prossimo passo: RULE-0014, Sole in Casa IV.

### 2026-07-12 — RULE-0015

- Obiettivo: implementare Sole in Casa V.
- Creato: www/includes/forecast/rules/Rule0015_Sun5.php.
- Creato: www/tests/test_rule_0015.php.
- Evidenze: amore, creativita, figli.
- condition_id propagata correttamente.
- RuleRegistry: 15 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0015 Sun House 5.
- Prossimo passo: RULE-0016, Sole in Casa VI.

### 2026-07-12 — RULE-0016

- Obiettivo: implementare Sole in Casa VI.
- Creato: www/includes/forecast/rules/Rule0016_Sun6.php.
- Creato: www/tests/test_rule_0016.php.
- Evidenze: lavoro, salute.
- condition_id propagata correttamente.
- RuleRegistry: 16 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0016 Sun House 6.
- Prossimo passo: RULE-0017, Sole in Casa VII.

### 2026-07-12 — RULE-0017

- Obiettivo: implementare Sole in Casa VII.
- Creato: www/includes/forecast/rules/Rule0017_Sun7.php.
- Creato: www/tests/test_rule_0017.php.
- Evidenze: matrimonio, relazioni.
- condition_id propagata correttamente.
- RuleRegistry: 17 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0017 Sun House 7.
- Prossimo passo: RULE-0018, Sole in Casa VIII.

### 2026-07-12 — RULE-0018

- Obiettivo: implementare Sole in Casa VIII.
- Creato: www/includes/forecast/rules/Rule0018_Sun8.php.
- Creato: www/tests/test_rule_0018.php.
- Evidenze: prove, trasformazione.
- condition_id propagata correttamente.
- RuleRegistry: 18 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0018 Sun House 8.
- Prossimo passo: RULE-0019, Sole in Casa IX.

### 2026-07-12 — RULE-0019

- Obiettivo: implementare Sole in Casa IX.
- Creato: www/includes/forecast/rules/Rule0019_Sun9.php.
- Creato: www/tests/test_rule_0019.php.
- Evidenze: estero, studio, viaggi.
- condition_id propagata correttamente.
- RuleRegistry: 19 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0019 Sun House 9.
- Prossimo passo: RULE-0020, Sole in Casa XI.

### 2026-07-12 — RULE-0020

- Obiettivo: implementare Sole in Casa XI.
- Creato: www/includes/forecast/rules/Rule0020_Sun11.php.
- Creato: www/tests/test_rule_0020.php.
- Evidenze: amicizie, progetti.
- condition_id propagata correttamente.
- RuleRegistry: 20 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0020 Sun House 11.
- Prossimo passo: RULE-0021, Sole in Casa XII.

### 2026-07-12 — RULE-0021

- Obiettivo: implementare Sole in Casa XII.
- Creato: www/includes/forecast/rules/Rule0021_Sun12.php.
- Creato: www/tests/test_rule_0021.php.
- Evidenze: introspezione, spiritualita.
- condition_id propagata correttamente.
- RuleRegistry: 21 Rule registrate.
- Completata la copertura del Sole nelle 12 case.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0021 Sun House 12.
- Prossimo passo: RULE-0022, Luna in Casa I.

### 2026-07-12 — RULE-0022

- Obiettivo: implementare Luna in Casa I.
- Creato: www/includes/forecast/rules/Rule0022_Moon1.php.
- Creato: www/tests/test_rule_0022.php.
- Evidenze: emotivita, identita, salute.
- condition_id propagata correttamente.
- RuleRegistry: 22 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0022 Moon House 1.
- Prossimo passo: RULE-0023, Luna in Casa II.

### 2026-07-12 — RULE-0023

- Obiettivo: implementare Luna in Casa II.
- Creato: www/includes/forecast/rules/Rule0023_Moon2.php.
- Creato: www/tests/test_rule_0023.php.
- Evidenze: denaro, entrate.
- condition_id propagata correttamente.
- RuleRegistry: 23 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0023 Moon House 2.
- Prossimo passo: RULE-0024, Luna in Casa III.

### 2026-07-12 — RULE-0024

- Obiettivo: implementare Luna in Casa III.
- Creato: www/includes/forecast/rules/Rule0024_Moon3.php.
- Creato: www/tests/test_rule_0024.php.
- Evidenze: comunicazione, spostamenti.
- condition_id propagata correttamente.
- RuleRegistry: 24 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0024 Moon House 3.
- Prossimo passo: RULE-0025, Luna in Casa V.

### 2026-07-12 — RULE-0025

- Obiettivo: implementare Luna in Casa V.
- Creato: www/includes/forecast/rules/Rule0025_Moon5.php.
- Creato: www/tests/test_rule_0025.php.
- Evidenze: amore, creativita, figli.
- condition_id propagata correttamente.
- RuleRegistry: 25 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0025 Moon House 5.
- Prossimo passo: RULE-0026, Luna in Casa VI.

### 2026-07-13 — RULE-0025

- Implementata RULE-0025: Luna in Casa V.
- Evidenze: amore, creativita, figli.
- Test dedicato: OK.
- Full Regression: OK.
- RuleRegistry: 25 Rule registrate.
- Commit: feat(rule): implement RULE-0025 Moon House 5.
- Prossimo passo: RULE-0026, Luna in Casa VI.

### 2026-07-13 — Intervallo anni Rivoluzione Solare

- Modificato www/ricerca.php.
- L'elenco degli anni RS parte dal 1960.
- Il limite massimo resta anno corrente + 5.
- La modifica è voluta e diventa parte della baseline funzionale.
- Non ripristinare il precedente limite anno corrente - 2.
- Commit: feat(search): extend solar return year range from 1960.
- Prossimo passo: RULE-0026, Luna in Casa VI.

### 2026-07-13 — RULE-0026

- Implementata RULE-0026: Luna in Casa VI.
- Creato: www/includes/forecast/rules/Rule0026_Moon6.php.
- Creato: www/tests/test_rule_0026.php.
- Evidenze: lavoro, salute.
- condition_id propagata correttamente.
- RuleRegistry: 26 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0026 Moon House 6.
- Prossimo passo: RULE-0027, Luna in Casa VII.

### 2026-07-13 — RULE-0027

- Implementata RULE-0027: Luna in Casa VII.
- Evidenze: matrimonio, relazioni.
- Test dedicato: OK.
- Full Regression: OK.
- RuleRegistry: 27 Rule registrate.
- Commit: feat(rule): implement RULE-0027 Moon House 7.
- Prossimo passo: RULE-0028, Luna in Casa VIII.

### 2026-07-13 — RULE-0028

- Implementata RULE-0028: Luna in Casa VIII.
- Evidenze: trasformazione, prove.
- Test dedicato: OK.
- Full Regression: OK.
- RuleRegistry: 28 Rule registrate.
- Commit: feat(rule): implement RULE-0028 Moon House 8.
- Prossimo passo: RULE-0029, Luna in Casa IX.

### 2026-07-13 — RULE-0029

- Implementata RULE-0029: Luna in Casa IX.
- Evidenze: viaggi, estero.
- Test dedicato: OK.
- Full Regression: OK.
- RuleRegistry: 29 Rule registrate.
- Commit: feat(rule): implement RULE-0029 Moon House 9.
- Prossimo passo: RULE-0030, Luna in Casa X.

### 2026-07-13 — RULE-0030

- Implementata RULE-0030: Luna in Casa X.
- Evidenze: carriera, popolarita.
- Test dedicato: OK.
- Full Regression: OK.
- RuleRegistry: 30 Rule registrate.
- Commit: feat(rule): implement RULE-0030 Moon House 10.
- Prossimo passo: RULE-0031, Luna in Casa XI.

### 2026-07-13 — RULE-0031 e RULE-0032

- Implementata RULE-0031: Luna in Casa XI.
- Evidenze RULE-0031: amicizie, progetti.
- Implementata RULE-0032: Luna in Casa XII.
- Evidenze RULE-0032: introspezione, spiritualita.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 32 Rule registrate.
- Completata la copertura della Luna nelle 12 case.
- Commit: feat(rule): implement RULE-0031 and RULE-0032 Moon Houses 11-12.
- Prossimo passo: RULE-0033 e RULE-0034, Mercurio nelle Case I e II.

### 2026-07-13 — RULE-0033 e RULE-0034

- Implementata RULE-0033: Mercurio in Casa I.
- Evidenze RULE-0033: comunicazione, identita.
- Implementata RULE-0034: Mercurio in Casa II.
- Evidenze RULE-0034: affari, denaro.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 34 Rule registrate.
- Commit: feat(rule): implement RULE-0033 and RULE-0034 Mercury Houses 1-2.
- Prossimo passo: RULE-0035 e RULE-0036, Mercurio nelle Case IV e V.

### 2026-07-13 — RULE-0035 e RULE-0036

- Implementata RULE-0035: Mercurio in Casa IV.
- Evidenze RULE-0035: casa, famiglia.
- Implementata RULE-0036: Mercurio in Casa V.
- Evidenze RULE-0036: creativita, figli.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 36 Rule registrate.
- Commit: feat(rule): implement RULE-0035 and RULE-0036 Mercury Houses 4-5.
- Prossimo passo: RULE-0037 e RULE-0038, Mercurio nelle Case VI e VII.

### 2026-07-13 — RULE-0037 e RULE-0038

- Implementata RULE-0037: Mercurio in Casa VI.
- Evidenze RULE-0037: lavoro, salute.
- Implementata RULE-0038: Mercurio in Casa VII.
- Evidenze RULE-0038: contratti, relazioni.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 38 Rule registrate.
- Commit: feat(rule): implement RULE-0037 and RULE-0038 Mercury Houses 6-7.
- Prossimo passo: RULE-0039 e RULE-0040, Mercurio nelle Case VIII e IX.
