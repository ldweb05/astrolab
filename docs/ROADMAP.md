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

- `docs/README.md`;
- `docs/START_HERE.md`;
- `docs/ROADMAP.md`;
- `docs/HANDOVER_OPERATIVO.md`;
- `docs/ADR_INDEX.md`, quando viene introdotta una nuova decisione architetturale.

Ogni attività completata deve essere registrata cronologicamente in
`docs/HANDOVER_OPERATIVO.md`.

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
