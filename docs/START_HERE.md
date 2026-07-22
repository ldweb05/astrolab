# START HERE

Benvenuto nel progetto **Astro-DSS**.

Astro-DSS è un progetto indipendente nato dalla base stabile di
**Astro-Val** con l'obiettivo di sviluppare un **Decision Support System**
per il confronto ragionato tra due Rivoluzioni Solari Mirate.

Questo documento è il punto di ingresso per comprendere lo stato del
progetto e sapere da dove riprendere lo sviluppo.

------------------------------------------------------------------------

# Stato iniziale del progetto

**Progetto:** Astro-DSS

**Tipologia:** Decision Support System astrologico

**Branch di sviluppo:** `feature/astro-dss`

**Base applicativa:** clone indipendente di Astro-Val

**Stato infrastruttura:** OPERATIVA

Sono già stati completati:

-   repository Git indipendente;
-   stack Docker indipendente;
-   database PostgreSQL indipendente;
-   rete Docker indipendente;
-   ripristino completo dei dati iniziali;
-   verifica dell'applicazione sulla porta `8192`.

------------------------------------------------------------------------

# Separazione da Astro-Val

Astro-Val rimane il progetto stabile dedicato al calcolo, alla valutazione
e alla produzione dei report astrologici.

Astro-DSS utilizza la base tecnica di Astro-Val, ma evolve come progetto
separato.

Le modifiche effettuate in Astro-DSS non devono alterare:

-   il repository di Astro-Val;
-   i container di Astro-Val;
-   il database di Astro-Val;
-   le porte e le reti Docker di Astro-Val;
-   la stabilità della versione operativa di Astro-Val.

------------------------------------------------------------------------

# Obiettivo del progetto

Astro-DSS deve confrontare due Rivoluzioni Solari Mirate già calcolate e
supportare una decisione motivata.

Il sistema dovrà evidenziare:

-   miglioramenti;
-   peggioramenti;
-   compromessi;
-   differenze rilevanti;
-   condizioni planetarie coinvolte;
-   regole ed evidenze applicabili;
-   bonus e penalità;
-   motivazioni della raccomandazione finale.

Astro-DSS non nasce per sostituire il motore astrologico di Astro-Val.

Il suo compito principale è trasformare risultati già disponibili in un
confronto strutturato, spiegabile e orientato alla decisione.

------------------------------------------------------------------------

# Principi architetturali

Il DSS dovrà essere costruito per livelli separati.

1.  Acquisizione dei risultati delle due RSM
2.  Normalizzazione dei dati confrontabili
3.  Comparator Engine
4.  Difference Analyzer
5.  Impact Evaluator
6.  Rule Correlator
7.  Narrative Generator
8.  Recommendation Engine
9.  UI di confronto

Ogni livello dovrà produrre dati verificabili e tracciabili.

La raccomandazione finale non dovrà essere una semplice differenza tra
punteggi, ma il risultato di evidenze esplicite e comprensibili.

------------------------------------------------------------------------

# Componenti ereditati da Astro-Val

La base iniziale comprende:

-   motore astronomico;
-   Planet Conditions;
-   Rule Engine;
-   Evidence Engine;
-   Theme Engine;
-   Narrative Engine;
-   gestione delle sessioni;
-   database applicativo;
-   interfaccia web esistente.

Questi componenti costituiscono la base tecnica iniziale.

Il Rule Engine ereditato contiene **120 Rule** ed è considerato
consolidato.

Non devono essere aggiunte o modificate Rule durante la prima fase di
sviluppo del DSS, salvo correzioni di bug documentati.

------------------------------------------------------------------------

# Prima milestone

## DSS V1 — Analisi e modello di confronto

La prima milestone non riguarda la nuova interfaccia grafica.

Le attività iniziali sono:

