<?php
require_once __DIR__ . '/includes/bootstrap.php';
session_start();
require_once 'includes/Auth.php';
$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediAdmin();
$username = $auth->getCurrentUsername();
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Test AI Agent — Astrologia Attiva</title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Eb+Garamond:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
#ai-prompt {
    width: 100%;
    min-height: 120px;
    font-family: inherit;
    font-size: 14px;
    padding: 10px;
    box-sizing: border-box;
}
#ai-risposta {
    margin-top: 16px;
    padding: 14px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    white-space: pre-wrap;
    font-size: 14px;
    min-height: 40px;
}
#ai-risposta.errore {
    background: #fdecea;
    border-color: #f5c2c0;
    color: #a12a2a;
}
#ai-stato {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
}
</style>
</head>
<body>
<?php $paginaAttiva = 'test_ai_agent'; include 'includes/header_nav.php'; ?>
<main>
<div class="page-title">
<h2>🤖 Test AI Agent — Gemini (Fase 3)</h2>
</div>

<p style="font-size:13px;color:#666;max-width:700px">
Pagina di test admin-only per la prima integrazione AI Agent (Provider 1: Google Gemini).
Invia un prompt testuale semplice e verifica la risposta. Nessun tool calling in questa fase:
solo prompt &rarr; testo, coerente con la roadmap (docs/roadmaps/ROADMAP_AI.md).
</p>

<div class="controlli" id="controlli-bar" style="flex-direction:column;align-items:stretch;gap:10px">
<div class="form-group" style="width:100%">
<label>Prompt</label>
<textarea id="ai-prompt" placeholder="Scrivi qui il prompt di test, es.: Rispondi con una sola parola: OK">Rispondi con una sola parola: OK</textarea>
</div>
<button class="btn-primary" id="btn-ai-invia" style="align-self:flex-start">Invia a Gemini</button>
</div>

<div id="ai-risposta"></div>
<div id="ai-stato"></div>

<script>
document.getElementById('btn-ai-invia').addEventListener('click', async function () {
    const prompt = document.getElementById('ai-prompt').value.trim();
    const risposta = document.getElementById('ai-risposta');
    const stato = document.getElementById('ai-stato');
    const btn = this;

    risposta.classList.remove('errore');
    risposta.textContent = '';
    stato.textContent = '';

    if (!prompt) {
        risposta.classList.add('errore');
        risposta.textContent = 'Scrivi un prompt prima di inviare.';
        return;
    }

    btn.disabled = true;
    const inizio = performance.now();
    stato.textContent = 'Invio in corso...';

    try {
        const res = await fetch('api/ai_agent_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'chiedi', prompt: prompt })
        });
        const dati = await res.json();
        const durata = ((performance.now() - inizio) / 1000).toFixed(1);

        if (dati.ok) {
            risposta.textContent = dati.testo;
            stato.textContent = 'HTTP ' + res.status + ' · ' + durata + 's';
        } else {
            risposta.classList.add('errore');
            risposta.textContent = dati.errore || 'Errore sconosciuto.';
            stato.textContent = 'HTTP ' + res.status + ' · ' + durata + 's';
        }
    } catch (e) {
        risposta.classList.add('errore');
        risposta.textContent = 'Errore di rete: ' + e.message;
    } finally {
        btn.disabled = false;
    }
});
</script>
</main>
</body>
</html>
