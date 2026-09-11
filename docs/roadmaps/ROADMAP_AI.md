# ROADMAP AI AGENT — ASTROLAB

Documento dedicato al percorso di integrazione di un AI Agent in ASTROLAB, un provider LLM alla volta. Contiene lo stato di avanzamento per fasi. Per i principi e le regole operative permanenti dell'AI Agent (sezioni 0-26), vedi `docs/roadmaps/ROADMAP_AI_PRINCIPI.md`.

> **Nota**
> Questo documento è il riferimento ufficiale per tutto ciò che riguarda l'integrazione AI in ASTROLAB. `docs/roadmaps/ROADMAP.md` non descrive queste attività; `docs/PROMPT_OPERATIVO_ASTROLAB.md` mantiene solo le regole operative generali del progetto e rimanda qui.

## Criterio di completamento (permanente, valido per ogni provider)

Un'integrazione AI non si considera "finita" quando il provider risponde a un prompt di test in una pagina isolata. Si considera finita quando un utente reale (in prima battuta: solo admin) può usarla per ottenere un risultato concreto e verificato di ASTROLAB — es. una ricerca RSM/RL — tramite tool calling, non testo libero. Il testo libero (Fasi 0-5) è l'infrastruttura di base indispensabile, non la funzionalità finale.

Solo dopo test approfonditi con l'admin, su ciascun tool aggiunto, si valuta l'apertura ai piani supporter/free — con eventuali limiti di quota/costo da definire in quel momento.

---

# Provider 1 — Google Gemini

## FASE 0 — Setup e primo contatto — COMPLETATA (2026-09-09)

Obiettivo: predisporre l'ambiente e verificare la raggiungibilità di Gemini, senza scrivere ancora codice applicativo.

Attività completate:
- rilevata l'architettura reale di ASTROLAB rilevante per l'AI Agent (container unico `astrolab-web`, nessun framework PHP, `bootstrap.php` unico punto di lettura env, `allow_url_fopen` Off, uso obbligatorio di curl);
- aggiunte le costanti `GEMINI_API_KEY` (nullable, nessun default) e `AI_AGENT_ENABLED` (default `false`) in `bootstrap.php` — commit `d38c661`;
- creata la chiave API Gemini su un progetto Google Cloud dedicato ad ASTROLAB, separato da altri progetti dell'account. Nessuna fatturazione attivata — chiave sul free tier, per scelta esplicita del committente, valida fino all'eventuale passaggio in produzione;
- valorizzata `GEMINI_API_KEY` nel `.env` locale sul Raspberry Pi (file non tracciato da Git);
- testata la raggiungibilità di rete verso l'endpoint Gemini `generateContent`, sia dall'host Raspberry Pi sia da dentro il container `astrolab-web` — risposta corretta (finishReason STOP).

