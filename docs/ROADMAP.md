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



# Manutenzione Ricerca RSM v3 (2026-07-26)

✅ Rifiniture completate

- percentuale con due decimali;
- barra mantenuta visibile al completamento.

Prossime attività:

- solo manutenzione correttiva senza modifiche architetturali.


# Manutenzione Ricerca — Astri nelle Case (2026-07-29)

✅ Correzione funzionale completata

- consentite più regole `NON VOGLIO` per lo stesso pianeta quando
  riguardano case differenti;
- mantenuto il vincolo di unicità per le regole `LO VOGLIO`;
- mantenuto il blocco delle regole duplicate sulla stessa casa;
- mantenuto il blocco delle combinazioni incompatibili `LO VOGLIO` /
  `NON VOGLIO` per lo stesso pianeta;
- modifica limitata al frontend
  `www/js/ricerca_astri.js`, senza variazioni al backend, alle API o al
  motore astrologico.

Verifiche completate:

- `node --check www/js/ricerca_astri.js`;
- `git diff --check`.


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

La Ricerca RSM non è più limitata agli aeroporti.

Il sistema supporta due modalità operative distinte:

- `solo_aeroporti`;
- `solo_localita`.

La precedente modalità mista `aeroporti_e_localita` è stata rimossa per
separare in modo esplicito la ricerca aeroportuale dalla ricerca sulle
località geografiche.

La condizione astrologica resta il filtro principale. In modalità
`solo_localita`, i calcoli utilizzano sempre le coordinate effettive della
località selezionata. Eventuali codici IATA, ICAO o aeroporti associati
rimangono informazioni opzionali e non determinano il punto di calcolo.

------------------------------------------------------------

### Obiettivi completati

✔ mantenere la compatibilità con la Ricerca RSM storica sugli aeroporti

✔ estendere la ricerca a tutte le località geografiche attive

✔ mantenere elevate prestazioni

✔ non modificare il motore astrologico

✔ utilizzare le coordinate del punto selezionato

✔ distinguere esplicitamente aeroporti e località

✔ garantire risultati deterministici nella deduplicazione

------------------------------------------------------------

### FASE 1 — COMPLETATA
Analisi del modello geografico

- censimento tabelle GeoNames;
- censimento aeroporti;
- classificazione feature code;
- studio relazioni città ↔ aeroporto;
- definizione del modello geografico.

------------------------------------------------------------

### FASE 2 — COMPLETATA
Nuovo modello geografico

Il repository tratta aeroporti e località come punti geografici compatibili,
mantenendone però distinta l'origine.

Ogni risultato espone il campo:

- `origine_punto = aeroporto`;
- `origine_punto = localita`.

------------------------------------------------------------

### FASE 3 — COMPLETATA
Backend

Il parametro `tipo_localita` supporta esclusivamente:

- `solo_aeroporti`;
- `solo_localita`.

La Streaming API rifiuta modalità non previste.

------------------------------------------------------------

### FASE 4 — COMPLETATA
Query SQL e deduplicazione

La sorgente geografica espone:

- coordinate;
- nome;
- città;
- tipo;
- nazione;
- popolazione, quando disponibile;
- aeroporto associato, quando disponibile;
- codici IATA e ICAO, quando disponibili;
- `origine_punto`.

La deduplicazione SQL mantiene la precedenza prevista dalla modalità di
ricerca e usa un ordinamento deterministico basato su:

- priorità dell'origine;
- nazione;
- latitudine;
- longitudine;
- nome;
- città;
- ICAO;
- IATA.

------------------------------------------------------------

### FASE 5 — COMPLETATA
Interfaccia

Aggiornamenti realizzati:

- filtro `Tipo località` limitato a:
  - `solo_aeroporti`;
  - `solo_localita`;
- invio del parametro `tipo_localita` alla Streaming API;
- distinzione del risultato tramite `origine_punto`;
- visualizzazione del nome completo;
- visualizzazione della popolazione quando disponibile;
- visualizzazione opzionale dei codici IATA e ICAO;
- utilizzo delle coordinate del punto selezionato;
- compatibilità preservata con il comportamento aeroportuale legacy.

------------------------------------------------------------

### FASE 5A — PIANIFICATA
Ricerca nazionale delle località

Motivazione

La ricerca mondiale sulle località geografiche risulta eccessivamente ampia
(oltre cinque milioni di punti) e comporta tempi di elaborazione non
compatibili con la Ricerca RSM.

Per questo motivo la modalità `solo_localita` verrà limitata ad una singola
nazione per ogni ricerca.

Funzionalità previste

- visualizzazione del selettore **Nazione** quando viene scelta la modalità
  `solo_localita`;
- selezione della nazione obbligatoria;
- visualizzazione del limite massimo di risultati:
  - 50 (predefinito);
  - 100;
  - 150;
  - Tutte;
- ricerca limitata esclusivamente alla nazione selezionata;
- inclusione di tutte le località geografiche della nazione
  (città, paesi, villaggi, borghi, frazioni e insediamenti minori),
  indipendentemente dalla presenza di un aeroporto;
- mantenimento della ricerca mondiale senza limitazioni nella modalità
  `solo_aeroporti`.

------------------------------------------------------------

### FASE 6 — COMPLETATA
Prestazioni e determinismo

Completati:

- deduplicazione SQL;
- ordinamento deterministico;
- equivalenza tra sequenza PHP e sequenza SQL;
- mantenimento delle prestazioni su dataset geografici estesi.

------------------------------------------------------------

### FASE 7 — COMPLETATA
Test

Verifiche superate:

✔ `legacy_solo_aeroporti`

✔ `v3_solo_aeroporti`

✔ `v3_solo_localita`

✔ equivalenza deduplicazione PHP/SQL: `851` risultati

✔ lint PHP

✔ compilazione sintattica dello script Python

✔ `git diff --check`

------------------------------------------------------------

### Rilascio locale

- branch: `fix/rsm-v3-localita-nazione-obbligatoria`;
- commit: `900c8f9`;
- descrizione: `Rimuove modalità mista e distingue aeroporti e località`;
- repository Git mantenuto esclusivamente in locale.

------------------------------------------------------------

### Stato attuale

La Ricerca RSM v3 è completata secondo il modello a due modalità.

Gli aeroporti e le località restano entrambi supportati, ma vengono ricercati
separatamente e identificati esplicitamente tramite `origine_punto`.

La selezione obbligatoria della nazione e il limite 50/100/150/Tutte per
`solo_localita` restano pianificati nella FASE 5A.
