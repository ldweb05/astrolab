# Cronologia dello sviluppo Astro-DSS (progetto successivamente confluito in ASTROLAB)

Documento storico dello sviluppo del progetto Astro-DSS precedente alla fusione in ASTROLAB.

Deve essere aggiornato al completamento di ogni milestone significativa.

> **Nota**
> La roadmap relativa alla registrazione utenti, autenticazione, gestione dei piani (`free`/`supporter`), permessi, quote, Comparator, Annual Report e relative fasi di implementazione è mantenuta separatamente nel documento `docs/roadmap_registrazioneutenti.md`, che costituisce il riferimento ufficiale per tale macro-funzionalità.
>
> La roadmap relativa alla comparazione funzionale tra Astrolab e MyAstral.org è mantenuta separatamente nel documento `docs/roadmap_comparazione_myastral.md`, che costituisce il riferimento ufficiale per le attività di allineamento con il software di Ciro Discepolo.
>
> La roadmap relativa alla ricerca RSM "Astri in Cuspide" (pianeti in cuspide di casa con orbo Regola 32, feature Supporter-gated) è mantenuta separatamente nel documento `docs/ROADMAP_2_ASTRI_IN_CUSPIDE.md`, sul branch `feature/2-astri-in-cuspide`, che costituisce il riferimento ufficiale per tale funzionalità.
>
> Il sistema valutativo stelline "V2" (logica additiva per colore) è stato promosso a sistema PRIMARIO in produzione (branch `feature/sostituzione-stelline-v2`, da `new_dashboard`): sostituisce il vecchio punteggio di `RuleEngine::valuta()` per ordinamento/filtro/visualizzazione su RSM, RL, viste singole ed endpoint secondari. Documentazione ufficiale della migrazione: `docs/ROADMAP_SOSTITUZIONE_STELLINE_V2.md`. Lo studio/laboratorio originale resta in `docs/ROADMAP_STELLINE_V2.md` per riferimento storico.
>
> `docs/ROADMAP.md` continua invece a descrivere l'evoluzione generale di Astro-DSS e delle funzionalità principali del progetto.
>
> La roadmap relativa all'integrazione di un AI Agent in ASTROLAB (Google Gemini come primo provider LLM, poi altri provider a seguire) è mantenuta separatamente nel documento `docs/ROADMAP_AI.md`, che costituisce il riferimento ufficiale per tale percorso.

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

Aggiornamento:
- la macro-funzionalità di registrazione utenti, autenticazione, piani `free`/`supporter`, limiti, permessi, Comparator, Annual Report e sicurezza delle sessioni è completata;
- il riferimento ufficiale resta `docs/roadmap_registrazioneutenti.md`;
- la decisione architetturale associata è ADR-016 (stato: `Accettata`).


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

# HTTPS tramite Caddy

⏳ Pianificata

Obiettivo:

abilitare la pubblicazione di ASTROLAB tramite Caddy come reverse proxy con
terminazione HTTPS, utilizzando il dominio configurato e mantenendo invariata
l'architettura Docker dell'applicazione.

Attività previste:

- introduzione del container Caddy;
- configurazione del reverse proxy verso il container `astrolab-web`;
- gestione automatica dei certificati TLS;
- aggiornamento della configurazione Docker;
- aggiornamento della documentazione operativa;
- verifica del funzionamento tramite HTTPS.

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

## Sezione Aiuto e Manuale d'Uso

La progettazione e lo sviluppo del menu "Aiuto" e del manuale d'uso integrato nell'applicazione sono tracciati nella roadmap dedicata:
- `docs/roadmap_aiuto.md`

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

### FASE 5A — COMPLETATA
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

La pagina di ricerca mantiene inoltre lo stato dell'ultima ricerca (risultati,
pagina corrente e principali filtri) quando si apre una RS e si ritorna con il
pulsante Indietro del browser, evitando di dover ripetere la ricerca.

La selezione obbligatoria della nazione e il limite 50/100/150/Tutte per
`localita` sono stati implementati e completati nella FASE 5A.

## 2026-08-07 — Codifica colore semantica dei pianeti sulla ruota

