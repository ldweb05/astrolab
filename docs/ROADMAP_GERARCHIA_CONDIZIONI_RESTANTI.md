# ROADMAP — Estensione architettura a gerarchia alle condizioni restanti

**Stato:** APERTA — 5 delle 7 condizioni completate (Decima, Amore, Lavoro,
Salute, Casa). Restano Denaro e Denaro Low.
**Origine:** al completamento di UX-0019 (condizione Lavoro, 2026-08-29), un
riepilogo di copertura ha mostrato che 3 delle 7 condizioni disponibili
(Decima, Amore, Lavoro) erano sul modello a gerarchia a livelli, mentre
le altre 4 restavano sul vecchio modello a filtro-che-esclude, mai
armonizzato. Con UX-0020 (Salute, 2026-08-30) sono diventate 4 su 7, con
UX-0021/UX-0023 (Casa, 2026-08-31) sono 5 su 7.

## Le 5 condizioni già completate (riferimento architetturale)

- **Decima** — UX-0015/UX-0018 (`docs/ux-myastral/DECISION_LOG_ux.md`)
- **Amore** — UX-0016/UX-0017
- **Lavoro** — UX-0019
- **Salute** — UX-0020 (con un'eccezione architetturale, vedi sotto)
- **Casa** — UX-0021 (gerarchia base), UX-0023 (correzione ordinamento veti/stellium misto)

Decima, Amore, Lavoro e Casa condividono lo stesso schema: rilevatore
geometrico puro in `RicercaRSFilters.php` (`verificaCondizione*()`, ritorna
solo `pianeti_in_casa`), gerarchia a livelli in `RuleEngineExtended.php`
(`calcolaLivello*()`), colonna VAL dedicata (`generaVal*()`), esclusione
totale se nessun segnale positivo (principio UX-0017), attive su tutte e
tre le modalità di ricerca (standard, griglia/area geografica/fascia
oraria, RL) fin dall'inizio — Amore fa eccezione, non supportata in
modalità griglia.

**Salute (UX-0020) è un'eccezione voluta**: `verificaCondizioneSalute()`
NON è stata rifattorizzata a rilevatore geometrico puro — resta invariata,
con i suoi 5 passaggi proprietari di veto (tolleranza pre-ingresso 4°,
scudo benefico in I, esclusione Sole in XII, rafforzamento ASC natale,
protezione universale Giove/Venere), decisione esplicita del committente.
`calcolaLivelloSalute()`/`pianetiPerCasaSalute()`/`generaValSalute()` sono
nuovi metodi indipendenti in `RuleEngineExtended.php` che ordinano solo le
RSM già validate a monte da `verificaCondizioneSalute()` — non ne
condividono il rilevatore. Gerarchia a 8 livelli + 1 fallback difensivo:
priorità di casa VI (principale) poi I/XII (pari tra loro), priorità di
pianeta Giove poi Venere (Sole escluso), bonus orbo 1,5° su entrambi.

## UX-0023/UX-0024 — Requisito trasversale ora obbligatorio per OGNI condizione a gerarchia

Dopo un bug segnalato dal committente su Casa (risultati con un benefico
valido ma un veto GENERALE delle 34 regole salivano comunque sopra
risultati completamente puliti), si è scoperto che **nessuna** delle
funzioni `calcolaLivello*()` controllava i veti prodotti da
`RuleEngine::valuta()` né l'alert `alert_stellium_misto` del sistema
Stelline V2 prima di assegnare il livello di ordinamento. Corretto per
Casa (UX-0023) e poi esteso a Decima/Amore/Lavoro/Salute (UX-0024).

**Il pattern è ora un requisito standard per qualunque nuova condizione a
gerarchia**, Denaro e Denaro Low comprese quando verranno implementate —
va incluso fin dalla progettazione iniziale, non aggiunto come correzione
successiva:
1. Un veto UFFICIALE delle 34 regole (Regola 4/5/31/34 — identificabile
   perché il testo del veto in `RuleEngine::calcolaVeti()` cita
   esplicitamente la regola o descrive ASC/Sole/Marte/stellium in
   I/VI/XII, Marte+Saturno stessa casa) non già coperto dalla logica
   propria della condizione deve escludere comunque la RSM/RL.
2. Il veto proprietario "astrolab-angoli" (esplicitamente commentato nel
   codice come non ufficiale) e l'alert `alert_stellium_misto` di
   `StellineV2Calculator` NON devono escludere, ma retrocedere il livello
   sotto qualunque risultato del tutto pulito — usare la costante
   condivisa `RuleEngineExtended::OFFSET_FASCIA_VETO_MINORE` (valore 10).
3. Verificare qualunque nuova gerarchia sull'INTERO dataset reale (non un
   campione ridotto) prima di metterla in produzione — un tentativo su
   campione ridotto per Casa non aveva rivelato un problema che si è poi
   manifestato solo sui dati completi.

