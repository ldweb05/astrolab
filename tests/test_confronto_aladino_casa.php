<?php
declare(strict_types=1);

/**
 * Confronto diagnostico ASTROLAB vs Aladino - condizione Casa.
 *
 * Soggetto "Lorenzo Diana", anno RS 2027, condizione Casa.
 * Passa alla pipeline reale di ASTROLAB (stessa di tests/test_casa.php e
 * api/ricerca_stream_api.php) le 102 localita' restituite da Aladino
 * (trascritte dagli screenshot, coordinate in gradi,primi; Aladino usa
 * longitudine negativa per Est, qui convertita in positiva Est).
 * Obiettivo: misurare quante di esse ASTROLAB accetta e con quali pianeti
 * in IV casa. Non tocca DB ne' codice di produzione (sola lettura).
 *
 * Uso: cd ~/astrolab && docker compose exec -T astrolab-web php /var/www/tests/test_confronto_aladino_casa.php
 */

require_once __DIR__ . '/../html/includes/bootstrap.php';
require_once __DIR__ . '/../html/includes/NascitaGmtHelper.php';
require_once __DIR__ . '/../html/includes/SweCalc.php';
require_once __DIR__ . '/../html/includes/RuleEngine.php';
require_once __DIR__ . '/../html/includes/RicercaRSFilters.php';
require_once __DIR__ . '/../html/includes/RicercaRSPlanetHouseAssigner.php';
require_once __DIR__ . '/../html/includes/RicercaRSThemeBuilder.php';
require_once __DIR__ . '/../html/includes/RuleEngineExtended.php';

