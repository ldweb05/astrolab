# Roadmap: Manuale d'Uso ASTROLAB (v2)

**Creata:** 24-10-2026
**Stato:** Fase 0 in corso
**Sostituisce:** la struttura di `docs/roadmaps/roadmap_aiuto.md`, che resta come archivio
storico della prima versione del menu Help (Fasi 1-5, pagine `help_*.php`).

---

## 1. Obiettivo

Trasformare l'attuale menu Help, oggi una guida ai pulsanti, in un **manuale d'uso vero**
per astrologi professionisti:

- **lato informatico: quasi elementare.** Nessun termine tecnico (niente "engine", "API",
  "query", "VAL", "IATA" senza spiegazione). Istruzioni del tipo "clicca", "scegli",
  "aspetta", con i nomi dei pulsanti esattamente come appaiono a schermo;
- **lato astrologico: professionale e approfondito.** Case, orbi, cuspidi, domificazione,
  gerarchie delle condizioni, veti e regole citate per numero (34 regole, metodo Discepolo).

---

## 2. Situazione di partenza (analisi del 24-10-2026, `main` @ 148b499)

Esistono 11 voci nel menu Help (`34_regole.html` + 10 pagine `help_*.php`, ~880 righe).

Limiti riscontrati:

1. Descrivono i comandi ma non spiegano cosa fa l'astrologo né perché.
2. Gergo da sviluppatore ("RSM v3", "Theme Engine", "Narrative Engine", "DSS", "VAL").
3. Parte astrologica quasi assente: condizioni (Decima, Lavoro, Amore, Salute, Denaro,
   Denaro Low, Casa), gerarchia a livelli, stelline, veti, regole a scarto automatico
   (4, 5, 31, 32, 34), Regola 14 mai spiegati.
4. Funzioni non documentate: Transiti, Dashboard, Ricerca RL (solo citata), accesso del
   soggetto con il CODICE, sessioni salvate in Ricerca RSM/RL.
5. `help_faq.php` ancora segnaposto.
6. Ordine delle voci = ordine storico di sviluppo, non percorso di lavoro dell'astrologo.
7. Stile CSS duplicato in ogni pagina help.

---

## 3. Struttura del nuovo manuale

Organizzata sul percorso reale di lavoro dell'astrologo.

### Parte A — Primi passi
| # | Capitolo | Pagine app di riferimento |
|---|---|---|
| 1 | Cos'è AstroLab e per chi è | — |
| 2 | Entrare, account, piani Free e Supporter | `login.php`, `registrazione.php`, `cambia_password.php` |
| 3 | I soggetti: inserimento, CODICE automatico, soggetto attivo | `index.php` |
| 4 | Dare accesso a un soggetto | `index.php`, `area_soggetto.php` |
| 5 | La Dashboard | `dashboard.php` |

### Parte B — Le carte
| # | Capitolo | Pagine app di riferimento |
|---|---|---|
| 6 | Tema Natale | `tema.php` |
| 7 | Rivoluzione Solare | `rs.php` |
| 8 | Rivoluzione Lunare | `rl.php` |
| 9 | Rilocazione (con Venere/Giove sugli angoli) | `rilocazione.php` |
| 10 | Transiti Planetari | `transiti.php` |

### Parte C — Cercare il luogo giusto (cuore del manuale)
| # | Capitolo | Pagine app di riferimento |
|---|---|---|
| 11 | Come AstroLab giudica un luogo: condizioni, livelli, stelline, veti, regole a scarto | `RuleEngine.php`, `StellineV2Calculator.php` (solo verifica) |
| 12 | Ricerca RSM passo passo | `ricerca.php` |
| 13 | Le tre modalità: Standard, Longitudine Cuspidi, Astri nelle Case | `ricerca.php` |
| 14 | I filtri: aree, aeroporti, orbe, fuso orario | `ricerca.php` |
| 15 | Ricerca RL mensile | `ricerca_rl.php` |
| 16 | Confrontare le RSM e salvare le sessioni | `compare_rs.php`, `compare_ril.php` |

