# HANDOVER OPERATIVO ASTROLAB

Documento unificato derivato dagli handover Astro-Val e Astro-DSS.

---



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

**Questo deve essere il workflow operativo:**
- 1	apriamo un file;
- 2	lo leggiamo;
- 3	individuiamo solo ciò che va aggiornato;
- 4	preparo uno script Python;
- 5	tu lo esegui;
- 6	verifichiamo (diff, php -l, git diff --check, ecc.);
- 7	commit.

# Astro-Val — Handover operativo

Ultimo aggiornamento: 2026-07-16

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
- Creato docs/ADR_INDEX_ASTROLAB.md.
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
- Prossimo passo: RULE-0012, Sole in Casa II.

### 2026-07-12 — RULE-0012

- Implementata RULE-0012: Sole in Casa II.
- Evidenze prodotte: denaro, patrimonio.
- condition_id propagata correttamente.
- RuleRegistry: 12 Rule registrate.
- Test dedicato: OK.
- Full Regression: OK.
- Commit: feat(rule): implement RULE-0012 Sun House 2.
- Prossimo passo: RULE-0013, Sole in Casa III.


### 2026-07-12 — Direttiva operativa permanente

- Congelata definitivamente l'architettura attuale.
- Vietate nuove versioni, riprogettazioni e refactoring non necessari.
- Obiettivo esclusivo: completare il progetto con i contratti esistenti.
- Aggiornati tutti i documenti operativi e normativi.
- Reso obbligatorio registrare ogni passaggio in HANDOVER_OPERATIVO_astrolab.md.
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

### 2026-07-12
— RULE-0021

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
- Test

 dedicati: OK.
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
- Rule registrate:

 86
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

## 2026-07-13 — RULE-0089 e RULE-0090

- Rule implementate: RULE-0089, RULE-0090
- Pianeta e case: Urano in Casa II; Urano in Casa III
- Evidenze prodotte:
  - RULE-0089: denaro, imprevisti
  - RULE-0090: studio, comunicazione, spostamenti
- Test dedicati:
  - test_rule_0089.php: OK
  - test_rule_0090.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 90
- Knowledge Coverage: 90/120 — 75,0%
- Commit: feat(rule): implement RULE-0089 and RULE-0090 Uranus Houses 2-3
- Prossime Rule da implementare: RULE-0091 — urano in Casa 4; RULE-0092 — urano in Casa 5

## 2026-07-13 — RULE-0091 e RULE-0092

- Rule implementate: RULE-0091, RULE-0092
- Pianeta e case: Urano in Casa IV; Urano in Casa V
- Evidenze prodotte:
  - RULE-0091: casa, traslochi
  - RULE-0092: amore, creativita
- Test dedicati:
  - test_rule_0091.php: OK
  - test_rule_0092.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 92
- Knowledge Coverage: 92/120 — 76,7%
- Commit: feat(rule): implement RULE-0091 and RULE-0092 Uranus Houses 4-5
- Prossime Rule da implementare: RULE-0093 — urano in Casa 6; RULE-0094 — urano in Casa 7

## 2026-07-13 — RULE-0093 e RULE-0094

- Rule implementate: RULE-0093, RULE-0094
- Pianeta e case: Urano in Casa VI; Urano in Casa VII
- Evidenze prodotte:
  - RULE-0093: lavoro, salute
  - RULE-0094: relazioni, rotture
- Test dedicati:
  - test_rule_0093.php: OK
  - test_rule_0094.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 94
- Knowledge Coverage: 94/120 — 78,3%
- Commit: feat(rule): implement RULE-0093 and RULE-0094 Uranus Houses 6-7
- Prossime Rule da implementare: RULE-0095 — urano in Casa 8; RULE-0096 — urano in Casa 9

## 2026-07-13 — RULE-0095 e RULE-0096

- Rule implementate: RULE-0095, RULE-0096
- Pianeta e case: Urano in Casa VIII; Urano in Casa IX
- Evidenze prodotte:
  - RULE-0095: trasformazione
  - RULE-0096: viaggi, estero
- Test dedicati:
  - test_rule_0095.php: OK
  - test_rule_0096.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 96
- Knowledge Coverage: 96/120 — 80,0%
- Commit: feat(rule): implement RULE-0095 and RULE-0096 Uranus Houses 8-9
- Prossime Rule da implementare: RULE-0097 — urano in Casa 10; RULE-0098 — urano in Casa 12

## 2026-07-13 — RULE-0097 e RULE-0098

- Rule implementate: RULE-0097, RULE-0098
- Pianeta e case: Urano in Casa X; Urano in Casa XII
- Evidenze prodotte:
  - RULE-0097: carriera, innovazione
  - RULE-0098: prove, liberazione
- Test dedicati:
  - test_rule_0097.php: OK
  - test_rule_0098.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 98
- Knowledge Coverage: 98/120 — 81,7%
- Commit: feat(rule): implement RULE-0097 and RULE-0098 Uranus Houses 10-12
- Prossime Rule da implementare: RULE-0099 — nettuno in Casa 1; RULE-0100 — nettuno in Casa 2

## 2026-07-13 — RULE-0099 e RULE-0100

- Rule implementate: RULE-0099, RULE-0100
- Pianeta e case: Nettuno in Casa I; Nettuno in Casa II
- Evidenze prodotte:
  - RULE-0099: spiritualita, identita
  - RULE-0100: denaro, confusione
- Test dedicati:
  - test_rule_0099.php: OK
  - test_rule_0100.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 100
- Knowledge Coverage: 100/120 — 83,3%
- Commit: feat(rule): implement RULE-0099 and RULE-0100 Neptune Houses 1-2
- Prossime Rule da implementare: RULE-0101 — nettuno in Casa 3; RULE-0102 — nettuno in Casa 4

## 2026-07-13 — RULE-0101 e RULE-0102

- Rule implementate: RULE-0101, RULE-0102
- Pianeta e case: Nettuno in Casa III; Nettuno in Casa IV
- Evidenze prodotte:
  - RULE-0101: studio, intuizione
  - RULE-0102: casa, famiglia
- Test dedicati:
  - test_rule_0101.php: OK
  - test_rule_0102.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 102
- Knowledge Coverage: 102/120 — 85,0%
- Commit: feat(rule): implement RULE-0101 and RULE-0102 Neptune Houses 3-4
- Prossime Rule da implementare: RULE-0103 — nettuno in Casa 5; RULE-0104 — nettuno in Casa 6

## 2026-07-13 — RULE-0103 e RULE-0104

- Rule implementate: RULE-0103, RULE-0104
- Pianeta e case: Nettuno in Casa V; Nettuno in Casa VI
- Evidenze prodotte:
  - RULE-0103: amore, creativita
  - RULE-0104: salute, lavoro
- Test dedicati:
  - test_rule_0103.php: OK
  - test_rule_0104.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 104
- Knowledge Coverage: 104/120 — 86,7%
- Commit: feat(rule): implement RULE-0103 and RULE-0104 Neptune Houses 5-6
- Prossime Rule da implementare: RULE-0105 — nettuno in Casa 7; RULE-0106 — nettuno in Casa 8

## 2026-07-13 — RULE-0105 e RULE-0106

- Rule implementate: RULE-0105, RULE-0106
- Pianeta e case: Nettuno in Casa VII; Nettuno in Casa VIII
- Evidenze prodotte:
  - RULE-0105: relazioni, idealizzazione
  - RULE-0106: trasformazione, psicologia
- Test dedicati:
  - test_rule_0105.php: OK
  - test_rule_0106.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 106
- Knowledge Coverage: 106/120 — 88,3%
- Commit: feat(rule): implement RULE-0105 and RULE-0106 Neptune Houses 7-8
- Prossime Rule da implementare: RULE-0107 — nettuno in Casa 10; RULE-0108 — nettuno in Casa 11

## 2026-07-13 — RULE-0107 e RULE-0108

- Rule implementate: RULE-0107, RULE-0108
- Pianeta e case: Nettuno in Casa X; Nettuno in Casa XI
- Evidenze prodotte:
  - RULE-0107: carriera, ispirazione
  - RULE-0108: amicizie, progetti
- Test dedicati:
  - test_rule_0107.php: OK
  - test_rule_0108.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 108
- Knowledge Coverage: 108/120 — 90,0%
- Commit: feat(rule): implement RULE-0107 and RULE-0108 Neptune Houses 10-11
- Prossime Rule da implementare: RULE-0109 — nettuno in Casa 12; RULE-0110 — plutone in Casa 1

## 2026-07-13 — RULE-0109 e RULE-0110

- Rule implementate: RULE-0109, RULE-0110
- Pianeta e case: Nettuno in Casa XII; Plutone in Casa I
- Evidenze prodotte:
  - RULE-0109: spiritualita, prove, introspezione
  - RULE-0110: trasformazione, identita
- Test dedicati:
  - test_rule_0109.php: OK
  - test_rule_0110.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 110
- Knowledge Coverage: 110/120 — 91,7%
- Commit: feat(rule): implement RULE-0109 Neptune 12 and RULE-0110 Pluto 1
- Prossime Rule da implementare: RULE-0111 — plutone in Casa 2; RULE-0112 — plutone in Casa 3

## 2026-07-13 — RULE-0111 e RULE-0112

- Rule implementate: RULE-0111, RULE-0112
- Pianeta e case: Plutone in Casa II; Plutone in Casa III
- Evidenze prodotte:
  - RULE-0111: denaro, patrimonio
  - RULE-0112: comunicazione, studio
- Test dedicati:
  - test_rule_0111.php: OK
  - test_rule_0112.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 112
- Knowledge Coverage: 112/120 — 93,3%
- Commit: feat(rule): implement RULE-0111 and RULE-0112 Pluto Houses 2-3
- Prossime Rule da implementare: RULE-0113 — plutone in Casa 4; RULE-0114 — plutone in Casa 5

## 2026-07-13 — RULE-0113 e RULE-0114

- Rule implementate: RULE-0113, RULE-0114
- Pianeta e case: Plutone in Casa IV; Plutone in Casa V
- Evidenze prodotte:
  - RULE-0113: casa, famiglia
  - RULE-0114: amore, figli, creativita
- Test dedicati:
  - test_rule_0113.php: OK
  - test_rule_0114.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 114
- Knowledge Coverage: 114/120 — 95,0%
- Commit: feat(rule): implement RULE-0113 and RULE-0114 Pluto Houses 4-5
- Prossime Rule da implementare: RULE-0115 — plutone in Casa 6; RULE-0116 — plutone in Casa 7

## 2026-07-13 — RULE-0115 e RULE-0116

- Rule implementate: RULE-0115, RULE-0116
- Pianeta e case: Plutone in Casa VI; Plutone in Casa VII
- Evidenze prodotte:
  - RULE-0115: lavoro, salute
  - RULE-0116: relazioni, trasformazione
- Test dedicati:
  - test_rule_0115.php: OK
  - test_rule_0116.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 116
- Knowledge Coverage: 116/120 — 96,7%
- Commit: feat(rule): implement RULE-0115 and RULE-0116 Pluto Houses 6-7
- Prossime Rule da implementare: RULE-0117 — plutone in Casa 9; RULE-0118 — plutone in Casa 10

## 2026-07-13 — RULE-0117 e RULE-0118

- Rule implementate: RULE-0117, RULE-0118
- Pianeta e case: Plutone in Casa IX; Plutone in Casa X
- Evidenze prodotte:
  - RULE-0117: estero, studio
  - RULE-0118: carriera, potere, prestigio
- Test dedicati:
  - test_rule_0117.php: OK
  - test_rule_0118.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 118
- Knowledge Coverage: 118/120 — 98,3%
- Commit: feat(rule): implement RULE-0117 and RULE-0118 Pluto Houses 9-10
- Prossime Rule da implementare: RULE-0119 — plutone in Casa 11; RULE-0120 — plutone in Casa 12

## 2026-07-13 — RULE-0119 e RULE-0120

- Rule implementate: RULE-0119, RULE-0120
- Pianeta e case: Plutone in Casa XI; Plutone in Casa XII
- Evidenze prodotte:
  - RULE-0119: amicizie, progetti
  - RULE-0120: prove, spiritualita

, introspezione
- Test dedicati:
  - test_rule_0119.php: OK
  - test_rule_0120.php: OK
- Full Regression: FULL REGRESSION OK
- Rule registrate: 120
- Knowledge Coverage: 120/120 — 100,0%
- Commit: feat(rule): implement RULE-0119 and RULE-0120 Pluto Houses 11-12
- Prossime Rule da implementare: nessuna — Knowledge Coverage completata


## 2026-07-13 — Avvio V5: Consolidamento Report Professionale

- Branch: `docs/v5-transition`.
- Baseline Rule Engine: commit `0bc53d0`.
- Rule Engine confermato in FREEZE.
- Aggiunta deduplicazione dei paragrafi narrativi finali.
- Aggiunto Executive Summary strutturato.
- Executive Summary propagato nell'Annual Report.
- Aggiunta sezione narrativa `Sintesi esecutiva`.
- Aggiunta sezione narrativa `Profilo dei temi principali`.
- Rimosso codice obsoleto da `AnnualReportDraftBuilder`.
- Aggiunto controllo automatico delle sezioni narrative duplicate.
- Annual Report corrente: 12 sezioni, circa 1.230 parole.
- Test dedicati: OK.
- Full Regression: OK.
- Rule registrate: 120.
- Knowledge Coverage: 100%.
- Ultimo commit funzionale: `e1fb4fe`.
- Prossimo passo: consolidamento Cross Dynamics e conclusione professionale.

## 2026-07-14 — Report Annuale integrato nel PDF nativo

- Confermata la pipeline di stampa:
  - browser: SVG + `window.print()`;
  - PDF nativo: SVG → Canvas → PNG base64 → Dompdf.
- Dompdf confermato disponibile nel container.
- Vietata la reintroduzione di SVG inline nel PDF.
- Creato `AnnualReportPrintRenderer`.
- Il payload di `stampa.php` trasmette ora `relazione_annuale`.
- `stampa_pdf_api.php` integra nota metodologica e sezioni narrative.
- Aggiunti stili editoriali compatibili con Dompdf.
- Escape HTML verificato.
- Test dedicato: `test_annual_report_print_renderer.php` — OK.
- Annual Report corrente: 12 sezioni, circa 1.223 parole.
- Full Regression: OK.
- Rule registrate: 120.
- Rule Engine invariato e in FREEZE.
- Commit: `ee27d89` — `feat(pdf): include annual narrative report in native PDF`.
- Prossimo passo: verifica visiva del PDF su casi reali e consolidamento UX.

## 2026-07-14 — Consolidamento finale V5

- Branch: `feature/sintesi-rsm`.
- Report Annuale disponibile nel PDF Dompdf.
- Report Annuale disponibile in anteprima e stampa browser.
- Pipeline grafica confermata:
  - browser: SVG + `window.print()`;
  - PDF: SVG → Canvas → PNG base64 → Dompdf.