// [nome, lon "gg,pp" Est, lat "gg,pp" (negativa = Sud)]
$aladino = [
 ['HAFR AL-BATIN SA','45,32','27,54'],['AMBATOLAHY MG','45,32','-20,01'],['MIANDRIVAZO MG','45,27','-19,33'],
 ['SOALALA MG','45,22','-16,06'],['MAJMA SA','45,22','25,56'],['MOGADISHU SO','45,21','02,02'],
 ['BEKILY MG','45,18','-24,14'],['ANKAVANDRA MG','45,16','-18,48'],['WADI AL DAWASSER SA','45,12','20,30'],
 ['WADI AD DAWASIR SA','45,10','20,30'],['KHANEH IR','45,09','36,44'],['ORUMIYEH IR','45,05','37,32'],
 ['URMIEH IR','45,04','37,37'],['REZAYIEH IR','45,04','37,40'],['ADEN YE','45,02','12,50'],
 ['BERBERA SO','45,01','10,25'],['MANDABE MG','44,57','-21,02'],['TBILISI GE','44,57','41,41'],
 ['MORAFENOBE MG','44,55','-17,51'],['ZILFI SA','44,49','26,18'],['AMPANIHY MG','44,44','-24,42'],
 ['DHALA YE','44,44','13,44'],['ANTSALOVA MG','44,37','-18,42'],['VLADIKAVKAZ RU','44,36','43,12'],
 ['GODE ET','44,35','05,06'],['MUHRANI GE','44,35','41,56'],['BELO MG','44,33','-19,40'],
 ['ANKAZOABO MG','44,32','-22,18'],['BAGHDAD RASHEED IQ','44,29','33,16'],['BESALAMPY MG','44,29','-16,45'],
 ['ANJOUAN KM','44,26','-12,08'],['NEJRAN SA','44,26','17,37'],['DHAMAR YE','44,26','14,32'],
 ['BAGHDAD METROP. IQ','44,25','33,21'],['YEREVAN AM','44,24','40,09'],['BETIOKY MG','44,23','-23,44'],
 ['KIRKUK IQ','44,22','35,28'],['VOLGOGRAD RU','44,21','48,41'],['MORONDAVA MG','44,19','-20,17'],
 ['ELISTA RU','44,19','46,22'],['MANJA MG','44,19','-21,25'],['ELISTA URWI RU','44,19','46,22'],
 ['KABRI DAR ET','44,16','06,44'],['BAGHDAD SIRSENK IQ','44,14','33,15'],['BAGHDAD INTL IQ','44,13','33,16'],
 ['SANAA YE','44,13','15,23'],['AWAREH ET','44,11','08,16'],['KELAFO ET','44,11','05,35'],
 ['TAIZ YE','44,08','13,41'],['MOKHA YE','44,08','13,15'],['HARGEISA SO','44,05','09,30'],
 ['MAINTIRANO MG','44,02','-18,03'],['UNAYZAH SA','44,00','26,30'],['TAMBOHORANO MG','43,58','-17,28'],
 ['KHAMIR YE','43,55','16,05'],['ALBUQ YE','43,46','15,50'],['MOHELI KM','43,46','-12,16'],
 ['GASSIM SA','43,46','26,18'],['TANANDAVA MG','43,44','-21,42'],['SAADA YE','43,44','16,58'],
 ['TOLIARA MG','43,43','-23,23'],['NALCHIK RU','43,42','43,31'],['BAIDOA SO','43,37','03,06'],
 ['EL RUSS SA','43,31','25,52'],['RAFHA SA','43,29','29,38'],['MOROMBE MG','43,22','-21,45'],
 ['VAN TR','43,19','38,28'],['MORONI HAHAYA KM','43,16','-11,32'],['BAMERNY IQ','43,16','37,06'],
 ['OBOCK DJ','43,15','11,55'],['MORONI ICONI KM','43,14','-11,42'],['ABBS YE','43,10','16,05'],
 ['AMBOULI DJ','43,10','11,33'],['MOSUL IQ','43,09','36,19'],['DJIBOUTI DJ','43,09','11,33'],
 ['KARS WYF TR','43,05','40,33'],['KARS KSY TR','43,05','40,33'],['MINERALNYE VODY RU','43,05','44,13'],
 ['AGRI WEX TR','43,04','39,44'],['CHABELLEY DJ','43,03','11,31'],['AGRI LTCO TR','43,01','39,39'],
 ['HODEIDAH YE','42,59','14,45'],['TADJOURA DJ','42,54','11,48'],['KHAMIS MUSHAYAT KMX SA','42,48','18,18'],
 ['JIJIGA ET','42,43','09,20'],['ALI SABIEH DJ','42,43','11,09'],['ASSAB HHSB ER','42,43','13,03'],
 ['ABHA SA','42,39','18,14'],['BISHA SA','42,37','19,59'],['KISMAYU KMU SO','42,35','-00,19'],
 ['KAMARAN ISLAND YE','42,35','15,22'],['GIZAN SA','42,34','16,54'],['KUTAISI GE','42,30','42,00'],
 ['DORRA DJ','42,28','12,10'],['KISIMAYU HCMK SO','42,27','-00,22'],['LUGH GANANE SO','42,27','03,35'],
 ['ASSAB HASB ET','42,25','13,02'],['DIKHIL DJ','42,21','11,06'],['BARDERA SO','42,18','02,21'],
 ['VOLGODONSK RU','42,04','47,40'],['STAVROPOL RU','42,00','45,20'],['DIRE DAWA ET','41,52','09,38'],
 ['MANDERA KE','41,52','03,56'],
];

function gp(string $s): float {
    $neg = str_starts_with(trim($s), '-');
    [$g, $p] = array_map('intval', explode(',', ltrim(trim($s), '-')));
    $v = $g + $p / 60;
    return $neg ? -$v : $v;
}
function fmtGp(float $x): string {
    $x = fmod($x + 360, 360);
    $segni = ['Ari','Tor','Gem','Can','Leo','Ver','Bil','Sco','Sag','Cap','Acq','Pes'];
    $s = (int)floor($x / 30); $d = $x - $s * 30;
    return sprintf("%02d°%02d' %s", (int)floor($d), (int)round(($d - floor($d)) * 60) % 60, $segni[$s]);
}

$pdo = db_connect();
$stmt = $pdo->prepare("SELECT * FROM soggetti WHERE nome ILIKE ? ORDER BY id LIMIT 1");
$stmt->execute(['%Lorenzo Diana%']);
$sog = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sog) { echo "✗ Soggetto 'Lorenzo Diana' non trovato\n"; exit(1); }

