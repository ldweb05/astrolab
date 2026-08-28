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

### UX-0006 - Regola 33 completa (stessa casa, incondizionata + case adiacenti X/IX)

- **Data:** 2026-08-18
- **Area:** Rule Engine (`includes/RuleEngine.php`) - veti assoluti FASE 1
- **Stato:** APPROVATA
- **Problema osservato:** la Regola 33 ("Saturno ha sempre la meglio su Giove, su Venere, sul
  Sole... per lo stesso motivo... un Giove congiunto al Medio Cielo, a cinque gradi da esso, in
  decima Casa, e un Saturno alla stessa distanza, in nona: alla fine prevalera' il secondo...
  lo stesso discorso vale se mettiamo Saturno e Giove in settima, in seconda") ha due casi: (a)
  Saturno e un benefico (Sole/Venere/Giove) nella STESSA casa, in QUALUNQUE casa; (b) Saturno e
  un benefico in case ADIACENTI, alla stessa distanza dalla cuspide condivisa (esempio: IX/X,
  Medio Cielo). Il caso (a) esiste nel codice solo come `saturno_prevale` in
  `RuleEngineExtended.php`, ma ristretto alla sola casa tematica della condizione cercata (non
  qualunque casa) ed e' attivo solo dietro il flag opzionale `MYASTRAL_ALIGNMENT_MODE` (finora
  usato come esperimento, ora da rendere definitivo). Il caso (b) non e' implementato affatto
  (gap noto, documentato in `RuleEngineExtended.php` righe 53-59).
- **Evidenze:** `docs/status/34_regole_rsm.md`, Regola 33; `RuleEngineExtended.php`
  (`calcolaPunteggioParziale()`, flag `saturno_prevale`); `docs/ROADMAP_34_REGOLE.md` Fase 2.
- **Confronto codice / regole ufficiali:** `RuleEngineExtended.php` e' un sistema separato,
  esplicitamente documentato come "non parte delle 34 regole in se': un metro di paragone" con
  MyAstral.org (decisione UX-0001), che include anche un bonus d'orbo per Giove NON previsto
  dalla Regola 33. Riattivare quel sistema per intero (flag a true) accenderebbe anche il bonus
  proprietario, mescolando i due sistemi. Si sceglie quindi di implementare la Regola 33 (in
  entrambi i casi) direttamente e incondizionatamente in `RuleEngine.php` (motore FREEZE),
  coerente con l'approccio gia' usato per le Regole 4/5/31/34. `RuleEngineExtended.php` e il
  flag `MYASTRAL_ALIGNMENT_MODE` restano invariati come sistema opzionale separato (UX-0001 non
  revocata).
