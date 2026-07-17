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
<title>Comparatore rilocazioni</title>
<link rel="stylesheet" href="css/style.css">
<style>
.compare-ril-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    margin-top: 24px;
}

.compare-ril-card {
    min-width: 0;
}

.compare-ril-card-wide {
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
    .compare-ril-grid {
        grid-template-columns: 1fr;
    }

    .compare-ril-card-wide {
        grid-column: auto;
    }
}
</style>
</head>
<body>

<?php $paginaAttiva = ''; include 'includes/header_nav.php'; ?>

<main>
<div class="page-title">
<h2>Comparatore rilocazioni</h2>
</div>

<div id="compare-output">
<p>Caricamento dati...</p>
</div>
</main>

<script>
const out = document.getElementById('compare-output');
const raw = sessionStorage.getItem('astroDssConfrontoRiloc');

if (!raw) {
    out.innerHTML = '<p><strong>Nessun dato di confronto disponibile.</strong></p>';
} else {
    try {
        const payload = JSON.parse(raw);
        const risultati = Array.isArray(payload.risultati)
            ? payload.risultati
            : [];
        const nomeSoggetto = payload.soggetto?.nome || 'Non disponibile';

        const schede = risultati.map((r, i) => {
            const localita = r.citta || r.nome || r.iata || r.icao || 'Località';
            const aeroporto = r.nome || 'Aeroporto non disponibile';
            const codice = r.iata || r.icao || '—';
            const nazione = r.nazione || '—';

            return `
                <section class="compare-ril-card ${i === 2 ? 'compare-ril-card-wide' : ''}">
                    <h3>${localita}</h3>
                    <p><strong>Aeroporto:</strong> ${aeroporto} (${codice})</p>
                    <p><strong>Nazione:</strong> ${nazione}</p>
                    <p><strong>Coordinate:</strong> ${r.lat}, ${r.lon}</p>

                    <div class="compare-chart-placeholder">
                        Grafico rilocazione ${localita}
                    </div>

                    <div class="compare-table-placeholder">
                        Dati rilocazione ${localita}
                    </div>
                </section>
            `;
        }).join('');

        out.innerHTML = `
            <div class="card">
                <h3>Comparatore rilocazioni</h3>
                <p><strong>Soggetto:</strong> ${nomeSoggetto}</p>
                <p><strong>Località confrontate:</strong> ${risultati.length}</p>

                <details style="margin:20px 0">
                    <summary><strong>Payload JSON</strong></summary>
                    <pre>${JSON.stringify(payload, null, 2)}</pre>
                </details>

                <div class="compare-ril-grid">
                    ${schede}
                </div>
            </div>
        `;
    } catch (errore) {
        out.innerHTML = '<p><strong>Dati di confronto non validi.</strong></p>';
    }
}
</script>

</body>
</html>
