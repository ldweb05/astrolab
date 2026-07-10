<?php
/**
 * FiltroEsclusione.php — Filtro di visualizzazione aggiuntivo per le Rivoluzioni Solari
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * IMPORTANTE: questo filtro NON modifica il punteggio stelline del RuleEngine,
 * NON è una delle 34 regole di Discepolo, e NON si applica alle Rivoluzioni
 * Lunari (RL). È un livello di visualizzazione/esclusione aggiuntivo richiesto
 * dall'astrologo, indipendente dalla valutazione RuleEngine::valuta().
 *
 * Una RS viene esclusa dalla visualizzazione se presenta ALMENO UNA delle
 * seguenti configurazioni:
 *
 *   1. Sole RS    in I, VI o XII casa della RS
 *   2. Marte RS   in I, VI o XII casa della RS
 *   3. ASC RS     in I, VI o XII casa del TEMA NATALE
 *   4. Saturno RS in X casa della RS
 *   5. Stellium (3+ pianeti tra i 10 classici SO-PLU) in una qualsiasi
 *      delle 12 case della RS
 *
 * Uso:
 *   require_once '../includes/FiltroEsclusione.php';
 *   $motivi = verificaEsclusioneRS($pianetiRS, $caseRS, $temaNatale);
 *   if (!empty($motivi)) { ... escludi oppure segnala ... }
 */

const FILTRO_ESCLUSIONE_PIANETI_CLASSICI = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]; // SO-PLU (esclude Nodo Lunare)

const FILTRO_ESCLUSIONE_NOMI_VAL = [
    0 => 'Sole', 1 => 'Luna', 2 => 'Mercurio', 3 => 'Venere', 4 => 'Marte',
    5 => 'Giove', 6 => 'Saturno', 7 => 'Urano', 8 => 'Nettuno', 9 => 'Plutone',
    11 => 'Nodo N.',
];

const FILTRO_ESCLUSIONE_ROMANI = [
    1=>'I', 2=>'II', 3=>'III', 4=>'IV', 5=>'V', 6=>'VI',
    7=>'VII', 8=>'VIII', 9=>'IX', 10=>'X', 11=>'XI', 12=>'XII',
];

/**
 * Trova la casa natale (1-12) in cui cade una data longitudine,
 * date le case del tema natale. Stessa logica di
 * RuleEngine::trovaCasaNatale() / sensibilita_api.php::_trovaCasaNatale().
 */
function _filtroEsclusioneTrovaCasaNatale(float $lon, array $caseNatale): int {
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
 * Verifica se una RS rientra in una delle 5 configurazioni escluse.
 *
 * @param array $pianetiRS  Array pianeti della RS (con 'casa' già assegnata)
 * @param array $caseRS     Array case della RS (con 'ASC', 'MC', 1..12)
 * @param array $temaNatale Tema natale completo (con 'case')
 * @return array Array di stringhe descrittive dei motivi di esclusione.
 *               Vuoto = RS ammessa, nessuna esclusione.
 */
function verificaEsclusioneRS(array $pianetiRS, array $caseRS, array $temaNatale): array {
    $motivi = [];

    // ── 1. Sole RS in I, VI o XII casa della RS ────────────────────────────
    if (isset($pianetiRS[0]) && in_array($pianetiRS[0]['casa'], [1, 6, 12], true)) {
        $casa = $pianetiRS[0]['casa'];
        $motivi[] = "Sole in " . FILTRO_ESCLUSIONE_ROMANI[$casa] . " casa della RS";
    }

    // ── 2. Marte RS in I, VI o XII casa della RS ───────────────────────────
    if (isset($pianetiRS[4]) && in_array($pianetiRS[4]['casa'], [1, 6, 12], true)) {
        $casa = $pianetiRS[4]['casa'];
        $motivi[] = "Marte in " . FILTRO_ESCLUSIONE_ROMANI[$casa] . " casa della RS";
    }

    // ── 3. ASC RS in I, VI o XII casa del tema natale ──────────────────────
    $ascRsLon = $caseRS['ASC']['longitudine'] ?? null;
    if ($ascRsLon !== null && isset($temaNatale['case'])) {
        $casaNataleAsc = _filtroEsclusioneTrovaCasaNatale((float)$ascRsLon, $temaNatale['case']);
        if (in_array($casaNataleAsc, [1, 6, 12], true)) {
            $motivi[] = "Ascendente della RS in " . FILTRO_ESCLUSIONE_ROMANI[$casaNataleAsc] . " casa del tema natale";
        }
    }

    // ── 4. Saturno RS in X casa della RS ───────────────────────────────────
    if (isset($pianetiRS[6]) && $pianetiRS[6]['casa'] === 10) {
        $motivi[] = "Saturno in X casa della RS";
    }

    // ── 5. Stellium (3+ pianeti classici SO-PLU) in una qualsiasi casa ─────
    $conteggioCase = [];
    foreach ($pianetiRS as $id => $p) {
        if (!in_array($id, FILTRO_ESCLUSIONE_PIANETI_CLASSICI, true)) continue;
        $casa = $p['casa'];
        if ($casa < 1 || $casa > 12) continue;
        $conteggioCase[$casa][] = $id;
    }
    foreach ($conteggioCase as $casa => $ids) {
        if (count($ids) >= 3) {
            $nomi = implode(', ', array_map(
                fn($id) => FILTRO_ESCLUSIONE_NOMI_VAL[$id] ?? '?',
                $ids
            ));
            $motivi[] = "Stellium (" . $nomi . ") in " . FILTRO_ESCLUSIONE_ROMANI[$casa] . " casa della RS";
        }
    }

    return $motivi;
}
