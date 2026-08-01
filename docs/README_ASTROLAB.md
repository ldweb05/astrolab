# ASTROLAB

Piattaforma professionale per l'astrologia previsionale e il Decision
Support System.

ASTROLAB rappresenta l'unificazione dei progetti **Astro-Val** (baseline
architetturale) e **Astro-DSS** (evoluzione del Comparator e del
Decision Support System) in un'unica piattaforma.

## Per iniziare

Leggere nell'ordine:

1.  `docs/START_HERE.md`
2.  `docs/ADR_INDEX_ASTROLAB.md`
3.  `docs/ROADMAP.md`
4.  `docs/HANDOVER_OPERATIVO_astrolab.md`

Questi documenti costituiscono la documentazione ufficiale del progetto.

## Stato del progetto

La macro-funzionalità di registrazione utenti, verifica email, recupero password,
gestione dei piani `free` e `supporter`, limiti funzionali, quote, restrizioni
lato server e interfaccia, Comparator, Annual Report e sicurezza delle sessioni
è completata.

La documentazione operativa di riferimento è:

- `docs/roadmap_registrazioneutenti.md`;
- `docs/HANDOVER_OPERATIVO_astrolab.md`;
- `docs/ADR_INDEX_ASTROLAB.md` — ADR-016 accettata.


-   Rule Engine: **120/120**
-   Rule registrate: **120**
-   Knowledge Coverage: **100%**
-   Full Regression: **OK**
-   Repository: **stabile**
-   Rule Engine: **in freeze**

Le 120 Rule costituiscono il nucleo stabile del sistema.

## Funzionalità principali

ASTROLAB integra:

-   Tema Natale
-   Rivoluzione Solare
-   Rivoluzione Lunare
-   Rilocazione
-   Annual Report
-   Comparator RS
-   Comparator Rilocazioni
-   confronto multiplo fino a tre risultati
-   persistenza della selezione
-   payload di confronto
-   tabelle comparative
-   ruote astrologiche integrate
-   preservazione delle regole personalizzate
-   Decision Support System

## Stato dello sviluppo

Il Comparator Engine è operativo.

Sono completati:

-   confronto multiplo RS;
-   confronto multiplo Rilocazioni;
-   selezione multipla fino a tre risultati;
-   payload di confronto;
-   pagine dedicate;
-   layout responsive;
-   riepilogo soggetti;
-   tabelle dei match astrologici;
-   integrazione ruote astrologiche;
-   preservazione delle regole personalizzate;
-   consolidamento Comparator RS;
-   compatibilità completa con il Rule Engine congelato;
-   lint PHP;
-   `git diff --check` pulito.

## Architettura logica
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

## Caratteristiche distintive

-   120 Rule completamente tracciabili
-   Rule Registry centralizzato
-   Explainability completa
-   Swiss Ephemeris tramite PHP FFI
-   Nessun backend swetest
-   Nessun framework
-   Docker + PostgreSQL 16
-   Vanilla JavaScript + SVG
-   CSS centralizzato
-   Architettura modulare
-   Full Regression automatica

## Stack tecnologico

-   Backend: PHP 8.3
-   Runtime: Docker
-   Database: PostgreSQL 16
-   Frontend: Vanilla JavaScript + SVG
-   Framework: nessuno

## Roadmap

La roadmap ufficiale è descritta in `docs/ROADMAP.md`.

## Punto di ripresa

Dal momento del deployment su VPS, il diario operativo del progetto
proseguirà in `docs/HANDOVER_OPERATIVO_VPS_v2.md`.

`docs/HANDOVER_OPERATIVO_astrolab.md` resterà lo storico ufficiale della
fase di sviluppo e consolidamento della baseline.

Per la Ricerca RSM v3 risultano completate:

-   FASE 1 — analisi del modello geografico;
-   FASE 2 — nuovo modello Località;
-   FASE 3 — Backend;
-   FASE 4 — Contratto backend;
-   FASE 5 — Interfaccia;
-   FASE 5A — ricerca delle località limitata ad una nazione con selezione
    obbligatoria della nazione e limite configurabile di risultati
    (50/100/150/Tutte).
-   FASE 6 — Prestazioni e determinismo;
-   FASE 7 — Test.

La baseline del progetto è stabile.

Le attività future riguardano esclusivamente:


Aggiornamento manutentivo Ricerca RSM v3:

- percentuale visualizzata con due decimali;
- barra di avanzamento mantenuta visibile al completamento;
- tranche di ricerca aumentata da 20.000 a 30.000;
- caricamento asincrono delle nazioni nella modalità Località.


- manutenzione correttiva;
- bug fixing;
- aggiornamento della documentazione;
- evoluzioni funzionali future quando realmente necessarie.

Il Rule Engine e il motore astrologico costituiscono la baseline permanente del progetto.
