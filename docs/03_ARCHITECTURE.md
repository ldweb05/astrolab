# ===================================================================
# Astro-Val Documentation
# Document : 03_ARCHITECTURE.md
# Version  : 2.0
# Status   : Authoritative
# ===================================================================

# 1. Introduzione

Questo documento descrive l'architettura software ufficiale di Astro-Val.

Ogni componente del sistema deve rispettare rigorosamente le responsabilità definite in questo documento.

L'obiettivo dell'architettura è ottenere:

- elevata manutenibilità;
- totale tracciabilità;
- separazione delle responsabilità;
- interpretazione spiegabile;
- evoluzione controllata del software.

Il principio fondamentale è:

**ogni modulo deve fare una sola cosa e farla bene.**

---

# 2. Visione generale

Astro-Val è organizzato come una pipeline.

Ogni livello riceve informazioni dal livello precedente.

Ogni livello aggiunge conoscenza.

Mai interpretazioni duplicate.

Mai logica ripetuta.

---

## Pipeline generale

```
             SweCalc
                 │
                 ▼
        PlanetResolver
                 │
                 ▼
    PlanetConditionEngine
                 │
                 ▼
         AARuleEngine
                 │
                 ▼
        Evidence Engine
                 │
                 ▼
         Theme Engine
                 │
                 ▼
      Narrative Engine
                 │
                 ▼
 Annual Report Generator
                 │
                 ▼
              API JSON
                 │
                 ▼
        Browser / PDF
```

---

# 3. Filosofia architetturale

Il software è suddiviso in tre macro-livelli.

---

## Livello 1

### Astronomia

Produce dati.

Mai interpretazioni.

Componenti:

- SweCalc
- Effemeridi
- Coordinate
- Case
- Aspetti

Output:

solo dati astronomici.

---

## Livello 2

### Dominio astrologico

Produce significati.

Mai testo.

Componenti:

PlanetResolver

PlanetConditionEngine

AARuleEngine

Evidence Engine

Theme Engine

Output:

strutture dati.

Mai narrativa.

---

## Livello 3

### Comunicazione

Produce testo.

Mai logica astrologica.

Componenti:

Narrative Engine

Annual Report Generator

Presenter

API

---

# REQ-ARCH-001

Nessun componente del Livello 3 può leggere direttamente i dati astronomici.

---

# REQ-ARCH-002

Ogni interpretazione astrologica deve provenire esclusivamente dal Livello 2.

---

# 4. Responsabilità dei moduli

Ogni classe del progetto deve possedere una responsabilità unica.

---

## SweCalc

Responsabilità.

Calcolo astronomico.

Produce:

- pianeti
- case
- aspetti
- coordinate
- dati temporali

Non conosce:

- astrologia
- narrativa
- temi

---

# REQ-ARCH-003

SweCalc non deve contenere alcuna logica interpretativa.

---

## PlanetResolver

Responsabilità.

Normalizzare definitivamente il nome dei pianeti.

È l'unica fonte autorizzata.

Tutti gli altri componenti devono utilizzare esclusivamente PlanetResolver.

---

# REQ-ARCH-004

È vietato normalizzare manualmente i nomi dei pianeti in qualsiasi altro componente.

---

## PlanetConditionEngine

Responsabilità.

Determinare lo stato del pianeta.

Ad esempio.

- dignità

- debilità

- retrogradazione

- angularità

- posizione Gauquelin

- combustione

- cazimi

- velocità

- eventuali modificatori futuri

Output.

```
PlanetCondition
```

Mai narrativa.

Mai temi.

---

# REQ-ARCH-005

PlanetConditionEngine produce esclusivamente condizioni planetarie.

---

## AARuleEngine

Rappresenta il cuore del dominio astrologico.

Implementa esclusivamente le regole dell'Astrologia Attiva.

Input.

PlanetCondition

Output.

Evidence

Mai testo.

Mai narrativa.

Mai frasi.

---

# REQ-ARCH-006

