<?php
require_once __DIR__ . '/bootstrap.php';
/**
 * ⚠️ LEGACY / NON USATO DALLA UI ATTUALE ⚠️
 * Questa classe è usata solo da api/ricerca_api.php (a sua volta non
 * richiamato da nessuna pagina del frontend). Implementa una valutazione
 * semplificata (solo RuleEngine::valuta(), senza Rule Map di esclusione
 * radicale, senza FiltroEsclusione.php, senza filtri specifici per
 * condizione). La fonte autorevole per la logica di ricerca batch è
 * api/ricerca_stream_api.php. Non usare questo file come riferimento per
 * modifiche alle regole di ricerca.
 */
/**
 * SearchEngine — Motore di ricerca RSM su database aeroporti
 * Calcola RS/RL per ogni aeroporto e restituisce ranking per stelline
 */

require_once 'SweCalc.php';
require_once 'RuleEngine.php';

class SearchEngine {

    private PDO $pdo;
    private SweCalc $swe;
    private RuleEngine $engine;

    public function __construct() {
        $this->pdo    = db_connect();
        $this->swe    = new SweCalc();
        $this->engine = new RuleEngine();
    }

    /**
     * Ricerca principale — calcola RS per ogni aeroporto e ordina per stelline
     *
     * @param array  $soggetto    Dati natali: [g,m,a,ora_gmt,lat,lon]
     * @param int    $annoRS      Anno della Rivoluzione Solare
     * @param string $condizione  Condizione tematica (Decima, Amore, ecc.)
     * @param string $tipoRicerca 'large_medium' | 'tutti' | 'iata_only'
     * @param bool   $escludiMilitari
     * @param array  $astriInCasa Filtri personalizzati
     * @param int    $limite      Max risultati da restituire
     * @return array
     */
    public function cerca(
        array  $soggetto,
        int    $annoRS,
        string $condizione      = 'Decima',
        string $tipoRicerca     = 'large_medium',
        bool   $escludiMilitari = true,
        array  $astriInCasa     = [],
        int    $limite          = 500
    ): array {

        // Calcola tema natale una volta sola
        [$g, $m, $a, $oraGmt, $lat, $lon] = $soggetto;
        $temaNatale = $this->swe->calcolaTema($g, $m, $a, $oraGmt, $lat, $lon);

        // Calcola momento RS (uguale per tutti gli aeroporti)
        $rs = $this->swe->calcolaRS($g, $m, $a, $oraGmt, $annoRS);
        $oraGmtRS = $rs['ora_gmt'];
        $giornoRS = $rs['giorno'];
        $meseRS   = $rs['mese'];
        $annoRSeff= $rs['anno'];

        // Costruisce query aeroporti
        $where = [];
        $params = [];

        if ($tipoRicerca === 'large_medium') {
            $where[] = "tipo IN ('large_airport','medium_airport')";
        } elseif ($tipoRicerca === 'iata_only') {
            $where[] = "iata_code != '' AND iata_code IS NOT NULL";
            $where[] = "tipo IN ('large_airport','medium_airport','small_airport')";
        } else {
            $where[] = "tipo IN ('large_airport','medium_airport','small_airport')";
        }

        if ($escludiMilitari) $where[] = "militare = false";
        $where[] = "attivo = true";

        $sql = "SELECT icao_code, iata_code, nome, citta, nazione, 
                       latitudine, longitudine, altitudine, militare
                FROM aeroporti 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY longitudine";

        $stmt = $this->pdo->query($sql);
        $aeroporti = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $risultati = [];

        foreach ($aeroporti as $aero) {
            $latA = floatval($aero['latitudine']);
            $lonA = floatval($aero['longitudine']);

            // Calcola tema RS per questo aeroporto
            try {
                $temaRS = $this->swe->calcolaTema(
                    $giornoRS, $meseRS, $annoRSeff,
                    $oraGmtRS, $latA, $lonA
                );

                // Valuta con RuleEngine
                $val = $this->engine->valuta(
                    $temaNatale, $temaRS, $condizione, $astriInCasa
                );

                $risultati[] = [
                    'icao'      => $aero['icao_code'],
                    'iata'      => $aero['iata_code'],
                    'nome'      => $aero['nome'],
                    'citta'     => $aero['citta'],
                    'nazione'   => $aero['nazione'],
                    'lat'       => $latA,
                    'lon'       => $lonA,
                    'alt'       => $aero['altitudine'],
                    'militare'  => $aero['militare'],
                    'stelline'  => $val['stelline'],
                    'stelle_str'=> $val['stelle_str'],
                    'val'       => $val['val'],
                    'valido'    => $val['is_valida'],
                    'veti'      => $val['veti'],
                ];

            } catch (Exception $e) {
                // Salta aeroporti con errori di calcolo
                continue;
            }
        }

        // Ordina per stelline decrescenti
        usort($risultati, fn($a, $b) => $b['stelline'] <=> $a['stelline']);

        return [
            'rs_gmt'    => $rs['stringa'],
            'condizione'=> $condizione,
            'totale'    => count($risultati),
            'risultati' => array_slice($risultati, 0, $limite),
        ];
    }
}