- Payload PDF sanitizzato tramite `AnnualReportPrintSanitizer`.
- Narrative Style Engine consolidato.
- Validazione narrativa multi-scenario completata.
- Test dedicati: OK.
- Full Regression: OK.
- Rule registrate: 120.
- Knowledge Coverage: 100%.
- Rule Engine invariato e in FREEZE.
- Ultimo commit: `3406077`.
- Prossimo passo: verifica visiva con casi operativi reali e preparazione V6.

## 2026-07-15 — V6 Hardening e Release Check

- Branch operativo: `feature/sintesi-rsm`.
- Rule Engine: 120 Rule, stato FREEZE.
- Manifest FREEZE: 122 file protetti.
- Hardening Suite: OK.
- Full Regression: OK.
- API non autenticate: OK.
- API autenticate: OK.
- Report Annuale: 12 sezioni.
- Determinismo JSON: OK.
- Determinismo PDF Dompdf: OK.
- Budget Forecast Engine: OK.
- Release check: 36 secondi su limite di 180.
- Comando release:
  `www/tests/run_v6_release_check.sh`
- Ultimo commit funzionale:
  `4586c81 test(v6): add release readiness check`
- Working Tree: CLEAN.

## 2026-07-15 — V6 Release Candidate 1 superata

- Branch: `feature/sintesi-rsm`.
- V6 Hardening Suite: OK.
- V6 Release Check: OK.
- V6 RC1 Check: OK.
- Durata release check: 28 secondi.
- Timeout: 180 secondi.
- Rule Engine: 120 Rule in FREEZE.
- Full Regression: OK.
- API autenticate: OK.
- API non autenticate: OK.
- Report Annuale e PDF: OK.
- Working Tree: CLEAN.
- Backup `.env` non più tracciati da Git.
- Ultimo commit di sicurezza: `935cedc`.
- Prossimo passo: verifica visuale e preparazione RC2.

## 2026-07-15 — Correzione finestra stampa Relazione Annuale

La funzione `stampaPrevisioneAnnuale()` continua a usare una finestra
temporanea dedicata alla stampa, ma ora la chiude automaticamente dopo
la conclusione o l'annullamento del dialogo del browser.

- file: `www/rs.php`;
- commit: `2f8055d`;
- verifica visuale: OK;
- Full Regression: OK;
- V6 Release Check: OK;
- durata: 30 secondi;
- Working Tree: CLEAN.

## 2026-07-15 — V6 Release Candidate 2 superata

- Branch: `feature/sintesi-rsm`.
- V6 Hardening Suite: OK.
- V6 Release Check: OK.
- V6 RC2 Check: OK.
- Durata release check: 27 secondi.
- Timeout: 180 secondi.
- Rule Engine: 120 Rule in FREEZE.
- Full Regression: OK.
- Verifica visuale browser/PDF: OK.
- Finestra temporanea stampa: chiusura automatica verificata.
- Backup PostgreSQL: 2.769.999 byte.
- Restore PostgreSQL: OK.
- Tabelle ripristinate: 7.
- Utenti ripristinati: 3.
- Soggetti ripristinati: 4.
- Working Tree: CLEAN.
- Ultimo commit RC2: `ff94c69`.
- Prossimo passo: configurazione production e RC finale.

## 2026-07-15 — V6.1 UX Mobile: Header Responsive

- Implementato menu hamburger responsive condiviso.
- Header unificato tramite `includes/header_nav.php`.
- Creato `www/js/header_nav.js` per la gestione della navigazione mobile.
- Refactoring degli stili dell'header centralizzati in `www/css/style.css`.
- Sottomenu "Rivoluzioni" con tre voci:
  - Rivoluzione Solare
  - Rivoluzione Lunare
  - Rilocazione
- Sezione utente integrata nel menu mobile (utente, soggetto, Password, Esci).
- Test browser desktop e iPhone completati con esito positivo.
- Commit: `2b471fa`.

## 2026-07-15 — Consolidamento documentazione V6 RC2

- README aggiornato allo stato reale della piattaforma.
- START_HERE allineato alla milestone corrente.
- ROADMAP e HANDOVER verificati e coerenti.
- Nessun riferimento operativo obsoleto residuo.
- Environment switching documentato.
- Working Tree CLEAN.
- Stato del progetto pronto per la preparazione della Release stabile.

------------------------------------------------------------------------

## 2026-07-16 — V6.1 Refactoring UI e CSS

Completato il refactoring degli stili delle quattro pagine principali:

- `www/index.php`;
- `www/tema.php`;
- `www/rs.php`;
- `www/rl.php`.

Interventi eseguiti:

- eliminazione degli inline style statici;
- centralizzazione degli stili in `www/css/style.css`;
- introduzione di classi CSS riutilizzabili;
- utilizzo della classe `is-hidden` per gli elementi inizialmente nascosti;
- mantenimento invariato del comportamento PHP e JavaScript;
- test browser desktop e iPhone completati con esito positivo;
- lint PHP e `git diff --check` completati con esito positivo.

Commit:

- `a824d80` — `refactor(ui): remove inline styles from subjects page`;
- `fb22cd6` — `refactor(ui): remove inline styles from natal chart page`;
- `8aa5b6c` — `refactor(ui): remove inline styles from solar return page`;
- `c411ee3` — `refactor(ui): remove inline styles from lunar return page`.

Stato corrente:

- branch: `feature/v6.1`;
- Rule Engine: FREEZE a 120 Rule;
- Working Tree: CLEAN;
- prossimo passo: rifiniture UX/UI finali e preparazione della release V6.1.

------------------------------------------------------------------------

## 2026-07-16 — V6.1 Refactoring stili JavaScript

Completata la rimozione degli inline style statici dai moduli JavaScript
della Rivoluzione Lunare e degli alert della Rivoluzione Solare.

Interventi eseguiti:

- refactoring di `www/js/rl.js`;
- riutilizzo delle classi condivise presenti in `www/css/style.css`;
- conversione di messaggi, tabelle, cuspidi e informazioni orarie RL;
- refactoring di `www/js/rs_alert.js`;
- sostituzione dei colori inline degli alert con classi CSS di severità;
- mantenimento degli stili runtime realmente dinamici;
- verifica sintassi JavaScript tramite `node --check`;
- verifica `git diff --check` completata con esito positivo.

Commit:

- `f7d7278` — `refactor(ui): remove inline styles from lunar return scripts`;
- `ea48964` — `refactor(ui): replace inline alert colors with CSS classes`.

Stato corrente:

- branch: `feature/v6.1`;
- Rule Engine: FREEZE a 120 Rule;
- prossimo passo: proseguire il censimento degli inline style statici residui nei moduli JavaScript.

------------------------------------------------------------------------

## 2026-07-16 — V6.1 Stabilizzazione automatica

Completata la prima validazione automatica completa dopo il
consolidamento UX/UI e CSS della V6.1.

Verifiche eseguite:

- Full Regression: OK;
- V6 Hardening Suite: OK;
- V6 Release Check: OK;
- PHP Syntax Check: OK;
- Shell Syntax Check: OK;
- Rule Engine FREEZE: 120 Rule e 122 file protetti;
- API autenticate e non autenticate: OK;
- Report Annuale: 12 sezioni;
- determinismo JSON e PDF: OK;
- casi reali e limiti delle case: OK;
- performance Forecast Engine: OK;
- tempo complessivo Release Check: 32 secondi;
- timeout disponibile: 180 secondi.

Comando eseguito:

`docker exec -e ASTRO_VAL_BASE_URL=http://127.0.0.1 astro-val-web sh -lc '/var/www/html/tests/run_v6_release_check.sh'`

Stato corrente:

- branch: `feature/v6.1`;
- Rule Engine: FREEZE a 120 Rule;
- Working Tree attesa: CLEAN;
- prossimo passo: validazione manuale completa desktop e mobile.

## 2026-07-16 — Stabilizzazione markup pagine principali

- Branch: `feature/v6.1`.
- Obiettivo: correggere piccoli problemi reali di markup emersi durante la validazione desktop, senza introdurre refactoring non necessari.
- Modificato `www/tema.php`:
  - rimossi i contenitori `table` che producevano tabelle HTML annidate;
  - sostituiti con contenitori `div`;
  - commit: `fb5d8b0` — `fix(ui): avoid nested tables in natal

 chart page`.
- Modificato `www/rs.php`:
  - corretta la costruzione della tabella pianeti;
  - aggiunti `thead`, `tbody` e chiusure HTML valide;
  - commit: `d62e465` — `fix(ui): correct planetary table markup in solar return page`.
- Modificato `www/js/rl.js`:
  - corretta `_popolaTabellaPianeti()`;
  - aggiunti `thead`, `tbody` e chiusure delle righe;
  - mantenuta la tabella contenitore già presente in `www/rl.php`;
  - commit: `6025743` — `fix(ui): correct lunar return planetary table markup`.
- Rule Engine non modificato.
- Rule Engine Freeze confermato: 120 Rule, 122 file protetti.
- Verifiche eseguite:
  - `php -l`: OK;
  - `node --check`: OK;
  - `git diff --check`: OK;
  - test dedicato RL: 13 rivoluzioni lunari OK;
  - Full Regression: OK;
  - V6 Hardening Suite: OK;
  - V6 Release Check: OK.
- Tempo V6 Release Check: 27 secondi.
- Working tree finale: CLEAN.
- Prossimo passo: proseguire con la validazione manuale desktop e mobile; intervenire solo su bug concreti eventualmente emersi.



---

# SEZIONE DSS

# Astro-DSS — Handover operativo

Ultimo aggiornamento: 2026-07-18

Stato corrente

progetto: Astro-DSS;

branch: feature/v6.1;

repository indipendente da Astro-Val;

container web: astro-dss-web;

container database: astro-dss-db;

container Adminer: astro-dss-adminer;

applicazione disponibile sulla porta 8192;

Adminer disponibile sulla porta 8193;

PostgreSQL disponibile sulla porta 5442;

database ripristinato: 7 tabelle, circa 28 MB;

Rule Engine ereditato: 120 Rule;

Knowledge Coverage: 100%;

Rule Engine congelato durante la prima fase DSS.

Obiettivo operativo corrente

Aggiornare la documentazione tecnica del Comparator Engine completato e proseguire con la progettazione dei successivi moduli DSS previsti dalla roadmap:

- Impact Evaluator;
- Rule Correlator.

Il lavoro dovrà mantenere invariato il Rule Engine ereditato e valorizzare i dati già prodotti dai confronti tra Rivoluzioni Solari e Rilocazioni.

Timeline Astro-DSS

2026-07-17 — Creazione del progetto indipendente

Componente modificato:

repository Git;

Docker Compose;

database PostgreSQL;

documentazione.

Obiettivo:

separare completamente Astro-DSS da Astro-Val.

Risultato:

creato stack Docker indipendente;

assegnate porte indipendenti;

ripristinato e verificato il database;

verificata l'applicazione sulla porta 8192;

aggiornati docs/START_HERE.md, README.md e docs/ROADMAP.md.

Test eseguiti:

avvio container;

accesso applicazione;

verifica database;

git diff --check;

verifica working tree.

Commit:

130944e — isolamento stack Docker;

24d25ba — definizione entry point;

27044bb — aggiornamento README;

db415b2 — definizione roadmap Astro-DSS.

Passo successivo:

inventario completo degli output del motore RS.

2026-07-18 — Prima implementazione del Comparator Engine

Obiettivo

Trasformare la fase di analisi iniziale in una prima implementazione operativa del confronto tra Rivoluzioni Solari e Rilocazioni senza modificare il Rule Engine ereditato da Astro-Val.

Componenti sviluppati

Confronto Rivoluzioni Solari

Sono state implementate le funzionalità necessarie per confrontare più Rivoluzioni Solari all'interno della stessa sessione.

In particolare sono stati aggiunti:

selezione tramite checkbox;

confronto fino a tre risultati contemporaneamente;

persistenza della selezione;

costruzione del payload di confronto;

pagina dedicata compare_rs.php;

riepilogo dei soggetti selezionati;

layout responsive.

Confronto Rilocazioni

Lo stesso modello è stato esteso al confronto delle rilocazioni.

Sono stati realizzati:

selezione multipla;

confronto fino a tre rilocazioni;

payload dedicato;

pagina compare_ril.php;

riepilogo delle località confrontate;

layout responsive.

Evoluzione di compare_ril.php

La pagina di confronto oggi visualizza:

informazioni del soggetto;

località confrontate;

dati sintetici;

tabelle dei match astrologici;

posizione di Venere;

posizione di Giove;

casa astrologica;

cuspide;

distanza dalla cuspide.

Il payload JSON rimane disponibile come strumento di debug tramite un elemento <details> inizialmente chiuso.

Correzioni effettuate

Durante lo sviluppo sono stati risolti:

inizializzazione di SubjectName;

inizializzazione di UserName;

warning PHP;

errori HTTP 500;

gestione incompleta del payload;

visualizzazione dei dati mancanti.

Stato raggiunto

Attualmente risultano completati:

confronto RS;

confronto Rilocazioni;

selezione multipla;

persistenza della selezione;

payload di confronto;

layout responsive;

tabelle dei match;

PHP lint senza errori;

risposta HTTP 200.

Commit della milestone

L'implementazione della prima versione del Comparator Engine è stata sviluppata attraverso i seguenti commit principali.

Confronto Rivoluzioni Solari

e731330 — test: lock RS VAL output contract

c4707d3 — feat: prepare comparison selection state

9b7f4ba — feat: add comparison checkbox to standard results

99e7013 — feat: persist up to three comparison selections

c444819 — feat: show comparison action for selected results

cae451d — style: simplify comparison checkbox column

5f36b2c — feat: resolve selected comparison results

6e8e92b — feat: pass comparison payload to compare page

4884831 — feat: add comparison payload debug page

c3dcc50 — feat: show comparison payload count

08507de — feat: show comparison summary

161e26f — feat: add responsive comparison layout

Confronto Rilocazioni

e4038f6 — feat: add comparison checkboxes to relocation results

a73cd25 — feat: persist up to three relocation selections

8c5eee1 — feat: show relocation comparison action

99276ca — feat: persist relocation comparison payload

470fafc — feat: add relocation comparison page

3611d06 — debug: show relocation comparison payload

e1d6a75 — fix: initialize subject name in comparison pages

538d131 — fix: show subject name in RS comparison

ce94849 — fix: initialize username in comparison pages

1fe9110 — feat: show relocation match details

Decisione architetturale

Per la visualizzazione delle ruote astrologiche è stato stabilito di non duplicare la logica esistente.

La pagina rilocazione.php rappresenta il riferimento funzionale da riutilizzare.

L'integrazione dovrà utilizzare direttamente:

tema_api.php;

ZodiacWheel;

WheelRiloc;

popolaTabellaPianeti().

Punto di ripresa definito in questa milestone

Al termine di questa fase, la successiva attività prevista consisteva nell'integrare in `compare_ril.php` la visualizzazione delle ruote astrologiche reali, mantenendo invariata la logica astrologica già presente nell'applicazione.

Questa attività è stata successivamente completata nella milestone del 2026-07-18 dedicata all'integrazione delle ruote astrologiche nel confronto Rilocazioni.

