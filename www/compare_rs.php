<?php
require_once __DIR__ . '/includes/bootstrap.php';

session_start();
require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();

$isAdmin = $auth->isAdmin();
$username = $auth->getCurrentUsername();
$soggettoNome = $auth->getSoggettoNome();
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

.compare-wheel-svg {
    display: block;
    width: 100%;
    height: auto;
}

.compare-wheel-status {
    margin: 0 0 12px;
}

.compare-wheel-angles {
    margin: 12px 0 0;
    font-weight: 600;
}

.compare-table-placeholder {
    min-height: 160px;
}

.compare-rsm-table {
    width: 100%;
    margin-top: 16px;
    border-collapse: collapse;
}

.compare-rsm-table th,
.compare-rsm-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #d9deea;
    text-align: left;
}

.compare-rsm-table th:nth-child(2),
.compare-rsm-table td:nth-child(2),
.compare-rsm-table th:nth-child(3),
.compare-rsm-table td:nth-child(3) {
    text-align: center;
}

.compare-rsm-dot {
    display: inline-block;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    vertical-align: middle;
}

.compare-rsm-dot-positivo {
    background: #2eaf5d;
}

.compare-rsm-dot-neutro {
    background: #e0b51b;
}

.compare-rsm-dot-negativo {
    background: #d64545;
}

