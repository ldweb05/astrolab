# ROADMAP — Estensione architettura a gerarchia alle condizioni restanti

**Stato:** APERTA — 1 delle 4 condizioni restanti completata (Salute).
**Origine:** al completamento di UX-0019 (condizione Lavoro, 2026-08-29), un
riepilogo di copertura ha mostrato che 3 delle 7 condizioni disponibili
(Decima, Amore, Lavoro) erano sul modello a gerarchia a livelli, mentre
le altre 4 restavano sul vecchio modello a filtro-che-esclude, mai
armonizzato. Con UX-0020 (Salute, 2026-08-30) sono ora 4 su 7.

## Le 4 condizioni già completate (riferimento architetturale)

- **Decima** — UX-0015/UX-0018 (`docs/ux-myastral/DECISION_LOG_ux.md`)
- **Amore** — UX-0016/UX-0017
- **Lavoro** — UX-0019
- **Salute** — UX-0020 (con un'eccezione architetturale, vedi sotto)

Decima, Amore e Lavoro condividono lo stesso schema: rilevatore geometrico
puro in `RicercaRSFilters.php` (`verificaCondizione*()`, ritorna solo
`pianeti_in_casa`), gerarchia a livelli in `RuleEngineExtended.php`
(`calcolaLivello*()`), colonna VAL dedicata (`generaVal*()`), esclusione
totale se nessun segnale positivo (principio UX-0017), attive su tutte e
tre le modalità di ricerca (standard, griglia/area geografica/fascia
oraria, RL) fin dall'inizio.

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

## Le 3 condizioni restanti DA FARE

Tutte e tre sono oggi un filtro binario (`['valida'=>bool,
'motivo'=>...]`) che esclude secco se manca il benefico richiesto o se è
presente un malefico — nessuna gerarchia a livelli, nessuna colonna VAL
dedicata, nessun bonus orbo. Già coperte (come filtro semplice, non come
gerarchia) su tutte e tre le modalità di ricerca.

### 1. Casa (IV casa — abitazione/famiglia)
- Filtro attuale: `verificaCondizioneCasa()` in `RicercaRSFilters.php`
- Unica casa target (IV) — già presente in
  `RuleEngineExtended::CASA_CONDIZIONE['Casa'] = 4`, utilizzabile da
  `calcolaPunteggioParziale()` ma senza una `calcolaLivelloCasa()` dedicata
- Candidata più semplice delle restanti: una sola casa, nessun caso "due
  case pari peso" da chiarire

### 2. Denaro (II, VIII casa — entrate/investimenti)
- Filtro attuale: `verificaCondizioneDenaro()`, include già un "alert
  Giove bistabile" proprietario (segnala se Giove può generare alternanza
  di fortuna/crollo) da preservare in caso di migrazione a gerarchia

### 3. Denaro Low (II, VIII casa — difesa del patrimonio)
- Filtro attuale: `verificaCondizioneDenaroLow()` — condizione
  intenzionalmente diversa dalle altre: puramente difensiva, non richiede
  la presenza di un benefico, esclude solo se c'è un malefico. Un'eventuale
  gerarchia a livelli qui avrebbe senso diverso (non "quale benefico
  premia di più" ma "quanto è pulita l'area da malefici") — da chiarire
  con il committente se ha senso portarla al modello a livelli o se
  resta intenzionalmente un filtro binario

## Metodo di lavoro (invariato rispetto a Lavoro/UX-0019)

Per ciascuna condizione, seguendo `docs/PROMPT_OPERATIVO_ASTROLAB.md`:
1. Chiarire con il committente i punti aperti (case target definitive,
   gerarchia di priorità tra i benefici, bonus orbo, eventuali vincoli
   specifici come la Regola 33 per Lavoro) — non assumere
2. Registrare la decisione in `docs/ux-myastral/DECISION_LOG_ux.md`
   (prossima voce libera: UX-0021)
3. Implementare rilevatore geometrico + `calcolaLivello*()` + VAL dedicata
4. Coprire fin da subito tutte e tre le modalità di ricerca nella stessa
   sessione (lezione appresa da Amore/Decima/Lavoro)
5. Aggiornare `docs/HANDOVER_OPERATIVO_astrolab.md` e
   `docs/START_HERE.md` a fine feature

## Ordine consigliato

Nessuna dipendenza tecnica reale tra le tre restanti — l'ordine può essere
deciso dal committente in base a priorità d'uso. Suggerimento (dalla più
alla meno simile al modello già completato): Casa (una sola casa, la più
semplice) → Denaro (due case, nessun vincolo speciale noto) → Denaro Low
(da chiarire se ha senso il modello a livelli).
