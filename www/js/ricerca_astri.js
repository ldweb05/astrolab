let regoleAstri = [];

function aggiungiRegola() {
    const pianeta = document.getElementById('nuovo-astro-select').value;
    const casa    = parseInt(document.getElementById('nuova-casa-select').value);
    const vuole   = document.getElementById('nuova-condizione-select').value === 'deve';

    const esiste = regoleAstri.some(r => String(r.pianeta) === String(pianeta));

    if (esiste) {
        alert('Esiste già una regola per ' + ASTRO_NOMI[pianeta] + '. Rimuovila prima di aggiungerne una nuova.');
        return;
    }

    regoleAstri.push({
        pianeta: pianeta === 'ASC' ? 'ASC' : parseInt(pianeta),
        casa,
        vuole
    });

    aggiornaListaRegole();
    aggiornaSommarioAstri();
}

function rimuoviRegola(index) {
    regoleAstri.splice(index, 1);
    aggiornaListaRegole();
    aggiornaSommarioAstri();
}

function resetTutteRegole() {
    if (regoleAstri.length > 0 && confirm('Rimuovere tutte le regole impostate?')) {
        regoleAstri = [];
        aggiornaListaRegole();
        aggiornaSommarioAstri();
    }
}

function aggiornaListaRegole() {
    const container = document.getElementById('regole-container');

    if (regoleAstri.length === 0) {
        container.innerHTML = '<div class="regole-vuote">Nessuna regola attiva.</div>';
        return;
    }

    let html = '';

    regoleAstri.forEach((r, idx) => {
        const pKey   = String(r.pianeta);
        const sim    = ASTRO_SIMBOLI[pKey] || '★';
        const nome   = ASTRO_NOMI[pKey] || pKey;
        const azione = r.vuole ? '✓ VOGLIO in' : '✗ NON VOGLIO in';
        const cls    = r.vuole ? 'deve' : 'evita';

        html += `<div class="regola-item ${cls}">
<div class="regola-info">
<span class="astro-simbolo">${sim}</span>
<span class="astro-nome">${nome}</span>
</div>
<div class="regola-azione ${cls}">${azione}</div>
<div class="casa-numero">Casa ${r.casa}</div>
<button class="btn-rimuovi" onclick="rimuoviRegola(${idx})">✕</button>
</div>`;
    });

    container.innerHTML = html;
}

function aggiornaSommarioAstri() {
    const sommario = document.getElementById('astri-sommario');
    const tags     = document.getElementById('astri-sommario-tags');

    if (regoleAstri.length === 0) {
        sommario.classList.remove('visibile');
        return;
    }

    tags.innerHTML = regoleAstri.map(r => {
        const pKey = String(r.pianeta);
        const sim  = ASTRO_SIMBOLI[pKey] || '★';
        const nome = ASTRO_NOMI[pKey] || pKey;
        const cls  = r.vuole ? 'tag-deve' : 'tag-evita';
        const txt  = r.vuole ? `→ Casa ${r.casa}` : `✗ Casa ${r.casa}`;

        return `<span class="tag-regola ${cls}">${sim} ${nome} ${txt}</span>`;
    }).join('');

    sommario.classList.add('visibile');
}

function buildAstriInCasaParam() {
    return regoleAstri.map(r => ({
        pianeta: r.pianeta,
        casa: r.casa,
        vuole: r.vuole
    }));
}