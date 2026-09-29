# ROADMAP — Diario RSM e Schede Località condivise

**Creata:** 29-09-2026
**Branch:** `main`
**Stato:** Fase 0 (design) — decisioni D1-D19 concordate con il committente il 29-09-2026; Fasi 1-5 da eseguire
una alla volta, ciascuna su conferma esplicita.
**Prerequisito:** `docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md` (blocchi A e B: CODICE
automatico e login dei soggetti). Le Fasi 1-5 di questa roadmap partono dopo quei blocchi.

---

## 1. Obiettivo

Permettere ai soggetti (con accesso tramite il proprio CODICE) e agli astrologi di salvare i
viaggi fatti per una RSM (località, periodo, albergo, costi, trasporti, note) e di
condividere con gli altri astrologi e soggetti le sole informazioni di viaggio, così che chi deve raggiungere la stessa
località (es. Longyearbyen, Svalbard) trovi già come arrivarci, dove alloggiare,
quanto spendere e i consigli pratici di chi c'è già stato.

## 2. Principio di design: due livelli separati

| Livello | Tabella | Chi lo vede | Contenuto |
|---|---|---|---|
| Viaggio RSM (privato) | `viaggi_rsm` | solo l'utente che lo ha scritto | collegamento alla sessione RS, date esatte, albergo, costi esatti, trasporti, note personali |
| Contributo località (condiviso) | `contributi_localita` + `contributi_tratte` | tutti gli utenti loggati | solo dati di viaggio: tratte, alloggio, spesa indicativa, info pratiche, contatti pubblici, consigli |

**Regola inderogabile:** un contributo condiviso non contiene e non espone mai dati del
soggetto (nome, dati di nascita, tema, condizione, stelline, sessione RS). Il collegamento
alla sessione RS esiste solo nel livello privato.

## 3. Decisioni del committente (29-09-2026)

- **D1 — Modello di condivisione:** contributi separati per autore (stile recensioni), non
  una scheda unica modificabile da tutti. Ogni autore modifica ed elimina solo i propri.
