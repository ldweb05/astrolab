<?php
require_once __DIR__ . '/AstroUtils.php';
/**
 * SweCalc — Wrapper PHP per Swiss Ephemeris
 * VERSIONE CORRETTA E PIÙ PRECISA
 * Compatibile RSM / Astrologia Attiva / Placidus
 */

class SweCalc {

    private string $ephePath;
    private ?FFI $sweFfi = null;

    // Pianeti
    const PIANETI = [
        0 => 'Sole',
        1 => 'Luna',
        2 => 'Mercurio',
        3 => 'Venere',
        4 => 'Marte',
        5 => 'Giove',
        6 => 'Saturno',
        7 => 'Urano',
        8 => 'Nettuno',
        9 => 'Plutone',
       11 => 'NodoNord',
       12 => 'NodoSud',
    ];

    private const LIBSWE_PIANETI = [
        0  => 0,
        1  => 1,
        2  => 2,
        3  => 3,
        4  => 4,
        5  => 5,
        6  => 6,
        7  => 7,
        8  => 8,
        9  => 9,
        11 => 10,
        12 => 11,
    ];

    // Simboli pianeti
    const SIMBOLI = [
        0 => '☉', 1 => '☽', 2 => '☿', 3 => '♀', 4 => '♂',
        5 => '♃', 6 => '♄', 7 => '♅', 8 => '♆', 9 => '♇',
       11 => '☊', 12 => '☋',
    ];

    // Segni zodiacali
    const SEGNI = [
        0  => ['nome' => 'Ariete',      'simbolo' => '♈', 'abbr' => 'Ari'],
        1  => ['nome' => 'Toro',        'simbolo' => '♉', 'abbr' => 'Tau'],
        2  => ['nome' => 'Gemelli',     'simbolo' => '♊', 'abbr' => 'Gem'],
        3  => ['nome' => 'Cancro',      'simbolo' => '♋', 'abbr' => 'Can'],
        4  => ['nome' => 'Leone',       'simbolo' => '♌', 'abbr' => 'Leo'],
        5  => ['nome' => 'Vergine',     'simbolo' => '♍', 'abbr' => 'Vir'],
        6  => ['nome' => 'Bilancia',    'simbolo' => '♎', 'abbr' => 'Lib'],
        7  => ['nome' => 'Scorpione',   'simbolo' => '♏', 'abbr' => 'Sco'],
        8  => ['nome' => 'Sagittario',  'simbolo' => '♐', 'abbr' => 'Sag'],
        9  => ['nome' => 'Capricorno',  'simbolo' => '♑', 'abbr' => 'Cap'],
        10 => ['nome' => 'Acquario',    'simbolo' => '♒', 'abbr' => 'Aqu'],
        11 => ['nome' => 'Pesci',       'simbolo' => '♓', 'abbr' => 'Pis'],
    ];

    public function __construct() {

        // IMPORTANTISSIMO
        date_default_timezone_set('UTC');

        $this->ephePath   = getenv('SWISSEPH_PATH') ?: '/opt/swisseph/ephe';
    }

    /**
     * Converte data in formato swetest
     */




    private function getSweFfi(): FFI
    {
        if ($this->sweFfi instanceof FFI) {
            return $this->sweFfi;
        }

        $this->sweFfi = FFI::cdef(
            "void swe_set_ephe_path(char *path);
             double swe_julday(int year, int month, int day, double hour, int gregflag);
             int swe_calc_ut(double tjd_ut, int ipl, int iflag, double *xx, char *serr);
             int swe_houses(double tjd_ut, double geolat, double geolon, int hsys, double *cusps, double *ascmc);
             const char *swe_get_planet_name(int ipl, char *spname);",
            "/usr/local/lib/libswe.so"
        );

        $this->sweFfi->swe_set_ephe_path($this->ephePath);

        return $this->sweFfi;
    }

    private function formatData(
        int $giorno,
        int $mese,
        int $anno
    ): string {

        return sprintf('%d.%d.%d', $giorno, $mese, $anno);
    }

