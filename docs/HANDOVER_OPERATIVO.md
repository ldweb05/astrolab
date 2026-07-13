

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

### 2026-07-13 — RULE-0039 e RULE-0040

- Implementata RULE-0039: Mercurio in Casa VIII.
- Evidenze RULE-0039: denaro, trasformazione.
- Implementata RULE-0040: Mercurio in Casa IX.
- Evidenze RULE-0040: estero, studio, viaggi.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 40 Rule registrate.
- Commit: feat(rule): implement RULE-0039 and RULE-0040 Mercury Houses 8-9.
- Prossimo passo: RULE-0041 e RULE-0042, Mercurio nelle Case X e XI.

### 2026-07-13 — RULE-0041 e RULE-0042

- Implementata RULE-0041: Mercurio in Casa X.
- Evidenze RULE-0041: carriera, successo.
- Implementata RULE-0042: Mercurio in Casa XI.
- Evidenze RULE-0042: amicizie, progetti.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 42 Rule registrate.
- Commit: feat(rule): implement RULE-0041 and RULE-0042 Mercury Houses 10-11.
- Prossimo passo: RULE-0043 e RULE-0044, Mercurio nelle Case XII e Venere nella Casa I.

### 2026-07-13 — RULE-0043 e RULE-0044

- Implementata RULE-0043: Mercurio in Casa XII.
- Evidenze RULE-0043: introspezione.
- Implementata RULE-0044: Venere in Casa I.
- Evidenze RULE-0044: identita, relazioni, salute.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 44 Rule registrate.
- Completata la copertura di Mercurio nelle 12 case.
- Commit: feat(rule): implement RULE-0043 and RULE-0044 Mercury 12 Venus 1.
- Prossimo passo: RULE-0045 e RULE-0046, Venere nelle Case II e III.

### 2026-07-13 — RULE-0045 e RULE-0046

- Implementata RULE-0045: Venere in Casa II.
- Evidenze RULE-0045: denaro, patrimonio.
- Implementata RULE-0046: Venere in Casa III.
- Evidenze RULE-0046: comunicazione, studio.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 46 Rule registrate.
- Confermato il lavoro a gruppi di due Rule.
- Dal prossimo gruppo il ciclo sarà maggiormente automatizzato.
- Commit: feat(rule): implement RULE-0045 and RULE-0046 Venus Houses 2-3.
- Prossimo passo: RULE-0047 e RULE-0048, Venere nelle Case IV e VI.

### 2026-07-13 — RULE-0047 e RULE-0048

- Implementata RULE-0047: Venere in Casa IV.
- Evidenze RULE-0047: casa, famiglia.
- Implementata RULE-0048: Venere in Casa VI.
- Evidenze RULE-0048: lavoro, salute.
- Controllo sintassi: OK.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 48 Rule registrate.
- Knowledge Coverage: 48/120 (40.0%).
- Commit: feat(rule): implement RULE-0047 and RULE-0048 Venus Houses 4-6.
- Prossimo passo: RULE-0049 e RULE-0050, Venere nelle Case VII e VIII.

### 2026-07-13 — RULE-0049 e RULE-0050

- Implementata RULE-0049: Venere in Casa VII.
- Evidenze RULE-0049: matrimonio, relazioni, societa.
- Implementata RULE-0050: Venere in Casa VIII.
- Evidenze RULE-0050: denaro, trasformazione.
- Controllo sintassi: OK.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 50 Rule registrate.
- Knowledge Coverage: 50/120 (41.7%).
- Commit: feat(rule): implement RULE-0049 and RULE-0050 Venus Houses 7-8.
- Prossimo passo: RULE-0051 e RULE-0052, Venere nelle Case IX e X.

### 2026-07-13 — RULE-0051 e RULE-0052

- Implementata RULE-0051: Venere in Casa IX.
- Evidenze RULE-0051: estero, studio, viaggi.
- Implementata RULE-0052: Venere in Casa X.
- Evidenze RULE-0052: carriera, prestigio.
- Controllo sintassi: OK.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 52 Rule registrate.
- Knowledge Coverage: 52/120 (43.3%).
- Commit: feat(rule): implement RULE-0051 and RULE-0052 Venus Houses 9-10.
- Prossimo passo: RULE-0053 e RULE-0054, Venere nelle Case XI e XII.

