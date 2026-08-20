<?php
declare(strict_types=1);

require_once __DIR__ . '/AstroUtils.php';

function diffAngolo(float $a, float $b): float {
    return AstroUtils::diffAngolo($a, $b);
}

function getNomePianeta(int $id): string {
    static $NOMI = [
        0=>'SO', 1=>'LU', 2=>'ME', 3=>'VE', 4=>'MA',
        5=>'GI', 6=>'SA', 7=>'UR', 8=>'NE', 9=>'PLU',
        11=>'NO',
    ];
    return $NOMI[$id] ?? 'P' . $id;
}

/** @return array{malevoli:int[], case:int[]} */
function getRuleMapEsclusione(string $condizione): array {
    // Tutti e cinque i malevoli (MA, SA, UR, NE, PLU)
    $tutti  = [4, 6, 7, 8, 9];
    // Solo i tre principali (MA, SA, UR) — per Denaro Low
    $forti  = [4, 6, 7];

    return match($condizione) {
        // Carriera, promozioni, status → X casa è sacra
        'Decima'     => ['malevoli' => $tutti,  'case' => [10]],

        // Lavoro quotidiano + avanzamento → VI e X
        'Lavoro'     => ['malevoli' => $tutti,  'case' => [6, 10]],

        // Amore → gestito separatamente da verificaCondizioneAmore()
        // Non applichiamo la Rule Map standard per non fare doppio controllo
        'Amore'      => ['malevoli' => [],     'case' => []],

        // Salute → VI (malattie acute) e XII (ospedalizzazione/crisi)
        // NOTA: La logica estesa con tolleranza 4° e controllo Sole in XII
        // è gestita separatamente in verificaCondizioneSalute()
        'Salute'     => ['malevoli' => $tutti,  'case' => [6, 12]],

        // Entrate/uscite, eredità, investimenti → II e VIII
        'Denaro'     => ['malevoli' => $tutti,  'case' => [2, 8, 10]],

        // Piccole entrate costanti → solo II; esclusi solo i tre principali
        'Denaro Low' => ['malevoli' => $forti,  'case' => [2, 8]],

        // Casa → gestito separatamente da verificaCondizioneCasa()
        // per includere le tolleranze di pre-ingresso e sicurezza uscita
        'Casa'       => ['malevoli' => [],     'case' => []],

        // Condizione sconosciuta: nessuna esclusione radicale
        default      => ['malevoli' => [],       'case' => []],
    };
}

function escludiPerRuleMap(array $pianetiConCase, string $condizione): bool {
    $map = getRuleMapEsclusione($condizione);

    if (empty($map['malevoli']) || empty($map['case'])) {
        return false;
    }

    foreach ($map['malevoli'] as $idMalevolo) {
        if (!isset($pianetiConCase[$idMalevolo])) {
            continue;
        }
        $casaPianeta = (int)$pianetiConCase[$idMalevolo]['casa'];
        if (in_array($casaPianeta, $map['case'], true)) {
            return true; // trovato → escludi immediatamente
        }
    }

    return false;
}

function verificaAstriInCasaDirectly(array $pianetiConCase, array $astriInCasa): array
{
    if (empty($astriInCasa)) {
        return [];
    }

    static $NOMI = [
        0=>'SO', 1=>'LU', 2=>'ME', 3=>'VE', 4=>'MA',
        5=>'GI', 6=>'SA', 7=>'UR', 8=>'NE', 9=>'PLU',
        11=>'NO',
    ];

    $violazioni = [];

    foreach ($astriInCasa as $filtro) {
        $pid   = $filtro['pianeta'];
        $casaV = (int)$filtro['casa'];
        $vuole = (bool)$filtro['vuole'];

        // ASC è sempre in casa 1 della RS per definizione di Placido.
        // Non è un pianeta indicizzato in $pianetiConCase → saltiamo.
        if ($pid === 'ASC' || $pid === -1) {
            continue;
        }

        $idP = (int)$pid;

        if (!isset($pianetiConCase[$idP])) {
            if ($vuole) {
                $violazioni[] = ($NOMI[$idP] ?? 'P'.$idP) . ' non trovato nel tema RS';
            }
            continue;
        }

        $casaEff = (int)$pianetiConCase[$idP]['casa'];
        $nome    = $NOMI[$idP] ?? 'P'.$idP;

        if ($vuole && $casaEff !== $casaV) {
            $violazioni[] = $nome . ' è in casa ' . $casaEff . ' (richiesta casa ' . $casaV . ')';
        } elseif (!$vuole && $casaEff === $casaV) {
            $violazioni[] = $nome . ' è in casa ' . $casaV . ' (indesiderato)';
        }
    }

    return $violazioni;
}
// ═══════════════════════════════════════════════════════════════════════════
//  VERIFICA SPECIFICA PER LA CONDIZIONE "AMORE"
//  Regole della scuola di Ciro Discepolo per le relazioni
// ═══════════════════════════════════════════════════════════════════════════


/**
 * Verifica che la condizione "Decima" sia soddisfatta secondo i criteri
 * della scuola di Ciro Discepolo.
 *
 * REGOLE:
 * 1. Almeno uno tra Sole (0), Giove (5) o Venere (3) di RS deve trovarsi
 *    in X casa RS (con pre-ingresso di 3°).
 *
 * 2. Sicurezza in uscita: se un benefico è a meno di 2° dalla cuspide
 *    della casa successiva (XI), la località NON è valida.
 *
 * 3. Filtro di esclusione: se MA/SA/UR/NE/PLU è in X casa RS
 *    (con pre-ingresso di 3°), la località DEVE essere scartata.
 *
 * @param array<int,array{casa:int,longitudine:float}> $pianetiConCase
 * @param array<int,array{longitudine:float}> $caseRS
 * @return array{valida:bool, motivo?:string}
 */
