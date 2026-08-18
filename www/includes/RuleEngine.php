<?php
require_once __DIR__ . '/AstroUtils.php';
/**
 * RuleEngine — Motore delle regole di Discepolo
 * Valuta una RS/RL e restituisce stelline, stringa VAL e note interpretative
 * Implementa ESCLUSIVAMENTE le regole della scuola di Ciro Discepolo
 *
 * v4.1 — Aggiunto pre-ingresso 3° e sicurezza-uscita 2° per condizione Decima;
 * pre-ingresso 3° sui veti assoluti malevoli in I/VI/XII RS e
 * ASC RS in I/VI/XII natale.
 *
 * v4.2 — Aggiunto metodo verificaPreIngressoUscita() per gestione centralizzata
 * delle tolleranze di pre-ingresso e sicurezza in uscita (usato da Amore).
 *
 * Condizioni: Decima | Lavoro | Amore | Salute | Denaro | Denaro Low | Casa
 *
 * Regola universale:
 * I, VI, XII sono case universalmente negative.
 * Malevoli (MA/SA/UR/NE/PLU) → sempre molto pesanti in queste case.
 * Giove o Venere in I/VI/XII → proteggono e neutralizzano il rischio.
 *
 * Regola pre-ingresso (Discepolo):
 * Un pianeta a meno di 3° dalla cuspide della casa successiva è considerato
 * già "nella casa successiva" ai fini della valutazione. Gestione modulo 360°.
 *
 * Sicurezza in uscita (specifica condizione Decima):
 * Sole o Giove a meno di 2° dalla cuspide della XI casa RS → veto.
 * Troppo vicini all'uscita dalla X: l'anno non può essere considerato "in X".
 */
class RuleEngine {

    const MALEVOLI        = [4, 6, 7, 8, 9];
    const BENEFICI        = [0, 1, 3, 5];
    const ANGOLARI        = [1, 4, 7, 10];
    const CASE_VETO       = [1, 6, 12];
    const CASE_PARCHEGGIO = [3, 9];

    const VAL_NOMI = [
        0=>'SO', 1=>'LU', 2=>'ME', 3=>'VE', 4=>'MA',
        5=>'GI', 6=>'SA', 7=>'UR', 8=>'NE', 9=>'PLU',
        11=>'NO'
    ];

    // ── 7 CONDIZIONI TEMATICHE ───────────────────────────────────────────
    //
    // Regola universale (Discepolo):
    //   I, VI, XII sono case universalmente negative.
    //   Malevoli (MA/SA/UR/NE/PLU) in I/VI/XII → sempre pesantissimi.
    //   Giove o Venere in I/VI/XII → bonus che protegge/neutralizza.
    //
    // Ogni condizione aggiunge la sua casa tematica specifica dove:
    //   - benefici (GI/VE + SO per Decima/Lavoro) → bonus +1 stella
    //   - malevoli (MA/SA/UR) → malus -1 stella extra (sopra quello universale)
    //
    // 'bonus'  = case dove GI/VE danno +1 stella condizionale
    // 'malus'  = case dove MA/SA/UR danno -1 stella extra condizionale
    // Le case 1, 6, 12 sono SEMPRE nei malus (regola universale) +
    //   la casa tematica specifica.
    //
    const CASE_TEMATICHE = [
        // X casa: carriera, promozioni, status, reputazione
        // Bonus: X (gloria), V (creatività/fortuna come trampolino)
        // Malus extra: X (malevoli qui bloccano la carriera)
        'Decima'     => ['bonus' => [10, 5],       'malus' => [1, 6, 12, 10]],
        // VI+X: lavoro quotidiano E avanzamento; Mercurio/Marte in VI/X = spinta operativa
        // Malus extra: VI (malevoli = disastro ambiente lavoro) E X (bloccano carriera)
        'Lavoro'     => ['bonus' => [6, 5, 10],    'malus' => [1, 6, 12, 10]],
        // V+VII: amore romantico (V) e legami stabili (VII)
        // Malus extra: V e VII (malevoli qui distruggono amore)
        'Amore'      => ['bonus' => [5, 7],        'malus' => [1, 6, 12, 5, 7]],
        // I+VI+XII: le tre case della salute — da proteggere sempre
        // Target positivo: SO/GI/VE in I/VI aumentano vitalità e recupero
        'Salute'     => ['bonus' => [1, 6, 12],    'malus' => [1, 6, 12]],
        // II+VIII: entrate/uscite, eredità, investimenti
        // Luna non lesa in II = flusso costante di entrate
        'Denaro'     => ['bonus' => [2, 8],        'malus' => [1, 6, 12, 2, 8]],
        // Solo II: piccole entrate costanti — Mercurio e Luna in II = flusso di cassa
        // Denaro Low: accettare configurazione neutra ma protetta
        'Denaro Low' => ['bonus' => [2],           'malus' => [1, 6, 12, 2]],
        // IV: abitazione, traslochi, acquisti immobiliari, pace familiare
        // Malus extra: IV (malevoli = guasti, liti, problemi strutturali)
        'Casa'       => ['bonus' => [4],           'malus' => [1, 6, 12, 4]],
    ];

