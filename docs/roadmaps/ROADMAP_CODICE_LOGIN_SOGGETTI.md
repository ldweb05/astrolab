# ROADMAP — CODICE automatico dei soggetti e login dei soggetti

**Creata:** 29-09-2026
**Branch:** `main`
**Stato:** design concordato con il committente il 29-09-2026 (decisioni C1-C12). Fasi A1-A3
completate il 29-09-2026 (vedi §4); A4 e blocco B da eseguire una fase alla volta, ciascuna
su conferma esplicita.
**Collegata a:** `docs/roadmaps/ROADMAP_DIARIO_RSM.md` (blocco C), di cui questa roadmap è
prerequisito: nel Diario RSM chi inserisce i dati del viaggio è il soggetto stesso.

---

## 1. Obiettivo

1. Compilare in automatico il campo CODICE di ogni nuovo soggetto, nel formato
   *prefisso dell'astrologo (2 lettere) + numero progressivo* (es. `LD001`, `RF120`), così
   che dal codice si capisca a quale astrologo appartiene il soggetto.
2. Usare il CODICE come username con cui il soggetto accede ad ASTROLAB, esclusivamente per
   inserire e consultare i dati dei propri viaggi RSM (Diario RSM).

## 2. Stato reale verificato (29-09-2026, DB del Pi, sola lettura)

- `soggetti.codice` è `VARCHAR(20)` facoltativo, con vincolo `UNIQUE` già presente
  (`soggetti_codice_key`, non riportato in `sql/schema_baseline.sql`).
- Codici esistenti: `RF001`-`RF005` (roxy), `LD001` (lodian), `TEST01` (admin). Tutti i
  soggetti hanno un codice, nessun duplicato.
- `utenti.nome_completo`: "Rossella Fumai" (roxy), "Lorenzo Diana" (lodian), vuoto (admin).
- La registrazione pubblica (`registrazione.php` → `Auth::registraUtentePubblico()`) chiede
  solo username, email e password.
- Il form soggetto in `index.php` ha il campo `#codice` libero (placeholder "Es: MR001");
  il valore è salvato così com'è da `api/soggetti_api.php`.
- `Auth::isLoggedIn()` considera loggato chi ha `$_SESSION['utente_id']`, e
  `richiediLogin()` protegge tutte le pagine dell'astrologo su questa base.
- `$_SESSION['soggetto_id']` / `$_SESSION['soggetto_nome']` sono già usati per il
  **soggetto attivo** scelto dall'astrologo: non vanno riutilizzati per il login del soggetto.
- Non esiste oggi un blocco dei tentativi di login falliti.
- I soggetti non hanno un indirizzo email.

## 3. Decisioni (29-09-2026)

### Blocco A — CODICE automatico
- **C1 — Prefisso salvato una sola volta:** nuova colonna `utenti.prefisso_codice`
  (`VARCHAR(2)`, univoca). Una volta assegnato non cambia più, anche se l'astrologo modifica
  il proprio nome: i codici già emessi restano coerenti.
- **C2 — Prefissi degli astrologi esistenti** impostati dalla migrazione in coerenza con i
  codici già in uso: roxy = `RF`, lodian = `LD`, admin = `AD` (ricavato dallo username in
  assenza di nome).
- **C3 — Regola di generazione del prefisso** (nuovi astrologi): lettere maiuscole senza
  accenti (É → E), solo A-Z. La prima lettera è sempre l'iniziale del nome; per la seconda
  si provano, nell'ordine: iniziale del cognome; 2ª, 3ª, … lettera del nome; 2ª, 3ª, …
  lettera del cognome; A-Z. Si usa il primo prefisso libero.
  Esempio: Rosa Fumai → `RF` occupato → `RO`.
- **C4 — Prefisso occupato** se è già il `prefisso_codice` di un altro utente, oppure se
  esiste un codice soggetto di un altro astrologo che inizia con quelle due lettere seguite
  da cifre.
- **C5 — Nome e Cognome obbligatori** in registrazione, in due campi separati, salvati in
  `utenti.nome_completo` come "Nome Cognome" (nessuna nuova colonna). Il prefisso viene
  calcolato e salvato nello stesso momento. Per un utente creato dall'admin in
  `admin_utenti.php` (campo unico `nome_completo`) il prefisso viene calcolato al suo primo
  soggetto, come per ogni utente senza prefisso (caso residuo): nome = prima parola di
  `nome_completo`, cognome = il resto, oppure lo username se `nome_completo` è vuoto. Limite
  accettato (29-09-2026): per un nome composto ("Maria Grazia De Luca") il prefisso
  risulta `MG` invece di `MD`, comunque stabile e univoco.
- **C6 — Numerazione:** contatore per astrologo `utenti.ultimo_numero_codice` + 1, con almeno
  3 cifre (`RF001` … `RF999`, poi `RF1000`). Il contatore non torna mai indietro: i numeri
  dei soggetti eliminati non vengono riusati e un codice non passa mai a un'altra persona.
  Per sicurezza si considera anche il numero più alto già presente con quel prefisso.
  *Aggiornamento 29-09-2026:* il contatore (migrazione `sql/011_contatore_codice.sql`) è
  stato aggiunto in Fase A2, perché il solo "numero più alto + 1" avrebbe riassegnato il
  numero dell'ultimo soggetto eliminato. Le migrazioni successive sono state rinumerate
  (accessi soggetti `012`, Diario RSM `013`).
