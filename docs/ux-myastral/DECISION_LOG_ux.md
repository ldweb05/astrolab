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

### UX-0002 - Rimozione dell'eccezione benefici nel veto stellium (RuleEngine.php, FREEZE)

- **Data:** 2026-08-17
- **Area:** Rule Engine (`includes/RuleEngine.php`) - veto stellium in I/VI/VIII/XII casa
- **Stato:** APPROVATA
- **Problema osservato:** il veto stellium in RuleEngine.php (righe ~578-585) perdona la
  configurazione se Sole o Giove sono presenti nello stellium ($hasBenefici). Le 34 regole
  ufficiali (docs/status/34_regole_rsm.md) non prevedono questa eccezione: la Regola 31 mostra
  esplicitamente un esempio - Giove in XII casa e Venere+Mercurio in I casa - come stellium
  ancora pericoloso, nonostante la presenza di un benefico (Giove).
- **Evidenze:** stesso caso gia trovato in FiltroEsclusione.php (corretto in commit 0088232,
  ma li il problema era lo scope delle case, non l'eccezione benefici, che li era gia assente
  correttamente); confronto diretto con Regola 4, 16, 26, 31.
- **Confronto codice / regole ufficiali:** RuleEngine.php scope case [1,6,8,12] e' GIA corretto
  (coincide esattamente con le case citate da Regole 4/16/26/31); l'unico difetto e'
  l'eccezione benefici, assente nel testo di Discepolo.
- **Decisione:** si autorizza la rimozione dell'eccezione $hasBenefici dal veto stellium in
  RuleEngine.php - unica modifica, nessun'altra parte del Rule Engine toccata. Lo scope delle
  case [1,6,8,12] resta invariato perche' gia corretto.
- **Motivazione:** allineare il Rule Engine alla fonte primaria vincolante (le 34 regole),
  eliminando una divergenza che oggi fa passare come "sicure" RSM/RL che Discepolo
  classificherebbe esplicitamente come pericolose.
- **Beneficio atteso:** ALTO
- **Costo tecnico stimato:** BASSO (rimozione di 2 righe di codice, nessuna nuova logica)
- **Rischi:** puo cambiare il numero di risultati per ricerche esistenti (alcune RSM/RL con
  stellium benefico in I/VI/VIII/XII che oggi passano il veto, dopo la modifica non lo
  passeranno piu) - comportamento atteso e voluto, non un effetto collaterale indesiderato.
- **Documento collegato:** `docs/status/34_regole_rsm.md` (Regole 4, 16, 26, 31)
- **Eventuale voce della roadmap tecnica:** nessuna, correzione diretta del Rule Engine.

---

### UX-0003 - Implementazione Regola 34 (Marte+Saturno stessa casa RS/RL, eccetto III/IX)