✅ Completata

- rosso = pianeta in moto diretto;
- blu = pianeta retrogrado;
- verde = pianeta esattamente in cuspide (tolleranza tecnica 0.01°);
- componente modificato: `www/js/zodiac_wheel.js`;
- riusato `ZodiacWheel.disegna()` senza duplicazioni;
- nessuna modifica al motore astrologico, alle API o al Rule Engine.

## BUG APERTO — Header sticky tabella risultati si sovrappone alla prima riga (ricerca.php, ricerca_rl.php)

⚠️ Da correggere in una sessione dedicata futura

- **Sintomo:** l'header sticky (`.tabella-risultati th`, `position: sticky; top: 56px`)
  della tabella risultati copre/taglia parzialmente la prima riga di risultati quando
  la finestra del browser è a larghezza naturale/ampia; il problema sparisce
  ridimensionando la finestra a una larghezza minore (comportamento intermittente
  legato alla larghezza della finestra, causa non ancora identificata con certezza).
- **Tentativi già fatti, senza successo:** aggiunto `z-index: 5` alla regola
  (nessun effetto); aggiunto `overflow-y: visible` esplicito al div
  `overflow-x:auto` che avvolge la tabella, per escludere che il wrapper diventasse
  un contenitore di scroll indipendente rompendo il calcolo dello sticky (nessun
  effetto, confermato via ispezione DOM in console: `wrapper.getBoundingClientRect()`
  e `getComputedStyle(wrapper).overflowY` restavano `auto` anche dopo il fix,
  suggerendo che il problema non è (solo) lì).
- **Escluso:** nessuna media query nota cambia l'altezza dell'header fisso (56px)
  a larghezze di finestra >900px (dove il menu resta inline, non ad hamburger);
  nessun `transform`/`will-change`/`contain`/`filter` sugli antenati della tabella
  che potrebbe creare un containing block alternativo; nessun listener JS su
  `resize` che ri-renderizzi la tabella (quindi il "fix" ottenuto ridimensionando
  la finestra è un genuino effetto di ricalcolo layout del browser, non un
  side-effect di codice JS).
- **Soluzione tampone applicata (21-08-2026):** aggiunto uno spacer trasparente
  di 8px (`<div style="height:8px" class="tabella-risultati-spacer"></div>`)
  subito prima del div `overflow-x:auto` che avvolge ciascuna tabella risultati,
  in tutti e 4 i punti di rendering di `ricerca.php` e `ricerca_rl.php`. Attenua
  visivamente il problema ma non lo risolve alla radice.
- **Da fare in una sessione dedicata:** diagnosi approfondita (probabilmente serve
  ispezione live con DevTools a più larghezze di finestra, verificando il valore
  calcolato di `top` sull'elemento sticky e la posizione esatta della prima riga
  `tbody` rispetto ad esso ad ogni larghezza) e correzione definitiva, poi
  rimuovere lo spacer temporaneo.

---

## BUG APERTO — Geocoding Nominatim impreciso per luogo di nascita/residenza (scoperto 23-08-2026)

⚠️ Da correggere in una sessione dedicata futura — branch aperto:
`fix/geocoding-nominatim-precisione` (base: `origin/main`), lavoro iniziato ma
non completato (bloccato da un problema di login legato alla divergenza tra
branch, vedi `docs/FREEZE.md` e `docs/HANDOVER_OPERATIVO_astrolab.md`)

Documentazione completa: `docs/BUG_GEOCODING_NOMINATIM.md`

- **Sintomo:** cercando una città (es. "Caserta", "Zurigo") nei campi di
  ricerca località con geocoding automatico via Nominatim, le coordinate
  salvate possono corrispondere a un'area amministrativa più ampia
  (provincia/regione/cantone) invece che al centro città preciso — scostamento
  di diversi km, verificato visivamente tramite la mappa aggiunta a
  `dashboard.php` sul branch `new_dashboard`.
