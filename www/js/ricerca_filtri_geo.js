function onMacroAreaChange(val) {
    // Il valore viene letto al momento dell'avvio ricerca tramite getNazioniFiltro()
}

function getNazioniFiltro() {
    const tipoLocalitaEl = document.getElementById('tipo-localita');
    const tipoLocalita = tipoLocalitaEl ? tipoLocalitaEl.value : 'aeroporti';

    if (tipoLocalita === 'localita') {
        const nazioneLocalitaEl = document.getElementById('nazione-localita');
        const nazioneLocalita = nazioneLocalitaEl
            ? nazioneLocalitaEl.value.trim().toUpperCase()
            : '';

        return nazioneLocalita ? [nazioneLocalita] : [];
    }

    const areaEl = document.getElementById('filtro-macro-area');
    const area = areaEl ? areaEl.value : '';

    if (area && MACRO_AREE[area]) {
        return MACRO_AREE[area];
    }

    return [];
}

function getFasceOrarieFiltro() {
    const lonMin = document.getElementById('filt-lon-min').value.trim();
    const lonMax = document.getElementById('filt-lon-max').value.trim();

    if (lonMin === '' && lonMax === '') {
        return null;
    }

    return {
        lon_min: lonMin !== '' ? parseFloat(lonMin) : -180,
        lon_max: lonMax !== '' ? parseFloat(lonMax) : 180,
    };
}

function aggiungiParamsGeografici(params) {
    const tipoLocalitaEl = document.getElementById('tipo-localita');
    const tipoLocalita = tipoLocalitaEl ? tipoLocalitaEl.value : 'aeroporti';

    params.set('tipo_localita', tipoLocalita);

    if (tipoLocalita === 'localita') {
        const numeroLocalitaEl = document.getElementById('numero-localita');
        const numeroLocalita = numeroLocalitaEl ? numeroLocalitaEl.value : '50';
        params.set('numero_localita', numeroLocalita);
    }

    const nazioni = getNazioniFiltro();

    if (nazioni.length > 0) {
        params.set('nazioni_filtro', nazioni.join(','));
    }

    const fascia = getFasceOrarieFiltro();

    if (fascia) {
        params.set('lon_min', fascia.lon_min);
        params.set('lon_max', fascia.lon_max);
    }
}