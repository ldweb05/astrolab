# Astro-DSS

Decision Support System per il confronto di due Rivoluzioni Solari Mirate (RSM), costruito sulla base architetturale consolidata di Astro-Val.

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
- Header condiviso con menu hamburger responsive e sottomenu Rivoluzioni
- CSS centralizzato senza inline style nelle pagine principali
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
astro-dss-web
```

Il nome del container PostgreSQL, i volumi e le reti devono essere verificati nel file `docker-compose.yml` della baseline corrente.

### Percorsi principali

Repository:

```text
~/astro-dss
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
www/css/style.css
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
docker exec astro-dss-web php /var/www/html/tests/test_regression_v3.php
```

Il risultato valido deve terminare con:

```text
FULL REGRESSION OK
```

### Conteggio Rule registrate

```bash
docker exec astro-dss-web php -r 'require "/var/www/html/includes/forecast/AARuleEngine.php"; echo count(RuleRegistry::all()).PHP_EOL;'
```

Risultato atteso:

```text
120
```

### Lint PHP

```bash
docker exec astro-dss-web php -l /var/www/html/<percorso-file>.php
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

La roadmap specifica del progetto è descritta in `docs/ROADMAP.md`.

## Punto di ripresa

Lo sviluppo corrente riguarda esclusivamente Astro-DSS.

Priorità attuale:

1. progettazione del Comparator Engine;
2. definizione del modello dati confrontabile;
3. progettazione del Decision Support System.

Per i dettagli consultare:

- `docs/START_HERE.md`
- `docs/ROADMAP.md`
- `docs/HANDOVER_OPERATIVO.md`
