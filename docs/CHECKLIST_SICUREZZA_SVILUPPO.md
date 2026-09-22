# Checklist di sicurezza per lo sviluppo — ASTROLAB

Regole nate direttamente dai problemi trovati e risolti nella sessione di
hardening di settembre 2026 (vedi `docs/SICUREZZA_HARDENING_2026-09.md` per
il dettaglio completo). Lo scopo di questo documento è evitare di dover
rifare lo stesso lavoro tra qualche anno: ogni regola qui sotto corrisponde
a un problema reale che c'è già stato.

Da leggere insieme a `docs/PROMPT_OPERATIVO_ASTROLAB.md`, che la richiama
come lettura obbligatoria.

## 1. File temporanei, backup, segreti

- **Mai creare copie `.bak`/`.old`/`.orig` di un file dentro `www/` o
  altrove nel repo**, nemmeno per un minuto durante un test manuale. Se
  serve una copia di sicurezza prima di modificare un file, tienila fuori
  dalla cartella del repository (es. `~/backup-temp/`, mai dentro
  `~/astrolab/`). Un file del genere, anche cancellato subito dopo, se
  finisce per errore in un `git add .` resta nella history per sempre.
- **Mai copiare `.env` con un nome tipo `.env.bak_<data>` dentro il repo.**
  Stessa logica: se ti serve un backup delle variabili d'ambiente, mettilo
  fuori dalla cartella del progetto.
- **Non usare mai `git add .` o `git add -A`.** Elenca sempre esplicitamente
  i file da aggiungere (già in `PROMPT_OPERATIVO_ASTROLAB.md` §6, ripetuto
  qui perché è la difesa più semplice ed efficace contro tutto quanto sopra).
- `.gitignore` copre già `*.bak`, `*.orig`, `*.old`, `*~`, `*.swp` — se in
  futuro nasce un nuovo tipo di file temporaneo ricorrente, aggiungilo lì
  subito, non aspettare che succeda un incidente.

## 2. Pagine di test, debug, laboratorio

- **Non creare mai una pagina di test/debug/laboratorio dentro `www/`.**
  Va nella cartella `tests/` alla radice del repo (fuori dal docroot,
  montata in sola lettura su `/var/www/tests` in `docker-compose.yml`),
  eseguibile solo via `docker compose exec astrolab-web php tests/...`.
  Se la pagina deve essere per forza raggiungibile da browser (es. per
  ispezionare visivamente un output), proteggila con `richiediAdmin()`
  **fin dal primo commit** — non "aggiungo l'autenticazione dopo, tanto è
  temporanea". Una pagina "temporanea" con `test_stelline_v2.php` è rimasta
  in produzione per settimane con solo `richiediLogin()`, raggiungibile da
  chiunque fosse loggato semplicemente digitando l'URL.
- Un link nascosto in navbar con `<?php if ($isAdmin): ?>` **non è una
  protezione**: nasconde il link, non blocca l'URL diretto. La protezione
  vera è sempre lato server, dentro la pagina stessa.
- Prima di eliminare una pagina di laboratorio superata, controlla se ha
  un endpoint API dedicato che diventerebbe codice morto (come
  `ricerca_stream_v2_api.php`) ed eliminalo insieme.

## 3. Docker / ambiente

- **`docker compose restart` non rilegge `env_file` né `volumes` nuovi.**
  Se cambi `.env` o `docker-compose.yml`, serve
  `docker compose up -d --force-recreate <servizio>`. Un `restart` sembra
  funzionare (il container riparte, non dà errori) ma continua a usare
  l'ambiente/i mount con cui era stato creato — un errore silenzioso,
  difficile da notare finché non si testa davvero la funzionalità cambiata.
- **`POSTGRES_PASSWORD` non ha alcun effetto su un volume dati Postgres già
  inizializzato.** Per ruotare la password di un utente DB già esistente
  serve `ALTER USER ... WITH PASSWORD ...` eseguito dentro il container,
  non solo modificare `.env`.
- `AllowOverride None` è il default dell'immagine `php:8.3-apache` usata
  qui: un `.htaccess` in qualunque sottocartella di `www/` viene **ignorato
  in silenzio**, senza errori. Non fare mai affidamento su un `.htaccess`
  per bloccare l'accesso a qualcosa senza prima aver verificato con
  `docker compose exec astrolab-web grep -A3 "Directory /var/www/"
  /etc/apache2/apache2.conf` che `AllowOverride` sia effettivamente attivo.

## 4. Git — branch e history

- **Non lasciare branch locali "dimenticati" per mesi/anni senza pubblicarli
  né eliminarli.** È già successo con `master` (mai pushato, ha causato
  confusione con `docs/START_HERE.md` che lo citava come branch stabile) e
  con altri 7 branch locali riemersi per errore durante un `git push
  --all --force`. Ogni branch locale che non serve più va eliminato
  (`git branch -d <nome>`); ogni branch locale attivo va tenuto allineato
  al remote.
- **Se in futuro serve un'altra riscrittura della history** (`git
  filter-repo` o simili): va fatta su **tutti** i branch locali esistenti,
  non solo su quello con cui si sta lavorando — verificare prima con
  `git branch` (non solo `git branch -r`) cosa esiste davvero in locale.
  Dopo la riscrittura, va fatto un mirror-clone indipendente
  (`git clone --mirror <url> /percorso/verifica`) per controllare dal
  punto di vista di un osservatore esterno, non dal proprio checkout che
  "sa già" cosa aspettarsi.
- **Le Pull Request su GitHub mantengono un riferimento permanente
  (`refs/pull/N/head`) alla history con cui sono state aperte**, che
  `git push --force` non può cancellare. Se una PR è mai stata aperta da
  un branch con dati sensibili, quella copia resta accessibile finché
  GitHub non la purga su richiesta esplicita (ticket al supporto). Tienilo
  presente **prima** di aprire una PR da un branch che potrebbe contenere
  qualcosa da non esporre.

## 5. Prima del trasferimento su VPS pubblica

Quando arriverà il momento (vedi `docs/roadmaps/ROADMAP_SEO.md`, Fase 6),
ripetere l'audit di sicurezza da capo usando
`docs/SICUREZZA_HARDENING_2026-09.md` come traccia: verificare di nuovo
file `.bak`/temporanei nel docroot, pagine di test raggiungibili,
credenziali nella history, `.gitignore` aggiornato. Un ambiente esposto
pubblicamente ha una superficie d'attacco diversa da un Raspberry Pi in
LAN domestica — non dare per scontato che quanto va bene oggi resti valido
dopo il trasferimento.
