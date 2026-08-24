# Roadmap — Allineamento Grafico (post nuova Dashboard)

## Obiettivo
Allineare SOLO graficamente (colori, font, sfondi) tutte le pagine
esistenti alla palette e allo stile approvati per `dashboard.php`.
Il funzionamento non va toccato. `dashboard.php` stessa resta
intoccata (nessuna migrazione a style.css: troppo rischioso, vedi
decisione sotto).

Branch di lavoro: `new_dashboard`.

---

## Decisioni prese (23-08-2026)

1. **Approccio**: `dashboard.php` (Tailwind via CDN) resta invariata.
   Le altre pagine (CSS classico in `style.css` + inline in alcuni
   file) vengono riallineate ai VALORI del design system Sahara,
   non migrate a Tailwind.
2. **Colore "attivo" navbar `#12A0D7`**: resta invariato ovunque
   compare (nav a.active, .nav-dropdown-trigger.active) — decisione
   esplicita dell'utente, non allinearlo al sienna.
3. **Sfondo pagina**: `#F2EDE4` (+ watermark zodiacale) sostituito
   con **`#FFFFFF`** puro (non con il crema Sahara `#faf5ee`, ritenuto
   troppo simile al bianco da percepire come cambiamento reale).
   Il watermark zodiacale SVG (oggi `#E4D9C0`) va MANTENUTO ma
   ricolorato in tono con il nuovo sfondo bianco (fase C, da
   completare).
4. **Font**: NESSUNA introduzione di Manrope/Eb Garamond nel corpo
   del sito. Font ufficiale ovunque resta Verdana (rimuovendo
   Montserrat da style.css) o monospace dove già usato (badge,
   numeri). Eb Garamond resta SOLO sul logo "AstroLab" (invariato,
   già così).
5. **Logo header (`.header-logo`)**: dimensione portata a 36px
   bold su richiesta esplicita dell'utente (deviazione dalla nota
   Fase 6 della roadmap dashboard, che diceva dimensione invariata
   — qui è stato deciso diversamente, di proposito).

---

## Mappatura problema → causa (utile per non ripartire da zero)

- Header/nav principale: CSS in `www/css/style.css` (righe iniziali,
  header/nav/.nav-toggle) + fascia `.header-user`/`.header-link`
  (righe ~2475+)
- **Dropdown "Ricerche"/"Help" (versione desktop)**: NON sono in
  style.css — vivono in un `<style>` inline dentro
  `www/includes/header_nav.php` (righe ~43-137). Facile perdersi
  questo pezzo se si guarda solo style.css.
- Colore accento primario storico dell'app: `#2C3E6B` (navy) —
  usato ovunque come "colore di marca" (titoli, bordi, bottoni,
  badge), non solo nell'header. 75 occorrenze totali nel progetto,
  56 solo in `style.css`. Target di allineamento: sienna `#c2652a`
  (Fase E, la più estesa).
- Sfondo `#F2EDE4` ripetuto (ridondante rispetto a style.css) anche
  in: `registrazione.php`, `verifica-email.php`, `rilocazione.php`,
  `tema.php` (box interno), e le 8 pagine `help_*.php` (blocco
  `<style>` identico in tutte e 8, un solo pattern da applicare
  8 volte). `login.php` fa eccezione: usa `#FFFFFF` (bg F2EDE4
  commentato), quirk preesistente non ancora toccato.
- `api/stampa_pdf_api.php` e file in `tests/` contengono anch'essi
  `#2C3E6B` ma generano PDF/test, non pagine web — proposto come
  FUORI SCOPE per questo lavoro (da confermare se necessario anche lì).

---

## Fasi

- [x] Fase A — Variabili CSS `:root` con i token Sahara aggiunte in
  cima a `www/css/style.css` (nessun effetto visivo, solo base per
  le fasi successive)
- [x] Fase B — Header/nav principale (style.css) allineato: sfondo,
  bordo navy + ombra "rialzata", testi, hover, toggle mobile, badge
  soggetto attivo
- [x] Fase B-fix — Dropdown "Ricerche"/"Help" (header_nav.php,
  inline `<style>`) allineati allo stesso schema
- [x] Fase C (parziale) — Sfondo pagina `#F2EDE4` → `#FFFFFF` in
  `style.css` (body) e in `header`; box esterno grafico in
  `tema.php` (`.tema-box-full`) reso trasparente