- **Causa confermata:** la query a Nominatim
  (`nominatim.openstreetmap.org/search?q=...`) non filtra per tipo di
  risultato (`addresstype`). A volte il risultato più "importante" secondo
  Nominatim (e quindi il primo in lista) è un'area amministrativa
  (`addresstype: county/state`) invece del punto città (`addresstype:
  city/town`); in altri casi il risultato corretto è primo ma il dropdown non
  distingue visivamente i tipi, favorendo la scelta sbagliata per errore
  umano (caso osservato con "Zurigo": scelto per errore il Canton Zurigo
  invece della città).
- **Superficie del bug:** `cercaLuogo()` e `cercaLuogoResidenza()` in
  `www/js/app.js` (luogo di nascita e residenza); `cercaLuogoRS()` in
  `rs.php`; `cercaLuogoRiloc()` in `rilocazione.php`; stesso pattern anche in
  `rl.php` (via `app.js`) e in `transiti.php` (feature presente solo su
  `fase9-comparator-quota`, non su `main` — da propagare separatamente).
  **Confermato NON coinvolto:** `ricerca.php` (motore RSM per condizione),
  che lavora su un database di località pre-caricato, non su geocoding live.
- **Fix in corso (parziale, committato su `fix/geocoding-nominatim-precisione`):**
  aggiunta in `www/js/app.js` di due helper (`_nominatimOrdinaRisultati`,
  `_nominatimEtichetta`) che spostano i risultati troppo generici
  (county/state/country/region/state_district) in fondo alla lista senza
  eliminarli, ed etichettano il tipo di ciascun risultato nel dropdown.
  Applicato a `cercaLuogo()` e `cercaLuogoResidenza()`. **Restano da fare:**
  `cercaLuogoRS()` in `rs.php`, `cercaLuogoRiloc()` in `rilocazione.php`, e
  valutare la propagazione a `transiti.php` su `fase9-comparator-quota`.
- **Nota operativa importante:** passare a un branch diverso (es. da
  `new_dashboard` a `main`) su un ambiente live cambia il codice servito ma
  NON la sessione PHP attiva né lo schema del database — se il branch di
  destinazione ha una versione più vecchia di `Auth.php`/`login.php`
  incompatibile con l'utente corrente, il login può entrare in loop. Successo
  qui: `main` confronta l'username in modo case-sensitive/esatto, mentre
  `new_dashboard` lo fa case-insensitive con `TRIM()` — cambiare branch ha
  temporaneamente impedito l'accesso, risolto tornando su `new_dashboard`.
- **Da fare in una sessione dedicata:** completare `cercaLuogoRS()` e
  `cercaLuogoRiloc()` sul branch `fix/geocoding-nominatim-precisione`,
  verificare se propagare a `transiti.php`, poi decidere come/quando fondere
  il fix sui branch `main`/`new_dashboard`/altri; valutare anche se soggetti
  già esistenti hanno coordinate imprecise da ricorreggere manualmente.

---

## Estensione architettura a gerarchia alle condizioni restanti (Denaro, Denaro Low)

Al completamento di UX-0019 (condizione Lavoro), 3 delle 7 condizioni disponibili (Decima, Amore, Lavoro) erano sul modello a gerarchia a livelli. Con UX-0020 (Salute, 2026-08-30) e UX-0021/UX-0023 (Casa, 2026-08-31) sono ora 5 su 7; restano solo Denaro e Denaro Low. UX-0023/UX-0024 hanno inoltre introdotto un requisito trasversale obbligatorio per ogni condizione a gerarchia (esclusione veti ufficiali delle 34 regole + retrocessione per veto proprietario/alert stellium misto), da includere fin dalla progettazione di Denaro e Denaro Low. Tracciata nella roadmap dedicata:
- `docs/ROADMAP_GERARCHIA_CONDIZIONI_RESTANTI.md`

---

## 2026-09-19 — Dashboard: grafici Tema Natale + RS (completata; fase 2 non necessaria)

Riferimento operativo dettagliato: `docs/roadmaps/roadmap_nuova_dashboard.md`
(sezione "Grafici Tema Natale + RS in dashboard — reintroduzione (19-09-2026)").

