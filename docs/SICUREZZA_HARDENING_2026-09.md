# Hardening di sicurezza — Settembre 2026

Sessione di remediation su ASTROLAB (branch `main`), avviata da una revisione
AppSec del repository pubblico `github.com/ldweb05/astrolab`.

## 1. Problemi risolti

| # | Problema | Azione | Commit |
|---|---|---|---|
| 1 | `DB_PASS`/`POSTGRES_PASSWORD` esposte in chiaro in un vecchio commit (`.env.bak_20260710_094533`), repo pubblico | Rotazione credenziali live via `ALTER USER` + aggiornamento `.env` | n/a (infrastruttura, non git) |
| 2 | `www/rilocazione.php.bak` servito in chiaro da Apache (source disclosure) | Rimosso dal repo | `dd85bca` |
| 3 | Segreti e file `.bak` nella history git | Purga con `git filter-repo` su tutti i branch e tag | `filter-repo` + force-push, vedi §3 sotto |
| 4 | `www/tests/` (~140 file, nessuna autenticazione, alcuni con `exec()`) raggiungibile via browser | Spostato in `tests/` (fuori dal docroot), montato in sola lettura su `/var/www/tests` | `598ec4c` |
| 5 | `.gitignore` senza pattern generici di backup | Aggiunti `*.bak`, `*.orig`, `*.old`, `*~`, `*.swp` | `09b2339` |
| 6 | `www/test_stelline_v2.php` raggiungibile da URL diretto da qualunque utente loggato (navbar nascondeva solo il link) | `richiediAdmin()`, poi pagina eliminata del tutto insieme al link navbar e all'API orfana `ricerca_stream_v2_api.php` | `ed6eb33`, `0bf97dc`, `ed762ed`, `d39410f` |

**Lavoro extra emerso durante il punto 6**: prima di eliminare `test_stelline_v2.php`
è stata portata la legenda colori stelline (già presente lì) anche in
`ricerca.php` e `ricerca_rl.php`, nelle due funzioni che mostrano davvero le
stelline (`renderTabellaGriglia`, `renderTabellaStandard` — non nelle viste
"Astri in Cuspide", che non le mostrano). CSS in `www/css/style.css`
(`94f8858`), pagine in `e456311` e `c66333b`.

## 2. Scoperte impreviste

- **Branch `master` mai pubblicato**: `docs/START_HERE.md` lo cita come
  "branch stabile", ma esisteva solo in locale sul Raspberry Pi, mai pushato
  su `origin`. Spiega la discrepanza notata a inizio sessione. È stato
  pubblicato per errore durante il force-push dei branch (insieme ad altri 7
  branch locali mai condivisi) e poi rimosso da `origin` su richiesta —
  restano tutti disponibili in locale.

## 3. Purga history — eccezione nota e accettata

`git filter-repo` ha ripulito correttamente tutti i branch e tag su
`origin` (verificato con un mirror-clone indipendente). **Eccezione**: due
Pull Request (`refs/pull/1/head`, `refs/pull/2/head`) mantengono ref
lato-server verso la history vecchia — GitHub li gestisce autonomamente,
`git push --force` non può toccarli e non esiste un comando per cancellarli.
Unica via ufficiale: ticket al supporto GitHub per la purga della cache
interna. **Rischio accettato**: la credenziale esposta è già stata ruotata
(punto 1), quindi il residuo è solo il vecchio sorgente PHP del `.bak`, non
un segreto attivo.

## 4. Lezioni operative da non dimenticare

- **`AllowOverride None`** è il default nell'immagine `php:8.3-apache` usata
  qui: un `.htaccess` in qualunque cartella di `www/` viene **ignorato in
  silenzio**. Per bloccare l'accesso a una cartella serve altro (spostarla
  fuori dal docroot, o una `<Directory>` nella config Apache con rebuild
  immagine).
- **`docker compose restart` non rilegge `env_file` né `volumes` nuovi.**
  Un container esistente mantiene l'ambiente e i mount con cui è stato
  creato. Qualunque modifica a `.env` o a `docker-compose.yml` richiede
  `docker compose up -d --force-recreate <servizio>`, non un semplice
  `restart` — costato un giro di debug non necessario durante la rotazione
  della password DB.
- **`POSTGRES_PASSWORD` non ha effetto su un volume dati già inizializzato.**
  Va cambiata live nel DB con `ALTER USER ... WITH PASSWORD ...`, non solo
  nel `.env`.

## 5. Stato attuale di `tests/`

Cartella spostata da `www/tests/` alla radice del repo (`tests/`), fuori dal
bind mount del docroot. Montata in sola lettura in `docker-compose.yml`:
`./tests:/var/www/tests:ro`. Eseguibile solo via
`docker compose exec astrolab-web php tests/run.php` (o gli script
`run_v6_*.sh`), non più raggiungibile da browser (verificato: 404 su URL
diretto). I path relativi verso `includes/`, `api/`, `css/`, `vendor/`,
`stampa.php` puntano ora a `../html/...` (nome della cartella "fratella"
dentro il container).
