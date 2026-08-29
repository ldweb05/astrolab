# ROADMAP — Estensione architettura a gerarchia alle condizioni restanti

**Stato:** APERTA — nessuna fase iniziata.
**Origine:** al completamento di UX-0019 (condizione Lavoro, 2026-08-29), un
riepilogo di copertura ha mostrato che 3 delle 7 condizioni disponibili
(Decima, Amore, Lavoro) sono ora sul modello a gerarchia a livelli, mentre
le altre 4 restano sul vecchio modello a filtro-che-esclude, mai
armonizzato.

## Le 3 condizioni già completate (riferimento architetturale)

- **Decima** — UX-0015/UX-0018 (`docs/ux-myastral/DECISION_LOG_ux.md`)
- **Amore** — UX-0016/UX-0017
- **Lavoro** — UX-0019

Tutte e tre condividono lo stesso schema: rilevatore geometrico puro in
`RicercaRSFilters.php` (`verificaCondizione*()`, ritorna solo
`pianeti_in_casa`), gerarchia a livelli in `RuleEngineExtended.php`
(`calcolaLivello*()`), colonna VAL dedicata (`generaVal*()`), esclusione
totale se nessun segnale positivo (principio UX-0017), attive su tutte e
tre le modalità di ricerca (standard, griglia/area geografica/fascia
oraria, RL) fin dall'inizio (lezione appresa da Amore/Decima, applicata
da subito per Lavoro).

## Le 4 condizioni DA FARE

Tutte e quattro sono oggi un filtro binario (`['valida'=>bool,
'motivo'=>...]`) che esclude secco se manca il benefico richiesto o se è
presente un malefico — nessuna gerarchia a livelli, nessuna colonna VAL
dedicata, nessun bonus orbo. Già coperte (come filtro semplice, non come
gerarchia) su tutte e tre le modalità di ricerca.

### 1. Casa (IV casa — abitazione/famiglia)
- Filtro attuale: `verificaCondizioneCasa()` in `RicercaRSFilters.php`
- Unica casa target (IV) — già presente in
  `RuleEngineExtended::CASA_CONDIZIONE['Casa'] = 4`, utilizzabile da
  `calcolaPunteggioParziale()` ma senza una `calcolaLivelloCasa()` dedicata
- Candidata più semplice delle quattro: una sola casa, nessun caso "due
  case pari peso" da chiarire

### 2. Salute (I, VI, XII casa — protezione massima)
- Filtro attuale: `verificaCondizioneSalute()` — il più complesso delle
  quattro, con 5 regole di protezione già proprietarie (tolleranza
  pre-ingresso ampliata, scudo benefico in I, esclusione Sole in XII,
  rafforzamento ASC natale, protezione universale Giove/Venere)
- **Punto già chiarito con il committente**: la Regola 33 specifica di
  Lavoro (UX-0019) NON si applica a Salute, pur condividendo il settore
  VI — da tenere presente se/quando si decide la gerarchia
- Tre case target, non due: il modello Amore/Lavoro (due case pari peso)
  andrà esteso o ridiscusso per questo caso a tre case

### 3. Denaro (II, VIII casa — entrate/investimenti)
- Filtro attuale: `verificaCondizioneDenaro()`, include già un "alert
  Giove bistabile" proprietario (segnala se Giove può generare alternanza
  di fortuna/crollo) da preservare in caso di migrazione a gerarchia

### 4. Denaro Low (II, VIII casa — difesa del patrimonio)
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
   (prossima voce libera: UX-0020)
3. Implementare rilevatore geometrico + `calcolaLivello*()` + VAL dedicata
4. Coprire fin da subito tutte e tre le modalità di ricerca nella stessa
   sessione (lezione appresa da Amore/Decima/Lavoro)
5. Aggiornare `docs/HANDOVER_OPERATIVO_astrolab.md` e
   `docs/START_HERE.md` a fine feature

## Ordine consigliato

Nessuna dipendenza tecnica reale tra le quattro — l'ordine può essere
deciso dal committente in base a priorità d'uso. Suggerimento (dalla più
alla meno simile al modello già completato): Casa (una sola casa, la più
semplice) → Denaro (due case, nessun vincolo speciale noto) → Salute (tre
case, regole proprietarie da preservare) → Denaro Low (da chiarire se
ha senso il modello a livelli).
