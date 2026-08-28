<?php
require_once __DIR__ . '/AstroUtils.php';
require_once __DIR__ . '/RuleEngine.php';

/**
 * RuleEngineExtended — Punteggio "Discepolo parziale" (allineamento MyAstral)
 *
 * NON sostituisce RuleEngine.php (che resta in FREEZE, invariato — vedi
 * docs/roadmap_comparazione_myastral.md e docs/ux-myastral/DECISION_LOG_ux.md,
 * decisione UX-0001). Calcola un punteggio PARALLELO e OPZIONALE, additivo,
 * basato sul metro di valutazione confermato direttamente dal committente
 * (non è parte delle 34 regole in sé: è un metro di paragone che serve a
 * misurare quanto una RSM/RL rispetta le regole).
 *
 * Attivo solo se il feature flag MYASTRAL_ALIGNMENT_MODE è abilitato in
 * config. A flag disattivo il comportamento dell'app resta identico a oggi.
 *
 * COPERTURA ATTUALE (parziale, per costruzione):
 * - Solo Sole (SO), Venere (VE), Giove (GI) contribuiscono positivamente.
 * - Solo le condizioni con UNA casa tematica univoca e già confermata dal
 *   committente sono supportate: Decima (X), Lavoro (VI), Casa (IV).
 * - Amore, Salute, Denaro, Denaro Low NON sono ancora supportate: hanno più
 *   case tematiche candidate (es. Amore: V e/o VII) e il committente non ha
 *   ancora confermato come si sommano i valori in quei casi — per non
 *   inventare, calcolaPunteggioParziale() restituisce 'supportata' => false
 *   per queste condizioni finché non arriva conferma.
 * - Marte, Saturno, Urano, Nettuno, Plutone, Luna, Mercurio non hanno ancora
 *   un valore confermato in questo sistema additivo: non contribuiscono al
 *   punteggio parziale. I veti/esclusioni esistenti (RuleEngine::calcolaVeti,
 *   FiltroEsclusione.php, RicercaRSFilters.php) restano invariati e vengono
 *   comunque applicati A MONTE, indipendentemente da questo punteggio.
 *
 * Estendere questa mappa SOLO dopo conferma esplicita di nuovi valori da
 * parte del committente — mai per interpolazione o supposizione.
 *
 * FONTE PRIMARIA E VINCOLANTE: docs/status/34_regole_rsm.md (le 34 regole
 * ufficiali dell'Astrologia Attiva di Ciro Discepolo, digitalizzate dal
 * committente). Qualunque regola aggiuntiva costruita sopra queste 34 (come
 * il punteggio additivo di questo file) è un livello supplementare, mai un
 * sostituto: in caso di conflitto le 34 regole prevalgono sempre.
 *
 * REGOLA 33 (Saturno prevale) — CASO "STESSA CASA" IMPLEMENTATO COME
 * ESCLUSIONE, non come semplice azzeramento del punteggio: se Saturno è
 * nella stessa casa della condizione, la RSM/RL viene tolta dai risultati
 * (vedi il flag 'saturno_prevale' qui sotto e il suo utilizzo in
 * www/api/ricerca_stream_api.php). Confermato esplicitamente dal committente.
 *
 * NOTA — "reg.33" già presente in RuleEngine.php (riga ~589, "Marte o
 * Saturno entro 2° dagli angoli") NON è questa regola: è un'etichetta
 * storica errata, stesso problema già trovato per "reg.31" (veto
 * latitudine). Non toccata qui: RuleEngine.php resta in FREEZE.
 *
 * GAP NOTO RESIDUO — Regola 33 descrive anche un confronto tra Saturno e un
 * benefico in CASE ADIACENTI, entro lo stesso orbo dalla medesima cuspide
 * (es. Giove a 5° dal MC in decima vs Saturno a 5° dallo stesso MC ma in
 * nona: "alla fine prevarrà il secondo"). Questo confronto tra orbi su case
 * diverse NON è ancora implementato: Discepolo non specifica una soglia
 * numerica univoca per "stessa distanza" in questo testo, e non voglio
 * indovinarla. Da chiarire con il committente prima di implementarlo.
 */
class RuleEngineExtended {

    // Casa tematica univoca per condizione — SOLO le condizioni confermate.
    // Aggiungere una voce qui abilita automaticamente calcolaPunteggioParziale()
    // per quella condizione: farlo solo dopo conferma esplicita della casa
    // corretta da usare (specie per condizioni con più case candidate).
    const CASA_CONDIZIONE = [
        'Decima' => 10,
        'Lavoro' => 6,
        'Casa'   => 4,
    ];

