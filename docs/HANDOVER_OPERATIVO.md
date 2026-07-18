# Astro-DSS — Handover operativo

Ultimo aggiornamento: 2026-07-17

---

# Stato corrente

- progetto: `Astro-DSS`;
- branch: `feature/astro-dss`;
- repository indipendente da Astro-Val;
- container web: `astro-dss-web`;
- container database: `astro-dss-db`;
- container Adminer: `astro-dss-adminer`;
- applicazione disponibile sulla porta `8192`;
- Adminer disponibile sulla porta `8193`;
- PostgreSQL disponibile sulla porta `5442`;
- database ripristinato: 7 tabelle, circa 28 MB;
- Rule Engine ereditato: 120 Rule;
- Knowledge Coverage: 100%;
- Rule Engine congelato durante la prima fase DSS.

# Obiettivo operativo corrente

Realizzare l'inventario tecnico completo degli output prodotti da una
Rivoluzione Solare.

Il censimento deve individuare:

- strutture PHP;
- dati persistiti nel database;
- dati mantenuti in sessione;
- condizioni planetarie;
- Rule attivate;
- evidenze;
- output narrativi;
- dati confrontabili direttamente;
- dati che richiedono normalizzazione.

Il risultato costituirà la base del Comparator Engine.

# Timeline Astro-DSS

## 2026-07-17 — Creazione del progetto indipendente

Componente modificato:

- repository Git;
- Docker Compose;
- database PostgreSQL;
- documentazione.

Obiettivo:

separare completamente Astro-DSS da Astro-Val.

Risultato:

- creato stack Docker indipendente;
- assegnate porte indipendenti;
- ripristinato e verificato il database;
- verificata l'applicazione sulla porta `8192`;
- aggiornati `START_HERE.md`, `README.md` e `ROADMAP.md`.

Test eseguiti:

- avvio container;
- accesso applicazione;
- verifica database;
- `git diff --check`;
- verifica working tree.

Commit:

- `130944e` — isolamento stack Docker;
- `24d25ba` — definizione entry point;
- `27044bb` — aggiornamento README;
- `db415b2` — definizione roadmap Astro-DSS.

Passo successivo:

inventario completo degli output del motore RS.

---