### 2026-07-13 — RULE-0053 e RULE-0054

- Implementata RULE-0053: Venere in Casa XI.
- Evidenze RULE-0053: amicizie, progetti.
- Implementata RULE-0054: Venere in Casa XII.
- Evidenze RULE-0054: prove, spiritualita.
- Controllo sintassi: OK.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 54 Rule registrate.
- Knowledge Coverage: 54/120 (45.0%).
- Completata la copertura di Venere nelle 12 case.
- Commit: feat(rule): implement RULE-0053 and RULE-0054 Venus Houses 11-12.
- Prossimo passo: RULE-0055 e RULE-0056, Marte nelle Case I e II.

### 2026-07-13 — RULE-0055 e RULE-0056

- Implementata RULE-0055: Marte in Casa I.
- Evidenze RULE-0055: energia, incidenti, salute.
- Implementata RULE-0056: Marte in Casa II.
- Evidenze RULE-0056: denaro, spese.
- Controllo sintassi: OK.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 56 Rule registrate.
- Knowledge Coverage: 56/120 (46.7%).
- Commit: feat(rule): implement RULE-0055 and RULE-0056 Mars Houses 1-2.
- Prossimo passo: RULE-0057 e RULE-0058, Marte nelle Case III e IV.

### 2026-07-13 — RULE-0057 e RULE-0058

- Implementata RULE-0057: Marte in Casa III.
- Evidenze RULE-0057: discussioni, spostamenti.
- Implementata RULE-0058: Marte in Casa IV.
- Evidenze RULE-0058: casa, famiglia, tensioni.
- Controllo sintassi: OK.
- Test dedicati: OK.
- Full Regression: OK.
- RuleRegistry: 58 Rule registrate.
- Knowledge Coverage: 58/120 (48.3%).
- Commit: feat(rule): implement RULE-0057 and RULE-0058 Mars Houses 3-4.
- Prossimo passo: RULE-0059 e RULE-0060, Marte nelle Case V e VII.

## 2026-07-13 — RULE-0059 e RULE-0060

- Rule implementate: RULE-0059, RULE-0060
- Pianeta e case: Marte in Casa V; Marte in Casa VII
- Evidenze prodotte:
  - RULE-0059: amore, figli
  - RULE-0060: relazioni, separazioni, cause
- Test dedicati:
  - test_rule_0059.php: OK
  - test_rule_0060.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 60
- Knowledge Coverage: 60/120 — 50,0%
- Commit: feat(rule): implement RULE-0059 and RULE-0060 Mars Houses 5-7
- Prossime Rule da implementare: RULE-0061 e RULE-0062

## 2026-07-13 — RULE-0061 e RULE-0062

- Rule implementate: RULE-0061, RULE-0062
- Pianeta e case: Marte in Casa VIII; Marte in Casa IX
- Evidenze prodotte:
  - RULE-0061: prove, trasformazione
  - RULE-0062: estero, viaggi
- Test dedicati:
  - test_rule_0061.php: OK
  - test_rule_0062.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 62
- Knowledge Coverage: 62/120 — 51,7%
- Commit: feat(rule): implement RULE-0061 and RULE-0062 Mars Houses 8-9
- Prossime Rule da implementare: RULE-0012 — sole in Casa 2; RULE-0063 — marte in Casa 10

## 2026-07-13 — RULE-0063 e RULE-0064

- Rule implementate: RULE-0063, RULE-0064
- Pianeta e case: Marte in Casa X; Marte in Casa XI
- Evidenze prodotte:
  - RULE-0063: carriera, conflitti
  - RULE-0064: amicizie, progetti
- Test dedicati:
  - test_rule_0063.php: OK
  - test_rule_0064.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 64
- Knowledge Coverage: 64/120 — 53,3%
- Commit: feat(rule): implement RULE-0063 and RULE-0064 Mars Houses 10-11
- Prossime Rule da implementare: RULE-0012 — sole in Casa 2; RULE-0065 — marte in Casa 12

## 2026-07-13 — RULE-0065 e RULE-0066

