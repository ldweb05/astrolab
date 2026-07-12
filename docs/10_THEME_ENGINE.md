# ===================================================================
# Astro-Val Documentation
# Document : 10_THEME_ENGINE.md
# Version  : 1.0
# Status   : Authoritative
# Part     : 1 / 5
# ===================================================================

# 1. Scopo

Il Theme Engine rappresenta il livello di sintesi dell'intero dominio astrologico.

Riceve le evidenze prodotte dal Rule Engine.

Le organizza.

Le pesa.

Le aggrega.

Le trasforma nei grandi temi dell'anno.

Il Theme Engine costituisce il vero punto di passaggio tra:

conoscenza astrologica

↓

conoscenza umana

Il Narrative Engine non interpreterà mai direttamente le evidenze.

Interpreterà esclusivamente i temi prodotti dal Theme Engine.

---

# REQ-THEME-001

Il Theme Engine rappresenta l'unica sorgente autorizzata dei temi narrativi.

---

# 2. Posizione nell'architettura

Pipeline.

```
Planet Conditions

↓

Rule Engine

↓

Evidence Engine

↓

Theme Engine

↓

Narrative Engine
```

Il Theme Engine riceve esclusivamente evidenze già validate.

---

# REQ-THEME-002

Il Theme Engine non può leggere direttamente pianeti, Case o aspetti.

---

# 3. Obiettivo

Le evidenze sono moltissime.

Una Rivoluzione Solare può produrre:

100

200

300

evidenze.

Il consultante non deve leggerle.

Deve comprenderne il significato.

Il Theme Engine esiste per questo.

---

# REQ-THEME-003

Il Theme Engine deve ridurre la complessità del dominio senza perdere informazione significativa.

---

# 4. Cos'è un Tema

Un Tema rappresenta un'area della vita.

Esempi.

- Carriera

- Relazioni

- Salute

- Famiglia

- Denaro

- Identità

- Crescita personale

- Trasformazione

Il Tema non appartiene all'astrologia.

Appartiene alla vita del consultante.

---

# REQ-THEME-004

I Temi devono essere formulati in linguaggio umano.

Mai astrologico.

---

# 5. Aggregazione

Più evidenze possono contribuire allo stesso Tema.

Esempio.

```
Giove X

↓

Career Protection

↓

Carriera


Sole X

↓

Recognition

↓

Carriera


Saturno X

↓

Responsibility

↓

Carriera
```

Il Theme Engine comprenderà che tutte descrivono il medesimo ambito.

---

# REQ-THEME-005

Ogni Tema deve poter ricevere contributi da un numero arbitrario di evidenze.

---

# 6. Responsabilità

Il Theme Engine deve:

✓ aggregare

✓ pesare

✓ deduplicare

✓ ordinare

✓ assegnare priorità

✓ calcolare polarità

✓ costruire il profilo del Tema

Non deve:

✗ scrivere testo

✗ interpretare eventi

✗ modificare le Rule

---

# REQ-THEME-006

Il Theme Engine termina il proprio lavoro producendo esclusivamente Theme Objects.

# ===================================================
# CONTINUA NELLA PARTE 2 / 5
# ===================================================
# ===================================================================
# Astro-Val Documentation
# Document : 10_THEME_ENGINE.md
# Version  : 1.0
# Status   : Authoritative
# Part     : 2 / 5
# ===================================================================

# 7. Anatomia di un Theme

Ogni Theme rappresenta un oggetto completo.

Non è semplicemente un punteggio.

Ogni Theme dovrà contenere almeno le seguenti informazioni.

```
Theme

id

name

intensity

priority

polarity

protection

exposure

confidence

summary

evidences[]

metadata
```

In futuro potranno essere aggiunti ulteriori campi senza modificare il contratto fondamentale.

---

# REQ-THEME-007

Ogni Theme deve essere completamente autosufficiente.

---

# 8. Intensità

L'intensità misura quanto un determinato ambito della vita sarà presente durante l'anno.

Non misura:

- fortuna;
- sfortuna;
- successo;
- fallimento.

Misura esclusivamente il livello di coinvolgimento.

Esempio.

Un tema "Carriera" con intensità elevata potrebbe descrivere:

- crescita;

- responsabilità;

- cambiamento;

- nuove opportunità;

- ridefinizione del ruolo.

L'intensità non contiene giudizi.

---

# REQ-THEME-008

L'intensità rappresenta la rilevanza del Tema.

