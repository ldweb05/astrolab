<?php
require_once __DIR__ . '/includes/bootstrap.php';

session_start();
require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();

$isAdmin = $auth->isAdmin();
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comparator RS</title>
<link rel="stylesheet" href="css/style.css">
<style>
.compare-rsm-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    margin-top: 24px;
}

.compare-rsm-card {
    min-width: 0;
}

.compare-rsm-card-wide {
    grid-column: 1 / -1;
}

.compare-chart-placeholder,
.compare-table-placeholder {
    border: 1px dashed #9aa6bd;
    border-radius: 8px;
    padding: 24px;
    margin-top: 16px;
    text-align: center;
}

.compare-chart-placeholder {
    min-height: 420px;
}

.compare-table-placeholder {
    min-height: 160px;
}

@media (max-width: 900px) {
    .compare-rsm-grid {
        grid-template-columns: 1fr;
    }

    .compare-rsm-card-wide {
        grid-column: auto;
    }
}
</style>
</head>
<body>

<?php $paginaAttiva = ''; include 'includes/header_nav.php'; ?>

<main>
<div class="page-title">
<h2>Comparator RS</h2>
</div>

<div id="compare-output">
<p>Caricamento dati...</p>
</div>
</main>

<script>
const out = document.getElementById('compare-output');
const raw = sessionStorage.getItem('astroDssConfrontoRs');

if (!raw) {
    out.innerHTML = '<p><strong>Nessun dato di confronto disponibile.</strong></p>';
} else {
    const payload = JSON.parse(raw);

    const schede = payload.risultati.map((r, i) => `
        <section class="compare-rsm-card ${i === 2 ? 'compare-rsm-card-wide' : ''}">
            <h3>RSM ${r.nome}</h3>
            <p>${r.nazione} · ${r.lat}, ${r.lon}</p>

            <div class="compare-chart-placeholder">
                Grafico RSM ${r.nome}
            </div>

            <div class="compare-table-placeholder">
                Tabella colori RSM ${r.nome}
            </div>
        </section>
    `).join('');

    out.innerHTML = `
        <div class="card">
            <h3>Comparator RS</h3>
            <p><strong>Soggetto:</strong> ${payload.soggetto.nome}</p>
            <p><strong>Anno:</strong> ${payload.anno}</p>
            <p><strong>Condizione:</strong> ${payload.condizione}</p>
            <p><strong>Località confrontate:</strong> ${payload.risultati.length}</p>

            <div class="compare-rsm-grid">
                ${schede}
            </div>
        </div>
    `;
}
</script>

</body>
</html>
