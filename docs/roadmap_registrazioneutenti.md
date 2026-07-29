# ROADMAP — Registrazione e gestione utenti

## Riferimento operativo

Questa roadmap è dedicata esclusivamente alla nuova feature di registrazione e gestione degli utenti normali e TEST.

Il workflow operativo, le regole permanenti di sviluppo e la cronologia generale del progetto restano definiti in:

- `docs/HANDOVER_OPERATIVO_astrolab.md`

Prima di riprendere questa feature devono essere letti, nell’ordine:

1. `docs/START_HERE.md`
2. `docs/ADR_INDEX_ASTROLAB.md`
3. `docs/ROADMAP.md`
4. `docs/HANDOVER_OPERATIVO_astrolab.md`
5. `docs/roadmap_registrazioneutenti.md`

La presente roadmap non sostituisce l’HANDOVER principale e contiene soltanto lo storico, le analisi, le decisioni e i prossimi passi relativi a questa feature.

---

## Stato attuale

**Fase:** analisi completata.

**Codice applicativo modificato:** nessuno.

**Documentazione modificata:** creazione e allineamento della presente roadmap.

**Prossimo passo:** definizione e implementazione dello schema dati per gli utenti TEST.

---

## Obiettivo della feature

Estendere la gestione utenti di ASTROLAB introducendo una distinzione configurabile tra:

- utenti normali;
- utenti TEST.

Un nuovo utente dovrà iniziare in modalità TEST e potrà successivamente essere approvato e configurato da un amministratore.

La feature è trasversale e interessa:

- database;
- autenticazione;
- amministrazione utenti;
- gestione soggetti;
- ricerca geografica;
- interfaccia utente;
- API;
- documentazione;
- test.

---

## Requisiti funzionali iniziali

Gli utenti TEST devono avere, come configurazione iniziale:

- massimo un soggetto;
- impossibilità di creare soggetti oltre il limite assegnato;
- ricerca a griglia disabilitata;
- ricerca geografica limitata agli aeroporti;
- espansione dinamica dell’orbe disabilitata.

L’amministratore deve poter:

- identificare gli utenti TEST;
- approvare un utente;
- trasformarlo in utente normale;
- configurare il limite massimo dei soggetti;
- abilitare o disabilitare le funzionalità soggette a restrizione;
- mantenere attivo o disattivo l’account indipendentemente dallo stato TEST.

---

## Analisi effettuate

### Gestione utenti amministrativa

File analizzato:

- `www/admin_utenti.php`

Funzioni esistenti individuate:

- creazione utente;
- modifica anagrafica;
- reset password;
- attivazione e disattivazione;
- modifica ruolo;
- eliminazione;
- elenco utenti;
- modali di creazione e modifica.

Conclusione:

L’interfaccia amministrativa non dispone attualmente di campi o azioni per gestire utenti TEST, limiti soggetti o permessi funzionali.

---

### Autenticazione e modello utente

File analizzato:

- `www/includes/Auth.php`

Funzioni esistenti individuate:

- autenticazione;
- creazione utente;
- aggiornamento utente;
- lettura utente;
- elenco utenti;
- gestione ruoli;
- gestione stato attivo;
- verifica proprietà dei soggetti.

Conclusione:

Il modello corrente non espone uno stato TEST, limiti quantitativi o permessi per singola funzionalità.

---

### Database

È stata verificata la struttura corrente della tabella `utenti`.

Campi presenti:

- `id`;
- `username`;
- `email`;
- `password_hash`;
- `ruolo`;
- `attivo`;
- `created_at`;
- `ultimo_accesso`;
- `nome_completo`;
- `telefono`;
- `note`.

Conclusione:

La tabella non contiene ancora campi dedicati a:

- stato TEST;
- approvazione;
- limite soggetti;
- abilitazione ricerca a griglia;
- abilitazione località;
- abilitazione espansione dinamica dell’orbe.

---

### Gestione soggetti

File analizzato:

- `www/api/soggetti_api.php`

Comportamento corrente:

