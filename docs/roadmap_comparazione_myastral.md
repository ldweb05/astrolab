# Roadmap — Comparazione Astrolab / MyAstral.org

**Stato:** Avviata
**Ultimo aggiornamento:** 2026-08-07
**Priorità:** ALTA
**Collegamento:** `docs/ROADMAP.md` (roadmap principale)
**Collegamento UX:** `docs/ux-myastral/` (protocolli operativi)

---

## 1. Scopo

Questo documento raccoglie tutte le attività di comparazione funzionale
tra Astrolab e MyAstral.org (software ufficiale di Ciro Discepolo) al fine
di identificare e risolvere le discrepanze di calcolo, valutazione e
visualizzazione dei risultati.

La comparazione non ha lo scopo di copiare MyAstral.org, ma di garantire
che Astrolab produca risultati coerenti con i principi dell'Astrologia
Attiva della scuola di Ciro Discepolo, individuando eventuali filtri
troppo restrittivi o algoritmi divergenti.

---

## 2. Principi guida

- MyAstral.org è il riferimento funzionale per l'Astrologia Attiva.
- Astrolab non deve essere trasformato in MyAstral.org.
- Ogni discrepanza deve essere spiegata, non eliminata.
- Il Rule Engine di Astrolab (120 Rule, FREEZE) non deve essere modificato.
- La documentazione UX (`docs/ux-myastral/`) resta il riferimento per
  l'analisi comparativa dell'esperienza utente.
- Le modifiche applicative sono subordinate all'approvazione esplicita
  di una decisione comparativa.

---

## 3. Prerequisiti

- [ ] Acquisto account MyAstral.org.
- [ ] Accesso operativo al software per test comparativi reali.
- [ ] Definizione di almeno un soggetto di test condiviso.
- [ ] Definizione di almeno un anno RS di test condiviso.

---

## 4. Filtri di esclusione identificati in Astrolab

Durante l'analisi preliminare sono stati censiti i seguenti livelli di
filtro applicati durante la ricerca RSM. Ogni discrepanza con MyAstral.org
dovrà essere ricondotta a uno di questi livelli.

### Livello 1 — Deduplicazione geografica
- Bucket 0,3° lat × 0,5° lon (~33 km).
- Riduzione osservata: 60-95% delle località candidate.
- File: `www/includes/RicercaRSAirportRepository.php`.
- Decisione: ADR-015.

### Livello 2 — Rule Map radicale per condizione
- **Decima:** malevoli (MA/SA/UR/NE/PLU) in X.
- **Lavoro:** malevoli in VI e X.
- **Salute:** malevoli in VI e XII.
- **Denaro:** malevoli in II, VIII e X.
- **Denaro Low:** solo MA/SA/UR in II e VIII.
- File: `www/includes/RicercaRSFilters.php` — `getRuleMapEsclusione()`.

### Livello 3 — Filtri specifici per condizione
- **Amore:** benefici in V/VII con pre-ingresso 3°, malevoli esclusi,
  sicurezza uscita 2°.
- **Casa:** benefici in IV con pre-ingresso 3°, malevoli esclusi,
  sicurezza uscita 2°.
- **Salute:** tolleranza pre-ingresso 4°, esclusione Sole in XII,
  scudo benefico in I casa, rafforzamento ASC natale ±3°, protezione
  universale I/VI/XII.
- **Denaro:** benefici in II/VIII con pre-ingresso 3°, malevoli esclusi,
  sicurezza uscita 2°.
- **Denaro Low:** esclusione malevoli in II/VIII, sconfinamento 3°.
- File: `www/includes/RicercaRSFilters.php`.

### Livello 4 — FiltroEsclusione globale
- Sole RS in I/VI/XII.
- Marte RS in I/VI/XII.
- ASC RS in I/VI/XII del tema natale.
- Saturno RS in X.
- Stellium 3+ pianeti in qualsiasi casa RS.
- File: `www/includes/FiltroEsclusione.php`.

### Livello 5 — Soglie di streaming
- `streaming_min` (default 3 stelle) per invio SSE live.
- `stelline_min` (default 0) per inclusione finale.

---

## 5. Macro-attività pianificate

### M1 — Comparazione ricerche RSM (PRIORITÀ ALTA)
Obiettivo: identificare perché alcune località appaiono in MyAstral.org
ma non in Astrolab, nonostante i calcoli di Astrolab siano esatti.

- [ ] Definire almeno 3 soggetti di test rappresentativi.
- [ ] Definire almeno 2 anni RS per soggetto.
- [ ] Definire le 7 condizioni: Decima, Lavoro, Amore, Salute, Denaro,
  Denaro Low, Casa.
