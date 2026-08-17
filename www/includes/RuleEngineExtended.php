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

        return [
            'supportata' => true,
            'punteggio'  => $punteggio,
            'casa'       => $casaCond,
            'dettaglio'  => $dettaglio,
            'motivo_non_supportata' => null,
        ];
    }
}
