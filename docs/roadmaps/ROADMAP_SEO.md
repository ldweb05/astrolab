# Roadmap — SEO e visibilità sui motori di ricerca

## Obiettivo
In previsione del trasferimento su VPS pubblica, rendere ASTROLAB compatibile con i motori
di ricerca e aiutarne la scoperta, senza toccare il motore astrologico e senza esporre le
pagine private.

**Stato (20-09-2026):** fase di studio e pianificazione. La Fase 1 (titoli) è completata; le
Fasi 2-7 sono una proposta di lavoro non ancora approvata dal committente. Le decisioni
aperte (sezione dedicata) vanno chiuse prima di iniziare la fase che le riguarda.

**Branch di lavoro:** `main`, con un tag `restore/...` creato prima di ogni intervento sul
codice. Il branch sul Pi non va cambiato.

**Ruolo:** su indicazione del committente, per questa sezione l'assistente opera anche come
webmaster, web developer e SEO. Restano del committente le decisioni su se e quando
modificare il codice e sui commit (PROMPT_OPERATIVO_ASTROLAB.md, §6).

**Documenti collegati:** `docs/FREEZE.md`, `docs/HANDOVER_OPERATIVO_VPS_v2.md`,
`docs/campagna-promozionale-e-sicurezza.md`.

---

## Stato reale (audit del 20-09-2026)
- `www/robots.txt` e `www/sitemap.xml` non esistono.
- Le pagine controllate hanno `lang="it"`, viewport e `<title>`. Non risultano meta
  description, robots, canonical né Open Graph. Audit da completare su `rs.php`, `stampa.php`,
  `tema.php`, `transiti.php`, `verifica-email.php` e `34_regole.html`.
- Server: immagine `php:8.3-apache` con `mod_rewrite` attivo (Dockerfile). Non è NGINX con
  PHP-FPM come ipotizzato in `campagna-promozionale-e-sicurezza.md`.
- `index.php` è la pagina Soggetti ed è privata. Chi non è loggato viene mandato a
  `login.php?next=<url richiesto>` da `Auth::richiediLogin()`: per un crawler il sito è,
  di fatto, una pagina di login.

**Mappa pubblico / privato** (criterio: la pagina chiama `richiediLogin`)
- Private: `cambia_password`, `compare_ril`, `compare_rs`, `dashboard`, gli 8 `help_*`,
  `index`, `ricerca`, `ricerca_rl`, `rilocazione`, `rl`, `rs`, `stampa`, `tema`,
  `transiti`. (`test_stelline_v2` rimosso: pagina eliminata, vedi
  `docs/SICUREZZA_HARDENING_2026-09.md`)
- Pubbliche (senza login): `login`, `logout`, `registrazione`, `verifica-email` e il file
  statico `34_regole.html`.
- `admin_utenti.php` non chiama `richiediLogin`: la protezione è presumibilmente
  `richiediAdmin()`, da verificare.

---

## Fase 1 — Titoli (COMPLETATA, 20-09-2026)
- Schema: `Nome pagina — AstroLab` su tutte le pagine; `login.php` con la frase
  "AstroLab — la tua esperienza in Astrologia Attiva"; titoli di ricerca RSM e RL distinti.
- Commit `261f787` su `main` (15 file, solo `<title>`). Punto di ripristino:
  tag `restore/pre-seo-title-2026-09-20` (`14f9afa`). Test funzionale nel browser: OK.
- Nota: `rs.php` ha due `<title>`; il secondo è nella finestra di stampa della Relazione
  Annuale (template JavaScript).
- Provvisorio: quando esisterà la landing, la frase passerà a lei e `login.php` tornerà
  a "Accesso — AstroLab".

## Fase 2 — Fondamenta tecniche (da fare)
- Nuovo `www/includes/seo.php` per meta description, canonical, robots e Open Graph, senza
  modificare `header_nav.php` (condiviso).
- `noindex` su pagine private, admin, `login.php` (comprese le varianti `?next=`) e
  `verifica-email.php`; su quest'ultima anche `Referrer-Policy: no-referrer`, perché il
  token viaggia nell'URL.
- `robots.txt`: `Disallow` solo per `/api/` e aree tecniche. Una pagina bloccata in
  `robots.txt` non mostra il suo `noindex`, quindi le pagine da escludere dall'indice
  usano `noindex` senza `Disallow`.