    const CONDIZIONI = [
        // Decima: SO/GI/VE in X o V amplificati; MA/SA/UR in X penalizzati extra
        'Decima'     => [
            'bonus'   => [0=>[10=>2.0,5=>1.5], 5=>[10=>2.0,5=>1.5], 3=>[10=>1.5,5=>1.0]],
            'penalita'=> [4=>[10=>2.0,1=>3.0,6=>3.0,12=>3.0],
                          6=>[10=>2.0,1=>2.5,6=>2.5,12=>2.5],
                          7=>[10=>1.5,1=>2.5,6=>2.5,12=>2.5],
                          8=>[1=>2.0,6=>2.0,12=>2.5],
                          9=>[1=>2.0,6=>2.0,12=>2.0]],
        ],
        // Lavoro: GI/VE in VI (proteggono) o in V; MA/SA/UR in VI pesantissimi
        'Lavoro'     => [
            'bonus'   => [5=>[6=>2.0,5=>1.5], 3=>[6=>2.0,5=>1.0], 0=>[6=>1.5,5=>1.5]],
            'penalita'=> [4=>[6=>3.0,1=>3.0,12=>3.0],
                          6=>[6=>2.5,1=>2.5,12=>2.5],
                          7=>[6=>2.5,1=>2.5,12=>2.5],
                          8=>[6=>2.0,1=>2.0,12=>2.5],
                          9=>[6=>1.5,1=>2.0,12=>2.0]],
        ],
        // Amore: GI/VE in V o VII ottimi; MA/SA/UR in V o VII pessimi
        'Amore'      => [
            'bonus'   => [3=>[5=>2.0,7=>2.0], 5=>[5=>2.0,7=>1.5], 0=>[5=>1.5,7=>1.0]],
            'penalita'=> [4=>[5=>2.0,7=>2.0,1=>3.0,6=>3.0,12=>3.0],
                          6=>[5=>2.0,7=>2.0,1=>2.5,6=>2.5,12=>2.5],
                          7=>[5=>1.5,7=>1.5,1=>2.5,6=>2.5,12=>2.5],
                          8=>[5=>1.0,7=>1.0,1=>2.0,6=>2.0,12=>2.5],
                          9=>[5=>1.0,7=>1.0,1=>2.0,6=>2.0,12=>2.0]],
        ],
        // Salute: GI/VE in I/VI/XII proteggono; tutti i malevoli lì sono devastanti
        'Salute'     => [
            'bonus'   => [5=>[1=>2.0,6=>2.0,12=>1.5], 3=>[1=>2.0,6=>2.0,12=>1.5]],
            'penalita'=> [4=>[1=>3.0,6=>3.0,12=>3.0],
                          6=>[1=>3.0,6=>3.0,12=>3.0],
                          7=>[1=>2.5,6=>2.5,12=>2.5],
                          8=>[1=>2.0,6=>2.0,12=>2.5],
                          9=>[1=>2.0,6=>2.0,12=>2.0]],
        ],
        // Denaro: GI/VE/SO in II o VIII; MA/SA/UR in II o VIII pesanti
        'Denaro'     => [
            'bonus'   => [5=>[2=>2.0,8=>1.5], 3=>[2=>2.0,8=>1.5], 0=>[2=>1.5,8=>1.0]],
            'penalita'=> [4=>[2=>2.0,8=>2.0,1=>3.0,6=>3.0,12=>3.0],
                          6=>[2=>2.0,8=>1.5,1=>2.5,6=>2.5,12=>2.5],
                          7=>[2=>1.5,8=>1.5,1=>2.5,6=>2.5,12=>2.5],
                          8=>[2=>1.0,8=>1.0,1=>2.0,6=>2.0,12=>2.5],
                          9=>[2=>1.0,8=>1.0,1=>2.0,6=>2.0,12=>2.0]],
        ],
        // Denaro Low: solo casa II (stipendio, entrate personali)
        'Denaro Low' => [
            'bonus'   => [5=>[2=>2.0], 3=>[2=>2.0], 0=>[2=>1.5]],
            'penalita'=> [4=>[2=>2.0,1=>3.0,6=>3.0,12=>3.0],
                          6=>[2=>1.5,1=>2.5,6=>2.5,12=>2.5],
                          7=>[2=>1.5,1=>2.5,6=>2.5,12=>2.5],
                          8=>[2=>1.0,1=>2.0,6=>2.0,12=>2.5],
                          9=>[2=>1.0,1=>2.0,6=>2.0,12=>2.0]],
        ],
        // Casa: GI/VE in IV; MA/SA/UR in IV devastanti
        'Casa'       => [
            'bonus'   => [5=>[4=>2.0], 3=>[4=>2.0], 0=>[4=>1.5]],
            'penalita'=> [4=>[4=>2.0,1=>3.0,6=>3.0,12=>3.0],
                          6=>[4=>2.0,1=>2.5,6=>2.5,12=>2.5],
                          7=>[4=>2.0,1=>2.5,6=>2.5,12=>2.5],
                          8=>[4=>1.0,1=>2.0,6=>2.0,12=>2.5],
                          9=>[4=>1.0,1=>2.0,6=>2.0,12=>2.0]],
        ],
    ];

    const MATRICE = [
        0  => [1=>0,  2=>0,  3=>2,  4=>2,  5=>3,  6=>0,  7=>0,  8=>0,  9=>2,  10=>3,  11=>2,  12=>0],
        1  => [1=>-1, 2=>0,  3=>1,  4=>2,  5=>2,  6=>-1, 7=>0,  8=>-1, 9=>1,  10=>1,  11=>1,  12=>-2],
        2  => [1=>1,  2=>1,  3=>2,  4=>0,  5=>1,  6=>-1, 7=>1,  8=>0,  9=>2,  10=>1,  11=>1,  12=>-1],
        3  => [1=>2,  2=>2,  3=>1,  4=>2,  5=>3,  6=>0,  7=>2,  8=>1,  9=>2,  10=>2,  11=>2,  12=>0],
        4  => [1=>-99,2=>-2, 3=>-1, 4=>-2, 5=>-2, 6=>-99,7=>-3, 8=>-3, 9=>-1, 10=>-2, 11=>-2, 12=>-99],
        5  => [1=>0,  2=>0,  3=>2,  4=>2,  5=>3,  6=>0,  7=>0,  8=>0,  9=>2,  10=>3,  11=>2,  12=>0],
        6  => [1=>-3, 2=>-2, 3=>-1, 4=>-2, 5=>-2, 6=>-3, 7=>-3, 8=>-2, 9=>-1, 10=>-3, 11=>-2, 12=>-3],
        7  => [1=>-3, 2=>-2, 3=>-1, 4=>-2, 5=>-2, 6=>-3, 7=>-3, 8=>-2, 9=>-1, 10=>-2, 11=>-2, 12=>-3],
        8  => [1=>-2, 2=>-1, 3=>-1, 4=>-1, 5=>-1, 6=>-2, 7=>-2, 8=>-1, 9=>-1, 10=>-2, 11=>-1, 12=>-3],
        9  => [1=>-2, 2=>-1, 3=>-1, 4=>-1, 5=>-1, 6=>-2, 7=>-2, 8=>-1, 9=>-1, 10=>-2, 11=>-1, 12=>-2],
        11 => [1=>0,  2=>0,  3=>0,  4=>0,  5=>1,  6=>0,  7=>0,  8=>0,  9=>0,  10=>1,  11=>0,  12=>0],
    ];

    const TIPI = [
        4  => [1=>'VETO',6=>'VETO',12=>'VETO',
               7=>'NEG3',8=>'NEG3',2=>'NEG2',4=>'NEG2',5=>'NEG2',10=>'NEG2',11=>'NEG2',
               3=>'NEG1',9=>'NEG1'],
        6  => [1=>'NEG3',6=>'NEG3',7=>'NEG3',10=>'NEG3',12=>'NEG3',
               2=>'NEG2',4=>'NEG2',5=>'NEG2',8=>'NEG2',11=>'NEG2',3=>'NEG1',9=>'NEG1'],
        7  => [1=>'NEG3',6=>'NEG3',7=>'NEG3',12=>'NEG3',
               2=>'NEG2',4=>'NEG2',5=>'NEG2',8=>'NEG2',10=>'NEG2',11=>'NEG2',
               3=>'NEG1',9=>'NEG1'],
        8  => [1=>'NEG2',6=>'NEG2',7=>'NEG2',10=>'NEG2',12=>'NEG3',
               2=>'NEG1',3=>'NEG1',4=>'NEG1',5=>'NEG1',8=>'NEG1',9=>'NEG1',11=>'NEG1'],
        9  => [1=>'NEG2',6=>'NEG2',7=>'NEG2',10=>'NEG2',12=>'NEG2',
               2=>'NEG1',3=>'NEG1',4=>'NEG1',5=>'NEG1',8=>'NEG1',9=>'NEG1',11=>'NEG1'],
        0  => [5=>'BON3',10=>'BON3',3=>'BON2',4=>'BON2',9=>'BON2',11=>'BON2',
               1=>'AVV',6=>'AVV',7=>'OSC',8=>'OSC',12=>'AVV'],
        5  => [5=>'BON3',10=>'BON3',3=>'BON2',4=>'BON2',9=>'BON2',11=>'BON2',
               1=>'AVV',6=>'AVV',7=>'OSC',8=>'OSC',12=>'AVV'],
        3  => [1=>'BON2',2=>'BON2',4=>'BON2',5=>'BON3',7=>'BON2',
               9=>'BON2',10=>'BON2',11=>'BON2',3=>'BON1',8=>'BON1'],
        1  => [4=>'BON2',5=>'BON2',3=>'BON1',9=>'BON1',10=>'BON1',11=>'BON1',
               1=>'NEG1',6=>'NEG1',8=>'NEG1',12=>'NEG2'],
    ];

