# ROADMAP — Sostituzione Sistema Stelline con V2 (definitiva)

**Data creazione:** 2026-08-26
**Branch di lavoro:** `feature/sostituzione-stelline-v2` (creato da `new_dashboard`)
**Stato:** IN CORSO — Fase 2 (RL) completata, avvio Fase 3 (pagine/endpoint secondari)
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

## Fase 3 — Pagine ed endpoint secondari (perimetro verificato via grep)

- [ ] 3.1 `rs.php` / `api/rs_api.php` (vista singola RSM salvata)
- [ ] 3.2 `rl.php` / `api/rl_api.php` — **incluso** il fix del bug CSS `.valutazione`
      (`rgba(245, 242, 238, 060)` → valore alpha valido) approfittando del passaggio su
      questo file
- [ ] 3.3 `api/cuspidi_search_api.php`
- [ ] 3.4 `api/ricerca_griglia_api.php`
- [ ] 3.5 `api/stampa_pdf_api.php` — verificare che le stelline stampate nel PDF siano
      coerenti con quelle mostrate a video
- [ ] 3.6 `api/sensibilita_api.php`
- [ ] 3.7 `api/rs_alert_api.php`

Ogni punto: checkpoint separato (commit + tag) dopo verifica e test funzionale.

## Fase 4 — Rimozione sistema vecchio e chiusura

- [ ] 4.1 **Solo su conferma esplicita:** rimuovere il doppio calcolo/fallback, V2 unico
      sistema attivo
- [ ] 4.2 Aggiornare `HANDOVER_OPERATIVO_astrolab.md`, `ROADMAP.md`, `START_HERE.md` (solo
      se il comportamento visibile all'utente finale cambia)
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
| 3    | Pagine/endpoint secondari              | Da iniziare |      |
| 4    | Rimozione vecchio sistema + chiusura   | Da iniziare |      |
