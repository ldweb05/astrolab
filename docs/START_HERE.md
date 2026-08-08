# START HERE

Benvenuto nel progetto **ASTROLAB**.

Questo documento costituisce il punto di ingresso ufficiale del progetto.

Il suo scopo è descrivere:

- lo stato reale dell'applicazione;
- l'architettura generale;
- le funzionalità consolidate;
- le regole permanenti di sviluppo;
- il punto esatto dal quale riprendere eventuali lavori futuri.

Questo documento deve sempre rappresentare lo stato corrente del
repository.

---

# Stato del progetto

**Nome progetto**

ASTROLAB

**Repository**

Repository unico.

**Branch stabile**

`master`

**Stato**

Stabile.

**Rule Engine**

Congelato.

**Knowledge Coverage**

100%

**Regressione**

Verde.

---

# Cos'è ASTROLAB

ASTROLAB è una piattaforma professionale dedicata
all'astrologia previsionale.

ASTROLAB è il progetto unico e definitivo del sistema.
La documentazione operativa, tecnica e progettuale deve fare riferimento
ad ASTROLAB come unico progetto.

L'applicazione comprende:

- Tema Natale;
- Rivoluzione Solare;
- Rivoluzione Lunare;
- Rilocazioni;
- Annual Report;
- Comparator;
- Decision Support System;
- Narrative Engine;
- Theme Engine;
- Rule Engine;
- Search API geografica.

L'applicazione operativa è ASTROLAB.

---

# Stato della piattaforma

La piattaforma è considerata stabile.

Sono consolidati:

- motore astronomico;
- Swiss Ephemeris tramite PHP FFI;
- Planet Condition Engine;
- Rule Engine;
- Evidence Engine;
- Theme Engine;
- Narrative Engine;
- Annual Report;
- Comparator;
- Decision Support System;
- gestione utenti;
- gestione soggetti;
- gestione sessioni;
- PostgreSQL;
- Docker;
- interfaccia web;
- suite automatica dei test.

Il Rule Engine contiene 120 Rule ed è considerato
parte della baseline permanente.

La macro-funzionalità di registrazione utenti, verifica email, recupero
password, piani `free` e `supporter`, limiti, quote, restrizioni funzionali e
sicurezza delle sessioni è completata.

Il riferimento operativo dedicato è
`docs/roadmap_registrazioneutenti.md`; la decisione architetturale corrispondente
è ADR-016, con stato `Accettata`.

La roadmap relativa alla comparazione funzionale tra Astrolab e MyAstral.org
è mantenuta separatamente nel documento `docs/roadmap_comparazione_myastral.md`,
che costituisce il riferimento ufficiale per le attività di allineamento con
il software di Ciro Discepolo. Le attività sono subordinate all'acquisto di
un account MyAstral.org e richiedono evidenze verificabili prima di qualsiasi
modifica applicativa.

Non deve essere modificato salvo:

- bug documentati;
- incompatibilità tecniche;
- decisioni architetturali esplicite.

---

# Architettura

La pipeline principale è:

```text
Swiss Ephemeris
        │
Planet Resolver
        │
Planet Condition Engine
        │
Rule Engine
        │
Evidence Engine
        │
Theme Engine
        │
Narrative Engine
        │
Annual Report
        │
Comparator
        │
Decision Support System
        │
API / Browser / PDF
```

---

# Ricerca RSM v3

La Ricerca RSM v3 costituisce il più recente consolidamento della
piattaforma.

L'obiettivo dell'evoluzione era superare la ricerca limitata ai soli
aeroporti introducendo un repository geografico mondiale composto da
aeroporti e località.

Le principali funzionalità implementate sono:

- modalità `solo_aeroporti`;
- modalità `solo_localita`;
- ricerca delle località limitata alla nazione selezionata;
- selezione obbligatoria della nazione nella modalità località;
- limite configurabile dei risultati;
- ordinamento alfabetico delle nazioni;
- caricamento asincrono delle nazioni all'apertura della pagina;
- distinzione tra aeroporti e località tramite `origine_punto`;
- visualizzazione del nome geografico completo;
- visualizzazione della popolazione quando disponibile;
- visualizzazione dei codici IATA e ICAO quando disponibili.

Il repository geografico supporta ormai entrambe le tipologie di
risultati senza alterare il comportamento storico della modalità
aeroportuale.

La deduplicazione viene eseguita direttamente in PostgreSQL.

L'ordinamento è deterministico.

La priorità dei risultati è determinata esclusivamente dalla condizione
astrologica.

Il limite dei risultati viene applicato soltanto dopo la valutazione
completa di tutti i candidati.

Durante questa evoluzione il motore astrologico non è stato modificato.

---

# Comparator

Il Comparator Engine è operativo.

Sono disponibili:

- confronto multiplo delle Rivoluzioni Solari;
- confronto multiplo delle Rilocazioni;
- selezione fino a tre risultati;
- payload condiviso;
- tabelle comparative;
- riepilogo soggetti;
- layout responsive;
- ruote astrologiche integrate;
- preservazione delle regole personalizzate.

Il Comparator rappresenta la base del Decision Support System.

---

# Decision Support System

Il Decision Support System utilizza i risultati già prodotti
dall'applicazione.

Il suo compito consiste nel costruire confronti spiegabili e
tracciabili.

Ogni raccomandazione deve poter essere ricondotta a:

- Rule;
- Evidence;
- Theme;
- Planet Conditions;
- dati sorgente.

Il DSS non sostituisce il Rule Engine.

Utilizza esclusivamente risultati già consolidati.