    const NOTE = [
        4 => [
            1  => 'Marte in I: VETO — incidenti, traumi fisici, interventi d\'urgenza',
            6  => 'Marte in VI: VETO — malattie acute, interventi chirurgici urgenti',
            12 => 'Marte in XII: VETO — incidenti al PS, shock emotivi devastanti',
            7  => 'Marte in VII: litigi violenti con partner/soci, rotture repentine',
            8  => 'Marte in VIII: spese devastanti, controlli fiscali, liti per eredità',
            2  => 'Marte in II: uscite di denaro violente e improvvise',
            3  => 'Marte in III: rischio sinistri stradali — casa parcheggio accettabile',
            4  => 'Marte in IV: guasti domestici, litigi in famiglia, rischio incendi',
            5  => 'Marte in V: litigi con figli, sessualità conflittuale',
            9  => 'Marte in IX: incidenti all\'estero — casa parcheggio accettabile',
            10 => 'Marte in X: conflitti con autorità, aggressioni alla reputazione',
            11 => 'Marte in XI: litigi furiosi con amici, progetti ostacolati',
        ],
        6 => [
            1  => 'Saturno in I: depressione, invecchiamento precoce, events traumatici',
            6  => 'Saturno in VI: malattie croniche, problemi ossei/dentali, fatica',
            7  => 'Saturno in VII: separazioni legali, raggelamento rapporti',
            10 => 'Saturno in X: crollo status, perdita lavoro, cause legali perse',
            12 => 'Saturno in XII: depressione strisciante, malattia cronica, isolamento',
            2  => 'Saturno in II: carestia finanziaria, sacrifici enormi',
            4  => 'Saturno in IV: oppressione domestica, spese ristrutturazione coatte',
            5  => 'Saturno in V: raggelamento affettivo, solitudine sentimentale',
            8  => 'Saturno in VIII: blocco mutui, perdita finanziamento, lutti',
            11 => 'Saturno in XI: raggelamento sociale, solitudine, pulizia amicizie',
            3  => 'Saturno in III: blocco comunicazione, ritardi trasporti — parcheggio',
            9  => 'Saturno in IX: blocchi universitari, visti rifiutati — parcheggio',
        ],
        7 => [
            1  => 'Urano in I: eventi traumatici improvvisi, cambiamenti brutali',
            6  => 'Urano in VI: licenziamento improvviso, trauma fisico acuto',
            7  => 'Urano in VII: tagli netti improvvisi, partner via dall\'oggi al domani',
            12 => 'Urano in XII: shock emotivi devastanti, segreti esplosivi',
            2  => 'Urano in II: crollo finanziario improvviso',
            4  => 'Urano in IV: trasloco forzato inaspettato, rottura nucleo familiare',
            5  => 'Urano in V: rottura sentimentale traumatica',
            8  => 'Urano in VIII: shock economici improvvisi, lutto inaspettato',
            10 => 'Urano in X: perdita improvvisa status, licenziamento inaspettato',
            11 => 'Urano in XI: tradimento improvviso amico, fallimento progetto',
            3  => 'Urano in III: incidente auto improvviso, perdita dati',
            9  => 'Urano in IX: interruzione brusca studi, viaggio annullato',
        ],
        8 => [
            1  => 'Nettuno in I: confusione identitaria, inganni, debolezza fisica',
            6  => 'Nettuno in VI: patologie difficili da diagnosticare, inganni lavorativi',
            7  => 'Nettuno in VII: partner ingannevole, contratti truffaldini',
            10 => 'Nettuno in X: status eroso lentamente, inganni professionali',
            12 => 'Nettuno in XII: depressione profonda, dipendenze, malattie misteriose',
            2  => 'Nettuno in II: perdite finanziarie per inganni',
            3  => 'Nettuno in III: comunicazioni confuse, inganni nei rapporti vicini',
            4  => 'Nettuno in IV: situazioni domestiche nebbiose, segreti familiari',
            5  => 'Nettuno in V: illusioni amorose, creatività caotica',
            8  => 'Nettuno in VIII: eredità nebbiose, inganni finanziari',
            9  => 'Nettuno in IX: viaggi caotici, inganni dall\'estero',
            11 => 'Nettuno in XI: amicizie ingannevoli, progetti illusori',
        ],
        9 => [
            1  => 'Plutone in I: trasformazioni radicali dolorose, crisi d\'identità',
            6  => 'Plutone in VI: patologie trasformative profonde',
            7  => 'Plutone in VII: trasformazioni radicali nei legami',
            10 => 'Plutone in X: caduta di potere lenta, trasformazione carriera',
            12 => 'Plutone in XII: trasformazioni dolorose nell\'ombra, nemici nascosti',
            2  => 'Plutone in II: trasformazioni profonde nella gestione del denaro',
            3  => 'Plutone in III: comunicazioni ossessive, trasformazioni nei rapporti',
            4  => 'Plutone in IV: trasformazioni radicali nella vita domestica',
            5  => 'Plutone in V: passioni ossessive, trasformazioni affettive',
            8  => 'Plutone in VIII: crisi finanziarie profonde, trasformazioni esistenziali',
            9  => 'Plutone in IX: trasformazioni nelle credenze, viaggi trasformativi',
            11 => 'Plutone in XI: trasformazioni radicali nel cerchio sociale',
        ],
        0 => [
            5  => 'Sole in V: anno d\'oro — amore, creatività, gioia di vivere',
            10 => 'Sole in X: super-scudo — successo, emancipazione, visibilità',
            3  => 'Sole in III: anno brillante per comunicazione e studi brevi',
            4  => 'Sole in IV: protezione del focolare, acquisto casa favorevole',
            9  => 'Sole in IX: espansione — viaggi, laurea, pubblicazione',
            11 => 'Sole in XI: anno di grandi appoggi sociali',
            1  => 'Sole in I: energia esplosiva — attenzione al rischio autocentrismo',
            6  => 'Sole in VI: anno assorbito da salute/lavoro — evitare',
            7  => 'Sole in VII: potere contrattuale si sposta verso l\'altro',
            8  => 'Sole in VIII: anno assorbito da finanze altrui — oscillatore bistabile',
            12 => 'Sole in XII: non protegge — anno di prove, ospedali, isolamento',
        ],
        5 => [
            5  => 'Giove in V: anno d\'oro — amore, figli, creatività, fortuna',
            10 => 'Giove in X: super-scudo — successo professionale, emancipazione',
            3  => 'Giove in III: anno brillante per comunicazione e viaggi brevi',
            4  => 'Giove in IV: protezione focolare, acquisti immobiliari favorevoli',
            9  => 'Giove in IX: espansione — viaggi, università, pubblicazione',
            11 => 'Giove in XI: grandi appoggi sociali, amicizie influenti',
            1  => 'Giove in I: protegge la I casa — neutralizza rischi fisici, energia e vitalità',
            6  => 'Giove in VI: protegge la VI casa — neutralizza malattie e problemi lavorativi',
            7  => 'Giove in VII: potere contrattuale si sposta verso l\'altro — oscillatore',
            8  => 'Giove in VIII: no vincite facili — anno assorbito da finanze altrui',
            12 => 'Giove in XII: protegge la XII casa — attenua isolamento e prove dell\'anno',
        ],
        3 => [
            5  => 'Venere in V: anno d\'oro per amore, figli, piaceri, creatività',
            10 => 'Venere in X: immagine pubblica attraente, successo professionale',
            1  => 'Venere in I: protegge la I casa — fascino, benessere, salute favorita',
            2  => 'Venere in II: anno favorevole per entrate, lusso, guadagni',
            4  => 'Venere in IV: armonia domestica, abbellimento casa',
            7  => 'Venere in VII: anno favorevole per matrimoni e contratti',
            9  => 'Venere in IX: viaggi piacevoli, incontri con stranieri',
            11 => 'Venere in XI: amicizie importanti, vita sociale attiva',
            3  => 'Venere in III: buone comunicazioni, rapporti familiari sereni',
            8  => 'Venere in VIII: possibili entrate inaspettate, eredità favorevoli',
            6  => 'Venere in VI: protegge la VI casa — attenua problemi di salute e lavoro',
            12 => 'Venere in XII: protegge la XII casa — attenua prove e isolamento',
        ],
        1 => [
            4  => 'Luna in IV: anno favorevole per famiglia e vita domestica',
            5  => 'Luna in V: vita affettiva attiva, rapporto positivo con figli',
            3  => 'Luna in III: buona comunicazione emotiva, rapporti familiari',
            9  => 'Luna in IX: viaggi frequenti, buon rapporto con culture estere',
            10 => 'Luna in X: visibilità pubblica, buon rapporto con il pubblico',
            11 => 'Luna in XI: buone relazioni sociali, amicizie femminili utili',
            1  => 'Luna in I: ipersensibilità, sbalzi emotivi, instabilità',
            6  => 'Luna in VI: tendenza a malattie psicosomatiche, stress',
            8  => 'Luna in VIII: fluttuazioni emotive intense, lutti nel cerchio',
            12 => 'Luna in XII: tendenza al ritiro, malinconia, isolamento emotivo',
        ],
    ];

