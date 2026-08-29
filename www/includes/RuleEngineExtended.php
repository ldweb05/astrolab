<?php
require_once __DIR__ . '/AstroUtils.php';
require_once __DIR__ . '/RuleEngine.php';
require_once __DIR__ . '/RicercaRSFilters.php';

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

    // ── UX-0015: Regola 14 e gerarchia a 7 livelli per Decima ──────────────
    const PIANETI_LENTI_REGOLA14 = [
        6 => 'Saturno',
        7 => 'Urano',
        8 => 'Nettuno',
        9 => 'Plutone',
    ];
    const ORBO_REGOLA14 = 2.5; // gradi, per congiunzione/quadratura/opposizione

    // Livelli di priorita' per la condizione Decima (1 = migliore).
    const LIVELLO_ASC_OK         = 1;
    const LIVELLO_GIOVE_ORBO     = 2;
    const LIVELLO_GIOVE          = 3;
    const LIVELLO_VENERE         = 4;
    const LIVELLO_SOLE           = 5;
    const LIVELLO_ASC_DECLASSATO = 6;
    const LIVELLO_MALEFICO       = 7;  // solo malefico/i in X, nessun benefico/ASC
    const LIVELLO_NEUTRO         = 8;  // solo Luna/Mercurio in X, nessun altro segnale

    // Pianeti malefici e neutri (id secondo RuleEngine::VAL_NOMI) per la
    // logica di inclusione/esclusione Decima (UX-0015, revisione 2).
    const MALEFICI_DECIMA = [4, 6, 7, 8, 9]; // Marte, Saturno, Urano, Nettuno, Plutone
    const NEUTRI_DECIMA   = [1, 2];          // Luna, Mercurio

    // Livelli gerarchia Amore (UX-0016): niente ASC, priorita' Venere/Giove/Sole
    // su V/VII casa (pari peso), bonus orbo piu' stretto di Decima.
    const LIVELLO_VENERE_ORBO_AMORE = 1;
    const LIVELLO_VENERE_AMORE      = 2;
    const LIVELLO_GIOVE_ORBO_AMORE  = 3;
    const LIVELLO_GIOVE_AMORE       = 4;
    const LIVELLO_SOLE_AMORE        = 5;
    const LIVELLO_MALEFICO_AMORE    = 6;  // solo malefico/i in V/VII, nessun benefico
    const LIVELLO_NEUTRO_AMORE      = 7;  // solo Luna/Mercurio in V/VII, nessun altro segnale

    // Orbo bonus Amore (UX-0016): piu' stretto di ORBO_MAX_GRADI (Decima, 2,5°),
    // applicato solo a Venere e Giove, non al Sole.
    const ORBO_BONUS_AMORE = 1.5;

    // Pianeti malefici e neutri per la logica di inclusione/esclusione Amore
    // (UX-0016) - stessi id di RuleEngine::VAL_NOMI usati per Decima.
    const MALEFICI_AMORE = [4, 6, 7, 8, 9]; // Marte, Saturno, Urano, Nettuno, Plutone
    const NEUTRI_AMORE   = [1, 2];          // Luna, Mercurio

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

    /**
     * Duplicazione locale di RuleEngine::trovaCasaNatale() (privata, FREEZE).
     * Puro calcolo geometrico, nessuna regola di business: sicuro da duplicare
     * senza toccare il file frozen.
     */
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

    /**
     * Aspetto dissonante = congiunzione (0 gradi), quadratura (90) o
     * opposizione (180), entro l'orbo ORBO_REGOLA14. Ritorna il nome
     * dell'aspetto o null se nessuno rientra nell'orbo.
     */
    private function aspettoDissonante(float $diff): ?string {
        foreach (['congiunzione' => 0, 'quadratura' => 90, 'opposizione' => 180] as $nome => $angoloEsatto) {
            if (abs($diff - $angoloEsatto) <= self::ORBO_REGOLA14) {
                return $nome;
            }
        }
        return null;
    }

    /**
     * Regola 14 (34 regole ufficiali, Discepolo): ASC di RSM in X casa natale
     * indebolito da un pianeta lento (Saturno/Urano/Nettuno/Plutone, posizione
     * RSM) in aspetto dissonante a uno dei 4 punti natali (Sole, Luna, ASC, MC).
     *
     * @return array{scattata: bool, nota: string|null, dettaglio: array}
     */
    private function verificaRegola14(array $temaNatale, array $temaRS): array {
        $puntiNatali = [];
        if (isset($temaNatale['pianeti'][0]['longitudine'])) {
            $puntiNatali[] = ['nome' => 'Sole natale', 'lon' => (float)$temaNatale['pianeti'][0]['longitudine']];
        }
        if (isset($temaNatale['pianeti'][1]['longitudine'])) {
            $puntiNatali[] = ['nome' => 'Luna natale', 'lon' => (float)$temaNatale['pianeti'][1]['longitudine']];
        }
        if (isset($temaNatale['case'][1]['longitudine'])) {
            $puntiNatali[] = ['nome' => 'ASC natale', 'lon' => (float)$temaNatale['case'][1]['longitudine']];
        }
        if (isset($temaNatale['case'][10]['longitudine'])) {
            $puntiNatali[] = ['nome' => 'MC natale', 'lon' => (float)$temaNatale['case'][10]['longitudine']];
        }

        foreach (self::PIANETI_LENTI_REGOLA14 as $idPianeta => $nomePianeta) {
            if (!isset($temaRS['pianeti'][$idPianeta]['longitudine'])) continue;
            $lonPianetaRS = (float)$temaRS['pianeti'][$idPianeta]['longitudine'];

            foreach ($puntiNatali as $punto) {
                $diff = abs(AstroUtils::diffAngolo($lonPianetaRS, $punto['lon']));
                $aspetto = $this->aspettoDissonante($diff);
                if ($aspetto !== null) {
                    return [
                        'scattata' => true,
                        'nota' => "Regola 14: {$nomePianeta} (RSM) in {$aspetto} a {$punto['nome']} - ASC in Decima indebolito",
                        'dettaglio' => [
                            'pianeta'        => $nomePianeta,
                            'punto_natale'   => $punto['nome'],
                            'aspetto'        => $aspetto,
                            'distanza_gradi' => round($diff, 2),
                        ],
                    ];
                }
            }
        }

        return ['scattata' => false, 'nota' => null, 'dettaglio' => []];
    }

    /**
     * Gerarchia a 7 livelli per la condizione Decima (UX-0015), da usare per
     * l'ordinamento dei risultati al posto del sistema stelline, solo quando
     * MYASTRAL_ALIGNMENT_MODE e' attivo e la condizione e' Decima. Il sistema
     * stelline (V2) resta invariato e visibile nel risultato, usato come
     * tie-break a parita' di livello.
     *
     * @return array{livello:int, regola14_scattata:bool, nota:string|null, dettaglio_regola14:array}
     */
    public function calcolaLivelloDecima(array $temaNatale, array $temaRS): array {
        $base = ['regola14_scattata' => false, 'nota' => null, 'dettaglio_regola14' => [], 'escludi' => false];

        $ascRS = $temaRS['case'][1]['longitudine'] ?? null;

        if ($ascRS !== null) {
            $casaNataleAscRS = $this->trovaCasaNatale((float)$ascRS, $temaNatale['case']);

            if ($casaNataleAscRS === 10) {
                $regola14 = $this->verificaRegola14($temaNatale, $temaRS);

                return array_merge($base, [
                    'livello'            => $regola14['scattata'] ? self::LIVELLO_ASC_DECLASSATO : self::LIVELLO_ASC_OK,
                    'regola14_scattata'  => $regola14['scattata'],
                    'nota'               => $regola14['nota'],
                    'dettaglio_regola14' => $regola14['dettaglio'],
                ]);
            }
        }

        // ASC non in X casa natale: gerarchia Giove/Venere/Sole nella X casa
        // DELLA RS, usando gli stessi orbi di pre-ingresso (3°) e sicurezza in
        // uscita (2°, solo benefici) di sempre, ora centralizzati in
        // verificaCondizioneDecima() (RicercaRSFilters.php).
        $rilevamento = verificaCondizioneDecima($temaRS['pianeti'], $temaRS['case']);
        $pianetiInCasa = $rilevamento['pianeti_in_casa'];

        if (in_array(5, $pianetiInCasa, true)) {
            $entroOrbo = false;
            if (isset($temaRS['case'][10]['longitudine'], $temaRS['pianeti'][5]['longitudine'])) {
                $diff = abs(AstroUtils::diffAngolo(
                    (float)$temaRS['pianeti'][5]['longitudine'],
                    (float)$temaRS['case'][10]['longitudine']
                ));
                $entroOrbo = $diff <= self::ORBO_MAX_GRADI;
            }
            return array_merge($base, [
                'livello' => $entroOrbo ? self::LIVELLO_GIOVE_ORBO : self::LIVELLO_GIOVE,
            ]);
        }

        if (in_array(3, $pianetiInCasa, true)) {
            return array_merge($base, ['livello' => self::LIVELLO_VENERE]);
        }

        if (in_array(0, $pianetiInCasa, true)) {
            return array_merge($base, ['livello' => self::LIVELLO_SOLE]);
        }

        // Nessun ASC natale in X, nessun benefico in X casa RS: verifica
        // malefico (incluso comunque, segnalato dai veti) o neutro
        // (Luna/Mercurio, nessun altro segnale) prima di escludere del tutto.
        $haMalefico = !empty(array_intersect($pianetiInCasa, self::MALEFICI_DECIMA));
        $haNeutro   = !empty(array_intersect($pianetiInCasa, self::NEUTRI_DECIMA));

        if ($haMalefico) {
            return array_merge($base, ['livello' => self::LIVELLO_MALEFICO]);
        }
        if ($haNeutro) {
            return array_merge($base, ['livello' => self::LIVELLO_NEUTRO]);
        }

        // X casa RS completamente vuota (per gli orbi applicati) e ASC
        // natale non in X: nessun segnale utile per Decima. RSM esclusa
        // (UX-0015, revisione 2).
        return array_merge($base, ['livello' => self::LIVELLO_NEUTRO, 'escludi' => true]);
    }

    /**
     * Calcola il livello di priorita' per la condizione Amore (UX-0016).
     *
     * A differenza di Decima, non esiste un elemento ASC da verificare: la
     * gerarchia parte direttamente da Venere/Giove/Sole in V o VII casa RS
     * (pari peso tra le due case), con bonus orbo (1,5°, piu' stretto dei
     * 2,5° di Decima) applicato solo a Venere e Giove, non al Sole.
     *
     * @return array{livello:int, escludi:bool}
     */
    public function calcolaLivelloAmore(array $temaRS): array {
        $base = ['escludi' => false];

        $rilevamento = verificaCondizioneAmore($temaRS['pianeti'], $temaRS['case']);
        $pianetiInCasa = $rilevamento['pianeti_in_casa'];

        // Venere (id 3): priorita' massima
        if (in_array(3, $pianetiInCasa, true)) {
            $entroOrbo = $this->pianetaEntroOrboAmore(3, $temaRS);
            return array_merge($base, [
                'livello' => $entroOrbo ? self::LIVELLO_VENERE_ORBO_AMORE : self::LIVELLO_VENERE_AMORE,
            ]);
        }

        // Giove (id 5): seconda priorita'
        if (in_array(5, $pianetiInCasa, true)) {
            $entroOrbo = $this->pianetaEntroOrboAmore(5, $temaRS);
            return array_merge($base, [
                'livello' => $entroOrbo ? self::LIVELLO_GIOVE_ORBO_AMORE : self::LIVELLO_GIOVE_AMORE,
            ]);
        }

        // Sole (id 0): terza priorita', nessun bonus orbo
        if (in_array(0, $pianetiInCasa, true)) {
            return array_merge($base, ['livello' => self::LIVELLO_SOLE_AMORE]);
        }

        // UX-0017: nessun benefico in V/VII significa che la condizione
        // Amore NON e' soddisfatta, a prescindere da cosa altro sia presente
        // (malefico, neutro, o nulla). La RSM va SEMPRE esclusa - non piu'
        // inclusa come "malefico" o "neutro" (livelli rimossi da UX-0016).
        // Il malefico continua a essere segnalato SOLO quando accompagna un
        // benefico effettivo (gestito nei rami Venere/Giove/Sole sopra,
        // tramite i veti esistenti a monte - comportamento invariato).
        return array_merge($base, ['livello' => self::LIVELLO_NEUTRO_AMORE, 'escludi' => true]);
    }

    /**
     * Verifica se un pianeta (Venere o Giove) e' entro il bonus orbo Amore
     * (1,5°) dalla cuspide di V o VII casa RS, qualunque delle due lo ospiti.
     */
    private function pianetaEntroOrboAmore(int $idPianeta, array $temaRS): bool {
        foreach ([5, 7] as $casaTarget) {
            if (!isset($temaRS['case'][$casaTarget]['longitudine'], $temaRS['pianeti'][$idPianeta]['longitudine'])) {
                continue;
            }
            $diff = abs(AstroUtils::diffAngolo(
                (float)$temaRS['pianeti'][$idPianeta]['longitudine'],
                (float)$temaRS['case'][$casaTarget]['longitudine']
            ));
            if ($diff <= self::ORBO_BONUS_AMORE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Stringa VAL dedicata alla condizione Decima (UX-0015): mostra ASC (se
     * l'Ascendente della RS cade nella X casa natale) e ogni pianeta la cui
     * posizione nella RS cade nella X casa della RS stessa. Sostituisce, SOLO
     * per Decima, la stringa VAL generica di RuleEngine::generaVAL() — non la
     * modifica, non la duplica per le altre condizioni.
     */
    public function generaValDecima(array $temaNatale, array $temaRS): string {
        $parti = [];

        $ascRS = $temaRS['case'][1]['longitudine'] ?? null;
        if ($ascRS !== null && $this->trovaCasaNatale((float)$ascRS, $temaNatale['case']) === 10) {
            $parti[] = 'ASC';
        }

        $rilevamento = verificaCondizioneDecima($temaRS['pianeti'], $temaRS['case']);
        foreach ($rilevamento['pianeti_in_casa'] as $idPianeta) {
            $parti[] = RuleEngine::VAL_NOMI[$idPianeta] ?? (string)$idPianeta;
        }

        return empty($parti) ? '—' : implode('+', $parti);
    }

    /**
     * Stringa VAL dedicata alla condizione Amore (UX-0016): mostra ogni
     * pianeta la cui posizione nella RS cade in V o VII casa della RS
     * stessa. Nessun elemento ASC (a differenza di Decima): la funzione
     * forte dell'Ascendente come primo livello resta specifica della sola
     * Decima. Sostituisce, SOLO per Amore, la stringa VAL generica di
     * RuleEngine::generaVAL() — non la modifica, non la duplica per le
     * altre condizioni.
     */
    public function generaValAmore(array $temaRS): string {
        $parti = [];

        $rilevamento = verificaCondizioneAmore($temaRS['pianeti'], $temaRS['case']);
        foreach ($rilevamento['pianeti_in_casa'] as $idPianeta) {
            $parti[] = RuleEngine::VAL_NOMI[$idPianeta] ?? (string)$idPianeta;
        }

        return empty($parti) ? '—' : implode('+', $parti);
    }
}