- `dashboard.php` (pagina di destinazione dal click sul soggetto in `index.php` e dal logo)
  mostra ora, tra i pulsanti Transiti/Rilocazione e la mappa, i grafici Tema Natale e RS;
  il riquadro centrale e' stato allargato.
- Fase 1 completata: grafici, pulsanti Cuspidi/Gradi, riga ASC/MC. Modificato solo
  `www/dashboard.php`; nessun file condiviso toccato. Data/ora GMT calcolate lato PHP con
  l'helper esistente (`NascitaGmtHelper.php`), verificate anche con un soggetto "a rischio".
- Fase 2 (pulsante "Mostra Dati") non necessaria: decisione del committente del 19-09-2026,
  sulla dashboard non serve. Punto di ripristino della fase 1: tag
  `restore/dashboard-grafici-fase1-2026-09-19` (main @ 0227feb).
- Ribaltata la decisione del 23-08-2026 che aveva eliminato i pannelli grafico dalla dashboard.
- Lavoro fatto su `main`; punto di ripristino: tag `restore/pre-dashboard-grafici-2026-09-19`
  (main @ 2ba830c), presente sul Pi e su GitHub.
- Cronologia completa: `docs/HANDOVER_OPERATIVO_astrolab.md`, voce 2026-09-19.

---

## 20-09-2026 — SEO: audit e primo intervento (titoli)

Riferimento operativo dettagliato: `docs/roadmaps/ROADMAP_SEO.md`.

- Obiettivo: in previsione del trasferimento su VPS pubblica, rendere ASTROLAB compatibile
  con i motori di ricerca. Oggi la fase e' di studio e pianificazione: le Fasi 2-7 della
  roadmap SEO sono una proposta non ancora approvata dal committente.
- Audit: non esistono `robots.txt` e `sitemap.xml`; le pagine non hanno meta description,
  canonical ne' Open Graph. Le pagine pubbliche sono solo `login`, `registrazione`,
  `verifica-email`, `logout` e `34_regole.html`: il resto richiede login, quindi per un
  crawler il sito e' di fatto una pagina di login.
- Fase 1 completata: `<title>` uniformati al formato "Nome pagina — AstroLab" in 15 file
  (commit `261f787` su `main`, solo righe `<title>`). Punto di ripristino: tag
  `restore/pre-seo-title-2026-09-20` (main @ 14f9afa). Test funzionale nel browser: OK.
- Decisioni aperte (anonimato e obblighi legali, uso di "Astrologia Attiva" e delle 34 regole
  nelle pagine pubbliche, manuale pubblico o no, lingue, dominio): elencate in
  `docs/roadmaps/ROADMAP_SEO.md`.
- Cronologia completa: `docs/HANDOVER_OPERATIVO_astrolab.md`, voce 20-09-2026.

---

## 29-09-2026 — Diario RSM, CODICE automatico e login dei soggetti (pianificazione)

Riferimenti operativi dettagliati: `docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md` e
`docs/roadmaps/ROADMAP_DIARIO_RSM.md`.