Mai la sua qualità.

---

# 9. Priorità

La priorità stabilisce l'ordine con il quale i Temi verranno presentati nella relazione.

Essa dipende da:

- quantità di evidenze;

- forza delle evidenze;

- qualità delle evidenze;

- modificatori;

- eventuali Meta Rule.

Due Temi possono possedere la stessa intensità ma priorità differenti.

---

# REQ-THEME-009

Il Narrative Engine dovrà utilizzare esclusivamente la priorità fornita dal Theme Engine.

---

# 10. Polarità

Ogni Tema possiede una polarità.

Essa nasce dal bilanciamento tra:

- evidenze favorevoli;

- evidenze impegnative;

- evidenze neutre.

Il risultato non deve mai essere espresso come:

positivo

contro

ma come un equilibrio simbolico.

Esempio.

```
Carriera

Protection

72

Exposure

64

↓

Polarità

Lievemente favorevole
```

---

# REQ-THEME-010

La polarità deve derivare esclusivamente dalle evidenze.

---

# 11. Protection

Il Theme Engine calcola il livello di protezione.

La protezione rappresenta la quantità di risorse simboliche disponibili.

Può derivare da:

- Giove;

- Venere;

- buone dignità;

- configurazioni armoniche;

- regole composite.

La protezione non elimina il Tema.

Lo accompagna.

---

# REQ-THEME-011

Protection non rappresenta assenza di problemi.

---

# 12. Exposure

Exposure rappresenta il livello di esposizione.

Non rappresenta:

pericolo.

Rappresenta:

attenzione.

Un Tema con alta esposizione richiederà una narrativa più prudente.

Non necessariamente più negativa.

---

# REQ-THEME-012

Exposure non deve essere tradotto automaticamente in criticità.

---

# 13. Confidence

Ogni Tema possiederà un indice di Confidence.

Esso misura quanto il Theme Engine ritiene robusta la sintesi.

Ad esempio.

Molte evidenze coerenti

↓

Confidence alta

Poche evidenze

↓

Confidence moderata

Evidenze contrastanti

↓

Confidence più bassa

Questo valore sarà inizialmente utilizzato solo internamente.

---

# REQ-THEME-013

Il valore di Confidence non dovrà influenzare direttamente la narrativa.

---

# 14. Summary

Ogni Theme produrrà una breve sintesi tecnica.

Non destinata al consultante.

Esempio.

```
Career

Strong Protection

High Responsibility

Medium Exposure

High Confidence
```

Questa sintesi costituirà il punto di partenza del Narrative Engine.

---

# REQ-THEME-014

Summary deve essere prodotta automaticamente dal Theme Engine.

---

# 15. Evidenze associate

Ogni Theme mantiene l'elenco completo delle evidenze che hanno contribuito alla sua costruzione.

Esempio.

```
Career

↓

Evidence 14

↓

Evidence 28

↓

Evidence 72

↓

Evidence 105
```

Questo rende possibile la completa spiegabilità della relazione.

---

# REQ-THEME-015

Ogni Theme deve mantenere il riferimento a tutte le evidenze che lo compongono.

# ===================================================================
# Astro-Val Documentation
# Document : 10_THEME_ENGINE.md
# Version  : 1.0
# Status   : Authoritative
# Part     : 3 / 5
# ===================================================================

# 16. Costruzione del Tema

La costruzione di un Theme segue sempre lo stesso processo.

```
Evidence Collection

↓

Grouping

↓

Weighting

↓

Aggregation

↓

Conflict Resolution

↓

Theme Construction

↓

Ranking

↓

Narrative
```

Ogni passaggio è deterministico.

Ogni passaggio è verificabile.

---

# REQ-THEME-016

La costruzione di un Theme deve seguire sempre la medesima pipeline.

---

# 17. Raggruppamento

Il primo compito consiste nel raggruppare le evidenze appartenenti alla stessa area della vita.

Esempio.

```
Evidence

Career Protection

↓

Career

Evidence

Recognition

↓

Career

Evidence

Authority

↓

Career
```

Il Theme Engine non interpreta.

Riconosce semplicemente la categoria.

---

# REQ-THEME-017

Il raggruppamento deve dipendere esclusivamente dalla categoria dell'Evidence.

---

# 18. Pesatura

Ogni evidenza contribuisce in misura diversa.

Il peso dipende da:

- intensità;
- priorità;
- modificatori;
- qualità della regola;
- eventuali Meta Rule.