    public function valuta(
        array $temaNatale,
        array $temaRS,
        string $condizione = 'Decima',
        array $astriInCasa = []
    ): array {

        $veti     = [];
        $penalita = [];
        $bonus    = [];
        $note     = [];
        $valParts = [];
        $punteggio = 0;

        $pianeti = $temaRS['pianeti'];
        $case    = $temaRS['case'];

        // ── FASE 1: VETI ASSOLUTI ─────────────────────────────────────────
        $veti = $this->calcolaVeti($temaNatale, $temaRS, $condizione, $pianeti, $case);

        if (!empty($veti)) {
            return $this->risultato(0, $veti, [], [], [], $pianeti, $condizione);
        }

        // ── FASE 2: CALCOLO PUNTEGGIO (usato per note e VAL) ─────────────
        $risPunteggio = $this->calcolaPunteggio($pianeti, $condizione);
        $punteggio = $risPunteggio['punteggio'];
        $bonus     = $risPunteggio['bonus'];
        $penalita  = $risPunteggio['penalita'];
        $note      = $risPunteggio['note'];
        $valParts  = $risPunteggio['valParts'];

        // UX-0007: latitudine estrema (>60°) — avviso informativo non
        // bloccante (non e' una delle 34 regole ufficiali). La RSM/RL viene
        // comunque valutata normalmente con tutte le regole, punteggio e
        // stelline come qualunque altra localita'.
        if (abs($temaRS['lat'] ?? 0) > 60) {
            $note[] = [
                'codice' => 'LAT',
                'tipo'   => 'AVV',
                'nota'   => 'Latitudine ' . round($temaRS['lat'], 1) . '° — oltre 60°: a queste latitudini il sistema di case Placido può risultare meno affidabile, valuta con cautela',
            ];
        }

        // ── FASE 3: FILTRO ASTRI IN CASA ─────────────────────────────────
        $penalitaAstri = $this->filtraAstri($astriInCasa, $pianeti);

        // ── FASE 4: STELLINE PER SOTTRAZIONE (condition-aware) ───────────
        $ct        = self::CASE_TEMATICHE[$condizione] ?? self::CASE_TEMATICHE['Decima'];
        $caseMalus = $ct['malus'];   // case chiave dove malevolo pesa di più
        $caseBonus = $ct['bonus'];   // case chiave dove benefico vale di più
        $stelline = $this->calcolaStelline($temaNatale, $pianeti, $case, $caseMalus, $caseBonus);

        // ── FASE 5: STRINGA VAL ───────────────────────────────────────────
        $valStr = $this->generaVAL($stelline, $valParts, $pianeti, $caseBonus);

        return $this->risultato($stelline, [], $bonus, $penalita, $note,
                                $pianeti, $condizione, $punteggio, $valStr, $penalitaAstri);
    }

    private function generaVAL(int $stelline, array $valParts, array $pianeti, array $caseBonus): string {
        // ── FASE 5: STRINGA VAL ───────────────────────────────────────────
        // Mostra i pianeti negativi rilevanti + i benefici nelle case chiave
        // con prefisso "+" per distinguerli visivamente.
        // Aggiunge anche GI/VE in I/VI/XII (protezione universale).

        foreach ($pianeti as $id => $p) {
            $casa = $p['casa'];
            // Benefici nelle case tematiche della condizione
            if (in_array($id, [0, 5, 3]) && in_array($casa, $caseBonus)) {
                $nomeBreve = '+' . (self::VAL_NOMI[$id] ?? '?') . $casa;
                if (!in_array($nomeBreve, $valParts)) $valParts[] = $nomeBreve;
            }
            // GI o VE in I/VI/XII (protezione universale) — evidenziati sempre
            if (in_array($id, [5, 3]) && in_array($casa, [1, 6, 12])) {
                $nomeBreve = '+' . (self::VAL_NOMI[$id] ?? '?') . $casa;
                if (!in_array($nomeBreve, $valParts)) $valParts[] = $nomeBreve;
            }
        }
        // Ordine: prima i negativi (MA, SA, UR, NE, PLU), poi i positivi (SO, GI, VE, LU, ME)
        $ordine = ['MA'=>1,'SA'=>2,'UR'=>3,'NE'=>4,'PLU'=>5,'SO'=>6,'GI'=>7,'VE'=>8,'LU'=>9,'ME'=>10,'NO'=>11];
        usort($valParts, function($a, $b) use ($ordine) {
            // Rimuove il prefisso "+" per l'ordinamento
            $cleanA = ltrim($a, '+');
            $cleanB = ltrim($b, '+');
            preg_match('/^([A-Z]+)/', $cleanA, $ma);
            preg_match('/^([A-Z]+)/', $cleanB, $mb);
            $posA = $ordine[$ma[1]] ?? 99;
            $posB = $ordine[$mb[1]] ?? 99;
            if ($posA !== $posB) return $posA <=> $posB;
            // A parità di pianeta, i negativi (senza +) vengono prima
            return (str_starts_with($b, '+') ? -1 : 0) <=> (str_starts_with($a, '+') ? -1 : 0);
        });

        return str_repeat('*', $stelline) . (empty($valParts) ? '' : '/' . implode('/', $valParts));
    }

