# Astro-DSS — Handover operativo

Ultimo aggiornamento: 2026-07-18

Stato corrente

progetto: Astro-DSS;

branch: feature/v6.1;

repository indipendente da Astro-Val;

container web: astro-dss-web;

container database: astro-dss-db;

container Adminer: astro-dss-adminer;

applicazione disponibile sulla porta 8192;

Adminer disponibile sulla porta 8193;

PostgreSQL disponibile sulla porta 5442;

database ripristinato: 7 tabelle, circa 28 MB;

Rule Engine ereditato: 120 Rule;

Knowledge Coverage: 100%;

Rule Engine congelato durante la prima fase DSS.

Obiettivo operativo corrente

Aggiornare la documentazione tecnica del Comparator Engine completato e proseguire con la progettazione dei successivi moduli DSS previsti dalla roadmap:

- Impact Evaluator;
- Rule Correlator.

Il lavoro dovrà mantenere invariato il Rule Engine ereditato e valorizzare i dati già prodotti dai confronti tra Rivoluzioni Solari e Rilocazioni.

Timeline Astro-DSS

2026-07-17 — Creazione del progetto indipendente

Componente modificato:

repository Git;

Docker Compose;

database PostgreSQL;

documentazione.

Obiettivo:

separare completamente Astro-DSS da Astro-Val.

Risultato:

creato stack Docker indipendente;

assegnate porte indipendenti;

ripristinato e verificato il database;

verificata l'applicazione sulla porta 8192;

aggiornati START_HERE.md, README.md e ROADMAP.md.

Test eseguiti:

avvio container;

accesso applicazione;

verifica database;

git diff --check;

verifica working tree.

Commit:

130944e — isolamento stack Docker;

24d25ba — definizione entry point;

27044bb — aggiornamento README;

db415b2 — definizione roadmap Astro-DSS.

Passo successivo:

inventario completo degli output del motore RS.

2026-07-18 — Prima implementazione del Comparator Engine

Obiettivo

Trasformare la fase di analisi iniziale in una prima implementazione operativa del confronto tra Rivoluzioni Solari e Rilocazioni senza modificare il Rule Engine ereditato da Astro-Val.

Componenti sviluppati

Confronto Rivoluzioni Solari

Sono state implementate le funzionalità necessarie per confrontare più Rivoluzioni Solari all'interno della stessa sessione.

In particolare sono stati aggiunti:

selezione tramite checkbox;

confronto fino a tre risultati contemporaneamente;

persistenza della selezione;

costruzione del payload di confronto;

pagina dedicata compare_rs.php;

riepilogo dei soggetti selezionati;

layout responsive.

Confronto Rilocazioni

Lo stesso modello è stato esteso al confronto delle rilocazioni.

Sono stati realizzati:

selezione multipla;

confronto fino a tre rilocazioni;

payload dedicato;

pagina compare_ril.php;

riepilogo delle località confrontate;

layout responsive.

Evoluzione di compare_ril.php

La pagina di confronto oggi visualizza:

informazioni del soggetto;

località confrontate;

dati sintetici;

tabelle dei match astrologici;

posizione di Venere;

posizione di Giove;

casa astrologica;

cuspide;

distanza dalla cuspide.

Il payload JSON rimane disponibile come strumento di debug tramite un elemento <details> inizialmente chiuso.

Correzioni effettuate

Durante lo sviluppo sono stati risolti:

inizializzazione di SubjectName;

inizializzazione di UserName;

warning PHP;

errori HTTP 500;

gestione incompleta del payload;

visualizzazione dei dati mancanti.

Stato raggiunto

Attualmente risultano completati:

confronto RS;

confronto Rilocazioni;

selezione multipla;

persistenza della selezione;

payload di confronto;

layout responsive;

tabelle dei match;

PHP lint senza errori;

risposta HTTP 200.

Commit della milestone

L'implementazione della prima versione del Comparator Engine è stata sviluppata attraverso i seguenti commit principali.

Confronto Rivoluzioni Solari

e731330 — test: lock RS VAL output contract

c4707d3 — feat: prepare comparison selection state

9b7f4ba — feat: add comparison checkbox to standard results

99e7013 — feat: persist up to three comparison selections

c444819 — feat: show comparison action for selected results

cae451d — style: simplify comparison checkbox column

5f36b2c — feat: resolve selected comparison results

6e8e92b — feat: pass comparison payload to compare page

4884831 — feat: add comparison payload debug page