Non tutte le evidenze hanno la stessa importanza.

---

# REQ-THEME-018

Il peso finale deve essere completamente spiegabile.

---

# 19. Aggregazione

Terminata la pesatura il Theme Engine costruisce il profilo del Tema.

Ad esempio.

```
Career

Protection

72

Exposure

48

Priority

91

Confidence

88

↓

Theme
```

Da questo momento il dominio smette di ragionare sulle singole evidenze.

Ragiona sul Tema.

---

# REQ-THEME-019

Ogni Theme rappresenta una sintesi e non un semplice contenitore di evidenze.

---

# 20. Conflitti

Può accadere che due evidenze sembrino opposte.

Esempio.

```
Career Protection

+

Career Challenge
```

Questo non rappresenta un errore.

È una caratteristica naturale dell'astrologia.

Il Theme Engine dovrà conservarle entrambe.

La sintesi dovrà descrivere:

- presenza di opportunità;

- presenza di responsabilità;

- necessità di equilibrio.

---

# REQ-THEME-020

Le evidenze contrastanti non devono essere eliminate.

Devono essere integrate.

---

# 21. Meta-Theme

Più Temi possono concorrere a descrivere un fenomeno più ampio.

Esempio.

```
Career

+

Identity

+

Transformation

↓

Professional Reinvention
```

Questo rappresenta un Meta-Theme.

Esso non sostituisce i singoli Temi.

Li integra.

---

# REQ-THEME-021

I Meta-Theme devono essere costruiti esclusivamente da Theme già validati.

---

# 22. Ranking

Terminata la costruzione dei Theme il sistema produce una classifica.

Questa classifica stabilisce:

- ordine della relazione;

- spazio dedicato ad ogni capitolo;

- importanza narrativa.

Non determina il giudizio sul Tema.

Determina soltanto la sua centralità.

---

# REQ-THEME-022

Il Ranking deve essere stabile e deterministico.

---

# 23. Deduplicazione

Molte evidenze possono descrivere lo stesso concetto.

Il Theme Engine dovrà evitare ridondanze.

Ad esempio.

```
Career Growth

Career Expansion

Professional Development
```

potrebbero concorrere allo stesso nucleo concettuale.

La narrativa non dovrà ripetere tre volte lo stesso significato.

---

# REQ-THEME-023

Ogni concetto deve essere rappresentato una sola volta nel Theme finale.

---

# 24. Explainability

Ogni Theme dovrà poter rispondere alla domanda.

"Perché questo Tema è presente?"

La risposta dovrà poter essere ricostruita.

```
Theme

↓

Evidence

↓

Rule

↓

Planet Condition

↓

Astronomical Data
```

Questa proprietà rappresenta uno dei pilastri del progetto Astro-Val.

---

# REQ-THEME-024

Ogni Theme deve mantenere la completa catena di spiegabilità.

---

# 25. Tema Dominante

Uno dei Theme assumerà normalmente il ruolo di Tema Dominante.

Esso rappresenta il filo conduttore dell'intera Rivoluzione Solare.

La relazione dovrà costruirsi intorno ad esso.

Gli altri Temi costituiranno variazioni dello stesso quadro generale.

---

# REQ-THEME-025

Ogni Annual Report deve identificare un Tema Dominante.

# ===================================================================
# Astro-Val Documentation
# Document : 10_THEME_ENGINE.md
# Version  : 1.0
# Status   : Authoritative
# Part     : 4 / 5
# ===================================================================

# 26. Il Tema come Identità dell'Anno

L'obiettivo del Theme Engine non consiste nel classificare informazioni.

Il suo vero compito consiste nel comprendere quale identità assuma l'intera Rivoluzione Solare.

Ogni anno possiede infatti un carattere predominante.

Può essere:

- un anno di costruzione;

- un anno di trasformazione;

- un anno di consolidamento;

- un anno di apertura;

- un anno di revisione;

- un anno di cambiamento.

I singoli Theme concorrono a definire questa identità.

---

# REQ-THEME-026

L'insieme dei Theme deve permettere di individuare il carattere generale dell'anno.

---

# 27. Coerenza tra i Theme

I Theme non devono essere considerati indipendenti.

Essi descrivono aspetti differenti della stessa esperienza annuale.

Per questo motivo il Theme Engine dovrà verificare la loro coerenza reciproca.

Esempio.

```
Carriera

molto alta

↓

Relazioni

molto basse

↓

Famiglia

molto alta
```