- **C7 — Generazione lato server** in `api/soggetti_api.php` all'inserimento del soggetto,
  dentro una transazione; in caso di violazione del vincolo `UNIQUE` (due inserimenti
  simultanei) si ricalcola e si riprova. Il campo CODICE in `index.php` diventa di sola
  lettura (mostrato, non modificabile). In modifica di un soggetto il codice non cambia.
- **C8 — Soggetti esistenti:** nessun intervento, tutti hanno già un codice valido.

### Blocco B — Login dei soggetti
- **C9 — Credenziali separate:** nuova tabella `accessi_soggetti` (una riga per soggetto
  abilitato), distinta da `utenti`: un soggetto non può mai ottenere per errore il ruolo o i
  permessi di un astrologo. Username = CODICE del soggetto.
- **C10 — Sessione separata:** il login del soggetto imposta una chiave di sessione dedicata
  (es. `$_SESSION['accesso_soggetto']`) e **non** imposta mai `utente_id`. Effetto: tutte le
  pagine esistenti protette da `richiediLogin()` restano automaticamente chiuse ai soggetti,
  senza modificarle. Nuovo controllo dedicato (es. `richiediLoginSoggetto()`) per le sole
  pagine del Diario accessibili ai soggetti.
- **C11 — Abilitazione e password:** l'astrologo abilita l'accesso dalla scheda del proprio
  soggetto impostando una password provvisoria; il soggetto deve cambiarla al primo accesso.
  L'astrologo può disabilitare l'accesso o reimpostare la password. Password con lunghezza
  minima come per gli astrologi, salvate con `password_hash()`.
- **C12 — Protezione del login:** i codici sono prevedibili per sequenza, quindi blocco
  temporaneo dopo N tentativi falliti (valori da fissare in Fase B2), messaggio d'errore
  sempre generico ("Credenziali non valide."), `session_regenerate_id()` al login come per
  gli astrologi. Gli username degli astrologi non possono avere la forma di un codice
  (2 lettere + sole cifre), per evitare ambiguità nel form di login unico; oggi nessuno
  username esistente ha questa forma.

**Permessi del soggetto (vincolo inderogabile):** vede e modifica solo i propri viaggi e i
propri contributi condivisi, e legge i contributi condivisi degli altri. Non vede mai dati
di nascita, temi, RS, RL, sessioni o altri soggetti. Il suo astrologo vede i viaggi dei
propri soggetti. Ogni controllo è lato server.

## 4. Fasi

| Fase | Contenuto | File principali | Stato |
|---|---|---|---|
| A1 | Migrazione `sql/010_codice_soggetti.sql`: `utenti.prefisso_codice` + valori RF/LD/AD | `sql/` | Completata (`9f612a5`) |
| A2 | Generazione automatica del codice all'inserimento del soggetto, contatore che non riusa i numeri | `sql/011_contatore_codice.sql`, `www/includes/CodiceSoggetto.php`, `www/api/soggetti_api.php` | Completata (`1d0f278`) |
| A3 | Campo CODICE di sola lettura nel form soggetto, manuale aggiornato | `www/index.php`, `www/help_soggetti.php` | Completata (`f0fc22f`) |
| A4 | Nome e Cognome in registrazione + calcolo prefisso; username in forma di codice rifiutati (C12) | `www/registrazione.php`, `www/includes/Auth.php` | Da fare |
| B1 | Migrazione `sql/012_accessi_soggetti.sql` | `sql/` | Da fare |
| B2 | Login del soggetto, sessione dedicata, blocco tentativi, cambio password obbligatorio | `www/includes/Auth.php`, `www/login.php` | Da fare |
| B3 | Abilitazione accesso e password provvisoria dalla scheda soggetto | `www/index.php`, `www/api/soggetti_api.php` | Da fare |
| B4 | Area riservata del soggetto (solo Diario) | nuova pagina | Da fare |

Il blocco C (Diario RSM) segue in `docs/roadmaps/ROADMAP_DIARIO_RSM.md`, con migrazione
`sql/013_diario_rsm.sql`.

## 5. Vincoli

- `www/includes/Auth.php` è condiviso da tutta l'app: solo aggiunte di metodi, nessuna
  modifica al comportamento di `login()`, `isLoggedIn()` e `richiediLogin()` per gli
  astrologi. Test di regressione del login degli astrologi dopo ogni modifica.
- Punto di ripristino: tag `restore/pre-codice-soggetti-<data>` prima della prima modifica
  al codice.
- Regole di `docs/CHECKLIST_SICUREZZA_SVILUPPO.md` e §7-§8 di `ROADMAP_DIARIO_RSM.md`
  (CSRF, escaping, query parametrizzate, predisposizione VPS) valgono anche qui.
- Nessun `git add .` / `git add -A`.