- Obiettivo: permettere ai soggetti di accedere ad ASTROLAB con il proprio CODICE per
  salvare i viaggi fatti per una RSM (localita', periodo, albergo, costi, trasporti, note) e
  condividere con astrologi e soggetti le sole informazioni di viaggio, senza alcun dato del
  soggetto (nascita, tema, RS).
- Ordine di lavoro: blocco A (CODICE automatico: prefisso di 2 lettere dell'astrologo +
  numero progressivo, es. `LD001`, `RF120`), blocco B (login dei soggetti con sessione e
  credenziali separate da quelle degli astrologi), blocco C (Diario RSM). Migrazioni:
  `sql/010` e `sql/011` (blocco A, applicate), `sql/012` (blocco B, applicata), `sql/013`
  (blocco C).
- Stato: design concordato con il committente (decisioni C1-C12 e D1-D16). Blocco A
  completato il 29-09-2026 (commit `9f612a5`, `1d0f278`, `f0fc22f`, `b13a33a`); blocco B
  completato il 29-09-2026 (commit `a727f7b`, `61e1a7b`, `900ba5a`); blocco C (Diario RSM)
  completato il 30-09-2026 (commit `6b7ec08`, `f84434d`, `bd4426d`, `fc717cd`, `2afd6e4`,
  `7bfbf92` e chiusura documentale). Lavoro concluso. La feature e' progettata fin da subito per la futura VPS
  pubblica (CSRF, blocco tentativi di login, escaping, noindex).
- Cronologia completa: `docs/HANDOVER_OPERATIVO_astrolab.md`, voci 29-09-2026 (blocchi A e B)
  e 30-09-2026 (blocco C).

---

## BUG RISOLTO — Eliminazione utente con trasferimento dei soggetti a se stesso (scoperto 29-09-2026, corretto 30-09-2026)

Stato: **risolto il 30-09-2026** (fix rimandato il 29-09-2026 e applicato il giorno dopo; test nel
browser OK: astrologo di prova con un soggetto eliminato dall'admin, menu senza l'utente da
eliminare, soggetto trasferito con codice invariato). Non causato dal
lavoro sul CODICE automatico: e' un difetto preesistente, emerso durante i test del blocco A.

- Sintomo: in `admin_utenti.php` l'eliminazione di un utente che ha soggetti termina con
  "Fatal error: Uncaught PDOException: SQLSTATE[23502] ... null value in column utente_id of
  relation soggetti" se nel menu "Trasferisci i soggetti a" e' selezionato l'utente stesso.
- Causa: il menu del modale elenca tutti gli utenti, compreso quello da eliminare.
  `Auth::eliminaUtente($id, $trasferisciA)` non controlla che `$trasferisciA` sia diverso da
  `$id`: l'UPDATE dei soggetti non cambia nulla, poi il DELETE dell'utente attiva la FK
  `soggetti_utente_id_fkey` (ON DELETE SET NULL) su una colonna NOT NULL e il DB rifiuta.
  Trasferimento ed eliminazione non sono in una transazione. Il DB resta comunque integro
  (verificato il 29-09-2026: nessuna modifica dopo l'errore).
- Workaround: nel modale scegliere un utente diverso da quello da eliminare, oppure eliminare
  prima i soggetti dell'utente e poi l'utente (pulsante di eliminazione diretta).
- Fix applicato (progettato il 29-09-2026, ricollaudato il 30-09-2026 sulla versione attuale di
  `Auth.php`: 9 casi su 9), due file:
  1. `www/includes/Auth.php`, `eliminaUtente()`: rifiuta trasferimento verso lo stesso utente
     o verso un utente inesistente (solo se ci sono soggetti da spostare), rifiuta un utente
     da eliminare inesistente, esegue UPDATE + DELETE in una transazione con rollback, e
     restituisce `['ok' => bool, 'errore' => string]` invece di un booleano.
  2. `www/admin_utenti.php`: usa `$result['errore']` nel messaggio e, in `apriModaleElimina()`,
     esclude dal menu l'utente da eliminare preselezionando il primo utente valido.
  I due file vanno applicati insieme, con un solo riavvio del container dopo entrambi
  (cambia il tipo restituito da `eliminaUtente()`). Collaudo sandbox: 9 casi su 9 OK
  (trasferimento a se stesso, destinazione inesistente, proprio account, utente inesistente,
  trasferimento valido con codice soggetto invariato, utente senza soggetti).
- Nota correlata: un soggetto trasferito mantiene il proprio CODICE (vedi
  `docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md`, nota dopo C8).

---

## PUNTO RISOLTO — Limite per IP del login (`loginRateLimit()`) (scoperto 29-09-2026, corretto 01-10-2026)

Stato: **risolto il 01-10-2026** (test nel browser OK: login ripetuti senza blocco, un soggetto
che sbaglia non blocca piu' il proprio astrologo, blocco per singolo account). Difetto preesistente,
emerso durante il test del blocco dei tentativi del login soggetti (Fase B2).

- Sintomo: durante il test, dopo il login di un soggetto, 5 password errate e un 6o tentativo,
  anche il login dell'astrologo `lodian` dallo stesso computer e' stato rifiutato con
  "Troppi tentativi di accesso. Riprova piu' tardi." per circa 15 minuti.
- Causa: `loginRateLimit()` in `www/login.php` (10 tentativi in 15 minuti per IP, file
  temporanei in `sys_get_temp_dir()`) ha tre limiti:
  1. conta **tutti** i tentativi, anche i login riusciti: chi entra ed esce spesso si blocca da solo;
  2. e' **condiviso da tutti gli utenti dello stesso IP** (famiglia, studio, stessa connessione):
     un soggetto che sbaglia la password puo' bloccare anche il proprio astrologo;
  3. i file temporanei si azzerano al riavvio del container.
- Rischio sulla VPS: se l'app sara' dietro un reverse proxy, `$_SERVER['REMOTE_ADDR']` potrebbe
  essere per tutti l'IP del proxy: un solo utente che sbaglia bloccherebbe l'accesso a tutti.
  Lo stesso vale per il limite per IP del login soggetti (tabella `tentativi_login_soggetti`).
- Correzione applicata (01-10-2026, decisioni del committente):
  1. si contano solo i tentativi **falliti**, salvati nel DB (tabella `tentativi_login`, IP solo
     come hash; migrazione `sql/014_blocco_login.sql`): 10 fallimenti in 15 minuti per IP
     (`Auth::ipBloccatoLogin()`); il vecchio `loginRateLimit()` a file e' stato rimosso;
  2. blocco per singolo account anche per gli astrologi (`Auth::loginAstrologo()`, colonne
     `utenti.tentativi_falliti` / `bloccato_fino`): 5 password errate -> 15 minuti, solo per
     quell'account; contano solo le credenziali errate; tempi di risposta uguali per username
     esistenti e inesistenti; `login()` invariato;
  3. IP reale dietro il reverse proxy **predisposto ma spento**: `www/includes/client_ip.php`
     (`astrolab_client_ip()`, usata da `login.php` e `registrazione.php`). Sul Pi non si
     configura nulla. **Sulla VPS** aggiungere nel file `.env` `TRUSTED_PROXIES=<IP del proxy>`
     (piu' indirizzi separati da virgola) e riavviare: solo le richieste che arrivano da quel
     proxy useranno `X-Forwarded-For`.
- Il limite per IP del login soggetti (5 errori in 15 minuti, solo tentativi falliti, nel DB)
  funziona correttamente: verificato il 29-09-2026 (vedi `ROADMAP_CODICE_LOGIN_SOGGETTI.md`, C12).

---

## BUG APERTO — Lista soggetti: dati inseriti come HTML senza protezione (XSS) (scoperto 30-09-2026)

Stato: **aperto, da correggere prima del trasferimento sulla VPS pubblica** (registrato su
decisione del committente il 30-09-2026). Difetto preesistente, notato durante la Fase B3 del
login soggetti e segnalato solo a fine lavoro.

- Sintomo potenziale: in `www/index.php`, `caricaSoggettiConDropdown()` costruisce le righe della
  lista soggetti concatenando i dati in una stringa HTML assegnata con `innerHTML`, senza
  escaping: `s.nome` (anche dentro l'attributo `onclick` di elimina), `s.luogo_nascita`,
  `s.nazione_nascita`, `s.residenza_luogo`, `s.residenza_nazione`; anche il riquadro del
  soggetto attivo (`infoEl.innerHTML`) inserisce il nome. Il `codice` oggi e' generato dal
  server (solo lettere e cifre).
- Rischio: un astrologo che desse a un proprio soggetto un nome contenente HTML o JavaScript lo
  farebbe eseguire nel browser di chi apre la lista; in particolare dell'**admin**, che vede i
  soggetti di tutti gli astrologi. Oggi basso (pochi utenti di fiducia), serio con la
  registrazione pubblica sulla VPS.
- Correzione indicata: inserire i dati con una funzione di escaping (o con `textContent`/DOM),
  e sostituire il nome nell'`onclick` con un attributo `data-` letto da JavaScript. Verificare
  lo stesso schema in `www/js/app.js` (14 usi di `innerHTML`, tra cui la vecchia
  `caricaSoggetti()`) e nelle altre pagine.
- Il codice nuovo del Diario RSM e del pulsante di accesso (`js/diario_rsm.js`,
  `js/accesso_soggetti.js`) usa gia' `textContent` e non ha questo problema.
- Ricognizione del 01-10-2026 (iniziale, non esaustiva): circa 140 usi di `innerHTML` in circa
  20 file; la maggior parte mostra dati calcolati (posizioni, case, aspetti, stelline) e non e'
  a rischio. Fonti di testo libero gia' individuate:
  1. dati dei soggetti scritti dall'astrologo (lista di `index.php`) — priorita' alta;
  2. note delle sessioni RS (`rs.php`, elenco "Sessioni RS salvate") — priorita' media;
  3. risultati della ricerca luoghi Nominatim/OpenStreetMap (menu dei luoghi in `www/js/app.js`):
     dati esterni modificabili da chiunque — priorita' alta.
- Piano di correzione (proposto al committente il 01-10-2026; ogni passo con collaudo, test del
  committente e commit):
  1. censimento completo di tutti gli usi di `innerHTML`, classificati in "testo libero, da
     correggere" e "solo dati calcolati, sicuro", con il risultato aggiunto a questa sezione;
  2. una funzione comune di protezione (`escHtml()`) in un punto condiviso;
  3. correzioni prioritarie: `index.php` e ricerca luoghi di `www/js/app.js` (file condiviso da 7
     pagine: Soggetti, Tema, RS, RL, Rilocazione, Transiti, Stampa — modifiche minime e test su
     ognuna);
  4. correzioni successive: note delle sessioni in `rs.php` e gli altri punti del censimento.
- **Censimento completato il 01-10-2026** (passo 1; analisi del codice nel sandbox, nessuna
  modifica): 138 inserimenti HTML (`innerHTML`, `insertAdjacentHTML`, `document.write`) in 20
  file. Classificazione dei punti con testo libero (numeri di riga al commit `ba8606b`):
  - **A. Testo scritto dagli utenti, vulnerabile:**
    1. **corretto il 01-10-2026** (passo 3, gruppo 2: testi con `escHtml()`, elimina da attributi
       `data-`, riquadro del soggetto attivo con `textContent`; collaudo 16 casi e test sul Pi)
       — `www/index.php` 329-371, lista soggetti: `s.nome` (anche nell'`onclick` di elimina, con
       escape del solo apice), `s.luogo_nascita`, `s.nazione_nascita`, `s.residenza_luogo`,
       `s.residenza_nazione`; riga 269, soggetto attivo: viene sostituito solo il carattere `<`
       — priorita' alta;
    2. **corretto il 01-10-2026** (passo 3, gruppo 2: `caricaSoggetti()` passa a
       `caricaSoggettiConDropdown()` quando esiste, vecchio disegno protetto con `escHtml()`; risolto
       anche il difetto per cui dopo salvataggio o eliminazione la lista ricompariva senza stella,
       accesso del soggetto e colonna Proprietario) — `www/js/app.js` 51-87, vecchia `caricaSoggetti()`, stessi campi; e' ancora chiamata dopo
       salvataggio ed eliminazione (righe 192 e 215) — priorita' alta;
    3. **corretto il 01-10-2026** (passo 3, gruppo 1: dati negli attributi `data-` con un solo
       gestore di clic, conferma di eliminazione da `data-conferma`, testo della finestra
       "Elimina" con `textContent`; risolto anche il pulsante di modifica che non funzionava con
       note su piu' righe; collaudo 11 casi e prova con un utente dal nome malevolo, sul Pi e
       nel sandbox) — `www/admin_utenti.php` 312-338, pulsanti "Modifica anagrafica" e "Gestisci piano":
       `nome_completo`, `email`, `telefono`, `note`, `note_piano` sono passati dentro stringhe
       JavaScript negli attributi `onclick` con `htmlspecialchars()`, che non protegge in quel
       contesto (il browser decodifica le entita' prima di eseguire lo script); `nome_completo`
       e' scritto da chiunque si registri; riga 724, `apriModaleElimina()` inserisce il nome con
       `innerHTML` (oggi lo username, che ha caratteri limitati) — priorita' alta;
    4. **corretto il 01-10-2026** (passo 4: testi delle sessioni con `escHtml()`, identificativi con
       `parseInt`) — `www/rs.php` 1562-1570, sessioni RS salvate: `s.luogo`, `s.note` — priorita' media;
    5. **corretto il 01-10-2026** (passo 4: come A4; corretto anche l'errore `</table>` nell'intestazione
       della tabella delle sessioni RL, che faceva finire le righe fuori dalla tabella) —
       `www/js/rl.js` 268, 302-306, sessioni RL e scelta della RS: `s.luogo`, `s.note` —
       priorita' media;
    6. `www/stampa.php` 698-888, report di stampa: nome, luogo e nazione del soggetto, luoghi di
       RS, RL e rilocazione — priorita' media;
    7. `www/compare_rs.php` 234 e `www/compare_ril.php` 234, confronti: nome del soggetto —
       priorita' media;
    8. `www/rs.php` 1025-1037, link Rome2Rio: il luogo finisce nell'attributo `href` senza
       codifica (`formatCittaUrl()` non toglie le virgolette) — priorita' media.
  - **B. Dati esterni (ricerca luoghi Nominatim/OpenStreetMap) — corretto il 01-10-2026** (passo 3,
    gruppo 3: voci dei menu con attributi `data-scelta` e testi con `escHtml()`, scelta gestita
    dalla nuova `collegaScelta()` di `www/js/sicurezza.js`, incluso anche in `rs.php`,
    `rilocazione.php`, `transiti.php`, `rl.php`; collaudo 24 casi con un risultato malevolo e
    test sul Pi delle 6 ricerche). Era: `display_name` nel
    menu dei risultati e nell'`onclick` (escape del solo apice) in `www/js/app.js` 275-281 e
    518-524, `www/rs.php` 1483-1484, `www/rilocazione.php` 1468-1469, `www/transiti.php` 500-501,
    `www/js/rl.js` 540-541 — priorita' alta.
  - **C. Dati della tabella `localita` (importazione GeoNames), rischio basso:** nome, citta' e
    nazione nei risultati di `www/ricerca.php` (1725-1810), `www/ricerca_rl.php` (1787-1872),
    `www/rilocazione.php` (1589-1595, anche in un `onclick`), `www/compare_rs.php` 207,
    `www/compare_ril.php` 178-195 — priorita' bassa, da correggere con la stessa funzione.
  - **D. Verificati sicuri:** testi inseriti con `textContent` o `value` (`rs.php` 1764,
    `rl.php` 333, risultati in corso di `ricerca.php`/`ricerca_rl.php`, `app.js` 44 e 128,
    `mostraMessaggio()`); nomi stampati da PHP con `htmlspecialchars()` in HTML normale
    (`header_nav.php`, `dashboard.php`); dati passati agli script con `json_encode()`, che
    codifica `/` e quindi impedisce di chiudere il tag `<script>`; tutti gli altri inserimenti,
    che mostrano solo dati calcolati (posizioni, case, aspetti, stelline, bonus e veti del
    motore, paginazione, messaggi del server).
- **Passo 2 completato il 01-10-2026:** nuovo file `www/js/sicurezza.js` con `escHtml()` (codifica
  i caratteri speciali dell'HTML, apici e accento grave compresi, per il contenuto e per gli
  attributi tra virgolette) e `urlSicuro()` (solo indirizzi http/https); collaudo 12 casi su 12.
  Decisioni del committente: funzione in un file comune, incluso con un tag `<script>` nelle
  pagine che lo usano (non in `app.js`, che non e' caricato da `admin_utenti.php`,
  `compare_rs.php`, `compare_ril.php`, `ricerca.php`, `ricerca_rl.php`); i dati scritti dagli
  utenti o arrivati da servizi esterni passano da attributi `data-` letti da JavaScript, mai
  dentro gli `onclick`. Il file non e' ancora incluso da nessuna pagina: lo si collega nel
  passo 3, pagina per pagina.