Il motore dovrà evidenziare che la pressione professionale potrebbe influenzare la sfera familiare e relazionale.

Non dovrà limitarsi a presentare tre temi separati.

---

# REQ-THEME-027

Il Theme Engine deve individuare relazioni significative tra i Theme.

---

# 28. Tema principale e Temi secondari

Ogni relazione dovrà distinguere chiaramente:

Tema Dominante

↓

Temi Primari

↓

Temi Secondari

↓

Temi Residuali

Questo permetterà alla narrativa di distribuire correttamente lo spazio disponibile.

Un Tema residuale non dovrà occupare lo stesso spazio del Tema Dominante.

---

# REQ-THEME-028

Il Theme Engine deve classificare i Theme in livelli di rilevanza.

---

# 29. Compressione della complessità

Una Rivoluzione Solare contiene una quantità enorme di informazioni.

Il Theme Engine ha il compito di comprimere questa complessità.

Esempio.

```
240 Evidence

↓

18 Theme

↓

5 Theme principali

↓

1 Tema dominante
```

Questa riduzione rappresenta una sintesi.

Non una perdita di informazione.

---

# REQ-THEME-029

La riduzione della complessità deve preservare il significato astrologico.

---

# 30. Tema ≠ Capitolo

È importante distinguere:

Theme

e

Capitolo Narrativo.

Un Theme rappresenta un concetto del dominio.

Un Capitolo rappresenta una scelta editoriale.

Ad esempio.

```
Theme

Professione

↓

Narrativa

Il lavoro e la realizzazione personale
```

La narrativa può riorganizzare.

Il Theme Engine no.

---

# REQ-THEME-030

Il Theme Engine non deve conoscere la struttura editoriale della relazione.

---

# 31. Evoluzione del Theme

Nel tempo un Theme potrà acquisire nuove proprietà.

Ad esempio.

```
Protection Index

Exposure Index

Growth Index

Stress Index

Harmony Index

Transformation Index
```

Queste proprietà dovranno essere aggiunte senza rompere il contratto pubblico.

---

# REQ-THEME-031

L'estensione del Theme Object deve essere retrocompatibile.

---

# 32. Theme Registry

Il sistema dovrà possedere un registro ufficiale dei Theme.

Esempio.

```
CAREER

RELATIONSHIPS

HEALTH

MONEY

FAMILY

HOME

IDENTITY

TRANSFORMATION

STUDY

TRAVEL

CHILDREN

SPIRITUALITY
```

Ogni Theme possiederà:

- ID stabile;

- descrizione;

- categoria;

- sinonimi;

- mapping con le Evidence.

---

# REQ-THEME-032

Ogni Theme deve essere identificato mediante un ID stabile.

---

# 33. Testing

Il Theme Engine dovrà essere sottoposto a test automatici.

Ogni test dovrà verificare:

- aggregazione;

- ordinamento;

- ranking;

- polarità;

- protection;

- exposure;

- confidence;

- explainability.

---

# REQ-THEME-033

Ogni algoritmo del Theme Engine deve essere coperto da test automatici.

---

# 34. Performance

L'efficienza rappresenta un obiettivo importante.

Tuttavia non deve compromettere:

- leggibilità;

- spiegabilità;

- correttezza.

Il Theme Engine costituisce uno dei componenti più importanti del dominio.

La chiarezza del codice ha priorità.

---

# REQ-THEME-034

La manutenibilità prevale sull'ottimizzazione prematura.

---

# 35. Visione architetturale

Il Theme Engine rappresenta il primo livello nel quale il software smette di ragionare come un astrologo e comincia a ragionare come un narratore.

Le configurazioni astrologiche vengono ormai trasformate in concetti appartenenti alla vita reale.

Questa trasformazione costituisce uno degli elementi più innovativi dell'intera architettura Astro-Val.

---

# REQ-THEME-035

Il Theme Engine costituisce il ponte ufficiale tra il dominio astrologico e il dominio narrativo.


# ===================================================================
# Astro-Val Documentation
# Document : 10_THEME_ENGINE.md
# Version  : 1.0
# Status   : Authoritative
# Part     : 5 / 5
# ===================================================================

# 36. Explainability completa

Uno dei requisiti fondamentali del Theme Engine consiste nella completa spiegabilità dei risultati.

Ogni Theme deve poter rispondere alle seguenti domande.

• Perché questo Tema è stato generato?

• Quali Rule hanno contribuito?

