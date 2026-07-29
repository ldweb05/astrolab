# ASTROLAB --- ADR Index

Indice delle decisioni architetturali permanenti.

  ------------------------------------------------------------------------
  ADR               Decisione          Stato             Versione
  ----------------- ------------------ ----------------- -----------------
  ADR-001           Atlas come Single  Accettata         V4.1
                    Source of Truth

  ADR-002           Rule Engine        Da formalizzare   V4.2
                    alimentato solo da
                    PlanetCondition

  ADR-003           Evidence Contract  Da formalizzare   V4.2
                    V4
                    retrocompatibile

  ADR-004           Generazione        Pianificata       V4.2
                    assistita delle
                    Rule

  ADR-005           Validazione        Pianificata       V4.2
                    automatica delle
                    Rule

  ADR-012           Freeze del Rule    Accettata         V5
                    Engine dopo il
                    completamento
                    delle 120 Rule

  ADR-015           Deduplicazione     Accettata         Ricerca RS v2
                    spaziale SQL
                    della Ricerca RS

  ADR-016           Registrazione,     Proposta           Gestione utenti
                    piani e permessi
                    utente
  ------------------------------------------------------------------------

## Regola

Ogni nuova decisione architetturale significativa deve:

1.  ricevere un identificativo ADR;
2.  descrivere contesto, decisione e conseguenze;
3.  essere registrata in questo indice;
4.  aggiornare la timeline della roadmap.

------------------------------------------------------------------------

# DIRETTIVA OPERATIVA PERMANENTE

L'architettura di Astro-Val e il Rule Engine sono considerati
**STABILI**.

Il Rule Engine è completo a 120 Rule e rimane in stato di **FREEZE**.

Non devono essere introdotte modifiche architetturali o modifiche al
dominio astrologico, salvo:

-   bug documentati;
-   incompatibilità tecniche;
-   refactoring che non alterino il comportamento;
-   decisioni esplicite formalizzate tramite ADR.

Le attività correnti possono riguardare:

-   Evidence Engine;
-   Theme Engine;
-   Narrative Engine;
-   Annual Report;
-   PDF;
-   UX/UI;
-   frontend e CSS condiviso;
-   test automatici;
-   documentazione;
-   preparazione delle release.

Ogni attività completata deve essere registrata cronologicamente in:

`docs/HANDOVER_OPERATIVO_astrolab.md`

Per riprendere il lavoro leggere nell'ordine:

1.  `docs/START_HERE.md`;
2.  `docs/HANDOVER_OPERATIVO_astrolab.md`;
3.  `docs/ROADMAP.md`;
4.  `docs/ADR_INDEX_ASTROLAB.md`.

Non è richiesto reinterpretare l'architettura.

Qualsiasi nuova decisione architetturale significativa deve essere
formalizzata tramite un nuovo ADR.

------------------------------------------------------------------------

------------------------------------------------------------------------

# ADR-012 --- Freeze del Rule Engine

**Stato:** Accettata **Versione:** V5

## Contesto

La milestone V4 ha completato il Rule Engine con:

-   120 Rule implementate;
-   120 Rule registrate;
-   Knowledge Coverage 100%;
-   Full Regression stabile.

Il commit di riferimento è:

`0bc53d0` ---
`feat(rule): implement RULE-0119 and RULE-0120 Pluto Houses 11-12`

## Decisione

Il Rule Engine entra ufficialmente in **FREEZE**.

Non devono essere:

-   aggiunte nuove Rule;
-   modificati i pesi;
-   modificata la logica interpretativa;
-   introdotti refactoring che alterino il comportamento.

Sono consentite modifiche esclusivamente in presenza di:

-   bug documentati;
-   incompatibilità tecniche;
-   refactoring strettamente comportamentali e verificati dalla Full
    Regression.

Le evoluzioni successive devono avvenire esclusivamente nei livelli
superiori:

-   Evidence Engine;
-   Theme Engine;
-   Narrative Engine;
-   Annual Report;
-   PDF e UX.

## Conseguenze

La V5 consolida il Report Professionale senza riaprire il dominio delle
120 Rule.

Ogni intervento sul Rule Engine richiede una motivazione documentata,
test specifici e Full Regression completa.

