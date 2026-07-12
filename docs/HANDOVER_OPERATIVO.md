

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
Le Rule possono essere create direttamente seguendo le classi esistenti.

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