    private function calcolaStelline(
        array $temaNatale,
        array $pianeti,
        array $case,
        array $caseMalus,
        array $caseBonus
    ): int {
        // ── FASE 4: STELLINE PER SOTTRAZIONE (condition-aware) ───────────
        //
        // Regola universale Discepolo:
        //   I, VI, XII sono case negative per QUALSIASI condizione.
        //   Malevoli (MA/SA/UR/NE/PLU) in I/VI/XII → pesantissimi sempre.
        //   Giove o Venere in I/VI/XII → bonus +1 che NEUTRALIZZA il rischio
        //   (non elimina del tutto, ma protegge significativamente).
        //
        // Si parte da 5 stelle.
        // 1. Sottrazioni fisse universali (malevoli in case critiche)
        // 2. Bonus GI/VE in I/VI/XII (neutralizzazione)
        // 3. Malus condizionale (malevoli nella casa tematica specifica)
        // 4. Bonus condizionale (GI/VE nella casa tematica specifica)
        // max(0) e min(5) garantiti a fine calcolo.

        $stelle = 5;

        // Pre-calcola: ci sono GI o VE in I, VI, XII?
        $protezioneCase = [1 => false, 6 => false, 12 => false];
        foreach ($pianeti as $id => $p) {
            if (in_array($id, [5, 3]) && in_array($p['casa'], [1, 6, 12])) {
                $protezioneCase[$p['casa']] = true;
            }
        }

        $bonusCondApplicati = [];
        $malusCondApplicati = [];

        foreach ($pianeti as $id => $p) {
            $casa = $p['casa'];
            $key  = "{$id}_{$casa}";

            // ── 1. Sottrazioni fisse universali ─────────────────────────────

            // Marte: I/VI/XII = veto già gestito sopra; VIII pesante; VII e altre -1
            // (III e IX = case parcheggio, non tolgono stelle)
            if ($id === 4) {
                if ($casa === 8)                              $stelle -= 2;
                elseif ($casa === 7)                          $stelle -= 1;
                elseif (in_array($casa, [2, 4, 5, 10, 11]))  $stelle -= 1;
                // I/VI/XII già coperti dal veto assoluto in FASE 1
            }

            // Saturno e Urano
            if (in_array($id, [6, 7])) {
                if (in_array($casa, [1, 6, 12])) {
                    // Protetti da GI/VE nella stessa casa? Penalità attenuata
                    $stelle -= $protezioneCase[$casa] ? 1 : 2;
                } elseif (in_array($casa, [7, 10])) {
                    $stelle -= 1;
                }
            }

            // Nettuno e Plutone in I/VI/XII: non scattano veto ma pesano
            if (in_array($id, [8, 9])) {
                if (in_array($casa, [1, 6, 12])) {
                    $stelle -= $protezioneCase[$casa] ? 0 : 1;
                }
            }

            // ── 2. Bonus GI/VE in I/VI/XII (neutralizzazione universale) ────
            // Giove o Venere in queste case difficili non solo attenuano
            // il rischio ma danno un bonus attivo (+1 per la protezione).
            if (in_array($id, [5, 3]) && in_array($casa, [1, 6, 12])
                && !isset($bonusCondApplicati[$key])) {
                $stelle += 1;
                $bonusCondApplicati[$key] = true;
            }

            // ── 3. Malus condizionale ────────────────────────────────────────
            // Malevolo (MA/SA/UR) nella casa tematica specifica della condizione
            // → penalità extra -1 (oltre le sottrazioni fisse già applicate).
            // Non si applica alle case I/VI/XII (già gestite sopra).
            if (in_array($id, [4, 6, 7])
                && in_array($casa, $caseMalus)
                && !in_array($casa, [1, 6, 12])  // evita doppio conteggio
                && !isset($malusCondApplicati[$key])) {
                $stelle -= 1;
                $malusCondApplicati[$key] = true;
            }

            // ── 4. Bonus condizionale ────────────────────────────────────────
            // GI/VE nella casa tematica specifica → +1 stella.
            // Non si applica a I/VI/XII (già gestite nel bonus universale).
            if (in_array($id, [0, 5, 3])
                && in_array($casa, $caseBonus)
                && !in_array($casa, [1, 6, 12])  // evita doppio conteggio
                && !isset($bonusCondApplicati[$key])) {
                $stelle += 1;
                $bonusCondApplicati[$key] = true;
            }
        }

        // ASC RS in casa natale — solo VIII penalizza significativamente
        $casaNataleAsc = $this->trovaCasaNatale(
            $case['ASC']['longitudine'] ?? 0,
            $temaNatale['case']
        );
        if ($casaNataleAsc === 8)                             $stelle -= 2;
        elseif (in_array($casaNataleAsc, [2, 7]))             $stelle -= 1;

        return max(0, min(5, $stelle));

    }

