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

## FASE 1 — Provider Layer — DA INIZIARE

Obiettivo: creare l'astrazione del provider AI, isolata e sostituibile.

Attività previste:
- `www/includes/ai/AiProviderInterface.php` — contratto minimo (chiedi(string $prompt): array, ritorno sempre ['ok'=>bool,'testo'=>...,'errore'=>...], mai eccezioni non gestite);
- `www/includes/ai/GeminiProvider.php` — implementazione concreta via curl verso gemini-3.6-flash, gestione esplicita di timeout, errori HTTP, quota esaurita, risposta malformata;
- nessuna libreria esterna, nessun Composer (coerente con il resto del progetto).

---

## FASE 2 — Primo endpoint — PIANIFICATA

`www/api/ai_agent_api.php`, stesso stile procedurale delle altre API di ASTROLAB, autenticato come le altre.

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