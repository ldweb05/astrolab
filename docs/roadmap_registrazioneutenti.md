# ROADMAP — Registrazione, piani e gestione utenti

## Riferimento operativo

Questa roadmap è dedicata esclusivamente alla nuova feature di registrazione, gestione utenti, piani applicativi e limiti funzionali.

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

**Fase:** macro-funzionalità in corso. Le Fasi 1–4 e 6–13 risultano implementate nei limiti documentati. Sbloccata la registrazione pubblica: l'account viene attivato automaticamente in assenza di invio email reale (previsto ma non attivato fino al deployment su VPS), con azione amministrativa di verifica manuale per gli eventuali casi residui in pending_email. La Fase 5 — amministrazione utenti, gestione del piano Supporter e limiti personalizzati — deve essere completata.

**Codice applicativo modificato:** aggiornati `www/includes/Auth.php` (registraUtentePubblico attiva subito l'account; nuovo metodo verificaManualmente), `www/registrazione.php` (messaggio di successo aggiornato), `www/login.php` (aggiunto link a registrazione.php), `www/admin_utenti.php` (azione e pulsante di verifica manuale, badge stato "Da verificare").

**Documentazione modificata:** roadmap aggiornata con lo sblocco della registrazione pubblica e la decisione di rimandare l'invio email reale al deployment su VPS.

**Prossimo passo:** completare la Fase 5 con modifica amministrativa del piano, gestione delle donazioni, validità annuale Supporter, scadenza e rinnovo, limiti soggetti personalizzati e relativi test.

---

## Obiettivo della feature

Introdurre in ASTROLAB un sistema completo di registrazione e gestione degli utenti basato su:

- registrazione pubblica;
- verifica dell’indirizzo email;
- account personale;
- piano gratuito iniziale;
- piano Supporter;
- limiti quantitativi configurabili;
- permessi funzionali centralizzati;
- gestione amministrativa degli account;
- protezione server-side di ogni funzionalità soggetta a limite.

La feature è trasversale e interessa:

- database;
- autenticazione;
- amministrazione utenti;
- gestione soggetti;
- salvataggio delle ricerche;
- ricerca geografica;
- ricerca a griglia;
- Comparator;
- Annual Report;
- stampa ed esportazione;
- interfaccia utente;
- API;
- documentazione;
- test.

---

## Decisione di prodotto

Il precedente concetto di “utente TEST” viene abbandonato.

Ogni nuovo utente registrato avrà un account personale con piano iniziale `free`.

Il termine `TEST` resterà eventualmente utilizzabile soltanto per account tecnici o collaudatori interni e non farà parte del normale percorso degli utenti.

Il modello applicativo dovrà distinguere chiaramente:

### Ruolo

Determina i privilegi amministrativi.

Valori iniziali previsti:

- `admin`;
- `user`.

### Stato account

Determina se l’utente può autenticarsi e usare il servizio.

Valori iniziali previsti:

- `pending_email`;
- `active`;
- `suspended`.

### Piano

Determina limiti e funzionalità disponibili.

Valori iniziali previsti:

- `free`;
- `supporter`.

### Eventuale account tecnico o beta tester

Dovrà essere rappresentato da un flag o da un attributo separato e non dovrà sostituire ruolo, stato o piano.

---

## Flusso previsto per i nuovi utenti

1. L’utente apre la pagina di registrazione.
2. Inserisce username, email e password.
3. Il sistema crea l’account con:
   - ruolo `user`;
   - stato `pending_email`;
   - piano `free`.
4. Il sistema invia un link di verifica email con token a scadenza.
5. Dopo la verifica, lo stato diventa `active`.
6. L’utente può utilizzare le funzionalità comprese nel piano gratuito.
7. Un eventuale sostegno al progetto abilita il piano `supporter`.
8. L’amministratore può sospendere l’account, modificare il piano o applicare eccezioni configurate.

Non è prevista l’approvazione manuale obbligatoria da parte dell’amministratore per ogni nuova registrazione.

---

## Piano gratuito iniziale

Il piano gratuito deve consentire all’utente di comprendere realmente il valore di ASTROLAB senza rendere illimitate le funzioni che generano maggiore utilizzo di risorse, archivio o valore professionale.

### Limiti proposti

| Funzionalità | Piano `free` | Motivazione |
|---|---:|---|
| Soggetti personali | massimo 2 | Permette di utilizzare il proprio profilo e un secondo soggetto senza trasformare il piano gratuito in un archivio professionale. |
| Ricerche salvate | massimo 10 | Consente un uso concreto e continuativo, creando al tempo stesso un incentivo naturale al passaggio di piano. |
| Confronto RSM | massimo 2 RSM | Fa conoscere il Comparator senza concedere il confronto completo fino a 3 risultati. |
| Confronto Rilocazioni | massimo 2 risultati | Mantiene coerenza con il Comparator RSM. |
| RSM calcolabili | nessun limite commerciale rigido | Il calcolo base rappresenta il cuore dell’app e deve restare realmente utilizzabile. |
| Esplorazione OpenStreetMap | abilitata | È una funzione distintiva e deve essere disponibile per mostrare il valore dell’applicazione. |
| Aggiornamento RSM spostando il cursore | abilitato | È parte essenziale dell’esperienza di ricerca e non deve essere bloccato. |
| Collegamento Rome2Rio | abilitato | È una funzione di servizio con basso valore come barriera commerciale. |
| Ricerca aeroporti | abilitata | Mantiene disponibile la modalità storica e più semplice. |
| Ricerca località per nazione | disabilitata | Il piano gratuito può visualizzare l’elenco delle nazioni, ma la ricerca delle località è riservata agli utenti del piano Supporter. |
| Ricerca a griglia | disabilitata | È una funzione avanzata e potenzialmente più onerosa; rappresenta un buon elemento distintivo del piano Supporter. |
| Espansione dinamica dell’orbe | disabilitata | È una funzione avanzata che amplia automaticamente la ricerca e può aumentare il carico computazionale. |
| Annual Report visualizzabile | abilitato | Il report annuale è una funzione centrale e deve poter essere provata integralmente. |
| Stampa Annual Report | massimo 3 al mese | Consente un utilizzo concreto evitando esportazioni intensive o professionali nel piano gratuito. |
| Esportazione PDF Annual Report | massimo 3 al mese | Usa lo stesso contatore della stampa per evitare duplicazioni di logica. |
| Cronologia completa delle ricerche | ultime 10 ricerche salvate | Coerente con il limite di archivio. |
| Note personali sulle ricerche | abilitate | Aumentano il valore dell’archivio senza costi significativi. |
| Duplicazione di una ricerca salvata | abilitata entro il limite di 10 | Utile per creare varianti senza introdurre un nuovo privilegio. |

### Regola importante

Il raggiungimento di un limite non deve eliminare o nascondere i dati già creati.

Esempi:

- al raggiungimento di 10 ricerche salvate, l’utente può continuare a consultarle, modificarle ed eliminarle, ma non crearne una nuova;
- se il piano viene ridotto e l’utente possiede più soggetti del limite, i soggetti esistenti restano consultabili ma non può crearne altri;
- i report già generati restano accessibili secondo le normali regole di conservazione del progetto.

---

## Piano sostenitore iniziale

Il piano `supporter` è destinato agli utenti che sostengono economicamente il progetto e utilizzano ASTROLAB in modo più continuativo o avanzato.

### Funzionalità proposte

| Funzionalità | Piano `supporter` |
|---|---:|
| Soggetti personali | illimitati con criterio di uso corretto |
| Ricerche salvate | illimitate con criterio di uso corretto |
| Confronto RSM | fino a 3 RSM |
| Confronto Rilocazioni | fino a 3 risultati |
| Esplorazione OpenStreetMap | abilitata |
| Collegamento Rome2Rio | abilitato |
| Ricerca aeroporti | abilitata |
| Ricerca località per nazione | abilitata |
| Ricerca a griglia | abilitata |
| Espansione dinamica dell’orbe | abilitata |
| Annual Report | abilitato |
| Stampa ed esportazione PDF | illimitate con criterio di uso corretto |
| Cronologia | completa |
| Funzioni avanzate future | abilitate secondo configurazione |

Il termine “illimitato” dovrà essere accompagnato da una politica tecnica di uso corretto per proteggere l’infrastruttura da automazioni, abusi o carichi anomali.

---

## Limiti tecnici e limiti commerciali

I limiti commerciali determinano ciò che il piano consente.

I limiti tecnici proteggono invece la stabilità del sistema e devono poter essere applicati a tutti gli utenti, compresi i sostenitori.

Dovranno essere previsti almeno:

- rate limit sulle richieste di login;
- rate limit sulla registrazione;
- rate limit sulla richiesta di reset password;
- limite alle richieste ripetute verso gli endpoint di ricerca;
- protezione da chiamate API parallele anomale;
- durata massima delle sessioni;
- scadenza dei token di verifica email e recupero password;
- dimensione massima dei dati salvati nei campi note;
- logging degli eventi di sicurezza rilevanti.

I valori tecnici definitivi dovranno essere scelti dopo avere misurato il comportamento reale dell’applicazione e non dovranno essere hardcoded nei singoli endpoint.

---

## Configurazione centralizzata dei permessi

I controlli non devono essere distribuiti come condizioni isolate nei vari file.

Dovrà esistere un unico punto applicativo autorizzato a rispondere a domande come:

- quanti soggetti può creare l’utente;
- quante ricerche può salvare;
- quante RSM può confrontare;
- se può usare la ricerca a griglia;
- se può usare l’espansione dinamica dell’orbe;
- se può stampare o esportare un nuovo report;
- se ha raggiunto un limite mensile.

Sono previsti helper centralizzati, ad esempio:

- `getUserPlan()`;
- `getUserLimits()`;
- `canCreateSubject()`;
- `canSaveSearch()`;
- `canUseGridSearch()`;
- `canUseDynamicOrbExpansion()`;
- `canCompareResults(int $count)`;
- `canExportAnnualReport()`;
- `getRemainingQuota(string $feature)`.

La denominazione definitiva dovrà rispettare lo stile corrente del progetto.

---

## Modello dati preliminare

La struttura definitiva dovrà essere formalizzata prima della migrazione.

### Tabella `utenti`

Campi esistenti:

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

Campi o adeguamenti previsti:

- `account_status`;
- `email_verified_at`;
- `plan_id` oppure `plan_code`;
- `is_beta_tester`;
- `updated_at`;
- eventuale data di sospensione;
- eventuale motivo amministrativo della sospensione.

Il campo `attivo` dovrà essere valutato per evitare duplicazioni con `account_status`. Se mantenuto per retrocompatibilità, dovrà avere una semantica chiara e non concorrente.

### Tabella `piani`

Campi iniziali proposti:

- `id`;
- `code`;
- `name`;
- `description`;
- `is_active`;
- `created_at`;
- `updated_at`.

Valori iniziali:

- `free`;
- `supporter`.

### Tabella `piano_limiti`

Campi iniziali proposti:

- `id`;
- `plan_id`;
- `feature_code`;
- `limit_value`;
- `enabled`;
- `period_type`;
- `created_at`;
- `updated_at`.

Esempi di `feature_code`:

- `subjects_max`;
- `saved_searches_max`;
- `comparison_rs_max`;
- `comparison_relocation_max`;
- `grid_search_enabled`;
- `dynamic_orb_enabled`;
- `annual_report_exports_max`;
- `annual_report_exports_period`.

### Tabella opzionale `utente_override`

Permette all’amministratore di concedere o ridurre un limite per un singolo utente senza creare un nuovo piano.

Campi proposti:

- `id`;
- `user_id`;
- `feature_code`;
- `limit_value`;
- `enabled`;
- `expires_at`;
- `note`;
- `created_at`;
- `updated_at`.

### Tabella quote o utilizzi

Per i limiti periodici, come le esportazioni mensili, dovrà essere previsto un conteggio affidabile.

Possibili campi:

- `id`;
- `user_id`;
- `feature_code`;
- `period_start`;
- `period_end`;
- `usage_count`;
- `updated_at`.

In alternativa il conteggio potrà essere derivato da una tabella eventi, se già compatibile con l’architettura del progetto.

### Token di sicurezza

Dovranno essere previste strutture dedicate per:

- verifica email;
- recupero password;
- eventuale revoca delle sessioni.

I token non dovranno essere conservati in chiaro quando tecnicamente evitabile.

---

## Requisiti funzionali aggiornati

### Registrazione

Il sistema deve:

- validare username, email e password;
- impedire duplicazioni di username ed email;
- non accettare ruolo, piano o permessi dal client;
- creare sempre il nuovo account con ruolo `user`;
- assegnare sempre il piano `free`;
- creare lo stato iniziale `pending_email`;
- inviare un token di verifica con scadenza;
- attivare l’account dopo la verifica.

### Accesso

Il sistema deve:

- consentire l’accesso solo agli account autorizzati;
- distinguere chiaramente email non verificata, account sospeso e credenziali errate;
- evitare messaggi che facilitino l’enumerazione degli account;
- aggiornare `ultimo_accesso` soltanto dopo autenticazione riuscita;
- prevedere protezione dai tentativi ripetuti.

### Gestione soggetti

Il sistema deve:

- associare ogni soggetto al proprietario;
- contare i soggetti prima dell’inserimento;
- applicare il limite effettivo derivato da piano ed eventuali override;
- bloccare l’inserimento oltre soglia;
- restituire un errore JSON esplicito e coerente;
- preservare l’accesso ai soggetti già presenti anche dopo un eventuale downgrade.

### Ricerche salvate

Il sistema deve:

- associare ogni ricerca all’utente;
- applicare il limite prima del salvataggio;
- consentire modifica, consultazione ed eliminazione delle ricerche esistenti;
- impedire la creazione oltre il limite;
- impedire l’accesso alle ricerche di altri utenti;
- preservare i dati in caso di downgrade.

### Ricerca geografica

Il piano gratuito deve poter utilizzare:

- ricerca aeroporti;
- visualizzazione dell’elenco delle nazioni, senza poterle utilizzare per avviare una ricerca delle località;
- OpenStreetMap;
- spostamento del cursore con aggiornamento della RSM;
- collegamento Rome2Rio.

La ricerca delle località, la ricerca a griglia e l’espansione dinamica dell’orbe devono essere abilitate soltanto quando consentite dal piano o da un override.

Quando un utente del piano gratuito tenta di selezionare o utilizzare una funzione non inclusa, l’interfaccia deve mostrare un alert con il messaggio «Questa funzione è riservata agli utenti del piano Supporter.»

Il controllo lato server deve comunque impedire l’utilizzo delle funzioni non autorizzate anche tramite chiamate API dirette.

### Comparator

Il backend deve verificare il numero di elementi richiesto.

Limiti iniziali:

- piano `free`: massimo 2 risultati;
- piano `supporter`: massimo 3 risultati.

Il controllo deve essere applicato lato server anche se il frontend limita già la selezione.

### Annual Report, stampa e PDF

Il piano gratuito può visualizzare il report annuale.

Stampa ed esportazione condividono una quota iniziale di 3 operazioni al mese.

Il backend deve:

- verificare la quota prima della generazione;
- registrare l’utilizzo soltanto dopo un’operazione completata correttamente;
- evitare doppi conteggi causati da retry o richieste duplicate;
- restituire la quota residua quando utile all’interfaccia.

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

L’interfaccia amministrativa non dispone attualmente di campi o azioni per gestire piano, stato di verifica, limiti, override o permessi funzionali.

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

Il modello corrente non espone stato di verifica email, piano, limiti quantitativi o permessi per singola funzionalità.

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

La tabella non contiene ancora campi o relazioni dedicati a:

- verifica email;
- stato account strutturato;
- piano applicativo;
- limiti quantitativi;
- permessi funzionali;
- override amministrativi;
- quote periodiche.

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

- il parametro `tipo_localita` distingue aeroporti e località;
- non esiste un controllo dei permessi dell’utente sulla tipologia richiesta.

Conclusione:

Entrambe le modalità restano disponibili nel piano gratuito. Il backend dovrà comunque ottenere centralmente i permessi dell’utente per supportare future variazioni di piano senza affidarsi ai parametri inviati dal browser.

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

Il frontend deve disabilitare il controllo per gli utenti non autorizzati e il backend deve comunque rifiutare o normalizzare parametri non consentiti.

---

## Decisioni architetturali preliminari

Le restrizioni non devono basarsi soltanto sul frontend.

Ogni limitazione deve essere applicata anche lato server per impedire chiamate API dirette o parametri manipolati.

Ruolo, stato account e piano devono restare concetti distinti.

I limiti non devono essere hardcoded in ogni endpoint.

Il server rappresenta la fonte definitiva dell’autorizzazione.

Gli amministratori non devono essere soggetti ai limiti commerciali, salvo decisione esplicita, ma restano soggetti alle protezioni tecniche e di sicurezza.

Questa feature introduce una decisione architetturale trasversale e dovrà essere formalizzata mediante un nuovo ADR prima dell’implementazione definitiva.

Titolo proposto:

`ADR-016 — Modello centralizzato di registrazione, piani e permessi utente`

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
- endpoint o pagina pubblica di registrazione;
- endpoint di verifica email;
- endpoint di recupero password;
- componenti per la lettura centralizzata di piani e permessi;
- endpoint relativi a Comparator, Annual Report, stampa e PDF;
- componenti relativi alle ricerche salvate.

File ulteriori da individuare:

- schema SQL iniziale;
- directory delle migrazioni o degli aggiornamenti database;
- sistema di invio email;
- gestione attuale delle sessioni;
- gestione delle ricerche salvate;
- test esistenti relativi ad autenticazione, utenti, soggetti, Comparator, report e ricerca.

Documenti da aggiornare al completamento:

- `docs/roadmap_registrazioneutenti.md`
- `docs/HANDOVER_OPERATIVO_astrolab.md`
- `docs/START_HERE.md`
- `docs/README_ASTROLAB.md`
- `docs/ROADMAP.md`
- `docs/ADR_INDEX_ASTROLAB.md`
- nuovo ADR dedicato.

---

## Piano di implementazione

### Fase 0 — ADR e contratti funzionali

- formalizzare il modello ruolo, stato e piano;
- formalizzare i limiti iniziali;
- formalizzare il comportamento in caso di downgrade;
- formalizzare la precedenza tra piano e override utente;
- formalizzare il significato di “illimitato con uso corretto”;
- creare ADR-016;
- aggiornare l’indice ADR.

### Fase 1 — Verifica infrastruttura database

- individuare il file sorgente dello schema della tabella `utenti`;
- individuare il metodo ufficiale usato dal progetto per aggiornare il database;
- verificare il modello delle chiavi esterne;
- verificare il supporto transazionale;
- definire tabelle, campi, vincoli e valori predefiniti;
- garantire compatibilità con gli utenti già esistenti;
- assegnare agli utenti esistenti uno stato e un piano coerenti;
- preparare lo script di modifica;
- verificare schema e dati.

### Fase 2 — Modello utente, piani e permessi

- aggiornare creazione, lettura e modifica degli utenti;
- aggiungere helper centralizzati per piano, limiti e permessi;
- implementare la precedenza degli override;
- evitare duplicazioni dei controlli nei singoli endpoint;
- preservare il comportamento attuale per amministratori e utenti esistenti;
- aggiungere o aggiornare i test.

### Fase 3 — Registrazione pubblica

- creare pagina o endpoint di registrazione;
- validare username, email e password;
- assegnare automaticamente ruolo `user`;
- assegnare automaticamente piano `free`;
- creare account `pending_email`;
- impedire escalation di ruolo o piano dal client;
- aggiungere protezione da registrazioni ripetute;
- aggiungere test del flusso.

### Fase 4 — Verifica email e recupero password ✅ completata

Implementato:

- token sicurezza crittograficamente casuali;
- memorizzazione sicura tramite hash SHA-256;
- scadenza automatica dei token;
- verifica email con attivazione account;
- nuova richiesta del link di verifica;
- richiesta e conferma reset password;
- invalidazione dei token dopo l’utilizzo;
- test automatici del flusso completo.

Restano attività infrastrutturali future:

- integrazione del servizio SMTP/mail reale;
- gestione template email e notifiche.

### Fase 5 — Amministrazione utenti ⚠️ non completata

#### Decisioni definitive

La gestione amministrativa deve distinguere chiaramente:

1. configurazione globale dei piani;
2. piano corrente del singolo utente;
3. eventuali eccezioni personali;
4. accessi speciali gratuiti e permanenti.

La configurazione globale deve consentire all'admin di definire:

- importo minimo della donazione necessario per attivare o rinnovare il piano `supporter`;
- durata standard del piano `supporter`, inizialmente prevista in un anno;
- limite soggetti predefinito del piano `free`;
- limite soggetti predefinito del piano `supporter`;
- altri limiti commerciali già gestiti tramite `piano_limiti`;
- attivazione o disattivazione dei singoli piani;
- disponibilità dei piani per nuove assegnazioni;
- eventuali nuovi piani futuri, senza distribuire controlli hardcoded negli endpoint.

La disattivazione di un piano deve impedire nuove assegnazioni, ma non deve cancellare utenti, soggetti, ricerche o altri dati già esistenti. L'admin deve poter migrare gli utenti interessati verso un altro piano tramite un'operazione esplicita e controllata.

L'importo della donazione e il limite soggetti sono dati indipendenti.

L'importo versato non deve determinare automaticamente il numero di soggetti assegnato. L'admin decide separatamente:

- se la donazione soddisfa il minimo richiesto;
- il piano da assegnare;
- la data di inizio;
- la data di scadenza;
- l'eventuale limite soggetti personalizzato.

Il limite effettivo dell'utente deve essere determinato con la seguente precedenza:

1. accesso speciale permanente, quando configurato;
2. limite personalizzato del singolo utente;
3. limite configurato per il piano corrente.

Nessun limite commerciale deve essere hardcoded negli endpoint applicativi. I valori devono essere letti dalla configurazione del piano o dalle eccezioni amministrative del singolo utente.

Le politiche commerciali di ASTROLAB devono essere completamente configurabili dall'interfaccia amministrativa. L'amministratore deve poter modificare importi minimi, durata dei piani, limiti standard, limiti personalizzati e disponibilità dei piani senza richiedere modifiche al codice o interventi manuali diretti sul database.

L'interfaccia amministrativa deve separare chiaramente:

1. configurazione globale dei piani e delle politiche commerciali;
2. gestione del singolo utente;
3. accessi speciali permanenti;
4. consultazione dello storico delle operazioni amministrative rilevanti.

Le modifiche globali non devono alterare retroattivamente o eliminare dati già creati. Quando un nuovo limite effettivo è inferiore all'utilizzo corrente, l'utente conserva l'accesso in lettura, modifica ed eliminazione dei dati esistenti, ma non può creare nuovi elementi fino al rientro nel limite.

L'admin deve inoltre poter concedere ad alcuni utenti speciali un accesso:

- completo;
- gratuito;
- permanente;
- senza donazione obbligatoria;
- senza scadenza annuale;
- indipendente dal normale ciclo del piano `supporter`.

Questo accesso speciale non deve trasformare l'utente in `admin` e non deve essere rappresentato tramite il ruolo. L'utente mantiene ruolo `user`, ma dispone delle funzionalità complete e dei limiti stabiliti dall'admin.

L'accesso speciale permanente deve poter essere:

- attivato manualmente;
- revocato manualmente;
- accompagnato da una motivazione o nota amministrativa;
- escluso dal ritorno automatico al piano `free`;
- distinto dai beta tester e dagli account tecnici;
- verificato sempre lato server.

Già implementato:

- visualizzazione di ruolo, stato account, piano e verifica email;
- sospensione e riattivazione coerente con `account_status`;
- modifica del ruolo amministrativo;
- validazione server-side dei ruoli;
- creazione amministrativa degli utenti.

Da completare per la chiusura definitiva:

- consentire all'admin di modificare il piano tra `free` e `supporter`;
- mantenere ruolo, stato account e piano come concetti separati;
- registrare l'importo della donazione che abilita il piano Supporter;
- consentire all'admin di definire la somma minima necessaria per ottenere o rinnovare il piano Supporter;
- registrare data di inizio e data di scadenza del periodo Supporter;
- prevedere validità annuale del piano Supporter;
- consentire rinnovo anticipato o successivo alla scadenza;
- riportare automaticamente l'utente al piano `free` alla scadenza, salvo rinnovo;
- mantenere consultabili i dati già creati dopo il ritorno al piano `free`;
- impedire nuove creazioni quando l'utente supera i nuovi limiti effettivi;
- consentire all'admin di impostare un limite soggetti personalizzato per ogni utente;
- consentire limiti personalizzati indipendenti dal piano standard;
- consentire all'admin di configurare i limiti predefiniti dei piani `free` e `supporter`;
- consentire all'admin di attivare o disattivare i piani;
- impedire l'assegnazione di un piano disattivato;
- preservare utenti e dati già esistenti quando un piano viene disattivato;
- consentire la migrazione amministrativa degli utenti da un piano disattivato a un piano attivo;
- predisporre il modello per eventuali nuovi piani futuri senza introdurre condizioni hardcoded negli endpoint;
- impedire valori numerici hardcoded nella logica applicativa dei limiti;
- mantenere indipendenti importo della donazione e limite soggetti;
- consentire l'attivazione di un accesso speciale completo, gratuito e permanente;
- consentire la revoca dell'accesso speciale permanente;
- impedire che l'accesso speciale modifichi automaticamente il ruolo dell'utente;
- escludere gli utenti con accesso speciale permanente dalla scadenza automatica Supporter;
- supportare configurazioni amministrative differenti;
- il limite soggetti deve poter essere deciso liberamente dall'amministratore indipendentemente dall'importo della donazione;
- supportare, a titolo esemplificativo:
  - utenti `free` con limiti differenti;
  - utenti `supporter` con limiti differenti;
  - utenti con limite personalizzato indipendente dal piano;
- definire chiaramente la precedenza tra limite del piano e limite personalizzato dell'utente;
- permettere la rimozione dell'override per ripristinare il limite standard del piano;
- mostrare nell'amministrazione:
  - piano corrente;
  - importo dell'ultima donazione;
  - inizio e scadenza Supporter;
  - limite soggetti standard;
  - limite soggetti personalizzato;
  - limite soggetti effettivo;
  - numero di soggetti attualmente creati;
- consentire all'admin di riportare manualmente un utente a `free`;
- consentire all'admin di rinnovare o modificare la scadenza Supporter;
- validare lato server importi, date, piano e limiti numerici;
- impedire l'attivazione ordinaria del piano `supporter` quando la donazione registrata è inferiore al minimo configurato;
- consentire l'accesso speciale permanente senza donazione soltanto tramite azione amministrativa esplicita;
- impedire valori negativi, date incoerenti, scadenze anteriori all'attivazione e piani non attivi;
- definire un comportamento deterministico alla scadenza del Supporter, con ritorno a `free` salvo rinnovo o accesso speciale permanente;
- aggiornare i permessi effettivi senza richiedere un nuovo login, oppure invalidare in modo controllato la sessione quando necessario;
- registrare le modifiche amministrative rilevanti con amministratore, utente interessato, data, operazione e valori precedenti e successivi;
- aggiungere test dedicati per:
  - passaggio `free` → `supporter`;
  - passaggio `supporter` → `free`;
  - scadenza annuale;
  - rinnovo;
  - limite soggetti personalizzato;
  - rimozione dell'override;
  - preservazione dei soggetti già esistenti;
  - separazione tra ruolo amministrativo e piano applicativo;
  - modifica dei limiti predefiniti dei piani;
  - indipendenza tra importo donato e limite soggetti;
  - attivazione dell'accesso speciale permanente;
  - revoca dell'accesso speciale permanente;
  - assenza di scadenza per gli utenti speciali;
  - mantenimento del ruolo `user` per gli utenti speciali;
  - attivazione e disattivazione dei piani;
  - blocco delle nuove assegnazioni verso piani disattivati;
  - preservazione dei dati degli utenti appartenenti a un piano disattivato;
  - migrazione amministrativa tra piani;
  - rifiuto del Supporter ordinario sotto la soglia minima configurata;
  - aggiornamento dei permessi dopo una modifica amministrativa;
  - registrazione dello storico delle operazioni amministrative;
  - assenza di valori commerciali hardcoded negli endpoint.

### Fase 6 — Limite soggetti

- contare i soggetti dell’utente prima dell’inserimento;
- confrontare il conteggio con il limite effettivo;
- bloccare l’inserimento oltre soglia;
- restituire un errore JSON comprensibile;
- preservare consultazione, modifica ed eliminazione dei soggetti esistenti;
- non applicare il limite commerciale agli amministratori;
- aggiungere test dedicati.

### Fase 7 — Limite ricerche salvate

- individuare il modello di persistenza corrente;
- associare ogni ricerca al proprietario;
- contare le ricerche prima del salvataggio;
- applicare il limite di 10 per il piano gratuito;
- consentire modifica ed eliminazione oltre soglia;
- impedire accessi tra utenti;
- aggiungere test dedicati.

### Fase 8 — Restrizioni ricerca lato server

- bloccare la ricerca a griglia quando non autorizzata;
- mantenere disponibile esclusivamente la ricerca aeroportuale nel piano gratuito;
- consentire al piano gratuito la visualizzazione delle nazioni senza permetterne l’utilizzo per la ricerca delle località;
- bloccare lato server la ricerca delle località quando non autorizzata;
- mostrare un alert «Questa funzione è riservata agli utenti del piano Supporter.» quando un utente gratuito tenta di utilizzare una funzione non inclusa;
- bloccare o normalizzare l’espansione dinamica dell’orbe;
- restituire errori coerenti;
- verificare chiamate API dirette;
- aggiungere test dei permessi.

### Fase 9 — Comparator

- applicare massimo 2 risultati al piano gratuito;
- applicare massimo 3 risultati al piano Supporter;
- mantenere il server come fonte definitiva;
- mostrare il limite residuo nell’interfaccia quando utile;
- aggiungere test RS e Rilocazioni.

### Fase 10 — Annual Report, stampa e PDF

- mantenere disponibile la visualizzazione del report;
- introdurre quota condivisa di 3 esportazioni o stampe al mese per il piano gratuito;
- registrare gli utilizzi in modo transazionale;
- evitare doppi conteggi;
- mostrare la quota residua;
- applicare uso corretto al piano Supporter;
- aggiungere test dedicati.

### Fase 11 — Restrizioni interfaccia ✅ completata

- nascondere o disabilitare i controlli non disponibili;
- impedire l’avvio di operazioni non consentite;
- mostrare messaggi chiari e non aggressivi;
- indicare limiti e quota residua;
- mantenere il server come fonte definitiva dell’autorizzazione.

### Fase 12 — Sicurezza e sessioni ✅ completata

Implementato e verificato:

- confermato l'utilizzo di `password_hash()` con bcrypt e `password_verify()`;
- confermata la rigenerazione dell'identificativo di sessione dopo il login;
- verificata la configurazione sicura dei cookie di sessione;
- verificata la distruzione della sessione e del cookie durante il logout;
- aggiunta la protezione CSRF alle operazioni amministrative;
- aggiunta la protezione CSRF al cambio password;
- confermata la protezione CSRF della registrazione pubblica;
- introdotto rate limiting sul login;
- confermato il rate limiting della registrazione;
- verificati i messaggi del login contro l'enumerazione delle credenziali;
- verificati token di verifica email e reset password monouso;
- lint PHP, regressione completa e `git diff --check` superati.

Attività future non bloccanti:

- logging strutturato degli eventi di sicurezza;
- ulteriori test HTTP dedicati a CSRF e rate limiting.

### Fase 13 — Regressione e documentazione ✅ completata

Implementato e verificato:

- eseguito il lint PHP nel container `astrolab-web` sui file modificati nella Fase 12;
- eseguito `git diff --check` con esito positivo;
- eseguiti i test specifici inclusi nella suite di regressione;
- eseguita la regressione completa `tests/run.php` con esito positivo;
- avviato l’allineamento della documentazione ufficiale;
- verificato lo stato del repository e il commit conclusivo della Fase 12;
- nessuna modifica applicativa o refactoring introdotti.

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

## Messaggi utente previsti

I messaggi relativi ai limiti devono spiegare chiaramente cosa è successo e cosa può fare l’utente.

Esempio per le ricerche salvate:

> Hai raggiunto il limite di 10 ricerche salvate previsto dal piano gratuito. Le ricerche esistenti restano disponibili e possono essere modificate o eliminate. Il piano Supporter consente di ampliare l’archivio e utilizzare le funzionalità avanzate.

Esempio per il Comparator:

> Il piano gratuito consente di confrontare fino a 2 risultati. Per confrontare 3 RSM è necessario il piano Supporter.

Esempio per stampa o PDF:

> Hai utilizzato le 3 esportazioni disponibili per questo mese. Puoi continuare a visualizzare il report annuale oppure attendere il rinnovo della quota mensile.

Esempio per la ricerca a griglia:

> La ricerca a griglia è una funzione avanzata disponibile per gli utenti sostenitori.

---

## Storico della feature

### 2026-07-29 — Analisi iniziale

- definito il requisito generale della gestione utenti;
- ipotizzato inizialmente un profilo TEST;
- analizzati i principali file backend e frontend coinvolti;
- verificata l’assenza dei campi necessari nella tabella `utenti`;
- nessuna modifica applicativa eseguita;
- creata la roadmap specifica della feature.

### 2026-07-29 — Revisione del modello utenti

- abbandonato il concetto di utente TEST per il normale percorso di registrazione;
- introdotto il modello separato ruolo, stato account e piano;
- definito il flusso registrazione, verifica email e attivazione;
- definiti i piani iniziali `free` e `supporter`;
- scelti i limiti iniziali del piano gratuito;
- mantenute accessibili OpenStreetMap, ricerca aeroporti, visualizzazione delle nazioni, Rome2Rio e visualizzazione Annual Report; la ricerca delle località è riservata agli utenti del piano Supporter;
- riservate al piano Supporter ricerca a griglia, espansione dinamica dell’orbe e confronto completo fino a 3 risultati;
- definito il limite di 2 soggetti e 10 ricerche salvate per il piano gratuito;
- definita una quota condivisa di 3 stampe o esportazioni PDF al mese;
- introdotta la necessità di permessi centralizzati e override amministrativi;
- creato ADR-016 con stato `Proposta` e registrato nell’indice ADR;
- nessuna modifica applicativa eseguita.

### 2026-07-29 — Completamento Fase 1 database

- individuato `sql/schema_baseline.sql` come schema di riferimento;
- preparata e corretta la migrazione `sql/002_registrazione_utenti.sql`;
- aggiunte le tabelle `piani` e `piano_limiti`;
- estesa la tabella `utenti` con stato account, piano, verifica email e campi amministrativi;
- convertito il ruolo legacy `astrologo` in `user`;
- assegnato il piano `supporter` agli utenti esistenti;
- validata la migrazione su database temporaneo dedicato;
- applicata la migrazione al database operativo;
- verificati dati, vincoli, indici e chiavi esterne;
- completata la verifica funzionale di login e accesso all’applicazione;
- pubblicato il commit `b00c2bd` sul branch `feature/registrazione-utenti`.

---

### 2026-07-29 — Completamento Fase 2 modello utente, piani e permessi

- aggiornato `www/includes/Auth.php`;
- centralizzata la lettura del modello utente;
- aggiunta la gestione coerente di ruolo, stato account e piano;
- introdotti helper centralizzati per piani, limiti e permessi;
- implementata la lettura dei limiti configurati nel database;
- preservata la compatibilità con amministratori e utenti esistenti;
- mantenuta la retrocompatibilità con il comportamento applicativo precedente;
- verificata la sintassi PHP utilizzando il container Docker `astrolab-web`;
- verificato il diff delle modifiche;
- pubblicato il commit `7a9e275` sul branch `feature/registrazione-utenti`.


### 2026-07-29 — Completamento Fase 3 registrazione pubblica

- implementata `www/registrazione.php`;
- aggiunto `Auth::registraUtentePubblico()`;
- validazione server-side di username, email e password;
- assegnazione forzata di ruolo `user`, piano `free` e stato `pending_email`;
- impedita l'escalation dei privilegi dal client;
- introdotta protezione CSRF e rate limiting della registrazione;
- aggiunti test dedicati della registrazione pubblica;
- eliminato l'avviso `session_start()` nei test CLI;
- regressione completa superata;
- fase 3 completata.

### 2026-07-30 — Implementazione Fase 6 limite soggetti

- creata la migrazione `sql/004_popola_piano_limiti.sql`;
- configurato il limite `subjects_max` a 2 per il piano `free`;
- configurato il piano `supporter` senza limite numerico;
- aggiornato `www/api/soggetti_api.php`;
- introdotto il conteggio dei soggetti prima dell'inserimento;
- bloccato l'inserimento oltre soglia con errore JSON;
- esclusi gli amministratori dal limite commerciale;
- controllo sintassi PHP superato;
- regressione completa con `www/tests/run.php` superata;
- aggiunto il test funzionale dedicato `www/tests/test_subjects_limit.php`;
- integrato il test nella regressione completa `www/tests/run.php`;
- verificato il blocco del terzo soggetto per il piano `free`;
- fase 6 completata.

### 2026-07-30 — Implementazione Fase 7 limite ricerche salvate

- creata la migrazione `sql/005_popola_limite_ricerche_salvate.sql`;
- configurato il limite `saved_searches_max` a 10 per il piano `free`;
- configurato il piano `supporter` senza limite numerico;
- aggiornato `www/api/sessioni_api.php`;
- introdotto il conteggio delle ricerche salvate prima del salvataggio;
- bloccato l'undicesimo salvataggio con errore JSON;
- esclusi gli amministratori dal limite commerciale;
- controllo sintassi PHP superato;
- aggiunto il test funzionale dedicato `www/tests/test_saved_searches_limit.php`;
- integrato il test nella regressione completa `www/tests/run.php`;
- verificato tramite HTTP il blocco dell'undicesima ricerca salvata;
- regressione completa superata;
- fase 7 completata.

### 2026-07-30 — Avanzamento parziale Fase 5 amministrazione utenti

- aggiornata l'amministrazione utenti al nuovo modello `user`;
- visualizzati ruolo, stato account, piano e verifica email;
- introdotta la sospensione e riattivazione coerente con `account_status`;
- validata server-side la gestione dei ruoli amministrativi;
- allineata parzialmente l'interfaccia amministrativa al nuovo modello utenti;
- regressione completa superata per le funzioni implementate;
- la fase non è completata: mancano modifica del piano, ciclo di vita annuale Supporter, gestione donazioni, scadenze, rinnovi e limiti personalizzati per singolo utente.

### 2026-08-11 — Sblocco registrazione pubblica

- deciso di rimandare l'attivazione reale dell'invio email (verifica account e reset password) al momento del deployment su VPS;
- `Auth::registraUtentePubblico()` ora attiva subito l'account (`account_status = 'active'`) invece di lasciarlo in `pending_email`;
- il meccanismo di token e verifica email via `verifica-email.php` resta nel codice, dormiente, pronto per l'attivazione futura;
- aggiunto `Auth::verificaManualmente()` per attivare dall'amministrazione un eventuale utente residuo in `pending_email`;
- aggiunta in `admin_utenti.php` l'azione e il pulsante di verifica manuale, con nuovo badge di stato "Da verificare";
- aggiunto in `login.php` il link mancante alla pagina `registrazione.php` (pagina già esistente ma non raggiungibile dall'interfaccia);
- verifica end-to-end eseguita manualmente: registrazione → account attivo in database → login riuscito;
- controllo sintassi PHP superato su tutti i file modificati;
- nessun test automatico dedicato esistente per il flusso di registrazione (`tests/run.php` fallisce per una funzione `passthru()` disabilitata, problema preesistente non legato a questa modifica).

### 2026-08-11 bis — Fase 5, passo 1: modello dati amministrazione piano Supporter

- creata la migrazione `sql/007_piano_supporter_amministrazione.sql`, applicata al database operativo;
- aggiunti su `utenti`: `subjects_limit_override` (limite soggetti personalizzato per singolo utente), `donazione_importo`, `supporter_inizio`, `supporter_scadenza`, `accesso_speciale_permanente` (accesso completo, gratuito, permanente, senza intaccare il ruolo), `note_piano`;
- aggiunti su `piani`: `donazione_minima` e `durata_giorni`, configurabili per piano (valorizzati inizialmente per `supporter`: 0 € minimo, 365 giorni);
- vincoli CHECK applicati su valori non negativi e coerenza tra date inizio/scadenza;
- verificato lo schema post-migrazione e la disponibilità dell'applicazione (login e index invariati);
- prossimo passo: helper centralizzati in `Auth.php` per il limite effettivo (precedenza accesso speciale permanente → override utente → piano) e per lo stato Supporter (attivo/scaduto).

---

## Prossimo passo

Fasi 7, 8, 9 e 10 — completate.


**Ultima fase completata: Fase 13 — Regressione e documentazione.**

La macro-funzionalità di registrazione, piani, permessi, limiti e sicurezza non è ancora completata.

Prossimo passo: completare la Fase 5 con gestione amministrativa del piano Supporter, validità annuale, donazioni, scadenze, rinnovi e limiti soggetti personalizzati per singolo utente.

---

## Commit

- Fase 12: commit `85311db` — `Completata Fase 12: sicurezza e sessioni`.
- Fase 13: commit documentale da eseguire esclusivamente dopo la verifica finale e la conferma dell’utente.