- Rule implementate: RULE-0065, RULE-0066
- Pianeta e case: Marte in Casa XII; Giove in Casa I
- Evidenze prodotte:
  - RULE-0065: prove, nemici, salute
  - RULE-0066: salute, identita, iniziative
- Test dedicati:
  - test_rule_0065.php: OK
  - test_rule_0066.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 66
- Knowledge Coverage: 66/120 — 55,0%
- Commit: feat(rule): implement RULE-0065 and RULE-0066 Mars 12 Jupiter 1
- Prossime Rule da implementare: RULE-0067 — giove in Casa 2; RULE-0068 — giove in Casa 3

## 2026-07-13 — RULE-0067 e RULE-0068

- Rule implementate: RULE-0067, RULE-0068
- Pianeta e case: Giove in Casa II; Giove in Casa III
- Evidenze prodotte:
  - RULE-0067: denaro, patrimonio
  - RULE-0068: studio, viaggi, comunicazione
- Test dedicati:
  - test_rule_0067.php: OK
  - test_rule_0068.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 68
- Knowledge Coverage: 68/120 — 56,7%
- Commit: feat(rule): implement RULE-0067 and RULE-0068 Jupiter Houses 2-3
- Prossime Rule da implementare: RULE-0069 — giove in Casa 4; RULE-0070 — giove in Casa 5

## 2026-07-13 — RULE-0069 e RULE-0070

- Rule implementate: RULE-0069, RULE-0070
- Pianeta e case: Giove in Casa IV; Giove in Casa V
- Evidenze prodotte:
  - RULE-0069: casa, famiglia
  - RULE-0070: amore, figli, creativita
- Test dedicati:
  - test_rule_0069.php: OK
  - test_rule_0070.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 70
- Knowledge Coverage: 70/120 — 58,3%
- Commit: feat(rule): implement RULE-0069 and RULE-0070 Jupiter Houses 4-5
- Prossime Rule da implementare: RULE-0071 — giove in Casa 6; RULE-0072 — giove in Casa 7

## 2026-07-13 — RULE-0071 e RULE-0072

- Rule implementate: RULE-0071, RULE-0072
- Pianeta e case: Giove in Casa VI; Giove in Casa VII
- Evidenze prodotte:
  - RULE-0071: lavoro, salute
  - RULE-0072: relazioni, matrimonio
- Test dedicati:
  - test_rule_0071.php: OK
  - test_rule_0072.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 72
- Knowledge Coverage: 72/120 — 60,0%
- Commit: feat(rule): implement RULE-0071 and RULE-0072 Jupiter Houses 6-7
- Prossime Rule da implementare: RULE-0073 — giove in Casa 8; RULE-0074 — giove in Casa 9

## 2026-07-13 — RULE-0073 e RULE-0074

- Rule implementate: RULE-0073, RULE-0074
- Pianeta e case: Giove in Casa VIII; Giove in Casa IX
- Evidenze prodotte:
  - RULE-0073: trasformazione, denaro
  - RULE-0074: viaggi, estero, studio
- Test dedicati:
  - test_rule_0073.php: OK
  - test_rule_0074.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 74
- Knowledge Coverage: 74/120 — 61,7%
- Commit: feat(rule): implement RULE-0073 and RULE-0074 Jupiter Houses 8-9
- Prossime Rule da implementare: RULE-0075 — giove in Casa 11; RULE-0076 — giove in Casa 12

## 2026-07-13 — RULE-0075 e RULE-0076

- Rule implementate: RULE-0075, RULE-0076
- Pianeta e case: Giove in Casa XI; Giove in Casa XII
- Evidenze prodotte:
  - RULE-0075: amicizie, progetti
  - RULE-0076: spiritualita, protezione
- Test dedicati:
  - test_rule_0075.php: OK
  - test_rule_0076.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 76
- Knowledge Coverage: 76/120 — 63,3%
- Commit: feat(rule): implement RULE-0075 and RULE-0076 Jupiter Houses 11-12
- Prossime Rule da implementare: RULE-0077 — saturno in Casa 1; RULE-0078 — saturno in Casa 2

## 2026-07-13 — RULE-0077 e RULE-0078