- `sitemap.xml` con le sole pagine pubbliche indicizzabili.

## Fase 3 — Landing pubblica (da fare, su richiesta del committente)
- Contenuto: spiegazione generale dell'utilità dell'Astrologia Attiva e di ciò in cui
  AstroLab può aiutare; servizio gratuito con funzioni avanzate riservate ai sostenitori
  annuali, per coprire i costi di mantenimento (server, ecc.); inviti a registrarsi e ad
  accedere; FAQ breve.
- Linea editoriale proposta: testo descrittivo, senza promesse di efficacia previsionale.
- Tecnica proposta: HTML renderizzato lato server, un solo H1, dati strutturati
  `WebApplication` senza valutazioni, utente già loggato rimandato alla dashboard.
- `index.php` resta la pagina Soggetti. Come far servire `/` dalla nuova pagina
  (`DirectoryIndex` o riscrittura) è da verificare: la presenza di un `.htaccess` attivo
  non è stata controllata.

## Fase 4 — Contenuti pubblici di approfondimento (da decidere)
- Il manuale (8 pagine `help_*`) oggi è privato. Se reso pubblico: separarlo dal menu di
  sessione senza modificare `header_nav.php` e rileggere i testi prima della pubblicazione.
- Eventuali guide su Rivoluzione Solare, Rivoluzione Lunare e rilocazione.

## Fase 5 — Pulizia pre-VPS
- [x] `www/rilocazione.php.bak` — rimosso dal repo (risolto nella sessione di hardening
  2026-09, vedi `docs/SICUREZZA_HARDENING_2026-09.md`).
- [x] `www/test_stelline_v2.php` — pagina eliminata (stessa sessione).
- [x] `www/tests/` — spostato fuori dal docroot in `tests/`, montato in sola lettura
  su `/var/www/tests` (stessa sessione).
- [ ] `www/composer.json`, `www/composer.lock`, `www/vendor/` (tracciato in git) — ancora
  da affrontare, non toccato nella sessione di hardening.
- Da verificare: `www/uploads/`. Da decidere: `34_regole.html` (vedi decisioni aperte).

## Fase 6 — Server VPS
- HTTPS, redirect http verso https, un solo host canonico (www o non-www), HSTS, header di
  sicurezza, compressione e cache degli asset.
- `expose_php` e `ServerTokens`: non verificati, da controllare.
- `docker-compose.production.yml` è stato rimosso nel commit `54550d2`: va ricreato.
  Nel compose locale PostgreSQL (5440) e Adminer (8091) sono pubblicati su `0.0.0.0`: in
  produzione non devono essere raggiungibili dall'esterno.
- `noindex` su ogni ambiente diverso dalla produzione.
- Registrare le attività in `docs/HANDOVER_OPERATIVO_VPS_v2.md`.

## Fase 7 — Verifiche dopo il deploy
- Search Console (verifica della proprietà via DNS) e invio della sitemap.
- Lighthouse e Core Web Vitals; controllo che le pagine private non compaiano nell'indice.
- Aspettative: la landing da sola non porta traffico; i risultati arrivano in settimane o
  mesi e dipendono soprattutto dai contenuti pubblici.

---

## Decisioni aperte
1. Identificabilità del titolare e anonimato (vedi `campagna-promozionale-e-sicurezza.md`):
   un servizio con dati personali e contributi ha obblighi di informativa e fiscali. Da
   chiarire con un professionista prima di scrivere le pagine legali.
2. Formula del modello gratuito con sostenitori: "contributo" o "donazione" se in cambio si
   sbloccano funzioni; aspetti fiscali.
3. Uso di "Astrologia Attiva" e delle 34 regole nelle pagine pubbliche; riferimenti a terzi;
   `34_regole.html` pubblico o no.
4. Il manuale diventa pubblico?
5. Lingue previste e struttura degli URL.
6. Dominio e host canonico.
7. `registrazione.php` indicizzabile o `noindex`?

## Vincoli
- Nessuna modifica a RuleEngine e alle 120 Rule (`docs/FREEZE.md`).
- File condivisi (`js/app.js`, `css/style.css`, `includes/header_nav.php`): massima cautela,
  preferire file nuovi.
- Ogni intervento sul codice segue `docs/PROMPT_OPERATIVO_ASTROLAB.md`: tag `restore/`
  prima, patch via script Python, verifiche nell'ordine del §5, git solo su conferma.
