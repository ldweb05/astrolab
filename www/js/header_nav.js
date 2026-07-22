'use strict';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.header-inner').forEach((header) => {
        const toggle = header.querySelector('.nav-toggle');
        const nav = header.querySelector('.main-nav');
        const dropdown = header.querySelector('.nav-dropdown');
        const dropdownTrigger = header.querySelector('.nav-dropdown-trigger');

        if (!toggle || !nav) return;

        const closeMenu = () => {
            header.classList.remove('menu-open');
            nav.classList.remove('is-open');
            dropdown?.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Apri menu di navigazione');
            toggle.textContent = '☰';
            dropdownTrigger?.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', () => {
            const open = !nav.classList.contains('is-open');

            if (!open) {
                closeMenu();
                return;
            }

            header.classList.add('menu-open');
            nav.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', 'Chiudi menu di navigazione');
            toggle.textContent = '✕';
        });

        dropdownTrigger?.addEventListener('click', () => {
            if (window.innerWidth > 900 || !dropdown) return;

            const open = dropdown.classList.toggle('is-open');
            dropdownTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        nav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMenu);
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 900) closeMenu();
        });
    });
});