---

## 2026-07-18 — Integrazione delle ruote astrologiche nel confronto Rilocazioni

### Componente modificato

- `compare_ril.php`

### Obiettivo

Completare il Comparator Engine integrando nella pagina di confronto le ruote astrologiche già utilizzate dall'applicazione, evitando duplicazioni della logica esistente.

### Risultato

- integrate le ruote astrologiche nella pagina di confronto;
- riutilizzati i componenti già presenti nell'applicazione;
- mantenuta invariata la logica astrologica;
- completato il precedente punto di ripresa relativo al Comparator Engine.

### Commit

- `984d4bd` — feat: render relocation wheels in comparison

---

## 2026-07-18 — Preservazione delle regole personalizzate nel confronto RS

### Componente modificato

- Comparator Engine RS

### Obiettivo

Garantire che il confronto tra Rivoluzioni Solari utilizzi le stesse regole personalizzate applicate durante il calcolo originale, senza ricostruire le configurazioni delle case.

### Risultato

- preservate le regole personalizzate dei pianeti nelle case;
- mantenuta la coerenza tra risultato originale e pagina di confronto;
- nessuna modifica al Rule Engine.

### Commit

- `57fbba4` — feat: preserve custom planet house rules in RS comparison

---

## 2026-07-18 — Merge nel branch principale di sviluppo

### Operazione

Il lavoro sviluppato nel branch `feature/astro-dss` è stato integrato nel branch di lavoro `feature/v6.1` mediante merge non fast-forward.

### Commit

- `faf8462` — Merge branch 'feature/astro-dss' into feature/v6.1

---

## 2026-07-18 — Consolidamento dell'interfaccia del Comparator RS

### Componenti modificati

- `ricerca.php`
- `style.css`

### Obiettivo

Rifinire l'interfaccia del Comparator mantenendo invariato il comportamento funzionale.

### Modifiche effettuate

- pulsante **Confronta** mantenuto sopra la tabella e allineato a destra;
- stile del pulsante uniformato a quello verde piatto di **Mappa**;
- dimensioni leggermente ridotte;
- colonna delle checkbox spostata all'estrema destra, dopo la colonna **RS**;
- pulsanti **Mappa** e **Usa** lasciati invariati.

### Verifiche

- `docker compose exec -T astro-dss-web php -l /var/www/html/ricerca.php` ✔
- `git diff --check` ✔
- working tree pulita ✔

### Commit

- `37d15be` — feat: refine RS comparison controls layout

---

### Stato attuale

- Comparator Engine RS operativo;
- Comparator Engine Rilocazioni operativo;
- ruote astrologiche integrate;
- regole personalizzate preservate nel confronto RS;
- interfaccia del Comparator consolidata;
- branch corrente: `feature/v6.1`;
- working tree pulita.

### Prossimo passo

Aggiornare la documentazione tecnica e proseguire con la progettazione dell'Impact Evaluator e del Rule Correlator previsti dalla roadmap Astro-DSS.


---

# 2026-07-21 — ADR-015: deduplicazione spaziale SQL

## Stato operativo

L'import GeoNames è completato.

La tabella `localita` contiene 5.220.791 record e mantiene ricercabili
anche i centri abitati con popolazione minima o non valorizzata.

Componenti coinvolti:

- `www/api/ricerca_stream_api.php`;
- `www/includes/RicercaRSAirportRepository.php`;
- deduplicatore geografico PHP corrente.

Il file legacy `search_engine.php` non deve essere modificato.

Il Rule Engine e il motore astrologico rimangono congelati.

## Decisione architetturale

La deduplicazione spaziale verrà trasferita progressivamente dal livello
PHP al database PostgreSQL.

La decisione è formalizzata in ADR-015 e descritta nel documento
`docs/03_ARCHITECTURE_ASTROLAB.md`.

Non saranno introdotte soglie minime di popolazione.

Tutte le località attive devono rimanere ricercabili.

## Responsabilità congelate

- PostgreSQL applica filtri geografici, bucket e riduzione dei risultati.
- `RicercaRSAirportRepository` resta l'unico accesso alle sorgenti geografiche.
- PHP orchestra la ricerca, il Rule Engine, il calcolo e il ranking.
- Il Rule Engine non modifica il proprio comportamento.
- Il motore astrologico non modifica il proprio comportamento.
- La Streaming API trasmette i risultati senza reinterpretarli.
- Il frontend presenta i dati senza deduplicarli o ricalcolarli.

## Benchmark di riferimento

### Italia

- località grezze: 63.029;
- aeroporti: 56;
- bucket SQL delle località: 330;
- risultato finale PHP: 330;
- query: circa 977 ms;
- deduplicazione PHP: circa 153 ms;
- memoria: circa 34 MB.

### Italia, Francia e Germania

- località grezze: 226.829;
- aeroporti: 228;
- bucket SQL delle località: 1.191;
- risultato finale PHP: 1.213;
- query: circa 4,3 secondi;
- deduplicazione PHP: circa 533 ms;
- picco di memoria: circa 130 MB.

### Fascia longitudinale da -81 a -79

- località grezze: 39.178;
- aeroporti: 86;
- bucket SQL delle località: 496;
- risultato finale PHP: 499;
- query: circa 935 ms.

La riduzione osservata è superiore al 98% e raggiunge circa il 99,5%
negli scenari europei.

La differenza tra bucket delle sole località e risultato finale è
compatibile con la presenza e la precedenza degli aeroporti.

## Vincoli funzionali

La futura implementazione SQL deve garantire:

- nessuna soglia minima di popolazione;
- inclusione di tutte le località attive;
- bucket compatibili con il deduplicatore corrente;
- precedenza degli aeroporti;
- rappresentanti deterministici;
- risultato stabile a parità di input;
- contratto del Repository invariato;
- Streaming API invariata;
- assenza di regressioni astrologiche.

## Strategia di migrazione

### Fase 1 — Specifica SQL

- definire con precisione la formula dei bucket;
- definire l'ordinamento deterministico;
- definire il rappresentante di ciascun bucket;
- formalizzare la precedenza aeroporto-località;
- analizzare la query con `EXPLAIN (ANALYZE, BUFFERS)`.

### Fase 2 — Esecuzione parallela

- mantenere attivo il deduplicatore PHP;
- produrre in parallelo il risultato SQL;
- confrontare bucket, chiavi e rappresentanti;
- registrare ogni divergenza;
- utilizzare PHP come oracolo funzionale temporaneo.

### Fase 3 — Regressione

Verificare almeno:

- Italia;
- Italia, Francia e Germania;
- fascia longitudinale da -81 a -79;
- coordinate negative;
- confini dei bucket;
- meridiano 180 gradi;
- località senza popolazione;
- località omonime;
- aeroporto e località nello stesso bucket.

### Fase 4 — Benchmark

Misurare:

- Raspberry Pi;
- VPS;
- memoria PHP;
- righe trasferite PostgreSQL-PHP;
- tempo della query SQL;
- latenza end-to-end;
- comportamento con ricerche concorrenti.

### Fase 5 — Attivazione

- predisporre un rollback semplice;
- attivare la deduplicazione SQL;
- monitorare errori, memoria e latenze;
- rimuovere la deduplicazione PHP solo dopo equivalenza verificata.

## Stato Git da preservare

Prima del prossimo intervento risultano:

- modificato `www/includes/RicercaRSAirportRepository.php`;
- presente il backup non tracciato
  `www/includes/RicercaRSAirportRepository.php.bak`;
- modificati i documenti Astrolab;
- eliminati dal working tree alcuni documenti legacy senza suffisso Astrolab.

Le eliminazioni dei documenti legacy devono essere verificate prima del
commit e non devono essere ripristinate o confermate automaticamente.

## Punto di ripresa

Il prossimo intervento applicativo deve riguardare esclusivamente:

1. definizione della query SQL di deduplicazione;
2. implementazione nel Repository;
3. confronto automatico SQL-PHP;
4. benchmark;
5. verifica di equivalenza.

La deduplicazione PHP non deve essere eliminata prima della validazione
completa del risultato SQL.


## 2026-07-22 — Ricerca RS v2: deduplicazione spaziale SQL e autenticazione test

- Completata la migrazione della deduplicazione geografica dal livello PHP al Repository SQL (`www/includes/RicercaRSAirportRepository.php`).
- Verificata la compatibilità funzionale mediante regressione completa.
- Introdotto il helper condiviso `www/tests/search_auth.php` per i test delle Search API protette.
- Aggiornati tutti i test in `www/tests/search/` affinché creino una sessione autenticata e inviino il cookie `PHPSESSID` tramite `stream_context_create()`.
- La suite "Validazione ricerca API" è nuovamente completamente verde.
- Regressione completa (`www/tests/run.php`): OK.
- Commit: `44438f8` — Fix authenticated search API tests.

### Nota permanente

Le API di ricerca (`ricerca_stream_api.php` e `ricerca_griglia_api.php`) richiedono autenticazione.

Ogni nuovo test che invoca tali endpoint deve utilizzare il helper:

`www/tests/search_auth.php`

e non deve effettuare chiamate HTTP anonime tramite `file_get_contents()`.

Prossimo passo: proseguire con le ottimizzazioni della Ricerca RS mantenendo invariati i contratti pubblici e la copertura della regressione.



------------------------------------------------------------

## Avvio Ricerca RSM v3

Terminata con successo la migrazione della deduplicazione SQL degli
aeroporti e ripristinata la regressione completa della Search API.

Da questo punto inizia l'evoluzione della Ricerca RSM.

Obiettivo:

trasformare il sistema da ricerca limitata agli aeroporti ad una
ricerca globale delle località geografiche.

Le decisioni già prese sono:

- gli aeroporti rimangono pienamente supportati;
- le località diventeranno il nuovo oggetto principale della ricerca;
- aeroporti, eliporti e idroporti saranno informazioni opzionali
  associate alla località;
- la UI permetterà di scegliere il tipo di località da ricercare;
- il motore astrologico non verrà modificato;
- la regressione automatica dovrà rimanere completamente verde durante
  ogni fase dello sviluppo.

La prima attività prevista consiste nello studio del modello dati
GeoNames e nella progettazione del nuovo modello "Località".

## Handover — Ricerca RSM v3, Sprint 1 completato

La ricerca streaming usa ora `recuperaAeroportiDeduplicati()` invece della sequenza
`recuperaAeroporti()` + `deduplicaAeroporti()`.

Comportamento verificato:

- modalità legacy senza filtro geografico: 9.293 aeroporti grezzi, nessuna località;
- modalità v3 con IT/FR e intervallo longitudinale: 144.856 punti grezzi,
  dei quali 144.627 località;
- deduplicazione PHP e SQL identiche per conteggio, ordine e record selezionati;
- contratto SSE invariato;
- test autenticati superati.

Test permanente:

`www/tests/test_rsm_location_repository.php`

## 2026-07-22 — Ricerca RSM v3, FASE 2 completata

### Obiettivo

Completare il nuovo modello unificato `Località`, mantenendo compatibilità con
la ricerca aeroportuale esistente e spostando la deduplicazione geografica nel
Repository SQL.

### Risultato

- aeroporti e località GeoNames sono gestiti come punti geografici compatibili;
- il ramo legacy senza filtro geografico continua a restituire solo aeroporti;
- il ramo geografico include aeroporti e tutte le località attive;
- nessuna soglia minima di popolazione è stata introdotta;
- precedenza, conteggio, ordine e rappresentanti coincidono con la pipeline PHP;
- il contratto della Streaming API è rimasto invariato;
- il codice condiviso di preparazione dei filtri è stato consolidato in
  `preparaFiltriLocalita()`;
- la selezione storica dei rappresentanti resta legata all'ordine della query
  legacy, che non deve essere semplificato senza una nuova specifica funzionale.

### Test eseguiti

- `www/tests/test_rsm_location_repository.php`: OK;
- `www/tests/test_rsm_dedup_sequence.php`: PHP=851, SQL=851, sequenze identiche;
- confronto con `work_mem` 64kB, 1MB e 64MB: identico;
- confronto con `enable_incremental_sort = off`: identico;
- `git diff --check`: OK;
- working tree pulito dopo il commit.

### Commit Git

`abc54a9` — Aggiunge supporto località alla ricerca RS e test di regressione
sulla sequenza di deduplica.

### Stato della roadmap

- FASE 1: completata;
- FASE 2: completata;
- FASE 3: completata;
- FASE 4: completata.

### Chiusura FASE 3 — Backend

Completata l’introduzione del parametro opzionale `tipo_localita` con i valori:

- `solo_aeroporti`;
- `solo_localita`.

Il comportamento legacy è preservato quando il parametro è assente o non valido.
La deduplicazione PHP e SQL resta equivalente con 851 risultati su 851 e sequenza identica.
La suite completa è verde. Aggiunti inoltre i test dedicati `RULE-0001`–`RULE-0010`
e riallineati i test del Rule Engine al freeze ufficiale di 120 Rule.

### Chiusura FASE 4 — Query SQL unificata

Completato il contratto unificato dei candidati geografici.

Il repository restituisce ora, per aeroporti e località:

- coordinate;
- nome;
- tipo;
- nazione;
- popolazione, quando disponibile;
- aeroporto associato, quando disponibile;
- IATA, quando disponibile;
- ICAO, quando disponibile.

Per gli aeroporti, `popolazione` è `NULL` e `aeroporto_associato` coincide con
il nome del record. Per le località, IATA, ICAO e `aeroporto_associato` restano
`NULL`, poiché non è ancora definita una regola geografica di associazione.

Il contratto è propagato da `RicercaRSAirportRepository.php` a
`RicercaRSResultBuilder.php`, mantenendo la compatibilità con i record legacy
tramite valori `NULL`.

### Test eseguiti per la FASE 4

- `www/tests/test_rsm_location_repository.php`: OK;
- `www/tests/test_rsm_result_builder.php`: OK;
- `www/tests/test_rsm_dedup_sequence.php`: PHP=851, SQL=851, sequenze identiche;
- lint PHP dei file modificati: OK;
- `git diff --check`: OK.

### Commit Git FASE 4

`9fd1fbc` — Completa contratto query unificata FASE 4.

### Stato successivo

La FASE 5 — Interfaccia è completata.
Le evoluzioni successive dovranno essere pianificate in una nuova fase
senza modificare il motore astrologico né il comportamento legacy consolidato.

---

## 2026-07-22 — Ricerca RSM v3, FASE 5 completata

### Obiettivo

Completare l'interfaccia della Ricerca RSM v3, rendendo disponibile
la selezione tra sole località aeroportuali oppure sole località geografiche.

### Risultato

- aggiunto il filtro `tipo_localita` nell'interfaccia;
- il parametro viene trasmesso alla Streaming API;
- i risultati distinguono aeroporti e località geografiche;
- vengono mostrati il nome della località e la popolazione quando disponibile;
- IATA e ICAO sono visualizzati solo quando presenti;
- i collegamenti alla Rivoluzione Solare utilizzano la località selezionata;
- il comportamento legacy resta compatibile;
- il motore astrologico non è stato modificato.