# ADR-013 --- Separazione di Astro-DSS da Astro-Val

**Stato:** Accettata **Versione:** V1

## Contesto

Per sviluppare un Decision Support System dedicato al confronto tra
Rivoluzioni Solari e Rilocazioni è stato deciso di non proseguire lo
sviluppo direttamente all'interno del repository Astro-Val.

L'obiettivo è consentire l'evoluzione del DSS mantenendo completamente
stabile il progetto Astro-Val.

## Decisione

Astro-DSS diventa un progetto indipendente con:

-   repository Git dedicato;
-   stack Docker dedicato;
-   database PostgreSQL dedicato;
-   porte e rete Docker dedicate;
-   documentazione indipendente;
-   branch di sviluppo autonomo.

Il motore astrologico, il Rule Engine e la logica di calcolo vengono
ereditati da Astro-Val senza modificarne il comportamento.

Tutte le nuove funzionalità del Comparator Engine e del Decision Support
System devono essere sviluppate esclusivamente all'interno di Astro-DSS.

## Conseguenze

La stabilità di Astro-Val viene preservata.

Astro-DSS può evolvere introducendo nuove interfacce, strumenti di
confronto e componenti DSS senza alterare il funzionamento del motore
astrologico originale.

Le future decisioni architetturali relative al Comparator Engine
dovranno essere documentate tramite nuovi ADR.

## Conseguenze

La V5 consolida il Report Professionale senza riaprire il dominio delle
120 Rule.

Ogni intervento sul Rule Engine richiede una motivazione documentata,
test specifici e Full Regression completa.

------------------------------------------------------------------------

# ADR-014 --- Il Comparator Engine preserva il risultato originale

**Stato:** Accettata **Versione:** V1

## Contesto

Durante lo sviluppo del Comparator delle Rivoluzioni Solari è emerso che
la pagina di confronto ricostruiva parte delle informazioni utilizzando
una logica differente rispetto a quella impiegata durante il calcolo
originario.

In particolare le regole personalizzate associate ai pianeti nelle case
potevano non coincidere con quelle realmente utilizzate per produrre il
risultato mostrato all'utente.

## Decisione

Il Comparator Engine non deve reinterpretare né ricalcolare il risultato
astrologico.

Deve utilizzare esclusivamente i dati prodotti dal motore astrologico e
preservare integralmente:

-   regole personalizzate;
-   configurazione della condizione selezionata;
-   classificazioni;
-   informazioni mostrate nel risultato originale.

Il Comparator rappresenta un livello di confronto e visualizzazione, non
un secondo motore di calcolo.

## Conseguenze

Viene garantita la perfetta coerenza tra:

-   risultato originale;
-   Comparator RS;
-   Comparator Rilocazioni;
-   futuri livelli decisionali del DSS.

Le evoluzioni successive (Difference Analyzer, Impact Evaluator, Rule
Correlator e Recommendation Engine) dovranno basarsi esclusivamente sui
dati prodotti dal Comparator senza alterarne il significato.

## Commit di riferimento

-   `57fbba4` --- preserve custom planet house rules in RS comparison
-   `37d15be` --- refine RS comparison controls layout

---

# ADR-015 --- Deduplicazione spaziale SQL della Ricerca RS

**Stato:** Accettata

**Versione:** Ricerca RS v2

## Contesto

La Ricerca RS utilizza aeroporti e località GeoNames.

La deduplicazione geografica viene oggi eseguita nel livello PHP dopo il
trasferimento dei dati dal database.

I benchmark hanno mostrato che oltre il 99% delle località candidate viene
eliminato durante questa fase.

## Decisione

La deduplicazione spaziale viene trasferita progressivamente nel database
PostgreSQL.

Il Repository continuerà a rappresentare l'unico punto di accesso ai dati.

Rule Engine, motore astrologico e Streaming API rimangono invariati.

Non vengono introdotte soglie minime di popolazione.

## Motivazione

La decisione consente:

- riduzione del traffico PostgreSQL → PHP;
- riduzione della memoria utilizzata;
- maggiore scalabilità;
- mantenimento del comportamento astrologico;
- preparazione della Ricerca RS mondiale.