function verificaCondizioneDecima(array $pianetiConCase, array $caseRS): array
{
    // Casa target: X (Medio Cielo)
    $casaTarget = 10;

    // Benefici da verificare: Sole (0), Giove (5), Venere (3)
    $benefici = [0, 5, 3];

    // Malevoli da escludere: Marte (4), Saturno (6), Urano (7), Nettuno (8), Plutone (9)
    $malevoli = [4, 6, 7, 8, 9];

    // Verifica che la casa target esista
    if (!isset($caseRS[$casaTarget])) {
        return [
            'valida' => false,
            'motivo' => 'Casa X non trovata nel tema RS'
        ];
    }

    $cuspideTarget = $caseRS[$casaTarget]['longitudine'];

    // Determina la casa successiva (XI) per il vincolo di uscita
    $casaSuccessiva = 11;
    $cuspideSuccessiva = isset($caseRS[$casaSuccessiva])
        ? $caseRS[$casaSuccessiva]['longitudine']
        : null;

    $beneficiTrovati = [];
    $malevoliTrovati = [];

    // Controlla tutti i pianeti
    foreach ($pianetiConCase as $idPianeta => $dati) {
        $casaAssegnata = (int)$dati['casa'];
        $longitudine = (float)$dati['longitudine'];

        // Pre-ingresso: il pianeta è nei 3° immediatamente precedenti la cuspide della X?
        $diffCuspide = diffAngolo($longitudine, $cuspideTarget);
        $inPreIngresso = ($diffCuspide > -3.0 && $diffCuspide < 0.0);

        // Il pianeta è nella casa target (assegnata da SweCalc) o in pre-ingresso?
        $inCasaTarget = ($casaAssegnata === $casaTarget) || $inPreIngresso;

        if (!$inCasaTarget) {
            continue;
        }

        // === VINCOLO DI SICUREZZA IN USCITA (SOLO PER BENEFICI) ===
        // Se il pianeta benefico è a meno di 2° dalla cuspide della XI casa
        // (cioè ha appena lasciato la X), la località è scartata.
        if ($cuspideSuccessiva !== null && in_array($idPianeta, $benefici, true)) {
            $diffUscita = diffAngolo($longitudine, $cuspideSuccessiva);
            // diffUscita ∈ [0°, 2°) → pianeta appena entrato nella XI casa
            if ($diffUscita >= 0.0 && $diffUscita < 2.0) {
                $nomeBenef = getNomePianeta($idPianeta);
                return [
                    'valida' => false,
                    'motivo' => "Sicurezza in uscita: {$nomeBenef} a " .
                                round($diffUscita, 1) . "° dalla cuspide della XI casa — " .
                                "troppo vicino all'uscita dalla X casa, protezione carriera non coperta"
                ];
            }
        }

        // Classifica il pianeta come benefico o malevolo
        if (in_array($idPianeta, $benefici, true)) {
            $beneficiTrovati[] = $idPianeta;
        } elseif (in_array($idPianeta, $malevoli, true)) {
            $malevoliTrovati[] = $idPianeta;
        }
    }

    // === FILTRO DI ESCLUSIONE: malevoli in X casa ===
    // Anche se i benefici sono presenti, se c'è un malevolo in X casa
    // (con pre-ingresso) la località deve essere scartata.
    if (!empty($malevoliTrovati)) {
        $nomiMalevoli = array_map('getNomePianeta', array_unique($malevoliTrovati));
        return [
            'valida' => false,
            'motivo' => 'Malevoli in X casa RS: ' . implode(', ', $nomiMalevoli) .
                        ' — danni a carriera/status garantiti'
        ];
    }

    // === VERIFICA PRESENZA BENEFICI ===
    if (empty($beneficiTrovati)) {
        return [
            'valida' => false,
            'motivo' => 'Nessun benefico (Sole, Giove o Venere) in X casa RS'
        ];
    }

    // Tutti i controlli superati
    return ['valida' => true];
}

/**
 * Verifica che la condizione "Amore" sia soddisfatta secondo le regole
 * della scuola di Ciro Discepolo.
 *
 * REGOLE:
 * 1. Almeno uno tra Venere (3), Giove (5) o Sole (0) di RS deve trovarsi
 *    in V o VII casa RS (con pre-ingresso di 3°).
 *
 * 2. Sicurezza in uscita: se un benefico è a meno di 2° dalla cuspide
 *    della casa successiva (VI se in V, VIII se in VII), la località
 *    NON è valida.
 *
 * 3. Filtro di esclusione: se MA/SA/UR/NE/PLU è in V o VII RS
 *    (con pre-ingresso di 3°), la località DEVE essere scartata.
 *
 * @param array<int,array{casa:int,longitudine:float}> $pianetiConCase
 * @param array<int,array{longitudine:float}> $caseRS
 * @return array{valida:bool, motivo?:string}  valida=true se passa il filtro
 */

