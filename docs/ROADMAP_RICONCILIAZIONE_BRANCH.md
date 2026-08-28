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