    private function calcolaVeti(
        array $temaNatale,
        array $temaRS,
        string $condizione,
        array $pianeti,
        array $case
    ): array {
        $veti = [];

        // ── FASE 1: VETI ASSOLUTI ─────────────────────────────────────────

        // ── Veto ASC RS in I, VI o XII casa natale (Regola 4 + Regola 6a) ─
        // v4.1: applicato anche il pre-ingresso di 3° sulle cuspidi natali
        // I, VI e XII. Se l'ASC RS è entro 3° PRIMA della cuspide di una
        // di queste case natali, è trattato come se vi fosse già entrato.
        $ascRS = $case['ASC']['longitudine'] ?? 0;
        $casaNataleAscRS = $this->trovaCasaNatale($ascRS, $temaNatale['case']);
        if (in_array($casaNataleAscRS, [1, 6, 12])) {
            $veti[] = "VETO: Ascendente RS in {$casaNataleAscRS}a casa natale — anno pesantissimo garantito";
        } else {
            // Pre-ingresso 3°: controlla se l'ASC RS è entro 3° prima delle
            // cuspidi natali di I, VI o XII (effetto pre-ingresso Discepolo).
            foreach ([1, 6, 12] as $casaCritica) {
                if (!isset($temaNatale['case'][$casaCritica])) continue;
                $cuspide = $temaNatale['case'][$casaCritica]['longitudine'];
                // diffAngolo(ASC, cuspide): negativo = ASC è PRIMA della cuspide
                $diff = $this->diffAngolo($ascRS, $cuspide);
                // diff ∈ (-3°, 0°) → ASC è nei 3° immediatamente precedenti la cuspide
                if ($diff > -3.0 && $diff < 0.0) {
                    $veti[] = "VETO: Ascendente RS a " . round(abs($diff), 1) . "° dalla {$casaCritica}a casa natale (pre-ingresso) — anno pesantissimo garantito";
                    break;
                }
            }
        }

        // ── Regole 2-4: Marte in case veto RS; Stellium in I/VI/VIII/XII ─
        // v4.1: il controllo di Marte in I/VI/XII RS è esteso al pre-ingresso
        // di 3° (se Marte è nei 3° immediatamente precedenti la cuspide di I,
        // VI o XII, è trattato come "già entrato" in quella casa).
        // Il controllo stellium usa le case assegnate da SweCalc (invariato).

        // Pre-ingresso Sole + malevoli in I/VI/XII RS (Fail-safe 1 v4.1;
        // Sole aggiunto con UX-0004 per completare la Regola 4)
        // SO=0, MA=4, SA=6, UR=7, NE=8, PLU=9
        foreach ([1, 6, 12] as $casaVeto) {
            if (!isset($case[$casaVeto])) continue;
            $cuspideVeto = $case[$casaVeto]['longitudine'];
            foreach (array_merge([0], self::MALEVOLI) as $idMal) {
                if (!isset($pianeti[$idMal])) continue;
                $lonMal = $pianeti[$idMal]['longitudine'];
                // Il pianeta è già assegnato a questa casa da SweCalc?
                // Lo skip avviene perché il veto standard sotto lo coprirà.
                if ((int)$pianeti[$idMal]['casa'] === $casaVeto) continue;
                // Pre-ingresso: pianeta entro 3° PRIMA della cuspide della casa veto
                $diff = $this->diffAngolo($lonMal, $cuspideVeto);
                if ($diff > -3.0 && $diff < 0.0) {
                    $nome = self::VAL_NOMI[$idMal] ?? '?';
                    $veti[] = "VETO: {$nome} a " . round(abs($diff), 1) . "° dalla {$casaVeto}a casa RS (pre-ingresso) — posizione pericolosa";
                }
            }
        }

        foreach ([1, 6, 8, 12] as $casaVeto) {
            $inCasa = $this->pianetaInCasa($pianeti, $casaVeto);

            // Marte in I, VI, XII (casa assegnata da SweCalc)
            if ($casaVeto !== 8 && in_array(4, $inCasa)) {
                $veti[] = "VETO: Marte in {$casaVeto}a casa RS";
            }

            // Sole in I, VI, XII (casa assegnata da SweCalc) - UX-0004,
            // completa la Regola 4 (Ascendente, stellium O Sole in I/VI/XII).
            if ($casaVeto !== 8 && in_array(0, $inCasa)) {
                $veti[] = "VETO (Regola 4): Sole in {$casaVeto}a casa RS";
            }

            // Stellium (3+) - REGOLA 4/16/26/31: nessuna eccezione per benefici
            // presenti nello stellium (allineato a docs/status/34_regole_rsm.md,
            // decisione UX-0002 in docs/ux-myastral/DECISION_LOG_ux.md).
            if (count($inCasa) >= 3) {
                $nomi = implode('+', array_map(fn($id) => self::VAL_NOMI[$id] ?? '?', $inCasa));
                $veti[] = "VETO: Stellium ({$nomi}) in {$casaVeto}a casa";
            }
        }

        // Regola 31 - UX-0005: stellium diviso tra XII e I casa (es. Giove in
        // XII + Venere/Mercurio in I) vale come stellium pieno in XII. Solo
        // la coppia XII/I e' prevista dal testo di Discepolo - non generalizzata
        // ad altre case adiacenti. Scatta solo se nessuna delle due case da
        // sola raggiunge gia' 3 (caso gia' coperto dal veto sopra).
        $inCasa12 = $this->pianetaInCasa($pianeti, 12);
        $inCasa1  = $this->pianetaInCasa($pianeti, 1);
        if (count($inCasa12) < 3 && count($inCasa1) < 3
            && count($inCasa12) >= 1 && count($inCasa1) >= 1
            && (count($inCasa12) + count($inCasa1)) >= 3) {
            $nomi = implode('+', array_map(fn($id) => self::VAL_NOMI[$id] ?? '?', array_merge($inCasa12, $inCasa1)));
            $veti[] = "VETO (Regola 31): Stellium diviso XII/I ({$nomi}) — vale come stellium pieno in XII";
        }

        // Regola 33 (UX-0006), caso (a): Saturno e un benefico (Sole/Venere/
        // Giove) nella stessa casa, qualunque essa sia - Saturno prevale sempre,
        // incondizionatamente (indipendente dal flag MYASTRAL_ALIGNMENT_MODE,
        // che resta un sistema separato per RuleEngineExtended.php - UX-0001).
        if (isset($pianeti[6])) {
            $casaSaturno = $pianeti[6]['casa'];
            $beneficiStessaCasa = [];
            foreach ([0, 3, 5] as $idBenef) {
                if (isset($pianeti[$idBenef]) && $pianeti[$idBenef]['casa'] === $casaSaturno) {
                    $beneficiStessaCasa[] = $idBenef;
                }
            }
            if (!empty($beneficiStessaCasa)) {
                $nomi = implode('+', array_map(fn($id) => self::VAL_NOMI[$id] ?? '?', $beneficiStessaCasa));
                $veti[] = "VETO (Regola 33): Saturno prevale su {$nomi} in {$casaSaturno}a casa";
            }
        }

        // Regola 33 (UX-0006), caso (b): Saturno e un benefico in case
        // adiacenti IX/X (Medio Cielo), entro 3° dalla stessa cuspide su lati
        // opposti. Solo questa coppia, come esplicitamente citata dal testo -
        // non generalizzata ad altre case adiacenti (coerente con la Regola 31).
        if (isset($case[10]['longitudine'])) {
            $cuspideMC = $case[10]['longitudine'];
            $saturnoVicino = null;
            if (isset($pianeti[6]) && (int)$pianeti[6]['casa'] === 9) {
                $diffSat = $this->diffAngolo($pianeti[6]['longitudine'], $cuspideMC);
                if ($diffSat > -3.0 && $diffSat <= 0.0) {
                    $saturnoVicino = round(abs($diffSat), 1);
                }
            }
            if ($saturnoVicino !== null) {
                foreach ([0, 3, 5] as $idBenef) {
                    if (!isset($pianeti[$idBenef]) || (int)$pianeti[$idBenef]['casa'] !== 10) continue;
                    $diffBenef = $this->diffAngolo($pianeti[$idBenef]['longitudine'], $cuspideMC);
                    if ($diffBenef >= 0.0 && $diffBenef < 3.0) {
                        $nome = self::VAL_NOMI[$idBenef] ?? '?';
                        $veti[] = "VETO (Regola 33 — case adiacenti): Saturno a {$saturnoVicino}° dal MC in IX e {$nome} a " . round($diffBenef,1) . "° dal MC in X — Saturno prevale";
                    }
                }
            }
        }

        // Regola 34 — UX-0003: Marte e Saturno nella stessa casa RS/RL,
        // eccetto III e IX (case-parcheggio neutre, self::CASE_PARCHEGGIO).
        // Nessun pre-ingresso previsto: il testo di Discepolo non lo menziona
        // per questa regola, a differenza delle Regole 4/5.
        if (isset($pianeti[4], $pianeti[6])) {
            $casaMarte   = $pianeti[4]['casa'];
            $casaSaturno = $pianeti[6]['casa'];
            if ($casaMarte === $casaSaturno && !in_array($casaMarte, self::CASE_PARCHEGGIO)) {
                $veti[] = "VETO (Regola 34): Marte e Saturno entrambi in {$casaMarte}a casa — non ammesso tranne in III o IX";
            }
        }

        // Veto astrolab-angoli (NON e' la Regola 33 ufficiale, vedi commento
        // sul messaggio sotto): Marte o Saturno entro 2° dagli angoli
        foreach ([4, 6] as $mal) {
            if (!isset($pianeti[$mal])) continue;
            $lonP = $pianeti[$mal]['longitudine'];
            foreach (self::ANGOLARI as $ang) {
                if (!isset($case[$ang])) continue;
                $diff = abs($this->diffAngolo($lonP, $case[$ang]['longitudine']));
                if ($diff <= 2.0) {
                    $veti[] = "VETO (astrolab-angoli): " . self::VAL_NOMI[$mal] . " a " . round($diff,1) . "° dalla cuspide casa {$ang} — regola proprietaria, non una delle 34 regole ufficiali";
                }
            }
        }

        // NOTA — UX-0007: il veto latitudine estrema (>60°) e' stato declassato
        // da veto assoluto a nota informativa non bloccante (vedi FASE 2 dopo
        // calcolaPunteggio()): non e' una delle 34 regole ufficiali, e scartava
        // automaticamente configurazioni che potevano essere astrologicamente
        // valide (confronto con myastral.org, sessione 2026-08-18).

        // ── FASE 1-BIS: CONTROLLO DECIMA (pre-ingresso e sicurezza-uscita) ──
        //
        // Specifico per condizione 'Decima': verifica che Sole o Giove siano
        // effettivamente "utili" in X casa RS, considerando:
        //
        //   a) PRE-INGRESSO (+): un pianeta nei 3° finali della IX è già
        //      considerato "in X" ai fini del bonus (ma non genera un VETO —
        //      questa regola è positiva, non serve qui).
        //
        //   b) SICUREZZA IN USCITA (−): se Sole o Giove sono nei PRIMI 2°
        //      della XI (cioè hanno già lasciato la X da meno di 2°), la
        //      destinazione viene scartata perché l'anno non è "coperto" dalla X.
        //
        if ($condizione === 'Decima' && empty($veti)) {
            foreach ([0, 5] as $idBenef) { // Sole=0, Giove=5
                if (!isset($pianeti[$idBenef])) continue;
                $casaXI    = 11;
                if (!isset($case[$casaXI])) continue;
                $cuspideXI = $case[$casaXI]['longitudine'];
                $lonBenef  = $pianeti[$idBenef]['longitudine'];
                $diff = $this->diffAngolo($lonBenef, $cuspideXI);
                if ($diff >= 0.0 && $diff < 2.0) {
                    $nome = self::VAL_NOMI[$idBenef] ?? '?';
                    $veti[] = "VETO (Decima — sicurezza uscita): {$nome} a " . round($diff, 1) . "° nella XI casa RS — troppo vicino all'uscita dalla X, anno non coperto";
                }
            }
        }

        // ── FASE 1-TER: CONTROLLO LAVORO (sicurezza-uscita) ─────────────────
        //
        // Specifica per condizione 'Lavoro': i benefici rilevanti sono
        // SO=0, GI=5, VE=3 nelle case VI e X.
        //
        // Sicurezza in uscita — due controlli paralleli:
        //
        //   1) Benefico nei primi 2° della XI (ha appena lasciato la X):
        //      l'anno di carriera/avanzamento non è "coperto". VETO.
        //
        //   2) Benefico nei primi 2° della VII (ha appena lasciato la VI):
        //      la VI come casa del lavoro quotidiano non è più coperta. VETO.
        //
        // Nota: SO in VI per Lavoro ha già un peso negativo elevato nella
        // MATRICE (casa 6, id 0 → AVV), ma non è un veto automatico.
        // La regola di uscita dalla VII si applica solo a GI e VE in VI
        // (che sarebbero i veri benefici lavorativi lì).
        //
        if ($condizione === 'Lavoro' && empty($veti)) {

            // 1. Uscita dalla X → XI: SO, GI, VE troppo vicini alla cuspide XI
            foreach ([0, 5, 3] as $idBenef) { // Sole=0, Giove=5, Venere=3
                if (!isset($pianeti[$idBenef])) continue;
                $casaXI    = 11;
                if (!isset($case[$casaXI])) continue;
                $cuspideXI = $case[$casaXI]['longitudine'];
                $lonBenef  = $pianeti[$idBenef]['longitudine'];
                // diff ∈ [0°, 2°) → pianeta appena entrato in XI, ha lasciato la X
                $diff = $this->diffAngolo($lonBenef, $cuspideXI);
                if ($diff >= 0.0 && $diff < 2.0) {
                    $nome = self::VAL_NOMI[$idBenef] ?? '?';
                    $veti[] = "VETO (Lavoro — sicurezza uscita X): {$nome} a " . round($diff, 1) . "° nella XI casa RS — troppo vicino all'uscita dalla X, anno lavorativo non coperto";
                }
            }

            // 2. Uscita dalla VI → VII: solo GI e VE (SO in VI è già neutro/neg per Lavoro)
            foreach ([5, 3] as $idBenef) { // Giove=5, Venere=3
                if (!isset($pianeti[$idBenef])) continue;
                $casaVII    = 7;
                if (!isset($case[$casaVII])) continue;
                $cuspideVII = $case[$casaVII]['longitudine'];
                $lonBenef   = $pianeti[$idBenef]['longitudine'];
                // diff ∈ [0°, 2°) → pianeta appena entrato in VII, ha lasciato la VI
                $diff = $this->diffAngolo($lonBenef, $cuspideVII);
                if ($diff >= 0.0 && $diff < 2.0) {
                    $nome = self::VAL_NOMI[$idBenef] ?? '?';
                    $veti[] = "VETO (Lavoro — sicurezza uscita VI): {$nome} a " . round($diff, 1) . "° nella VII casa RS — troppo vicino all'uscita dalla VI, protezione lavorativa non coperta";
                }
            }
        }

        return $veti;
    }

