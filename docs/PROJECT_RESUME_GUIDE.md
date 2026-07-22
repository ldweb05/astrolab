# Astro-Val --- Project Resume Guide

## Scopo

Questo documento è la guida operativa per riprendere il progetto
Astro-Val dopo un periodo di sospensione.

Serve a consentire a qualsiasi sviluppatore (o al futuro manutentore del
progetto) di comprendere rapidamente: - lo stato reale del progetto; -
la baseline tecnica disponibile; - ciò che non deve essere modificato; -
il workflow obbligatorio; - i controlli da eseguire; - le condizioni per
la Release definitiva della V6.1.

------------------------------------------------------------------------

## Stato del progetto

-   Versione: V6.1
-   Stato: sviluppo funzionale completato; progetto sospeso durante la
    validazione reale.
-   Branch: feature/v6.1
-   Ultimo commit documentale: c1011e1 --- docs: record v6.1 markup
    stabilization
-   Working Tree attesa: CLEAN
-   Rule Engine: FREEZE (120 Rule)

### Ultima certificazione tecnica

FULL REGRESSION OK V6 HARDENING SUITE OK V6 RELEASE CHECK OK

Tempo ultimo Release Check: 27 secondi.

La V6.1 è tecnicamente stabile. Non sono previste ulteriori attività di
sviluppo salvo bug reali emersi durante l'utilizzo da parte dei due
utenti iniziali.

------------------------------------------------------------------------

## Componenti completati

-   autenticazione multi-astrologo
-   gestione soggetti
-   Tema Natale
-   Rivoluzione Solare
-   Rivoluzione Lunare
-   motore Swiss Ephemeris
-   Rule Engine (120 Rule)
-   Narrative Engine
-   Annual Forecast
-   Annual Report
-   stampa browser
-   PDF Dompdf
-   CSS condiviso
-   refactoring V6
-   documentazione tecnica

------------------------------------------------------------------------

## Ultimi commit

-   fb5d8b0 --- fix(ui): avoid nested tables in natal chart page
-   d62e465 --- fix(ui): correct planetary table markup in solar return
    page
-   6025743 --- fix(ui): correct lunar return planetary table markup
-   c1011e1 --- docs: record v6.1 markup stabilization

------------------------------------------------------------------------

## Documentazione obbligatoria

1.  START_HERE.md
2.  README.md
3.  ROADMAP.md
4.  HANDOVER_OPERATIVO.md
5.  ADR_INDEX.md
6.  PROJECT_RESUME_GUIDE.md

------------------------------------------------------------------------

## Workflow obbligatorio

1.  Aprire un solo file.
2.  Leggerlo integralmente.
3.  Individuare la modifica.
4.  Preparare uno script Python.
5.  Eseguire lo script.
6.  Verificare il diff.
7.  Eseguire i controlli.
8.  Eseguire git diff --check.
9.  Commit dedicato.
10. Aggiornare HANDOVER_OPERATIVO.md.

Mai modificare direttamente i file.

------------------------------------------------------------------------

## Regole da non violare

-   Non modificare il Rule Engine.
-   Non modificare le 120 Rule.
-   Nessun mega-refactoring.
-   Nessuna modifica multipla nello stesso commit.
-   Non sostituire gli ultimi style.display runtime.
-   Working Tree sempre pulita.

------------------------------------------------------------------------

## Ripresa del progetto

Eseguire:

cd \~/astro-val git status --short git branch --show-current git log -5
--oneline docker exec astro-val-web bash
/var/www/html/tests/run_v6_release_check.sh

Il Release Check deve terminare con: - FULL REGRESSION OK - V6 HARDENING
SUITE OK - V6 RELEASE CHECK OK

------------------------------------------------------------------------

## Gestione bug

-   Riprodurre il bug.
-   Individuare un solo file.
-   Preparare uno script Python.
-   Eseguire i controlli.
-   Commit autonomo.
-   Release Check completo.
-   Aggiornare HANDOVER_OPERATIVO.md.

------------------------------------------------------------------------

## Condizioni per la Release V6.1

-   Nessun bug bloccante.
-   Working Tree pulita.
-   Full Regression OK.
-   V6 Hardening Suite OK.
-   V6 Release Check OK.
-   Documentazione allineata.
-   Tag Git finale.

------------------------------------------------------------------------

## Evoluzione futura

Le nuove funzionalità dovranno essere sviluppate nella V6.2. La V6.1
deve rimanere la baseline stabile del progetto.

------------------------------------------------------------------------

Fine documento.