### Parte D — Dopo la scelta
| # | Capitolo | Pagine app di riferimento |
|---|---|---|
| 17 | Relazione Annuale | `rs.php` |
| 18 | Stampa e PDF | `stampa.php` |
| 19 | Diario RSM | `diario.php` |

### Parte E — Consultazione
| # | Capitolo | Pagine app di riferimento |
|---|---|---|
| 20 | Le 34 Regole, con nota su come l'app applica ciascuna | `34_regole.html` |
| 21 | Legenda: simboli, colori della ruota, icone | — |
| 22 | Glossario | — |
| 23 | Convertitore | `help_convertitore.php` |
| 24 | Domande frequenti e limiti dei piani | — |

Le pagine admin (`admin_utenti.php`, `admin_accessi.php`) restano fuori dal manuale per
astrologi.

---

## 4. Modello fisso di ogni capitolo

1. **A cosa serve** — due righe.
2. **Prima di iniziare** — cosa deve esserci già (es. un soggetto attivo).
3. **Passo passo** — passaggi numerati, nomi dei pulsanti identici allo schermo.
4. **Come leggere il risultato.**
5. **📐 Approfondimento astrologico** — riquadro separato, registro professionale.
6. **⚠️ Attenzione** — limiti, tempi lunghi, differenze volute rispetto ad Aladino.
7. **💎 Piano** — cosa è Free, cosa è Supporter.
8. **Vedi anche** — collegamenti ad altri capitoli.

### Regole di scrittura
- Ogni affermazione su comportamento, limiti o regole va **verificata nel codice** prima
  di essere scritta.
- I due registri (istruzioni semplici / approfondimento astrologico) restano visivamente
  distinti.
- Date in formato DD-MM-YYYY.
- Nessun riferimento pubblico a MyAstral.org; Aladino citato solo dove serve a spiegare
  una differenza voluta.
- Metodo per ogni capitolo: bozza in chat → correzione con il committente → inserimento
  via patch Python → verifica (`php -l`, `git diff`, riavvio container) → commit su conferma.

---

## 5. Fasi

- [~] **Fase 0** — Questa roadmap e decisioni aperte (§6). Solo documentazione.
- [ ] **Fase 1** — Scheletro: nuovo `www/css/help.css` dedicato, template di pagina,
      indice e navigazione "← precedente / successivo →", nuovo menu Help in
      `header_nav.php`. Nessun file condiviso esistente toccato oltre a `header_nav.php`.
- [ ] **Fase 2** — Capitolo pilota 11 + 12, per fissare tono e profondità.
- [ ] **Fase 3** — Parti A e B (capitoli 1-10).
- [ ] **Fase 4** — Parti C (13-16), D (17-19) ed E (20-24), con glossario e FAQ reali.
- [ ] **Fase 5** — (Facoltativa, da valutare a parte) link "?" contestuale in ogni pagina
      operativa verso il capitolo corrispondente.
- [ ] **Fase 6** — Revisione incrociata testo/codice, test UX, versione stampabile PDF,
      aggiornamento `START_HERE.md` e `HANDOVER_OPERATIVO_astrolab.md`.

---

## 6. Decisioni aperte

| # | Domanda | Proposta |
|---|---|---|
| D1 | Riscrivere le pagine `help_*.php` esistenti o affiancarne di nuove? | Riscrivere e rinumerare, recuperando i contenuti validi |
| D2 | Screenshot nel manuale? | Da decidere (utili ma vanno rifatti a ogni modifica grafica) |
| D3 | Mini-capitolo per i soggetti che accedono con il CODICE? | Da decidere |
| D4 | Branch di lavoro | `main` con tag di ripristino prima di ogni intervento |

---

## 7. Registro avanzamento

| Data | Fase | Cosa |
|---|---|---|
| 24-10-2026 | 0 | Creata la roadmap |