    // I quattro angoli — solo qui si applica il bonus d'orbo per Giove.
    const CASE_ANGOLARI = [1, 4, 7, 10];

    // Valori base confermati (id pianeta secondo RuleEngine::VAL_NOMI).
    // 0 = Sole, 3 = Venere, 5 = Giove.
    const VALORI_BASE = [
        0 => 2,  // Sole
        3 => 4,  // Venere
        5 => 4,  // Giove (base, senza bonus d'orbo)
    ];

    const ORBO_MAX_GRADI    = 2.5;  // orbo massimo dalla cuspide per il bonus
    const BONUS_ORBO_GIOVE  = 2;    // Giove entro l'orbo: 4 + 2 = 6

    const ID_SATURNO = 6;  // Regola 33: Saturno nella stessa casa esclude il risultato

    /**
     * Calcola il punteggio parziale Sole/Venere/Giove nella casa tematica
     * della condizione, con bonus d'orbo per Giove angolare.
     *
     * @param array  $pianeti   Come in RuleEngine::valuta() — $temaRS['pianeti']
     * @param array  $case      Come in RuleEngine::valuta() — $temaRS['case']
     * @param string $condizione
     * @return array{
     *   supportata: bool,
     *   punteggio: int,
     *   casa: int|null,
     *   dettaglio: array<int, array{pianeta:string, casa:int, valore:int, entro_orbo:bool}>,
     *   motivo_non_supportata: string|null
     * }
     */
    public function calcolaPunteggioParziale(array $pianeti, array $case, string $condizione): array {

        if (!isset(self::CASA_CONDIZIONE[$condizione])) {
            return [
                'supportata' => false,
                'punteggio'  => null,
                'casa'       => null,
                'dettaglio'  => [],
                'motivo_non_supportata' =>
                    "Condizione '{$condizione}' non ancora supportata dal punteggio parziale: " .
                    "casa tematica non confermata dal committente.",
            ];
        }

        $casaCond = self::CASA_CONDIZIONE[$condizione];
        $punteggio = 0;
        $dettaglio = [];

        foreach (self::VALORI_BASE as $idPianeta => $valoreBase) {
            if (!isset($pianeti[$idPianeta])) continue;
            if ((int)$pianeti[$idPianeta]['casa'] !== $casaCond) continue;

            $valore = $valoreBase;
            $entroOrbo = false;

            // Bonus d'orbo: solo Giove (id 5), solo se la casa è un angolo,
            // solo se abbiamo la longitudine della cuspide per calcolarlo.
            if ($idPianeta === 5
                && in_array($casaCond, self::CASE_ANGOLARI, true)
                && isset($case[$casaCond]['longitudine'])
                && isset($pianeti[$idPianeta]['longitudine'])
            ) {
                $lonGiove = (float)$pianeti[$idPianeta]['longitudine'];
                $lonCuspide = (float)$case[$casaCond]['longitudine'];
                $diff = abs(AstroUtils::diffAngolo($lonGiove, $lonCuspide));

                if ($diff <= self::ORBO_MAX_GRADI) {
                    $valore = $valoreBase + self::BONUS_ORBO_GIOVE; // 4 + 2 = 6
                    $entroOrbo = true;
                }
            }

            $punteggio += $valore;
            $dettaglio[] = [
                'pianeta'    => RuleEngine::VAL_NOMI[$idPianeta] ?? (string)$idPianeta,
                'casa'       => $casaCond,
                'valore'     => $valore,
                'entro_orbo' => $entroOrbo,
            ];
        }

        // Regola 33: Saturno nella stessa casa della condizione prevale sempre
        // sui benefici presenti — la RSM/RL va ESCLUSA dai risultati (non solo
        // azzerata di punteggio). L'esclusione vera e propria avviene nel
        // chiamante (ricerca_stream_api.php), qui esponiamo solo il segnale.
        $saturnoPrevale = isset($pianeti[self::ID_SATURNO])
            && (int)$pianeti[self::ID_SATURNO]['casa'] === $casaCond;

        return [
            'supportata'      => true,
            'punteggio'       => $punteggio,
            'saturno_prevale' => $saturnoPrevale,
            'casa'            => $casaCond,
            'dettaglio'       => $dettaglio,
            'motivo_non_supportata' => null,
        ];
    }
}
