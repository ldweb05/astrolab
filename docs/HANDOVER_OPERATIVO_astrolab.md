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

aggiornati START_HERE.md, README.md e ROADMAP.md.

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
- FASE 5A: pianificata.

Il modello attuale supporta esclusivamente `solo_aeroporti` e
`solo_localita`, distinguendo i risultati tramite `origine_punto`.

La FASE 5A introdurrà, per `solo_localita`, la selezione obbligatoria
della nazione e il limite 50/100/150/Tutte. La modalità
`solo_aeroporti` manterrà la ricerca mondiale e il comportamento legacy.