- **Data:** 2026-08-18
- **Area:** Rule Engine (`includes/RuleEngine.php`) - veti assoluti FASE 1
- **Stato:** APPROVATA
- **Problema osservato:** la Regola 34 ("Non e' possibile posizionare Marte e Saturno nella
  stessa casa di RS, qualunque essa sia, tranne che in Terza e in Nona casa. In caso contrario
  si potranno subire danni molto gravi da cio'") e' completamente assente dal codice - nessun
  veto attuale controlla la compresenza di Marte e Saturno nella stessa casa.
- **Evidenze:** `docs/status/34_regole_rsm.md`, Regola 34; confermato in
  `docs/ROADMAP_34_REGOLE.md` Fase 1, punto 4 ("completamente assente dal codice, va scritta
  da zero").
- **Confronto codice / regole ufficiali:** il codice esistente ha gia' il concetto di
  case-parcheggio neutre (`CASE_PARCHEGGIO = [3, 9]`, gia' usato altrove nel Rule Engine) che
  coincide esattamente con l'eccezione III/IX della Regola 34 - riuso diretto della costante
  esistente, nessuna nuova costante da introdurre.
- **Decisione:** aggiunto in `calcolaVeti()`, nella sezione FASE 1 (veti assoluti), un nuovo
  controllo: se Marte (id 4) e Saturno (id 6) sono assegnati alla stessa casa RS/RL (campo
  `casa` gia' calcolato da SweCalc, stesso pattern di `pianetaInCasa()`), e quella casa NON e'
  in `CASE_PARCHEGGIO` (III o IX), viene generato un veto assoluto -> scarto automatico. Nessun
  pre-ingresso di 3 gradi previsto (il testo della Regola 34 non lo menziona, a differenza delle
  Regole 4/5).
- **Motivazione:** la Regola 34 e' esplicitamente indicata come inderogabile dal committente;
  senza questo veto il Rule Engine puo' promuovere/mostrare RSM e RL che Discepolo classificherebbe
  come pericolose senza eccezioni.
- **Beneficio atteso:** ALTO
- **Costo tecnico stimato:** BASSO (nuovo blocco isolato di poche righe, riuso di costanti e
  pattern esistenti, nessuna modifica a logica preesistente)
- **Rischi:** puo' ridurre il numero di risultati per ricerche esistenti (RSM/RL con Marte+Saturno
  nella stessa casa, oggi passano, dopo la modifica verranno scartate) - comportamento atteso e
  voluto, non un effetto collaterale indesiderato.
- **Documento collegato:** `docs/status/34_regole_rsm.md` (Regola 34), `docs/ROADMAP_34_REGOLE.md`
  (Fase 1)
- **Eventuale voce della roadmap tecnica:** `docs/ROADMAP_34_REGOLE.md` - Fase 1, punto 4

---

### UX-0004 - Veto assoluto Sole in I/VI/XII casa RS/RL (completamento Regola 4)

- **Data:** 2026-08-18
- **Area:** Rule Engine (`includes/RuleEngine.php`) - veti assoluti FASE 1 + matrice punteggio
- **Stato:** APPROVATA
- **Problema osservato:** la Regola 4 ("Se... l'Ascendente, uno stellium o il Sole si trovano
  nelle Case I, VI o XII, il soggetto va incontro a un anno particolarmente difficile, esiziale,
  nero a 360 gradi") prevede tre condizioni alternative di scarto automatico. Nel codice attuale
  solo Ascendente e stellium sono veti assoluti; il Sole in I/VI/XII ha peso 0 in `MATRICE[0]` e
  tipo `'AVV'` in `TIPI[0]`, che produce solo una nota informativa in UI, senza alcun impatto su
  punteggio o scarto.
- **Evidenze:** `docs/status/34_regole_rsm.md`, Regola 4; `MATRICE[0][1]=0, [6]=0, [12]=0`;
  `TIPI[0][1]='AVV', [6]='AVV', [12]='AVV'` in `RuleEngine.php`.
- **Confronto codice / regole ufficiali:** stesso schema gia' usato per Marte (Regola 5,
  `TIPI[4]` = `VETO` per 1/6/12, enforced in `calcolaVeti()`); il Sole manca del tutto di questo
  trattamento nonostante la Regola 4 lo tratti esplicitamente come equivalente ad Ascendente e
  stellium.
- **Decisione:** aggiunto in `calcolaVeti()`, stessa sezione FASE 1, un controllo: se il Sole
  (id 0) e' assegnato alla casa 1, 6 o 12 RS/RL (con lo stesso pre-ingresso di 3 gradi gia' usato
  per Ascendente/malefici, per coerenza con la Regola 1 e con Marte), scarto automatico. La voce
  `TIPI[0]` per le case 1/6/12 resta `'AVV'` (mantenuta per compatibilita' delle note in UI), ma
  il veto in `calcolaVeti()` ha sempre precedenza e scarta la RSM/RL prima che il punteggio venga
  calcolato.
- **Motivazione:** la Regola 4 e' esplicitamente indicata come inderogabile; senza questo veto il
  Rule Engine puo' promuovere RSM/RL col Sole in I/VI/XII come pienamente valide, in
  contraddizione diretta con la fonte primaria vincolante.
- **Beneficio atteso:** ALTO
- **Costo tecnico stimato:** BASSO (nuovo blocco isolato, stesso pattern gia' collaudato per
  Marte/Regola 5 e Regola 34)
- **Rischi:** riduce il numero di risultati per ricerche esistenti (RSM/RL col Sole in I/VI/XII,
  oggi valide, verranno scartate) - comportamento atteso e voluto.
- **Documento collegato:** `docs/status/34_regole_rsm.md` (Regola 4)
- **Eventuale voce della roadmap tecnica:** `docs/ROADMAP_34_REGOLE.md` - Fase 1, punto 1

---

### UX-0005 - Stellium diviso tra XII e I casa RS/RL (Regola 31)

- **Data:** 2026-08-18
- **Area:** Rule Engine (`includes/RuleEngine.php`) - veti assoluti FASE 1
- **Stato:** APPROVATA
- **Problema osservato:** la Regola 31 ("La pericolosita' di uno stellium in prima o dodicesima
  di RS si deve intendere anche se lo stesso si divide... tra le Case; uno stellium tra
  dodicesima e prima vale esattamente come uno stellium in dodicesima, anche se, per esempio,
  abbiamo Giove in dodicesima Casa e Venere e Mercurio in prima") non e' implementata: il veto
  stellium attuale conta solo 3+ pianeti dentro la stessa casa, non la somma tra XII e I quando
  lo stellium e' diviso tra le due.
- **Evidenze:** `docs/status/34_regole_rsm.md`, Regola 31; confermato in
  `docs/ROADMAP_34_REGOLE.md`, Fase 1 punto 3.
- **Confronto codice / regole ufficiali:** il testo cita esplicitamente e solo la coppia XII/I
  (adiacenti attraverso la cuspide dell'Ascendente); non generalizza ad altre coppie di case
  adiacenti (es. VI/VII) - non estesa quindi oltre XII/I, per non aggiungere un'interpretazione
  non scritta nella fonte.
- **Decisione:** dopo il ciclo esistente su `[1, 6, 8, 12]` (che resta invariato e continua a
  coprire lo stellium "puro" dentro una sola casa), aggiunto un controllo separato: se la XII
  casa ha almeno 1 pianeta, la I casa ha almeno 1 pianeta, e la somma dei due gruppi e' >= 3 (e
  nessuna delle due case da sola aveva gia' raggiunto 3, caso gia' coperto dal veto esistente),
  scatta un veto "Stellium diviso XII/I - Regola 31", con l'elenco dei pianeti coinvolti.
- **Motivazione:** la Regola 31 e' indicata come inderogabile; senza questo veto una RSM/RL con,
  ad esempio, Giove in XII e Venere+Mercurio in I passerebbe il veto stellium anche se Discepolo
  la classifica esplicitamente come pericolosa quanto uno stellium pieno in XII.
- **Beneficio atteso:** ALTO
- **Costo tecnico stimato:** BASSO (blocco isolato, riusa `pianetaInCasa()` e `VAL_NOMI`
  esistenti)
- **Rischi:** riduce il numero di risultati per ricerche esistenti con questa configurazione
  specifica - comportamento atteso e voluto.
- **Documento collegato:** `docs/status/34_regole_rsm.md` (Regola 31)
- **Eventuale voce della roadmap tecnica:** `docs/ROADMAP_34_REGOLE.md` - Fase 1, punto 3

---

Nessuna ulteriore decisione registrata.
