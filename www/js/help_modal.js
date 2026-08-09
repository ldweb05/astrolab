// help_modal.js — Logica per il modale di Aiuto contestuale con menu sezioni

(function() {
    'use strict';

    // Cache dei contenuti per sezione
    const sectionCache = {};

    // Mappatura pagina -> sezione default (per aiuto contestuale futuro)
    const pageToSection = {
        'login.php': 1,
        'registrazione.php': 1,
        'cambia_password.php': 1,
        'index.php': 2,
        'tema.php': 3,
        'rs.php': 3,
        'rl.php': 3,
        'rilocazione.php': 3,
        'ricerca.php': 4,
        'stampa.php': 5,
        'compare_rs.php': 6,
        'compare_ril.php': 6
    };

    // Titoli delle sezioni
    const sectionTitles = {
        1: 'Introduzione e Account',
        2: 'Gestione Soggetti',
        3: 'Calcoli e Analisi',
        4: 'Ricerca Geografica Avanzata',
        5: 'Report, Narrazione e Stampa',
        6: 'Comparatore e Supporto Decisionale',
        7: 'Interfaccia e Visualizzazione',
        8: 'FAQ e Limiti Operativi'
    };

    // Carica i contenuti JSON di una sezione
    function loadSection(num) {
        if (sectionCache[num]) return Promise.resolve(sectionCache[num]);
        return fetch('js/help_content_s' + num + '.json')
            .then(function(r) {
                if (!r.ok) throw new Error('Not found');
                return r.json();
            })
            .then(function(data) {
                sectionCache[num] = data;
                return data;
            })
            .catch(function() { return null; });
    }

    // Rileva la sezione default dalla pagina corrente
    function getDefaultSection() {
        var path = window.location.pathname.split('/').pop() || 'index.php';
        return pageToSection[path] || 1;
    }

    // Apre il modale con il contenuto di una sezione specifica
    window.openHelpSection = function(sectionNum) {
        var overlay = document.getElementById('help-modal-overlay');
        var body = document.getElementById('help-modal-body');
        var title = document.querySelector('.help-modal-title');
        if (!overlay || !body) return;

        // Chiude il dropdown se aperto
        var dropdown = document.querySelector('.help-dropdown');
        if (dropdown) dropdown.classList.remove('active');

        overlay.style.display = 'block';
        body.innerHTML = '<p><em>Caricamento...</em></p>';
        if (title) title.textContent = '\u2753 ' + (sectionTitles[sectionNum] || 'Aiuto');

        loadSection(sectionNum).then(function(data) {
            if (!data) {
                body.innerHTML = '<p><em>\ud83d\udea7 Questa sezione \u00e8 in fase di redazione.</em></p>' +
                    '<p>Torna pi\u00f9 tardi o consulta la <strong>Sezione 1</strong> per informazioni su account e accesso.</p>';
                return;
            }
            // Se c'\u00e8 un contenuto specifico per la pagina corrente, usalo
            var path = window.location.pathname.split('/').pop() || '';
            var pageKey = path.replace('.php', '');
            var pageContent = data.pages && (data.pages[pageKey] || data.pages['default']);
            if (pageContent) {
                if (title) title.textContent = '\u2753 ' + (pageContent.title || sectionTitles[sectionNum]);
                body.innerHTML = pageContent.content || '<p><em>Nessun contenuto disponibile.</em></p>';
            } else if (data.default_content) {
                body.innerHTML = data.default_content;
            } else {
                body.innerHTML = '<p><em>Contenuto non disponibile per questa pagina.</em></p>';
            }
        });
    };

    // Funzione legacy: apre la sezione default per la pagina corrente
    window.openHelpModal = function() {
        window.openHelpSection(getDefaultSection());
    };

    // Gestione chiusura modale
    document.addEventListener('DOMContentLoaded', function() {
        var overlay = document.getElementById('help-modal-overlay');
        var closeBtn = document.getElementById('help-modal-close');
        if (!overlay) return;

        function closeModal() {
            overlay.style.display = 'none';
        }

        if (closeBtn) closeBtn.addEventListener('click', closeModal);

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (overlay.style.display === 'block') closeModal();
                var dropdown = document.querySelector('.help-dropdown');
                if (dropdown) dropdown.classList.remove('active');
            }
        });

        // Chiude il dropdown cliccando fuori
        document.addEventListener('click', function(e) {
            var dropdown = document.querySelector('.help-dropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });
})();
