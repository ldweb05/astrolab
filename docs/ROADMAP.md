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

clone indipendente della versione stabile di Astro-Val.

Infrastruttura operativa:

- repository Git indipendente;
- stack Docker indipendente;
- database PostgreSQL indipendente;
- rete Docker indipendente;
- applicazione disponibile sulla porta `8192`;
- Adminer disponibile sulla porta `8193`;
- PostgreSQL disponibile sulla porta `5442`.

Il Rule Engine ereditato rimane invariato:

- 120 Rule registrate;
- Knowledge Coverage 100%;
- Full Regression disponibile;
- Rule Engine in freeze.

Lo sviluppo si è concentrato sull'implementazione del primo Comparator
Engine e dell'interfaccia di confronto.

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

---

# DSS V1 — Inventario e modello di confronto

✅ Completata

Sono stati censiti gli output dell'applicazione e definita la struttura
dei dati utilizzata per il confronto.

Il lavoro svolto ha permesso di identificare le informazioni necessarie
alla costruzione del payload di confronto e delle pagine dedicate al
Comparator Engine.

---

# DSS V2 — Comparator Engine

✅ Completata

Risultano già implementati:

- confronto multiplo delle Rivoluzioni Solari;
- confronto multiplo delle Rilocazioni;
- selezione fino a tre risultati;
- persistenza della selezione;
- costruzione del payload di confronto;
- pagine `compare_rs.php` e `compare_ril.php`;
- riepilogo dei soggetti confrontati;
- layout responsive;
- tabelle dei match astrologici;
- correzione dei warning PHP e delle inizializzazioni mancanti.

Sono inoltre stati completati:

- integrazione delle ruote astrologiche nel Comparator delle Rilocazioni;
- preservazione delle regole personalizzate delle case nel Comparator RS;
- consolidamento dell'interfaccia del Comparator RS;
- merge delle funzionalità nel branch `feature/v6.1`.

La milestone Comparator Engine può considerarsi conclusa.

Commit conclusivi della milestone:

- `984d4bd` — integrazione delle ruote astrologiche;
- `57fbba4` — preservazione delle regole personalizzate delle case;
- `faf8462` — merge in `feature/v6.1`;
- `37d15be` — consolidamento dell'interfaccia del Comparator RS.

---

# DSS V3 — Impact Evaluator e Rule Correlator

⏳ Pianificata

Obiettivo:

attribuire un significato decisionale alle differenze individuate dal
Comparator Engine correlando Rule, evidenze e priorità astrologiche.

---

# DSS V4 — Recommendation Engine

⏳ Pianificata

Obiettivo:

produrre una raccomandazione finale spiegabile e completamente
tracciabile.

---

# DSS V5 — Narrative e interfaccia di confronto

⏳ Pianificata

Obiettivo:

realizzare l'interfaccia definitiva del Decision Support System con
narrativa, spiegazioni e visualizzazione completa del confronto.

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

- `docs/README_ASTROLAB.md`;
- `docs/START_HERE.md`;
- `docs/ROADMAP.md`;
- `docs/HANDOVER_OPERATIVO_astrolab.md`;
- `docs/ADR_INDEX_ASTROLAB.md`, quando viene introdotta una nuova decisione architetturale.

Ogni attività completata deve essere registrata cronologicamente in
`docs/HANDOVER_OPERATIVO_astrolab.md`.

---

# Prossimo passo operativo

Il Comparator Engine costituisce ora la baseline stabile del progetto.

La prossima milestone riguarda l'avvio del livello decisionale del DSS:

- Difference Analyzer;
- Impact Evaluator;
- Rule Correlator;
- Recommendation Engine.

Il Rule Engine rimane congelato e non deve essere modificato.

Le nuove funzionalità dovranno utilizzare esclusivamente i risultati
prodotti dal Comparator Engine senza alterare la logica astrologica
ereditata da Astro-Val.


---

## Ricerca RS v2 — Riduzione spaziale SQL

**Decisione:** ADR-015

**Priorità:** strutturale

**Vincolo:** nessuna soglia minima di popolazione.

### Specifica e architettura

- [x] Importare il dataset GeoNames completo.
- [x] Mantenere ricercabili tutte le località attive.
- [x] Verificare la distribuzione dei bucket SQL.
- [x] Raccogliere la baseline della pipeline PHP.
- [x] Formalizzare ADR-015.
- [x] Aggiornare architettura e handover.
- [ ] Definire formalmente la formula dei bucket.
- [ ] Definire l'ordinamento deterministico dei rappresentanti.
- [ ] Formalizzare la precedenza aeroporto-località.
- [ ] Definire i casi limite geografici.

### Implementazione SQL

- [ ] Introdurre la riduzione spaziale in
      `www/includes/RicercaRSAirportRepository.php`.
- [ ] Mantenere invariato il contratto del Repository.
- [ ] Non modificare il file legacy `search_engine.php`.
- [ ] Mantenere tutte le località attive.
- [ ] Non utilizzare soglie minime di popolazione.
- [ ] Analizzare la query con `EXPLAIN (ANALYZE, BUFFERS)`.
- [ ] Verificare gli indici PostgreSQL necessari.
- [ ] Preservare la precedenza degli aeroporti.

