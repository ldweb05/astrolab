# ROADMAP — Ricerca RSM "Astri in Cuspide"

**Branch dedicato:** `feature/2-astri-in-cuspide`
**Creato da:** `feature/allineamento-myastral` (per ereditare il lavoro già fatto sulle 34 regole
e sul pannello "Astri nelle Case").

**Origine:** idea discussa con l'utente (Francesco) durante una sessione di confronto UX sulla
ricerca RSM.

---

## Obiettivo

Estendere il pannello "Astri nelle Case" (già esistente in `ricerca.php`/`ricerca_rl.php`) con una
nuova modalità per-regola: invece del solo match "pianeta X in casa Y", poter chiedere "pianeta X
in cuspide della casa Y (entro l'orbo ufficiale di Regola 32)". Combinabile su più pianeti nella
stessa ricerca (es. Venere su cuspide II + Giove su cuspide X), riusando il meccanismo multi-regola
già esistente.

## Vincoli non negoziabili (confermati dall'utente)

1. **Feature Supporter-gated**: visibile a tutti gli utenti, cliccabile solo dai piani Supporter;
   sul piano free mostra `SUPPORTER_MESSAGE` al click (pattern identico a `dynamic_orb` /
   `grid_search` / `locality_search`, già in `Auth::hasFeature()`).
2. **Orbo fisso, non configurabile dall'utente**: si usa l'orbo ufficiale della Regola 32
   (2°30′), nessun valore nuovo o arbitrario.
3. **Le regole 4, 5, 31, 34 restano veti assoluti incondizionati**, applicati da `RuleEngine.php`
   esattamente come oggi, a prescindere dalla modalità di ricerca che ha prodotto il candidato.
   Nessuna logica di scarto duplicata o parallela: la nuova modalità si limita ad aggiungere un
   criterio di *selezione* dei candidati, il `RuleEngine` fa comunque il suo passaggio di veto
   standard su ognuno di essi, invariato.
4. Il div `time-controls` in `rs.php` resta **congelato** per questo lavoro (vedi nota permanente
   in `docs/HANDOVER_OPERATIVO_astrolab.md` sul branch `chore/porta-feature-da-allineamento-myastral`)
   — nessuna modifica prevista in questo piano lo tocca.

## Nota sulla divergenza tra branch

`feature/allineamento-myastral` (e quindi `feature/2-astri-in-cuspide`) e `fase9-comparator-quota`
sono divergenti dal commit comune `a52c2e9`, mai riallineati tra loro. Questo lavoro procede sulla
linea `feature/allineamento-myastral`; la riconciliazione tra le due linee resta un'attività
separata, da affrontare con verifica funzionale dedicata quando verrà pianificata (vedi
[[astrolab-rs-time-controls-regressione]] per il precedente reale già occorso due volte su questo
stesso div).

---

## Fase 0 — Setup e verifica stato reale

- [x] Rilette `docs/START_HERE.md`, `docs/ROADMAP.md`, `docs/HANDOVER_OPERATIVO_astrolab.md` sul
  branch `feature/2-astri-in-cuspide`.
- [x] Branch `feature/2-astri-in-cuspide` creato da `feature/allineamento-myastral` e pushato.
- [ ] Creare voce UX-0008 in `docs/ux-myastral/DECISION_LOG_ux.md` prima di toccare
  `RuleEngine.php` o `RicercaRSFilters.php` (obbligatorio per la regola di FREEZE già in vigore).
- [ ] Questo documento creato e collegato da `docs/ROADMAP.md` e `docs/HANDOVER_OPERATIVO_astrolab.md`.

## Fase 1 — Backend: nuova modalità nella regola "Astri nelle Case" (COMPLETATA - commit 627379e)

File coinvolti: `www/includes/RicercaRSFilters.php`, `www/api/ricerca_stream_api.php`,
`www/api/ricerca_stream_rl_api.php`.

- Estendere il formato di ogni riga `astri_in_casa` da `{pianeta, casa, vuole}` a
  `{pianeta, casa, vuole, modalita}` con `modalita: 'in_casa'` (default, comportamento attuale
  invariato) o `'cuspide'`.
- Nuova funzione (o estensione di `verificaAstriInCasaDirectly()`) che per `modalita: 'cuspide'`
  usa `AstroUtils::diffAngolo()` contro la longitudine della cuspide della casa richiesta, con orbo
  fisso 2°30′ (Regola 32), riusando lo stesso stile già presente per pre-ingresso/uscita in
  `verificaCondizioneDecima`/`verificaCondizioneDenaroLow`.
- Nessuna modifica al passaggio del `RuleEngine` sui veti 4/5/31/34: resta a valle, invariato.

## Fase 2 — Frontend: UI del pannello "Astri nelle Case" (COMPLETATA - verificata da admin)

File coinvolti: `www/ricerca.php`, `www/ricerca_rl.php`.

- Per ogni riga regola, aggiungere selettore "in casa" / "in cuspide (orbo Regola 32)".
- Opzione "in cuspide" visibile ma disabilitata sul piano free, con tooltip `SUPPORTER_MESSAGE` al
  click (stesso pattern di `dynamic_orb`).
- Flag lato client `astri_in_cuspide: <?= json_encode($auth->hasFeature('astri_in_cuspide')) ?>`
  accanto agli altri flag già presenti.
- **Attenzione divergenza branch**: verificare esplicitamente, prima di modificare, se
  `chore/porta-feature-da-allineamento-myastral` ha toccato le stesse porzioni di `ricerca.php` /
  `ricerca_rl.php` (quel branch ha già un fix più recente sull'header sticky di questi stessi
  file).

## Fase 3 — Gating Supporter lato server (COMPLETATA)

File coinvolti: `www/includes/Auth.php`, endpoint API.

- Nuova feature key (es. `astri_in_cuspide`) in `hasFeature()`.
- Validazione server-side: se `modalita: 'cuspide'` arriva senza `hasFeature('astri_in_cuspide')`,
  la riga va ignorata/rifiutata (mai fidarsi solo del disabled lato client).

## Fase 4 — Test e verifica

- `php -l` sui file toccati, `node --check` su eventuale JS.
- Test funzionale reale: almeno un caso con Venere su cuspide II + Giove su cuspide X, verificando
  che eventuali candidati con Marte+Saturno stessa casa (Regola 34) o Ascendente/Sole/Marte in
  I/VI/XII (Regole 4/5) vengano comunque scartati.
- Verifica piano free: opzione visibile ma bloccata, tooltip corretto.
- Nessuna regressione sulla modalità "in casa" esistente.

## Fase 5 — Chiusura

- Aggiornare `docs/HANDOVER_OPERATIVO_astrolab.md` e questa roadmap a "COMPLETATA".
- Commit solo su conferma esplicita, file specifici elencati (mai `git add -A`).
