# Roadmap — Allineamento sistematico alle 34 regole ufficiali

## Principio guida

`docs/status/34_regole_rsm.md` è la fonte primaria e vincolante ("la bibbia")
per tutta l'applicazione ASTROLAB. Ogni regola aggiuntiva o personalizzata
(veti extra, filtri, punteggi come `RuleEngineExtended.php`) è un livello
costruito SOPRA le 34 regole, mai un sostituto: in caso di conflitto, le 34
regole prevalgono sempre. Questo principio è già stabilito nelle decisioni
UX-0001 e UX-0002 (`docs/ux-myastral/DECISION_LOG_ux.md`).

## Cosa è già stato fatto (branch `feature/allineamento-myastral`)

- 34 regole tracciate come fonte ufficiale (`docs/status/34_regole_rsm.md`)
- Trovate e corrette/documentate 3 etichette storicamente sbagliate nel
  codice: "reg.31" (in realtà il veto proprietario sulla latitudine estrema,
  non una delle 34 regole), "reg.33" (in realtà "Marte o Saturno entro 2°
  dagli angoli", non la vera Regola 33), e un veto ASC-RS-vs-case-natali
  etichettato "Regola 1" (in realtà Regola 4 + Regola 6a)
- Regola 33 (Saturno prevale su Giove/Venere/Sole, caso "stessa casa")
  implementata come esclusione reale, su RSM e RL, dietro
  `MYASTRAL_ALIGNMENT_MODE`
- Criterio stellium allineato alle Regole 4/16/26/31: `FiltroEsclusione.php`
  ristretto da 12 case a sole I/VI/VIII/XII; `RuleEngine.php` corretto
  rimuovendo l'eccezione benefici (Regola 31 mostra un esempio esplicito con
  Giove che non salva lo stellium) — decisione UX-0002

## Bug noto, fuori scope per questo lavoro

L'ora GMT calcolata per un soggetto sembra "oscillare" tra ricerche identiche
(osservato: differenza di un'ora esatta). Causa probabile: la chiamata al
servizio esterno timezonedb.com in `www/js/app.js`
(`ottieniOffsetTimeZone()`). Da investigare in una sessione dedicata separata
— non toccare in questo lavoro, ma tenerne conto se i test producono numeri
incoerenti tra loro: verificare prima se l'ora GMT usata è la stessa.

## Fase 1 — Le 4 regole a scarto automatico (priorità massima) — COMPLETATA (2026-08-18)

Queste 4 regole, se violate, impongono lo scarto automatico della RSM/RL —
non ammettono eccezioni né punteggi parziali. Tutte e 4 verificate e/o
implementate:

1. **Regola 4** — Ascendente, stellium o Sole in I, VI o XII casa →
   Ascendente-vs-case-natali corretto (etichetta era "Regola 1", ora "Regola
   4 + Regola 6a"); stellium già corretto nelle case proprie RS/RL; Sole
   NON era un veto assoluto (peso 0, solo nota "AVV") — corretto con
   decisione UX-0004, ora veto assoluto con pre-ingresso 3°
2. **Regola 5** — Marte in I, VI o XII casa della RS/RL → verificata,
   già corretta (veto assoluto con pre-ingresso 3°, copertura RSM e RL
   tramite motore condiviso)
3. **Regola 31** — la sfumatura "stellium diviso tra case" (es. Giove in XII
   + Venere/Mercurio in I conta come stellium pieno in XII) → implementata
   con decisione UX-0005, limitata alla coppia XII/I esplicitamente citata
   dal testo (non generalizzata ad altre case adiacenti)
4. **Regola 34** — Marte e Saturno nella stessa casa della RS/RL, eccetto
   III e IX → implementata da zero con decisione UX-0003, riuso di
   `CASE_PARCHEGGIO` esistente

## Fase 2 — Altre regole-veto identificate nel testo completo — COMPLETATA (2026-08-18)

- Regola 26 — stellium in VIII casa → verificata, già corretta senza
  modifiche (il codice vieta solo lo stellium in VIII, non i singoli
  pianeti, esattamente come il testo)
- Regola 33 — completata in entrambi i casi: "stessa casa" spostato da
  `RuleEngineExtended.php` (opzionale, ristretto alla casa tematica) a
  veto assoluto incondizionato in `RuleEngine.php` (decisione UX-0006);
  "case adiacenti, stesso orbo" implementato limitatamente alla coppia
  IX/X esplicitamente citata dal testo, tolleranza 3° (orbo Regola 23)

## Fase 3 — Confronto sistematico delle 120 regole esistenti contro le 34 — COMPLETATA (2026-08-18)

Per ogni veto/regola in `RuleEngine.php`, `FiltroEsclusione.php`,
`RicercaRSFilters.php`: è prevista dalle 34 regole ufficiali? È un'aggiunta
proprietaria del progetto (come il veto latitudine)? È mal etichettata
(come i 3 casi già trovati)? Ogni discrepanza va registrata nel decision log
prima di essere corretta, con lo stesso formato già usato per UX-0001/0002.

Risultato: `RuleEngine.php` già interamente auditato nelle Fasi 1/2 (tutte le
discrepanze trovate risolte). `FiltroEsclusione.php` verificato, nessuna
discrepanza (già correttamente proprietario e documentato). `RicercaRSFilters.php`
verificato: tutta logica proprietaria per le condizioni di ricerca, nessuna
regola ufficiale mancante da aggiungere; unica correzione, 7 etichette interne
"REGOLA N" (numerazione locale) rinominate in "PASSO N" per non essere confuse
con le regole ufficiali — solo commenti, nessuna modifica di logica.

## Fase 4 — Regole di metodo/peso — COMPLETATA (2026-08-18)

Regole 6, 22, 30 (e altre simili) non sono veti ma indicano cosa guardare e
quanto pesarlo (es. l'Ascendente conta più di Giove al MC; gli aspetti
angolari contano pochissimo). Valutare se e come integrarla nella logica di
`RuleEngineExtended.php` (il punteggio parziale).

Risultato: già rispettate senza modifiche. Il motore RSM/RL non calcola mai
aspetti né retrogradazione (Regole 30/6), e l'ordine di priorità di
`calcolaVeti()` già rispecchia esattamente quello della Regola 6 (ASC →
stellium → Sole → malefici). La Regola 22 non si applica (il motore non
incrocia transiti). Il sottosistema `www/includes/forecast/` (feature
"Relazione Annuale", ~5000 righe) usa aspetti/retrogradazione/dignità ma è
stato dichiarato fuori scope dal committente: interpreta narrativamente un
risultato già calcolato, non incide su calcoli o regole di scarto/punteggio.

## Fase 5 — Regole sui transiti

Regole 11, 12, 14, 15, 23, 24, 27, 28 riguardano i transiti nel tempo, non
la ricerca della miglior località per RSM/RL. Verificare se sono rilevanti
per `ricerca.php`/`ricerca_rl.php` o solo per `transiti.php` (area separata
del progetto) prima di decidere se e come intervenire.

## Metodo di lavoro

Stesso protocollo già in uso: un file alla volta, patch minime verificate,
`php -l` + `git status` prima di ogni commit, test funzionale reale prima
del commit per modifiche comportamentali. Per ogni modifica a
`RuleEngine.php` (FREEZE) o `FiltroEsclusione.php` che cambi il
comportamento di default: registrare la decisione in
`docs/ux-myastral/DECISION_LOG_ux.md` PRIMA di scrivere il codice, come già
fatto per UX-0001 e UX-0002. Confermare sempre con il committente prima di
ogni commit.