function verificaCondizioneAmore(array $pianetiConCase, array $caseRS): array
{
    // Case target per l'amore: V e VII
    $caseTarget = [5, 7];

    // Benefici da verificare: Venere (3), Giove (5), Sole (0)
    $benefici = [3, 5, 0];

    // Malevoli da escludere: Marte (4), Saturno (6), Urano (7), Nettuno (8), Plutone (9)
    $malevoli = [4, 6, 7, 8, 9];

    // Per ogni casa target, controlliamo la presenza di benefici e malevoli
    $beneficiTrovati = [];
    $malevoliTrovati = [];

    foreach ($caseTarget as $casaTarget) {
        if (!isset($caseRS[$casaTarget])) {
            continue;
        }

        $cuspideTarget = $caseRS[$casaTarget]['longitudine'];

        // Determina la casa successiva per il vincolo di uscita
        $casaSuccessiva = ($casaTarget === 5) ? 6 : 8;
        $cuspideSuccessiva = isset($caseRS[$casaSuccessiva])
            ? $caseRS[$casaSuccessiva]['longitudine']
            : null;

        // Controlla tutti i pianeti
        foreach ($pianetiConCase as $idPianeta => $dati) {
            $casaAssegnata = (int)$dati['casa'];
            $longitudine = (float)$dati['longitudine'];

            // Pre-ingresso: il pianeta è nei 3° immediatamente precedenti la cuspide?
            $diffCuspide = diffAngolo($longitudine, $cuspideTarget);
            $inPreIngresso = ($diffCuspide > -3.0 && $diffCuspide < 0.0);

            // Il pianeta è nella casa target (assegnata da SweCalc) o in pre-ingresso?
            $inCasaTarget = ($casaAssegnata === $casaTarget) || $inPreIngresso;

            if (!$inCasaTarget) {
                continue;
            }

            // === VINCOLO DI SICUREZZA IN USCITA ===
            // Se il pianeta benefico è a meno di 2° dalla cuspide della casa successiva
            if ($cuspideSuccessiva !== null && in_array($idPianeta, $benefici, true)) {
                $diffUscita = diffAngolo($longitudine, $cuspideSuccessiva);
                // diffUscita ∈ [0°, 2°) → pianeta appena entrato nella casa successiva
                if ($diffUscita >= 0.0 && $diffUscita < 2.0) {
                    $nomeBenef = getNomePianeta($idPianeta);
                    return [
                        'valida' => false,
                        'motivo' => "Sicurezza in uscita: {$nomeBenef} a " .
                                    round($diffUscita, 1) . "° dalla cuspide della " .
                                    $casaSuccessiva . "a casa — troppo vicino all'uscita dalla " .
                                    $casaTarget . "a casa"
                    ];
                }
            }

            // Classifica il pianeta come benefico o malevolo
            if (in_array($idPianeta, $benefici, true)) {
                $beneficiTrovati[] = $idPianeta;
            } elseif (in_array($idPianeta, $malevoli, true)) {
                $malevoliTrovati[] = $idPianeta;
            }
        }
    }

    // === FILTRO DI ESCLUSIONE: malevoli in V o VII ===
    if (!empty($malevoliTrovati)) {
        $nomiMalevoli = array_map('getNomePianeta', array_unique($malevoliTrovati));
        return [
            'valida' => false,
            'motivo' => 'Malevoli in V/VII casa RS: ' . implode(', ', $nomiMalevoli)
        ];
    }

    // === VERIFICA PRESENZA BENEFICI ===
    if (empty($beneficiTrovati)) {
        return [
            'valida' => false,
            'motivo' => 'Nessun benefico (Venere, Giove o Sole) in V o VII casa RS'
        ];
    }

    // Tutti i controlli superati
    return ['valida' => true];
}


/**
 * Verifica che la condizione "Lavoro" sia soddisfatta.
 *
 * Case target: VI e X (gia' definite nella Rule Map di
 * getRuleMapEsclusione() per l'esclusione dei malevoli).
 * Coerente con la Regola 33 ("discorso lavoro/emancipazione/successo/
 * prestigio" legato al Medio Cielo).
 *
 * Stessa struttura di verificaCondizioneAmore() (l'unico altro caso con
 * due case target contemporanee): benefici Sole/Giove/Venere, malevoli
 * Marte/Saturno/Urano/Nettuno/Plutone, pre-ingresso 3°, sicurezza-uscita 2°.
 *
 * @param array<int,array{casa:int,longitudine:float}> $pianetiConCase
 * @param array<int,array{longitudine:float}> $caseRS
 * @return array{valida:bool, motivo?:string}
 */
function verificaCondizioneLavoro(array $pianetiConCase, array $caseRS): array
{
    // Case target per il lavoro: VI e X
    $caseTarget = [6, 10];

    // Benefici da verificare: Sole (0), Giove (5), Venere (3)
    $benefici = [0, 5, 3];

    // Malevoli da escludere: Marte (4), Saturno (6), Urano (7), Nettuno (8), Plutone (9)
    $malevoli = [4, 6, 7, 8, 9];

    // Per ogni casa target, controlliamo la presenza di benefici e malevoli
    $beneficiTrovati = [];
    $malevoliTrovati = [];

    foreach ($caseTarget as $casaTarget) {
        if (!isset($caseRS[$casaTarget])) {
            continue;
        }

        $cuspideTarget = $caseRS[$casaTarget]['longitudine'];

        // Determina la casa successiva per il vincolo di uscita
        $casaSuccessiva = ($casaTarget === 6) ? 7 : 11;
        $cuspideSuccessiva = isset($caseRS[$casaSuccessiva])
            ? $caseRS[$casaSuccessiva]['longitudine']
            : null;

        // Controlla tutti i pianeti
        foreach ($pianetiConCase as $idPianeta => $dati) {
            $casaAssegnata = (int)$dati['casa'];
            $longitudine = (float)$dati['longitudine'];

            // Pre-ingresso: il pianeta è nei 3° immediatamente precedenti la cuspide?
            $diffCuspide = diffAngolo($longitudine, $cuspideTarget);
            $inPreIngresso = ($diffCuspide > -3.0 && $diffCuspide < 0.0);

            // Il pianeta è nella casa target (assegnata da SweCalc) o in pre-ingresso?
            $inCasaTarget = ($casaAssegnata === $casaTarget) || $inPreIngresso;

            if (!$inCasaTarget) {
                continue;
            }

            // === VINCOLO DI SICUREZZA IN USCITA ===
            // Se il pianeta benefico è a meno di 2° dalla cuspide della casa successiva
            if ($cuspideSuccessiva !== null && in_array($idPianeta, $benefici, true)) {
                $diffUscita = diffAngolo($longitudine, $cuspideSuccessiva);
                // diffUscita ∈ [0°, 2°) → pianeta appena entrato nella casa successiva
                if ($diffUscita >= 0.0 && $diffUscita < 2.0) {
                    $nomeBenef = getNomePianeta($idPianeta);
                    return [
                        'valida' => false,
                        'motivo' => "Sicurezza in uscita: {$nomeBenef} a " .
                                    round($diffUscita, 1) . "° dalla cuspide della " .
                                    $casaSuccessiva . "a casa — troppo vicino all'uscita dalla " .
                                    $casaTarget . "a casa"
                    ];
                }
            }

            // Classifica il pianeta come benefico o malevolo
            if (in_array($idPianeta, $benefici, true)) {
                $beneficiTrovati[] = $idPianeta;
            } elseif (in_array($idPianeta, $malevoli, true)) {
                $malevoliTrovati[] = $idPianeta;
            }
        }
    }

    // === FILTRO DI ESCLUSIONE: malevoli in VI o X ===
    if (!empty($malevoliTrovati)) {
        $nomiMalevoli = array_map('getNomePianeta', array_unique($malevoliTrovati));
        return [
            'valida' => false,
            'motivo' => 'Malevoli in VI/X casa RS: ' . implode(', ', $nomiMalevoli)
        ];
    }

    // === VERIFICA PRESENZA BENEFICI ===
    if (empty($beneficiTrovati)) {
        return [
            'valida' => false,
            'motivo' => 'Nessun benefico (Sole, Giove o Venere) in VI o X casa RS'
        ];
    }

    // Tutti i controlli superati
    return ['valida' => true];
}