    /**
     * Converte ora float in HH:MM:SS
     * FIX PRINCIPALE
     */
    private function formatOra(float $oraGmt): string {

        $h = intval($oraGmt);

        $minFloat = ($oraGmt - $h) * 60;
        $m = intval($minFloat);

        $secFloat = ($minFloat - $m) * 60;
        $s = intval(round($secFloat));

        // gestione overflow
        if ($s >= 60) {
            $s = 0;
            $m++;
        }

        if ($m >= 60) {
            $m = 0;
            $h++;
        }

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    public function gradi2DMS(float $gradi): array {

        $gradi = fmod($gradi + 360, 360);

        $segno = intval($gradi / 30);

        $resto = fmod($gradi, 30);

        $g = intval($resto);

        $minFloat = ($resto - $g) * 60;
        $min = intval($minFloat);

        $secFloat = ($minFloat - $min) * 60;

        // FIX: round invece di intval
        $sec = intval(round($secFloat));

        // gestione overflow
        if ($sec >= 60) {
            $sec = 0;
            $min++;
        }

        if ($min >= 60) {
            $min = 0;
            $g++;
        }

        return [
            'gradi_totali' => $gradi,
            'segno_num'    => $segno,
            'segno_nome'   => self::SEGNI[$segno]['nome'],
            'segno_simbolo'=> self::SEGNI[$segno]['simbolo'],
            'segno_abbr'   => self::SEGNI[$segno]['abbr'],
            'gradi'        => $g,
            'minuti'       => $min,
            'secondi'      => $sec,
            'stringa'      => sprintf(
                '%s %d° %02d\' %02d"',
                self::SEGNI[$segno]['nome'],
                $g,
                $min,
                $sec
            ),
        ];
    }

    /**
     * Calcolo pianeti
     */
    public function calcolaPianeti(
        int $giorno,
        int $mese,
        int $anno,
        float $oraGmt
    ): array {

        static $cache = [];

        $cacheKey = sprintf('%04d-%02d-%02d|%.6F', $anno, $mese, $giorno, $oraGmt);

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        return $cache[$cacheKey] = $this->calcolaPianetiBackendLibswe(
            $giorno,
            $mese,
            $anno,
            $oraGmt
        );
    }





    private function calcolaPianetiBackendLibswe(
        int $giorno,
        int $mese,
        int $anno,
        float $oraGmt
    ): array {
        $ffi = $this->getSweFfi();
        $jd = $ffi->swe_julday($anno, $mese, $giorno, $oraGmt, 1);
        $risultato = [];

        foreach (range(0, 9) as $id) {
            $libsweId = self::LIBSWE_PIANETI[$id];
            $xx = $ffi->new('double[6]');
            $err = $ffi->new('char[256]');

            $retCode = $ffi->swe_calc_ut($jd, $libsweId, 258, $xx, $err);

            if ($retCode < 0) {
                throw new RuntimeException('Errore libswe calcolaPianeti: ' . FFI::string($err));
            }

            $lon = (float) $xx[0];
            $pos = $this->gradi2DMS($lon);

            $risultato[$id] = [
                'id'          => $id,
                'nome'        => self::PIANETI[$id] ?? (string) $id,
                'simbolo'     => self::SIMBOLI[$id] ?? '?',
                'longitudine' => $lon,
                'retrogrado'  => ((float) $xx[3]) < 0,
                'posizione'   => $pos,
                'casa'        => 0,
            ];
        }

        return $risultato;
    }

    private function calcolaCasePlacidoBackendLibswe(
        int $giorno,
        int $mese,
        int $anno,
        float $oraGmt,
        float $latitudine,
        float $longitudine
    ): array {
        $ffi = $this->getSweFfi();
        $jd = $ffi->swe_julday($anno, $mese, $giorno, $oraGmt, 1);

        $cusps = $ffi->new('double[13]');
        $ascmc = $ffi->new('double[10]');

        $retCode = $ffi->swe_houses($jd, $latitudine, $longitudine, ord('P'), $cusps, $ascmc);

        if ($retCode < 0) {
            $datiValidi = is_finite((float) $ascmc[0])
                && is_finite((float) $ascmc[1]);

            for ($i = 1; $i <= 12 && $datiValidi; $i++) {
                $datiValidi = is_finite((float) $cusps[$i]);
            }

            if (!$datiValidi) {
                throw new RuntimeException('Errore libswe calcolaCasePlacido');
            }
        }

        $case = [];

        for ($i = 1; $i <= 12; $i++) {
            $case[$i] = [
                'numero'      => $i,
                'longitudine' => (float) $cusps[$i],
                'posizione'   => $this->gradi2DMS((float) $cusps[$i]),
            ];
        }

        $case['ASC'] = [
            'numero'      => 'ASC',
            'longitudine' => (float) $ascmc[0],
            'posizione'   => $this->gradi2DMS((float) $ascmc[0]),
        ];

        $case['MC'] = [
            'numero'      => 'MC',
            'longitudine' => (float) $ascmc[1],
            'posizione'   => $this->gradi2DMS((float) $ascmc[1]),
        ];

        return $case;
    }

    /**
     * Calcolo case Placido
     *
     * NOTA CRITICA sul flag -house di swetest:
     * Il formato corretto è: -house<LON>,<LAT>,<sistema>
     * - LON e LAT devono essere attaccati al flag, NON quotati separatamente.
     * - escapeshellarg() rompe il flag con valori negativi (es. longitudine ovest).
     * - Usiamo number_format per produrre stringhe sicure (solo cifre, punto, segno meno)
     * e le concateniamo direttamente nel flag senza quoting.
     */
    public function calcolaCasePlacido(
        int $giorno,
        int $mese,
        int $anno,
        float $oraGmt,
        float $latitudine,
        float $longitudine
    ): array {

        // Non memorizzare le case per coordinate: durante le ricerche geografiche
        // ogni località ha latitudine/longitudine differenti e una cache statica
        // crescerebbe fino a esaurire la memoria del processo PHP.
        return $this->calcolaCasePlacidoBackendLibswe(
            $giorno,
            $mese,
            $anno,
            $oraGmt,
            $latitudine,
            $longitudine
        );
    }

    /**
     * Tema completo
     */
    public function calcolaTema(
        int $giorno,
        int $mese,
        int $anno,
        float $oraGmt,
        float $latitudine,
        float $longitudine
    ): array {

        $pianeti = $this->calcolaPianeti(
            $giorno,
            $mese,
            $anno,
            $oraGmt
        );

        $case = $this->calcolaCasePlacido(
            $giorno,
            $mese,
            $anno,
            $oraGmt,
            $latitudine,
            $longitudine
        );

        foreach ($pianeti as $id => $pianeta) {

            $pianeti[$id]['casa'] = $this->trovaCasa(
                $pianeta['longitudine'],
                $case
            );
        }

        return [
            'pianeti' => $pianeti,
            'case'    => $case,
            'data'    => "$giorno/$mese/$anno",
            'ora_gmt' => $oraGmt,
            'lat'     => $latitudine,
            'lon'     => $longitudine,
        ];
    }

    /**
     * Trova casa
     */
    private function trovaCasa(
        float $longitudine,
        array $case
    ): int {

        $lon = fmod($longitudine + 360, 360);

        for ($c = 1; $c <= 12; $c++) {

            if (!isset($case[$c])) {
                continue;
            }

            $ini = fmod(
                $case[$c]['longitudine'] + 360,
                360
            );

            $fine = fmod(
                $case[($c % 12) + 1]['longitudine'] + 360,
                360
            );

            if ($ini <= $fine) {

                if ($lon >= $ini && $lon < $fine) {
                    return $c;
                }

            } else {

                if ($lon >= $ini || $lon < $fine) {
                    return $c;
                }
            }
        }

        return 1;
    }

    /**
     * Versione pubblica di trovaCasa — usata da ricerca_stream.php
     * per riutilizzare i pianeti già calcolati senza ricalcolarli
     * per ogni aeroporto.
     */
    public function trovaCasaPublic(
        float $longitudine,
        array $case
    ): int {
        return $this->trovaCasa($longitudine, $case);
    }

    /**
     * DIFFERENZA ANGOLARE
     */
    private function diffAngolo(
        float $a,
        float $b
    ): float {

        return AstroUtils::diffAngolo($a, $b);
    }

    private function calcolaLongitudineLibswe(float $jd, int $libsweId): float
    {
        $ffi = $this->getSweFfi();

        $xx = $ffi->new('double[6]');
        $err = $ffi->new('char[256]');

        $retCode = $ffi->swe_calc_ut($jd, $libsweId, 258, $xx, $err);

        if ($retCode < 0) {
            throw new RuntimeException('Errore libswe longitudine: ' . FFI::string($err));
        }

        return (float) $xx[0];
    }

    /**
     * LONGITUDINE SOLE
     */
    private function soleLongitudine(float $jd): float {

        return $this->calcolaLongitudineLibswe($jd, 0);
    }

    /**
     * LONGITUDINE LUNA
     */
    private function lunaLongitudine(float $jd): float {

        return $this->calcolaLongitudineLibswe($jd, 1);
    }

    /**
     * BISEZIONE RS
     */
    private function bisezioneRS(
        float $lonTarget,
        float $jd1,
        float $jd2
    ): float {

        for ($i = 0; $i < 60; $i++) {

            $jdMid = ($jd1 + $jd2) / 2;

            $lonMid = $this->soleLongitudine($jdMid);

            $diff = $this->diffAngolo($lonMid, $lonTarget);

            if (abs($diff) < 0.00001) {
                return $jdMid;
            }

            if ($diff > 0) {
                $jd2 = $jdMid;
            } else {
                $jd1 = $jdMid;
            }
        }

        return ($jd1 + $jd2) / 2;
    }

    /**
     * BISEZIONE RL
     */
    private function bisezioneRL(
        float $lonTarget,
        float $jd1,
        float $jd2
    ): float {

        for ($i = 0; $i < 70; $i++) {

            $jdMid = ($jd1 + $jd2) / 2;

            $lonMid = $this->lunaLongitudine($jdMid);

            $diff = $this->diffAngolo($lonMid, $lonTarget);

            if (abs($diff) < 0.00001) {
                return $jdMid;
            }

            if ($diff > 0) {
                $jd2 = $jdMid;
            } else {
                $jd1 = $jdMid;
            }
        }

        return ($jd1 + $jd2) / 2;
    }

    /**
     * CALCOLO RS
     */
    public function calcolaRS(
        int $giornoNascita,
        int $meseNascita,
        int $annoNascita,
        float $oraGmtNascita,
        int $annoRS
    ): array {

        $natale = $this->calcolaPianeti(
            $giornoNascita,
            $meseNascita,
            $annoNascita,
            $oraGmtNascita
        );

        $soleLon = $natale[0]['longitudine'];

        $jdInizio = $this->dataAJulian(
            $giornoNascita,
            $meseNascita,
            $annoRS
        ) - 2;

        $jdFine = $jdInizio + 4;

        $jdRS = $this->bisezioneRS(
            $soleLon,
            $jdInizio,
            $jdFine
        );

        return $this->julianAData($jdRS);
    }

    /**
     * JULIAN DAY
     */
    public function dataAJulian(
        int $g,
        int $m,
        int $a,
        float $h = 12.0
    ): float {

        if ($m <= 2) {
            $a--;
            $m += 12;
        }

        $A = intval($a / 100);

        $B = 2 - $A + intval($A / 4);

        return intval(365.25 * ($a + 4716))
            + intval(30.6001 * ($m + 1))
            + $g
            + $B
            - 1524.5
            + $h / 24.0;
    }

    /**
     * JULIAN -> DATA
     */
    public function julianAData(float $jd): array {

        $jd += 0.5;

        $Z = intval($jd);

        $F = $jd - $Z;

        if ($Z < 2299161) {
            $A = $Z;
        } else {
            $alpha = intval(($Z - 1867216.25) / 36524.25);
            $A = $Z + 1 + $alpha - intval($alpha / 4);
        }

        $B = $A + 1524;

        $C = intval(($B - 122.1) / 365.25);

        $D = intval(365.25 * $C);

        $E = intval(($B - $D) / 30.6001);

        $giorno = $B - $D - intval(30.6001 * $E);

        $mese = ($E < 14) ? $E - 1 : $E - 13;

        $anno = ($mese > 2) ? $C - 4716 : $C - 4715;

        $oreFloat = $F * 24;

        $ore = intval($oreFloat);

        $minFloat = ($oreFloat - $ore) * 60;

        $minuti = intval($minFloat);

        $secFloat = ($minFloat - $minuti) * 60;

        $secondi = intval(round($secFloat));

        return [
            'jd' => $jd - 0.5,
            'giorno' => $giorno,
            'mese' => $mese,
            'anno' => $anno,
            'ora' => $ore,
            'minuti' => $minuti,
            'secondi' => $secondi,
            'ora_gmt' => $oreFloat,
            'stringa' => sprintf(
                '%02d/%02d/%04d %02d:%02d:%02d GMT',
                $giorno,
                $mese,
                $anno,
                $ore,
                $minuti,
                $secondi
            ),
        ];
    }

    /**
     * PARSE PIANETI
     */


    /**
     * PARSE CASE
     */


    /**
     * CALCOLA TUTTE LE RIVOLUZIONI LUNARI DI UN ANNO RS
     *
     * Trova tutti i ritorni della Luna sulla longitudine natale
     * all'interno dell'anno della Rivoluzione Solare selezionata.
     *
     * NOTA: questo metodo non è usato dal path produzione (rl_api.php
     * ha la propria implementazione calcolaListaRL()). Viene mantenuto
     * per compatibilità e usa lo stesso algoritmo corretto.
     *
     * BUG CORRETTI rispetto alla versione precedente:
     *   1. $jdInizioAnno era undefined (fatal error silenzioso PHP)
     *   2. La finestra +366 sovrascriveva il calcolo corretto della RS successiva
     *   3. Il passo da 24 giorni con verifica diffAngolo(target, luna) era
     *      invertito rispetto alla convenzione della bisezione (luna, target)
     *   4. La scansione partiva da jdInizioAnno-5 ma senza $jdInizioAnno
     *      definito produceva risultati casuali
     */
    /**
     * Restituisce il numero di ore dell'anno.
     */
    private function oreAnnuali(int $anno): int
    {
        $isLeap = (($anno % 4 == 0) && ($anno % 100 != 0)) || ($anno % 400 == 0);

        return $isLeap ? 8784 : 8760;
    }


    public static function getProfilazione(): array
    {
        return [
            'calls' => 0,
            'time_ms' => 0,
            'avg_ms' => 0,
            'by_method' => [],
        ];
    }

    public function calcolaTutteRLLibsweCompatibileLunaApi(
        int $giornoNascita,
        int $meseNascita,
        int $annoNascita,
        float $oraGmtNascita,
        int $annoRS
    ): array {
        $ffi = FFI::cdef(
            "void swe_set_ephe_path(char *path);
             double swe_julday(int year, int month, int day, double hour, int gregflag);
             int swe_calc_ut(double tjd_ut, int ipl, int iflag, double *xx, char *serr);",
            "/usr/local/lib/libswe.so"
        );

        // Compatibilità esatta con luna_api.php.
        $ffi->swe_set_ephe_path('.');

        $lunaTarget = $this->calcolaLongitudineLunaCompatibile($ffi, $annoNascita, $meseNascita, $giornoNascita, $oraGmtNascita);

        $results = [];
        $prevPos = null;
        $prevTs = null;

        $startTs = strtotime(sprintf('%04d-01-01 00:00:00 UTC', $annoRS));
        $totalHours = $this->oreAnnuali($annoRS);

        for ($i = 0; $i < $totalHours; $i++) {
            $ts = $startTs + ($i * 3600);
            $ora = (int)gmdate('H', $ts);

            $pos = $this->calcolaLongitudineLunaCompatibile(
                $ffi,
                (int)gmdate('Y', $ts),
                (int)gmdate('m', $ts),
                (int)gmdate('d', $ts),
                (float)$ora
            );

            if ($prevPos !== null) {
                $p1 = $prevPos;
                $p2 = $pos;
                $target = $lunaTarget;
                $cross = false;

                if ($p1 <= $target && $p2 >= $target) {
                    $cross = true;
                } elseif ($p1 > $p2 && ($p1 - $p2) > 180) {
                    $p2Adj = $p2 + 360;
                    if ($p1 <= $target + 360 && $p2Adj >= $target + 360) {
                        $cross = true;
                        $p2 = $p2Adj;
                        $target += 360;
                    }
                }

                if ($cross) {
                    $fraction = ($target - $p1) / ($p2 - $p1);
                    $exactTs = $prevTs + round($fraction * 3600);

                    $dt = new DateTime('@' . $exactTs);
                    $dt->setTimezone(new DateTimeZone('UTC'));

                    $results[] = [
                        'index'   => count($results),
                        'jd'      => $exactTs / 86400,
                        'gmt_str' => $dt->format('Y-m-d H:i:s'),
                        'giorno'  => (int)$dt->format('d'),
                        'mese'    => (int)$dt->format('m'),
                        'anno'    => (int)$dt->format('Y'),
                        'ora_gmt' => (int)$dt->format('H') + ((int)$dt->format('i') / 60),
                    ];
                }
            }

            $prevPos = $pos;
            $prevTs = $ts;
        }

        return $results;
    }

    private function calcolaLongitudineLunaCompatibile(FFI $ffi, int $anno, int $mese, int $giorno, float $oraGmt): float
    {
        $jd = $ffi->swe_julday($anno, $mese, $giorno, $oraGmt, 1);

        $xx = $ffi->new('double[6]');
        $err = $ffi->new('char[256]');

        $retCode = $ffi->swe_calc_ut($jd, 1, 258, $xx, $err);

        if ($retCode < 0) {
            throw new RuntimeException('Errore libswe RL compatibile: ' . FFI::string($err));
        }

        return (float)$xx[0];
    }

  
    }