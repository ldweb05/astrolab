# Roadmap Riconciliazione Branch ASTROLAB

**Sessione iniziata:** 2026-08-28

## Stato mappatura (completata)

Il fork reale mai riconciliato e' tra:
- **Linea A** (34 regole): feature/allineamento-myastral -> feature/2-astri-in-cuspide -> new_dashboard / feature/sostituzione-stelline-v2 (identici)
- **Linea B**: fase9-comparator-quota, con due fratelli non ancora riuniti: chore/porta-feature-da-allineamento-myastral e fix/nome-posizione-mappa

Comune antenato A/B: a52c2e9

Isolato, basso rischio: fix/geocoding-nominatim-precisione (base main, commit e3f3a1d)

Gia' assorbito (nessuna riconciliazione necessaria): feature/registrazione-utenti

Branch gia' mergeati in main, solo storico: feature/rsm-next-optimization, feature/rsm-tranche-optimization, feature/rsm-v3-fase3-backend, feature/sql-spatial-dedup, fix/localita-memory-limit, master, stable-rsm-memory-fix

Branch effettivamente in uso in produzione sul Pi al 2026-08-28: new_dashboard

## Decisione tronco finale

**consolidamento** (nuovo branch da main), per non rischiare main finche' il lavoro non e' concluso e validato.

## Ordine di riconciliazione deciso

1. fix/geocoding-nominatim-precisione (isolato, basso rischio)
2. Riunire chore/porta-feature-da-allineamento-myastral + fix/nome-posizione-mappa dentro fase9-comparator-quota (completare Linea B)
3. Riconciliare Linea B completa con Linea A (new_dashboard) -- passo piu' grande, conflitto noto su usort() in ricerca_stream_api.php

## Checkpoint creati (2026-08-28)

| Tag | Branch fotografato | Comando di ripristino |
|-----|--------------------|-----------------------|
| checkpoint-riconciliazione-new_dashboard-2026-08-28 | new_dashboard (produzione attuale) | git checkout checkpoint-riconciliazione-new_dashboard-2026-08-28 |
| checkpoint-riconciliazione-main-2026-08-28 | main | git checkout checkpoint-riconciliazione-main-2026-08-28 |
| checkpoint-riconciliazione-feature-allineamento-myastral-2026-08-28 | feature/allineamento-myastral | git checkout checkpoint-riconciliazione-feature-allineamento-myastral-2026-08-28 |
| checkpoint-riconciliazione-feature-sostituzione-stelline-v2-2026-08-28 | feature/sostituzione-stelline-v2 | git checkout checkpoint-riconciliazione-feature-sostituzione-stelline-v2-2026-08-28 |
| checkpoint-riconciliazione-fase9-comparator-quota-2026-08-28 | fase9-comparator-quota | git checkout checkpoint-riconciliazione-fase9-comparator-quota-2026-08-28 |
| checkpoint-riconciliazione-chore-porta-feature-da-allineamento-myastral-2026-08-28 | chore/porta-feature-da-allineamento-myastral (solo locale, ora al sicuro) | git checkout checkpoint-riconciliazione-chore-porta-feature-da-allineamento-myastral-2026-08-28 |
| checkpoint-riconciliazione-fix-nome-posizione-mappa-2026-08-28 | fix/nome-posizione-mappa (solo locale, ora al sicuro) | git checkout checkpoint-riconciliazione-fix-nome-posizione-mappa-2026-08-28 |
| checkpoint-riconciliazione-fix-geocoding-nominatim-precisione-2026-08-28 | fix/geocoding-nominatim-precisione | git checkout checkpoint-riconciliazione-fix-geocoding-nominatim-precisione-2026-08-28 |

**Nota:** per tornare a un funzionamento reale sul Pi/Docker dopo un checkout di emergenza, eseguire poi `docker compose restart astrolab-web` per invalidare l'OPcache.

## Vincoli attivi per questa sessione

- Nessuna cancellazione dei branch originali finche' l'utente non conferma esplicitamente, in fase separata.
- Ogni git push --force richiede conferma esplicita separata, anche se il commit sottostante era gia' confermato.
- File www/api/stelline_v2_api.php.bak: file orfano non tracciato, non referenziato da nessun file PHP/JS, rinominato da .php a .bak il 2026-08-28 (non cancellato, nel dubbio).


## Scoperta importante (2026-08-28, durante Passo 2)

Il branch chore/porta-feature-da-allineamento-myastral contiene 9 commit,
non solo il fix header sticky come descritto in FREEZE.md. Tra questi,
www/includes/NascitaGmtHelper.php (fix del bug GMT giorno di nascita,
gia noto e documentato) e risultato IDENTICO byte per byte a quello gia
presente in new_dashboard (produzione attuale) e nei 9 file che lo usano.

CONCLUSIONE: il bug GMT e gia risolto e in produzione. Nessuna migrazione
necessaria su questo fronte quando uniremo Linea B con Linea A (Passo 3).

Gli altri 8 commit di chore (modal correzione tempo, toggle Mostra/Nascondi
Dati, sezioni collassabili, DST timezonedb, header fisso) NON sono un
sottoinsieme di new_dashboard (merge-base torna al vecchio a52c2e9, non
al tip di chore) - vanno valutati singolarmente, uno alla volta, prima di
un merge in blocco.