• Quali Evidence sono state utilizzate?

• Quali modificatori hanno incrementato o ridotto il peso?

• Perché questo Tema è classificato come dominante?

Il sistema dovrà poter ricostruire integralmente il percorso logico.

```
Theme

↓

Evidence

↓

Rule

↓

Planet Condition

↓

Planet

↓

Astronomical Data
```

Questo rende Astro-Val un sistema verificabile.

---

# REQ-THEME-036

Ogni Theme deve mantenere l'intera catena di tracciabilità.

---

# 37. Tema Dominante dell'Anno

Ogni Annual Report dovrà individuare un Tema Dominante.

Il Tema Dominante rappresenta il filo conduttore della Rivoluzione Solare.

Non coincide necessariamente con il Tema avente il punteggio più elevato.

Esso deriva dall'insieme di:

- priorità;
- intensità;
- numero di Evidence;
- Meta Rule;
- coerenza con gli altri Theme.

La narrativa dovrà costruire l'intera relazione intorno a questo nucleo interpretativo.

---

# REQ-THEME-037

Ogni relazione deve possedere un solo Tema Dominante.

---

# 38. Equilibrio dei Theme

Una Rivoluzione Solare raramente è sbilanciata verso un solo ambito della vita.

Il Theme Engine dovrà mantenere un equilibrio tra:

- temi di crescita;

- temi di responsabilità;

- temi di trasformazione;

- temi di consolidamento.

L'obiettivo non consiste nel trovare il "tema migliore".

Consiste nel comprendere la configurazione complessiva dell'anno.

---

# REQ-THEME-038

Il Theme Engine deve rappresentare l'equilibrio generale della Rivoluzione Solare.

---

# 39. Evoluzione futura

L'architettura è progettata affinché il Theme Engine possa evolvere.

Tra le future estensioni previste.

• Theme Relationship Graph

• Cross Theme Analysis

• Annual Identity Score

• Opportunity Index

• Attention Index

• Life Balance Index

• Evolution Index

Questi elementi potranno essere aggiunti senza modificare il contratto fondamentale del Theme Engine.

---

# REQ-THEME-039

Le evoluzioni future dovranno essere implementate mediante estensione.

Mai rompendo la compatibilità del dominio.

---

# 40. Obiettivo finale del Theme Engine

Il Theme Engine rappresenta probabilmente il componente più importante dell'intera architettura Astro-Val.

Esso costituisce il punto nel quale la complessità astrologica viene trasformata in conoscenza comprensibile.

Il Rule Engine produce centinaia di evidenze.

Il Theme Engine le trasforma in pochi grandi significati.

Il Narrative Engine trasformerà successivamente tali significati in una relazione leggibile.

Questa separazione rende possibile:

- migliorare il dominio senza modificare la narrativa;

- migliorare la narrativa senza modificare il dominio;

- testare ogni livello in maniera indipendente;

- spiegare ogni conclusione prodotta dal sistema.

Il Theme Engine rappresenta quindi il vero ponte tra la conoscenza astrologica e l'esperienza del consultante.

---

# REQ-THEME-040

Il Theme Engine costituisce il livello ufficiale di sintesi del dominio astrologico di Astro-Val.

Tutte le future implementazioni narrative dovranno utilizzare esclusivamente i Theme prodotti da questo componente.

---

# Principi fondamentali del Theme Engine

L'intero Theme Engine si fonda sui seguenti principi.

• Un Theme nasce dalle Evidence.

• Un Theme non nasce direttamente dai Pianeti.

• Un Theme rappresenta un'area della vita.

• Un Theme è spiegabile.

• Un Theme è deterministico.

• Un Theme è indipendente dalla Narrativa.

• Un Theme mantiene la tracciabilità completa.

• Un Theme descrive il significato dell'anno.

• Un Theme non descrive eventi certi.

• Un Theme costituisce il linguaggio comune tra il dominio astrologico e il Narrative Engine.

Questi principi rappresentano il fondamento del livello di sintesi dell'intero progetto Astro-Val.

---

# Stato del documento

Versione: 1.0

Status: Authoritative

Documento normativo.

Qualsiasi implementazione del Theme Engine dovrà rispettare integralmente il presente documento.

# ===================================================================


---

# Evoluzione del Theme Engine

Ogni nuova funzionalità del Theme Engine dovrà essere accompagnata
dall'aggiornamento della documentazione e della roadmap del progetto.


# FINE DOCUMENTO
# ===================================================================