# Astro-DSS Roadmap

Documento di avanzamento del progetto Astro-DSS.

Deve essere aggiornato al completamento di ogni milestone significativa.

---

# Visione

Trasformare i risultati prodotti da due Rivoluzioni Solari Mirate in un
confronto strutturato, spiegabile e orientato alla decisione.

Astro-DSS deve supportare l'utente nell'individuare:

- la soluzione complessivamente preferibile;
- i miglioramenti ottenuti;
- i peggioramenti introdotti;
- i compromessi necessari;
- le aree astrologiche maggiormente coinvolte;
- le evidenze e le Rule che sostengono la raccomandazione.

La decisione finale non deve dipendere esclusivamente da un punteggio
totale, ma da un insieme tracciabile di dati, condizioni, evidenze e
criteri di priorità.

---

# Stato attuale

Data di avvio operativo: 2026-07-17

Progetto:

Astro-DSS

Branch di sviluppo:

`feature/astro-dss`

Baseline tecnica:

clone indipendente della versione stabile di Astro-Val

Infrastruttura:

- repository Git indipendente;
- stack Docker indipendente;
- database PostgreSQL indipendente;
- rete Docker indipendente;
- applicazione disponibile sulla porta `8192`;
- Adminer disponibile sulla porta `8193`;
- PostgreSQL disponibile sulla porta `5442`.

Database iniziale:

- 7 tabelle applicative;
- dati ripristinati dalla baseline Astro-Val;
- dimensione iniziale verificata: circa 28 MB.

Rule Engine ereditato:

- 120 Rule registrate;
- Knowledge Coverage 100%;
- Full Regression disponibile;
- stato consolidato;
- freeze operativo durante la prima fase DSS.

---

# Baseline ereditata da Astro-Val

Astro-DSS eredita una piattaforma applicativa già funzionante composta da:

- motore astronomico;
- Swiss Ephemeris tramite PHP FFI;
- calcolo delle Rivoluzioni Solari;
- calcolo delle Rivoluzioni Lunari;
- Planet Condition Engine;
- Rule Engine;
- Evidence Engine;
- Theme Engine;
- Narrative Engine;
- Annual Report;
- gestione soggetti e utenti;
- gestione sessioni;
- persistenza PostgreSQL;
- frontend web esistente;
- suite di test e regressione.

Questa baseline non rappresenta la roadmap futura di Astro-DSS.

Costituisce invece il punto di partenza tecnico sul quale costruire il
Decision Support System.

---

# DSS V1 — Inventario e modello di confronto

🚧 In corso

## Obiettivo

Individuare quali dati prodotti dall'applicazione siano realmente
confrontabili tra due RSM e definire un modello dati comune.

## Attività

1. censire tutti gli output prodotti per una Rivoluzione Solare;
2. individuare le strutture PHP che contengono i risultati;
3. identificare i dati salvati nel database;
4. identificare i dati presenti soltanto in memoria o in sessione;
5. distinguere:
   - dati numerici;
   - dati categorici;
   - condizioni planetarie;
   - Rule attivate;
   - evidenze;
   - testi narrativi;
   - metadati;
6. stabilire quali valori siano confrontabili direttamente;
7. individuare i dati che richiedono normalizzazione;
8. progettare una struttura dati comune per RSM A e RSM B;
9. documentare i limiti informativi della baseline.

## Deliverable

- inventario tecnico degli output;
- mappa delle fonti dati;
- modello normalizzato di una RSM;
- specifica iniziale del Comparator Engine;
- criteri minimi di tracciabilità.

## Vincoli

- nessuna modifica funzionale al Rule Engine;
- nessuna UI definitiva;
- nessuna raccomandazione automatica prematura;
- nessuna logica basata esclusivamente sul punteggio totale.

---

# DSS V2 — Comparator Engine

⏳ Pianificata

## Obiettivo

Realizzare il motore deterministico che confronta due strutture RSM
normalizzate.

