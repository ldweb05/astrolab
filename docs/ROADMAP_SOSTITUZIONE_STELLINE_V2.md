# ROADMAP — Sostituzione Sistema Stelline con V2 (definitiva)

**Data creazione:** 2026-08-26
**Branch di lavoro:** `feature/sostituzione-stelline-v2` (creato da `new_dashboard`)
**Stato:** IN CORSO — Fase 3 completata, avvio Fase 4 (rimozione vecchio sistema + chiusura)
**Documento studio/laboratorio di riferimento:** `docs/ROADMAP_STELLINE_V2.md`
**Prompt operativo di riferimento:** `docs/PROMPT_OPERATIVO_ASTROLAB.md` v2.0 — seguito alla lettera

---

## Obiettivo

Sostituire il sistema di valutazione stelline attuale (`RuleEngine::valuta()`, campo
`stelline`, 0-5 a tetto fisso) con il nuovo sistema V2 (`StellineV2Calculator`, additivo
per colore, allineato all'ordinamento reale delle ricerche RSM), su tutte le pagine ed
endpoint di produzione — non solo nel laboratorio admin-only. Il sistema vecchio resta
attivo in parallelo come rete di sicurezza fino a conferma esplicita finale di rimozione.

## Principi inviolabili (dal Prompt Operativo Standard v2.0)

1. Un file alla volta, patch sempre via script Python con verifica `old_str` univoco.
2. Verifica sempre nello stesso ordine: `php -l` nel container → `node --check` (se serve)
   → `git status` → `git diff --check` / `git diff` → `docker compose restart astrolab-web`
   → test funzionale reale nel browser.
3. Zero refactoring non richiesto. Nessuna modifica a `RuleEngine.php`.
4. File condivisi (`style.css`, `app.js`, ecc.): massima cautela, page-scoped quando possibile.
5. Commit solo su conferma esplicita, mai `git add .` / `git add -A`, sempre file elencati.
6. Passi piccoli: ogni sotto-fase è una singola modifica verificabile, non un blocco unico.

## Punti di ripristino — OBBLIGATORI ad ogni fase completata

Dopo ogni fase (o sotto-fase) verificata con successo e testata funzionalmente da te nel
browser:

1. `git add <file specifici>` (mai `-A`)
2. `git commit -m "checkpoint: <descrizione fase>"`
3. `git tag checkpoint-faseN-<slug>`
4. `git push origin feature/sostituzione-stelline-v2 --tags`

**Rollback in caso di problemi:**
- Per ispezionare uno stato precedente senza perdere il lavoro dopo: `git checkout <tag>`
- Per tornare indietro sul branch di lavoro (mai su `main`/`new_dashboard`):
  `git reset --hard <tag>`, poi se già pushato: `git push --force origin feature/sostituzione-stelline-v2`
- Per il singolo file: `git checkout <tag> -- <file>` (ripristina solo quel file dal checkpoint,
  lascia intatto il resto del lavoro successivo)

Nessuna fase successiva inizia se il checkpoint della fase precedente non è stato creato e pushato.

## Nota sul perimetro reale (verificato nel codice, non assunto)

`rilocazione.php` e `api/riloc_angolari_api.php` **non usano** `RuleEngine`/stelline — è una
feature diversa (angoli di rilocazione), quindi **non fa parte** di questa sostituzione.
Il perimetro reale verificato è quello elencato nella Fase 3.

---

## Fase 0 — Fix bug nel Calculator condiviso

- [x] 0.1 Fix ASC in X e ASC in casa condizione: scoperto che il codice originale
      controllava `$case['ASC']['casa']`, chiave mai esistente nella struttura reale
      (SweCalc::calcolaCasePlacido non la imposta) — il bonus non scattava mai. Corretto
      per mappare l'ASC di RS/RL sulle case del TEMA NATALE (via trovaCasaNatale), come
      da regola fondamentale confermata dal committente. Valore ASC in X corretto a 5★
      (non 4 come nella tabella pesi originale)
- [x] 0.2 Fix `trovaCasaNatale()`: chiavi `inizio`/`fine` inesistenti → `longitudine`
      (riuso della stessa logica di `SweCalc::trovaCasa()`)
- [x] 0.3 Verifica sintassi + restart container + test isolato con script PHP temporaneo
      su 3 scenari fittizi (ASC in X natale → 5★; ASC in casa bonus non-X, condizione
      Lavoro → 3★; ASC in casa neutra → 0★) — tutti e tre confermati corretti
- [x] 0.4 **Checkpoint:** commit `4deb822` + tag `checkpoint-fase0-bugfix-calculator` (pushato)

## Fase 1 — RSM di produzione (`ricerca.php` / `api/ricerca_stream_api.php`) — COMPLETATA

- [x] 1.1 Analisi mirata completata: campo `stelline` costruito in `costruisciRisultatoRicercaRS`,
      ordinamento/filtro individuati in `ricerca_stream_api.php`
- [x] 1.2 Calcolo V2 aggiunto in parallelo (additivo) — commit `d20583c`
- [x] 1.3 Verifica + restart + test funzionale (confermato via EventStream, nessuna differenza)
- [x] 1.4 **Checkpoint:** `checkpoint-fase1a-v2-parallelo-rsm`
- [x] 1.5 Colonna V2 affiancata a Stelle in `ricerca.php` — commit `0a29c58`
- [x] 1.6 **Checkpoint:** `checkpoint-fase1b-doppio-punteggio-rsm`
- [x] 1.6-bis (aggiunta non pianificata, scoperta durante 1.7) Fix `RicercaRSTopK.php`:
      il buffer top-K in memoria (attivo solo in modalità "numero di località") scartava
      risultati durante il calcolo confrontando il vecchio sistema — corretto per usare
      V2 con fallback, altrimenti lo switch a V2 sarebbe stato incompleto in quella
      modalità. Commit `642f9fe`, **Checkpoint:** `checkpoint-fase1c-topk-v2`
- [x] 1.7 Switch confermato dall'utente su validazione visiva reale: filtro `stelline_min`,
      soglia streaming e `usort` finale ora su V2 primario (vecchio sistema come criterio
      secondario di stabilità). Commit `cc28f97`
- [x] 1.8 Verifica + restart + test funzionale completo, confermato dall'utente:
      "adesso è più coerente" — risultati con V2 alto ora emergono in cima
- [x] 1.9 **Checkpoint:** `checkpoint-fase1c-v2-primario-rsm`

## Fase 2 — RL di produzione (`ricerca_rl.php` / `api/ricerca_stream_rl_api.php`) — COMPLETATA

- [x] 2.1-2.9 Stesso schema della Fase 1, applicato a RL, tutti i passaggi confermati
      dall'utente su ricerche RL reali:
      - Calcolo V2 parallelo (additivo) — commit `c270ee5`,
        checkpoint `checkpoint-fase2a-v2-parallelo-rl`
      - Colonna V2 affiancata in `ricerca_rl.php` — commit `568fdd1`,
        checkpoint `checkpoint-fase2b-doppio-punteggio-rl`
      - Switch V2 primario (filtro, streaming, ordinamento) — commit `d22d5d7`,
        checkpoint `checkpoint-fase2c-v2-primario-rl`
      - Fix buffer top-K già coperto in Fase 1 (file condiviso `RicercaRSTopK.php`,
        nessun lavoro aggiuntivo necessario per RL)

**Rifinitura post-Fase 2 (richiesta esplicita dell'utente):** rimossa la colonna
"Stelle" (vecchio sistema) da entrambe le tabelle di `ricerca.php` e `ricerca_rl.php`;
colonna "V2" rinominata in "Ranking", ora unico punteggio visibile in interfaccia.
Il vecchio sistema resta solo internamente come criterio secondario di stabilita
nell'ordinamento. Commit `263f6e1` (RSM, checkpoint `checkpoint-fase1d-ranking-unico-rsm`)
e `9e71ffd` (RL, checkpoint `checkpoint-fase2d-ranking-unico-rl`).

## Fase 3 — Pagine ed endpoint secondari (perimetro verificato via grep)

- [x] 3.1 `rs.php` / `api/rs_api.php` (vista singola RSM salvata) — COMPLETATA.
      Backend: campo `valutazione_v2` aggiunto alla risposta JSON (commit `d920f81`,
      checkpoint `checkpoint-fase3a-v2-rs-singola`). Frontend: riquadro "Bonus e Veti"
      mostra ora il rendering V2 al posto delle vecchie stelle, con fallback
      (commit `9da810f`, checkpoint `checkpoint-fase3a-rs-frontend-v2`)
- [x] 3.2 `rl.php` / `api/rl_api.php` — COMPLETATA.
      Backend: campo `valutazione_v2` aggiunto (commit `cc3ab9e`,
      checkpoint `checkpoint-fase3b-v2-rl-singola`). Frontend: scoperto che la
      logica di rl.php vive in `js/rl.js` (file condiviso), non inline — patch
      mirata a una riga (commit `57e2e73`, checkpoint `checkpoint-fase3b-rl-frontend-v2`).
      Fix bug CSS `.valutazione` (`rgba(245, 242, 238, 060)` → `0.65`) completato
      (commit `c05b390`, checkpoint `checkpoint-fase3b-css-fix`)
- [x] 3.3 `api/cuspidi_search_api.php` — VERIFICATO, NON APPLICABILE. Analisi
      (grep esaustivo su RuleEngine/stelline) conferma che questo endpoint non
      usa affatto il sistema di valutazione stelline: è la modalità "Longitudine
      Cuspidi", cerca dove cade una cuspide/segno/grado target, non valuta
      condizioni. Nessuna modifica necessaria.
- [x] 3.4 `api/ricerca_griglia_api.php` — COMPLETATA. V2 sistema primario per
      filtro/streaming/ordinamento nelle modalità standard/astri (stesso pattern
      di ricerca_stream_api.php, vecchio sistema e vicinanza benefici come
      criteri secondari di stabilità); modalità cuspidi non toccata (non usa
      RuleEngine). Testato su RSM (funzionante). Nota: la "ricerca a griglia"
      non è ancora disponibile in interfaccia per la RL (limite preesistente,
      non introdotto da questa migrazione). Commit `1c1e967`,
      checkpoint `checkpoint-fase3c-v2-griglia`
- [x] 3.5 `api/stampa_pdf_api.php` — COMPLETATA, con scoperta importante.
      Esistono DUE percorsi di stampa distinti e indipendenti:
      1) i bottoni "🖨️ Stampa Rivoluzione Solare/Lunare" in rs.php/rl.php
         (prepareStampaRS/RL → stampaPagina, stampa lato BROWSER via
         window.print()) — quello realmente usato dal committente. Non mostra
         mai stelline (né vecchie né nuove), per scelta esplicita: deve
         mostrare solo grafici e dati soggetto. NON TOCCATO, su richiesta.
      2) `stampa.php` → `api/stampa_pdf_api.php` (Dompdf, PDF lato SERVER),
         raggiungibile da un link poco visibile in rs.php — mostra il
         riquadro valutazione con stelle/veti/bonus. V2 aggiunto qui come
         sistema di rendering delle stelle (fallback al vecchio se assente).
         Commit `f3cde78`, checkpoint `checkpoint-fase3d-v2-pdf`