1.  censire tutti gli output prodotti da Astro-Val;
2.  identificare gli output disponibili per ogni RSM;
3.  distinguere dati numerici, categorici, narrativi ed evidenze;
4.  stabilire quali informazioni siano confrontabili;
5.  definire una struttura dati comune per due RSM;
6.  documentare criteri, priorità e limiti del confronto;
7.  progettare il primo Comparator Engine senza modificare il Rule Engine.

------------------------------------------------------------------------

# Dove riprendere

La prossima attività consiste nell'eseguire un inventario tecnico degli
output prodotti dall'applicazione.

L'analisi dovrà individuare:

-   tabelle e colonne coinvolte;
-   strutture PHP che rappresentano una RSM;
-   risultati numerici disponibili;
-   condizioni planetarie;
-   Rule attivate;
-   evidenze generate;
-   testi narrativi;
-   dati persistiti nelle sessioni;
-   eventuali informazioni calcolate ma non salvate.

Non deve ancora essere sviluppata la UI definitiva del confronto.

------------------------------------------------------------------------

# Criteri di qualità

Ogni confronto prodotto da Astro-DSS dovrà essere:

-   riproducibile;
-   spiegabile;
-   tracciabile;
-   indipendente dalla sola differenza di punteggio;
-   coerente con le evidenze astrologiche disponibili;
-   leggibile anche da un utente non tecnico;
-   verificabile a livello di regole e dati sorgente.

------------------------------------------------------------------------

# Obiettivo finale

Realizzare un Decision Support System capace di confrontare due RSM e
fornire una raccomandazione motivata, mostrando chiaramente:

-   quale soluzione risulta preferibile;
-   in quali ambiti risulta migliore;
-   quali criticità rimangono;
-   quali compromessi sono necessari;
-   quali dati, condizioni e regole sostengono la decisione.


---

# 🚀 Focus di sviluppo corrente

## Stato del progetto

La piattaforma Astrolab è stabile.

Sono stati completati:

- migrazione della deduplicazione geografica PHP → SQL;
- regressione completa della Search API;
- consolidamento della suite automatica dei test;
- documentazione tecnica aggiornata.

La regressione (`www/tests/run.php`) deve rimanere completamente verde
durante tutto lo sviluppo.

---

## Sprint attuale

### Ricerca RSM v3 — Località geografiche complete

Lo sviluppo corrente riguarda l'evoluzione della Ricerca RSM.

L'obiettivo è trasformare il sistema da ricerca limitata agli aeroporti
ad una ricerca su tutte le località geografiche disponibili
nel database mondiale.

Gli aeroporti continueranno ad essere supportati ma diventeranno una
particolare tipologia di località.

---

## Stato corrente

✔ infrastruttura stabile

✔ regressione verde

✔ deduplicazione SQL completata

✔ documentazione aggiornata

🚧 FASE 1 in corso:
analisi del modello geografico (GeoNames + aeroporti)

---

## Documenti da leggere nell'ordine

1. START_HERE.md

2. ROADMAP.md

3. HANDOVER_OPERATIVO_astrolab.md

4. ADR_INDEX_ASTROLAB.md

---

## Regole di sviluppo

Durante questo sprint:

- mantenere sempre verde la regressione automatica;
- non modificare il motore astrologico;
- procedere per piccoli commit verificabili;
- documentare ogni decisione importante;
- completare una fase prima di iniziare la successiva.

---

## Obiettivo del prossimo sprint

Progettare il nuovo modello logico "Località" che unificherà:

- aeroporti;
- eliporti;
- idroporti;
- città;
- villaggi;
- località remote.

Successivamente verranno aggiornati backend, API e interfaccia senza
rompere la compatibilità con la Ricerca RSM esistente.

## Stato corrente Ricerca RSM v3

Lo Sprint 1 è completato.

Il repository geografico supporta aeroporti e località, mentre
`ricerca_stream_api.php` esegue la deduplicazione direttamente in PostgreSQL.

Comando di verifica principale:

`docker compose exec -T astrolab-web php /var/www/html/tests/test_rsm_location_repository.php`