    private function calcolaPunteggio(array $pianeti, string $condizione): array {
        // ── FASE 2: CALCOLO PUNTEGGIO (usato per note e VAL) ─────────────
        $cond = self::CONDIZIONI[$condizione] ?? self::CONDIZIONI['Decima'];

        $punteggio = 0;
        $bonus = [];
        $penalita = [];
        $note = [];
        $valParts = [];

        foreach ($pianeti as $id => $pianeta) {
            $casa = $pianeta['casa'];
            if ($casa === 0 || !isset(self::MATRICE[$id][$casa])) continue;

            $pesoBase = self::MATRICE[$id][$casa];

            $moltiplicatore = 1.0;
            if ($pesoBase > 0 && isset($cond['bonus'][$id][$casa])) {
                $moltiplicatore = $cond['bonus'][$id][$casa];
            } elseif ($pesoBase < 0 && isset($cond['penalita'][$id][$casa])) {
                $moltiplicatore = $cond['penalita'][$id][$casa];
            }

            $peso = $pesoBase * $moltiplicatore;
            $punteggio += $peso;

            $tipo = self::TIPI[$id][$casa] ?? null;
            if (!$tipo || $tipo === 'NEUT') continue;

            $nomeBreve = (self::VAL_NOMI[$id] ?? '?') . $casa;

            $includi = match(true) {
                $tipo === 'VETO'                                     => true,
                $tipo === 'NEG3'                                     => true,
                $tipo === 'NEG2' && !($id === 7 && $casa === 5) => true,
                // NEG1 solo se NON è casa parcheggio (III=3, IX=9)
                $tipo === 'NEG1' && in_array($id,[4,6,7,8,9]) && !in_array($casa,[3,9]) => true,
                $tipo === 'BON3'                                     => true,
                false, // Aladino non mostra i bonus nella VAL
                in_array($tipo,['OSC','AVV']) && in_array($id,[0,5])=> true,
                default                                              => false,
            };

            if ($includi) $valParts[] = $nomeBreve;

            $testo = self::NOTE[$id][$casa] ?? null;

            if (str_starts_with($tipo, 'BON')) {
                $bonus[] = ['codice'=>$nomeBreve,'tipo'=>$tipo,'peso'=>$peso,'nota'=>$testo];
            } elseif (str_starts_with($tipo, 'NEG')) {
                $penalita[] = ['codice'=>$nomeBreve,'tipo'=>$tipo,'peso'=>$peso,'nota'=>$testo];
            } elseif (in_array($tipo, ['OSC','AVV'])) {
                $note[] = ['codice'=>$nomeBreve,'tipo'=>$tipo,'nota'=>$testo];
            }
        }

        return [
            'punteggio' => $punteggio,
            'bonus' => $bonus,
            'penalita' => $penalita,
            'note' => $note,
            'valParts' => $valParts,
        ];
    }


