# Roadmap — Nuova Dashboard (dashboard.php)

## Obiettivo
Creare una nuova pagina `dashboard.php` con estetica "Sahara — Warm
Minimalism" (EB Garamond + Manrope, palette calda), lavorando in
background senza toccare le pagine esistenti. Nessun link verso
`dashboard.php` verrà attivato finché non sarà completa: fino ad
allora si raggiunge solo con URL diretto.

Branch di lavoro: `new_dashboard` (verificare stato reale prima di
ogni step, non assumere che coincida con `feature/2-astri-in-cuspide`
solo perché il comportamento di `index.php` osservato coincide).

---

## Decisioni prese (2026-08-22)

1. **Help**: dropdown identico a quello attuale (8 voci: Account,
   Calcoli, Comparatore, FAQ, Interfaccia, Report, Ricerca, Soggetti),
   solo restyle nei colori/font Sahara. Nessuna modifica di struttura
   o contenuti.
2. **Nome Astrologo**: resta testo non cliccabile, come oggi. Accanto
   compare il nome del soggetto attivo (comportamento invariato).
3. **Soggetto di studio / index.php**: `index.php` resta la pagina di
   gestione soggetti così com'è. Il collegamento nome-soggetto →
   dashboard è previsto ma SOLO a fine lavoro: oggi il nome del
   soggetto in `index.php` è già un link (`<a onclick="apriRS(id)">`,
   riga ~347) che chiama `apriRS(id)` in `js/app.js` (`window.location
   .href = 'rs.php?id=' + id`, righe 223-225). Verificato sul branch
   `feature/2-astri-in-cuspide`. Quando la dashboard sarà completa,
   questo redirect cambierà target da `rs.php` a `dashboard.php`
   (stesso meccanismo, nuova funzione `apriDashboard(id)` o modifica
   di `apriRS`) — non prima.

---

## Mappatura voce → pagina reale

| Voce mockup | Pagina/funzione reale | Stato |
|---|---|---|
| Logo AstroLab | `dashboard.php` | in sviluppo — nessun link finché non è completa |
| Utenti | `admin_utenti.php` | esiste, solo admin |
| Help (dropdown) | `help_account.php`, `help_calcoli.php`, `help_comparatore.php`, `help_faq.php`, `help_interfaccia.php`, `help_report.php`, `help_ricerca.php`, `help_soggetti.php` | esistono, solo restyle |
| Nome Astrologo | — (testo, non link) | invariato |
| Soggetto di studio | — (testo, non link, per ora) | invariato fino a fine lavoro |
| Password | `cambia_password.php` | esiste |
| Esci | `logout.php` | esiste |
| Tab TEMA | `tema.php` | esiste |
| Tab LOCALITÀ | `ricerca.php` (tipo=localita) | esiste |
| Tab AEROPORTI | `ricerca.php` (tipo=aeroporti) | esiste, stessa pagina di LOCALITÀ |
| Scelta Anno + Tipo Analisi + Cerca | `rs.php` / `rl.php` | esistono |
| Transiti | `transiti.php` | esiste |
| Rilocazione | `rilocazione.php` | esiste |
| Pannello "Cielo Natale" | dati da `tema.php` | da collegare (Fase 3) |
| Pannello "RS per residenza" | dati da `rs.php` | da collegare (Fase 3) |

---

## Fasi

- [x] Fase 1 — Rebranding testuale mockup statico (AstroPrecision → AstroLab)
- [x] Fase 2 — Mockup statico rifinito (navbar centrata, dimensioni voci menu, palette Sahara) — approvato dall'utente
- [x] Fase 3 — Creare `dashboard.php` reale sul Pi (scheletro protetto da auth, non linkato da nessuna parte), verificare che il container lo serva senza errori
- [x] Fase 4 — Collegare tab/pannelli ai dati reali
  - [x] Bottoni Transiti/Rilocazione → transiti.php / rilocazione.php (con ?id= dinamico)
  - [x] Tab LOCALITÀ/AEROPORTI → ricerca.php (con ?tipo=localita per LOCALITÀ)
  - [x] Riga ricerca (Scelta Anno 1960->+7 + Tipo Analisi + Cerca) → rs.php / rl.php
  - [x] DECISIONE (2026-08-23): i due pannelli grafico "Cielo Natale"/"RS per residenza"
    del mockup sono stati eliminati — rs.php mostra già entrambe le ruote insieme
    (Tema Natale + RS) con pulsanti/collassabili/mappa; duplicarli in dashboard.php
    avrebbe richiesto o un iframe ingombrante o la duplicazione della logica di
    rendering. Sostituiti con: tab TEMA (→ tema.php) e nuovo tab RSM (→ rs.php),
    entrambi con ?id= dinamico. Contenitore centrale ristretto di conseguenza.
  - [x] Nome Cognome: dropdown reale con i soggetti dell'astrologo (1 solo soggetto
    → nome mostrato in automatico; più soggetti → placeholder "Seleziona Soggetto").
    Campi Data/Ora di Nascita (ora locale, non GMT) si riempiono di conseguenza.
  - [x] Tab TEMA/RSM, bottoni Transiti/Rilocazione e "Cerca" si aggiornano dinamicamente
    via JS (aggiornaSoggettoSelezionato) in base al soggetto scelto nel dropdown.
  - [x] Campo Tipo Analisi disabilitato finché non è selezionato un soggetto (solo
    caso multi-soggetto; con un soggetto solo è già abilitato al caricamento).