function verificaCondizioneCasa(array $pianetiConCase, array $caseRS): array
{
    // Casa target: IV (Fondo Cielo)
    $casaTarget = 4;

    // Benefici da verificare: Sole (0), Giove (5), Venere (3)
    $benefici = [0, 5, 3];

    // Malevoli da escludere: Marte (4), Saturno (6), Urano (7), Nettuno (8), Plutone (9)
    $malevoli = [4, 6, 7, 8, 9];

    // Verifica che la casa target esista
    if (!isset($caseRS[$casaTarget])) {
        return [
            'valida' => false,
            'motivo' => 'Casa IV non trovata nel tema RS'
        ];
    }

    $cuspideTarget = $caseRS[$casaTarget]['longitudine'];

    // Determina la casa successiva (V) per il vincolo di uscita
    $casaSuccessiva = 5;
    $cuspideSuccessiva = isset($caseRS[$casaSuccessiva])
        ? $caseRS[$casaSuccessiva]['longitudine']
        : null;

    $beneficiTrovati = [];
    $malevoliTrovati = [];

    // Controlla tutti i pianeti
    foreach ($pianetiConCase as $idPianeta => $dati) {
        $casaAssegnata = (int)$dati['casa'];
        $longitudine = (float)$dati['longitudine'];

        // Pre-ingresso: il pianeta è nei 3° immediatamente precedenti la cuspide della IV?
        $diffCuspide = diffAngolo($longitudine, $cuspideTarget);
        $inPreIngresso = ($diffCuspide > -3.0 && $diffCuspide < 0.0);

        // Il pianeta è nella casa target (assegnata da SweCalc) o in pre-ingresso?
        $inCasaTarget = ($casaAssegnata === $casaTarget) || $inPreIngresso;

        if (!$inCasaTarget) {
            continue;
        }

        // === VINCOLO DI SICUREZZA IN USCITA (SOLO PER BENEFICI) ===
        // Se il pianeta benefico è a meno di 2° dalla cuspide della V casa
        // (cioè ha appena lasciato la IV), la località è scartata.
        if ($cuspideSuccessiva !== null && in_array($idPianeta, $benefici, true)) {
            $diffUscita = diffAngolo($longitudine, $cuspideSuccessiva);
            // diffUscita ∈ [0°, 2°) → pianeta appena entrato nella V casa
            if ($diffUscita >= 0.0 && $diffUscita < 2.0) {
                $nomeBenef = getNomePianeta($idPianeta);
                return [
                    'valida' => false,
                    'motivo' => "Sicurezza in uscita: {$nomeBenef} a " .
                                round($diffUscita, 1) . "° dalla cuspide della V casa — " .
                                "troppo vicino all'uscita dalla IV casa, protezione immobiliare non coperta"
                ];
            }
        }

        // Classifica il pianeta come benefico o malevolo
        if (in_array($idPianeta, $benefici, true)) {
            $beneficiTrovati[] = $idPianeta;
        } elseif (in_array($idPianeta, $malevoli, true)) {
            $malevoliTrovati[] = $idPianeta;
        }
    }

    // === FILTRO DI ESCLUSIONE: malevoli in IV casa ===
    // Anche se i benefici sono presenti, se c'è un malevolo in IV casa
    // (con pre-ingresso) la località deve essere scartata.
    if (!empty($malevoliTrovati)) {
        $nomiMalevoli = array_map('getNomePianeta', array_unique($malevoliTrovati));
        return [
            'valida' => false,
            'motivo' => 'Malevoli in IV casa RS: ' . implode(', ', $nomiMalevoli) .
                        ' — danni immobiliari/familiari garantiti'
        ];
    }

    // === VERIFICA PRESENZA BENEFICI ===
    if (empty($beneficiTrovati)) {
        return [
            'valida' => false,
            'motivo' => 'Nessun benefico (Sole, Giove o Venere) in IV casa RS'
        ];
    }

    // Tutti i controlli superati
    return ['valida' => true];
}