- **Decisione:** in `calcolaVeti()`:
  (a) STESSA CASA, qualunque casa: se Saturno (id 6) e almeno uno tra Sole/Venere/Giove
  (id 0/3/5) sono nella stessa casa RS/RL, veto assoluto "Saturno prevale su <benefico/i>".
  (b) CASE ADIACENTI, solo la coppia IX/X esplicitamente citata dal testo (non generalizzata ad
  altre coppie adiacenti, per coerenza con la scelta gia' fatta per la Regola 31): se Saturno e'
  entro 3 gradi PRIMA della cuspide del Medio Cielo (X casa, ancora in IX) e un benefico
  (Sole/Venere/Giove) e' entro 3 gradi DOPO la stessa cuspide (appena entrato in X), veto
  assoluto "Saturno prevale (Regola 33 - case adiacenti)". Tolleranza di 3 gradi allineata
  all'orbo gia' stabilito dalla Regola 23 per i transiti di Giove e Saturno.
- **Motivazione:** la Regola 33 non e' limitata alla casa tematica della condizione cercata ne'
  a un sistema opzionale: si applica in generale, in ogni RSM/RL, indipendentemente da quale
  condizione si stia cercando. Implementarla nel motore FREEZE la rende coerente con le altre
  regole inderogabili gia' allineate in questa sessione.
- **Beneficio atteso:** ALTO
- **Costo tecnico stimato:** MEDIO (due blocchi distinti, nuova logica di confronto per il caso
  adiacente, nessuna modifica al codice esistente riusato)
- **Rischi:** riduce il numero di risultati per ricerche esistenti con Saturno e un benefico
  nella stessa casa o in configurazione IX/X ravvicinata - comportamento atteso e voluto.
  `RuleEngineExtended.php`/`MYASTRAL_ALIGNMENT_MODE` restano un sistema separato: se il flag e'
  gia' attivo sul Pi, il suo `saturno_prevale` (ristretto alla casa tematica) e il nuovo veto
  incondizionato in `RuleEngine.php` (qualunque casa) opereranno in parallelo senza conflitto,
  quest'ultimo piu' ampio.
- **Documento collegato:** `docs/status/34_regole_rsm.md` (Regola 33), `RuleEngineExtended.php`
- **Eventuale voce della roadmap tecnica:** `docs/ROADMAP_34_REGOLE.md` - Fase 2

---

### UX-0007 - Veto latitudine >60 gradi declassato ad avviso informativo (non bloccante)

- **Data:** 2026-08-18
- **Area:** Rule Engine (`includes/RuleEngine.php`) - veto proprietario "astrolab-latitudine"
- **Stato:** APPROVATA
- **Problema osservato:** il veto proprietario "astrolab-latitudine" (introdotto storicamente, non
  parte delle 34 regole ufficiali - gia' rietichettato in Fase 1 di questo lavoro) scarta
  automaticamente ogni RSM/RL con latitudine oltre 60 gradi, indipendentemente dal resto della
  configurazione astrale. Un confronto diretto con myastral.org (che non applica questo limite)
  ha mostrato una localita' (Baker Lake, 64.28 gradi N) scartata da astrolab senza alcuna
  violazione delle 34 regole ufficiali, solo per questo veto proprietario.
- **Decisione:** rimosso il controllo dalla sezione veti assoluti di `calcolaVeti()`. Aggiunto
  invece, dopo il calcolo del punteggio (FASE 2 di `valuta()`), un controllo indipendente sulla
  latitudine della localita': se oltre 60 gradi, aggiunta una voce all'array `note` (stesso
  canale gia' usato per gli avvisi non bloccanti tipo Sole/Giove in case avverse) con testo
  esplicito di cautela. La RSM/RL viene quindi valutata normalmente con tutte le regole (veti,
  punteggio, stelline) come qualunque altra localita', con in piu' questa nota visibile.
- **Motivazione:** il limite di 60 gradi non e' nel testo di Discepolo; scartarlo automaticamente
  nascondeva configurazioni che potrebbero essere astrologicamente valide. Un avviso informativo
  permette all'utente di decidere consapevolmente, mantenendo comunque la cautela tecnica (a
  latitudini estreme il sistema di case Placido puo' degenerare) come nota, non come esclusione.
- **Beneficio atteso:** ALTO - piu' risultati utili senza perdere l'avviso di cautela tecnica.
- **Costo tecnico stimato:** BASSO - riuso del canale `note` gia' esistente, rimozione di un
  blocco isolato.
- **Rischi:** nessuno noto; la nota e' puramente informativa, non cambia stelline o punteggio.
- **Documento collegato:** confronto con myastral.org, sessione 2026-08-18 (Baker Lake, RSM 2025
  Sinner)

---

### UX-0008 - Veto Marte entro 2,5 gradi (o dentro) la X Casa declassato ad avviso

- **Data:** 2026-08-19
- **Area:** Rule Engine (`includes/RuleEngine.php`) - veto proprietario "astrolab-angoli"
- **Stato:** APPROVATA
- **Problema osservato:** il veto proprietario "astrolab-angoli" scarta automaticamente ogni
  RSM/RL in cui Marte si trova entro 2 gradi da una delle 4 case angolari (I, IV, VII, X),
  indipendentemente dal resto della configurazione astrale. Per la X Casa in particolare
  (rilevanza diretta per la condizione "Decima"), questo veto risulta eccessivamente
  restrittivo rispetto al peso reale della configurazione.
- **Decisione:** per Marte e la sola X Casa, il controllo esce dalla sezione veti assoluti di
  `calcolaVeti()`. Aggiunto invece, dopo il calcolo del punteggio (FASE 2 di `valuta()`), un
  controllo indipendente: se Marte si trova entro 2,5 gradi dalla cuspide della X Casa (in
  qualunque direzione) oppure e' assegnato dal calcolo casa alla X Casa stessa, viene aggiunta
  una voce all'array `note` (stesso canale gia' usato per l'avviso di latitudine UX-0007) con
  testo esplicito di cautela. La RSM/RL viene quindi valutata normalmente con tutte le regole
  (veti, punteggio, stelline) come qualunque altra localita', con in piu' questa nota visibile.
  Il veto "astrolab-angoli" resta invariato per Marte sulle altre 3 case angolari (I, IV, VII)
  e per Saturno su tutte e 4 le case angolari, incluso Marte-Saturno entro 2 gradi come gia'
  previsto. Resta invariato anche il veto separato "Marte in I/VI/XII" (Regola 2-4), con la sua
  soglia di pre-ingresso a 3 gradi.
- **Motivazione:** la X Casa non e' equiparabile alle altre 3 case angolari per la condizione
  "Decima": un Marte vicino al Medio Cielo puo' indicare tensione o esposizione, non
  necessariamente un rischio da scarto automatico. Un avviso informativo permette di valutare
  consapevolmente la configurazione senza perdere risultati potenzialmente validi.
- **Beneficio atteso:** ALTO - piu' risultati utili per la condizione "Decima" senza perdere
  l'avviso di cautela tecnica.
- **Costo tecnico stimato:** BASSO - riuso del canale `note` gia' esistente (pattern UX-0007),
  rimozione mirata di un solo ramo del blocco veto esistente.
- **Rischi:** nessuno noto; la nota e' puramente informativa, non cambia stelline o punteggio.
  Il veto Marte-Saturno entro 2 gradi dagli angoli (incluso su X) resta attivo se coinvolge
  Saturno.
- **Documento collegato:** richiesta esplicita dell'utente, sessione 2026-08-19

---

### UX-0012 - Parita' dropdown Condizione tra ricerca.php (RS) e ricerca_rl.php (RL)

- **Data:** 2026-08-20
- **Area:** `www/ricerca_rl.php`
- **Stato:** APPROVATA
- **Problema osservato:** il menu Condizione di `ricerca_rl.php` mostra solo 7 voci (le
  condizioni tematiche), escludendo con un `array_diff()` le due voci speciali
  '— Astri nelle Case —' e '— Longitudine Cuspidi —' presenti invece nelle 9 voci ufficiali di
  `RicercaPageData.php` e nel menu di `ricerca.php` (RS). Confermato anche un impatto funzionale
  reale sul flusso `ricerche.php` -> `ricerca_rl.php`: se l'utente sceglie una di quelle due
  condizioni per una RL, la preselezione via URL non trova l'opzione nel <select> e la ricerca
  automatica parte con la condizione di default sbagliata. Tutta la logica JS per le due
  modalita' speciali (costanti CONDIZIONE_CUSPIDI/CONDIZIONE_ASTRI, pannelli dedicati, calcolo
  modalitaBase, ricerca a griglia) e' gia' presente e funzionante in `ricerca_rl.php` - le due
  voci sono irraggiungibili solo a causa del filtro nel <select>.
- **Decisione:** rimuovere il filtro `array_diff()` in `ricerca_rl.php`, riportando il menu
  Condizione a tutte e 9 le voci ufficiali, identico a `ricerca.php`. Nessuna altra modifica
  alla logica di ricerca RL.
- **Motivazione:** richiesto esplicitamente dal committente dopo aver verificato il flusso
  `ricerche.php` -> `ricerca_rl.php`; necessario per coerenza con la nuova pagina `ricerche.php`
  (UX-0010), che deve mostrare sempre tutte e 9 le condizioni indipendentemente dal fatto che
  l'utente abbia scelto un RS o un RL.
- **Beneficio atteso:** ALTO (coerenza UI, corregge un bug funzionale reale nel nuovo flusso)
- **Costo tecnico stimato:** BASSO (rimozione di una riga, nessuna nuova logica)
- **Rischi:** minimi - la logica per le due modalita' speciali e' gia' testata lato RS; verificare
  comunque con un test funzionale reale su ricerca_rl.php dopo la modifica.
- **Documento collegato:** `docs/ux-myastral/03_RICERCA_RSM_ux.md` (UX-0010)
- **Eventuale voce della roadmap tecnica:** roadmap Fase 2 (PROMPT_OPERATIVO_ASTROLAB_ALLIUNEAMENTO_UX)

---

### UX-0013 - Regola 33 declassata da veto assoluto a nota informativa (revoca UX-0006)

- **Data:** 2026-08-20
- **Area:** Rule Engine (`includes/RuleEngine.php`) - Regola 33
- **Stato:** APPROVATA
- **Problema osservato:** in decisione UX-0006 (Fase 2) la Regola 33 era stata implementata come
  veto assoluto incondizionato, su esplicita richiesta del committente. Il committente ha ora
  chiarito che la Regola 33 NON e' tra le regole a scarto automatico (che sono solo 4, 5, 31, 32,
  34) - e' un principio interpretativo su cui l'astrologo deve dare un giudizio, non un criterio
  di esclusione automatica.
- **Decisione:** revocata UX-0006 nella parte relativa allo scarto automatico. I due controlli
  gia' implementati in `calcolaVeti()` (caso "stessa casa" incondizionato, caso "case adiacenti"
  IX/X) vengono spostati dall'array `$veti` all'array `$note` (stesso canale gia' usato per
  UX-0007, veto latitudine), con testo che spiega la situazione ("Saturno prevale su X - valutare
  con attenzione") invece di scartare la RSM/RL.
- **Motivazione:** allineamento letterale alle indicazioni del committente sulle regole realmente
  inderogabili (4, 5, 31, 32, 34 - non 33).
- **Beneficio atteso:** piu' risultati utili, coerenti con l'effettiva severita' della regola
  secondo il committente.
- **Costo tecnico stimato:** BASSO - stesso pattern gia' collaudato per UX-0007.
- **Rischi:** nessuno noto.
- **Documento collegato:** `docs/status/34_regole_rsm.md` (Regola 33), UX-0006, UX-0007

---

### UX-0014 - Nuova modalita' "in cuspide" nel pannello Astri nelle Case (ricerca RSM/RL)

- **Data:** 2026-08-22
- **Area:** Ricerca RSM/RL - pannello "Astri nelle Case" (`ricerca.php`, `ricerca_rl.php`,
  `RicercaRSFilters.php`, `ricerca_stream_api.php`, `ricerca_stream_rl_api.php`) - non tocca
  RuleEngine.php
- **Stato:** APPROVATA
- **Problema osservato:** oggi il pannello "Astri nelle Case" verifica solo l'appartenenza di un
  pianeta a una casa (match esatto), non la vicinanza a una cuspide. Il committente vuole poter
  combinare piu' pianeti "in cuspide" nella stessa ricerca (es. Venere in cuspide II, Giove in
  cuspide X).
