# ROADMAP — Correzione bug giorno di nascita / conversione GMT

**Branch dedicato:** `fix/giorno-nascita-gmt`
**Origine:** scoperto durante la validazione del progetto "allineamento 34 regole"
(sessione 2026-08-18, confronto con myastral.org su caso reale Sinner), già
documentato come "bug noto sull'oscillazione dell'ora GMT" in
`docs/ROADMAP_34_REGOLE.md` e lasciato volutamente fuori scope in quel lavoro.

## Il problema

Quando l'ora di nascita locale è precedente all'offset del fuso orario (es.
nato alle 00:52 in Italia, offset +2), il vero istante UTC di nascita cade
nel **giorno precedente** (22:52 del giorno prima). Il bug: il calcolo usa
sempre `data_nascita` (la data locale) così com'è, senza mai retrocederla,
producendo un tema natale — e quindi ogni RSM/RL derivata — calcolato su un
giorno sbagliato (fino a un giorno intero di scarto, confermato con 0° di
errore su un caso reale).

**Causa radice:** `www/js/app.js`, funzione `aggiornaOraGmt()` — calcola
correttamente l'ora GMT ma fa solo il wrap dell'orario (`% 1440`) senza mai
spostare il giorno. Il risultato (solo ora, nessuna indicazione del giorno)
viene salvato in `ora_nascita_gmt` e riusato lato server abbinato sempre e
comunque a `data_nascita` invariata.

**File lato server affetti** (tutti abbinano `data_nascita` a
`ora_nascita_gmt` senza correggere il giorno):
`www/ricerca.php`, `www/ricerca_rl.php`, `www/rilocazione.php`, `www/rl.php`,
`www/rs.php`, `www/stampa.php`, `www/tema.php`, `www/transiti.php`,
`www/api/rl_api.php`, `www/api/stampa_pdf_api.php`,
`www/includes/RicercaPageData.php`.

## Fase 0 — Setup

- Verificare con un nuovo `grep -rn "ora_nascita_gmt"` che l'elenco sopra sia
  ancora completo e aggiornato (il codice può essere cambiato da allora)
- Verificare lo schema esatto della tabella `soggetti` (colonne `ora_nascita`
  e `offset_gmt` — servono entrambe per il fix, verificare che siano sempre
  popolate per i soggetti esistenti, non solo per quelli nuovi)

## Fase 1 — Funzione helper centralizzata

- Creare una funzione `calcolaDataOraGmtCorretta()` (nuovo file isolato, es.
  `www/includes/NascitaGmtHelper.php`) che combina `data_nascita` +
  `ora_nascita` (locali, già in tabella) + `offset_gmt` (già in tabella)
  tramite `DateTime` nativo di PHP, lasciando che gestisca da solo il cambio
  di giorno (evitando di reimplementare a mano la logica di wrap che ha
  causato il bug originale). Restituisce giorno/mese/anno/ora_gmt corretti.
- Non serve nessuna modifica allo schema DB: `ora_nascita` e `offset_gmt`
  sono già colonne esistenti e popolate.
- Test funzionale isolato (script PHP temporaneo, poi rimosso) con almeno
  questi casi:
  - Sinner: 2001-08-16, 00:52 locale, offset +2.00 → deve dare
    15/08/2001 22:52 GMT (verificato manualmente, Sole natale 143.157°)
  - offset negativo (es. USA, offset -5) con ora locale che scavalca in
    avanti
  - mezzanotte esatta (00:00 locale)
  - offset 0 (nessun cambiamento)
  - ora locale ben dentro la giornata (nessun cambio di giorno, caso
    "normale" già funzionante — deve continuare a dare lo stesso risultato
    di prima, per non introdurre regressioni sui casi già corretti)

## Fase 2 — Migrazione file, uno alla volta

Ordine dal più isolato al più centrale/rischioso:

1. `www/includes/RicercaPageData.php` — verificare/integrare la SELECT con
   `ora_nascita`, `offset_gmt` (oggi seleziona solo `ora_nascita_gmt`)
2. `www/api/rl_api.php`
3. `www/api/stampa_pdf_api.php`
4. `www/tema.php`
5. `www/rs.php`
6. `www/rl.php`
7. `www/rilocazione.php`
8. `www/transiti.php`
9. `www/stampa.php`
10. `www/ricerca.php` e `www/ricerca_rl.php` — i più delicati, toccano
    direttamente la ricerca RSM/RL; ultimi, con test più approfonditi
    (confronto risultati prima/dopo su un soggetto con nascita "a rischio")

Per ognuno: patch minima, `php -l`, verifica che la query SQL selezioni le
colonne necessarie (`ora_nascita`, `offset_gmt`, non solo `ora_nascita_gmt`),
test funzionale con un caso reale noto, confronto prima/dopo, conferma
esplicita dell'utente, commit singolo per file.

## Fase 3 — Coerenza lato client

- Verificare/correggere `www/js/app.js` (`aggiornaOraGmt()`) per mostrare in
  UI anche l'eventuale cambio di giorno GMT (oggi mostra solo l'ora). Con la
  Fase 2 completata i calcoli reali sono già corretti lato server — questo è
  un fix di visualizzazione/coerenza, non di calcolo.

## Fase 4 — Verifica finale e chiusura

- Suite di regressione `tests/run.php`
- Individuare (query DB) e verificare l'impatto reale sui soggetti esistenti
  con nascita "a rischio" (ora locale < offset del fuso)
- Aggiornare `docs/HANDOVER_OPERATIVO_astrolab.md` con il riepilogo
- Aggiornare `docs/ROADMAP_34_REGOLE.md`: il bug "oscillazione GMT" lì
  documentato come fuori scope va segnato come risolto, con riferimento a
  questo lavoro