    private function filtraAstri(array $astriInCasa, array $pianeti): array {
        // ── FASE 3: FILTRO ASTRI IN CASA ─────────────────────────────────
        $penalitaAstri = [];
        foreach ($astriInCasa as $filtro) {
            $idP   = $filtro['pianeta'];
            $casaV = $filtro['casa'];
            $vuole = $filtro['vuole'];
            if (!isset($pianeti[$idP])) continue;
            $casaE = $pianeti[$idP]['casa'];
            if ($vuole && $casaE !== $casaV)
                $penalitaAstri[] = self::VAL_NOMI[$idP] . ' non e in casa ' . $casaV;
            elseif (!$vuole && $casaE === $casaV)
                $penalitaAstri[] = self::VAL_NOMI[$idP] . ' e in casa ' . $casaV . ' (indesiderato)';
        }

        return $penalitaAstri;
    }

    /**
     * Verifica le tolleranze di pre-ingresso e sicurezza in uscita
     * per una coppia (pianeta, casa) secondo le regole Discepolo.
     *
     * @param array  $pianeta   Dati del pianeta ['longitudine' => float, 'casa' => int]
     * @param int    $casaTarget Casa target (es. 5 per V, 7 per VII)
     * @param array  $caseRS    Array delle case RS con longitudine
     * @param float  $tolleranzaPreIngresso Gradi prima della cuspide (default 3.0)
     * @param float  $tolleranzaUscita Gradi dopo la cuspide successiva (default 2.0)
     * @return array{inCasa:bool, inPreIngresso:bool, inUscita:bool, diffCuspide:float, diffUscita:?float}
     */
    public function verificaPreIngressoUscita(
        array $pianeta,
        int $casaTarget,
        array $caseRS,
        float $tolleranzaPreIngresso = 3.0,
        float $tolleranzaUscita = 2.0
    ): array {
        $longitudine = (float)$pianeta['longitudine'];
        $casaAssegnata = (int)$pianeta['casa'];

        // Verifica che la casa target esista
        if (!isset($caseRS[$casaTarget])) {
            return [
                'inCasa' => false,
                'inPreIngresso' => false,
                'inUscita' => false,
                'diffCuspide' => 0.0,
                'diffUscita' => null
            ];
        }

        $cuspideTarget = $caseRS[$casaTarget]['longitudine'];

        // Calcola la differenza angolare (modulo 360°)
        $diffCuspide = $this->diffAngolo($longitudine, $cuspideTarget);

        // Pre-ingresso: il pianeta è nei 3° immediatamente precedenti la cuspide
        $inPreIngresso = ($diffCuspide > -$tolleranzaPreIngresso && $diffCuspide < 0.0);

        // Il pianeta è nella casa target (assegnata da SweCalc) o in pre-ingresso?
        $inCasaTarget = ($casaAssegnata === $casaTarget) || $inPreIngresso;

        // Verifica la sicurezza in uscita: pianeta a meno di 2° dalla cuspide successiva
        $diffUscita = null;
        $inUscita = false;

        if ($inCasaTarget) {
            $casaSuccessiva = ($casaTarget === 12) ? 1 : $casaTarget + 1;
            if (isset($caseRS[$casaSuccessiva])) {
                $cuspideSuccessiva = $caseRS[$casaSuccessiva]['longitudine'];
                $diffUscita = $this->diffAngolo($longitudine, $cuspideSuccessiva);
                // diffUscita ∈ [0°, tolleranzaUscita°) → pianeta appena entrato nella casa successiva
                $inUscita = ($diffUscita >= 0.0 && $diffUscita < $tolleranzaUscita);
            }
        }

        return [
            'inCasa' => $inCasaTarget,
            'inPreIngresso' => $inPreIngresso,
            'inUscita' => $inUscita,
            'diffCuspide' => $diffCuspide,
            'diffUscita' => $diffUscita
        ];
    }

    private function trovaCasaNatale(float $lon, array $caseNatale): int {
        $lon = fmod($lon + 360, 360);
        for ($c = 1; $c <= 12; $c++) {
            if (!isset($caseNatale[$c])) continue;
            $ini  = fmod($caseNatale[$c]['longitudine'] + 360, 360);
            $fine = fmod($caseNatale[($c % 12) + 1]['longitudine'] + 360, 360);
            if ($ini <= $fine) { if ($lon >= $ini && $lon < $fine) return $c; }
            else               { if ($lon >= $ini || $lon < $fine) return $c; }
        }
        return 1;
    }

    private function pianetaInCasa(array $pianeti, int $casa): array {
        $r = [];
        foreach ($pianeti as $id => $p) if ($p['casa'] === $casa) $r[] = $id;
        return $r;
    }

    private function diffAngolo(float $a, float $b): float {
        return AstroUtils::diffAngolo($a, $b);
    }

    private function risultato(int $stelline, array $veti, array $bonus, array $penalita,
                               array $note, array $pianeti, string $condizione,
                               float $punteggio = 0, string $valStr = '0', array $astri = []): array {
        return [
            'stelline'         => $stelline,
            'stelle_str'       => str_repeat('★',$stelline) . str_repeat('☆',5-$stelline),
            'val'              => $valStr,
            'punteggio_grezzo' => $punteggio,
            'veti'             => $veti,
            'bonus'            => $bonus,
            'penalita'         => $penalita,
            'note'             => $note,
            'astri_warning'    => $astri,
            'condizione'       => $condizione,
            'is_valida'        => empty($veti),
        ];
    }
}