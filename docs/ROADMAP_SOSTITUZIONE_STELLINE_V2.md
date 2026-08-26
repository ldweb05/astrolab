# ROADMAP — Sostituzione Sistema Stelline con V2 (definitiva)

**Data creazione:** 2026-08-26
**Branch di lavoro:** `feature/sostituzione-stelline-v2` (creato da `new_dashboard`)
**Stato:** IN CORSO — Fase 0
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

- [ ] 0.1 Fix disallineamento ASC in X (`$totaleVerdi += 5` → `+= 4`, coerente con la
      tabella pesi definitiva 2026-08-26)
- [ ] 0.2 Fix `trovaCasaNatale()`: chiavi `inizio`/`fine` inesistenti → `longitudine`
      (riuso della stessa logica di `SweCalc::trovaCasa()`)
- [ ] 0.3 Verifica sintassi + restart container + retest nel laboratorio admin
      (`test_stelline_v2.php`) su un caso noto, per confermare che il malus ASC-VIII natale
      e il conteggio ASC-in-X ora si comportino come atteso
- [ ] 0.4 **Checkpoint:** commit + tag `checkpoint-fase0-bugfix-calculator`

## Fase 1 — RSM di produzione (`ricerca.php` / `api/ricerca_stream_api.php`)

- [ ] 1.1 Analisi mirata (solo lettura) di `ricerca_stream_api.php`: dove viene costruito
      il campo `stelline` nel record risultato e dove avviene l'ordinamento/il filtro
      `stelline_min`
- [ ] 1.2 Aggiungere (non sostituire) il calcolo V2 in parallelo — stesso pattern già
      usato in `ricerca_stream_v2_api.php` — arricchendo il risultato con i campi
      `v2_stelle_totali` ecc. Ordinamento e filtro `stelline_min` restano sul sistema
      vecchio in questo sotto-step: è puramente additivo, nessuna differenza visibile
- [ ] 1.3 Verifica + restart + test funzionale (nessun cambiamento visibile atteso)
- [ ] 1.4 **Checkpoint:** commit + tag `checkpoint-fase1a-v2-parallelo-rsm`
- [ ] 1.5 Modifica frontend di `ricerca.php` per mostrare **entrambi** i punteggi
      affiancati (vecchio + V2), per validazione visiva diretta su casi reali
- [ ] 1.6 **Checkpoint:** commit + tag `checkpoint-fase1b-doppio-punteggio-rsm`
- [ ] 1.7 **Solo dopo tua conferma esplicita** di validazione sui casi reali: switch di
      ordinamento e filtro `stelline_min` al sistema V2 come primario; il vecchio resta
      come campo informativo secondario (non rimosso)
- [ ] 1.8 Verifica + restart + test funzionale completo (casi noti, es. Sinner/Decima)
- [ ] 1.9 **Checkpoint:** commit + tag `checkpoint-fase1c-v2-primario-rsm`

## Fase 2 — RL di produzione (`ricerca_rl.php` / `api/ricerca_stream_rl_api.php`)

Stesso schema della Fase 1 (2.1 → 2.9), applicato a RL. Checkpoint separati.

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
| 0    | Fix bug Calculator                    | IN CORSO    |      |
| 1    | RSM produzione                        | Da iniziare |      |
| 2    | RL produzione                         | Da iniziare |      |
| 3    | Pagine/endpoint secondari              | Da iniziare |      |
| 4    | Rimozione vecchio sistema + chiusura   | Da iniziare |      |