- [x] Fase 5 — Dropdown Help costruito direttamente con palette Sahara fin dall'inizio (Fase 4), nessun restyle successivo necessario
- [x] Fase 6 — Collegamento completato (23-08-2026):
  - [x] Click sul nome soggetto in index.php: da apriRS() (rs.php) a nuova
    apriDashboard() (dashboard.php), con id preservato
  - [x] dashboard.php ora gestisce ?id= esplicito: verifica appartenenza
    all'astrologo, imposta soggetto attivo, preseleziona nel dropdown anche
    con più soggetti salvati (prima funzionava solo con un soggetto unico)
  - [x] Rimosso in app.js il bottone "↺ Rivoluzione Solare" (confermato
    codice morto: nessuna pagina chiamava caricaSoggetti(), solo la versione
    locale caricaSoggettiConDropdown() di index.php, senza quel bottone).
    Funzione caricaSoggetti() lasciata intatta (referenziata da
    _caricaSoggettiOrig in index.php, anche se mai invocata)
  - [x] Logo in header_nav.php (condiviso da 12 pagine) ora punta a
    dashboard.php invece di index.php, via _navUrl() come gli altri link nav
  - [x] Logo unificato esteticamente a dashboard.php: colore #c2652a,
    font-family Eb Garamond, rimosso simbolo ☉ — dimensione lasciata
    invariata su richiesta esplicita. Il committente aggiungerà manualmente
    il link Google Fonts Eb Garamond nell'<head> delle 12 pagine (scelta
    esplicita: <link> nel body funzionava ma giudicato "poco professionale")
- [x] Fase 7 — Verifica finale completata (23-08-2026): php -l pulito su
  tutti e 8 i file PHP toccati sul branch, node --check pulito su app.js,
  git status pulito, git diff --check pulito (un solo trailing whitespace
  cosmetico in un file di documentazione, lasciato di proposito)

## ROADMAP COMPLETATA (23-08-2026)

Tutte le 7 fasi concluse. `dashboard.php` è ora la pagina di destinazione
raggiungibile da: click sul nome soggetto in `index.php`, e dal logo su
tutte le 12 pagine che includono `header_nav.php`. Resta da fare, a
discrezione del committente e senza fretta: aggiungere il link Google
Fonts Eb Garamond nell'`<head>` delle 12 pagine (dettagli in Fase 6).

---

## Decisioni future su header/navbar (2026-08-22, NON ora)

Da fare dopo il collegamento delle pagine (Fase 4), su richiesta esplicita:

- Rimuovere la voce testuale "👤 Nome Astrologo" dalla nav, sostituendola
  con il piccolo avatar/foto già presente in alto a destra (placeholder
  attuale, "potrà servire in futuro per una foto dell'astrologo").
- L'icona ingranaggio (attualmente placeholder) deve aprire un **modale**
  che permette all'astrologo di cambiare la password — a quel punto la
  voce testuale "🔑 Password" nella nav va rimossa (funzione assorbita
  dal modale).
- L'icona campanella va sostituita con un'icona "porta aperta" (logout),
  con tooltip "ESCI" al passaggio del mouse — a quel punto la voce
  testuale "Esci" nella nav va rimossa (funzione assorbita dall'icona).

## Rischi noti / cautele

- `header_nav.php` e `js/app.js` sono condivisi da più pagine — ogni
  modifica lì (Fase 6) va fatta con marker univoci e verifica su
  tutte le pagine che li importano (regola già in `docs/FREEZE.md`).
- Branch potenzialmente divergenti (vedi `docs/FREEZE.md`): verificare
  sempre lo stato reale di `new_dashboard` prima di ogni step, non
  assumere che coincida con altri branch controllati in fase di analisi.

## Nota (23-08-2026) — Rimossa voce "Password" dalla nav, sostituita dal modale Impostazioni

Prima della rimozione, il link nella navbar era: <a class="text-on-surface-variant hover:text-on-surface transition-colors hover:bg-surface-container font-title-md text-[16px] leading-6 font-medium" href="cambia_password.php">🔑 Password</a> Puntava direttamente a `cambia_password.php` (pagina dedicata esistente). Ora il
cambio password avviene dal modale "Impostazioni" (icona ingranaggio in alto a
destra), che in una fase successiva verra' collegato funzionalmente riusando
`Auth::cambiaPropriaPassword()` (stessa logica gia' usata da
`cambia_password.php`) tramite un endpoint AJAX dedicato, non ancora creato.

## Modale Impostazioni completato (23-08-2026)

Sostituita la voce nav "Password" con un modale "Impostazioni" aperto
dall'icona ingranaggio in header, con due sezioni:

- Cambia Password: form (attuale/nuova/conferma), endpoint dedicato
  www/api/cambia_password_api.php che riusa Auth::cambiaPropriaPassword()
  (stessa logica di cambia_password.php), CSRF via sessione
  $_SESSION['dash_settings_csrf'], testato funzionante end-to-end.
- Foto Profilo: riservata al piano Supporter (nuovo feature-code
  'foto_profilo' in Auth::hasFeature(), stesso meccanismo gia' usato
  per locality_search/grid_search/astri_in_cuspide). Colonna
  utenti.foto_profilo aggiunta con sql/009_foto_profilo_utenti.sql.
  Upload validato (MIME reale via finfo, max 2MB, solo jpg/png/webp)
  in www/api/foto_profilo_api.php, salvato in www/uploads/avatar/
  (cartella persistente ma esclusa da git, permessi corretti per
  l'utente www-data del container). L'avatar nell'header mostra la
  foto salvata al posto del cerchietto vuoto, aggiornato subito dopo
  l'upload senza ricaricare la pagina. Testato funzionante end-to-end.

Rifiniture layout header nella stessa sessione: respiro a sinistra del
logo e a destra delle icone, avatar ingrandito da 32px a 40px.