// ═══════════════════════════════════════════════════════════════════════════
//  VERIFICA SPECIFICA PER LA CONDIZIONE "SALUTE"
//  Regole della scuola di Ciro Discepolo per la salute - PROTEZIONE MASSIMA
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Verifica che la condizione "Salute" sia soddisfatta secondo i criteri
 * di protezione massima della scuola di Ciro Discepolo.
 *
 * REGOLE:
 * 1. TOLLERANZA PRE-INGRESSO AMPLIATA A 4° per i malefici in I/VI/XII:
 *    MA/SA/UR/NE/PLU entro 4° prima della cuspide di I/VI/XII → FAIL ASSOLUTO.
 *
 * 2. SCUDO BENEFICO IN I CASA (priorità massima):
 *    Giove o Venere in I casa RS (con pre-ingresso 3°) → marcatura come
 *    "protetta" a condizione che non siano a meno di 3° dalla cuspide della II.
 *
 * 3. ESCLUSIONE SOLE IN XII:
 *    Sole in XII casa RS (incluso pre-ingresso 3°) → FAIL ASSOLUTO
 *    (XII casa spegne l'energia vitale del Sole).
 *
 * 4. RAFFORZAMENTO ASCENDENTE NATALE:
 *    ASC RS non deve toccare le cuspidi delle case natali I/VI/XII
 *    con tolleranza rigida di 3°.
 *
 * 5. PROTEZIONE UNIVERSALE:
 *    Almeno uno tra Giove e Venere deve proteggere I, VI o XII casa RS
 *    (con pre-ingresso 3°).
 *
 * @param array<int,array{casa:int,longitudine:float}> $pianetiConCase
 * @param array<int,array{longitudine:float}> $caseRS
 * @param array<int,array{longitudine:float}> $caseNatale
 * @param float $latA Latitudine dell'aeroporto (per il contesto)
 * @return array{valida:bool, motivo?:string, scudo_benefico?:bool, benefico_in_i?:array}
 */
function verificaCondizioneSalute(
    array $pianetiConCase,
    array $caseRS,
    array $caseNatale,
    float $latA
): array {
    
    // Nomi pianeti per messaggi di errore
    static $NOMI = [
        0=>'SO', 1=>'LU', 2=>'ME', 3=>'VE', 4=>'MA',
        5=>'GI', 6=>'SA', 7=>'UR', 8=>'NE', 9=>'PLU',
        11=>'NO'
    ];
    
    // ================================================================
    // PASSO 1 (numerazione locale, non e' la Regola 1 ufficiale): TOLLERANZA PRE-INGRESSO AMPLIATA A 4° PER MALEFICI
    // ================================================================
    $malevoli = [4, 6, 7, 8, 9]; // MA, SA, UR, NE, PLU
    $caseVetoSalute = [1, 6, 12];
    $tolleranzaPreIngressoSalute = 4.0; // +1° rispetto allo standard
    
    // Controllo malefici in I/VI/XII con tolleranza 4°
    foreach ($caseVetoSalute as $casaVeto) {
        if (!isset($caseRS[$casaVeto])) continue;
        $cuspideVeto = $caseRS[$casaVeto]['longitudine'];
        
        foreach ($malevoli as $idMal) {
            if (!isset($pianetiConCase[$idMal])) continue;
            $lonMal = $pianetiConCase[$idMal]['longitudine'];
            $casaAssegnata = (int)$pianetiConCase[$idMal]['casa'];
            
            // Se il pianeta è già nella casa veto, è un fail
            if ($casaAssegnata === $casaVeto) {
                return [
                    'valida' => false,
                    'motivo' => $NOMI[$idMal] . ' in ' . $casaVeto . 'a casa RS — fail assoluto (tolleranza 4°)'
                ];
            }
            
            // Pre-ingresso ampliato a 4°
            $diff = diffAngolo($lonMal, $cuspideVeto);
            if ($diff > -$tolleranzaPreIngressoSalute && $diff < 0.0) {
                return [
                    'valida' => false,
                    'motivo' => $NOMI[$idMal] . ' a ' . round(abs($diff), 1) . 
                                '° dalla ' . $casaVeto . 'a casa RS (pre-ingresso 4°) — fail assoluto'
                ];
            }
        }
    }
    
    // ================================================================
    // PASSO 2 (numerazione locale, non e' la Regola 2 ufficiale): SCUDO BENEFICO IN I CASA (priorità massima)
    // ================================================================
    $beneficiScudo = [3, 5]; // Venere, Giove
    $casaScudo = 1;
    $tolleranzaPreIngressoScudo = 3.0;
    $tolleranzaUscitaScudo = 3.0; // 3° dalla cuspide della II
    
    $beneficoInI = null;
    
    if (isset($caseRS[$casaScudo])) {
        $cuspideI = $caseRS[$casaScudo]['longitudine'];
        $casaSuccessiva = 2;
        $cuspideII = isset($caseRS[$casaSuccessiva]) 
            ? $caseRS[$casaSuccessiva]['longitudine'] 
            : null;
        
        foreach ($beneficiScudo as $idBenef) {
            if (!isset($pianetiConCase[$idBenef])) continue;
            $lonBenef = $pianetiConCase[$idBenef]['longitudine'];
            $casaAssegnata = (int)$pianetiConCase[$idBenef]['casa'];
            
            // Pre-ingresso 3° per lo scudo
            $diffCuspide = diffAngolo($lonBenef, $cuspideI);
            $inPreIngresso = ($diffCuspide > -$tolleranzaPreIngressoScudo && $diffCuspide < 0.0);
            $inCasaI = ($casaAssegnata === $casaScudo) || $inPreIngresso;
            
            if ($inCasaI) {
                // Verifica sicurezza in uscita: non deve essere a meno di 3° dalla II
                if ($cuspideII !== null) {
                    $diffUscita = diffAngolo($lonBenef, $cuspideII);
                    if ($diffUscita >= 0.0 && $diffUscita < $tolleranzaUscitaScudo) {
                        // Non è un fail, ma perde lo scudo
                        continue;
                    }
                }
                
                // Trovato un benefico che fa scudo
                $beneficoInI = [
                    'id' => $idBenef,
                    'nome' => $NOMI[$idBenef] ?? '?',
                    'longitudine' => $lonBenef,
                    'in_preingresso' => $inPreIngresso,
                    'distanza_cuspide' => round(abs($diffCuspide), 1)
                ];
                break;
            }
        }
    }
    
    // ================================================================
    // PASSO 3 (numerazione locale, non e' la Regola 3 ufficiale): ESCLUSIONE SOLE IN XII
    // ================================================================
    $idSole = 0;
    $casaXII = 12;
    $tolleranzaSoleXII = 3.0;
    
    if (isset($pianetiConCase[$idSole]) && isset($caseRS[$casaXII])) {
        $lonSole = $pianetiConCase[$idSole]['longitudine'];
        $casaSole = (int)$pianetiConCase[$idSole]['casa'];
        $cuspideXII = $caseRS[$casaXII]['longitudine'];
        
        $diffXII = diffAngolo($lonSole, $cuspideXII);
        $inPreIngressoXII = ($diffXII > -$tolleranzaSoleXII && $diffXII < 0.0);
        $inCasaXII = ($casaSole === $casaXII) || $inPreIngressoXII;
        
        if ($inCasaXII) {
            return [
                'valida' => false,
                'motivo' => 'SOLE in XII casa RS — XII casa spegne l\'energia vitale del Sole (fail assoluto)'
            ];
        }
    }
    
    // ================================================================
    // PASSO 4 (numerazione locale, non e' la Regola 4 ufficiale): RAFFORZAMENTO ASCENDENTE NATALE (tolleranza 3°)
    // ================================================================
    if (isset($caseRS['ASC'])) {
        $ascRS = $caseRS['ASC']['longitudine'];
        $tolleranzaAscNatale = 3.0;
        $caseCriticheNatale = [1, 6, 12];
        
        foreach ($caseCriticheNatale as $casaNat) {
            if (!isset($caseNatale[$casaNat])) continue;
            $cuspideNat = $caseNatale[$casaNat]['longitudine'];
            $diff = diffAngolo($ascRS, $cuspideNat);
            
            // ASC RS a meno di 3° dalla cuspide natale di I/VI/XII
            if ($diff > -$tolleranzaAscNatale && $diff < $tolleranzaAscNatale) {
                return [
                    'valida' => false,
                    'motivo' => 'ASC RS a ' . round(abs($diff), 1) . 
                                '° dalla ' . $casaNat . 'a casa natale — fail assoluto (tolleranza 3°)'
                ];
            }
        }
    }
    
    // ================================================================
    // PASSO 5 (numerazione locale, non e' la Regola 5 ufficiale): PROTEZIONE UNIVERSALE (Giove o Venere in I/VI/XII)
    // ================================================================
    $caseProtezione = [1, 6, 12];
    $protezioneTrovata = false;
    $beneficiProtezione = [3, 5]; // Venere, Giove
    
    foreach ($caseProtezione as $casaP) {
        if (!isset($caseRS[$casaP])) continue;
        $cuspideP = $caseRS[$casaP]['longitudine'];
        
        foreach ($beneficiProtezione as $idBenef) {
            if (!isset($pianetiConCase[$idBenef])) continue;
            $lonBenef = $pianetiConCase[$idBenef]['longitudine'];
            $casaAssegnata = (int)$pianetiConCase[$idBenef]['casa'];
            
            // Pre-ingresso standard 3°
            $diff = diffAngolo($lonBenef, $cuspideP);
            $inPreIngresso = ($diff > -3.0 && $diff < 0.0);
            $inCasa = ($casaAssegnata === $casaP) || $inPreIngresso;
            
            if ($inCasa) {
                $protezioneTrovata = true;
                break 2;
            }
        }
    }
    
    if (!$protezioneTrovata) {
        return [
            'valida' => false,
            'motivo' => 'Nessun benefico (Giove o Venere) in I/VI/XII casa RS — protezione universale assente'
        ];
    }
    
    // ================================================================
    // TUTTI I CONTROLLI SUPERATI
    // ================================================================
    return [
        'valida' => true,
        'scudo_benefico' => ($beneficoInI !== null),
        'benefico_in_i' => $beneficoInI
    ];
}

// ═══════════════════════════════════════════════════════════════════════════
//  VERIFICA SPECIFICA PER LA CONDIZIONE "DENARO"
//  Regole della scuola di Ciro Discepolo per finanza, entrate, investimenti
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Verifica che la condizione "Denaro" sia soddisfatta secondo le regole
 * della scuola di Ciro Discepolo per finanza, entrate e investimenti.
 *
 * REGOLE:
 * 1. Almeno uno tra Sole (0), Giove (5) o Venere (3) di RS deve trovarsi
 *    in II casa RS (guadagni diretti) OPPURE in VIII casa RS
 *    (grandi flussi finanziari, investimenti, entrate straordinarie).
 *
 * 2. TOLLERANZA PRE-INGRESSO 3°: il pianeta target è considerato valido
 *    anche se si trova nei 3° immediatamente precedenti la cuspide
 *    della II o della VIII casa RS (gestione modulo 360°).
 *
 * 3. SICUREZZA IN USCITA 2°: se il pianeta benefico si trova a meno di 2°
 *    PRIMA della cuspide della casa successiva (III se in II, IX se in VIII),
 *    la località DEVE essere scartata (FAIL assoluto).
 *
 * 4. FILTRO DI ESCLUSIONE: anche se la condizione dei benefici è soddisfatta,
 *    se MA/SA/UR/NE/PLU è in II o VIII casa RS (con pre-ingresso 3°),
 *    la località è un FAIL assoluto.
 *
 * 5. ALERT GIOVE BISTABILE: se Giove soddisfa la condizione, viene
 *    aggiunto un alert colorato per segnalare che PUÒ essere bistabile
 *    (Giove in II/VIII può generare alternanza di grande fortuna e
 *    improvvisi crolli).
 *
 * @param array<int,array{casa:int,longitudine:float}> $pianetiConCase
 * @param array<int,array{longitudine:float}> $caseRS
 * @return array{valida:bool, motivo?:string, beneficio_trovato?:array, alert_giove_bistabile?:bool}
 */
function verificaCondizioneDenaro(array $pianetiConCase, array $caseRS): array
{
    // Case target finanziarie: II (guadagni diretti) e VIII (grandi flussi)
    $caseTarget = [2, 8];

    // Benefici da verificare: Sole (0), Giove (5), Venere (3)
    $benefici = [0, 5, 3];

    // Malevoli da escludere: Marte (4), Saturno (6), Urano (7), Nettuno (8), Plutone (9)
    $malevoli = [4, 6, 7, 8, 9];

    // Mappa case successive per sicurezza in uscita
    $casaSuccessivaMap = [
        2 => 3,   // II → III
        8 => 9    // VIII → IX
    ];

    $beneficiTrovati = [];
    $malevoliTrovati = [];
    $alertGioveBistabile = false;

    // Per ogni casa target (II e VIII)
    foreach ($caseTarget as $casaTarget) {
        if (!isset($caseRS[$casaTarget])) {
            continue;
        }

        $cuspideTarget = $caseRS[$casaTarget]['longitudine'];

        // Determina la casa successiva per il vincolo di uscita
        $casaSuccessiva = $casaSuccessivaMap[$casaTarget] ?? $casaTarget + 1;
        $cuspideSuccessiva = isset($caseRS[$casaSuccessiva])
            ? $caseRS[$casaSuccessiva]['longitudine']
            : null;

        // Controlla tutti i pianeti
        foreach ($pianetiConCase as $idPianeta => $dati) {
            $casaAssegnata = (int)$dati['casa'];
            $longitudine = (float)$dati['longitudine'];

            // === PRE-INGRESSO 3° ===
            $diffCuspide = diffAngolo($longitudine, $cuspideTarget);
            $inPreIngresso = ($diffCuspide > -3.0 && $diffCuspide < 0.0);

            // Il pianeta è nella casa target (assegnata da SweCalc) o in pre-ingresso?
            $inCasaTarget = ($casaAssegnata === $casaTarget) || $inPreIngresso;

            if (!$inCasaTarget) {
                continue;
            }

            // === SICUREZZA IN USCITA 2° (SOLO PER BENEFICI) ===
            if ($cuspideSuccessiva !== null && in_array($idPianeta, $benefici, true)) {
                $diffUscita = diffAngolo($longitudine, $cuspideSuccessiva);
                // diffUscita ∈ [0°, 2°) → pianeta appena entrato nella casa successiva
                if ($diffUscita >= 0.0 && $diffUscita < 2.0) {
                    $nomeBenef = getNomePianeta($idPianeta);
                    return [
                        'valida' => false,
                        'motivo' => "Sicurezza in uscita: {$nomeBenef} a " .
                                    round($diffUscita, 1) . "° dalla cuspide della " .
                                    $casaSuccessiva . "a casa — troppo vicino all'uscita dalla " .
                                    $casaTarget . "a casa, flusso finanziario non coperto"
                    ];
                }
            }

            // Classifica il pianeta come benefico o malevolo
            if (in_array($idPianeta, $benefici, true)) {
                $beneficiTrovati[] = [
                    'id' => $idPianeta,
                    'casa' => $casaTarget,
                    'in_preingresso' => $inPreIngresso,
                    'longitudine' => $longitudine
                ];
                // Se è Giove, attiva l'alert bistabile
                if ($idPianeta === 5) {
                    $alertGioveBistabile = true;
                }
            } elseif (in_array($idPianeta, $malevoli, true)) {
                $malevoliTrovati[] = $idPianeta;
            }
        }
    }

    // === FILTRO DI ESCLUSIONE: malevoli in II o VIII ===
    if (!empty($malevoliTrovati)) {
        $nomiMalevoli = array_map('getNomePianeta', array_unique($malevoliTrovati));
        return [
            'valida' => false,
            'motivo' => 'Malevoli in II/VIII casa RS: ' . implode(', ', $nomiMalevoli) .
                        ' — spese eccessive, blocchi finanziari, perdite garantite'
        ];
    }

    // === VERIFICA PRESENZA BENEFICI ===
    if (empty($beneficiTrovati)) {
        return [
            'valida' => false,
            'motivo' => 'Nessun benefico (Sole, Giove o Venere) in II o VIII casa RS'
        ];
    }

    // === COSTRUISCI IL DETTAGLIO DEL BENEFICIO TROVATO ===
    // Prendi il primo beneficio trovato (priorità: Giove > Sole > Venere)
    $priorita = [5 => 1, 0 => 2, 3 => 3];
    usort($beneficiTrovati, function($a, $b) use ($priorita) {
        return ($priorita[$a['id']] ?? 99) <=> ($priorita[$b['id']] ?? 99);
    });
    $beneficioPrimario = $beneficiTrovati[0];
    $beneficioPrimario['nome'] = getNomePianeta($beneficioPrimario['id']);

    // Tutti i controlli superati
    return [
        'valida' => true,
        'beneficio_trovato' => $beneficioPrimario,
        'alert_giove_bistabile' => $alertGioveBistabile
    ];
}

// ═══════════════════════════════════════════════════════════════════════════
//  VERIFICA SPECIFICA PER LA CONDIZIONE "DENARO LOW"
//  Regole della scuola di Ciro Discepolo per difesa del patrimonio
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Verifica che la condizione "Denaro Low" sia soddisfatta secondo i criteri
 * di "difesa del patrimonio" della scuola di Ciro Discepolo.
 *
 * FILOSOFIA DELLA CONDIZIONE:
 * A differenza della condizione "Denaro" standard, in "Denaro Low" NON è
 * richiesto che Sole, Giove o Venere si trovino in II o VIII Casa di RS.
 * Le case II e VIII possono essere vuote. La condizione è puramente difensiva:
 * se non ci sono malefici nelle case finanziarie, la località è valida.
 *
 * REGOLE:
 * 1. RIGIDITÀ SULL'ESCLUSIONE ASSOLUTA (Protezione dalle perdite):
 *    La località viene scartata se MA/SA/UR/NE/PLU si trova IN II o VIII casa RS
 *    (con pre-ingresso di 3° dalla cuspide della casa).
 *
 * 2. TOLLERANZA IN USCITA SUI MALEFICI VICINI:
 *    Se uno dei 5 malefici si trova nella casa precedente (I per II, VII per VIII)
 *    ma è a meno di 3° dalla cuspide della casa finanziaria, la località viene
 *    scartata perché l'effetto malefico "sconfina" nella casa del denaro.
 *
 * 3. COERENZA CON I FILTRI GLOBALI:
 *    Questo controllo gira solo se la località ha già superato i blocchi globali
 *    (No malefici in I, VI, XII di RS e No Ascendente di RS in I, VI, XII Natale).
 *
 * @param array<int,array{casa:int,longitudine:float}> $pianetiConCase
 * @param array<int,array{longitudine:float}> $caseRS
 * @return array{valida:bool, motivo?:string, malefico_trovato?:array}
 */
function verificaCondizioneDenaroLow(array $pianetiConCase, array $caseRS): array
{
    // Case target finanziarie: II (guadagni diretti) e VIII (grandi flussi)
    $caseTarget = [2, 8];

    // Mappa delle case precedenti per il controllo di sconfinamento
    // I → II, VII → VIII
    $casaPrecedenteMap = [
        2 => 1,   // II ← I
        8 => 7    // VIII ← VII
    ];

    // Malevoli da escludere: Marte (4), Saturno (6), Urano (7), Nettuno (8), Plutone (9)
    $malevoli = [4, 6, 7, 8, 9];

    $tolleranzaPreIngresso = 3.0;
    $tolleranzaSconfinamento = 3.0;

    // Per ogni casa target (II e VIII)
    foreach ($caseTarget as $casaTarget) {
        if (!isset($caseRS[$casaTarget])) {
            continue;
        }

        $cuspideTarget = $caseRS[$casaTarget]['longitudine'];
        $casaPrecedente = $casaPrecedenteMap[$casaTarget] ?? $casaTarget - 1;

        // Controlla tutti i malevoli
        foreach ($malevoli as $idMal) {
            if (!isset($pianetiConCase[$idMal])) {
                continue;
            }

            $casaAssegnata = (int)$pianetiConCase[$idMal]['casa'];
            $longitudine = (float)$pianetiConCase[$idMal]['longitudine'];
            $nomeMal = getNomePianeta($idMal);

            // --- PASSO 1 (numerazione locale, non e' la Regola 1 ufficiale): RIGIDITÀ SULL'ESCLUSIONE ASSOLUTA ---
            // Il malevolo è nella casa target (assegnata da SweCalc)?
            if ($casaAssegnata === $casaTarget) {
                return [
                    'valida' => false,
                    'motivo' => "{$nomeMal} in {$casaTarget}a casa RS — esclusione assoluta (perdite garantite)"
                ];
            }

            // Pre-ingresso 3°: il malevolo è nei 3° immediatamente precedenti la cuspide?
            $diffCuspide = diffAngolo($longitudine, $cuspideTarget);
            if ($diffCuspide > -$tolleranzaPreIngresso && $diffCuspide < 0.0) {
                return [
                    'valida' => false,
                    'motivo' => "{$nomeMal} a " . round(abs($diffCuspide), 1) . 
                                "° dalla {$casaTarget}a casa RS (pre-ingresso) — esclusione assoluta"
                ];
            }

            // --- PASSO 2 (numerazione locale, non e' la Regola 2 ufficiale): TOLLERANZA IN USCITA SUI MALEFICI VICINI ---
            // Se il malevolo è nella casa precedente (I per II, VII per VIII)
            // ma a meno di 3° dalla cuspide della casa target, sconfina
            if ($casaAssegnata === $casaPrecedente) {
                // Calcola la distanza dalla cuspide della casa target
                $diffSconfinamento = diffAngolo($longitudine, $cuspideTarget);
                // Se il malevolo è a meno di 3° PRIMA della cuspide (negativo)
                // o a meno di 3° DOPO la cuspide (positivo), sconfina
                if ($diffSconfinamento > -$tolleranzaSconfinamento && $diffSconfinamento < $tolleranzaSconfinamento) {
                    return [
                        'valida' => false,
                        'motivo' => "{$nomeMal} in {$casaPrecedente}a casa a " . 
                                    round(abs($diffSconfinamento), 1) . 
                                    "° dalla {$casaTarget}a casa — sconfinamento nella casa del denaro"
                    ];
                }
            }
        }
    }

    // Tutti i controlli superati: nessun malevolo nelle case finanziarie
    return ['valida' => true];
}

