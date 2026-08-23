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
- [ ] Fase 5 — Restyle dropdown Help con palette Sahara (solo colori/font, nessuna modifica di contenuto/struttura)
- [ ] Fase 6 — SOLO a fine lavoro, su conferma esplicita: collegare il logo (da ogni pagina del sito) e il nome soggetto in `index.php` a `dashboard.php`
- [ ] Fase 7 — Verifica finale (php -l, git diff, restart container, test reale) e commit solo su conferma esplicita

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