- [ ] Fase C (resto) — Rimuovere/allineare i background
  `#F2EDE4` ridondanti in: `registrazione.php`, `verifica-email.php`,
  `rilocazione.php`, le 8 pagine `help_*.php`; ricolorare il
  watermark zodiacale SVG (oggi `#E4D9C0`) in tono con `#FFFFFF`;
  decidere se toccare anche il quirk di `login.php`
- [ ] Fase D — Rimuovere Montserrat da `style.css` (font body diventa
  Verdana/monospace); verificare che nessuna regola Eb Garamond sia
  stata introdotta fuori dal logo
- [ ] Fase E — Sostituire il colore accento `#2C3E6B` con `#c2652a`
  (sienna) in tutta l'app: 56 selettori in `style.css` (a gruppi
  tematici: bottoni, titoli, bordi/badge) + le 8 pagine help
  (pattern identico) + le 7 pagine individuali rimanenti
  (`login.php`, `registrazione.php`, `rilocazione.php`, `stampa.php`,
  `ricerca.php`, `ricerca_rl.php`, `admin_utenti.php`,
  `cambia_password.php`)

Commit di riferimento per Fase B/B-fix/C-parziale: `7412b2e` su
`new_dashboard`.

## Rischi noti

- `style.css` e `header_nav.php` sono condivisi da molte/tutte le
  pagine — ogni modifica futura va verificata su un campione ampio
  di pagine, non solo su quella in cui si nota il problema.
- Le 8 pagine help hanno blocchi `<style>` identici: comodo per
  applicare un pattern in 8 colpi, ma rischioso se si dimentica una
  delle 8 (verificare sempre il conteggio grep prima di dare per
  completato un giro).

---

## Aggiornamento 23-08-2026 (sera) — index.php: navbar minimale + fix link dashboard.php

### Decisioni prese
6. **Logo `index.php`**: allineato allo stesso schema delle altre pagine
   — rimosso il simbolo `☉`, ora `<a class="header-logo">AstroLab</a>`
   dentro `<h1>`, con link a `dashboard.php` (preservando `?id=` se
   c'è un soggetto attivo, stessa convenzione di `_navUrl()` in
   `header_nav.php`, qui reimplementata inline perché `index.php`
   non include quel file).
7. **Navbar `index.php` ridotta al minimo** (decisione esplicita
   dell'utente, DEVIAZIONE dal principio "solo grafica" — qui si
   tocca anche il funzionamento): rimossi i link diretti Soggetti
   (self-link), Tema Natale, Rivoluzione Solare, Ricerca Località,
   Utenti (admin), il link separato "🔑 Password" e il badge
   "⭐ soggetto attivo". Restano SOLO: nome astrologo, icona
   ingranaggio (modale Impostazioni), avatar, link "Esci" (mantenuto
   su richiesta esplicita per poter fare logout anche da questa
   pagina). Il soggetto attivo resta visibile solo nelle altre
   pagine; da `index.php` ci si arriva a `dashboard.php` cliccando
   il nome del soggetto (comportamento preesistente, invariato).
8. **Modale Impostazioni in `index.php`**: `dashboard.php` usa
   Tailwind (CDN) per bottone ingranaggio/modale/avatar — non
   riusabile direttamente in `index.php`, che resta su CSS classico
   (stessa decisione presa per non migrare `dashboard.php`).
   Ricostruito l'equivalente in CSS classico (nuove classi in
   `style.css`: `.header-icon-btn`, `.header-avatar`,
   `.idx-modal-*`), riusando 1:1 la stessa logica JS (fetch) e le
   stesse API backend già esistenti e non toccate:
   `api/foto_profilo_api.php`, `api/cambia_password_api.php`.
   Icona ingranaggio: emoji ⚙️ invece del font Material Symbols
   Outlined usato in dashboard.php, per non introdurre una nuova
   dipendenza esterna in una pagina che non l'aveva.
9. **`dashboard.php` — fix voce navbar**: la voce "Utenti"
   (`href="#"`, non collegata a nulla) rinominata in "Soggetti" e
   collegata a `index.php` — è la pagina reale di gestione soggetti,
   l'etichetta "Utenti" era fuorviante/residua.

### File toccati
- `www/index.php`: variabili PHP modale (`$idxHasFotoProfilo`,
  `$idxSettingsCsrf`, `$idxFotoProfilo`), header semplificato,
  markup + JS modale Impostazioni
- `www/css/style.css`: nuove classi `.header-icon-btn`,
  `.header-avatar`, `.idx-modal-overlay`, `.idx-modal-box` e
  derivate
- `www/dashboard.php`: fix link "Utenti"→"Soggetti"

### Stato Fasi (aggiornato)
- [x] Fase F (nuova) — `index.php`: navbar minimale + modale
  Impostazioni in CSS classico + fix link "Soggetti" in
  `dashboard.php`

