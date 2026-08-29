# ROADMAP — Correzione condizioni "Decima" e "Lavoro" nella ricerca RSM/RL

**Stato: COMPLETATA E SUPERATA (2026-08-29).** Decima è stata implementata
e poi evoluta in UX-0015/UX-0018 (gerarchia a 8→6 livelli, Regola 14,
principio UX-0017). Lavoro è stata implementata ed estesa a tutte le
modalità in UX-0019, con un'architettura più completa di quanto previsto
qui sotto (gerarchia a livelli invece di un semplice filtro, vincolo
Regola 33 specifico per-casa). Vedi `docs/ux-myastral/DECISION_LOG_ux.md`
(voci UX-0015/16/17/18/19) e `docs/HANDOVER_OPERATIVO_astrolab.md` (voci
2026-08-28 e 2026-08-29) per lo stato reale e definitivo. Questo documento
resta solo come riferimento storico del problema originale.

**Origine:** bug segnalato dall'utente confrontando un risultato di ricerca
condizione "Decima" con myastral.org — nessuna relazione reale con la X casa
nei risultati di astrolab.

## Il problema

Nel flusso di ricerca aeroporti (`www/api/ricerca_stream_api.php`), solo 5
delle 7 condizioni hanno un controllo positivo dedicato (un benefico deve
essere presente/vicino alla casa tematica):

```
'Amore'      -> verificaCondizioneAmore()      COLLEGATA
'Casa'       -> verificaCondizioneCasa()       COLLEGATA
'Salute'     -> verificaCondizioneSalute()     COLLEGATA
'Denaro'     -> verificaCondizioneDenaro()     COLLEGATA
'Denaro Low' -> verificaCondizioneDenaroLow()  COLLEGATA
'Decima'     -> verificaCondizioneDecima()     ESISTE MA NON E' MAI CHIAMATA
'Lavoro'     -> (nessuna funzione)             ASSENTE DEL TUTTO
```

Per "Decima" e "Lavoro" oggi scatta solo il filtro negativo della Rule Map
(`getRuleMapEsclusione()`: nessun malefico nella casa tematica), mai quello
positivo (un benefico presente o in pre-ingresso nella casa tematica) - per
questo compaiono risultati senza alcun legame reale con la X casa.

**Case tematiche gia' definite in `getRuleMapEsclusione()`** (da riusare,
non da reinventare):
- Decima -> X casa
- Lavoro -> VI e X casa (coerente con Regola 33: "discorso
  lavoro/emancipazione/successo/prestigio" legato al Medio Cielo)

## Fase 1 - Completare e collegare `verificaCondizioneDecima()`

- La funzione esiste (`www/includes/RicercaRSFilters.php`) ma controlla solo
  `casa === 10` esatta, senza pre-ingresso di 3 gradi ne' sicurezza-uscita
  2 gradi, a differenza delle funzioni sorelle (`verificaCondizioneCasa()`
  ne e' il modello piu' vicino: una sola casa target, IV).
- Aggiungere pre-ingresso 3 gradi e sicurezza-uscita 2 gradi, sullo stesso
  modello di `verificaCondizioneCasa()`.
- Collegarla in `ricerca_stream_api.php` con un blocco
  `if ($condizione === 'Decima') { ... }`, sullo stesso pattern gia' usato
  per Amore/Casa/Salute/Denaro/Denaro Low.
- Test funzionale: verificare su un caso reale (Sinner, RSM 2025, Decima)
  che i risultati abbiano tutti un benefico in o vicino alla X casa.

## Fase 2 - Creare e collegare `verificaCondizioneLavoro()`

- Non esiste, va scritta da zero sul modello di `verificaCondizioneAmore()`
  (l'unica funzione esistente con DUE case target contemporanee, V e VII)
  - qui le case target sono VI e X, gia' definite nella Rule Map.
- Stessa struttura: benefici SO/GI/VE, malevoli MA/SA/UR/NE/PLU, pre-ingresso
  3 gradi, sicurezza-uscita 2 gradi per ciascuna delle due case.
- Collegarla in `ricerca_stream_api.php` con un blocco
  `if ($condizione === 'Lavoro') { ... }`.
- Test funzionale sullo stesso caso reale, condizione Lavoro.

## Fase 3 - Verifica finale

- Suite di regressione `tests/run.php`
- Confronto dei conteggi risultati prima/dopo per entrambe le condizioni
  (atteso: drastica riduzione, coerente con l'introduzione di un vincolo
  positivo mai applicato finora)
- Documentazione: nuova voce in `docs/HANDOVER_OPERATIVO_astrolab.md`