## Passo 2 concluso (2026-08-28)

fix/nome-posizione-mappa: codice gia assorbito in new_dashboard (via
feature/2-astri-in-cuspide, 2026-08-22). Il suo unico commit di punta e
solo docs (HANDOVER+DECISION_LOG). Nessun codice da migrare.

chore/porta-feature-da-allineamento-myastral: tentato cherry-pick isolato
di 67b3c5a (commento protezione anti-regressione time-controls) su
consolidamento -> conflitto (il modale che il commento descrive non esiste
ancora su main/consolidamento, e stato costruito nei commit precedenti di
chore, gia superati da new_dashboard). Cherry-pick abortito, nessun danno.

DECISIONE: rimandare al Passo 3 (quando si unira new_dashboard) i seguenti
4 elementi residui di chore, ancora assenti in new_dashboard, da applicare
li sopra dove la base esiste gia:
1. Commento protezione anti-regressione time-controls (67b3c5a) - zero rischio
2. Fix header sticky tabella risultati (f914d2b) - gia noto da FREEZE.md
3. Sezioni collassabili Sessioni Salvate/Bonus e Veti (dcb72b2)
4. Header fisso/pannello trascinabile (4879e6d) - da verificare con cura,
   tocca la zona del div time-controls gia oggetto di regressioni ricorrenti


## Analisi Passo 3 - avviato (2026-08-28)

diff consolidamento vs new_dashboard: 128 file, +24765/-86250 righe.
Numero enorme atteso: consolidamento e ancora basato su main (molto
indietro), non riflette la vera distanza tra le linee di lavoro reali.

CONFLITTO usort() CITATO NEL PROMPT DI SESSIONE: verificato risolto.
www/ricerca_stream_api.php e stato spostato/riorganizzato in
www/api/ricerca_stream_api.php durante levoluzione di new_dashboard.
Il suo usort() attuale ordina SOLO su v2_stelle_totali (stelline V2),
nessuna menzione di livello Decima/Regola 14 - coerente con il fatto che
quel lavoro (mai committato) e stato scartato a fine sessione precedente.
CONCLUSIONE: nessun conflitto di logica applicativa reale da arbitrare
per il Passo 3. Resta solo il problema strutturale di allineare due basi
di codice molto distanti (riorganizzazione cartelle inclusa, es. www/*.php
API spostate in www/api/*.php).


## Passo 3 completato (2026-08-28)

Merge di new_dashboard in consolidamento: eseguito, zero conflitti reali,
sintassi PHP/JS verificata su tutti i file (61 PHP + JS modificati).

4 elementi residui di chore/porta-feature-da-allineamento-myastral applicati
via cherry-pick su consolidamento, ora che new_dashboard fornisce la base
necessaria:
1. Commento protezione time-controls (67b3c5a) - applicato, conflitto minore
   nel log HANDOVER risolto per ordine cronologico
2. Fix header sticky tabella (f914d2b) - applicato; conflitto reale in
   style.css (max-width 1250px + trasparenza) risolto mantenendo la
   versione piu recente/attuale (HEAD) su decisione esplicita dellutente;
   stesso approccio per piccolo conflitto in ricerca_rl.php (link font)
3. Sezioni collassabili (dcb72b2) - applicato, conflitto minimo in rs.php
   risolto mantenendo HEAD
4. Header fisso/pannello trascinabile (4879e6d) - applicato; 12 blocchi di
   conflitto in style.css (design bianco attuale vs design crema superato)
   risolti TUTTI mantenendo HEAD su decisione esplicita dellutente
   (lasciare il design attualmente in uso)

Esito: solo 2 file risultano effettivamente diversi da new_dashboard puro
dopo tutti e 4 i cherry-pick (HANDOVER_OPERATIVO_astrolab.md, rs.php) -
tutto il resto (style.css, ricerca_rl.php, ricerca.php, rl.php) era gia
completamente coperto da new_dashboard.

STATO: riconciliazione codice completata. Prossimo passo: test funzionale
visivo di consolidamento (secondo stack Docker su porta diversa, mai
impostato finora), poi decisione su come/quando promuovere consolidamento
a riferimento definitivo (es. merge in main).


## PROMEMORIA PULIZIA FINALE (richiesto esplicitamente dallutente)

Al termine della sessione di test, RIMUOVERE tutto cio che non fa parte
del funzionamento reale del sito:
- Stack Docker di test: docker-compose.yml in astrolab-consolidamento/,
  container astrolab-web-test e astrolab-db-test (docker compose down -v)
- Cartella dati: astrolab-consolidamento/postgres-data-test/
- Worktree astrolab-consolidamento/ stesso, una volta che consolidamento
  e stato promosso/mergiato nel branch definitivo (git worktree remove)
- Worktree astrolab-lineab/ (usato per analisi Linea B, ormai non piu
  necessario)
- File www/api/stelline_v2_api.php.bak in ~/astrolab (produzione) - gia
  verificato non referenziato, da eliminare solo su conferma esplicita
