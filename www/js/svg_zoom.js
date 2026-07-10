/**
 * svg_zoom.js — Zoom/resize in-line per le ruote zodiacali SVG
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * Uso: chiamare initSvgZoom('idSvg') dopo che il <svg> esiste nel DOM
 * (anche se il suo contenuto viene rigenerato successivamente da
 * ZodiacWheel.disegna — il binding resta sul contenitore .tema-box).
 *
 * Comportamento:
 *  - click sul <svg> → toggle classe "zoomed" sul .tema-box genitore
 *  - da zoomed, l'utente può anche ridimensionare con resize:both (CSS)
 *  - un secondo click riporta alle dimensioni di default
 */
'use strict';

function initSvgZoom(svgId) {
    const svg = document.getElementById(svgId);
    if (!svg || svg.dataset.zoomBound === '1') return;
    svg.dataset.zoomBound = '1';

    const box = svg.closest('.tema-box');
    if (!box) return;
    box.classList.add('svg-zoomable');

    let customSize = null;
    svg.addEventListener('click', () => {
        const isZoomed = box.classList.toggle('zoomed');
        if (isZoomed && customSize) {
            box.style.width  = customSize.width;
            box.style.height = customSize.height;
        } else if (!isZoomed) {
            const rect = box.getBoundingClientRect();
            customSize = { width: rect.width + 'px', height: rect.height + 'px' };
            box.style.width  = '';
            box.style.height = '';
        }
    });
}
