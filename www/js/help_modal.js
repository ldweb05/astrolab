// help_modal.js — Logica per il modale di Aiuto contestuale

(function() {
    'use strict';

    // Mappatura pagina -> chiave nel JSON
    const pageMap = {
        'login.php': 'login',
        'registrazione.php': 'registrazione',
        'cambia_password.php': 'cambia_password'
    };

    let helpData = null;

    // Carica i contenuti della Sezione 1
    function loadHelpContent() {
        if (helpData) return Promise.resolve(helpData);
        return fetch('js/help_content_s1.json')
            .then(r => r.json())
            .then(data => { helpData = data; return data; })
            .catch(() => null);
    }

    // Rileva la pagina corrente
    function getCurrentPageKey() {
        const path = window.location.pathname.split('/').pop() || 'index.php';
        return pageMap[path] || 'default';
    }

    // Funzione globale per aprire il modale
    window.openHelpModal = function() {
        const overlay = document.getElementById('help-modal-overlay');
        const body = document.getElementById('help-modal-body');
        const title = document.querySelector('.help-modal-title');
        if (!overlay || !body) return;

        overlay.style.display = 'block';
        body.innerHTML = '<p><em>Caricamento...</em></p>';

        loadHelpContent().then(data => {
            if (!data) {
                body.innerHTML = '<p><em>Contenuto non disponibile.</em></p>';
                return;
            }
            const key = getCurrentPageKey();
            const page = data.pages[key] || data.pages['default'];
            if (title) title.textContent = '❓ ' + (page.title || data.section_title);
            body.innerHTML = page.content || '<p><em>Nessun contenuto disponibile.</em></p>';
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('help-modal-overlay');
        const closeBtn = document.getElementById('help-modal-close');

        if (!overlay) return;

        function closeModal() {
            overlay.style.display = 'none';
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.style.display === 'block') closeModal();
        });
    });
})();