- [x] 3.6 `api/sensibilita_api.php` — COMPLETATA. Nota: il pannello non mostra
      mai le stelline grezze, ma calcola una % di stabilità e un'etichetta
      (alta/media/critica) che DIPENDONO dal sistema stelline usato per il
      confronto — quindi è comunque un valore visibile all'utente. Migrato
      backend (commit `5bdcc21`, checkpoint `checkpoint-fase3e-v2-sensibilita`)
      e frontend in rs.php (icone 🟡, colore riga, stelle nel dettaglio —
      commit `7284cc7`, checkpoint `checkpoint-fase3e-sensibilita-frontend-v2`).
      Scoperto durante l'analisi: altre occorrenze di `.stelline` in rs.php
      (righe ~1169, 1542-43, 1610) appartengono alla feature separata "elenco
      RS salvate", FUORI SCOPE per questo punto — non toccate, da valutare
      eventualmente a parte.
- [x] 3.7 `api/rs_alert_api.php` — VERIFICATO, NON APPLICABILE. Grep esaustivo
      conferma nessun riferimento a RuleEngine/stelline: il sistema di alert
      si basa su altri criteri, non sul punteggio stelline. Nessuna modifica
      necessaria.

## FASE 3 COMPLETATA (2026-08-26)

Ogni punto: checkpoint separato (commit + tag) dopo verifica e test funzionale.