Correzioni rilevanti emerse durante la sessione:
- il modello `gemini-2.5-flash` non è più disponibile per nuovi progetti (errore 404 restituito dall'API); il modello sostitutivo confermato funzionante e incluso nel free tier è `gemini-3.6-flash` — è questo il modello da usare in Fase 1;
- `gemini-3.6-flash` ha il thinking (ragionamento interno) attivo di default: anche un prompt banale (9 token) ha generato circa 91-106 thoughtsTokenCount fatturati. Da valutare in `GeminiProvider.php` (Fase 1) se e come limitarlo, per non consumare inutilmente la quota free-tier.

Test eseguiti: chiamata curl diretta a generateContent da host e da container, esito positivo in entrambi i casi.

Commit Git: `d38c661` (costanti in bootstrap.php). Nessun commit aggiuntivo in questa fase (script di test volutamente non committato).

Passo successivo: FASE 1 — Provider Layer.

---

## FASE 1 — Provider Layer — COMPLETATA (2026-09-09)

Obiettivo: creare l'astrazione del provider AI, isolata e sostituibile.

Attività completate:
- `www/includes/ai/AiProviderInterface.php` — contratto minimo (chiedi(string $prompt): array, ritorno sempre ['ok'=>bool,'testo'=>...,'errore'=>...], mai eccezioni non gestite);
- `www/includes/ai/GeminiProvider.php` — implementazione concreta via curl verso gemini-3.6-flash, con gestione esplicita di: chiave API assente, errori di rete, timeout, HTTP non-200, quota esaurita (429), risposta malformata o priva di testo;
- nessuna libreria esterna, nessun Composer (coerente con il resto del progetto);
- thinking del modello disattivabile via parametro del costruttore (`abilitaThinking`, default `false`).

Correzione rilevante emersa durante l'implementazione: la sintassi per limitare il thinking della serie Gemini 2.5 (`thinkingConfig.thinkingBudget`) non è valida per Gemini 3.x e causa un errore "Request contains an invalid argument". Per `gemini-3.6-flash` va usata `thinkingConfig.thinkingLevel` (valori tipo "low"/"high"). Inoltre, i modelli Flash della serie Gemini 3 **non supportano la disattivazione completa del thinking**: "low" è il minimo disponibile, non uno zero assoluto.

Test eseguiti: chiamata funzionale reale a `chiedi()` sia con thinking ridotto (default) sia con thinking pieno, entrambe con esito positivo (`ok: true`, `testo: 'OK'`).

Passo successivo: FASE 2 — Primo endpoint.

---

## FASE 2 — Primo endpoint — COMPLETATA (2026-09-10)

`www/api/ai_agent_api.php` creato, stesso stile procedurale delle altre API di ASTROLAB (modello: `session_api.php`).

Dettagli implementativi:
- richiede login (`Auth::isLoggedIn()`, HTTP 401 se assente) e ruolo admin (`Auth::isAdmin()`, HTTP 403 se non admin) — in questa fase l'AI Agent è riservato agli amministratori;
- azione `chiedi` con parametro `prompt`: valida che non sia vuoto (HTTP 400), rispetta il flag `AI_AGENT_ENABLED` (se `false`, risponde con errore esplicito invece di tentare la chiamata a Gemini);
- istanzia `GeminiProvider` e restituisce direttamente il suo output (`ok`/`testo`/`errore`), senza tool calling (fuori scope, Sezione 18).

Test eseguiti: verifica funzionale reale dell'endpoint via `curl` sull'IP LAN del Pi (porta 8090, mappata da Docker Compose) — richiesta senza autenticazione correttamente rifiutata con HTTP 401 e corpo JSON `{"ok":false,"errore":"Non autenticato."}`. Il percorso con utente admin reale non è stato testato via curl (per non far transitare credenziali in chat) e sarà verificato naturalmente in Fase 3, tramite la sessione browser già autenticata.

Passo successivo: FASE 3 — Test UI minimale.

---

## FASE 3 — Test UI minimale — COMPLETATA (2026-09-10)

Pagina `www/test_ai_agent.php` creata: campo prompt, pulsante di invio, area risposta con gestione visiva di errore. Accesso protetto con `Auth::richiediAdmin()` (redirect se non admin, coerente con l'uso corretto di questo metodo per pagine HTML, a differenza di `isAdmin()` + JSON usato in `ai_agent_api.php`).

Correzione rispetto alla roadmap originale: `test_stelline_v2.php`, indicato come modello "admin-only", in realtà richiede solo login (`richiediLogin()`), non `richiediAdmin()` — la restrizione ad amministratori per l'AI Agent è stata implementata direttamente qui.

Attivato `AI_AGENT_ENABLED=true` nel `.env` (era rimasto `false` come interruttore di sicurezza dalla Fase 0), con `docker compose up -d astrolab-web` per far rileggere la variabile.

**Test end-to-end riuscito dal browser**: prompt reale ("Rispondi con una sola parola: OK va bene?") inviato dalla pagina, risposta reale di Gemini ricevuta e mostrata correttamente ("Sì"), HTTP 200, 1.9 secondi. Prima verifica completa dell'intera catena: browser → `test_ai_agent.php` → `ai_agent_api.php` → `GeminiProvider` → Gemini API → ritorno.

Passo successivo: FASE 4 — Hardening.

---

## FASE 4 — Hardening — COMPLETATA (2026-09-10)

Obiettivo: verificare con test reali (non solo lettura del codice) i percorsi di errore già implementati in `GeminiProvider.php` fin dalla Fase 1, e l'assenza di impatto sul resto di ASTROLAB.

Metodo: script PHP temporanei isolati (non committati), che non toccano mai `.env` ne' richiedono riavvii del container - per lo scenario "chiave assente" le costanti sono definite localmente nello script stesso invece di modificare la configurazione reale, per non esporre l'intera applicazione a una finestra con chiave vuota.

Test eseguiti, tutti con esito positivo, nessun bug trovato:
- **Chiave API assente**: `ok:false`, errore `'GEMINI_API_KEY non configurata'`, nessun crash;
- **Modello inesistente (HTTP 404)**: `ok:false`, messaggio d'errore estratto correttamente dal campo `error.message` della risposta Gemini;
- **Timeout di rete** (forzato a 1s su un prompt che richiede piu' tempo): `ok:false`, errore `'errore di rete: Operation timed out...'`, interruzione pulita a 1.01s, nessuna richiesta rimasta pendente;
- **Non-impatto su ASTROLAB**: verificato che un endpoint estraneo all'AI Agent (`session_api.php`) risponda normalmente (401 su richiesta anonima, comportamento atteso) nello stesso momento, confermando l'indipendenza del resto dell'applicazione dall'AI Agent (Sezione 10).

Nessuna modifica al codice è stata necessaria: la gestione errori implementata in Fase 1 si è rivelata già corretta per tutti gli scenari testati.

Passo successivo: FASE 5 — Criterio di successo.

---

## FASE 5 — Criterio di successo — COMPLETATA (2026-09-10)

Verifica finale contro tutti i criteri della Sezione 25 di `docs/roadmaps/ROADMAP_AI_PRINCIPI.md`, con evidenza oggettiva per ciascuno (non solo dichiarazione):

- **Gemini raggiungibile tramite API** — verificato in Fase 0 (test curl da host e container);
- **Chiave esterna al codice** — verificato: `GEMINI_API_KEY` esiste solo in `.env`, mai in nessun commit di file `.php` (confermato con `git log --name-status`);
- **Integrazione isolata** — verificato: tutto il codice AI Agent è confinato a `www/includes/ai/AiProviderInterface.php`, `www/includes/ai/GeminiProvider.php`, `www/api/ai_agent_api.php`, `www/test_ai_agent.php` — zero modifiche a file esistenti del core;
- **ASTROLAB funzionante anche senza Gemini** — verificato in Fase 4 (`session_api.php` funzionante durante errori Gemini simulati);
- **Errori API gestiti** — verificato in Fase 4 (chiave assente, modello inesistente/404, timeout; gestione di quota 429 presente nel codice fin dalla Fase 1);
- **Provider sostituibile senza riscrivere il core** — `AiProviderInterface` predisposta a questo scopo; non ancora testato con un secondo provider reale, verifica completa rimandata all'arrivo del Provider 2;
- **Nessuna alterazione a logiche astronomiche** — verificato con `git log --name-status 0d373d7..HEAD`: nessun file del motore RSM/RL/Rule Engine/Evidence Engine/Theme Engine toccato in tutto il percorso;
- **Nessuna alterazione alle 34 Regole** — idem;
- **Nessun veto modificato** — idem;
- **Modifiche completamente reversibili tramite Git** — verificato: ogni fase è un commit atomico e distinto (`5c56fca`, `34074d3`, `c1a761e`, `25d53c1`, `4939df2`), tutti pushati su `origin/main`.

**Provider 1 (Google Gemini) formalmente concluso — limitatamente all'infrastruttura.** Tutti i criteri di successo soddisfatti per il canale Gemini isolato, sicuro e testato. Non soddisfatto (volutamente, per scope): il Criterio di completamento sopra definito — un utente non può ancora ottenere un risultato concreto di ASTROLAB tramite l'AI. Prosegue con la sezione "Tool Calling" sotto.

Fuori scope per l'intero Provider 1: tool calling verso le funzioni ASTROLAB, chat completa, memoria conversazionale — restano fuori scope anche ora che il Provider 1 è concluso; arriveranno solo con una futura roadmap dedicata al tool calling, dopo l'eventuale aggiunta di ulteriori provider per il benchmark (Sezione 22).

---

# Tool Calling — Ricerca RSM/RL (Provider 1: Gemini)

Le 9 condizioni di ricerca usate dall'astrologo nell'interfaccia sono: Decima (default), Lavoro, Amore, Salute, Denaro, Denaro Low, Casa, Astri nelle Case, Longitudini Cuspidi. Tecnicamente si dividono in 7 condizioni "standard" (parametro `condizione` di `ricerca_stream_api.php`), + Astri nelle Case (stesso endpoint, parametro aggiuntivo `astri_in_casa`), + Longitudini Cuspidi (endpoint diverso, `cuspidi_search_api.php`). Tutte e 9 restano nel piano, introdotte in tool separati e progressivi — nessuna esclusa definitivamente.

Mappa pagina→API in produzione verificata (non tutte le varianti nel repository sono in uso reale):
- Ricerca RSM per condizione: `ricerca.php` → `ricerca_stream_api.php` (+ `ricerca_griglia_api.php` per modalità griglia, `cuspidi_search_api.php` per cuspidi);
- Ricerca RL per condizione: `ricerca_rl.php` → `ricerca_stream_rl_api.php`;
- Ricerca rilocazione: `rilocazione.php` → `riloc_angolari_api.php`;
- `ricerca_stream_v2_api.php` e `ricerca_api.php` risultano non in uso da nessuna pagina di produzione (solo la prima usata dal laboratorio `test_stelline_v2.php`) — scartate come base per i tool.

## FASE 6 — Primo tool reale: RicercaRsmTool (7 condizioni standard) — COMPLETATA (2026-09-10)

Obiettivo: costruire un tool PHP deterministico (nessuna AI coinvolta in questo passo) che, dato un soggetto/anno/condizione, restituisca un risultato JSON pulito riusando al 100% la logica di ricerca esistente — senza duplicarla.

Decisione architetturale: `ricerca_stream_api.php` (oltre 1200 righe: deduplicazione geografica, batch/tranche per la modalità località, 7+ contatori di esclusione per condizione, ordinamenti condizionali UX-0015/0016/0019) è troppo complesso e delicato per essere duplicato in una nuova funzione (violerebbe la Sezione 7). Il tool lo chiama invece come client HTTP interno (`curl` verso `http://localhost`, stessa sessione), consumandone lo stream SSE e restituendo solo l'evento finale `done`. Zero duplicazione, aggiornamento automatico se l'endpoint originale cambia.

Creato `www/includes/ai/RicercaRsmTool.php`:
- riceve soggetto/anno/condizione (una delle 7 condizioni standard; Astri in Casa e Cuspidi restano fuori scope per questo tool, vedi Fasi successive);
- carica il soggetto con `caricaSoggettoById()` (`SoggettoRepository.php`) e converte i dati di nascita con `calcolaDataOraGmtCorretta()` (`NascitaGmtHelper.php`) — stessa funzione già usata da `tema.php`, che gestisce correttamente il cambio di giorno GMT (vedi `ROADMAP_BUG_GIORNO_GMT.md`);
- propaga la sessione corrente (cookie `ASTROSESSID`) alla chiamata interna, così l'endpoint riusa l'autenticazione già presente, senza bisogno di credenziali separate;
- estrae dall'evento SSE `done` i primi N risultati (default 5) più i totali. Nota di correzione: il tool NON filtra i campi di ciascun risultato (passa gli oggetti completi, con tutti i campi tecnici); limita solo il numero di risultati restituiti. Questo si è rivelato importante in Fase 7.

**Due bug/scoperte architetturali reali emerse e risolte durante l'implementazione, rilevanti per ogni tool futuro che farà chiamate HTTP interne:**
1. **Deadlock da lock di sessione**: PHP blocca in scrittura il file di sessione per tutta la durata di una richiesta con `session_start()` attivo. Una richiesta che ne chiama un'altra sulla stessa sessione (come il nostro tool verso `ricerca_stream_api.php`) va in deadlock se non si rilascia il lock prima — risolto con `session_write_close()` subito prima della chiamata `curl` interna.
2. **OPcache con `validate_timestamps=Off`**: il container ASTROLAB ha questa direttiva disattivata, quindi **ogni modifica a un file PHP richiede un riavvio del container** (`docker compose restart astrolab-web`) per essere effettiva — salvare il file non basta, a differenza di quanto si potrebbe assumere. Da tenere presente per ogni futura sessione di sviluppo, non solo per l'AI Agent.

Test eseguiti: chiamata reale (login via `curl` con utente admin, poi richiesta autenticata) — risultato positivo, `ok:true`, 6 risultati reali per RSM 2027/Lavoro del soggetto di test, 2.7 secondi, dati coerenti con una chiamata diretta all'endpoint originale. Azione di test temporanea (`test_rsm` in `ai_agent_api.php`, usata solo per questa verifica) rimossa a fine test — l'endpoint è tornato alla sua forma pulita di Fase 2.

Passo successivo: FASE 7 — collegamento del tool a Gemini (tool calling vero: Gemini interpreta la richiesta in linguaggio naturale, chiama `RicercaRsmTool`, spiega il risultato reale senza inventare nulla).

## FASE 7 — Collegamento a Gemini (tool calling) — COMPLETATA (2026-09-10)

Obiettivo: Gemini interpreta una richiesta in linguaggio naturale, estrae anno e condizione, chiama `RicercaRsmTool`, e spiega il risultato reale restituito — mai inventato (Sezione 1, Sezione 23).

Implementazione:
- `GeminiProvider.php` riscritto: logica HTTP comune estratta in un metodo privato condiviso (`eseguiRichiesta`), riusato da `chiedi()` (invariato nel comportamento esterno, riverificato con lo stesso test della Fase 1 — nessuna regressione) e dai due nuovi metodi `chiediConFunzione()` e `rispondiConRisultatoFunzione()`, che implementano il flusso a due turni richiesto dall'API Gemini per il function calling (turno 1: Gemini decide se rispondere a testo libero o chiedere di invocare una funzione; turno 2, solo se richiesto: gli viene passato il risultato vero della funzione, esegue la sintesi finale in linguaggio naturale);
- verificato isolatamente con un tool fittizio (somma di due numeri) prima di collegare quello vero, per isolare eventuali problemi di formato dal resto della logica — esito positivo;
- `ai_agent_api.php`, azione `chiedi`, ora dichiara a Gemini il tool `cerca_rsm` (anno, condizione tra le 7 standard) e orchestra i due turni; il soggetto su cui cercare è **sempre** quello attivo di sessione (`Auth::getSoggettoAttivo()`), mai un parametro che Gemini possa scegliere o vedere (Sezione 23);
- se nessun soggetto è attivo, risposta esplicita che invita a selezionarne uno, senza tentare la ricerca.

**Test end-to-end reale dal browser, riuscito**: prompt "Cercami una buona RSM per la DECIMA nell'anno 2026" → Gemini ha capito anno e condizione, chiamato il tool, ricevuto dati reali (soggetto 23), risposto con 5 località (Mar del Plata, Montevideo, Villa Gesell, Durazno, Santa Teresita) corrispondenti esattamente ai primi 5 risultati reali del motore (verificato per confronto diretto con una chiamata separata a `ricerca_stream_api.php`).

**Correzione di un giudizio affrettato durante la verifica**: inizialmente è stato valutato come "inventato" un riferimento di Gemini alla "Regola 14" nella spiegazione. Verifica successiva nel codice (`RuleEngineExtended::verificaRegola14()`) ha mostrato che si tratta di una regola reale delle 34 ufficiali (ASC di RSM in X casa natale, indebolito da pianeti lenti in aspetto dissonante ai 4 punti natali), già calcolata dal motore con un campo `regola14_scattata` e una `nota` testuale pronta quando la regola scatta. Gemini aveva letto correttamente il dato (`regola14_scattata: false` per tutti i risultati mostrati), non l'aveva inventato. Lezione operativa: verificare sempre il significato esatto di un campo nel codice prima di giudicare una risposta AI come "inventata".

**Scoperta strutturale importante emersa durante l'analisi**: il motore (`RuleEngineExtended.php`) ha già una ricca gerarchia a livelli, condizione per condizione (Decima, Amore, Lavoro, Salute, Casa: pianeti benefici/malefici per casa, orbi, fasce), ma **solo Decima** ha una spiegazione testuale pronta (`nota` per la Regola 14) — le altre condizioni restituiscono solo un numero di `livello`, senza frase leggibile. Gemini riceve già questi numeri (il tool non filtra nulla), ma dovrebbe interpretarli da solo per spiegarli bene, con lo stesso rischio di imprecisione già visto. Tracciato come lavoro separato in Fase 10, non essendo tool calling ma arricchimento del motore.

Denaro e Denaro Low restano senza alcuna gerarchia a livelli (solo veto/non-veto) - confermato nel codice, coerente con quanto già segnalato dal committente in precedenti sessioni.

Passo successivo: valutare Fase 8 (condizioni restanti), Fase 10 (spiegazioni testuali) e Fase 11 (conversazione sulle esclusioni) in base alle priorità del committente.

## FASE 8 — Astri nelle Case e Longitudini Cuspidi — PIANIFICATA

Estensione di `RicercaRsmTool` (o nuovo tool dedicato per le Cuspidi, dato l'endpoint diverso) alle rimanenti 2 delle 9 condizioni. Da avviare solo dopo che la Fase 7 è stata testata a fondo dall'admin sulle 7 condizioni standard.

## FASE 9 — Apertura ai piani supporter — PIANIFICATA

Da valutare solo dopo che tutte le 9 condizioni sono state implementate e testate a fondo dall'admin (Criterio di completamento). Include la definizione di eventuali limiti di quota/costo per l'uso AI da parte dei supporter.

## FASE 10 — Spiegazioni testuali per Amore, Lavoro, Salute, Casa — PIANIFICATA

Obiettivo: portare Amore, Lavoro, Salute e Casa allo stesso livello di Decima — una frase testuale pronta, scritta dal motore stesso (non da Gemini), per ogni combinazione di livello/fascia malefici già calcolata da `RuleEngineExtended.php`. Nessuna nuova dottrina astrologica da inventare: solo tradurre in linguaggio leggibile una logica che il motore applica già solo in forma numerica (`livello`, `escludi`).

Esempio concreto verificato: `calcolaLivelloLavoro()` restituisce oggi solo `{'livello': 1, 'escludi': false}` per "Giove entro l'orbo stretto in VI o X casa" (il livello più alto per Lavoro). Con questa fase, lo stesso caso includerebbe anche `'spiegazione': "Giove entro l'orbo stretto (2,5°) dalla cuspide di VI o X casa - massima protezione professionale."`, pronta per essere citata da Gemini senza che debba interpretare il numero da solo.

Da fare per ciascuna condizione (Amore, Lavoro, Salute, Casa): una tabella di corrispondenza livello→frase nello stesso file (`RuleEngineExtended.php`), seguendo lo schema già esistente per la Regola 14 di Decima.

## FASE 11 — Conversazione sulle RSM/RL escluse — PIANIFICATA

Richiesta del committente: quando una ricerca non trova risultati validi (es. Salute 2027, oggi zero risultati secchi), l'astrologo deve poter chiedere "vuoi vedere quelle scartate?" e ricevere **tutte** le RSM/RL scartate per quella condizione (non solo alcune), poter cliccare su una qualsiasi e vedere la pagina di dettaglio RSM/RL **identica in tutto** a quella attuale, con l'aggiunta di un riquadro di colore diverso in alto che mostra il motivo dell'esclusione. Se serve creare una pagina nuova, deve essere visivamente identica a quella esistente, non un nuovo design.

Implicazioni architetturali da affrontare, in ordine:

1. **Modifica al motore, solo per le condizioni a veto duro (Salute, Denaro, Denaro Low)**: oggi `ricerca_stream_api.php` scarta questi risultati con un semplice `continue`, senza salvare il motivo (commento nel codice, riga 595: "Le RS escluse dal filtro Salute NON vengono incluse nei risultati"). Va modificato per conservarli con il motivo, invece di scartarli. Decima/Amore/Lavoro/Casa non hanno bisogno di questa modifica: non scartano più duramente da tempo (UX-0015/0016/0021), restano già visibili con un livello basso.
2. **Nuova funzione nel tool AI** per recuperare tutte le RSM/RL escluse dell'ultima ricerca fatta per una condizione.
3. **Riapertura consapevole di uno scope escluso in Fase 5**: questo flusso ("vuoi vederle?" → "sì" → mostrale) richiede memoria minima della conversazione (l'ultima ricerca fatta), esplicitamente fuori scope per l'intero Provider 1 fino a questo momento. Va riaperto con una decisione esplicita, non introdotto di nascosto.
4. **Pagina di dettaglio**: riuso totale del template esistente della pagina RSM/RL, con solo l'aggiunta del riquadro colorato del motivo — nessun nuovo design.

Da pianificare in dettaglio (numero di sotto-fasi, ordine, test) solo quando il committente decide di darle priorità rispetto a Fase 8/9/10.

---

# Provider successivi

Percorso a un provider alla volta. I prossimi candidati per il benchmark (GPT, Claude, DeepSeek, Kimi o altri) verranno aggiunti qui come nuove sezioni "Provider N", con lo stesso schema di fasi 0-5, solo dopo che il tool calling con Gemini (Fasi 6-9) avrà raggiunto il Criterio di completamento.