Dettagli completi in `docs/ux-myastral/DECISION_LOG_ux.md` voci UX-0023 e
UX-0024, `docs/HANDOVER_OPERATIVO_astrolab.md` voce 2026-08-31.

## Le 2 condizioni restanti DA FARE

Entrambe sono oggi un filtro binario (`['valida'=>bool, 'motivo'=>...]`)
che esclude secco se manca il benefico richiesto o se è presente un
malefico — nessuna gerarchia a livelli, nessuna colonna VAL dedicata,
nessun bonus orbo. Già coperte (come filtro semplice, non come gerarchia)
su tutte e tre le modalità di ricerca.

### 1. Denaro (II, VIII casa — entrate/investimenti)
- Filtro attuale: `verificaCondizioneDenaro()`, include già un "alert
  Giove bistabile" proprietario (segnala se Giove può generare alternanza
  di fortuna/crollo) da preservare in caso di migrazione a gerarchia

### 2. Denaro Low (II, VIII casa — difesa del patrimonio)
- Filtro attuale: `verificaCondizioneDenaroLow()` — condizione
  intenzionalmente diversa dalle altre: puramente difensiva, non richiede
  la presenza di un benefico, esclude solo se c'è un malefico. Un'eventuale
  gerarchia a livelli qui avrebbe senso diverso (non "quale benefico
  premia di più" ma "quanto è pulita l'area da malefici") — da chiarire
  con il committente se ha senso portarla al modello a livelli o se
  resta intenzionalmente un filtro binario

## Metodo di lavoro (invariato rispetto a Lavoro/UX-0019, aggiornato con UX-0023/UX-0024)

Per ciascuna condizione, seguendo `docs/PROMPT_OPERATIVO_ASTROLAB.md`:
1. Chiarire con il committente i punti aperti (case target definitive,
   gerarchia di priorità tra i benefici, bonus orbo, eventuali vincoli
   specifici come la Regola 33 per Lavoro) — non assumere
2. Registrare la decisione in `docs/ux-myastral/DECISION_LOG_ux.md`
   (prossima voce libera: UX-0025)
3. Implementare rilevatore geometrico + `calcolaLivello*()` + VAL dedicata
4. **Includere fin da subito** il controllo veti ufficiali (esclusione) +
   astrolab-angoli/stellium misto (retrocessione via
   `OFFSET_FASCIA_VETO_MINORE`) — vedi sezione UX-0023/UX-0024 sopra
5. Coprire fin da subito tutte e tre le modalità di ricerca nella stessa
   sessione (lezione appresa da Amore/Decima/Lavoro)
6. Verificare sull'intero dataset reale, non un campione, prima di mettere
   in produzione (lezione appresa da Casa/UX-0023)
7. Aggiornare `docs/HANDOVER_OPERATIVO_astrolab.md` e
   `docs/START_HERE.md` a fine feature

## Ordine consigliato

Nessuna dipendenza tecnica reale tra le due restanti — l'ordine può essere
deciso dal committente in base a priorità d'uso. Suggerimento: Denaro
(due case, nessun vincolo speciale noto oltre l'alert Giove bistabile da
preservare) → Denaro Low (da chiarire prima se ha senso il modello a
livelli, data la sua natura puramente difensiva).