$gmt = calcolaDataOraGmtCorretta($sog['data_nascita'], $sog['ora_nascita'], (float)($sog['offset_gmt'] ?? 0));
$dt = new DateTime($gmt['data_gmt'] . ' ' . $gmt['ora_gmt']);
$op = explode(':', $gmt['ora_gmt']);
$g = (int)$dt->format('d'); $m = (int)$dt->format('m'); $a = (int)$dt->format('Y');
$oraGmt = (int)$op[0] + ((int)($op[1] ?? 0) / 60);

echo "Soggetto: {$sog['nome']} (id {$sog['id']}) - nascita GMT {$gmt['data_gmt']} {$gmt['ora_gmt']}"
   . "  [Aladino: 05/09/1960 16:47]\n";

$swe = new SweCalc(); $engine = new RuleEngine(); $engineExt = new RuleEngineExtended();
$rs = $swe->calcolaRS($g, $m, $a, $oraGmt, 2027);
$temaNatale = $swe->calcolaTema($g, $m, $a, $oraGmt, (float)$sog['latitudine'], (float)$sog['longitudine']);
$pianetiRS = $swe->calcolaPianeti($rs['giorno'], $rs['mese'], $rs['anno'], $rs['ora_gmt']);
echo "RS ASTROLAB: {$rs['stringa']}  [Aladino: 05/09/2027 21:30 GMT]\n\nPianeti RS:\n";
foreach ($pianetiRS as $id => $p) {
    $lon = is_array($p) ? (float)($p['longitudine'] ?? 0) : (float)$p;
    echo "  " . str_pad(RuleEngine::VAL_NOMI[$id] ?? (string)$id, 10) . fmtGp($lon) . "\n";
}
echo "\n";

$ok = 0; $tot = 0; $perPianeti = []; $motiviEsclusione = [];
printf("%-24s %6s %7s %-14s %-14s %-4s %s\n", 'LOCALITA', 'LON', 'LAT', 'IC', 'IN IV', 'ESC', 'VETI/NOTE');
foreach ($aladino as [$nome, $lonS, $latS]) {
    $tot++;
    $lon = gp($lonS); $lat = gp($latS);
    try {
        $caseRS = $swe->calcolaCasePlacido($rs['giorno'], $rs['mese'], $rs['anno'], $rs['ora_gmt'], $lat, $lon);
        $pc = assegnaCaseAiPianeti($pianetiRS, $caseRS, $swe);
        $temaRS = costruisciTemaRS($pc, $caseRS, $lat, $lon);
        $liv = $engineExt->calcolaLivelloCasa($temaRS);
        $val = $engineExt->generaValCasa($temaRS);
        $valut = $engine->valuta($temaNatale, $temaRS, 'Casa', []);
        $veti = array_values(array_filter($valut['veti'] ?? [],
            static fn(string $v): bool => strpos($v, 'astrolab-angoli') === false));
        $esc = ($liv['escludi'] ?? false) || !empty($veti);
        if (!$esc) { $ok++; $perPianeti[$val] = ($perPianeti[$val] ?? 0) + 1; }
        else {
            $k = ($liv['escludi'] ?? false) ? 'nessun benefico in IV' : 'veto: ' . implode(' | ', $veti);
            $motiviEsclusione[$k] = ($motiviEsclusione[$k] ?? 0) + 1;
        }
        printf("%-24s %6.2f %7.2f %-14s %-14s %-4s %s\n", substr($nome, 0, 24), $lon, $lat,
            fmtGp((float)$caseRS[4]['longitudine']), $val, $esc ? 'SI' : 'no',
            $esc ? (($liv['escludi'] ?? false) ? 'nessun benefico in IV' : implode(' | ', $veti)) : '');
    } catch (Throwable $e) {
        printf("%-24s ERRORE: %s\n", $nome, $e->getMessage());
    }
}

printf("\n=== ALLINEAMENTO: %d/%d localita' Aladino accettate da ASTROLAB (%.1f%%) ===\n", $ok, $tot, $ok * 100 / $tot);
echo "Accettate per pianeti in IV:\n";
foreach ($perPianeti as $k => $n) echo "  {$k}: {$n}\n";
if ($motiviEsclusione) {
    echo "Escluse per motivo:\n";
    foreach ($motiviEsclusione as $k => $n) echo "  {$n} x {$k}\n";
}