.compare-rsm-table-status {
    margin-top: 16px;
    text-align: center;
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

<script src="js/zodiac_wheel.js"></script>
<script src="js/svg_zoom.js"></script>
<script>
const out = document.getElementById('compare-output');
const raw = sessionStorage.getItem('astroDssConfrontoRs');

if (!raw) {
    out.innerHTML = '<p><strong>Nessun dato di confronto disponibile.</strong></p>';
} else {
    try {
        const payload = JSON.parse(raw);
        const risultati = Array.isArray(payload.risultati)
            ? payload.risultati
            : [];
        const soggetto = payload.soggetto;
        const nomeSoggetto = soggetto?.nome
            || <?= json_encode($soggettoNome ?: 'Non disponibile') ?>;
        const datiSoggettoValidi = soggetto &&
            ['giorno', 'mese', 'anno', 'ora_gmt', 'lat', 'lon'].every(campo =>
                soggetto[campo] !== undefined &&
                soggetto[campo] !== null &&
                soggetto[campo] !== ''
            );
        const annoRsValido = payload.anno !== undefined &&
            payload.anno !== null &&
            payload.anno !== '';
        const condizione = payload.condizione || '';
        const modalita = payload.modalita === 'astri' ? 'astri' : 'standard';
        const astriInCasa = Array.isArray(payload.astri_in_casa)
            ? payload.astri_in_casa
            : [];

        const schede = risultati.map((r, i) => {
            const localita = r.citta || r.nome || r.iata || r.icao || 'Località';
            const nazione = r.nazione || '—';

            return `
                <section class="compare-rsm-card ${i === 2 ? 'compare-rsm-card-wide' : ''}">
                    <h3>RSM ${localita}</h3>
                    <p>${nazione} · ${r.lat}, ${r.lon}</p>

                    <div class="compare-chart-placeholder tema-box">
                        <p id="wheel-status-${i}" class="compare-wheel-status">
                            Caricamento rivoluzione solare...
                        </p>
                        <svg
                            id="wheel-rs-${i}"
                            class="compare-wheel-svg"
                            role="img"
                            aria-label="Ruota della rivoluzione solare per ${localita}">
                        </svg>
                        <p id="wheel-angles-${i}" class="compare-wheel-angles">
                            ASC: — · MC: —
                        </p>
                    </div>

                    <div id="table-rs-${i}" class="compare-rsm-table-status">
                        Caricamento tabella...
                    </div>
                </section>
            `;
        }).join('');

        out.innerHTML = `
            <div class="card">
                <h3>Comparator RS</h3>
                <p><strong>Soggetto:</strong> ${nomeSoggetto}</p>
                <p><strong>Anno:</strong> ${payload.anno}</p>
                <p><strong>Condizione:</strong> ${condizione}</p>
                <p><strong>Località confrontate:</strong> ${risultati.length}</p>

                <div class="compare-rsm-grid">
                    ${schede}
                </div>
            </div>
        `;

        const caricaRuotaRs = async (risultato, indice) => {
            const status = document.getElementById(`wheel-status-${indice}`);
            const angles = document.getElementById(`wheel-angles-${indice}`);
            const svgId = `wheel-rs-${indice}`;
            const latRs = Number.parseFloat(risultato.lat);
            const lonRs = Number.parseFloat(risultato.lon);
            const luogoRs = risultato.citta ||
                risultato.nome ||
                risultato.iata ||
                risultato.icao ||
                'Località';

            try {
                if (!datiSoggettoValidi) {
                    throw new Error('Dati natali del soggetto incompleti');
                }

                if (!annoRsValido) {
                    throw new Error('Anno della rivoluzione solare non valido');
                }

                if (!Number.isFinite(latRs) || !Number.isFinite(lonRs)) {
                    throw new Error('Coordinate della località non valide');
                }

                const url = 'api/rs_api.php?' +
                    'g='           + encodeURIComponent(soggetto.giorno) +
                    '&m='          + encodeURIComponent(soggetto.mese) +
                    '&a='          + encodeURIComponent(soggetto.anno) +
                    '&ora_gmt='    + encodeURIComponent(soggetto.ora_gmt) +
                    '&lat='        + encodeURIComponent(soggetto.lat) +
                    '&lon='        + encodeURIComponent(soggetto.lon) +
                    '&anno='       + encodeURIComponent(payload.anno) +
                    '&lat_rs='     + encodeURIComponent(latRs) +
                    '&lon_rs='     + encodeURIComponent(lonRs) +
                    '&condizione='   + encodeURIComponent(condizione) +
                    '&modalita='     + encodeURIComponent(modalita) +
                    '&astri_in_casa=' + encodeURIComponent(JSON.stringify(astriInCasa)) +
                    '&luogo_rs='     + encodeURIComponent(luogoRs);

                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`Richiesta HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data?.errore) {
                    throw new Error(data.errore);
                }

                if (!data?.tema_rs) {
                    throw new Error('Tema RS non presente nella risposta');
                }

                ZodiacWheel.disegna(svgId, data.tema_rs, {size: 480});
                initSvgZoom(svgId);

                const tableContainer = document.getElementById(`table-rs-${indice}`);
                const righe = Array.isArray(data.tabella_confronto)
                    ? data.tabella_confronto
                    : [];

                tableContainer.innerHTML = `
                    <table class="compare-rsm-table">
                        <thead>
                            <tr>
                                <th>Pianeta</th>
                                <th>Casa</th>
                                <th>Stato</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${righe.map(riga => `
                                <tr>
                                    <td>${riga.pianeta}</td>
                                    <td>${riga.casa}</td>
                                    <td>
                                        <span
                                            class="compare-rsm-dot compare-rsm-dot-${riga.stato}"
                                            aria-label="${riga.stato}">
                                        </span>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;

                const asc = data.tema_rs.case?.ASC?.posizione?.stringa ?? '—';
                const mc = data.tema_rs.case?.MC?.posizione?.stringa ?? '—';

                angles.textContent = `ASC: ${asc} · MC: ${mc}`;
                status.hidden = true;
            } catch (errore) {
                status.textContent = `Impossibile caricare la ruota: ${errore.message}`;
                angles.textContent = 'ASC: — · MC: —';

                const tableContainer = document.getElementById(`table-rs-${indice}`);
                if (tableContainer) {
                    tableContainer.textContent = 'Impossibile caricare la tabella.';
                }
            }
        };

        risultati.forEach((risultato, indice) => {
            caricaRuotaRs(risultato, indice);
        });
    } catch (errore) {
        out.innerHTML = '<p><strong>Dati di confronto non validi.</strong></p>';
    }
}
</script>

</body>
</html>