- ogni soggetto viene associato all’utente autenticato;
- gli utenti non amministratori vedono soltanto i propri soggetti;
- inserimento, modifica ed eliminazione verificano l’utente proprietario;
- non esiste un controllo sul numero massimo di soggetti.

Conclusione:

Il limite deve essere applicato obbligatoriamente lato server prima dell’`INSERT`, con risposta JSON esplicita in caso di superamento.

---

### Ricerca geografica standard

File analizzato:

- `www/api/ricerca_stream_api.php`

Comportamento individuato:

- il parametro `tipo_localita` accetta attualmente `aeroporti` e `localita`;
- non esiste un controllo dei permessi dell’utente sulla tipologia richiesta.

Conclusione:

Per gli utenti TEST il backend deve forzare o accettare esclusivamente la modalità aeroporti, indipendentemente dai parametri inviati dal browser.

---

### Ricerca a griglia

File analizzati:

- `www/api/ricerca_griglia_api.php`
- `www/ricerca.php`

Comportamento individuato:

- la ricerca a griglia può essere avviata dal frontend;
- l’endpoint verifica l’autenticazione ma non uno specifico permesso funzionale.

Conclusione:

La ricerca a griglia deve essere:

- nascosta o disabilitata nell’interfaccia per gli utenti non autorizzati;
- bloccata obbligatoriamente anche lato server.

---

### Ricerca cuspidi ed espansione dell’orbe

File analizzati:

- `www/api/cuspidi_search_api.php`
- `www/ricerca.php`

Comportamento individuato:

- l’espansione dinamica dell’orbe è gestita principalmente dal frontend;
- nella ricerca a griglia delle cuspidi la tolleranza può essere aumentata dinamicamente;
- l’endpoint cuspidi non conosce attualmente il permesso dell’utente relativo all’espansione.

Conclusione:

Il frontend deve disabilitare il controllo per gli utenti TEST e il backend deve comunque rifiutare o normalizzare parametri non consentiti.

---

## Decisioni preliminari

Le restrizioni non devono basarsi soltanto sul frontend.

Ogni limitazione deve essere applicata anche lato server per impedire chiamate API dirette o parametri manipolati.

Lo stato TEST non deve essere confuso con:

- il ruolo applicativo;
- lo stato attivo dell’account.

Il ruolo continuerà a rappresentare i privilegi amministrativi.

Lo stato attivo continuerà a determinare se l’account può autenticarsi.

Lo stato TEST rappresenterà invece il profilo funzionale e le relative limitazioni.

---

## File analizzati

- `www/admin_utenti.php`
- `www/includes/Auth.php`
- `www/api/soggetti_api.php`
- `www/api/ricerca_stream_api.php`
- `www/api/ricerca_griglia_api.php`
- `www/api/cuspidi_search_api.php`
- `www/ricerca.php`
- `docs/README_ASTROLAB.md`
- `docs/START_HERE.md`
- `docs/HANDOVER_OPERATIVO_astrolab.md`

---

## File previsti per l’implementazione

L’elenco sarà aggiornato durante lo sviluppo.

File applicativi previsti:

- `www/includes/Auth.php`
- `www/admin_utenti.php`
- `www/api/soggetti_api.php`
- `www/api/ricerca_stream_api.php`
- `www/api/ricerca_griglia_api.php`
- `www/api/cuspidi_search_api.php`
- `www/ricerca.php`

File ulteriori da individuare:

- schema SQL iniziale;
- directory delle migrazioni o degli aggiornamenti database;
- eventuale pagina pubblica di registrazione;
- test esistenti relativi ad autenticazione, utenti, soggetti e ricerca.

Documenti da aggiornare al completamento:

- `docs/roadmap_registrazioneutenti.md`
- `docs/HANDOVER_OPERATIVO_astrolab.md`
- `docs/START_HERE.md`
- `docs/README_ASTROLAB.md`
- eventuale `docs/ROADMAP.md`

---

## Piano di implementazione

### Fase 1 — Verifica infrastruttura database