Restano da fare, invariate rispetto a stamattina: Fase C (resto),
Fase D, Fase E — vedi sopra.

---

## Aggiornamento 23-08-2026 (sera, 2) — login.php: toggle occhiolino password

### Decisione presa
10. **Campo password `login.php`**: aggiunto bottone toggle
    mostra/nascondi password (emoji 👁️/🙈, coerente con la scelta
    già presa di non introdurre font-icona esterni). Markup: input
    avvolto in `.password-field-wrap` (position relative), bottone
    assoluto a destra dentro il campo. JS vanilla, nessuna
    dipendenza esterna, alterna `input.type` tra `password`/`text`.

### File toccati
- `www/login.php`: markup + CSS (`.password-field-wrap`,
  `.password-toggle-btn`) + JS toggle

### Stato Fasi (aggiornato)
- [x] Fase G (nuova) — `login.php`: toggle occhiolino password

Restano da fare, invariate: Fase C (resto), Fase D, Fase E.

---

## Aggiornamento 24-08-2026 (notte) — rs.php: banner esclusione + opacità Bonus/Veti; header_nav.php: ingranaggio+modale+avatar su TUTTE le pagine

### Correzioni/decisioni prese
11. **`rs.php` — banner esclusione RS**: il div `#rs-filtro-esclusione`
    (striscia rosa "Questa RS non viene elencata...") era fuori da
    qualunque collassabile, sempre visibile. Spostato dentro
    `#collapse-body-bonus-veti`: ora è visibile solo quando il
    pannello "Bonus e Veti" è espanso. Nessuna modifica alla logica
    JS (`aggiornaBannerEsclusione()` cerca l'elemento per id, invariato).
12. **`.valutazione` (box "Bonus e Veti") — colore**: era
    `rgba(255,255,255,0.60)`, visivamente diverso dall'header di
    "Analisi Sensibilità Oraria" (`.sensib-header`, sfondo opaco
    `#F5F2EE`) perché quest'ultimo ha uno sfondo proprio mentre
    `.valutazione .collapse-toggle` no (eredita il contenitore).
    Allineato: `.valutazione` ora è `rgba(245,242,238,0.65)` — stesso
    colore base di Sensibilità Oraria, trasparenza fissata a 0.65
    come richiesto.
13. **IMPORTANTE — chiarimento di scope, per non ripetere l'errore**:
    la richiesta di ieri sera "elimina le voci navbar, solo
    ingranaggio+modale+avatar" riguardava ESPLICITAMENTE E SOLO
    `index.php` nel testo originale. Il resto delle pagine (via
    `header_nav.php`) NON era stato allineato di conseguenza, ed
    era un errore: l'aspettativa reale dell'utente era che TUTTE le
    pagine avessero già questo schema (ingranaggio+modale+avatar),
    dato che `dashboard.php` lo aveva da prima. Corretto stanotte:
    vedi punto 14. **Nota per il futuro**: quando un pattern viene
    implementato su una pagina "presa a riferimento", chiedere
    sempre esplicitamente se va esteso anche alle altre pagine
    condivise, non assumere che lo scope sia limitato alla pagina
    nominata nella richiesta.
14. **`header_nav.php` (navbar condivisa da 13 pagine)**: link
    "🔑 Password" sostituito con bottone ingranaggio + avatar +
    stesso modale Impostazioni (password + foto profilo), stesso
    schema di `dashboard.php` — qui però, a differenza di
    `index.php` ieri sera, si è usata l'icona reale **Material
    Symbols Outlined "settings"** (non l'emoji ⚙️), per fedeltà
    visiva a `dashboard.php` su richiesta esplicita. Font caricato
    via `@import` in cima al `<style>` già presente in
    `header_nav.php` (evita di editare l'`<head>` di ogni pagina
    singolarmente). Tab di navigazione (Soggetti/Tema Natale/
    Ricerche/Help) e badge "⭐ soggetto attivo" INVARIATI — tocca
    solo il blocco `.header-user` a destra. `index.php` NON toccata
    (resta con l'emoji ⚙️, implementazione di ieri sera, per scelta
    esplicita di non uniformare le due icone in questo giro).

### File toccati
- `www/rs.php`: banner esclusione spostato nel collassabile
- `www/css/style.css`: `.valutazione` opacità aggiornata; nuove
  classi `.settings-modal-*` (namespace separato da `.idx-modal-*`
  di index.php, stesso pattern visivo)
- `www/includes/header_nav.php`: variabili PHP modale, `@import`
  font Material Symbols, bottone ingranaggio+avatar, markup+JS
  modale Impostazioni

### Stato Fasi (aggiornato)
- [x] Fase H (nuova) — rs.php: banner esclusione nel collassabile +
  opacità Bonus/Veti allineata
- [x] Fase I (nuova) — header_nav.php: ingranaggio+modale+avatar
  estesi a tutte le pagine condivise (icona Material Symbols, non
  emoji come su index.php)

Restano da fare, invariate: Fase C (resto), Fase D, Fase E.

---

## Aggiornamento 24-08-2026 (notte, 2) — rs.php: spostamento bottone stampa + allineamento colore .card globale

### Decisioni prese
15. **`rs.php` — bottone "Stampa Rivoluzione Solare"**: spostato da
    un contenitore dedicato (`.page-title.page-title-compact`, subito
    dopo la fascia `.header-rs`) dentro `.rs-actions-row`, subito
    dopo "⏱️ Correzione tempo ed ora". Solo posizione: classe,
    `onclick`, testo e comportamento invariati. Contenitore
    precedente (rimasto vuoto dopo lo spostamento) rimosso.
16. **Colore box `.card` — allineamento GLOBALE**: la classe `.card`
    (contenitore generico usato in 10 file diversi: rs.php, rl.php,
    tema.php, index.php, stampa.php, admin_utenti.php, rilocazione.php,
    transiti.php, compare_rs.php, compare_ril.php) era
    `rgba(255,255,255,0.60)`. Allineata a `rgba(245,242,238,0.65)` —
    stesso colore/trasparenza già usato per `.valutazione` (Bonus e
    Veti). Decisione esplicita dell'utente: "l'immagine grafica deve
    essere unica in tutto il sito" — cambio intenzionalmente globale,
    non limitato al singolo box "Sessioni RS salvate" che aveva
    originato la richiesta.
17. **`#pannello-sensibilita` (Analisi Sensibilità Oraria)**:
    trasparenza anch'essa portata da 0.60 a 0.65, stesso colore base
    (bianco) invariato — ora tutti e tre i box (`.card`,
    `.valutazione`, `#pannello-sensibilita`) condividono la stessa
    trasparenza 0.65, con `.valutazione`/`.card` sul crema
    `rgb(245,242,238)` e `#pannello-sensibilita` sul bianco puro
    (la sua tinta effettiva arriva dall'header opaco `.sensib-header`
    `#F5F2EE` sovrapposto, non dal contenitore).

### File toccati
- `www/rs.php`: bottone stampa spostato
- `www/css/style.css`: `.card` e `#pannello-sensibilita` allineati

### Stato Fasi (aggiornato)
- [x] Fase J (nuova) — rs.php: bottone stampa riposizionato;
  colore `.card`/`#pannello-sensibilita` allineato globalmente a
  `.valutazione`

Restano da fare, invariate: Fase C (resto), Fase D, Fase E.

---

## Aggiornamento 24-08-2026 (notte, 3) — rl.php: spostamento bottone stampa + rimozione trasparenza box

### Decisioni prese
18. **`rl.php` — bottone "Stampa Rivoluzione Lunare"**: stessa
    operazione fatta ieri su `rs.php`. Spostato da un contenitore
    dedicato (`.page-title.page-title-compact`, dopo `.header-rl`)
    dentro `.rl-actions-row`, subito dopo "🌍 Mappa". Solo posizione:
    classe, `onclick`, testo e comportamento invariati. Contenitore
    vuoto rimosso.
19. **`.card`, `.valutazione`, `#pannello-sensibilita` — trasparenza
    rimossa**: modifica fatta direttamente dall'utente. I tre box
    passano da `rgba(..., 0.65)` a colore pieno opaco (stesso valore
    RGB, canale alpha rimosso): `.card`/`.valutazione` →
    `rgb(245,242,238)`, `#pannello-sensibilita` → `rgb(255,255,255)`.
    Riga originale con l'alpha lasciata commentata sopra ciascuna
    regola per riferimento.

### File toccati
- `www/rl.php`: bottone stampa spostato
- `www/css/style.css`: `.card`, `.valutazione`, `#pannello-sensibilita`
  portati a colore pieno (no trasparenza)

### Stato Fasi (aggiornato)
- [x] Fase K (nuova) — rl.php: bottone stampa riposizionato;
  box `.card`/`.valutazione`/`#pannello-sensibilita` portati a
  colore pieno (rimossa trasparenza)

Restano da fare, invariate: Fase C (resto), Fase D, Fase E.