Ogni regola astrologica deve essere implementata esclusivamente nel Rule Engine.

---

## Esempio

Saturno XII

↓

regola

↓

Evidence

```
SATURN_12

priority

strength

category

metadata
```

Il Rule Engine non sa cosa sia una relazione.

Sa solo produrre evidenze.

---

# Evidence Engine

Responsabilità.

Organizzare le evidenze.

Operazioni.

- deduplicazione

- ordinamento

- priorità

- raggruppamento

- pesatura

Output.

```
Evidence Collection
```

---

# REQ-ARCH-007

Le evidenze costituiscono la fonte unica del Theme Engine.

---

## Theme Engine

Trasforma le evidenze nei grandi temi della relazione.

Ad esempio.

Carriera

Relazioni

Famiglia

Salute

Denaro

Trasformazione

Ogni tema possiede.

```
intensity

priority

protection

exposure

polarity

supporting evidences
```

Il Theme Engine non conosce i pianeti.

Lavora esclusivamente sulle evidenze.

---

# REQ-ARCH-008

Il Theme Engine non deve leggere direttamente alcuna configurazione planetaria.


## Narrative Engine

Il Narrative Engine rappresenta il primo componente che produce linguaggio naturale.

Esso non interpreta direttamente:

- pianeti;
- aspetti;
- case;
- dignità.

Riceve esclusivamente i temi già validati dal dominio.

Input:

```
ThemeCollection
```

Output:

```
NarrativeSection[]
```

Ogni sezione contiene:

- titolo;
- testo;
- riferimenti alle evidenze;
- priorità.

---

# REQ-ARCH-009

Il Narrative Engine non può contenere regole astrologiche.

---

# Annual Report Generator

Responsabilità.

Costruire la relazione finale.

Riceve:

```
NarrativeSection[]
```

Produce:

```
AnnualReport
```

composto da:

```
Titolo

↓

Introduzione

↓

Significato dell'anno

↓

Temi principali

↓

Dinamiche trasversali

↓

Opportunità

↓

Aree di attenzione

↓

Conclusione
```

Il Report Generator non genera interpretazioni.

Organizza esclusivamente il documento.

---

# REQ-ARCH-010

Il Report Generator non deve produrre nuova logica astrologica.

---

# API

L'API rappresenta esclusivamente il livello di esposizione del sistema.

Essa non interpreta.

Non aggrega.

Non modifica.

Riceve un Annual Report già completo.

Lo serializza.

---

# REQ-ARCH-011

Ogni modifica interpretativa deve avvenire prima dell'API.

Mai all'interno dell'API.

---

# 5. Flusso dei dati

L'architettura segue un flusso unidirezionale.

Mai cicli.

Mai ritorni.

Mai dipendenze inverse.

```
Astronomia

↓

Condizioni

↓

Regole

↓

Evidenze

↓

Temi

↓

Narrativa

↓

Relazione

↓

API
```

---

# REQ-ARCH-012

Le dipendenze devono essere sempre orientate verso il basso della pipeline.

---

# 6. Contratti

Ogni livello comunica esclusivamente tramite strutture dati.

Mai tramite testo libero.

Ad esempio.

PlanetCondition

↓

Evidence

↓

Theme

↓

NarrativeSection

↓

AnnualReport

Questi costituiscono i contratti ufficiali del dominio.

---

# REQ-ARCH-013

Ogni contratto pubblico dovrà essere documentato.

---

# 7. Spiegabilità

Uno degli obiettivi fondamentali dell'architettura è la spiegabilità.

Per ogni frase della relazione dovrà essere possibile ricostruire il percorso completo.

Esempio.

```
Giove X

↓

PlanetCondition

↓

Rule

↓

Evidence

↓

Tema Carriera

↓

Paragrafo

↓

Relazione
```

Questa proprietà rende Astro-Val verificabile.

---

# REQ-ARCH-014

Ogni frase deve possedere almeno una evidenza di origine.

---

