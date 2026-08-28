<?php
/**
 * StellineV2Calculator — Sistema valutativo parallelo per RSM/RL
 *
 * FILE SPERIMENTALE — NON USATO IN PRODUZIONE
 * Implementa la logica V2 additiva per colore, completamente separata
 * dal sistema stelline attuale di RuleEngine.
 * I veti assoluti restano gestiti da RuleEngine::calcolaVeti().
 *
 * Logica: ogni pianeta contribuisce con N stelle del proprio colore.
 * Nessun clamp sul totale. Display contiguo senza spazi.
 * PRIORITA ASSOLUTA ALLA CASA CONDIZIONE scelta dall utente.
 */

require_once __DIR__ . '/RuleEngine.php';

class StellineV2Calculator {

    public const COLOR_VERDE   = '#2ecc71';
    public const COLOR_GIALLO  = '#f1c40f';
    public const COLOR_ARANCIO = '#f39c12';
    public const COLOR_ROSSO   = '#e74c3c';

    private const SOLE     = 0;
    private const LUNA     = 1;
    private const MERCURIO = 2;
    private const VENERE   = 3;
    private const MARTE    = 4;
    private const GIOVE    = 5;
    private const SATURNO  = 6;
    private const URANO    = 7;
    private const NETTUNO  = 8;
    private const PLUTONE  = 9;

    private const BENEFICI  = [self::GIOVE, self::VENERE];
    private const MALEFICI  = [self::MARTE, self::SATURNO, self::URANO, self::NETTUNO, self::PLUTONE];
    private const BISTABILI = [self::GIOVE, self::SOLE];

    // Case tematiche V2 — override rispetto a RuleEngine
    // Decima: solo X (carriera/status/reputazione).
    // Lavoro: VI+X (operativita quotidiana + avanzamento).
    // Amore: V+VII (amore/sesso/figli + legami stabili/partnership).
    // Salute: I+VI+XII (le tre case della salute da proteggere).
    // Denaro: II+VIII (entrate/uscite + eredita/investimenti).
    // Denaro Low: solo II (piccole entrate costanti).
    // Casa: IV (abitazione/traslochi/pace familiare).
    private const CASE_TEMATICHE_V2 = [
        'Decima'     => ['bonus' => [10],          'malus' => [1, 6, 12]],
        'Lavoro'     => ['bonus' => [6, 10],       'malus' => [1, 6, 12]],
        'Amore'      => ['bonus' => [5, 7],        'malus' => [1, 6, 12, 5, 7]],
        'Salute'     => ['bonus' => [1, 6, 12],    'malus' => [1, 6, 12]],
        'Denaro'     => ['bonus' => [2, 8],        'malus' => [1, 6, 12, 2, 8]],
        'Denaro Low' => ['bonus' => [2],           'malus' => [1, 6, 12, 2]],
        'Casa'       => ['bonus' => [4],           'malus' => [1, 6, 12, 4]],
    ];