c3dcc50 — feat: show comparison payload count

08507de — feat: show comparison summary

161e26f — feat: add responsive comparison layout

Confronto Rilocazioni

e4038f6 — feat: add comparison checkboxes to relocation results

a73cd25 — feat: persist up to three relocation selections

8c5eee1 — feat: show relocation comparison action

99276ca — feat: persist relocation comparison payload

470fafc — feat: add relocation comparison page

3611d06 — debug: show relocation comparison payload

e1d6a75 — fix: initialize subject name in comparison pages

538d131 — fix: show subject name in RS comparison

ce94849 — fix: initialize username in comparison pages

1fe9110 — feat: show relocation match details

Decisione architetturale

Per la visualizzazione delle ruote astrologiche è stato stabilito di non duplicare la logica esistente.

La pagina rilocazione.php rappresenta il riferimento funzionale da riutilizzare.

L'integrazione dovrà utilizzare direttamente:

tema_api.php;

ZodiacWheel;

WheelRiloc;

popolaTabellaPianeti().

Punto di ripresa definito in questa milestone

Al termine di questa fase, la successiva attività prevista consisteva nell'integrare in `compare_ril.php` la visualizzazione delle ruote astrologiche reali, mantenendo invariata la logica astrologica già presente nell'applicazione.

Questa attività è stata successivamente completata nella milestone del 2026-07-18 dedicata all'integrazione delle ruote astrologiche nel confronto Rilocazioni.

---

## 2026-07-18 — Integrazione delle ruote astrologiche nel confronto Rilocazioni

### Componente modificato

- `compare_ril.php`

### Obiettivo

Completare il Comparator Engine integrando nella pagina di confronto le ruote astrologiche già utilizzate dall'applicazione, evitando duplicazioni della logica esistente.

### Risultato

- integrate le ruote astrologiche nella pagina di confronto;
- riutilizzati i componenti già presenti nell'applicazione;
- mantenuta invariata la logica astrologica;
- completato il precedente punto di ripresa relativo al Comparator Engine.

### Commit

- `984d4bd` — feat: render relocation wheels in comparison

---

## 2026-07-18 — Preservazione delle regole personalizzate nel confronto RS

### Componente modificato

- Comparator Engine RS

### Obiettivo

Garantire che il confronto tra Rivoluzioni Solari utilizzi le stesse regole personalizzate applicate durante il calcolo originale, senza ricostruire le configurazioni delle case.

### Risultato

- preservate le regole personalizzate dei pianeti nelle case;
- mantenuta la coerenza tra risultato originale e pagina di confronto;
- nessuna modifica al Rule Engine.

### Commit

- `57fbba4` — feat: preserve custom planet house rules in RS comparison

---

## 2026-07-18 — Merge nel branch principale di sviluppo

### Operazione

Il lavoro sviluppato nel branch `feature/astro-dss` è stato integrato nel branch di lavoro `feature/v6.1` mediante merge non fast-forward.

### Commit

- `faf8462` — Merge branch 'feature/astro-dss' into feature/v6.1

---

## 2026-07-18 — Consolidamento dell'interfaccia del Comparator RS

### Componenti modificati

- `ricerca.php`
- `style.css`

### Obiettivo

Rifinire l'interfaccia del Comparator mantenendo invariato il comportamento funzionale.

### Modifiche effettuate

- pulsante **Confronta** mantenuto sopra la tabella e allineato a destra;
- stile del pulsante uniformato a quello verde piatto di **Mappa**;
- dimensioni leggermente ridotte;
- colonna delle checkbox spostata all'estrema destra, dopo la colonna **RS**;
- pulsanti **Mappa** e **Usa** lasciati invariati.

### Verifiche

- `docker compose exec -T astro-dss-web php -l /var/www/html/ricerca.php` ✔
- `git diff --check` ✔
- working tree pulita ✔

### Commit

- `37d15be` — feat: refine RS comparison controls layout

---

### Stato attuale

- Comparator Engine RS operativo;
- Comparator Engine Rilocazioni operativo;
- ruote astrologiche integrate;
- regole personalizzate preservate nel confronto RS;
- interfaccia del Comparator consolidata;
- branch corrente: `feature/v6.1`;
- working tree pulita.

### Prossimo passo

Aggiornare la documentazione tecnica e proseguire con la progettazione dell'Impact Evaluator e del Rule Correlator previsti dalla roadmap Astro-DSS.