# 8. Tracciabilità

Ogni livello dovrà mantenere i riferimenti ai livelli precedenti.

Ad esempio.

```
Narrative

↓

Theme ID

↓

Evidence ID

↓

Rule ID

↓

PlanetCondition ID
```

Questo consentirà future funzioni di debugging e spiegazione.

---

# REQ-ARCH-015

Nessuna informazione dovrà perdere il riferimento alla propria origine.

---

# 9. Aggregazione

L'aggregazione avviene una sola volta.

Non sono ammessi:

- ricalcoli;
- reinterpretazioni;
- doppi conteggi.

Ogni passaggio aggiunge informazione.

Mai modifica arbitrariamente quella precedente.

---

# REQ-ARCH-016

Ogni aggregazione deve essere deterministica.

A parità di input il risultato deve essere identico.

---

# 10. Modularità

Ogni componente deve poter essere sostituito senza modificare gli altri.

Ad esempio.

In futuro potrà esistere:

```
Narrative Engine V4
```

senza modificare:

PlanetConditionEngine

oppure

AARuleEngine.

Questa indipendenza costituisce uno dei principali obiettivi architetturali.

---

# REQ-ARCH-017

Ogni modulo comunica esclusivamente attraverso contratti pubblici.

Mai mediante conoscenza interna delle classi.


# 11. Layer Isolation

Ogni layer dell'architettura deve essere completamente indipendente dagli altri.

La conoscenza del dominio deve fluire in un'unica direzione.

È vietato introdurre dipendenze circolari.

---

# REQ-ARCH-018

Nessun componente può dipendere da un livello successivo della pipeline.

---

# 12. Domain First

Astro-Val adotta il principio:

**Domain First**

Questo significa che:

prima viene costruita la conoscenza astrologica,

solo successivamente viene costruita la narrativa.

La narrativa non aggiunge significato.

La narrativa comunica il significato.

---

# REQ-ARCH-019

Ogni nuova funzionalità deve essere implementata prima nel dominio e solo successivamente nel livello narrativo.

---

# 13. Single Source of Truth

Ogni informazione deve avere una sola fonte ufficiale.

Esempi.

Normalizzazione pianeti

↓

PlanetResolver

Condizioni planetarie

↓

PlanetConditionEngine

Regole astrologiche

↓

AARuleEngine

Temi

↓

ThemeEngine

Narrativa

↓

NarrativeEngine

Relazione

↓

AnnualReportGenerator

---

# REQ-ARCH-020

La duplicazione della logica è vietata.

---

# REQ-ARCH-021

Ogni modifica deve essere effettuata esclusivamente nel componente proprietario della logica.

---

# 14. Open for Extension

L'architettura dovrà essere progettata per crescere senza modificare il comportamento esistente.

Ad esempio.

Sarà possibile aggiungere:

- nuove regole astrologiche;
- nuovi modificatori;
- nuovi tipi di evidenza;
- nuovi temi;
- nuovi capitoli della relazione;

senza alterare i moduli già consolidati.

---

# REQ-ARCH-022

I nuovi componenti devono estendere il sistema.

Mai sostituire logiche consolidate senza motivazione architetturale.

---

# 15. Closed for Modification

I moduli maturi devono rimanere stabili.

Le modifiche dovranno concentrarsi nei nuovi layer.

Questo riduce drasticamente il rischio di regressioni.

---

# REQ-ARCH-023

Prima di modificare un modulo stabile valutare sempre se la nuova logica possa essere implementata mediante estensione.

---

# 16. Explainability by Design

La spiegabilità non rappresenta una funzione accessoria.

È parte integrante dell'architettura.

Ogni elemento della relazione dovrà poter essere giustificato.

Ogni punteggio.

Ogni priorità.

Ogni frase.

Ogni conclusione.

---

# REQ-ARCH-024

Ogni componente deve preservare le informazioni necessarie alla spiegabilità.

---

# 17. Determinismo

Astro-Val è un sistema deterministico.

