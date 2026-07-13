# Astro-Val

Motore professionale per l’interpretazione della Rivoluzione Solare, basato su un’architettura a livelli, con Rule Engine completamente tracciabile e Report Annuale narrativo.

## Per iniziare

Leggere nell’ordine:

1. `docs/START_HERE.md`
2. `docs/ADR_INDEX.md`
3. `docs/ROADMAP.md`
4. `docs/HANDOVER_OPERATIVO.md`

Questi documenti descrivono il percorso del progetto, le decisioni architetturali, lo stato corrente e il punto esatto da cui riprendere.

## Stato del progetto

- Rule Engine: **120/120**
- Rule registrate: **120**
- Knowledge Coverage: **100%**
- Full Regression: **OK**
- Repository: **stabile**
- Rule Engine: **in freeze**

Le 120 Rule costituiscono il nucleo stabile del sistema. Non devono essere aggiunte nuove Rule né modificate quelle esistenti, salvo correzioni di bug documentati o incompatibilità tecniche.

Le evoluzioni future riguardano esclusivamente:

- Annual Report;
- Narrative Engine;
- Executive Summary;
- Theme Profile;
- Cross Dynamics;
- Conclusion;
- PDF professionale;
- UX e presentazione;
- validazione con casi reali.

## Caratteristiche distintive

- 120 Rule completamente tracciabili
- Rule Registry centralizzato
- Annual Report spiegabile e basato su evidenze
- Swiss Ephemeris tramite PHP FFI
- Nessun backend `swetest`
- Nessun framework
- Docker + PostgreSQL 16
- Frontend Vanilla JavaScript + SVG
- Architettura modulare
- Knowledge Coverage 100%
- Full Regression automatica

## Architettura logica

```text
Calcoli astronomici
        │
Planet Conditions
        │
Rule Engine (120 Rule)
        │
Evidence Engine
        │
Theme Engine
        │
Narrative Engine
        │
Annual Report professionale
```

## Architettura tecnica

### Stack tecnologico

- **Backend:** PHP 8.3
- **Runtime:** Docker
- **Database:** PostgreSQL 16
- **Frontend:** Vanilla JavaScript + SVG
- **Framework:** nessuno
- **Tema grafico:** crema/blu (`style.css`)

### Motore astronomico

Astro-Val utilizza esclusivamente **Swiss Ephemeris** tramite **PHP FFI (`libswe`)**.

Caratteristiche:

- nessun processo esterno;
- nessuna chiamata shell;
- nessun backend `swetest`;
- nessuna dipendenza Python;
- calcoli eseguiti direttamente da PHP tramite FFI.

Il backend storico basato su `swetest` non è più presente e non deve essere reintrodotto.

### Docker

Container applicativo principale:

```text
astro-val-web
```

Il nome del container PostgreSQL, i volumi e le reti devono essere verificati nel file `docker-compose.yml` della baseline corrente.

### Percorsi principali

Repository:

```text
~/astro-val
```

Document root nel container:

```text
/var/www/html
```

Forecast Engine:

```text
/var/www/html/includes/forecast/
```

Rule Engine:

```text
/var/www/html/includes/forecast/rules/
```

Atlas:

```text
/var/www/html/includes/atlas/
```

Test:

```text
/var/www/html/tests/
```

Documentazione:

```text
docs/
```

Tema grafico:

```text
www/style.css
```

Swiss Ephemeris / binding FFI:

```text
www/includes/
```

Il percorso esatto di `libswe` e dell’header FFI deve essere verificato nella configurazione runtime e nei file PHP che caricano la libreria.

### Database

- **Engine:** PostgreSQL 16
- **Charset:** UTF-8
- **Timezone:** verificare la configurazione corrente
- **Nome database:** verificare in `.env` e `docker-compose.yml`
- **Host:** verificare in `.env` e `docker-compose.yml`
- **Porta:** verificare in `.env` e `docker-compose.yml`
- **Utente applicativo:** verificare in `.env`

I valori effettivi non devono essere duplicati nel README se già definiti nella configurazione runtime.

## Comandi principali

### Full Regression

```bash
docker exec astro-val-web php /var/www/html/tests/test_regression_v3.php
```

Il risultato valido deve terminare con:

```text
FULL REGRESSION OK
```

### Conteggio Rule registrate

```bash
docker exec astro-val-web php -r 'require "/var/www/html/includes/forecast/AARuleEngine.php"; echo count(RuleRegistry::all()).PHP_EOL;'
```

Risultato atteso:

```text
120
```

### Lint PHP

```bash
docker exec astro-val-web php -l /var/www/html/<percorso-file>.php
```

### Stato Git

```bash
git status --short
```

Lo stato corretto a fine ciclo è:

```text
WORKING TREE CLEAN
```

## Roadmap

- V1 — Motore astronomico: completata
- V2 — Base Interpretation: completata
- V3 — Forecast Engine: completata
- V4 — Rule Engine 120/120: completata
- V5 — Consolidamento Report Professionale: in corso
- V6 — Hardening e Release 1.0: pianificata

Per i dettagli consultare `docs/ROADMAP.md`.

## Punto di ripresa

Il prossimo sviluppo deve concentrarsi sul consolidamento del Report Professionale.

Priorità operative:

1. Executive Summary
2. Narrative Engine
3. Theme Profile
4. Cross Dynamics
5. Conclusion
6. Riduzione delle ripetizioni
7. Coerenza stilistica
8. Qualità del PDF
9. UX del report
10. Validazione con casi reali

Il Rule Engine non deve essere riaperto salvo bug documentati.
