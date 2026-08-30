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

**Prima di toccare codice, leggi `docs/FREEZE.md`** — elenca cosa è congelato (e perché) e
i fix noti che esistono solo su alcuni branch.

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
- Transiti Planetari;
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

# Ricerca RSM/RL — condizione Decima (Regola 14, UX-0015/UX-0018)

Per la condizione Decima, con `MYASTRAL_ALIGNMENT_MODE` attivo, la ricerca
applica una gerarchia dedicata a 6 livelli (ASC in X casa natale come
segnale primario, declassato dalla Regola 14 se un pianeta lento è in
aspetto dissonante ai punti natali; Giove/Venere/Sole in X casa RS come
segnali alternativi) al posto del sistema stelline V2 come criterio di
ordinamento principale — V2 resta visibile e usato come tie-break. Le RSM/
RL prive di qualunque segnale positivo in X casa vengono SEMPRE escluse
(UX-0018: nessun esito "malefico segnalato" o "neutro" viene più
mostrato); se la ricerca non trova nulla, viene mostrato un messaggio
dedicato invece della tabella vuota. Attiva su tutte e tre le modalità di
ricerca (standard, griglia/area geografica/fascia oraria, RL). Le altre
condizioni non elencate in questa sezione restano invariate. Dettagli
completi in `docs/HANDOVER_OPERATIVO_astrolab.md`, voci 2026-08-28 e
2026-08-29 (2)/(3).

---

# Ricerca RSM/RL — condizione Amore (UX-0016/UX-0017)

Per la condizione Amore, con `MYASTRAL_ALIGNMENT_MODE` attivo, la ricerca
applica una gerarchia dedicata a 5 livelli (Venere, poi Giove — entrambi
con bonus se entro 1,5° dalla cuspide — poi Sole, in V o VII casa RS, pari
peso tra le due case) al posto del sistema stelline V2 come criterio di
ordinamento principale — V2 resta visibile e usato come tie-break. Nessun
elemento ASC (a differenza di Decima). Le RSM/RL prive di qualunque
segnale positivo in V/VII casa vengono SEMPRE escluse (UX-0017); se la
ricerca non trova nulla, viene mostrato un messaggio dedicato invece della
tabella vuota. Attiva su tutte e tre le modalità di ricerca (standard,
griglia/area geografica/fascia oraria, RL). Dettagli completi in
`docs/HANDOVER_OPERATIVO_astrolab.md`, voce 2026-08-29 (1).

---

# Ricerca RSM/RL — condizione Lavoro (UX-0019)

Per la condizione Lavoro, con `MYASTRAL_ALIGNMENT_MODE` attivo, la ricerca
applica una gerarchia dedicata a 5 livelli (Giove, poi Venere — entrambi
con bonus se entro 2,5° dalla cuspide, come Decima — poi Sole, in VI o X
casa RS, pari peso tra le due case) al posto del sistema stelline V2 come
criterio di ordinamento principale — V2 resta visibile e usato come tie-
break. Vincolo aggiuntivo specifico di questa sola condizione (non
condiviso con Amore/Decima né con Salute, pur condividendo il settore VI):
la Regola 33 ufficiale (Saturno prevale sempre su Giove/Venere/Sole) qui
si applica alla lettera — un benefico nella STESSA casa di Saturno viene
neutralizzato come segnale per quella casa, non per l'intera RSM/RL, che
resta valutata sull'eventuale segnale rimasto nell'altra casa. Le RSM/RL
prive di qualunque segnale positivo residuo vengono SEMPRE escluse
(stesso principio UX-0017 di Amore/Decima). Attiva fin da subito su tutte
e tre le modalità di ricerca (standard, griglia/area geografica/fascia
oraria, RL). Dettagli completi in `docs/HANDOVER_OPERATIVO_astrolab.md`,
voce 2026-08-29 (4).

---

# Ricerca RSM/RL — condizione Salute (UX-0020)

Per la condizione Salute, con `MYASTRAL_ALIGNMENT_MODE` attivo, la ricerca
applica una gerarchia dedicata a 8 livelli (Giove, poi Venere — Sole
escluso, a differenza di Amore/Decima/Lavoro — entrambi con bonus se entro
1,5° dalla cuspide, come Amore) al posto del sistema stelline V2 come
criterio di ordinamento principale — V2 resta visibile e usato come
tie-break. Priorità di CASA (non di solo pianeta, unica tra le quattro
condizioni con gerarchia): VI è la casa principale, I e XII sono pari peso
tra loro ma subordinate a VI. A differenza di Decima/Amore/Lavoro,
`verificaCondizioneSalute()` NON è stata trasformata in un rilevatore
geometrico puro: resta invariata, con i suoi 5 passaggi proprietari di
veto (tolleranza pre-ingresso ampliata a 4° per malefici in I/VI/XII,
scudo benefico in I casa, esclusione assoluta del Sole in XII, rafforzamento
ASC natale, protezione universale Giove/Venere) — è l'unico filtro di
validità/esclusione per Salute; la gerarchia a livelli ordina solo le RSM
che l'hanno già superata. La Regola 33 ufficiale (Saturno prevale sempre)
non richiede un meccanismo dedicato qui: il Passo 1 di
`verificaCondizioneSalute()` esclude già Saturno da I/VI/XII in modo
assoluto, più severo di quanto richiederebbe la Regola 33 stessa. Attiva
fin da subito su tutte e tre le modalità di ricerca (standard,
griglia/area geografica/fascia oraria, RL). Dettagli completi in
`docs/HANDOVER_OPERATIVO_astrolab.md`, voce 2026-08-30.

---

# Ricerca RL

La Ricerca RL (Rivoluzioni Lunari, base mensile) estende alla Luna la stessa ricerca geografica per condizione già disponibile per le RSM.

Riusa integralmente il motore di valutazione esistente (RuleEngine, Rule Map di esclusione radicale, FiltroEsclusione, deduplicazione geografica).

È disponibile in una pagina dedicata (Ricerca Località RL), separata dalla ricerca RSM per non introdurre accoppiamento tra i due motori.

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