A parità di input dovrà produrre sempre il medesimo risultato.

Non sono ammessi:

- componenti casuali;
- ordinamenti instabili;
- pesature variabili non documentate.

---

# REQ-ARCH-025

Ogni algoritmo dovrà essere completamente deterministico.

---

# 18. Logging

Ogni livello dovrà poter produrre un log tecnico.

Il log non è destinato al consultante.

Serve allo sviluppatore.

Esempio.

```
PlanetResolver

↓

PlanetCondition

↓

Rule Fired

↓

Evidence Generated

↓

Theme Updated

↓

Narrative Produced
```

Questo permetterà di analizzare facilmente eventuali anomalie.

---

# REQ-ARCH-026

Il logging deve essere attivabile senza modificare la logica del dominio.

---

# 19. Performance

L'ottimizzazione rappresenta un obiettivo secondario.

La priorità è:

1. correttezza;
2. spiegabilità;
3. manutenibilità;
4. performance.

Una piccola perdita prestazionale è accettabile se migliora significativamente la chiarezza del codice.

---

# REQ-ARCH-027

È vietato introdurre ottimizzazioni che riducano la leggibilità del dominio senza un beneficio dimostrabile.

---

# 20. Evoluzione futura

L'architettura è progettata per ospitare futuri motori, tra cui:

- Rule Engine V2;
- AI-assisted Narrative Engine;
- Explainability Engine;
- Confidence Engine;
- Consistency Validator;
- Annual Report Designer.

Tali componenti dovranno integrarsi senza modificare i contratti esistenti.

---

# REQ-ARCH-028

Le evoluzioni future dovranno rispettare integralmente la pipeline architetturale definita in questo documento.


# 21. Error Handling

Gli errori devono essere gestiti nel livello in cui vengono generati.

Non è responsabilità del Narrative Engine correggere errori prodotti dal dominio.

Allo stesso modo il dominio non deve conoscere le modalità con cui gli errori verranno presentati all'utente.

---

# REQ-ARCH-029

Ogni layer deve gestire esclusivamente i propri errori.

---

# 22. Public API Contract

L'API rappresenta esclusivamente il contratto pubblico del sistema.

Qualunque modifica incompatibile deve essere:

- documentata;
- motivata;
- versionata.

L'obiettivo è garantire la stabilità del frontend.

---

# REQ-ARCH-030

I contratti pubblici devono essere retrocompatibili quando possibile.

---

# 23. Internal Contracts

Ogni motore comunica tramite oggetti o strutture dati ben definite.

Esempio.

```
PlanetCondition

↓

Evidence

↓

Theme

↓

NarrativeSection

↓

AnnualReport
```

Ogni contratto dovrà possedere:

- struttura stabile;
- documentazione;
- significato univoco.

---

# REQ-ARCH-031

Ogni contratto interno deve essere documentato nel manuale DATA_CONTRACTS.md.

---

# 24. Explainability Chain

L'intero software è costruito per poter rispondere ad una domanda fondamentale.

**Perché questa frase compare nella relazione?**

Il sistema dovrà essere sempre in grado di ricostruire il percorso.

```
Relazione

↓

Paragrafo

↓

Tema

↓

Evidence

↓

Rule

↓

PlanetCondition

↓

Tema Astronomico
```

Questo principio costituisce il fondamento della verificabilità dell'intero progetto.

---

# REQ-ARCH-032

Ogni elemento narrativo deve mantenere la propria catena di origine.

---

# 25. Separation of Concerns

Astro-Val distingue rigorosamente:

Astronomia

↓

Astrologia

↓

Narrativa

↓

Presentazione

Ogni livello utilizza il precedente ma non lo modifica.

---

# REQ-ARCH-033

Le responsabilità dei layer non devono sovrapporsi.

---

# 26. Architectural Stability

Una volta stabilizzato un componente esso dovrà essere modificato solo in presenza di:

- bug verificati;
- nuove esigenze di dominio;
- refactoring approvati;
- ADR esplicite.

