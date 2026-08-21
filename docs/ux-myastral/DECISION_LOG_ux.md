# Registro delle decisioni UX

Questo documento contiene esclusivamente decisioni formalmente valutate.

## Formato

### UX-0001 — Titolo

- **Data:**
- **Area:**
- **Stato:** PROPOSTA / APPROVATA / RESPINTA / SUPERATA
- **Problema osservato:**
- **Evidenze:**
- **Confronto MyAstral.org / Astrolab:**
- **Decisione:**
- **Motivazione:**
- **Beneficio atteso:** BASSO / MEDIO / ALTO
- **Costo tecnico stimato:** BASSO / MEDIO / ALTO / DA VALUTARE
- **Rischi:**
- **Documento collegato:**
- **Eventuale voce della roadmap tecnica:**

---

Nessuna decisione registrata.

### UX-0014 - Reverse geocoding del nome posizione dopo "USA QUESTA POSIZIONE" su mappa (rs.php)

- **Data:** 2026-08-21
- **Area:** `www/rs.php` - mappa flottante, funzione `usaPosizione()`
- **Stato:** APPROVATA
- **Problema osservato:** dopo aver spostato il puntatore sulla mappa e cliccato "USA QUESTA
  POSIZIONE", il grafico e le coordinate si aggiornavano correttamente per la nuova posizione,
  ma il nome della località nell'header restava quello della posizione di partenza, perché
  `usaPosizione()` aggiornava solo lat/lon senza mai toccare il campo `luogo-rs-input` da cui
  `calcolaRS()` legge il nome da mostrare.
- **Decisione:** al click su "USA QUESTA POSIZIONE", `usaPosizione()` esegue ora una chiamata di
  reverse geocoding a Nominatim (stesso servizio già usato per il forward geocoding in
  `cercaLuogoRS()`, riusata la funzione esistente `_estraiNomeLuogoNominatim()`) per ottenere il
  nome reale della nuova posizione; se la chiamata fallisce o non produce un nome utilizzabile,
  il campo viene impostato a `"NaN"` invece di lasciare il vecchio nome.
- **Motivazione:** la posizione scelta a mano sulla mappa non ha un nome noto in memoria (nessun
  geocoding inverso esisteva prima); recuperarlo al volo da Nominatim evita di mostrare un nome
  falso/obsoleto mantenendo comunque un'esperienza utile quando il nome è disponibile.
- **Beneficio atteso:** MEDIO - corregge un'incoerenza visibile dell'header senza toccare calcolo
  o coordinate, già corretti.
- **Costo tecnico stimato:** BASSO - riuso di funzione e pattern di chiamata Nominatim già
  esistenti nello stesso file.
- **Rischi:** dipendenza da disponibilità/latenza del servizio Nominatim al momento del click;
  mitigato dal fallback "NaN" e dal fatto che grafico e coordinate restano corretti anche se la
  chiamata fallisce.
- **Documento collegato:** nessuno specifico: fix isolato, non collegato alla roadmap 34 regole.