## Funzioni previste

- confronto valore per valore;
- classificazione delle differenze;
- identificazione di miglioramenti e peggioramenti;
- gestione dei valori mancanti;
- confronto delle condizioni planetarie;
- confronto delle Rule attivate;
- confronto delle evidenze;
- produzione di un risultato strutturato e verificabile.

## Output previsto

Per ogni elemento confrontato:

- valore RSM A;
- valore RSM B;
- differenza;
- direzione del cambiamento;
- rilevanza;
- fonte del dato;
- motivazione tecnica.

---

# DSS V3 — Impact Evaluator e Rule Correlator

⏳ Pianificata

## Obiettivo

Attribuire significato decisionale alle differenze prodotte dal
Comparator Engine.

## Funzioni previste

- classificazione degli impatti;
- valutazione delle priorità;
- correlazione con le Rule;
- correlazione con le evidenze;
- rilevazione di bonus e penalità;
- individuazione dei conflitti;
- individuazione dei compromessi;
- aggregazione per ambito astrologico.

## Principio

Una differenza numerica non è automaticamente un miglioramento.

Il significato deve essere derivato dal contesto astrologico, dalle Rule,
dalle evidenze e dalle priorità definite.

---

# DSS V4 — Recommendation Engine

⏳ Pianificata

## Obiettivo

Produrre una raccomandazione finale motivata e tracciabile.

## Funzioni previste

- sintesi dei vantaggi di RSM A;
- sintesi dei vantaggi di RSM B;
- individuazione delle criticità residue;
- classificazione dei compromessi;
- gestione dei casi equivalenti;
- gestione dei casi non decidibili;
- raccomandazione finale;
- confidence level;
- elenco delle evidenze principali.

## Requisito fondamentale

Il sistema deve poter dichiarare che non esistono dati sufficienti per
una raccomandazione affidabile.

---

# DSS V5 — Narrative e interfaccia di confronto

⏳ Pianificata

## Obiettivo

Rendere il confronto comprensibile e utilizzabile attraverso una
interfaccia dedicata.

## Funzioni previste

- selezione di due RSM;
- visualizzazione affiancata;
- evidenziazione dei miglioramenti;
- evidenziazione dei peggioramenti;
- visualizzazione dei compromessi;
- dettaglio delle Rule coinvolte;
- dettaglio delle evidenze;
- sintesi narrativa;
- raccomandazione finale;
- esportazione del confronto.

## Vincolo

La UI deve rappresentare dati già prodotti dai motori sottostanti.

Non deve contenere logica decisionale non tracciata.

---

# Direttiva operativa permanente

L'architettura ereditata e il Rule Engine sono considerati componenti
stabili della baseline.

Il Rule Engine non deve essere modificato salvo:

- bug documentati;
- incompatibilità tecniche;
- refactoring che non alterino il comportamento;
- decisione architetturale esplicita e documentata.

Ogni nuova logica DSS deve essere:

- separata dal Rule Engine;
- deterministica;
- testabile;
- tracciabile;
- spiegabile;
- documentata.

---

# Documentazione da aggiornare

Ogni milestone deve aggiornare almeno:

- `docs/README.md`;
- `docs/START_HERE.md`;
- `docs/ROADMAP.md`;
- `docs/HANDOVER_OPERATIVO.md`;
- `docs/ADR_INDEX.md`, quando viene introdotta una nuova decisione
  architetturale.

Ogni attività completata deve essere registrata cronologicamente in:

`docs/HANDOVER_OPERATIVO.md`

---

# Prossimo passo operativo

Il prossimo passo è l'inventario tecnico degli output prodotti da una
Rivoluzione Solare.

L'analisi deve partire da:

- struttura del database;
- file PHP coinvolti nel calcolo RS;
- strutture dati restituite dal motore;
- sessioni applicative;
- Rule ed evidenze generate;
- dati usati dal report annuale;
- informazioni calcolate ma non persistite.
