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

### UX-0001 — Sblocco condizionato del FREEZE del Rule Engine per allineamento a MyAstral.org

- **Data:** 2026-08-17
- **Area:** Rule Engine (`includes/RuleEngine.php`) — valutazione a stelle delle RSM/RL
- **Stato:** APPROVATA
- **Problema osservato:** confronto diretto RSM (Jannik Sinner, anno 2025, condizione Decima,
  ricerca aeroporti) mostra risultati radicalmente diversi tra Astrolab (685 risultati, tetto
  rigido a 5 stelle) e MyAstral.org (8 aeroporti raccomandati). Causa: tetto a 5 stelle e assenza
  di regola di bonus "Giove entro 2° dalla cuspide della X casa" nel Rule Engine attuale.
- **Evidenze:** screenshot ricerca comparata Sinner RS 2025 condizione Decima; dettaglio in
  `docs/ROADMAP_MYASTRAL_UX.md` §3.
- **Confronto MyAstral.org / Astrolab:** secondo l'intervista di Discepolo riportata dal
  committente, MyAstral usa una scala fino a 8-10 stelle con bonus per Giove angolare in X;
  Astrolab tronca a 5 e non ha tale bonus.
- **Decisione:** il FREEZE dichiarato in `docs/roadmap_comparazione_myastral.md` (120 Rule) resta
  valido per il Rule Engine di default, invariato. Si autorizza lo sviluppo di una logica
  **parallela e opzionale**, attivabile solo tramite feature flag `MYASTRAL_ALIGNMENT_MODE`
  (default: OFF), che implementa scala estesa (roadmap §3.1) e bonus Giove angolare in X
  (roadmap §3.2) senza modificare né sostituire le funzioni esistenti. A flag OFF il
  comportamento resta identico all'attuale; l'attivazione resta riservata al committente.
- **Motivazione:** valida l'allineamento a MyAstral.org senza rischiare regressioni sul motore in
  produzione e senza violare il principio "ogni discrepanza deve essere spiegata, non eliminata".
- **Beneficio atteso:** ALTO
- **Costo tecnico stimato:** MEDIO
- **Rischi:** doppia manutenzione tra logica standard e logica estesa finché non si deciderà se e
  come unificarle; necessità di tenere sincronizzate le regole di veto condivise tra le due
  versioni.
- **Documento collegato:** `docs/ROADMAP_MYASTRAL_UX.md`, `docs/roadmap_comparazione_myastral.md`
- **Eventuale voce della roadmap tecnica:** `docs/ROADMAP_MYASTRAL_UX.md` §3.1, §3.2

---

Nessuna ulteriore decisione registrata.