Le modifiche "opportunistiche" sono scoraggiate.

---

# REQ-ARCH-034

Ogni modifica architetturale significativa deve essere accompagnata da una Architecture Decision Record (ADR).

---

# 27. Domain Integrity

L'integrità del dominio astrologico ha priorità sulla semplicità della narrativa.

Se una semplificazione narrativa altera il significato astrologico, dovrà essere rifiutata.

Il testo potrà essere semplificato.

Il dominio no.

---

# REQ-ARCH-035

La correttezza astrologica prevale sempre sulla semplicità comunicativa.

---

# 28. Evolution Strategy

L'evoluzione futura del software seguirà questo ordine:

```
Nuova conoscenza astrologica

↓

Nuove regole

↓

Nuove evidenze

↓

Nuovi temi

↓

Nuova narrativa

↓

Nuova relazione
```

Mai il contrario.

---

# REQ-ARCH-036

L'architettura evolve dal dominio verso la narrativa.

Mai dalla narrativa verso il dominio.

---

# 29. Architectural Principles Summary

L'intero progetto Astro-Val si fonda sui seguenti principi.

• Single Responsibility

• Domain First

• Explainability

• Traceability

• Determinism

• Modularity

• Separation of Concerns

• Single Source of Truth

• Open for Extension

• Closed for Modification

• Public Contract Stability

• Narrative built from validated themes

Questi principi dovranno guidare ogni futura evoluzione del software.

---

# REQ-ARCH-037

Ogni nuovo componente dovrà rispettare integralmente i principi architetturali sopra definiti.

---

# 30. Final Objective

L'obiettivo finale dell'architettura Astro-Val non è produrre testo.

Non è produrre interpretazioni isolate.

Non è generare un report.

L'obiettivo è costruire un sistema esperto capace di trasformare dati astronomici in una relazione professionale, completamente spiegabile, coerente con l'Astrologia Attiva e utilizzabile come base del colloquio tra consultante e astrologo.

L'architettura è progettata affinché ogni decisione presa dal software possa essere compresa, verificata e motivata.

Questo rappresenta il criterio principale con cui dovrà essere valutata ogni futura evoluzione del progetto.

---

# Stato del documento

Versione: 2.0

Status: Authoritative

Documento normativo.

Qualsiasi implementazione software in contrasto con il presente documento dovrà essere adeguata.

# ===================================================================


---

# 31. Evoluzione del progetto

L'evoluzione di Astro-Val deve seguire un processo controllato e misurabile.

Ogni milestone deve rispettare il seguente ordine:

1. aggiornamento della documentazione;
2. aggiornamento della roadmap;
3. aggiornamento del Knowledge Coverage;
4. implementazione del codice;
5. esecuzione della regressione completa.

L'architettura deve rimanere sempre coerente con la documentazione normativa.




---

# ADR-001 — Atlas come Single Source of Truth

## Contesto

Durante la V4.1 è emerso che la conoscenza astrologica era presente sia
nell'Atlas sia nelle Rule.

Questa duplicazione rende più difficile l'evoluzione del dominio.

## Decisione

L'Atlas diventa l'unica fonte autorevole della conoscenza astrologica.

Le Rule NON devono duplicare:

- temi;
- significati simbolici;
- pesi astrologici;
- relazioni pianeta/casa.

Le Rule devono limitarsi ad aggiungere comportamento.

## Responsabilità

Atlas
- conoscenza astrologica
- temi
- pesi
- configurazioni

Rule Engine
- explainability
- priorità
- confidence
- metadata
- composite
- comportamento

Theme Engine
- aggregazione

Narrative Engine
- traduzione in linguaggio naturale

Annual Report
- composizione editoriale

## Motivazione

Una sola fonte di verità.

Maggiore manutenibilità.

Riduzione della duplicazione.

Facilità di espansione verso 120+ Rule.


# FINE DOCUMENTO
# ===================================================================



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