### Verifiche

- lint PHP dei file interessati: OK;
- `git diff --check`: OK;
- working tree pulito dopo il rilascio;
- branch locale `main` allineato a `astro-val/main`.

### Riferimenti Git

- commit funzionale: `bad3026` — completa selezione e rendering località;
- merge su `main`: `49423d1`;
- tag: `rsm-v3-fase5-completata`.

### Stato della roadmap

- FASE 1: completata;
- FASE 2: completata;
- FASE 3: completata;
- FASE 4: completata;
- FASE 5: completata;
- FASE 6: completata;
- FASE 7: completata;
- FASE 5A: completata.

Il modello attuale supporta le modalità `aeroporti` e `localita`,
distinguendo i risultati tramite `origine_punto`.

La FASE 5A ha introdotto, per `localita`, la selezione obbligatoria
della nazione e il limite 50/100/150/Tutte. La modalità
`aeroporti` mantiene la ricerca mondiale e il comportamento legacy.

Implementazione principale: commit `50fd768`.
Integrazione nel ramo di sviluppo: commit `21d5bb0`.


### 2026-07-26 — Ricerca RSM v3: rifinitura UX avanzamento

- Componente: `www/ricerca.php`
- Obiettivo:
  - visualizzare la percentuale con due decimali;
  - mantenere visibile la barra di avanzamento al termine della ricerca.
- Risultato:
  - `setProgress()` ora mostra il valore con `toFixed(2)`;
  - al completamento definitivo la barra rimane visibile e viene portata al 100%.
- Test eseguiti:
  - `php -l` (Docker): OK;
  - `git diff --check`: OK.
- Commit Git:
  - `b25178b` — Ricerca: carica nazioni località in modo asincrono.

### 2026-07-26 — Ricerca RSM v3: caricamento asincrono nazioni

- Componenti:
  - `www/ricerca.php`;
  - `www/api/nazioni_localita_api.php`.
- Obiettivo:
  - alleggerire il rendering iniziale della pagina;
  - mantenere invariata la logica della ricerca.
- Risultato:
  - elenco nazioni rimosso dall'HTML iniziale;
  - recupero via AJAX all'avvio della pagina;
  - selettore popolato in background;
  - comportamento della ricerca invariato.
- Test eseguiti:
  - `php -l` (Docker): OK;
  - endpoint HTTP: 401 da anonimo;
  - query SQL: 248 nazioni restituite;
  - `git diff --check`: OK.
- Passo successivo:
  - sola manutenzione correttiva e futuri miglioramenti incrementali della Ricerca RSM v3.

### 2026-07-26 — Ricerca RSM v3: incremento tranche a 30.000

- Componenti:
  - `www/api/ricerca_stream_api.php`;
  - `www/ricerca.php`.
- Obiettivo:
  - aumentare la dimensione delle tranche da 20.000 a 30.000 località;
  - mantenere invariata l'architettura della ricerca progressiva.
- Motivazione:
  - scelta prudenziale rispetto all'alternativa da 40.000;
  - ambiente con `memory_limit=256M` e swap già in uso.
- Risultato:
  - limite e valore predefinito backend portati a 30.000;
  - dimensione blocco backend portata a 30.000;
  - parametro e fallback frontend allineati a 30.000.
- Test eseguiti:
  - `php -l` in Docker sui due file PHP: OK;
  - `git diff --check`: OK.
- Commit Git:
  - da eseguire.

### 2026-07-27 — Sessioni RSM: modifica e lettura delle note

- Componenti:
  - `www/rs.php`;
  - `www/api/sessioni_api.php`.
- Obiettivo:
  - consentire la modifica delle sessioni RSM salvate limitatamente al campo Note;
  - preservare integralmente tutti gli altri dati della sessione.
- Risultato:
  - aggiunta l'azione **Modifica** tra **Richiama** e **Cancella**;
  - la finestra di modifica permette di aggiornare esclusivamente il campo `note`;
  - l'API `modifica_rs` aggiorna solamente `note`, senza modificare anno, luogo, condizione, coordinate o altri dati salvati;
  - limite massimo delle note impostato a 500 caratteri;
  - aggiunto il contatore dei caratteri residui;
  - la tabella mostra un'anteprima delle note limitata a due righe;
  - aggiunta l'azione **Leggi tutto**, che riutilizza la finestra in modalità sola lettura;
  - finestra ridimensionabile e area testo dotata di scorrimento verticale.
- Verifiche eseguite:
  - lint PHP di `www/rs.php`: OK;
  - lint PHP di `www/api/sessioni_api.php`: OK;
  - verifica funzionale dell'interfaccia: OK;
  - modifica delle note senza alterazione degli altri campi salvati: OK.
- Commit Git:
  - da eseguire.

### 29-07-2026 — Documentazione e manutenzione repository

- Componenti:
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/campagna-promozionale-e-sicurezza.md`;
  - `STRUTTURA_astrolab.md`;
  - repository Git.

- Obiettivo:
  - separare la roadmap della funzionalità di registrazione utenti dalla documentazione generale;
  - aggiungere un documento personale per l'organizzazione della campagna promozionale;
  - salvare uno snapshot aggiornato della struttura del progetto;
  - eliminare file di backup obsoleti e sincronizzare il repository con GitHub.

- Risultato:
  - creata la roadmap dedicata alla registrazione utenti;
  - rimossa la documentazione obsoleta sulla struttura applicativa;
  - eliminati i backup non più necessari di `docker-compose`;
  - aggiunto il documento `docs/campagna-promozionale-e-sicurezza.md`;
  - aggiunto il documento `STRUTTURA_astrolab.md`;
  - repository sincronizzato con `origin/main` e working tree pulito.

- Verifiche eseguite:
  - `git status`: OK;
  - `git log --oneline`: OK;
  - `git pull --rebase origin main`: OK;
  - `git push origin main`: OK.

- Commit Git:
  - `5817b84` — docs: aggiunge roadmap registrazione utenti e aggiorna php.ini;
  - `eb95d24` — docs: rimuove documentazione obsoleta sulla struttura applicativa;
  - `cc56bca` — chore: rimuove backup obsoleti di docker-compose;
  - `3a7e0ff` — docs: aggiunge piano campagna promozionale e sicurezza;
  - `93e2819` — docs: aggiunge snapshot della struttura del progetto.

- Passo successivo:
  - proseguire con lo sviluppo funzionale mantenendo una roadmap dedicata per ogni nuova funzionalità e aggiornando cronologicamente questo HANDOVER.

### 29-07-2026 — ADR-016 registrazione, piani e permessi utenti

- Componenti:
  - `docs/ADR_INDEX_ASTROLAB.md`;
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/HANDOVER_OPERATIVO_astrolab.md`.

- Obiettivo:
  - completare la Fase 1 del nuovo sistema utenti introducendo il modello dati e validando la migrazione PostgreSQL.

- Risultato:
  - completata la migrazione `sql/002_registrazione_utenti.sql`;
  - create le tabelle `piani` e `piano_limiti`;
  - estesa la tabella `utenti` con ruolo aggiornato, stato account, piano e campi di verifica email;
  - migrati gli utenti esistenti al ruolo `user` e al piano `supporter`;
  - verificati vincoli, indici, chiavi esterne e login applicativo;
  - roadmap aggiornata con il completamento della Fase 1.

- Verifiche eseguite:
  - migrazione validata su database temporaneo: OK;
  - migrazione applicata al database operativo: OK;
  - verifica funzionale dell'applicazione: OK;
  - `git diff --check`: OK.

- Commit Git:
  - `b00c2bd` — fix: correggi ordine vincolo ruolo nella migrazione utenti.

- Passo successivo:
  - avviare la Fase 2 aggiornando `www/includes/Auth.php`;
  - introdurre helper centralizzati per piani, limiti e permessi.


## 2026-07-29 — Completamento Fase 3 registrazione pubblica

- Componente modificato:
  - `www/includes/Auth.php`;
  - `www/registrazione.php`;
  - `www/tests/run.php`;
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/HANDOVER_OPERATIVO_astrolab.md`.
- Obiettivo:
  - implementare la registrazione pubblica degli utenti con validazioni e protezioni server-side.
- Risultato:
  - implementato `Auth::registraUtentePubblico()`;
  - creata la pagina pubblica `www/registrazione.php`;
  - validate username, email, password e conferma password;
  - assegnati esclusivamente dal server ruolo `user`, piano `free` e stato `pending_email`;
  - impedita l'escalation di ruolo, piano o permessi dal client;
  - introdotte protezione CSRF e limitazione delle registrazioni ripetute;
  - gestiti i duplicati di username ed email;
  - evitato `session_start()` durante l'esecuzione CLI dei test.
- Test eseguiti:
  - controllo sintassi PHP su `www/includes/Auth.php`: OK;
  - controllo sintassi PHP su `www/tests/run.php`: OK;
  - test dedicati della registrazione pubblica: OK;
  - regressione completa con `www/tests/run.php`: OK;
  - nessun warning residuo.
- Commit Git:
  - da eseguire dopo la verifica finale della documentazione e del diff.
- Fase successiva completata:
  - verifica email;
  - nuovo token verifica;
  - reset password;
  - token monouso;
  - regressione completa superata.
- Prossimo passo:
  - integrazione servizio email reale e gestione template notifiche.

## 2026-07-30 — Completamento Fase 5 amministrazione utenti

- Componenti modificati:
  - `www/includes/Auth.php`;
  - `www/admin_utenti.php`;
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/HANDOVER_OPERATIVO_astrolab.md`.
- Obiettivo:
  - allineare l'amministrazione utenti al modello definitivo basato su ruolo, stato account, piano e verifica email.
- Risultato:
  - sostituito il ruolo legacy `astrologo` con `user`;
  - validati lato server i soli ruoli `admin` e `user`;
  - estese lista e dettaglio utenti con `account_status`, `email_verified_at`, `plan_id` e piano;
  - sincronizzati sospensione e riattivazione con `attivo`, `account_status`, `suspended_at` e `suspension_reason`;
  - allineata l'interfaccia amministrativa al nuovo modello utenti;
  - verificata la creazione degli utenti amministrativi con stato `active`, email verificata e piano `supporter`.
- Verifiche eseguite:
  - lint PHP di `www/includes/Auth.php`: OK;
  - lint PHP di `www/admin_utenti.php`: OK;
  - test funzionale sospensione e riattivazione: OK;
  - test lettura lista utenti e piani: OK;
  - regressione completa con `www/tests/run.php`: OK;
  - `git diff --check`: OK.
- Commit Git:
  - da eseguire con la chiusura della Fase 5 e l'avvio della Fase 6.
- Prossimo passo:
  - avviare la Fase 6 — Limite soggetti;
  - contare i soggetti dell'utente prima dell'inserimento;
  - applicare il limite effettivo del piano;
  - escludere gli amministratori dal limite commerciale;
  - aggiungere test dedicati e regressione.

## 2026-07-30 — Implementazione Fase 6 limite soggetti

- Componenti modificati:
  - `sql/004_popola_piano_limiti.sql`;
  - `www/api/soggetti_api.php`;
  - `www/tests/test_subjects_limit.php`;
  - `www/tests/run.php`;
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/HANDOVER_OPERATIVO_astrolab.md`.
- Obiettivo:
  - applicare lato server il numero massimo di soggetti previsto dal piano dell'utente.
- Risultato:
  - configurato `subjects_max = 2` per il piano `free`;
  - configurato `subjects_max = NULL` per il piano `supporter`;
  - conteggiati i soggetti dell'utente prima dell'inserimento;
  - bloccato l'inserimento oltre soglia con errore JSON comprensibile;
  - preservate consultazione, modifica ed eliminazione dei soggetti esistenti;
  - esclusi gli amministratori dal limite commerciale.
- Verifiche eseguite:
  - lint PHP di `www/api/soggetti_api.php`: OK;
  - lint PHP di `www/tests/test_subjects_limit.php`: OK;
  - test funzionale HTTP del limite soggetti: OK;
  - verificato che il terzo soggetto del piano `free` riceva HTTP 400;
  - verificato che il numero dei soggetti resti pari a 2;
  - test dedicato integrato in `www/tests/run.php`;
  - regressione completa con `www/tests/run.php`: OK;
  - verifica del diff applicativo: OK.
- Stato:
  - Fase 6 completata.
- Commit Git:
  - da eseguire con la chiusura formale della Fase 6.
- Prossimo passo:
  - avviare la Fase 7 — Limite ricerche salvate.


## 2026-07-30 — Implementazione Fase 8 restrizioni ricerca lato server

- Componenti modificati:
  - `www/includes/Auth.php`;
  - `www/api/ricerca_stream_api.php`;
  - `www/api/cuspidi_search_api.php`;
  - `www/api/ricerca_griglia_api.php`;
  - `www/ricerca.php`;
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/HANDOVER_OPERATIVO_astrolab.md`.

- Obiettivo:
  - applicare lato server le restrizioni delle funzionalità di ricerca previste dal piano utente;
  - mantenere disponibili nel piano `free` gli aeroporti e la visualizzazione delle nazioni;
  - riservare al piano `supporter` le funzioni avanzate.

- Risultato:
  - introdotto il controllo centralizzato delle feature tramite `Auth::hasFeature()`;
  - bloccata lato server la ricerca delle località per utenti non Supporter;
  - bloccata lato server la ricerca a griglia per utenti non Supporter;
  - bloccata lato server l'espansione dinamica dell'orbe per utenti non Supporter;
  - mantenuta disponibile la ricerca aeroporti nel piano gratuito;
  - mantenuta visibile la lista nazioni senza consentire la ricerca località nel piano gratuito;
  - aggiunto messaggio standard:
    - `Questa funzione è riservata agli utenti del piano Supporter.`;
  - sincronizzata la UI con i permessi disponibili dell'utente.

- Verifiche eseguite:
  - lint PHP di `www/includes/Auth.php`: OK;
  - lint PHP di `www/api/ricerca_stream_api.php`: OK;
  - lint PHP di `www/api/cuspidi_search_api.php`: OK;
  - lint PHP di `www/api/ricerca_griglia_api.php`: OK;
  - lint PHP di `www/ricerca.php`: OK;
  - verifica presenza controlli `locality_search`, `grid_search`, `dynamic_orb`: OK.

- Stato:
  - Fase 8 completata.

- Verifiche finali:
  - test dedicato griglia Amore 2026: OK;
  - regressione completa con `www/tests/run.php`: OK;
  - ricerca API standard: OK;
  - ricerca griglia: OK;
  - registrazione utenti: OK;
  - limiti piano free: OK.

- Correzione aggiuntiva:
  - aggiornato `www/tests/search_auth.php`;
  - la sessione dei test ora usa il piano reale dal database;
  - normalizzazione del piano in minuscolo per allineamento con `Auth::hasFeature()`.

- Commit Git:
  - da eseguire dopo verifica finale:
    - `git diff --check`;
    - `git status`.

- Prossimo passo:
  - chiusura formale Fase 8;
  - commit delle modifiche.