## Conseguenze

La deduplicazione PHP rimane temporaneamente come riferimento durante la
fase di equivalenza.

Verrà rimossa soltanto dopo la completa validazione della pipeline SQL.

---

# ADR-016 --- Modello centralizzato di registrazione, piani e permessi utente

**Stato:** Proposta

**Versione:** Gestione utenti

## Contesto

ASTROLAB deve introdurre un flusso pubblico di registrazione e una gestione
centralizzata degli account senza distribuire nei singoli endpoint condizioni
hardcoded relative a ruolo, stato, piano, limiti e permessi.

Il precedente modello basato sul normale utilizzo di account TEST viene
abbandonato. Ogni nuovo account dovrà distinguere chiaramente:

- ruolo applicativo;
- stato dell'account;
- piano assegnato;
- limiti quantitativi;
- permessi funzionali;
- eventuali override amministrativi;
- quote periodiche e relativo utilizzo.

Il modello dati definitivo non è ancora approvato. Tabelle, campi, relazioni e
strategie di persistenza dovranno essere definiti dopo la verifica dello schema
PostgreSQL esistente e del meccanismo ufficiale di migrazione del progetto.

## Decisione

ASTROLAB adotterà un unico livello applicativo centralizzato per determinare i
permessi effettivi di ogni utente.

Il modello manterrà separati i seguenti concetti:

- il ruolo determina i privilegi amministrativi;
- lo stato account determina se l'account può autenticarsi e usare il servizio;
- il piano determina limiti e funzionalità commerciali;
- gli override consentono eccezioni amministrative esplicite;
- le protezioni tecniche e di sicurezza restano indipendenti dal piano.

I nuovi account saranno creati con:

- ruolo `user`;
- stato `pending_email`;
- piano `free`.

Dopo la verifica dell'indirizzo email lo stato potrà diventare `active`.

Il server sarà la fonte definitiva dell'autorizzazione. Il frontend potrà
nascondere o disabilitare funzioni non disponibili, ma ogni controllo dovrà
essere applicato anche lato server.

I limiti non dovranno essere duplicati o hardcoded nei singoli endpoint.

La precedenza prevista sarà:

1. protezioni tecniche e di sicurezza;
2. stato dell'account;
3. privilegi amministrativi;
4. override utente valido;
5. configurazione del piano;
6. comportamento predefinito sicuro.

Gli amministratori non saranno soggetti ai limiti commerciali, salvo decisione
esplicita, ma resteranno soggetti alle protezioni tecniche e di sicurezza.

In caso di downgrade, i dati già creati resteranno consultabili, modificabili o
eliminabili secondo i normali permessi. Sarà impedita soltanto la creazione di
nuovi dati oltre il limite effettivo.

L'espressione “illimitato con uso corretto” indica l'assenza di un limite
commerciale ordinario, non l'assenza di rate limit, protezioni anti-abuso o
vincoli infrastrutturali.

## Limiti funzionali iniziali

Il piano `free` prevede inizialmente:

- massimo 2 soggetti personali;
- massimo 10 ricerche salvate;
- massimo 2 risultati nel Comparator;
- ricerca a griglia disabilitata;
- espansione dinamica dell'orbe disabilitata;
- visualizzazione dell'Annual Report abilitata;
- quota condivisa di 3 stampe o esportazioni PDF al mese.

Il piano `supporter` abilita le funzionalità avanzate e rimuove i limiti
commerciali ordinari secondo una politica di uso corretto.

Questi valori sono iniziali e potranno essere modificati tramite configurazione
senza distribuire nuove condizioni nei singoli endpoint.

## Conseguenze

Prima dell'implementazione applicativa sarà necessario:

- individuare lo schema PostgreSQL ufficiale;
- individuare il meccanismo di migrazione o aggiornamento del database;
- verificare il sistema di invio email;
- formalizzare lo schema dati definitivo;
- garantire compatibilità con gli utenti esistenti;
- definire helper o servizi centralizzati per limiti e permessi;
- aggiungere test server-side per ogni funzionalità soggetta a restrizione.

La presente decisione definisce il contratto architetturale, ma non approva
ancora nomi, numero o struttura definitiva delle tabelle.
