# START HERE

Benvenuto nel progetto **Astro-Val**.

Questo documento è il punto di ingresso per comprendere rapidamente lo
stato del progetto e sapere da dove riprendere lo sviluppo.

------------------------------------------------------------------------

# Ultima milestone completata

**Commit:** `c411ee3`

**Descrizione:** Refactoring UI/CSS completato sulle quattro pagine principali

**Stato:** COMPLETATA

**Branch di sviluppo:** `feature/v6.1`

**Milestone corrente:** V6.1 — Consolidamento UX/UI e CSS

------------------------------------------------------------------------

# Stato del progetto

**Versione architetturale:** V4 completata

-   ✅ Rule Engine: **120/120**
-   ✅ Rule Registry: **120 Rule registrate**
-   ✅ Full Regression: OK
-   ✅ Knowledge Coverage: **100%**
-   ✅ Repository stabile

Il Rule Engine è considerato **completo** e **congelato (freeze)**.

Non devono essere aggiunte nuove Rule né modificate quelle esistenti,
salvo correzioni di bug documentati.

------------------------------------------------------------------------

# Percorso del progetto

-   V1 --- Motore astronomico ✅
-   V2 --- Base Interpretation ✅
-   V3 --- Forecast Engine ✅
-   V4 --- Rule Engine (120 Rule) ✅
-   V5 --- Consolidamento Report Professionale ✅
-   V6 --- Hardening e Release stabile completata ✅
-   V6.1 --- Consolidamento UX/UI e CSS in corso 🚧

------------------------------------------------------------------------

# Da leggere in quest'ordine

1.  **ADR_INDEX.md**
    -   decisioni architetturali
    -   freeze del Rule Engine
2.  **ROADMAP.md**
    -   roadmap del progetto
    -   milestone raggiunte
    -   prossimi obiettivi
3.  **HANDOVER_OPERATIVO.md**
    -   stato operativo corrente
    -   punto esatto da cui riprendere

------------------------------------------------------------------------

# Dove riprendere

Le prossime attività NON riguardano il Rule Engine.

Lo sviluppo prosegue esclusivamente su:

-   rifiniture UX/UI finali;
-   consolidamento del frontend condiviso;
-   ottimizzazione di `www/css/style.css`;
-   validazione browser desktop e mobile;
-   preparazione della release V6.1.

Sono già completati:

-   header responsive condiviso con menu hamburger;
-   refactoring UI di `www/index.php`;
-   refactoring UI di `www/tema.php`;
-   refactoring UI di `www/rs.php`;
-   refactoring UI di `www/rl.php`;
-   eliminazione degli inline style statici dalle pagine principali.

------------------------------------------------------------------------

# Filosofia del progetto

L'architettura è organizzata a livelli.

1.  Calcoli astronomici
2.  Planet Conditions
3.  Rule Engine
4.  Evidence Engine
5.  Theme Engine
6.  Narrative Engine
7.  Annual Report

I livelli inferiori sono ormai consolidati.

L'evoluzione del progetto avverrà esclusivamente nei livelli superiori,
migliorando la qualità del report professionale senza alterare la logica
delle 120 Rule.

------------------------------------------------------------------------

# Obiettivo finale

Consolidare la Release V6.1 di Astro-Val:

-   architettura stabile;
-   report professionale;
-   piena tracciabilità delle evidenze;
-   qualità narrativa elevata;
-   documentazione completa;
-   prodotto pronto per l'utilizzo operativo.