- Rule implementate: RULE-0077, RULE-0078
- Pianeta e case: Saturno in Casa I; Saturno in Casa II
- Evidenze prodotte:
  - RULE-0077: salute, fatica, isolamento
  - RULE-0078: denaro, restrizioni
- Test dedicati:
  - test_rule_0077.php: OK
  - test_rule_0078.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 78
- Knowledge Coverage: 78/120 — 65,0%
- Commit: feat(rule): implement RULE-0077 and RULE-0078 Saturn Houses 1-2
- Prossime Rule da implementare: RULE-0079 — saturno in Casa 3; RULE-0080 — saturno in Casa 4

## 2026-07-13 — RULE-0079 e RULE-0080

- Rule implementate: RULE-0079, RULE-0080
- Pianeta e case: Saturno in Casa III; Saturno in Casa IV
- Evidenze prodotte:
  - RULE-0079: studio, spostamenti, parenti
  - RULE-0080: casa, famiglia
- Test dedicati:
  - test_rule_0079.php: OK
  - test_rule_0080.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 80
- Knowledge Coverage: 80/120 — 66,7%
- Commit: feat(rule): implement RULE-0079 and RULE-0080 Saturn Houses 3-4
- Prossime Rule da implementare: RULE-0081 — saturno in Casa 5; RULE-0082 — saturno in Casa 6

## 2026-07-13 — RULE-0081 e RULE-0082

- Rule implementate: RULE-0081, RULE-0082
- Pianeta e case: Saturno in Casa V; Saturno in Casa VI
- Evidenze prodotte:
  - RULE-0081: amore, figli
  - RULE-0082: salute, lavoro, responsabilita
- Test dedicati:
  - test_rule_0081.php: OK
  - test_rule_0082.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 82
- Knowledge Coverage: 82/120 — 68,3%
- Commit: feat(rule): implement RULE-0081 and RULE-0082 Saturn Houses 5-6
- Prossime Rule da implementare: RULE-0083 — saturno in Casa 7; RULE-0084 — saturno in Casa 8

## 2026-07-13 — RULE-0083 e RULE-0084

- Rule implementate: RULE-0083, RULE-0084
- Pianeta e case: Saturno in Casa VII; Saturno in Casa VIII
- Evidenze prodotte:
  - RULE-0083: relazioni, matrimonio, separazioni
  - RULE-0084: prove, trasformazione
- Test dedicati:
  - test_rule_0083.php: OK
  - test_rule_0084.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 84
- Knowledge Coverage: 84/120 — 70,0%
- Commit: feat(rule): implement RULE-0083 and RULE-0084 Saturn Houses 7-8
- Prossime Rule da implementare: RULE-0085 — saturno in Casa 9; RULE-0086 — saturno in Casa 10

## 2026-07-13 — RULE-0085 e RULE-0086

- Rule implementate: RULE-0085, RULE-0086
- Pianeta e case: Saturno in Casa IX; Saturno in Casa X
- Evidenze prodotte:
  - RULE-0085: estero, studio
  - RULE-0086: carriera, responsabilita, prestigio
- Test dedicati:
  - test_rule_0085.php: OK
  - test_rule_0086.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 86
- Knowledge Coverage: 86/120 — 71,7%
- Commit: feat(rule): implement RULE-0085 and RULE-0086 Saturn Houses 9-10
- Prossime Rule da implementare: RULE-0087 — saturno in Casa 11; RULE-0088 — urano in Casa 1

## 2026-07-13 — RULE-0087 e RULE-0088

- Rule implementate: RULE-0087, RULE-0088
- Pianeta e case: Saturno in Casa XI; Urano in Casa I
- Evidenze prodotte:
  - RULE-0087: amicizie, progetti
  - RULE-0088: cambiamenti, identita
- Test dedicati:
  - test_rule_0087.php: OK
  - test_rule_0088.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 88
- Knowledge Coverage: 88/120 — 73,3%
- Commit: feat(rule): implement RULE-0087 Saturn 11 and RULE-0088 Uranus 1
- Prossime Rule da implementare: RULE-0089 — urano in Casa 2; RULE-0090 — urano in Casa 3
