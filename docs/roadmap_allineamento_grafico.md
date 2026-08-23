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
