/**
 * coord_gms.js — Formattazione coordinate geografiche in GMS (Gradi, Minuti, Secondi)
 *
 * Solo VISUALIZZAZIONE: i valori decimali usati per calcoli, API e DB restano invariati.
 * Esempio: formattaCoordGMS(41.9028, 'lat')  -> 41°54′10″ N
 *          formattaCoordGMS(-12.4964, 'lon') -> 12°29′47″ W
 *
 * @param {number|string} valore  coordinata in gradi decimali
 * @param {'lat'|'lon'}   tipo    'lat' -> N/S, 'lon' -> E/W
 * @returns {string}
 */
function formattaCoordGMS(valore, tipo) {
    const n = Number(valore);
    if (valore === null || valore === undefined || valore === '' || !isFinite(n)) {
        return (valore === null || valore === undefined) ? '' : String(valore);
    }
    const emisfero = (tipo === 'lat') ? (n >= 0 ? 'N' : 'S') : (n >= 0 ? 'E' : 'W');
    // arrotondamento sul totale dei secondi: evita overflow tipo 59′60″
    const totSec = Math.round(Math.abs(n) * 3600);
    const g = Math.floor(totSec / 3600);
    const m = Math.floor((totSec % 3600) / 60);
    const s = totSec % 60;
    return g + '°' + String(m).padStart(2, '0') + '′' +
           String(s).padStart(2, '0') + '″ ' + emisfero;
}