## Fase 4 — Rimozione sistema vecchio e chiusura

**Rete di sicurezza creata prima di iniziare:** tag annotato
`safety-net-pre-rimozione-vecchio-sistema` sull'ultimo commit di Fase 3 (stato
doppio-sistema, tutto validato). Rollback totale in caso di problemi:
`git reset --hard safety-net-pre-rimozione-vecchio-sistema`.

**Scope confermato dal committente:** rimuovere SOLO l'uso del vecchio punteggio
a stelle (`stelline`/`stelle_str`) per ordinamento/filtro/rendering. NON toccare
`RuleEngine.php` (congelato) né le sue altre funzioni (veti, `is_valida`,
`passed_amore/casa/denaro`, colonna VAL) — tutto questo resta invariato e
continua a funzionare esattamente come prima.

- [x] 4.1 Rimosso il vecchio sistema come fallback/tiebreaker in 6 file su 7
      (un commit/checkpoint per file, ognuno testato funzionalmente prima del
      commit; ogni rimozione lasciata commentata inline, non cancellata, per
      un rollback puntuale immediato senza dover toccare git):
      1. `RicercaRSTopK.php` — commit `4d27b49`, checkpoint `checkpoint-fase4-topk`
      2. `api/ricerca_stream_api.php` (ordinamento RSM) — commit `df6a22b`,
         checkpoint `checkpoint-fase4-rsm`
      3. `api/ricerca_stream_rl_api.php` (ordinamento RL) — commit `2c58dbb`,
         checkpoint `checkpoint-fase4-rl`
      4. `api/ricerca_griglia_api.php` (vicinanza_gradi mantenuto come criterio
         secondario, non è "vecchio sistema") — commit `1d5a925`,
         checkpoint `checkpoint-fase4-griglia`
      5. `rs.php` (riquadro valutazione principale) — commit `45e1e09`,
         checkpoint `checkpoint-fase4-rs-frontend`
      6. `js/rl.js` — commit `c949fb6`, checkpoint `checkpoint-fase4-rl-frontend`

      **7. `api/stampa_pdf_api.php` — VOLUTAMENTE LASCIATO IN SOSPESO**,
      rimandato a sessione futura (modifica vista/testata in sandbox ma MAI
      committata, per esplicita decisione del committente). Cronologia:
      - Scoperto che il link "📄 Stampa / PDF Report" in `rs.php` (che
        dovrebbe attivare questo endpoint via `stampa.php`) non compare MAI
        nell'interfaccia reale, nonostante il codice lo preveda — bug
        preesistente, non introdotto da questa migrazione (la funzione
        `_aggiornaLinkReportRS()` che dovrebbe renderlo visibile 600ms dopo
        ogni calcolo RS non produce l'effetto atteso, causa non
        diagnosticata)
      - Il committente ricorda di aver fatto rimuovere questa feature in
        passato — confermato: `rilocazione.php` ha LO STESSO identico
        pattern di link nascosto verso `stampa.php` (verosimilmente con lo
        stesso bug di visibilità), accanto al bottone "🖨️ Stampa
        Rilocazione" che invece funziona regolarmente (browser-print,
        meccanismo separato, non tocca `stampa.php`)
      - Verificato che eliminare `stampa.php` + `api/stampa_pdf_api.php` NON
        romperebbe nessuna delle stampe attualmente in uso (RS/RL/
        Rilocazione sono tutte via bottoni diretti browser-print,
        meccanismo indipendente) — unico impatto residuo: due script di
        test manuali in `www/tests/` (`test_annual_report_browser_print.php`,
        `test_api_unauthenticated_contract.php`) referenziano questi file
        per path/URL; non eseguiti automaticamente, quindi zero rischio
        pratico se lasciati così, ma andrebbero aggiornati/rimossi insieme
        in un'eventuale pulizia futura
      - **Decisione presa:** lasciare tutto com'è per ora (nessun file
        toccato, nessuna eliminazione), da riprendere in una sessione
        dedicata

      **Caratteristiche di `stampa.php`/`stampa_pdf_api.php` per riferimento
      futuro (nel caso si decida di recuperarlo invece di eliminarlo):**
      genera un report PDF combinato (Dompdf, non window.print) che può
      includere in un unico file Tema Natale + RS + RL + Rilocazione insieme
      (moduli selezionabili via parametro `moduli`), con per ognuno ruota,
      tabella pianeti, tabella case Placido, E il riquadro valutazione
      completo (stelle/veti/bonus/penalità/VAL) — a differenza dei bottoni
      diretti che mostrano solo ruote e dati soggetto. Se il modulo RS è
      incluso, aggiunge anche la Relazione Annuale completa nello stesso PDF.

      **TODO futuro (da decidere in una sessione dedicata):** eliminare
      definitivamente `stampa.php`, `api/stampa_pdf_api.php`, i due link
      nascosti in `rs.php`/`rilocazione.php`, e aggiornare/rimuovere i due
      script di test collegati — oppure, in alternativa, diagnosticare e
      correggere il bug di visibilità per far tornare la feature utilizzabile.

- [ ] 4.2 Aggiornare `HANDOVER_OPERATIVO_astrolab.md`, `ROADMAP.md`, `START_HERE.md`
- [ ] 4.3 **Checkpoint finale:** commit + tag `checkpoint-fase4-sostituzione-completata`
- [ ] 4.4 Merge di `feature/sostituzione-stelline-v2` verso `new_dashboard`, solo su tua
      conferma esplicita

---

## Stato di avanzamento

| Fase | Descrizione                          | Stato       | Data |
|------|---------------------------------------|-------------|------|
| 0    | Fix bug Calculator                    | COMPLETATA  | 2026-08-26 |
| 1    | RSM produzione                        | COMPLETATA  | 2026-08-26 |
| 2    | RL produzione                         | COMPLETATA  | 2026-08-26 |
| 3    | Pagine/endpoint secondari              | COMPLETATA  | 2026-08-26 |
| 4    | Rimozione vecchio sistema + chiusura   | Da iniziare |      |