- [ ] Eseguire la stessa ricerca in Astrolab e MyAstral.org.
- [ ] Raccogliere screenshot e CSV dei risultati.
- [ ] Identificare località presenti in MyAstral.org ma assenti in Astrolab.
- [ ] Per ogni località mancante, determinare quale filtro Astrolab la esclude.
- [ ] Classificare le cause (dedup geografica, rule map, filtro specifico,
  FiltroEsclusione globale, database località).
- [ ] Proporre decisioni documentate in `docs/ux-myastral/DECISION_LOG_ux.md`.
- [ ] Eventuali modifiche applicative solo dopo approvazione.

### M2 — Comparazione Rivoluzioni Lunari (PRIORITÀ MEDIA)
Obiettivo: verificare coerenza del calcolo e della valutazione delle RL,
ed eventualmente implementare la ricerca della migliore località per RL
(funzionalità presente in MyAstral.org).

- [ ] Verificare coerenza degli istanti di RL tra Astrolab e MyAstral.org.
- [ ] Verificare coerenza della valutazione bonus/penalità.
- [ ] Valutare se implementare ricerca automatica della migliore località
  per una singola RL (estensione del motore RSM v3).
- [ ] Eventuale roadmap tecnica dedicata se implementazione corposa.

### M3 — Interpretazione narrativa del Tema Natale (PRIORITÀ MEDIA)
Obiettivo: verificare se Astrolab genera un'interpretazione narrativa
del tema natale come MyAstral.org.

- [ ] Confermare che `www/tema.php` attualmente mostra solo dati tecnici.
- [ ] Valutare se estendere Theme Engine e Narrative Engine al tema natale.
- [ ] Valutare se l'astrologo professionista è il target di tale
  interpretazione o se preferisce elaborarla manualmente.
- [ ] Eventuale roadmap tecnica dedicata se implementazione corposa.

### M4 — Sinastria / Couple Score (PRIORITÀ BASSA, ESCLUSA DAL COMMITMENT)
Obiettivo: funzionalità presente in MyAstral.org come "Affinity Couple 2.0".
Stato: **esclusa per decisione esplicita del committente**.
Non rientra nelle priorità attuali e non sarà sviluppata in questa roadmap.

---

## 6. Relazione con la documentazione UX

Questa roadmap è strettamente collegata alla documentazione UX già presente
in `docs/ux-myastral/`:

| Documento | Ruolo |
|---|---|
| `START_HERE_ux.md` | Punto di ingresso dell'analisi UX |
| `00_METODOLOGIA_ux.md` | Framework operativo |
| `11_PRINCIPI_UX_ASTROLAB_ux.md` | Vincoli permanenti |
| `03_RICERCA_RSM_ux.md` | Protocollo comparativo RSM (M1) |
| `06_RIVOLUZIONE_LUNARE_ux.md` | Protocollo comparativo RL (M2) |
| `07_TEMA_NATALE_ux.md` | Protocollo comparativo tema natale (M3) |
| `BACKLOG_ux.md` | Registro delle osservazioni |
| `DECISION_LOG_ux.md` | Registro delle decisioni approvate |

Ogni attività di comparazione deve seguire il workflow UX documentato
in `START_HERE_ux.md` e produrre evidenze verificabili prima di qualsiasi
modifica applicativa.

---

## 7. Vincoli tecnici

- Il Rule Engine (120 Rule) è in FREEZE e non può essere modificato.
- La deduplicazione geografica (ADR-015) non può essere rimossa senza
  nuova decisione architetturale.
- Il FiltroEsclusione globale è una scelta funzionale documentata e
  non può essere disattivato senza nuova decisione architetturale.
- Il dataset GeoNames è già importato (5.220.791 record in `localita`).
- Il dataset aeroporti è già importato.
- Il motore Swiss Ephemeris è stabile e deterministico.

---

## 8. Condizioni di successo

Per ogni macro-attività la comparazione è considerata conclusa quando:

- [ ] Sono stati eseguiti almeno 3 casi di test per ogni condizione.
- [ ] Ogni discrepanza è stata classificata per causa.
- [ ] Le decisioni sono state registrate in `DECISION_LOG_ux.md`.
- [ ] Le modifiche applicative approvate sono state implementate.
- [ ] La regressione completa (`www/tests/run.php`) resta verde.
- [ ] La documentazione tecnica e UX è allineata.
- [ ] Il commit è stato eseguito e tracciato in
  `HANDOVER_OPERATIVO_astrolab.md`.

---

## 9. Registro decisioni

| ID | Data | Decisione | Stato |
|---|---|---|---|
| COMP-001 | 2026-08-07 | La sinastria è esclusa dalle priorità attuali. | Accettata |
| COMP-002 | 2026-08-07 | Il lavoro di comparazione richiede account MyAstral.org. | In attesa |

---

## 10. Cronologia

| Data | Attività |
|---|---|
| 2026-08-07 | Avvio della roadmap. Analisi preliminare dei filtri Astrolab. |