## 2026-07-31 — Completamento Fase 10 Annual Report, stampa e PDF

- Componenti modificati:
  - `sql/006_quota_esportazioni_report.sql`;
  - `www/includes/AnnualReportExportQuota.php`;
  - `www/tests/test_annual_report_export_quota.php`;
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/HANDOVER_OPERATIVO_astrolab.md`.

- Obiettivo:
  - applicare al piano `free` una quota condivisa mensile di 3 stampe o esportazioni dell’Annual Report;
  - mantenere disponibile la visualizzazione del report;
  - evitare conteggi duplicati della stessa esportazione.

- Risultato:
  - introdotta la persistenza degli utilizzi mensili delle esportazioni;
  - implementata la gestione centralizzata della quota tramite `AnnualReportExportQuota`;
  - registrati gli utilizzi in modo transazionale;
  - verificata l’idempotenza prima del controllo di esaurimento della quota;
  - evitato il doppio conteggio della stessa operazione;
  - mantenuto senza limite commerciale il piano `supporter`;
  - resa disponibile la quota residua per il piano gratuito.

- Verifiche eseguite:
  - controllo sintassi PHP: OK;
  - test `ANNUAL REPORT EXPORT QUOTA`: OK;
  - test `ANNUAL REPORT BROWSER PRINT`: OK;
  - test `ANNUAL REPORT DETERMINISM`: OK;
  - `git diff --check`: OK.

- Stato:
  - Fase 10 completata.

- Commit Git:
  - da eseguire dopo la verifica finale del diff e dello stato del repository.

- Prossimo passo:
  - avviare la Fase 11 — Restrizioni interfaccia.

## 2026-07-31 — Completamento Fase 11 Restrizioni interfaccia

- Componenti modificati:
  - `www/ricerca.php`;
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/HANDOVER_OPERATIVO_astrolab.md`.

- Obiettivo:
  - disabilitare preventivamente nell'interfaccia i controlli non disponibili per il piano utente;
  - mantenere il server come fonte definitiva dell'autorizzazione.

- Risultato:
  - aggiunta l'inizializzazione centralizzata delle restrizioni UI;
  - disabilitate le opzioni Supporter per ricerca località, ricerca a griglia ed espansione automatica dell'orbe;
  - mantenuti i controlli server-side e i messaggi già presenti.

- Verifiche eseguite:
  - lint PHP di `www/ricerca.php`: OK;
  - regressione completa `tests/run.php`: OK;
  - `git diff --check`: OK.

- Stato:
  - Fase 11 completata.

- Commit Git:
  - da eseguire dopo verifica finale di `git diff` e `git status`.

- Prossimo passo:
  - avviare la Fase 12 — Sicurezza e sessioni.

## 2026-07-31 — Completamento Fase 12 Sicurezza e sessioni

- Componenti modificati:
  - `www/admin_utenti.php`;
  - `www/cambia_password.php`;
  - `www/login.php`;
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/HANDOVER_OPERATIVO_astrolab.md`.

- Obiettivo:
  - consolidare la sicurezza delle operazioni sensibili e delle sessioni.

- Risultato:
  - introdotta la protezione CSRF nelle principali operazioni amministrative;
  - aggiunta la protezione CSRF al cambio password;
  - confermata la protezione CSRF della registrazione;
  - introdotto rate limiting del login;
  - mantenuto il rate limiting della registrazione;
  - verificato l'utilizzo dell'hashing password;
  - verificati cookie di sessione, rigenerazione ID e logout;
  - verificati i messaggi del login contro l'enumerazione delle credenziali;
  - verificati i token monouso di verifica email e reset password;
  - verificata la regressione completa;
  - rinviati a futuro hardening il logging strutturato e ulteriori test HTTP dedicati.

- Verifiche eseguite:
  - lint PHP dei file modificati: OK;
  - regressione completa `tests/run.php`: OK;
  - `git diff --check`: OK.

- Stato:
  - Fase 12 completata.

- Commit Git:
  - da eseguire dopo verifica finale di `git diff` e `git status`.

- Prossimo passo:
  - avviare la Fase 13 — Regressione e documentazione.

## 2026-08-01 — Completamento Fase 13 Regressione e documentazione

- Componenti verificati:
  - `www/admin_utenti.php`;
  - `www/cambia_password.php`;
  - `www/login.php`;
  - suite `www/tests/run.php`.

- Documentazione aggiornata:
  - `docs/roadmap_registrazioneutenti.md`;
  - `docs/HANDOVER_OPERATIVO_astrolab.md`;
  - ulteriori documenti ufficiali da allineare nella stessa iterazione.

- Obiettivo:
  - completare la regressione finale della macro-funzionalità registrazione, piani, permessi, limiti e sicurezza;
  - consolidare la documentazione ufficiale senza modifiche applicative.

- Risultato:
  - lint PHP eseguito nel container `astrolab-web` sui file modificati nella Fase 12;
  - test specifici eseguiti tramite la suite di regressione;
  - regressione completa `tests/run.php` superata;
  - `git diff --check` superato;
  - repository inizialmente pulito;
  - confermato il commit `85311db` per la Fase 12;
  - nessun refactoring e nessuna modifica fuori obiettivo.

- Verifiche eseguite:
  - `php -l admin_utenti.php`: OK;
  - `php -l cambia_password.php`: OK;
  - `php -l login.php`: OK;
  - `php tests/run.php`: OK;
  - `git diff --check`: OK.

- Stato:
  - Fase 13 completata;
  - macro-funzionalità registrazione utenti completata.

- Commit Git:
  - da eseguire esclusivamente dopo verifica finale di documentazione, `git diff --check`, `git diff`, `git status` e conferma dell’utente.

- Prossimo passo:
  - completare l’allineamento di ADR, README, START_HERE e ROADMAP;
  - eseguire le verifiche finali;
  - richiedere conferma prima del commit.

## 2026-08-07 — Codifica colore semantica dei pianeti nella ruota zodiacale

- Componente modificato:
  - `www/js/zodiac_wheel.js`

- Obiettivo:
  - implementare la codifica colore diretta sulla mappa/ruota:
    - rosso (`#CC0000`) = pianeta in moto diretto;
    - blu (`#0000CC`) = pianeta retrogrado;
    - verde (`#00AA00`) = pianeta esattamente in cuspide;
  - sovrascrivere i colori identitari dei pianeti in base al loro stato dinamico;
  - mantenere invariata la logica astrologica ereditata da Astro-Val;
  - non modificare il motore astronomico, il Rule Engine o il backend.

- Risultato:
  - aggiunto il metodo helper `_coloreSemantico(p, houses)` in `ZodiacWheel`;
  - implementata la verifica di congiunzione stretta con le cuspidi (tolleranza 0.01°);
  - sostituiti i colori statici `PIANETI_COLORI` con la codifica semantica in tre metodi di rendering:
    - `_disegnaAspetti` (pallino sugli aspetti);
    - `_disegnaLineePianetiModificato` (linea guida);
    - `_disegnaPianetiModificato` (glifo principale e testo gradi);
  - preservata la compatibilità con tutte le pagine che utilizzano `ZodiacWheel.disegna()`:
    - `rilocazione.php`;
    - `rs.php`;
    - `compare_rs.php`;
    - `compare_ril.php`;
  - nessuna modifica al backend, alle API o al motore astrologico.

- Verifiche eseguite:
  - `node --check www/js/zodiac_wheel.js`: OK;
  - `git diff --check`: OK;
  - regressione backend `tests/run.php`: OK (sezione "Validazione casi JSON" superata);
  - nota: il fatal error `passthru()` nella sezione "Validazione rivoluzioni lunari" è ambientale e preesistente, non collegato alla modifica JavaScript.

- Stato:
  - codifica colore semantica implementata e verificata;
  - ready per test manuale nell'interfaccia web.

- Commit Git:
  - da eseguire dopo conferma dell'utente.

- Prossimo passo:
  - testare la visualizzazione nella pagina `rilocazione.php` per verificare che i colori siano applicati correttamente;
  - confermare che la codifica verde (cuspide) si attivi solo per congiunzioni estremamente strette (< 0.01°);
  - procedere con il commit dopo verifica funzionale.

## 2026-08-07 bis — Rifinitura codifica colore: tolleranza cuspide e grassetto

- Componente modificato:
  - `www/js/zodiac_wheel.js`

- Obiettivo:
  - allineare la soglia di rilevamento della congiunzione pianeta-cuspide
    alla convenzione astrologica (orbite inferiori a 2° sono già congiunzioni);
  - rendere immediatamente distinguibili i pianeti in cuspide aggiungendo
    il grassetto al glifo quando il colore semantico è verde.

- Risultato:
  - soglia di congiunzione a cuspide portata da 0.01° a 0.5° (30 primi);
  - glifo del pianeta reso in grassetto quando `colore === '#00AA00'`;
  - mantenuta invariata la codifica rosso (diretto) / blu (retrogrado);
  - nessuna modifica al backend, al motore astrologico o al Rule Engine;
  - riutilizzata la logica esistente in `_coloreSemantico()` e `_disegnaPianetiModificato()`.

- Verifiche eseguite:
  - `node --check www/js/zodiac_wheel.js`: OK;
  - `git diff --check`: OK;
  - sintassi PHP non toccata (nessun file backend modificato).

- Stato:
  - rifinitura applicata e verificata;
  - pronta per test manuale nell'interfaccia web.

- Commit Git:
  - da eseguire dopo conferma dell'utente.

- Prossimo passo:
  - testare la visualizzazione in `rilocazione.php` e `rs.php`;
  - verificare che Giove a 24°10'33" Sagittario con cuspide X casa a 24°10'00"
    Sagittario venga ora correttamente renderizzato in verde e in grassetto.

## 2026-08-07 ter — Orbite differenziate per codifica colore cuspide

- Componente modificato:
  - `www/js/zodiac_wheel.js`

- Obiettivo:
  - implementare orbite differenziate per la rilevazione della congiunzione
    pianeta-cuspide nella codifica colore semantica della ruota zodiacale;
  - applicare le soglie reali comunicate dall'utente:
    - tutti i pianeti: orbita massima di 2.5° su qualsiasi cuspide;
    - Marte (id=4) e Saturno (id=6): orbita di 10° solo su casa I/ASC e X/MC;
    - Marte e Saturno su tutte le altre case: stessa orbita degli altri pianeti (2.5°).

- Risultato:
  - sostituita la soglia fissa di 0.5° con logica dinamica basata su pianeta e casa;
  - definiti i parametri configurabili in `_coloreSemantico()`:
    - `SOGLIA_BASE = 2.5` (tutti i pianeti, tutte le case);
    - `SOGLIA_ANGOLI = 10.0` (Marte e Saturno su I/ASC e X/MC);
    - `PIANETI_ANGOLI = [4, 6]` (Marte e Saturno);
    - `CASE_ANGOLI = ['1', 'ASC', '10', 'MC']` (casa I=ASC, casa X=MC);
  - mantenuta invariata la codifica rosso (diretto) / blu (retrogrado) / verde (cuspide);
  - mantenuto il grassetto per i pianeti in cuspide (verde);
  - nessuna modifica al backend, al motore astrologico o al Rule Engine.

- Verifiche eseguite:
  - `node --check www/js/zodiac_wheel.js`: OK;
  - `git diff --check`: OK;
  - sintassi PHP non toccata (nessun file backend modificato).

- Stato:
  - orbite differenziate applicate e verificate;
  - pronta per test manuale nell'interfaccia web.

- Commit Git:
  - da eseguire dopo conferma dell'utente.

- Prossimo passo:
  - testare la visualizzazione in `rilocazione.php` e `rs.php`;
  - verificare che Marte e Saturno siano verdi entro 10° da ASC/MC;
  - verificare che tutti gli altri pianeti siano verdi entro 2.5° da qualsiasi cuspide.

2026-08-07 — Creazione roadmap comparazione Astrolab / MyAstral.org
Componenti modificati:
`docs/roadmap_comparazione_myastral.md`;
`docs/ROADMAP.md`;
`docs/START_HERE.md`;
`docs/HANDOVER_OPERATIVO_astrolab.md`.
Obiettivo:
creare la roadmap ad-hoc dedicata alla comparazione funzionale tra
Astrolab e MyAstral.org, collegandola alla roadmap principale e
allineando tutta la documentazione di progetto.
Risultato:
creata `docs/roadmap_comparazione_myastral.md` con:
scopo e principi guida;
prerequisiti (account MyAstral.org);
inventario completo dei 5 livelli di filtro Astrolab;
4 macro-attività pianificate (M1 RSM, M2 RL, M3 Tema Natale, M4 Sinastria esclusa);
relazione con la documentazione UX esistente;
vincoli tecnici;
condizioni di successo;
registro decisioni iniziale;
aggiornata `docs/ROADMAP.md` con riferimento alla nuova roadmap;
aggiornata `docs/START_HERE.md` con riferimento alla nuova roadmap;
aggiornato questo HANDOVER con la presente voce.
Test eseguiti:
nessun file PHP modificato;
verifica contenuto documenti: OK;
`git diff --check`: da eseguire.
Commit Git:
da eseguire dopo verifica finale.
Prossimo passo:
acquistare account MyAstral.org;
definire soggetti e anni RS di test;
avviare M1 — Comparazione ricerche RSM.

## 2026-08-09 — Creazione roadmap Menu Aiuto e Manuale d'Uso

- Componenti modificati:
  - `docs/roadmap_aiuto.md` (creato);
  - `docs/ROADMAP.md` (aggiornato).

- Obiettivo:
  - progettare l'organizzazione del menu "Aiuto" e del manuale d'uso dell'applicazione;
  - definire le voci e le sezioni del manuale;
  - creare una roadmap dedicata (`docs/roadmap_aiuto.md`) senza appesantire la roadmap principale.

- Risultato:
  - creata la struttura logica del manuale in 8 sezioni principali (Introduzione e Account, Gestione Soggetti, Calcoli e Analisi, Ricerca Geografica, Report, Comparatore, Interfaccia, FAQ);
  - creato `docs/roadmap_aiuto.md` con fasi di sviluppo future;
  - aggiornato `docs/ROADMAP.md` inserendo un breve rimando alla roadmap dedicata;
  - nessuna modifica applicativa, architetturale o al Rule Engine.

- Verifiche eseguite:
  - `git status`: file tracciati correttamente;
  - `git diff docs/ROADMAP.md`: diff minimale e corretto;
  - `git diff --check`: nessun problema rilevato.

- Stato:
  - struttura del menu Aiuto definita e documentata;
  - pronta per future iterazioni di sviluppo UX e stesura dei contenuti.

- Commit Git:
  - da eseguire dopo conferma dell'utente.

- Prossimo passo:
  - analizzare le interfacce correnti per mappare i link del menu Aiuto alle pagine specifiche;
  - avviare la stesura dei contenuti testuali per le prime sezioni.

## 2026-08-09 bis — Analisi e mappatura Menu Aiuto sulle pagine esistenti

- Componente modificato:
  - `docs/roadmap_aiuto.md` (aggiornato).