    /**
     * Calcola le stelline V2 per una RS/RL gia validata (veti superati).
     */
    public function calcola(array $pianeti, array $case, string $condizione, array $temaNatale): array {
        $ct = self::CASE_TEMATICHE_V2[$condizione] ?? self::CASE_TEMATICHE_V2['Decima'];

        $contributi = [];
        $totaleVerdi   = 0;
        $totaleGialle  = 0;
        $totaleArancio = 0;
        $totaleRosse   = 0;
        $alertStelliumMisto = false;

        // Rileva stellium (>=3 pianeti nella stessa casa)
        $casePianeti = [];
        foreach ($pianeti as $id => $p) {
            $casa = $p['casa'];
            if (!isset($casePianeti[$casa])) $casePianeti[$casa] = [];
            $casePianeti[$casa][] = $id;
        }
        $stelliumCase = [];
        foreach ($casePianeti as $casa => $ids) {
            if (count($ids) >= 3) {
                $stelliumCase[$casa] = $ids;
            }
        }

        // Elabora ogni pianeta
        foreach ($pianeti as $id => $p) {
            $casa = $p['casa'];
            $inCasaCondizione = in_array($casa, $ct['bonus']);
            $inCasaMalus = in_array($casa, $ct['malus']);
            $inCuspideAngolare = in_array($casa, [1, 4, 7, 10]);
            $inBistabile = in_array($casa, [2, 7, 8]);
            $inParcheggio = in_array($casa, [3, 9]);
            $isBenefico = in_array($id, self::BENEFICI);
            $isMalefico = in_array($id, self::MALEFICI);
            $isBistabile = in_array($id, self::BISTABILI);
            $inStellium = isset($stelliumCase[$casa]);

            // Se parte di stellium, elabora come gruppo (una sola volta)
            if ($inStellium && $id === reset($stelliumCase[$casa])) {
                $result = $this->evalStellium(
                    $stelliumCase[$casa], $casa, $ct,
                    $inCuspideAngolare, $inBistabile, $pianeti
                );
                foreach ($result['contributi'] as $c) {
                    $contributi[] = $c;
                    match($c['colore']) {
                        self::COLOR_VERDE   => $totaleVerdi += $c['stelle'],
                        self::COLOR_GIALLO  => $totaleGialle += $c['stelle'],
                        self::COLOR_ARANCIO => $totaleArancio += $c['stelle'],
                        self::COLOR_ROSSO   => $totaleRosse += $c['stelle'],
                        default => null,
                    };
                }
                if ($result['alert_misto']) $alertStelliumMisto = true;
                continue;
            }
            if ($inStellium) continue;

            // Pianeta singolo — PRIORITA ASSOLUTA ALLA CASA CONDIZIONE
            $stelle = 0;
            $colore = null;
            $note = '';

            // LIVELLO 1: Pianeta nella casa BONUS della condizione (massima priorita)
            if ($inCasaCondizione) {
                if ($isBenefico) {
                    $stelle = 4; $colore = self::COLOR_VERDE;
                    $note = ucfirst($this->nomePianeta($id)) . ' in casa condizione';
                } elseif ($id === self::SOLE) {
                    $stelle = 3; $colore = self::COLOR_VERDE;
                    $note = 'Sole in casa condizione';
                } elseif ($id === self::LUNA || $id === self::MERCURIO) {
                    $stelle = 2; $colore = self::COLOR_GIALLO;
                    $note = ucfirst($this->nomePianeta($id)) . ' in casa condizione';
                } elseif ($isMalefico) {
                    $stelle = 2; $colore = self::COLOR_ROSSO;
                    $note = ucfirst($this->nomePianeta($id)) . ' malefico in casa condizione';
                }
            }
            // LIVELLO 1b: Malefico in casa MALUS della condizione
            elseif ($inCasaMalus && $isMalefico) {
                $stelle = 2; $colore = self::COLOR_ROSSO;
                $note = ucfirst($this->nomePianeta($id)) . ' malefico in casa malus';
            }
            // LIVELLO 2: Bistabile nelle case bistabili (II/VII/VIII)
            elseif ($isBistabile && $inBistabile) {
                $stelle = 2; $colore = self::COLOR_ARANCIO;
                $note = ucfirst($this->nomePianeta($id)) . ' bistabile in II/VII/VIII';
            }
            // LIVELLO 3: Benefico in cuspide angolare (solo se NON in casa condizione)
            elseif ($isBenefico && $inCuspideAngolare) {
                $stelle = 2; $colore = self::COLOR_GIALLO;
                $note = ucfirst($this->nomePianeta($id)) . ' in cuspide angolare (non cond.)';
            }
            // LIVELLO 4: Malefico in parcheggio (III/IX) = neutro
            elseif ($isMalefico && $inParcheggio) {
                $stelle = 0; $colore = null;
                $note = ucfirst($this->nomePianeta($id)) . ' in parcheggio (neutro)';
            }

            if ($stelle > 0 && $colore !== null) {
                $contributi[] = [
                    'pianeta' => $id, 'casa' => $casa,
                    'stelle' => $stelle, 'colore' => $colore, 'note' => $note,
                ];
                match($colore) {
                    self::COLOR_VERDE   => $totaleVerdi += $stelle,
                    self::COLOR_GIALLO  => $totaleGialle += $stelle,
                    self::COLOR_ARANCIO => $totaleArancio += $stelle,
                    self::COLOR_ROSSO   => $totaleRosse += $stelle,
                    default => null,
                };
            }
        }

        // ASC di RS/RL mappato sulle case del TEMA NATALE (non sulle case della
        // RS: l'ASC di un tema è sempre cuspide della propria casa I, quindi un
        // controllo su $case['ASC']['casa'] non ha senso e infatti quella chiave
        // non esiste mai nella struttura prodotta da SweCalc::calcolaCasePlacido()).
        $casaAscNatale = isset($case['ASC']['longitudine'])
            ? $this->trovaCasaNatale($case['ASC']['longitudine'], $temaNatale['case'] ?? [])
            : 0;

        // ASC in X (bonus indipendente dai pianeti)
        $ascInX = false;
        if ($casaAscNatale === 10) {
            $ascInX = true;
            $contributi[] = [
                'pianeta' => 'ASC', 'casa' => 10,
                'stelle' => 5, 'colore' => self::COLOR_VERDE, 'note' => 'ASC in X (tema natale)',
            ];
            $totaleVerdi += 5;
        }

        // ASC in casa condizione bonus (se non gia in X)
        if (!$ascInX && $casaAscNatale > 0 && in_array($casaAscNatale, $ct['bonus'])) {
            $contributi[] = [
                'pianeta' => 'ASC', 'casa' => $casaAscNatale,
                'stelle' => 3, 'colore' => self::COLOR_VERDE, 'note' => 'ASC in casa condizione (tema natale)',
            ];
            $totaleVerdi += 3;
        }

        // Malus sottrattivi
        $malus = 0;

        // -2 ASC RS in casa natale VIII
        if (isset($case['ASC']['longitudine']) && isset($temaNatale['case'])) {
            $casaNataleAsc = $this->trovaCasaNatale($case['ASC']['longitudine'], $temaNatale['case']);
            if ($casaNataleAsc === 8) {
                $malus += 2;
            }
        }

        // -1 Malefico in VII
        foreach ($pianeti as $id => $p) {
            if (in_array($id, self::MALEFICI) && $p['casa'] === 7) {
                $malus += 1;
            }
        }

        $totale = max(0, $totaleVerdi + $totaleGialle + $totaleArancio + $totaleRosse - $malus);

        return [
            'stelle_totali'        => $totale,
            'stelle_verdi'         => $totaleVerdi,
            'stelle_gialle'        => $totaleGialle,
            'stelle_arancio'       => $totaleArancio,
            'stelle_rosse'         => $totaleRosse,
            'malus'                => $malus,
            'alert_stellium_misto' => $alertStelliumMisto,
            'dettaglio'            => $contributi,
        ];
    }

