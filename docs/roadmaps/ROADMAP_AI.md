# ROADMAP AI AGENT — ASTROLAB

Documento dedicato al percorso di integrazione di un AI Agent in ASTROLAB, un provider LLM alla volta. Contiene lo stato di avanzamento per fasi. Per i principi e le regole operative permanenti dell'AI Agent (sezioni 0-26), vedi `docs/roadmaps/ROADMAP_AI_PRINCIPI.md`.

> **Nota**
> Questo documento è il riferimento ufficiale per tutto ciò che riguarda l'integrazione AI in ASTROLAB. `docs/roadmaps/ROADMAP.md` non descrive queste attività; `docs/PROMPT_OPERATIVO_ASTROLAB.md` mantiene solo le regole operative generali del progetto e rimanda qui.

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

**Provider 1 (Google Gemini) formalmente concluso.** Tutti i criteri di successo soddisfatti.

Fuori scope per l'intero Provider 1: tool calling verso le funzioni ASTROLAB, chat completa, memoria conversazionale — restano fuori scope anche ora che il Provider 1 è concluso; arriveranno solo con una futura roadmap dedicata al tool calling, dopo l'eventuale aggiunta di ulteriori provider per il benchmark (Sezione 22).

---

# Provider successivi

Percorso a un provider alla volta. I prossimi candidati per il benchmark (GPT, Claude, DeepSeek, Kimi o altri) verranno aggiunti qui come nuove sezioni "Provider N", con lo stesso schema di fasi 0-5, solo dopo che il Provider 1 (Gemini) avrà superato la Fase 5.