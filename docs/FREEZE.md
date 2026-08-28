# FREEZE — Cosa e' congelato, cosa e' disallineato tra branch

Leggere questo documento PRIMA di qualunque intervento su regole, veti,
o file che compaiono nella Sezione 2. Obiettivo: che chi riprende il
progetto, anche dopo un anno, sappia in pochi secondi cosa puo'
toccare, cosa no, e se un fix che sta guardando esiste davvero anche
sul branch su cui si trova.

---

## Sezione 1 — Sistemi di regole congelati

Esistono DUE sistemi di regole distinti, senza alcun legame tra loro,
che condividono solo il nome generico "Rule Engine". Non confonderli.

### Sistema A — Rule Engine 120 Rule (forecast)
- Percorso: www/includes/forecast/rules/ (120 file, Rule0001...Rule0120*.php)
- Scopo: interpretazione narrativa dei transiti pianeta/casa per la
  "Relazione Annuale" (Annual Report / Narrative Engine).
- Non incide sulla ricerca RSM/RL.
- Freeze: formale permanente, ADR-012 (docs/ADR_INDEX_ASTROLAB.md su main).
- Si tocca solo per: bug bloccante documentato o incompatibilita'
  tecnica esplicita. Mai per estendere le regole.

### Sistema B — RuleEngine 34 regole di Discepolo
- Percorso: www/includes/RuleEngine.php, RuleEngineExtended.php
- Scopo: veti/punteggio per la ricerca RSM/RL (dove trovare la citta'
  giusta). Questa e' LA BIBBIA operativa del progetto attuale.
- Freeze: informale e modificabile, ma ogni modifica al comportamento
  di default va registrata PRIMA come decisione in
  docs/ux-myastral/DECISION_LOG_ux.md (UX-XXXX).
- Si tocca: sempre, ma solo dopo aver scritto la decisione UX
  corrispondente. Mai codice prima della decisione.
- Regole a scarto automatico incondizionato: 4, 5, 31, 32, 34
  (confermato dal committente in UX-0013/UX-0014). Ogni altra regola
  (es. 33) e' un principio interpretativo, non un criterio di
  esclusione automatica, salvo diversa decisione UX esplicita.

---

## Sezione 2 — Registro divergenze tra branch

Casi noti di fix o feature che esistono SOLO su alcuni branch. Prima
di assumere che un fix "esiste gia'" perche' fatto in una sessione
precedente, controllare qui su quale branch. Riferimento completo di
ogni caso in docs/HANDOVER_OPERATIVO_astrolab.md (cercare la data).

### Nome posizione in header dopo "USA QUESTA POSIZIONE" su mappa
- Data: 2026-08-21
- File: www/rs.php, funzione usaPosizione()
- Commit originale: 3108ced
- Esiste su: fix/nome-posizione-mappa, feature/2-astri-in-cuspide
  (portato il 2026-08-22, commit d196742)
- NON esiste su: fase9-comparator-quota, main,
  chore/porta-feature-da-allineamento-myastral
- Dettagli: HANDOVER 2026-08-21 e 2026-08-22 quater

### Fix confronto RSM/RL (r.nome mancante)
- File: www/ricerca.php, funzione getRisultatiConfronto()
- Commit originale: 1e4c8bb
- Esiste su: fase9-comparator-quota,
  chore/porta-feature-da-allineamento-myastral
- NON esiste su: feature/allineamento-myastral,
  feature/2-astri-in-cuspide, main
- Stato: non ancora verificato se impatta questo branch

### Fix header sticky tabella risultati (due versioni diverse)
- Data: 2026-08-19/22, portato su questo branch il 2026-08-22
- Versione superata (rimossa qui il 2026-08-22): feature/allineamento-myastral
  (commit 5eca91, d30fd1d, 338b923)
- Versione buona (definitiva), origine: chore/porta-feature-da-allineamento-myastral
  (commit f914d2b)
- Portata su feature/2-astri-in-cuspide il 2026-08-22 (patch equivalente,
  senza il ripristino pulsante RL incluso nel commit originale, non necessario qui)
- NON ancora portata su main
- Dettagli: HANDOVER 2026-08-19, 2026-08-22 (sessione pomeridiana), nota su branch chore

### Regressione ricorrente div time-controls (rs.php)
- Causa: lavoro parallelo su feature/allineamento-myastral e
  fase9-comparator-quota mai riunito
- Stato: bug, non un fix. Il div e' CONGELATO su richiesta utente
  finche' non si decide come riallineare le due linee
- Dettagli: HANDOVER 2026-08-21, nota su branch chore

### Collisione di numerazione UX-0008 (2026-08-20)
- Due decisioni diverse (veto Marte X Casa, Regola 33) hanno usato
  lo stesso ID lo stesso giorno
- Risolta con rinumerazione UX-0008 -> UX-0013, commit 9ff6cbd
- Regola pratica: verificare sempre l'ultimo numero libero con un
  grep mirato prima di assegnarne uno nuovo, mai a memoria

### Fix geocoding Nominatim (ordina risultati + etichetta tipo)
- Data: 2026-08-23
- Bug: coordinate imprecise per luogo di nascita/residenza (dettagli in
  docs/BUG_GEOCODING_NOMINATIM.md e docs/ROADMAP.md, sezione "BUG APERTO")
- File coinvolti: www/js/app.js (cercaLuogo, cercaLuogoResidenza),
  www/rs.php (cercaLuogoRS), www/rilocazione.php (cercaLuogoRiloc);
  stesso pattern anche in www/rl.php (via app.js) e www/transiti.php
  (solo su fase9-comparator-quota, non su main)
- Esiste su: fix/geocoding-nominatim-precisione (base origin/main) -
  SOLO PARZIALE: fatto www/js/app.js, mancano ancora
  www/rs.php e www/rilocazione.php
- NON esiste su: new_dashboard, feature/2-astri-in-cuspide, main,
  fase9-comparator-quota, tutti gli altri branch
- Dettagli: HANDOVER 23-08-2026

---

Nota sulla causa comune: feature/allineamento-myastral e
fase9-comparator-quota sono divergenti dal commit comune a52c2e9, mai
riallineati tra loro. Ogni voce sopra con "NON esiste su" che include
uno di questi due branch e' un sintomo di questa stessa causa. La
riconciliazione delle due linee resta un'attivita' a se', non ancora
pianificata.