    /**
     * Valuta uno stellium come entita unica.
     */
    private function evalStellium(
        array $ids, int $casa, array $ct,
        bool $inCuspide, bool $inBistabile, array $pianeti
    ): array {
        $hasMalefico = false;
        $hasBenefico = false;
        $hasBistabile = false;
        $hasVenere = false;

        foreach ($ids as $id) {
            if (in_array($id, self::MALEFICI)) $hasMalefico = true;
            if (in_array($id, self::BENEFICI)) $hasBenefico = true;
            if (in_array($id, self::BISTABILI)) $hasBistabile = true;
            if ($id === self::VENERE) $hasVenere = true;
        }

        $inCasaBonus = in_array($casa, $ct['bonus']);
        $inCasaMalus = in_array($casa, $ct['malus']);
        $contributi = [];
        $alertMisto = false;

        if ($hasMalefico && $hasBenefico) {
            $alertMisto = true;
            foreach ($ids as $id) {
                $isMal = in_array($id, self::MALEFICI);
                $contributi[] = [
                    'pianeta' => $id, 'casa' => $casa,
                    'stelle' => 1,
                    'colore' => $isMal ? self::COLOR_ROSSO : self::COLOR_GIALLO,
                    'note' => 'Stellium misto',
                ];
            }
        } elseif (!$hasMalefico) {
            if ($inCuspide && $inCasaBonus) {
                $contributi[] = ['pianeta' => 'stellium', 'casa' => $casa, 'stelle' => 4, 'colore' => self::COLOR_VERDE, 'note' => 'Stellium benefico in cuspide angolare + condizione'];
            } elseif ($inCasaBonus) {
                $contributi[] = ['pianeta' => 'stellium', 'casa' => $casa, 'stelle' => 4, 'colore' => self::COLOR_VERDE, 'note' => 'Stellium benefico in casa condizione'];
            } elseif ($inCuspide) {
                $contributi[] = ['pianeta' => 'stellium', 'casa' => $casa, 'stelle' => 3, 'colore' => self::COLOR_GIALLO, 'note' => 'Stellium benefico in cuspide angolare'];
            } elseif ($inBistabile && $hasBistabile && !$hasVenere) {
                $contributi[] = ['pianeta' => 'stellium', 'casa' => $casa, 'stelle' => 2, 'colore' => self::COLOR_ARANCIO, 'note' => 'Stellium bistabile in II/VII/VIII'];
            } else {
                $contributi[] = ['pianeta' => 'stellium', 'casa' => $casa, 'stelle' => 1, 'colore' => self::COLOR_GIALLO, 'note' => 'Stellium benefico in casa neutra'];
            }
        } else {
            if ($inCasaMalus) {
                $contributi[] = ['pianeta' => 'stellium', 'casa' => $casa, 'stelle' => 2, 'colore' => self::COLOR_ROSSO, 'note' => 'Stellium malefico in casa malus'];
            } else {
                $contributi[] = ['pianeta' => 'stellium', 'casa' => $casa, 'stelle' => 1, 'colore' => self::COLOR_ROSSO, 'note' => 'Stellium malefico'];
            }
        }

        return ['contributi' => $contributi, 'alert_misto' => $alertMisto];
    }