- individuare il file sorgente dello schema della tabella `utenti`;
- individuare il metodo ufficiale usato dal progetto per aggiornare il database;
- definire i nuovi campi e i relativi valori predefiniti;
- garantire compatibilità con gli utenti già esistenti;
- preparare lo script di modifica;
- verificare schema e dati.

### Fase 2 — Modello utente e Auth

- aggiornare creazione, lettura e modifica degli utenti;
- aggiungere helper centralizzati per stato TEST e permessi;
- evitare duplicazioni dei controlli nei singoli endpoint;
- preservare il comportamento attuale per amministratori e utenti esistenti;
- aggiungere o aggiornare i test.

### Fase 3 — Amministrazione utenti

- mostrare chiaramente lo stato TEST;
- aggiungere campi per limite soggetti e funzionalità;
- consentire approvazione e conversione a utente normale;
- validare i valori ricevuti lato server;
- verificare creazione e modifica utenti.

### Fase 4 — Limite soggetti

- contare i soggetti dell’utente prima dell’inserimento;
- confrontare il conteggio con il limite configurato;
- bloccare l’inserimento oltre soglia;
- restituire un errore JSON comprensibile;
- non limitare gli amministratori salvo decisione esplicita;
- aggiungere test dedicati.

### Fase 5 — Restrizioni ricerca lato server

- bloccare la ricerca a griglia quando non autorizzata;
- limitare la ricerca geografica agli aeroporti;
- bloccare o normalizzare l’espansione dinamica dell’orbe;
- restituire errori coerenti;
- verificare chiamate API dirette.

### Fase 6 — Restrizioni interfaccia

- nascondere o disabilitare i controlli non disponibili;
- impedire l’avvio di operazioni non consentite;
- mostrare messaggi chiari sulle limitazioni TEST;
- mantenere il server come fonte definitiva dell’autorizzazione.

### Fase 7 — Registrazione nuovi utenti

- individuare o creare il flusso pubblico di registrazione;
- assegnare automaticamente il profilo TEST;
- applicare i valori predefiniti della feature;
- validare username, email e password;
- evitare escalation di ruolo o permessi dal client;
- aggiungere test del flusso completo.

### Fase 8 — Regressione e documentazione

- eseguire lint PHP sui file modificati;
- eseguire `git diff --check`;
- eseguire i test specifici;
- eseguire la regressione disponibile;
- aggiornare questa roadmap;
- registrare la sintesi nell’HANDOVER principale;
- aggiornare i documenti ufficiali che descrivono le funzionalità della piattaforma.

---

## Verifiche obbligatorie

Per ogni iterazione devono essere eseguite, quando applicabili:

- lettura preventiva del file;
- modifica mediante script;
- controllo del diff;
- `php -l` sui file PHP modificati;
- `git diff --check`;
- test funzionali specifici;
- regressione disponibile;
- aggiornamento documentale;
- commit Git.

---

## Storico della feature

### 2026-07-29 — Analisi iniziale

- definito il requisito generale della gestione utenti TEST;
- stabilito che i nuovi utenti inizieranno in modalità TEST;
- stabilito il limite iniziale di un soggetto;
- stabilita la disabilitazione della ricerca a griglia;
- stabilita la limitazione della ricerca alle sole modalità aeroportuali;
- stabilita la disabilitazione dell’espansione dinamica dell’orbe;
- analizzati i principali file backend e frontend coinvolti;
- verificata l’assenza dei campi necessari nella tabella `utenti`;
- nessuna modifica applicativa eseguita;
- creata la roadmap specifica della feature.

---

## Prossimo passo

Individuare il file ufficiale che definisce lo schema PostgreSQL e il meccanismo utilizzato dal progetto per le migrazioni o gli aggiornamenti del database.

Solo dopo questa verifica dovrà essere definito lo schema definitivo dei nuovi campi utente.

---

## Commit

Nessun commit applicativo ancora eseguito.

Il primo commit previsto riguarda esclusivamente la creazione e l’allineamento di questa roadmap.