- Obiettivo:
  - mappare le sezioni del manuale d'uso rispetto alle pagine PHP reali dell'applicazione;
  - definire la strategia di implementazione dell'interfaccia per il menu Aiuto.

- Risultato:
  - completata la mappatura tra le 8 sezioni del manuale e i file PHP principali (`index.php`, `tema.php`, `rs.php`, `rl.php`, `rilocazione.php`, `ricerca.php`, `stampa.php`, `compare_rs.php`, `compare_ril.php`);
  - scelta la strategia **Modale/Popup contestuale JS** per non interrompere il workflow dell'astrologo;
  - nessuna modifica applicativa o al Rule Engine.

- Verifiche eseguite:
  - `git diff --check`: nessun problema rilevato;
  - `git status`: file tracciato correttamente.

- Stato:
  - fase di analisi e progettazione completata;
  - pronto per lo sviluppo dell'infrastruttura JS/PHP del modale Aiuto.

- Commit Git:
  - da eseguire dopo conferma dell'utente.

- Prossimo passo:
  - creare il file JS per il modale contestuale (`www/js/help_modal.js`);
  - creare il file CSS per lo styling (`www/css/help_modal.css`);
  - integrare il trigger nel menu di navigazione (`header_nav.php`).

## 2026-08-09 ter — Implementazione modale Aiuto nell'interfaccia

- Componenti modificati:
  - `www/includes/header_nav.php` (CSS inline, trigger menu, markup modale, script tag);
  - `www/css/help_modal.css` (creato);
  - `www/js/help_modal.js` (creato).

- Obiettivo:
  - integrare il trigger "Aiuto" nel menu di navigazione principale;
  - implementare il modale contestuale con struttura HTML/CSS/JS;
  - rendere il modale disponibile in tutte le pagine protette.

- Risultato:
  - aggiunto blocco CSS del modale nel `<style>` di `header_nav.php`;
  - inserito trigger `❓ Aiuto` dopo "Ricerca Località" nel menu principale;
  - aggiunto markup HTML del modale con overlay, header, corpo e pulsante di chiusura;
  - creato `www/js/help_modal.js` con logica open/close (ESC, click esterno, pulsante X);
  - nessuna modifica al backend, al motore astrologico o al Rule Engine.

- Verifiche eseguite:
  - `php -l www/includes/header_nav.php`: OK (nessun errore di sintassi);
  - `git diff --check`: nessun problema rilevato;
  - regressione: errore preesistente `passthru()` in `tests/run.php:278` non correlato alle modifiche attuali.

- Stato:
  - modale Aiuto integrato e visibile in tutte le pagine protette;
  - contenuto attualmente placeholder (in attesa dei testi del manuale);
  - pronto per test manuale nell'interfaccia web.

- Commit Git:
  - da eseguire dopo conferma dell'utente.

- Prossimo passo:
  - testare manualmente l'apertura/chiusura del modale nel browser;
  - avviare la stesura dei contenuti testuali per le sezioni del manuale.

## 2026-08-09 quater — Indagine errore passthru() e test search (fix rimandato)

- Problema: `tests/run.php` usa `passthru()` per lanciare i sotto-test; la funzione è disabilitata dal 2 agosto in `php-config/php.ini` (indurimento sicurezza). Da qui il fatal error prima inesistente.
- Soluzione operativa (sicura, senza toccare il php.ini montato):
  `docker compose exec -T astrolab-web php -d disable_functions= tests/run.php`
  L'override vale solo per il processo CLI effimero; il server web resta indurito.
- Esito con override: backend, casi JSON, RL e rilocazione PASSANO; restano fallimenti nei test `search/`.
- Causa test search (diagnosticata, fix RIMANDATO su richiesta): `search_auth.php` forza `session_name('PHPSESSID')` e un ID custom, ma il php.ini indurito usa `session.name = ASTROSESSID` e `session.use_strict_mode = 1`. L'ID custom viene rifiutato (file sessione non creato) e Apache attende il cookie ASTROSESSID, non PHPSESSID.
- Fix da fare in futuro: leggere `ini_get('session.name')` invece di hardcodare PHPSESSID, inviare il cookie con quel nome e disattivare `use_strict_mode` solo nel processo di seeding CLI.
- Stato: nessun file applicativo modificato in questa indagine; suite eseguibile con override tranne search.

## 2026-08-09 quinquies — Sezione 1 Manuale Aiuto: Introduzione e Account

- Creato `www/js/help_content_s1.json`: contenuti testuali per login, registrazione, cambio password e default.
- Modificato `www/js/help_modal.js`: caricamento dinamico dei contenuti in base alla pagina corrente (rilevamento URL).
- Contenuti redatti sulla base dell'analisi di `login.php`, `registrazione.php`, `cambia_password.php`.
- Roadmap aggiornata: Fase 3 parzialmente completata (Sezione 1 fatta, Sezione 2 da fare).
- Nessun cambiamento al backend o al motore astrologico.

## 2026-08-09 sexies — Riprogettazione UX Menu Aiuto (dropdown + modale per sezione)

- Modificato `header_nav.php`: trigger `? Aiuto` trasformato in dropdown con 8 voci della roadmap.
- Riscritto `help_modal.js`: nuova funzione `openHelpSection(n)` che carica `help_content_sN.json` e apre il modale con il contenuto della sezione scelta.
- Sezioni non ancora redatte mostrano placeholder "in fase di redazione".
- Chiusura dropdown: click fuori, ESC, o apertura modale.
- Roadmap aggiornata: Fase 2 completata con nuova UX.
- Nessun cambiamento al backend o al motore astrologico.

## 2026-08-09 septies — Cambio strategia Menu Aiuto: dropdown + pagine PHP dedicate

- Abbandonato approccio modale JS (problemi CSS con dropdown).
- Adottato dropdown navbar (classi nav-dropdown come Rivoluzioni) con 8 voci.
- Ogni voce linka a pagina PHP dedicata (target=_blank): help_account.php, help_soggetti.php, help_calcoli.php, help_ricerca.php, help_report.php, help_comparatore.php, help_interfaccia.php, help_faq.php.
- help_account.php: contenuti completi Sezione 1 (login, registrazione, cambio password, piani, sicurezza).
- Sezioni 2-8: pagine placeholder "in fase di redazione".
- Tasto destro disabilitato su tutte le pagine help (JS contextmenu + body oncontextmenu).
- header_nav.php: dropdown aggiornato con link corretti.
- Roadmap aggiornata con nuova strategia.
- Nessun cambiamento al backend o al motore astrologico.

## 2026-08-10 — Fix dropdown "? Aiuto" duplicato e ricostruzione pulita come "Help"

- Problema segnalato: trigger "? Aiuto" ancora visibile in navbar nonostante rimozione da tutti i sorgenti (verificato: nessuna occorrenza in nessun file .php/.css/.js), persistente su più browser e dopo svuotamento cache.
- Diagnosi: `header_nav.php` conteneva DUE blocchi dropdown "Aiuto" duplicati e mai ripuliti dopo il cambio strategia (voce "septies" sopra) — uno con link a pagine `help_*.php`, uno con `onclick` verso il modale mai adottato. La causa della persistenza visiva era però `opcache.validate_timestamps=Off` + `opcache.revalidate_freq=0` nel container `astrolab-web`: il bytecode compilato non veniva mai rivalidato contro il file sorgente, quindi ogni modifica ai .php restava invisibile finché il container non veniva riavviato.
- Fix (commit `d4622e6`, pushato su `origin/fase9-comparator-quota`):
  - rimossi entrambi i blocchi duplicati del dropdown, il CSS dedicato (`.help-trigger`, `.help-dropdown*`, `.help-modal*`), il modale HTML condiviso e l'include di `www/js/help_modal.js`;
  - eliminati anche `www/css/help_modal.css` e `www/js/help_content_s1.json` (orfani, nessun riferimento residuo);
  - riavviato `astrolab-web` per invalidare OPcache e confermare la scomparsa della voce.
- Ricostruzione pulita del dropdown Help (in corso di commit):
  - un solo blocco dropdown, classi `nav-dropdown` condivise con "Rivoluzioni" per uniformità grafica;
  - trigger rinominato da "Aiuto" a **"Help"**, colore testo `#D4C9A8` (coerente con `.soggetto-attivo`), `background: none; border: none;` per evitare lo stile di default del `<button>` del browser quando non attivo/hover;
  - 8 voci verso le pagine `help_*.php` esistenti (help_account.php con contenuto reale, le altre 7 placeholder), invariate.
- Roadmap aggiornata: `docs/roadmap_aiuto.md`, Fase 2 e sezione "Strategia di Implementazione Interfaccia" allineate allo stato reale (pagine dedicate confermate come scelta definitiva, modale definitivamente rimosso).
- Verifiche eseguite:
  - `docker compose exec -T astrolab-web php -l www/includes/header_nav.php`: OK ad ogni step;
  - `git diff --check`: OK (corrette anche 2 righe di trailing whitespace preesistenti);
  - verifica manuale nel browser dopo riavvio container: voce "Aiuto" sparita, dropdown "Help" funzionante e graficamente uniforme a "Rivoluzioni".
- Nessun cambiamento al backend o al motore astrologico.

- Nota operativa permanente: la configurazione OPcache attuale (`validate_timestamps=Off`) è corretta per produzione ma in ambiente di sviluppo richiede il riavvio di `astrolab-web` dopo ogni modifica a file `.php` per vedere l'effetto — da tenere a mente per i prossimi cicli di sviluppo/debug su questo container.

## 2026-08-11 — Rimozione airports.csv dal tracking git

- Verificato che `airports.csv` (13 MB, al root del repo dal commit iniziale `af1716a`, ereditato dal progetto precedente `astro-dev`) non è referenziato da nessun file `.php`/`.py`/`.sh` del progetto: l'atlante geografico attuale (ricerca RSM per aeroporti e località) è alimentato dalla pipeline GeoNames (`import/convert_geonames.py`, `import/import_geonames.sh`) e legge dalla tabella Postgres `aeroporti` (84.616 righe, già popolata e persistita nel volume Docker), non dal CSV.
- Il file resta comunque disponibile in chiaro nella storia git (commit `af1716a` e precedenti) in caso di necessità di riferimento futuro.
- Azione: `git mv` → `git rm --cached`, file spostato fisicamente in `import/data/airports.csv` (cartella già esclusa da `.gitignore`, coerente con gli altri dataset non versionati tipo GeoNames), rimosso dal tracking.
- Aggiornato `STRUTTURA_astrolab.md` (riga spostata da root a `import/data/`).
- Nessun cambiamento a codice applicativo, schema DB o motore astrologico. Nessun rischio di perdita dati: il file resta recuperabile dalla storia git in qualunque momento.

## 2026-08-11 bis — Sblocco registrazione pubblica utenti

