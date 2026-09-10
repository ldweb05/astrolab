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

## FASE 3 — Test UI minimale — PIANIFICATA

Pagina `test_ai_agent.php` (admin-only, stesso pattern di test_stelline_v2.php), prima verifica end-to-end reale nel browser.

---

## FASE 4 — Hardening — PIANIFICATA

Gestione esplicita di quota esaurita, rate limit, timeout, modello non disponibile, risposta malformata; verifica che ASTROLAB continui a funzionare normalmente senza GEMINI_API_KEY o con Gemini irraggiungibile.

---

## FASE 5 — Criterio di successo — PIANIFICATA

Verifica finale: Gemini raggiungibile, chiave esterna al codice, integrazione isolata, ASTROLAB funzionante anche senza Gemini, errori API gestiti, provider sostituibile senza riscrivere il core, nessuna alterazione a logiche astronomiche/34 Regole/veti, modifiche completamente reversibili tramite Git.

Fuori scope per l'intero Provider 1: tool calling verso le funzioni ASTROLAB, chat completa, memoria conversazionale — arriveranno solo dopo la conclusione e validazione delle Fasi 0-5.

---

# Provider successivi

Percorso a un provider alla volta. I prossimi candidati per il benchmark (GPT, Claude, DeepSeek, Kimi o altri) verranno aggiunti qui come nuove sezioni "Provider N", con lo stesso schema di fasi 0-5, solo dopo che il Provider 1 (Gemini) avrà superato la Fase 5.