---

# Stato dello sviluppo

Alla data di questo documento risultano completati:

- infrastruttura;
- architettura;
- Rule Engine;
- Theme Engine;
- Narrative Engine;
- Annual Report;
- Comparator;
- Ricerca RSM v3;
- Search API;
- deduplicazione SQL;
- regressione automatica;
- documentazione tecnica.
- codifica colore semantica dei pianeti sulla ruota;

Il ciclo principale di sviluppo funzionale può essere considerato
completato.

Le attività residue riguardano esclusivamente:

- consolidamento documentale;
- verifiche finali;
- manutenzione;
- eventuali correzioni di bug.

Non risultano milestone funzionali obbligatorie ancora aperte.

---

# Verifiche permanenti

Ogni modifica deve rispettare le seguenti regole.

La regressione automatica deve rimanere completamente verde.

Ogni modifica deve essere:

- deterministica;
- tracciabile;
- spiegabile;
- documentata;
- verificabile.

Prima di ogni commit devono essere verificati almeno:

- sintassi PHP;
- test interessati;
- regressione;
- `git diff --check`;
- documentazione coinvolta.

Il Rule Engine non deve essere modificato senza una motivazione
architetturale esplicita.

---

# Documentazione ufficiale

La documentazione principale del progetto è composta dai seguenti file.

1. `docs/START_HERE.md`
2. `docs/README_ASTROLAB.md`
3. `docs/03_ARCHITECTURE_ASTROLAB.md`
4. `docs/ROADMAP.md`
5. `docs/HANDOVER_OPERATIVO_astrolab.md`
6. `docs/ADR_INDEX_ASTROLAB.md`
7. `docs/01_PROJECT_MANIFESTO.md`
8. `docs/02_ASTROLOGY.md`
9. `docs/10_THEME_ENGINE.md`
10. `docs/11_ANNUAL_REPORT_SPEC.md`

I documenti devono essere mantenuti coerenti tra loro.

Ogni modifica significativa del progetto deve aggiornare almeno:

- README;
- START_HERE;
- ROADMAP;
- HANDOVER.

---

# Filosofia del progetto

ASTROLAB privilegia:

- semplicità;
- stabilità;
- prevedibilità;
- tracciabilità;
- spiegabilità.

Non costituiscono obiettivi del progetto:

- introdurre framework;
- introdurre livelli architetturali superflui;
- duplicare la conoscenza astrologica;
- creare nuove versioni della pipeline senza necessità.

Ogni evoluzione deve avere un beneficio concreto e verificabile.

---

# Stato del repository

Alla data di questo documento il repository presenta:

- branch principale `master`;
- working tree pulita;
- Rule Engine congelato;
- regressione disponibile;
- documentazione in fase di allineamento finale.

La Ricerca RSM v3 è completata.

L'ultima evoluzione ha introdotto:

- selezione obbligatoria della nazione;
- ordinamento alfabetico delle nazioni;
- visualizzazione IATA e ICAO;
- priorità della condizione astrologica mantenuta durante la ricerca;
- tag stabile della funzionalità.

La baseline applicativa può essere considerata stabile.

---

# Punto di ripresa

Nel caso in cui il progetto venga riaperto in futuro, l'ordine di lavoro
raccomandato è il seguente.

1. Verificare lo stato del repository.
2. Eseguire la regressione completa.
3. Verificare la documentazione.
4. Analizzare eventuali bug aperti.
5. Pianificare nuove funzionalità solo dopo avere confermato la stabilità
   della baseline.

Qualunque nuova evoluzione dovrà rispettare i principi architetturali
descritti nella documentazione ufficiale.

---

# Obiettivo raggiunto

ASTROLAB dispone oggi di una piattaforma completa per:

- calcolo astrologico;
- produzione dei report;
- confronto delle Rivoluzioni Solari;
- confronto delle Rilocazioni;
- supporto decisionale;
- ricerca geografica mondiale;
- gestione completa dei soggetti;
- tracciabilità delle Rule;
- spiegabilità delle evidenze;
- regressione automatica.

Il progetto è stato sviluppato mantenendo:

- separazione delle responsabilità;
- architettura modulare;
- compatibilità della baseline;
- determinismo dei risultati;
- elevata manutenibilità.

---

# Stato finale

Lo sviluppo funzionale previsto dalla roadmap iniziale può essere
considerato concluso.

L'attività futura del progetto è orientata principalmente a:

- manutenzione correttiva;
- aggiornamenti tecnologici;
- miglioramenti incrementali;
- evoluzioni richieste da esigenze reali;
- aggiornamento continuo della documentazione.

La baseline corrente costituisce il riferimento ufficiale per ogni
sviluppo successivo.

Dal momento del deployment su VPS, il diario operativo del progetto
proseguirà in `docs/HANDOVER_OPERATIVO_VPS_v2.md`.

`docs/HANDOVER_OPERATIVO_astrolab.md` rimarrà lo storico ufficiale della
fase di sviluppo e consolidamento della baseline.

---

# Regola finale

Prima di qualsiasi modifica futura verificare sempre:

- stato del repository;
- regressione;
- documentazione;
- compatibilità con il Rule Engine;
- impatto architetturale.

Ogni modifica deve lasciare il progetto in uno stato almeno equivalente,
o migliore, rispetto a quello precedente.

---

**Ultimo aggiornamento**

Versione documentale allineata alla baseline stabile di ASTROLAB.

Repository: `master`

Stato: **Baseline stabile – sviluppo funzionale completato, manutenzione evolutiva e documentale.**
