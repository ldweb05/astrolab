function buildPaginazione(pagina, totPagine) {
    if (totPagine <= 1) return '';

    let html = '<div class="paginazione">';

    if (pagina > 1) {
        html += `<button onclick="vaiPagina(1)">«</button>`;
        html += `<button onclick="vaiPagina(${pagina-1})">‹</button>`;
    }

    const start = Math.max(1, pagina-3);
    const end   = Math.min(totPagine, start+6);

    for (let i = start; i <= end; i++) {
        html += `<button onclick="vaiPagina(${i})" class="${i===pagina?'attiva':''}">${i}</button>`;
    }

    if (pagina < totPagine) {
        html += `<button onclick="vaiPagina(${pagina+1})">›</button>`;
        html += `<button onclick="vaiPagina(${totPagine})">»</button>`;
    }

    return html + '</div>';
}

function vaiPagina(p) {
    stato.pagina = p;
    renderTabella();
    window.scrollTo(0,0);
}

function setPerPagina(v) {
    stato.perPagina = parseInt(v)||50;
    stato.pagina = 1;
    renderTabella();
}