    /**
     * Trova la casa natale in cui cade una longitudine.
     */
    private function trovaCasaNatale(float $longitudine, array $caseNatali): int {
        $lon = fmod($longitudine + 360, 360);
        for ($c = 1; $c <= 12; $c++) {
            if (!isset($caseNatali[$c]['longitudine'])) continue;
            $ini  = fmod($caseNatali[$c]['longitudine'] + 360, 360);
            $fine = fmod(($caseNatali[($c % 12) + 1]['longitudine'] ?? 0) + 360, 360);
            if ($ini <= $fine) {
                if ($lon >= $ini && $lon < $fine) return $c;
            } else {
                if ($lon >= $ini || $lon < $fine) return $c;
            }
        }
        return 0;
    }

    private function nomePianeta(int $id): string {
        return match($id) {
            self::SOLE => 'sole', self::LUNA => 'luna', self::MERCURIO => 'mercurio',
            self::VENERE => 'venere', self::MARTE => 'marte', self::GIOVE => 'giove',
            self::SATURNO => 'saturno', self::URANO => 'urano', self::NETTUNO => 'nettuno',
            self::PLUTONE => 'plutone', default => 'pianeta',
        };
    }

    /**
     * Genera HTML delle stelline colorate contigue senza spazi.
     */
    public function renderHTML(array $risultato): string {
        $html = '';
        if ($risultato['stelle_verdi'] > 0) {
            $html .= '<span style="color:' . self::COLOR_VERDE . '">' . str_repeat('★', $risultato['stelle_verdi']) . '</span>';
        }
        if ($risultato['stelle_gialle'] > 0) {
            $html .= '<span style="color:' . self::COLOR_GIALLO . '">' . str_repeat('★', $risultato['stelle_gialle']) . '</span>';
        }
        if ($risultato['stelle_arancio'] > 0) {
            $html .= '<span style="color:' . self::COLOR_ARANCIO . '">' . str_repeat('★', $risultato['stelle_arancio']) . '</span>';
        }
        if ($risultato['stelle_rosse'] > 0) {
            $html .= '<span style="color:' . self::COLOR_ROSSO . '">' . str_repeat('★', $risultato['stelle_rosse']) . '</span>';
        }
        if ($risultato['alert_stellium_misto']) {
            $html .= ' <span style="color:' . self::COLOR_GIALLO . '" title="Stellium misto: presenza di malefico">⚠️</span>';
        }
        return $html;
    }
}