### Confronto e regressione

- [ ] Mantenere temporaneamente la deduplicazione PHP.
- [ ] Eseguire SQL e PHP in parallelo durante i test.
- [ ] Confrontare automaticamente i conteggi dei bucket.
- [ ] Confrontare i rappresentanti selezionati.
- [ ] Verificare la precedenza degli aeroporti.
- [ ] Verificare ordinamento e determinismo.
- [ ] Coprire coordinate negative.
- [ ] Coprire i confini dei bucket.
- [ ] Coprire il meridiano 180 gradi.
- [ ] Coprire località senza popolazione.
- [ ] Coprire località omonime.
- [ ] Coprire aeroporto e località nello stesso bucket.
- [ ] Verificare l'assenza di regressioni astrologiche.
- [ ] Verificare la compatibilità della Streaming API.

### Benchmark

- [x] Registrare la baseline sul Raspberry Pi.
- [x] Misurare il caso Italia.
- [x] Misurare il caso Italia, Francia e Germania.
- [x] Misurare la fascia longitudinale da -81 a -79.
- [ ] Misurare la query SQL ottimizzata sul Raspberry Pi.
- [ ] Misurare la memoria PHP dopo la riduzione SQL.
- [ ] Misurare le righe trasferite PostgreSQL-PHP.
- [ ] Eseguire benchmark sul VPS.
- [ ] Eseguire test con ricerche concorrenti.
- [ ] Documentare latenza p50, p95 e p99.
- [ ] Confrontare i risultati con la baseline corrente.

### Attivazione

- [ ] Predisporre un rollback semplice.
- [ ] Attivare la pipeline SQL.
- [ ] Monitorare errori, memoria e latenze.
- [ ] Verificare il comportamento in produzione.
- [ ] Rimuovere la deduplicazione PHP solo dopo equivalenza verificata.
- [ ] Aggiornare definitivamente i test.
- [ ] Aggiornare handover e ADR con i benchmark finali.

### Evoluzioni successive

- [ ] Cache dei risultati.
- [ ] Metriche e osservabilità.
- [ ] Ricerca mondiale.
- [ ] Ricerca delle cuspidi.
- [ ] Ricerca delle angularità.
- [ ] Riuso per le rilocazioni.
- [ ] Valutazione di job asincroni.
- [ ] Valutazione di Redis.
- [ ] Valutazione di PostgreSQL separato.
- [ ] Valutazione di PostGIS mediante ADR dedicato.


# ==========================================================
## Ricerca RSM v3 — Località geografiche complete
# ==========================================================

### Visione

La Ricerca RSM non dovrà più essere limitata agli aeroporti.

L'obiettivo finale è permettere la ricerca astrologica su qualsiasi
località geografica disponibile nel database mondiale (GeoNames),
compresi piccoli paesi, villaggi, località remote, stazioni polari
e centri abitati privi di aeroporto.

Gli aeroporti continueranno ad essere supportati ma diventeranno
una particolare tipologia di località.

------------------------------------------------------------

### Obiettivi

✔ mantenere la compatibilità con la Ricerca RSM attuale

✔ estendere la ricerca a tutti i centri abitati

✔ mantenere elevate prestazioni

✔ nessuna modifica al motore astrologico

✔ utilizzare sempre le coordinate della località selezionata

✔ mostrare eventuali codici IATA / ICAO solo quando disponibili

------------------------------------------------------------

### FASE 1
Analisi del modello geografico

- censimento tabelle GeoNames
- censimento aeroporti
- classificazione feature code
- studio relazioni città ↔ aeroporto
- definizione modello "Località"

Output:
documentazione tecnica.

------------------------------------------------------------

### FASE 2
Nuovo modello Località

Introduzione del concetto unificato di:

- Aeroporto
- Eliporto
- Idroporto
- Città
- Villaggio
- Località

Ogni risultato dovrà rappresentare una Località.

------------------------------------------------------------

### FASE 3
Backend

Introduzione del parametro:

tipo_localita

valori previsti:

- solo_aeroporti
- aeroporti_e_localita
- solo_localita

------------------------------------------------------------

### FASE 4
Query SQL unificata

Creazione della sorgente unica delle località candidate.

Ogni record dovrà contenere:

- coordinate
- nome
- tipo
- nazione
- popolazione (quando disponibile)
- eventuale aeroporto associato
- eventuale IATA
- eventuale ICAO

------------------------------------------------------------

### FASE 5
Interfaccia

Aggiornamenti previsti:

- Nazione con nome completo
- filtro Tipo località
- colonna Località
- colonna Tipo
- IATA / ICAO opzionale

------------------------------------------------------------

### FASE 6
Prestazioni

Ottimizzazione SQL

Riduzione query

Caching

Benchmark

------------------------------------------------------------

### FASE 7
Test

Nuovi test automatici:

✔ solo aeroporti

✔ aeroporti + località

✔ solo località

✔ località senza aeroporto

✔ regressione completa

------------------------------------------------------------

### Criterio di completamento

La funzionalità sarà considerata completata quando la Ricerca RSM sarà
in grado di proporre qualsiasi località geografica del database mondiale,
mantenendo la piena compatibilità con il comportamento storico.
