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

.compare-ril-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
    font-size: 13px;
}

.compare-ril-table th,
.compare-ril-table td {
    padding: 8px;
    border-bottom: 1px solid #dfe3eb;
    text-align: left;
}

.compare-ril-table th {
    background: #f4f6fa;
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

<script src="js/zodiac_wheel.js"></script>
<script src="js/svg_zoom.js"></script>
<script>
const out = document.getElementById('compare-output');
const raw = sessionStorage.getItem('astroDssConfrontoRiloc');

if (!raw) {
    out.innerHTML = '<p><strong>Nessun dato di confronto disponibile.</strong></p>';
} else {
    (async () => {
    try {
        const payload = JSON.parse(raw);
        const risultati = Array.isArray(payload.risultati)
            ? payload.risultati
            : [];
        const autorizzazione = await fetch('api/comparator_api.php', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tipo: 'rilocazioni',
                totale: risultati.length
            })
        });

        const esitoAutorizzazione = await autorizzazione.json();

        if (!autorizzazione.ok || !esitoAutorizzazione.ok) {
            throw new Error(
                esitoAutorizzazione.errore
                    || 'Confronto non autorizzato.'
            );
        }

        const soggetto = payload.soggetto;
        const nomeSoggetto = soggetto?.nome || 'Non disponibile';
        const datiSoggettoValidi = soggetto &&
            ['giorno', 'mese', 'anno', 'ora_gmt'].every(campo =>
                soggetto[campo] !== undefined &&
                soggetto[campo] !== null &&
                soggetto[campo] !== ''
            );

        const renderMatch = (matches, pianeta) => {
            if (!Array.isArray(matches) || matches.length === 0) {
                return `
                    <tr>
                        <td>${pianeta}</td>
                        <td colspan="3">Nessun match</td>
                    </tr>
                `;
            }

            return matches.map(match => `
                <tr>
                    <td>${pianeta}</td>
                    <td>${match.nome || match.casa || '—'}</td>
                    <td>${match.cuspide || '—'}</td>
                    <td>${match.distanza ?? '—'}°</td>
                </tr>
            `).join('');
        };

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

                    <div class="compare-chart-placeholder tema-box">
                        <p id="wheel-status-${i}" class="compare-wheel-status">
                            Caricamento tema rilocato...
                        </p>
                        <svg
                            id="wheel-riloc-${i}"
                            class="compare-wheel-svg"
                            role="img"
                            aria-label="Ruota astrologica rilocata per ${localita}">
                        </svg>
                        <p id="wheel-angles-${i}" class="compare-wheel-angles">
                            ASC: — · MC: —
                        </p>
                    </div>

                    <table class="compare-ril-table">
                        <thead>
                            <tr>
                                <th>Pianeta</th>
                                <th>Casa</th>
                                <th>Cuspide</th>
                                <th>Distanza</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${renderMatch(r.match_venere, '♀ Venere')}
                            ${renderMatch(r.match_giove, '♃ Giove')}
                        </tbody>
                    </table>
                </section>
            `;
        }).join('');

        out.innerHTML = `
            <div class="card">
                <h3>Comparatore rilocazioni</h3>
                <p><strong>Soggetto:</strong> ${nomeSoggetto}</p>
                <p><strong>Località confrontate:</strong> ${risultati.length}</p>
                <div class="compare-ril-grid">
                    ${schede}
                </div>
            </div>
        `;

        const caricaRuotaRilocata = async (risultato, indice) => {
            const status = document.getElementById(`wheel-status-${indice}`);
            const angles = document.getElementById(`wheel-angles-${indice}`);
            const svgId = `wheel-riloc-${indice}`;
            const lat = Number.parseFloat(risultato.lat);
            const lon = Number.parseFloat(risultato.lon);

            try {
                if (!datiSoggettoValidi) {
                    throw new Error('Dati natali del soggetto incompleti');
                }

                if (!Number.isFinite(lat) || !Number.isFinite(lon)) {
                    throw new Error('Coordinate della località non valide');
                }

                const url = 'api/tema_api.php?tipo=natale' +
                    '&g='       + encodeURIComponent(soggetto.giorno) +
                    '&m='       + encodeURIComponent(soggetto.mese) +
                    '&a='       + encodeURIComponent(soggetto.anno) +
                    '&ora_gmt=' + encodeURIComponent(soggetto.ora_gmt) +
                    '&lat='     + encodeURIComponent(lat) +
                    '&lon='     + encodeURIComponent(lon);

                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`Richiesta HTTP ${response.status}`);
                }

                const temaRiloc = await response.json();

                if (temaRiloc?.errore) {
                    throw new Error(temaRiloc.errore);
                }

                ZodiacWheel.disegna(svgId, temaRiloc, {size: 480});
                initSvgZoom(svgId);

                const asc = temaRiloc.case?.ASC?.posizione?.stringa ?? '—';
                const mc = temaRiloc.case?.MC?.posizione?.stringa ?? '—';

                angles.textContent = `ASC: ${asc} · MC: ${mc}`;
                status.hidden = true;
            } catch (errore) {
                status.textContent = `Impossibile caricare la ruota: ${errore.message}`;
                angles.textContent = 'ASC: — · MC: —';
            }
        };

        risultati.forEach((risultato, indice) => {
            caricaRuotaRilocata(risultato, indice);
        });
    } catch (errore) {
        out.innerHTML = `<p><strong>${errore.message || 'Dati di confronto non validi.'}</strong></p>`;
    }
    })();
}
</script>

</body>
</html>