- **Decisione:** ogni riga della regola "Astri nelle Case" guadagna un campo opzionale
  `modalita` ('in_casa', default, invariato; oppure 'cuspide'). In modalita' 'cuspide' si verifica
  la distanza angolare tra il pianeta e la cuspide richiesta con `AstroUtils::diffAngolo()`, orbo
  fisso 2 gradi e 30 minuti (stesso valore di Regola 32, non configurabile dall'utente). Feature
  Supporter-gated (stesso pattern di `dynamic_orb`/`grid_search`/`locality_search` in
  `Auth::hasFeature()`); sul piano free l'opzione resta visibile ma disabilitata, con tooltip
  `SUPPORTER_MESSAGE` al click. Le regole 4, 5, 31, 34 restano veti assoluti incondizionati del
  `RuleEngine`, applicati come oggi a valle, senza alcuna logica di scarto duplicata o parallela
  per questa nuova modalita'.
- **Motivazione:** richiesto esplicitamente dal committente; l'orbo riusa quello gia' ufficiale di
  Regola 32 (vedi UX-0013, dove il committente ha confermato che le regole a scarto automatico
  sono 4, 5, 31, 32, 34) invece di inventarne uno nuovo.
- **Beneficio atteso:** ALTO - nuovo criterio di ricerca richiesto dal committente, coerente con
  il pannello gia' esistente.
- **Costo tecnico stimato:** MEDIO - nuova funzione di verifica angolare, UI aggiuntiva, gating
  Supporter lato server e client.
- **Rischi:** `ricerca.php`/`ricerca_rl.php` sono file con storico di divergenza tra
  `feature/allineamento-myastral` e altre linee (`chore/porta-feature-da-allineamento-myastral`,
  `fase9-comparator-quota`) - verificare in Fase 2 se le stesse porzioni sono state toccate
  altrove prima di modificare.
- **Documento collegato:** `docs/ROADMAP_2_ASTRI_IN_CUSPIDE.md`, `docs/status/34_regole_rsm.md`
  (Regola 32), UX-0013
- **Eventuale voce della roadmap tecnica:** `docs/ROADMAP_2_ASTRI_IN_CUSPIDE.md`, Fase 1

---

### UX-0015 - Gerarchia a 7 livelli per Decima + Regola 14 (declassamento ASC in X)

- **Data:** 2026-08-28
- **Area:** `includes/RuleEngineExtended.php` (nuovo), ordinamento in `api/ricerca_stream_api.php`
- **Stato:** APPROVATA
- **Problema osservato:** per la condizione Decima non esiste oggi una vera gerarchia con
  priorita' tra ASC/Giove/Venere/Sole: `RuleEngineExtended::calcolaPunteggioParziale()` calcola
  solo un punteggio additivo (Sole+Venere+Giove sommati), e l'ordinamento finale dei risultati
  (`usort` in ricerca_stream_api.php) si basa esclusivamente sul sistema stelline V2
  (`v2_stelle_totali`, sistema primario in produzione dopo la migrazione documentata in
  ROADMAP_SOSTITUZIONE_STELLINE_V2.md). Manca inoltre l'implementazione della Regola 14
  ufficiale (ASC in X + pianeta lento in aspetto dissonante ai punti natali = ASC indebolito).
- **Decisione:**
  1. Nuova gerarchia a 7 livelli per Decima, calcolata in RuleEngineExtended.php (fuori dal
     punteggio additivo esistente, che resta invariato per la sola visualizzazione):
     Livello 1 = ASC RS in X casa natale (Regola 14 non scattata)
     Livello 2 = Giove RS in X casa, entro 2.5 gradi dalla cuspide (bonus orbo)
     Livello 3 = Giove RS in X casa, oltre l'orbo
     Livello 4 = Venere RS in X casa
     Livello 5 = Sole RS in X casa
     Livello 6 = ASC RS in X casa natale, ma Regola 14 scattata (declassato)
     Livello 7 = nessun segnale Decima
  2. Regola 14: prerequisito ASC RS in X casa natale (funzione trovaCasaNatale() duplicata
     localmente in RuleEngineExtended.php, essendo private in RuleEngine.php/FREEZE). Per
     Saturno/Urano/Nettuno/Plutone (posizione RS) verificare aspetto dissonante (congiunzione,
     quadratura o opposizione, orbo 2.5 gradi) rispetto a Sole natale, Luna natale, ASC natale
     (cuspide I), MC natale (cuspide X, coincidente con la Decima casa). Un solo aspetto tra i 4
     pianeti lenti e i 4 punti natali fa scattare il declassamento (livello 1 -> 6), con nota che
     indica pianeta e punto natale coinvolti.
  3. Ordinamento: solo con MYASTRAL_ALIGNMENT_MODE attivo e solo per condizione Decima, l'usort
     finale in ricerca_stream_api.php ordina per livello crescente (1 = migliore) al posto del
     sistema stelline V2. Le stelle V2 (v2_stelle_totali) restano calcolate e visibili nel record
     risultato, e fanno da tie-break a parita' di livello. Tutte le altre condizioni:
     comportamento invariato (v2_stelle_totali come oggi). RuleEngine.php (FREEZE) e i veti/
     esclusioni a monte (34 regole) restano applicati esattamente come oggi, a monte di questo
     ordinamento.
- **Motivazione:** allineamento letterale alla gerarchia Discepolo confermata dal committente
  (ASC prevale su Giove/Venere/Sole quando non indebolito dalla Regola 14) e implementazione della
  Regola 14 ufficiale, finora assente. Il committente ha esplicitamente richiesto che il risultato
  migliore per la condizione prevalga sempre sul numero di stelle, a prescindere dal sistema di
  stelle in uso (prima le stelline classiche, ora V2).
- **Beneficio atteso:** ordinamento dei risultati Decima coerente con l'Astrologia Attiva di
  Discepolo; copertura della Regola 14 ufficiale, finora mancante.
- **Costo tecnico stimato:** MEDIO — nuovo metodo in RuleEngineExtended.php, piccola modifica
  condizionale all'usort esistente in ricerca_stream_api.php (solo per Decima + flag attivo).
- **Rischi:** nessuna modifica a RuleEngine.php ne' al sistema stelline V2; da testare con
  attenzione il caso limite in cui piu' risultati abbiano lo stesso livello (tie-break gia'
  definito su v2_stelle_totali).
- **Scope esplicitamente escluso:** estensione della gerarchia ad altre condizioni (Salute,
  Lavoro, Soldi/Denaro), gia' menzionate dal committente con priorita' diverse (es. Salute:
  Giove poi Venere; Soldi: Venere poi alert Giove; Lavoro: Giove poi Venere) - da trattare in una
  sessione dedicata futura, non in questa.
- **Documento collegato:** `docs/status/34_regole_rsm.md` (Regola 14), `docs/PROMPT_OPERATIVO_ASTROLAB.md` par. 9,
  `docs/ROADMAP_SOSTITUZIONE_STELLINE_V2.md`

---

Nessuna ulteriore decisione registrata.
