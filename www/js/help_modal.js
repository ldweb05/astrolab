// help_modal.js — Logica per il modale di Aiuto contestuale

(function() {
    'use strict';
    
    // Funzione globale per aprire il modale (chiamata dal trigger nell'header)
    window.openHelpModal = function() {
        const overlay = document.getElementById('help-modal-overlay');
        if (overlay) {
            overlay.style.display = 'block';
        }
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

        // Chiudi cliccando fuori dal contenuto
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeModal();
            }
        });

        // Chiudi con tasto ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.style.display === 'block') {
                closeModal();
            }
        });
    });
})();