- Ripreso il lavoro sulla macro-feature "Registrazione, piani e gestione utenti" ([[docs/roadmap_registrazioneutenti.md]]), partendo dalla verifica dello stato reale via lettura diretta dei documenti e del codice su GitHub (branch `fase9-comparator-quota`).
- Individuato un blocco critico: `Auth::registraUtentePubblico()` creava l'account con `account_status = 'pending_email'`, ma nessun invio email reale era mai stato implementato (`registrazione.php` generava il token di verifica e lo scartava). Risultato: qualunque utente si registrasse restava bloccato fuori dall'app, senza alcuna via di attivazione.
- Decisione concordata: finché l'applicazione non sarà spostata online sul VPS, l'invio email (verifica account e "password dimenticata") resta previsto nel codice ma non attivato. La registrazione pubblica attiva quindi subito l'account (`account_status = 'active'`), e in aggiunta è stata introdotta un'azione amministrativa di verifica manuale per gli eventuali utenti residui in `pending_email`.
- Modifiche applicative:
  - `www/includes/Auth.php`: `registraUtentePubblico()` inserisce `account_status = 'active'` invece di `'pending_email'`; aggiunto il metodo `verificaManualmente(int $utenteId)`.
  - `www/registrazione.php`: aggiornato il messaggio di successo (non più "controlla la tua email").
  - `www/login.php`: aggiunto il link mancante alla pagina `registrazione.php` (pagina già esistente ma irraggiungibile dall'interfaccia).
  - `www/admin_utenti.php`: nuova azione POST `verifica_manuale`, nuovo pulsante icona visibile solo per utenti `pending_email`, nuovo badge di stato "⏳ Da verificare" nella colonna Stato.
- Il meccanismo di token/verifica email esistente (`creaTokenSicurezza`, `verificaEmailToken`, `verifica-email.php`, `richiediResetPassword`, `confermaResetPassword`) resta invariato nel codice, dormiente, pronto per l'attivazione quando l'invio email reale sarà collegato sul VPS.
- Verifica: `php -l` superato su tutti i file modificati; `git diff --check` pulito; test end-to-end manuale via curl dentro il container (registrazione → utente creato con `account_status = active` in database → login riuscito), utente di test poi rimosso dal database.
- `tests/run.php` (regressione completa) fallisce per un problema preesistente e non collegato a questa modifica: la funzione `passthru()` risulta disabilitata nel container `astrolab-web` (probabile `disable_functions` in `php.ini`), già notato in una sessione precedente (2026-08-09 quater) e mai risolto.
- Nessun test automatico dedicato esisteva per il flusso di registrazione; non è stato creato in questo passaggio (fuori scope rispetto all'obiettivo puntuale di sblocco).
- Prossimo passo: proseguire con la Fase 5 della roadmap dedicata (amministrazione del piano Supporter: donazioni, validità annuale, scadenza/rinnovo, limiti soggetti personalizzati, accesso speciale permanente).

## 2026-08-11 ter — Fase 5, passo 1: modello dati amministrazione piano Supporter

- Avviata la Fase 5 della roadmap [[docs/roadmap_registrazioneutenti.md]] partendo dal modello dati, come base necessaria sia per l'interfaccia admin sia per la logica dei limiti effettivi.
- Decisione di prodotto confermata dall'utente: l'admin deve poter concedere un accesso completo, gratuito e permanente a chi ritiene opportuno (indipendente dal ciclo Supporter, senza toccare il ruolo); per tutti gli altri utenti valgono le regole già definite nella roadmap (piano, donazione, limiti).
- Creata e applicata al database operativo la migrazione `sql/007_piano_supporter_amministrazione.sql`:
  - su `utenti`: `subjects_limit_override`, `donazione_importo`, `supporter_inizio`, `supporter_scadenza`, `accesso_speciale_permanente` (default `FALSE`), `note_piano`;
  - su `piani`: `donazione_minima`, `durata_giorni` (valorizzati per `supporter`: 0 € minimo, 365 giorni, poi modificabili dall'admin);
  - vincoli CHECK su valori non negativi e coerenza date inizio/scadenza.
- Verificato lo schema post-migrazione (`\d utenti`, `\d piani`) e la disponibilità dell'applicazione (login e index rispondono correttamente dopo l'alter table).
- Il limite soggetti del piano Supporter resta configurabile in `piano_limiti` (già esistente da `sql/004_popola_piano_limiti.sql`); l'admin potrà modificarlo per adeguarlo alle fasce di donazione, oltre a impostare override per singolo utente.
- Prossimo passo: helper centralizzati in `Auth.php` (limite soggetti effettivo con precedenza accesso speciale permanente → override utente → piano; stato Supporter attivo/scaduto), poi estensione dell'interfaccia admin.

## 2026-08-11 quater — Fase 5, passo 2: helper centralizzato limite soggetti

- Aggiunto `Auth::getLimiteSoggettiEffettivo()` in `www/includes/Auth.php`, unico punto applicativo per il calcolo del limite soggetti, con precedenza: accesso speciale permanente (illimitato) → override personalizzato per singolo utente → limite del piano effettivo (un Supporter con `supporter_scadenza` superata viene trattato come free ai fini del limite).
- Refactorato `www/api/soggetti_api.php` (azione `inserisci`): rimossa la query inline duplicata su `piano_limiti`, ora richiama l'helper centralizzato.
- Confermato con l'utente che la registrazione `free` è già completamente self-service (nessuna approvazione admin richiesta), coerente con lo sblocco fatto nel passo precedente; il pulsante "verifica manuale" resta per i soli casi residui `pending_email`. Valutate protezioni anti-abuso aggiuntive (rate limit più stretto, blacklist domini email); deciso di non aggiungerle ora, restando con CSRF + rate limit 5/ora/IP già presenti, da rivalutare se necessario in futuro.
- Verifica funzionale diretta (script PHP temporaneo, poi rimosso) sui tre scenari: piano free standard → limite 2; override a 5 → limite 5; accesso speciale permanente → illimitato (null). Confermato nessun impatto sugli utenti reali esistenti (tutti piano supporter, nessuna scadenza, nessun override).
- `php -l` e `git diff --check` superati su entrambi i file modificati.
- Prossimo passo: estendere `admin_utenti.php` per assegnare/modificare piano, donazione, scadenza Supporter, override soggetti e accesso speciale permanente.

## 2026-08-11 quinquies — Fase 5, passo 3: interfaccia admin gestione piano

- Aggiunto `Auth::aggiornaPianoUtente()` in `www/includes/Auth.php`: validazione server-side completa (piano tra i codici attivi in `piani`, importi/limiti non negativi, coerenza tra data inizio e scadenza Supporter) prima di scrivere piano, donazione, date, override soggetti, accesso speciale permanente e note sull'utente.
- Estesa `Auth::getListaUtenti()` con i nuovi campi per precompilare l'interfaccia.
- Discussa con l'utente la scelta dell'interfaccia (modale vs altre soluzioni): confermato il modale, coerente con lo stile già usato in tutta la pagina per le altre azioni, organizzato in tre blocchi (Piano, Ciclo Supporter, Personalizzazioni) per restare leggibile nonostante i molti campi.
- Aggiunto in `admin_utenti.php`: modale "Gestione Piano" (max-width 620px), azione POST `aggiorna_piano`, pulsante 💎 in tabella, funzione JS `apriModalePiano()` per precompilare i campi.
- Verifica funzionale diretta (script PHP temporaneo su utente di test, poi rimosso): rifiuto piano non valido, rifiuto scadenza precedente all'inizio, assegnazione Supporter con donazione/date/note salvata correttamente in database, passaggio a free con accesso speciale permanente attivato correttamente.
- `php -l` e `git diff --check` superati su entrambi i file modificati.
- Fase 5 ora copre funzionalmente i punti richiesti dall'utente: piano assegnabile dall'admin, importo donazione registrato, validità annuale Supporter (inizio/scadenza), limite soggetti personalizzabile per singolo utente (in vista delle fasce di donazione che l'utente gestirà), accesso completo/gratuito/permanente concedibile a discrezione dell'admin senza intaccare il ruolo.
- Rimandato a un passo successivo, se necessario: riporto automatico del campo piano a free alla scadenza (oggi la scadenza è già considerata ai fini del limite soggetti effettivo tramite `getLimiteSoggettiEffettivo()`, ma il valore di `plan_id` resta quello assegnato finché l'admin non lo cambia manualmente), storico delle modifiche amministrative (chi ha cambiato cosa e quando), attivazione/disattivazione dei piani dall'interfaccia.
- Prossimo passo: da concordare con l'utente in una prossima sessione — se completare i punti rimandati della Fase 5 o passare ad altro.

## 2026-08-11 sexies — Bugfix username case-sensitive + vista soggetti in amministrazione

- Bug reale segnalato dall'utente durante un test: registrato username pippo, salvato come Pippo per capitalizzazione automatica della tastiera; login con pippo minuscolo rifiutato per confronto case-sensitive in Auth::login(). L'utente era comunque presente in database e visibile in amministrazione (probabile falso allarme da cache browser), ma il bug di login era reale e confermato.
- Decisione dell'utente: lo username non deve mai essere case-sensitive. Verificata assenza di duplicati case-insensitive esistenti, creata e applicata la migrazione sql/008_username_case_insensitive.sql (indice univoco su LOWER(TRIM(username))).
- Aggiornato Auth::login() per confrontare LOWER(TRIM(username)) invece dell'uguaglianza esatta. Verificato end-to-end: login con pippo minuscolo riuscito dopo la modifica.
- Introdotta la terminologia di prodotto da usare d'ora in avanti: gli utenti con ruolo user sono "Astrologi"; le persone che l'astrologo gestisce (tabella soggetti) sono "Soggetti di studio".
- Su richiesta dell'utente, aggiunta in admin_utenti.php una riga espandibile sotto ogni astrologo: click sul nome (con freccia toggle e contatore soggetti tra parentesi, es. roxy (4)) mostra una mini-tabella con i soggetti di studio di quell'astrologo, stessi campi della tabella soggetti standard (Codice, Nome, Data Nascita, Ora, Luogo). Vincolo esplicito rispettato: la modifica riguarda solo admin_utenti.php, nessun cambiamento a index.php — la vista personale dell'astrologo (es. roxy) resta identica a prima.
- Verifica funzionale diretta (script PHP temporaneo, poi rimosso) sul caso reale roxy: 4 soggetti (Cristina De Brand, Lorenzo Diana, Manuel Raso, Rossella Fumai) correttamente raggruppati e ordinati alfabeticamente.
- `php -l` e `git diff --check` superati su tutti i file; confermato tramite `git status` che index.php non compare tra i file modificati.
- Prossimo passo: da concordare con l'utente.

## 2026-08-11 septies — Redirect post-login differenziato per ruolo

- Su richiesta dell'utente, dopo aver verificato la nuova riga espandibile soggetti nella pagina corretta (admin_utenti.php, non index.php dove stava guardando inizialmente): l'admin deve atterrare direttamente su admin_utenti.php dopo il login.
- Modificato login.php in tre punti: redirect "gia' loggato" in cima alla pagina, valore di default di $next, e redirect post-login riuscito. In tutti e tre i casi, se non e' presente un parametro next esplicito, l'admin va su admin_utenti.php, gli astrologi normali restano su index.php come prima. Un eventuale link diretto (next=...) resta rispettato per entrambi i ruoli.
- Verificato end-to-end via curl con utenti reali: login astrologo pippo -> Location: index.php (invariato); login admin -> Location: admin_utenti.php (nuovo comportamento).
- php -l e git diff --check superati.

## 2026-08-12 — Nuova feature: Transiti Planetari

- Implementata la nuova feature "Transiti Planetari", estensione dell'applicazione senza refactoring: calcolo dei pianeti in transito confrontati con il tema natale del soggetto, con ruota grafica e tabella aspetti Transito → Natale.
- Menu: in `www/includes/header_nav.php` la voce "Rivoluzioni" è stata rinominata "Ricerche" e aggiunta la voce "☌ Transiti Planetari" verso `transiti.php`.
- Creata `www/transiti.php` clonando `www/rs.php` e adattandola: rimossi interamente i blocchi non pertinenti ai transiti (Mappa/Leaflet, rettifica ora di nascita, Relazione Annuale, Analisi Sensibilità Oraria, Sessioni RS salvate, pannello Valutazione/Rule Engine, alert Stellium, filtro esclusione, stampa PDF RS); mantenuti e riadattati form soggetto/luogo/coordinate, `datiSoggetto` (variabile `DS`), `ZodiacWheel.disegna()`, tabelle pianeti/cuspidi.
- Estesa `www/api/tema_api.php` con un nuovo ramo `tipo=transito` che riusa `SweCalc::calcolaTema()` esistente (nessun nuovo endpoint necessario, nessuna duplicazione della logica Swiss Ephemeris); i rami `natale` ed `rs` esistenti restano invariati.
- Aggiunta la funzione JS `calcolaAspettiTransitoNatale()` che riusa `ZodiacWheel.ASPETTI` e `ZodiacWheel._trovaAspetto()` già esistenti (differenza angolare circolare corretta), evitando di duplicare la configurazione degli aspetti; il risultato alimenta senza modifiche `popolaTabellaAspetti()` già esistente (esteso solo il `tipoMap` interno con i nomi italiani minuscoli usati da `ZodiacWheel.ASPETTI`).
- UX: all'apertura della pagina il calcolo parte in automatico con la data odierna, ore 00:00 locali e il luogo di residenza del soggetto (fallback al luogo di nascita, già gestito dalla logica esistente di `$defaultLat/$defaultLon/$defaultLuogo`); pulsante "Oggi/Adesso" per precompilare data/ora corrente.
- I campi Ore/Minuti sono in **ora locale**: la conversione a GMT (inclusa la gestione automatica dell'ora legale) avviene tramite lookup a TimeZoneDB (stesso servizio già usato altrove nel progetto per mostrare l'ora locale nella RSM), con fallback a interpretazione diretta come GMT se il servizio non risponde.
- Corretta una discrepanza di spaziatura nel form: la classe condivisa `.controlli .form-group` in `css/style.css` (usata anche da RS/RL/Rilocazione) impone `min-width:150px`; risolto con `style` inline mirato solo sui nuovi campi di `transiti.php`, senza modificare il CSS condiviso (le altre pagine restano invariate).
- Calcoli verificati dall'utente per confronto con sito esterno di riferimento (Swiss Ephemeris) sullo stesso soggetto/data/ora — validazione superata.
- `php -l` superato su tutti i file modificati (`transiti.php`, `header_nav.php`, `api/tema_api.php`); `git diff --check` pulito.
- Nota tecnica per sessioni future: durante lo sviluppo un'edit basata su marker ambiguo (`<?php endif; ?>` presente più volte nel file) ha causato la cancellazione accidentale di un endif strutturale, poi individuata e corretta ricostruendo il file con marker univoci — quando si rimuovono blocchi HTML/JS estesi in file con più `if/endif` PHP, verificare sempre che il numero di `<?php endif; ?>` resti invariato dopo la modifica.

## 2026-08-14 — Nuova feature: Ricerca RL (Rivoluzioni Lunari mensili per condizione)

- Estesa la ricerca geografica per CONDIZIONE, finora disponibile solo per le RSM (base annuale), anche alle RL (base mensile), riusando al 100% il motore di valutazione esistente (RuleEngine, Rule Map di esclusione radicale, FiltroEsclusione, ThemeBuilder, ResultBuilder, TopK, deduplicazione geografica), già agnostico rispetto a RS/RL.
- Creato `www/api/ricerca_stream_rl_api.php` (copia mirata di `ricerca_stream_api.php`): unico punto realmente specifico-RS sostituito, lo Step 1 ora usa `SweCalc::calcolaTutteRLLibsweCompatibileLunaApi()` con selezione della RL tramite `anno_rs` + `rl_index` (stesso schema già in uso da `rl_api.php`), invece di `calcolaRS()`. Verificato con `php -l`, con script CLI di verifica funzionale (coincidenza esatta con `tests/rl_lorenzo_2026.php` e con dati reali del soggetto id=23) e con l'intera suite `tests/run.php` (nessuna regressione; unico fallimento è quello preesistente e già noto di `search_auth.php`, non collegato).
- Creata pagina dedicata `www/ricerca_rl.php` (clone mirato di `ricerca.php`, non modificata): titolo, label e selettore RL propri; le modalità Griglia/Astri nelle Case/Cuspidi (specifiche RS) restano visibili ma disabilitate con nota "Non ancora disponibile per la ricerca RL"; tabella risultati identica alla RSM (stelline, veti, bonus, esclusioni). Scelta architetturale della pagina dedicata (invece di integrare RS/RL nella stessa `ricerca.php`) decisa dopo un primo tentativo di integrazione che aveva introdotto bug reali (titolo statico, label "RS GMT" fissa, fallback fragili) — evitare accoppiamento tra i due motori di ricerca.
- Pulsante "☽ Usa" sui risultati RL punta a `rl.php` (non più a `rs.php`) con `lat_rl`/`lon_rl`/`luogo_rl`/`anno`/`rl_index`, per aprire direttamente il grafico della RL cercata nel luogo trovato. Aggiunto supporto opzionale e puramente additivo al parametro URL `rl_index` in `js/rl.js` (override di `_trovaIndiceCorrente()` solo se il parametro è presente e valido; comportamento invariato altrimenti) — nessuna modifica a `rl.php`.
- Aggiunta voce "Ricerca Località RL" in `header_nav.php`, subito dopo "Ricerca Località" (RS), con evidenziazione attiva dedicata (`$paginaAttiva = 'ricerca_rl'`).
- File nuovi: `www/api/ricerca_stream_rl_api.php`, `www/ricerca_rl.php`. File modificati: `www/includes/header_nav.php`, `www/js/rl.js`. Nessuna modifica a `www/ricerca.php`, `www/rs.php`, `www/rl.php`, RuleEngine o motore astrologico.
- `php -l` superato su tutti i file PHP toccati; `git diff --check` pulito (uniche righe di trailing whitespace segnalate sono preesistenti nell'originale copiato, non introdotte oggi, non toccate per restare nello scope); test funzionali CLI e suite di regressione eseguiti con esito positivo; verifica manuale end-to-end in UI con l'utente, inclusi 2 bug reali trovati e corretti durante il test (campo soggetto non riconosciuto nel primo tentativo integrato, poi superato dalla pagina dedicata; preselezione mese mancante sul pulsante "Usa", risolta con l'override opzionale `rl_index`).
- Nota per sessione futura (fuori scope): fix di `tests/search_auth.php` (mismatch `session.name` PHPSESSID vs ASTROSESSID indurito), non impatta l'applicazione reale.
- roadmap_aiuto.md aggiornata (Sezione 4 e tabella di mappatura).

## 2026-08-14 bis — Riorganizzazione navbar: Localita RS/RL spostate nel dropdown "Ricerche"

- Su richiesta dell'utente: rinominate le voci "Ricerca Localita" -> "Localita RS" e "Ricerca Localita RL" -> "Localita RL", spostate dalla barra principale (link standalone) dentro il dropdown "Ricerche" in `header_nav.php`.
- Ordine scelto (raggruppato per tipo di rivoluzione): Riv. Solare, Localita RS, Riv. Lunare, Localita RL, Rilocazione, Transiti Planetari.
- Aggiornato `$_riviActive` per includere `ricerca` e `ricerca_rl`, cosi il trigger del dropdown si evidenzia correttamente anche su queste due pagine.
- Nessuna modifica a `ricerca.php`, `ricerca_rl.php`, `ricerca_stream_rl_api.php` o al motore di ricerca: solo riposizionamento dei link e rinomina delle etichette in `header_nav.php`.
- `php -l` superato; `git diff --check` pulito; verifica visuale in UI con l'utente.

## 2026-08-14 ter — Fix geocoding indirizzo (RS/RL): nome luogo robusto + bug dropdown RL

- Su segnalazione dell'utente: il campo "Luogo RS"/"Luogo RL", quando popolato tramite ricerca indirizzo (Nominatim/OpenStreetMap, gia esistente), mostrava il numero civico ("3314") invece del nome della citta per indirizzi USA con formato "numero, via, citta" — causa: `nome.split(',')[0].trim()` prendeva sempre il primo segmento del `display_name`, dipendente dal formato indirizzo del singolo paese.
- Fix robusto e indipendente dal paese: nuova funzione `_estraiNomeLuogoNominatim(r)` (duplicata in `rs.php` inline e in `js/rl.js`, stessi scope separati) che usa i campi strutturati e standardizzati di Nominatim (`address.city/town/village/municipality/hamlet/county` + `address.state`), gia richiesti con `addressdetails=1` ma non ancora sfruttati. Il nome breve viene calcolato in `cercaLuogoRS()`/`cercaLuogoRL()` (dove `r.address` e disponibile) e passato come parametro aggiuntivo opzionale `nomeBreve` a `selezionaLuogoRS()`/`selezionaLuogo()`, con fallback al vecchio comportamento se assente (retrocompatibile).
- Bug reale scoperto durante il test in UI su `rl.php`: il pulsante "Cerca" del Luogo RL chiamava `cercaLuogoRL()` senza il prefisso `RLModule.` (la funzione vive dentro l'IIFE del modulo, non e globale) — click silenziosamente inefficace. Fix: `onclick="RLModule.cercaLuogoRL()"`.
- Secondo bug reale scoperto durante il test (diagnosi via DevTools Network + confronto CSS con rs.php): dopo il fix del pulsante, la richiesta a Nominatim arrivava correttamente (200 OK, dati validi) ma il menu a tendina dei risultati non era visibile — causa: `.rl-location-group` (contenitore del campo Luogo RL) non aveva `position: relative`, mentre l'equivalente `.luogo-group` di `rs.php` si; il dropdown (`position: absolute`) veniva quindi posizionato rispetto a un antenato sbagliato, fuori vista ma presente nel DOM. Fix: aggiunto `position: relative` a `.rl-location-group` in `css/style.css` (classe usata esclusivamente in `rl.php`, nessun impatto su altre pagine).
- File modificati: `www/rs.php`, `www/js/rl.js`, `www/rl.php`, `www/css/style.css`. Nessuna modifica a `ricerca.php`, `ricerca_rl.php`, motore di ricerca RSM/RL o RuleEngine.
- Verifica: `php -l` su `rs.php`/`rl.php`; bilanciamento graffe verificato su `js/rl.js`; `git diff --check` pulito; diagnosi con richiesta reale a Nominatim (indirizzo USA con numero civico) tramite DevTools Network dell'utente; test end-to-end in UI con l'utente su entrambe le pagine dopo ogni fix.

## 2026-08-19 — Toggle "Mostra Dati" su rs.php/rl.php e allineamento riga RL in ricerca_rl.php (branch feature/allineamento-myastral)

- Aggiunto un toggle "Mostra Dati"/"Nascondi Dati" (freccia + testo, stile identico a "Nascondi Cuspidi"/"Mostra Gradi") per mostrare/nascondere le tabelle sotto ogni grafico. Tabelle nascoste di default al caricamento.
  - CSS condiviso: nuova classe `.tema-info-row` in `css/style.css` (riga info ASC/MC + pulsante allineati orizzontalmente).
  - JS condiviso: nuova funzione globale `toggleDatiTabella(suffix)` in `js/zodiac_wheel.js`, riusabile per ogni box.
  - Applicato a `rs.php` (box Tema Natale + box Rivoluzione Solare) e `rl.php` (box Tema Natale + box Rivoluzione Lunare). Su indicazione del committente la feature resta scoped a queste due pagine: non estesa a `rilocazione.php`, `transiti.php`, `tema.php`.
- Corretto disallineamento estetico in `ricerca_rl.php`: il pulsante "Aggiorna elenco RL" stava su una riga separata sotto la select, disallineando la barra filtri rispetto agli altri campi. Wrappati select+pulsante in un'unica riga flex (`.rl-index-row`), pulsante trasformato in icona-soltanto (rotondo, 🔄) con tooltip CSS custom via `data-tooltip`/`::after` (il `title` nativo del browser non risultava affidabile in hover) — modifica CSS page-scoped solo in `ricerca_rl.php`.
- Verifica: `php -l` superato nel container per tutti i file PHP toccati, `node --check` superato su `zodiac_wheel.js`, `git diff --check` pulito, test funzionale confermato dal committente nel browser su entrambe le modifiche.
- File toccati: `www/css/style.css`, `www/js/zodiac_wheel.js`, `www/rs.php`, `www/rl.php`, `www/ricerca_rl.php`. Nessuna decisione UX necessaria (RuleEngine.php non toccato).

## 2026-08-19 bis — Sezioni collassabili "Sessioni Salvate" e "Bonus e Veti" in rs.php/rl.php (branch feature/allineamento-myastral)

- Rese collassabili (chiuse di default, header cliccabile con freccina che ruota) due sezioni presenti identicamente in `rs.php` e `rl.php`:
  - Card "Sessioni Salvate" (`#card-sessioni-rs` / `#card-sessioni-rl`).
  - Intero box "Bonus e Veti" (`#valutazione`, comprese stelle/punteggio/condizione, non solo le liste sotto) — aggiunto anche un titolo "Bonus e Veti" che prima non esisteva.
- Riuso del pattern grafico gia' esistente in `rs.php` per "Analisi Sensibilita' Oraria" (stessa classe `.sensib-chevron` per la freccina rotante). Aggiunte solo due nuove classi generiche condivise in `css/style.css` (`.collapse-toggle`, `.valutazione .collapse-toggle`) e una nuova funzione globale condivisa `toggleCollapse(suffix)` in `js/zodiac_wheel.js`, riusabile per entrambe le sezioni su entrambe le pagine — nessuna duplicazione di CSS/JS tra rs.php e rl.php.
- Verifica: `php -l` superato nel container su entrambi i file PHP, `node --check` superato su `zodiac_wheel.js`, `git diff --check` pulito, test funzionale confermato dal committente nel browser su entrambe le pagine.
- File toccati: `www/css/style.css`, `www/js/zodiac_wheel.js`, `www/rs.php`, `www/rl.php`. Nessuna decisione UX necessaria (RuleEngine.php non toccato).

## 2026-08-19 — Risolto bug oscillazione oraria DST da timezonedb.com (branch fix/oscillazione-dst-timezonedb)

- Risolto il bug distinto diagnosticato in sessione precedente (2026-08-19, allineamento fix/giorno-nascita-gmt): oscillazione di un'ora nell'offset GMT calcolato da `ottieniOffsetTimeZone()` in `js/app.js`, causata da un'approssimazione strutturale (nessun istante UTC noto su cui basare la chiamata API).
- Riscritta `ottieniOffsetTimeZone()` (app.js) e `calcolaTransiti()` (transiti.php, punto non previsto nella diagnosi originale, trovato durante la riverifica) con nuova funzione condivisa `calcolaOffsetPreciso()` basata su `Intl.DateTimeFormat` — nessuna chiamata API aggiuntiva, database IANA nativo del browser, algoritmo a due iterazioni per correggere i casi al confine DST.
- Migliorati 3 punti "display-only" (`rl.js`, `rs.php`, `transiti.php::aggiornaFusoOrarioLocale()`) per mostrare "N/D" con tooltip invece di fallire silenziosamente quando l'API non risponde.
- Verifica: test isolati con Node (6 casi, incluso confine DST primavera/autunno), stabilita' confermata (10 chiamate identiche = 10 risultati identici, zero oscillazione), test funzionali reali nel browser su form soggetto e pagina Transiti (offset corretto su entrambi i lati del confine DST 29/03/2026), nessuna regressione su RS/Transiti/RL in condizioni normali.
- Suite `tests/run.php`: sezioni Backend e casi JSON RS passate; sezione RL non eseguibile per limite pre-esistente `passthru()` (non ri-sbloccato, non necessario per questo lavoro client-side).
- File toccati: `www/js/app.js`, `www/js/rl.js`, `www/rs.php`, `www/transiti.php` (codice); `docs/ROADMAP_BUG_GIORNO_GMT.md`, `docs/ROADMAP_34_REGOLE.md` (documentazione, nota chiusa definitivamente).

## 2026-08-19 — Header fisso, background zodiacale, pannello trascinabile

- Header di navigazione reso fisso in cima (`position: fixed`), con compensazione `padding-top` sul body e riallineamento dell'intestazione sticky della tabella risultati ricerca (`.tabella-risultati th`, offset a 56px invece di 0) per non finire nascosta dietro la barra.
- Aggiunto background al body con 3 simboli zodiacali (Vergine, Pesci, Scorpione) disegnati in SVG inline, tono su tono sul crema esistente (`#F2EDE4`), `background-attachment: fixed`. Dimensione ridotta su richiesta dopo prima verifica visiva.
- Sfondo di 10 pannelli di contenuto (`.card`, `.tema-box`, `.controlli`, `.valutazione`, `.time-controls`, `.rl-timeline`, `#pannello-sensibilita`, `.tabella-risultati`, `.stats-bar`, `.pwd-box`) reso semi-trasparente (`rgba(255,255,255,0.88)`) per lasciar intravedere i simboli del background; dropdown, select, bottoni e modali lasciati bianco pieno per non compromettere la leggibilita'.
- Pannello "Correzione tempo ed ora" (`#correzione-tempo-modal`, rs.php) reso trascinabile: drag scoped per id (non tocca altre eventuali finestre che riusassero le stesse classi `.annual-report-*`), maniglia sull'header esistente, limiti ai bordi della viewport, click sulla X di chiusura non attiva il trascinamento, posizione resettata alla chiusura. Nessuna modifica a dimensioni o colori.
- Verifica: `php -l` su rs.php, `git diff --check` pulito, test funzionali reali confermati dal committente su tutti e 4 i punti (header fisso, background, trasparenza, drag).
- File toccati: `www/css/style.css`, `www/rs.php`.

## REGOLA PERMANENTE — Modale "Correzione tempo ed ora" in rs.php (congelata 2026-08-21)

Il pannello "Correzione tempo ed ora" in `www/rs.php` DEVE restare un modale orizzontale indipendente (`#correzione-tempo-modal`, introdotto 2026-08-18 dal commit `0e7e023`).

**NON reintrodurre** il vecchio div `time-controls time-controls-top` posizionato tra le due colonne (Tema Natale / Rivoluzione Solare). Questo è già successo due volte per errore, causato da lavoro svolto su branch divergenti (`feature/allineamento-myastral` vs `fase9-comparator-quota`) mai riallineati tra loro.

Aggiunto un commento HTML di protezione direttamente nel codice sopra il div del modale (commit su branch `chore/porta-feature-da-allineamento-myastral`, 2026-08-21). Qualunque sessione futura che debba modificare questo blocco deve chiedere conferma esplicita all'utente prima di procedere.

## 2026-08-21 bis — Fix sticky header tabella risultati (ricerca.php, ricerca_rl.php) e ripristino pulsante icona RL

- Componenti modificati:
  - `www/css/style.css`;
  - `www/ricerca.php`;
  - `www/ricerca_rl.php`.

- Obiettivo:
  - risolvere in via definitiva il bug (documentato solo su `feature/allineamento-myastral`, mai portato qui) per cui l'header sticky della tabella risultati (`.tabella-risultati th`, `position: sticky; top: 56px` ancorato al viewport) si sovrapponeva/tagliava la prima riga di dati a certe larghezze di finestra, con tentativi precedenti (z-index, overflow-y esplicito sui wrapper, spacer da 8px) rimasti senza successo o solo tampone.

- Risultato:
  - introdotto un contenitore di scroll dedicato `.tabella-risultati-wrap` (`overflow-x:auto; overflow-y:auto; max-height: calc(100vh - 260px)`) attorno a ciascuna tabella risultati (4 punti di rendering per file, sia in `ricerca.php` che `ricerca_rl.php`);
  - `.tabella-risultati th` passato da `position: sticky; top: 56px` (ancorato al viewport, dipendente dall'header fisso globale e dalla larghezza finestra) a `position: sticky; top: 0; z-index: 2` (ancorato al nuovo contenitore locale, indipendente dalla larghezza della pagina);
  - la tabella ora scorre al proprio interno con barra di scorrimento verticale dedicata, invece che con l'intera pagina;
  - corretto anche un refuso di markup preesistente, non collegato al bug sticky: `</td></td>` → `</td></tr>` nel messaggio "Nessun risultato. Prova ad aumentare la tolleranza." (una occorrenza per file, vista Cuspidi);
  - ripristinato il pulsante "Aggiorna elenco RL" in `ricerca_rl.php` all'aspetto icona rotonda con tooltip (`.btn-refresh-rl`, `data-tooltip`) invece del pulsante testuale su riga propria — regressione dovuta al fatto che il commit `34e1e34` (19/08, "fix: allineamento riga Rivoluzione Lunare... pulsante Aggiorna elenco RL a icona con tooltip") esisteva solo su `feature/allineamento-myastral` e non era mai stato portato su questo branch; applicato qui come patch mirata equivalente, non come cherry-pick.

- Verifiche eseguite:
  - `docker compose exec -T astrolab-web php -l ricerca.php`: OK;
  - `docker compose exec -T astrolab-web php -l ricerca_rl.php`: OK;
  - `git diff --check`: OK su tutti e 3 i file;
  - riavvio `astrolab-web` dopo ogni modifica CSS/PHP;
  - test funzionale confermato dal committente nel browser: sticky header stabile a diverse larghezze di finestra, nessuna sovrapposizione con la prima riga; pulsante RL tornato a icona con tooltip funzionante; messaggio "nessun risultato" corretto.

- Nota per sessioni future: la vecchia nota nell'audit di `[[astrolab-rs-time-controls-regressione]]` sui commit orfani "fix header sticky tabella risultati" (5ecaa91/d30fdd5/338b9e5) può considerarsi superata per la parte funzionale — questo fix adotta un approccio diverso (contenitore di scroll dedicato) rispetto ai tentativi precedenti su quel branch, quindi quei 3 commit non vanno portati qui. Resta invece ancora da valutare `338b9e5` (larghezza max 1250px + centratura tabella), puramente estetico e indipendente dal bug, se lo si vuole per coerenza visiva. Il commit `34e1e34` (icona RL) non va più cercato tra i commit orfani da portare: è stato riapplicato qui come patch equivalente.