- **D2 — Autore:** visibile con lo `username`.
- **D3 — Costi:** condivisibili come *indicazione di spesa* (valuta e riferimento, es. "per
  notte"). I costi esatti restano nel livello privato.
- **D4 — Privacy della data:** nel contributo pubblico solo **anno e mese** del viaggio,
  mai il giorno. Motivo: una RSM avviene nei giorni del compleanno, quindi username + data
  esatta rivelerebbero il compleanno dell'autore. Le date esatte restano private.
- **D5 — Data di aggiornamento visibile** su ogni contributo, per capire quanto sono recenti
  voli e prezzi.
- **D6 — Trasporti a tratte:** ogni contributo ha N tratte ordinate (mezzo, da/a, compagnia,
  durata indicativa, costo indicativo, note).
- **D7 — Alloggio strutturato:** nome, tipo, sito ufficiale, fascia di prezzo, giudizio.
- **D8 — Info pratiche:** documenti/visti, clima nel periodo, lingua e valuta, connettività,
  particolarità locali (es. a Svalbard obbligo di guida armata fuori dall'abitato).
- **D9 — Solo contatti pubblici:** strutture, agenzie, guide professionali. Spunta
  obbligatoria "non inserisco dati personali di privati" al salvataggio.
- **D10 — Moderazione minima:** l'admin può nascondere un contributo (`visibile = false`).

## 4. Modello dati proposto (Fase 1)

Migrazione: `sql/013_diario_rsm.sql` (010-012 sono del blocco A-B), in transazione `BEGIN; ... COMMIT;` come le
precedenti. Nessuna modifica a `sessioni_rs` né ad altre tabelle esistenti.

### `viaggi_rsm` (privato)
`id`, `utente_id` (FK `utenti`, nullable, ON DELETE CASCADE), `soggetto_id` (FK `soggetti`,
nullable, ON DELETE CASCADE) — esattamente uno dei due valorizzato (D14), `sessione_rs_id`
(FK `sessioni_rs`, nullable, ON DELETE SET NULL, impostabile solo dall'astrologo — D16), `luogo`, `iso_nazione` (D18), `latitudine`, `longitudine`,
`data_arrivo`, `data_partenza`, `albergo`, `costo_alloggio`, `costo_trasporti`, `valuta`
(default `EUR`), `trasporti` (testo), `note_private`, `creato_il`, `aggiornato_il`.

### `contributi_localita` (condiviso)
`id`, `utente_id` (FK `utenti`, nullable, ON DELETE SET NULL — vedi D12), `soggetto_id` (FK
`soggetti`, nullable, ON DELETE SET NULL — vedi D14), `luogo`, `iso_nazione` (D18), `latitudine`, `longitudine`,
`anno_viaggio`, `mese_viaggio` (1-12, nullable), `alloggio_nome`, `alloggio_tipo`
(hotel / b&b / appartamento / guesthouse / campeggio / altro), `alloggio_sito`,
`alloggio_fascia_prezzo`, `alloggio_giudizio`, `costo_alloggio_indicativo`,
`costo_alloggio_riferimento` (es. "per notte"), `valuta`, `info_documenti`, `info_clima`,
`info_lingua_valuta`, `info_connettivita`, `info_particolarita`, `contatti_utili`,
`consigli`, `dichiarazione_contatti` (boolean, deve essere true), `visibile` (default
true), `creato_il`, `aggiornato_il`.

### `contributi_tratte` (condiviso, figlia)
`id`, `contributo_id` (FK `contributi_localita`, ON DELETE CASCADE), `ordine`, `mezzo`
(aereo / nave / treno / bus / auto / altro), `da_luogo`, `a_luogo`, `compagnia`,
`durata_indicativa`, `costo_indicativo`, `valuta`, `note`.

### `nazioni` (riferimento, D18)
`iso` (codice ISO 3166-1 alpha-2, chiave), `nome_it` (nome italiano da CLDR, es. "Polinesia
Francese"), `nome_en` (nome inglese, come in `localita.nazione`), `alias` (altri nomi
cercabili, es. "Malvine", "Malvinas"). Popolata dalla migrazione con i dati CLDR (263 voci).

### `nazioni_appartenenza` (riferimento, D19)
`iso_territorio`, `iso_nazione` (chiave composta). Un territorio con codice ISO proprio
compare anche sotto la nazione indicata: SJ → NO (Svalbard), GL e FO → DK, PF e NC → FR,
FK → GB e AR (vedi D19), ecc. Elenco completo nella migrazione, da rivedere col committente.

## 5. Decisioni chiuse in Fase 0 (29-09-2026)

- **D11 — Raggruppamento per località (ex A1):** per **nome + nazione**. Il confronto
  avviene su valori normalizzati (minuscolo, spazi iniziali/finali e doppi rimossi). Per
  limitare i doppioni ("Longyearbyen" / "Longyearbyen Airport"), il form del contributo
  propone con autocompletamento i nomi già presenti per la stessa nazione (Fase 2-3).
- **D12 — Utente eliminato (ex A2):** i suoi contributi **restano**. `utente_id` diventa
  NULL (ON DELETE SET NULL) e l'autore viene mostrato come "utente non più registrato".
  I viaggi privati invece vengono cancellati con l'utente (CASCADE). Vedi anche §8 (GDPR).
- **D13 — Piani (ex A3):** nessuna differenza tra `free` e `supporter`, nessuna voce in
  `piano_limiti`. Resta solo il limite anti-abuso tecnico del §8.
- **D14 — Autori astrologi e soggetti:** viaggi e contributi possono essere scritti da un
  astrologo (`utente_id`) o da un soggetto loggato con il proprio CODICE (`soggetto_id`),
  con vincolo `CHECK` che non siano mai valorizzati entrambi. Sui contributi l'autore è
  mostrato con lo username (astrologo) o con il CODICE (soggetto); se l'autore è stato
  eliminato, "utente non più registrato" (D12).
- **D15 — Visibilità dei viaggi privati di un soggetto:** solo il soggetto stesso e il suo
  astrologo (`soggetti.utente_id`). Nessun altro utente o soggetto.
- **D16 — Collegamento alla sessione RS:** solo l'astrologo può collegare un viaggio a una
  propria sessione RS salvata; il soggetto non vede mai sessioni, temi o dati di nascita
  (vincolo di `ROADMAP_CODICE_LOGIN_SOGGETTI.md`).
- **D17 — Motore di ricerca (29-09-2026):** i contributi condivisi si consultano con una
  ricerca libera per nazione o località (es. "Norvegia", "Australia", "Tokyo", "Svalbard"),
  tollerante a maiuscole, accenti e piccoli errori (estensione `pg_trgm`, già presente). Una
  nazione porta all'elenco delle sue località con contributi, ciascuna con il numero di
  contributi; una località porta alla sua scheda. Senza testo si vede l'elenco delle nazioni
  con contributi. Accessibile a tutti gli utenti loggati, astrologi e soggetti.
- **D18 — Nazione per nome, mai per codice (29-09-2026):** nel DB la nazione è salvata come
  codice ISO (`iso_nazione`), ma nell'interfaccia compare **sempre e solo il nome italiano**
  (tabella `nazioni`, nomi CLDR). La ricerca accetta nome italiano, nome inglese e alias.
  Motivo: il committente non conosce i codici (es. PF = Polinesia Francese, CY = Cipro), e un
  testo libero renderebbe la ricerca inaffidabile ("Norvegia" / "Norway" / "NO").
- **D19 — Territori con codice proprio (29-09-2026):** un territorio con codice ISO proprio
  compare anche sotto la nazione di appartenenza (tabella `nazioni_appartenenza`), così
  cercando "Norvegia" si trova Longyearbyen, che nella tabella `localita` è classificata SJ
  (Svalbard e Jan Mayen). Lo stesso per Groenlandia (Danimarca), Polinesia Francese (Francia),
  ecc. Le Isole Falkland compaiono come "Isole Falkland (Malvine)", come nella denominazione
  usata dall'ONU, sono cercabili anche come "Malvine" / "Malvinas" e risultano sia sotto il
  Regno Unito (amministrazione) sia sotto l'Argentina (rivendicazione): scelta di sola
  navigazione, senza alcuna presa di posizione. Territori contesi senza un'appartenenza
  univoca non vengono collegati ad alcuna nazione.

## 6. Fasi

| Fase | Contenuto | Stato |
|---|---|---|
| 0 | Questa roadmap | Completata (aggiornata con D14-D16) |
| 1 | Migrazione `sql/013_diario_rsm.sql` + applicazione sul DB del Pi | Da fare |
| 2 | API `www/api/diario_rsm_api.php` (CRUD viaggi privati e contributi) | Da fare |
| 3 | Pagina viaggi privati (astrologi e soggetti) + collegamento da "Sessioni RS salvate" in `rs.php` | Da fare |
| 4 | Motore di ricerca (D17) e schede località condivise (lettura contributi e tratte) | Da fare |
| 5 | Documentazione: HANDOVER, START_HERE, ROADMAP generale | Da fare |

## 7. Vincoli di sicurezza (da `docs/CHECKLIST_SICUREZZA_SVILUPPO.md` e specifici)

- Login obbligatorio (astrologo o soggetto) su tutte le pagine e le API nuove; ogni scrittura verifica lato server
  che `utente_id` coincida con l'utente in sessione (mai fidarsi di un id inviato dal client).
- Solo query parametrizzate (PDO prepared statements).
- Contenuto scritto da utenti e letto da altri: escaping in output (`htmlspecialchars` in
  PHP, `textContent` in JS, mai `innerHTML` con dati utente).
- `alloggio_sito` accettato solo con schema `http://` o `https://` (blocco `javascript:`).
- Lunghezza massima validata lato server su ogni campo testo.
- Token CSRF obbligatorio su ogni scrittura (creazione, modifica, eliminazione) fin dalla
  Fase 2. `api/sessioni_api.php` oggi non ne usa; si riusa il pattern di
  `includes/header_nav.php` (`dash_settings_csrf`) senza modificare le API esistenti.
- Ogni utente può eliminare in qualunque momento i propri contributi.
- Nessun file di test in `www/`: eventuali script di prova in `tests/`.
- Nessun `git add .` / `git add -A`.

## 8. Predisposizione per la VPS pubblica

Oggi ASTROLAB gira sul Raspberry Pi raggiungibile solo in LAN o tramite tunnel WireGuard.
La feature viene però progettata **fin da subito** come se fosse esposta su VPS con
dominio pubblico, così che al trasferimento non serva riscrivere nulla. Nessuna di queste
misure deve dare per scontata la protezione della VPN.

**Già nel codice della feature (Fasi 1-4):**
- Limite anti-abuso sulle scritture, applicato lato applicazione tramite conteggio su DB
  (es. massimo N contributi per utente al giorno, massimo 15 tratte per contributo;
  valori esatti da fissare in Fase 2).
- Link esterni (`alloggio_sito`, contatti) resi con `rel="noopener noreferrer nofollow ugc"`
  e `target="_blank"`: nessun beneficio SEO per siti inseriti da terzi, nessun accesso della
  pagina esterna alla finestra di ASTROLAB.
- Messaggi di errore delle API generici verso il client (mai stack trace o SQL); dettagli
  solo nel log del server.
- Nuove pagine senza nuovi handler JavaScript inline (`onclick=` ecc.), per non ostacolare
  una futura Content-Security-Policy. Le pagine esistenti non vengono toccate per questo.
- Pagine e API del diario dietro login e con `<meta name="robots" content="noindex">`: le
  schede località non sono indicizzabili finché non si decide diversamente.

**Da fare al trasferimento (insieme a `docs/roadmaps/ROADMAP_SEO.md` Fase 6 e al §5 di
`docs/CHECKLIST_SICUREZZA_SVILUPPO.md`):**
- HTTPS obbligatorio; cookie di sessione `Secure`, `HttpOnly`, `SameSite=Lax` o `Strict`.
- `display_errors=Off` in produzione; header di sicurezza (CSP, `X-Content-Type-Options`,
  `Referrer-Policy`) a livello di Apache o reverse proxy.
- Nuove tabelle incluse nei backup del DB della VPS.
- IP reale del client dietro il reverse proxy per tutti i limiti per IP del login (astrologi e
  soggetti): vedi `docs/roadmaps/ROADMAP.md`, sezione "PUNTO APERTO — Limite per IP del login".
- **GDPR / informativa privacy:** dichiarare che lo username è visibile agli altri utenti
  sui contributi e che, alla cancellazione dell'account, i contributi restano senza autore
  (D12). Punto di attenzione: il testo libero di un contributo potrebbe contenere dati
  personali; da valutare se, alla cancellazione dell'account, offrire all'utente la scelta
  di eliminare anche i contributi. Decisione rimandata alle scelte legali già aperte in
  `ROADMAP_SEO.md`.
- Eventuale apertura delle schede località ai visitatori non registrati o ai motori di
  ricerca: solo con decisione esplicita del committente, perché cambia la superficie
  d'attacco e gli obblighi legali.

## 9. Idee future (escluse per ora dal committente)

- **"Pubblica dal mio viaggio":** pulsante nel viaggio privato che precompila un contributo
  pubblico con i soli campi generici.
- **Segnalazione di un contributo** (inaccurato / inappropriato) da parte degli utenti.
- Indicatore "info di viaggio disponibili" sulle località nei risultati di ricerca RSM.
