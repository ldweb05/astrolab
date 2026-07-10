<?php
/**
 * api/stampa_pdf_api.php — Generazione PDF nativo lato server
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * REQUISITI:
 *   Nel container Docker, dentro /var/www/html (o la root del progetto):
 *     composer require dompdf/dompdf
 *
 *   Quindi nel Dockerfile (o docker-compose exec astro-web):
 *     cd /var/www/html && composer require dompdf/dompdf
 *
 *   Dompdf v2.x supporta PHP 8+ e SVG inline.
 *
 * FLUSSO:
 *   1. Riceve via fetch() JSON: soggetto_id, formato, moduli, parametri RS/RL/Riloc
 *   2. Riceve le ruote già renderizzate come PNG base64 dal client
 *      (png_natale, png_rs, png_rl, png_riloc) — il rendering SVG→Canvas→PNG
 *      è fatto lato client da zodiac_wheel.js + serializzaPNG() in stampa.php.
 *      Le immagini PNG sono compatibili nativamente con Dompdf, a differenza
 *      degli SVG inline che Dompdf gestisce in modo inaffidabile.
 *   3. Recupera i dati astronomici dal server (pianeti, case, valutazione) per le tabelle
 *   4. Assembla HTML completo del report con <img src="data:image/png;base64,...">
 *   5. Passa HTML a Dompdf → stream PDF al browser (download)
 *
 * SICUREZZA:
 *   - Verifica sessione + appartenenza soggetto tramite Auth
 *   - Sanitizza tutti gli input ricevuti
 *   - I PNG vengono validati (devono iniziare con "data:image/png;base64,")
 */

declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

session_start();
require_once '../includes/Auth.php';

$pdo  = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(403);
    die('Non autenticato.');
}

require_once '../includes/SweCalc.php';
require_once '../includes/RuleEngine.php';
require_once '../includes/FiltroEsclusione.php';

// ── Carica Dompdf via Composer ──────────────────────────────────────────
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    // Dompdf non installato: fornisce messaggio chiaro
    header('Content-Type: text/html; charset=UTF-8');
    die(_paginaErroreDompdf());
}
require_once $autoload;

use Dompdf\Dompdf;
use Dompdf\Options;

// ── Parametri: JSON via fetch() ──────────────────────────────────────────
// Il client invia JSON via fetch() per evitare i limiti degli <input hidden>
// e la conversione PNG-base64 per compatibilità Dompdf con le immagini.
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];
$get  = fn(string $k, mixed $d = '') => $data[$k] ?? $d;

$soggettoId = intval($get('soggetto_id', 0));
$formato    = preg_replace('/[^a-z0-9\-]/', '', $get('formato', 'a4-portrait'));
$moduliRaw  = $get('moduli', '');
$moduli     = array_filter(explode(',', $moduliRaw));
$annoRS     = intval($get('anno_rs', date('Y')));
$luogoRS    = strip_tags(trim($get('luogo_rs', '')));
$latRS      = floatval($get('lat_rs', 0));
$lonRS      = floatval($get('lon_rs', 0));
$condizione = strip_tags(trim($get('condizione', 'Decima')));
$rlIndex    = intval($get('rl_index', 0));
$latRL      = floatval($get('lat_rl', 0));
$lonRL      = floatval($get('lon_rl', 0));
$latRiloc   = floatval($get('lat_riloc', 0));
$lonRiloc   = floatval($get('lon_riloc', 0));
$luogoRiloc = strip_tags(trim($get('luogo_riloc', '')));

// Immagini PNG in base64 generate lato client da Canvas (compatibili Dompdf)
// Formato atteso: "data:image/png;base64,..." oppure stringa vuota
$pngNatale = _validaPng($get('png_natale', ''));
$pngRS     = _validaPng($get('png_rs',     ''));
$pngRL     = _validaPng($get('png_rl',     ''));
$pngRiloc  = _validaPng($get('png_riloc',  ''));

// ── Verifica autorizzazione soggetto ────────────────────────────────────
$soggetto = $auth->verificaSoggetto($soggettoId);
if (!$soggetto) {
    http_response_code(403);
    die('Soggetto non trovato o non autorizzato.');
}

$condizioniValide = ['Decima','Lavoro','Amore','Salute','Denaro','Denaro Low','Casa'];
if (!in_array($condizione, $condizioniValide, true)) $condizione = 'Decima';

// ── Dati natali del soggetto ────────────────────────────────────────────
$dateNascita = new DateTime($soggetto['data_nascita']);
$g = (int)$dateNascita->format('d');
$m = (int)$dateNascita->format('m');
$a = (int)$dateNascita->format('Y');
$oraGmtParts = explode(':', $soggetto['ora_nascita_gmt']);
$oraGmt = (int)$oraGmtParts[0] + ((isset($oraGmtParts[1]) ? (int)$oraGmtParts[1] : 0)) / 60.0;
$latNasc = (float)$soggetto['latitudine'];
$lonNasc = (float)$soggetto['longitudine'];

$swe    = new SweCalc();
$engine = new RuleEngine();

// ── Recupero dati per le tabelle (pianeti, case, valutazione) ───────────
$temaNatale = null;
$temaRS     = null;
$temaRL     = null;
$temaRiloc  = null;
$valRS      = null;
$valRL      = null;
$rsGmt      = '';
$rlGmt      = '';

if (in_array('natale', $moduli) || in_array('rs', $moduli) || in_array('riloc', $moduli)) {
    $temaNatale = $swe->calcolaTema($g, $m, $a, $oraGmt, $latNasc, $lonNasc);
}

if (in_array('rs', $moduli)) {
    $latRSeff = $latRS ?: $latNasc;
    $lonRSeff = $lonRS ?: $lonNasc;
    $rs       = $swe->calcolaRS($g, $m, $a, $oraGmt, $annoRS);
    $rsGmt    = $rs['stringa'];
    $temaRS   = $swe->calcolaTema($rs['giorno'], $rs['mese'], $rs['anno'], $rs['ora_gmt'], $latRSeff, $lonRSeff);
    $valRS    = $engine->valuta($temaNatale, $temaRS, $condizione);
}

if (in_array('rl', $moduli)) {
    $latRLeff = $latRL ?: $latNasc;
    $lonRLeff = $lonRL ?: $lonNasc;
    try {
        $rlList = $swe->calcolaTutteRLLibsweCompatibileLunaApi($g, $m, $a, $oraGmt, $annoRS);
        $rlData = isset($rlList[$rlIndex + 1]) ? $rlList[$rlIndex + 1] : (isset($rlList[1]) ? $rlList[1] : null);
        if ($rlData) {
            $rlGmt  = $rlData['stringa'];
            $temaRL = $swe->calcolaTema(
                $rlData['giorno'], $rlData['mese'], $rlData['anno'],
                $rlData['ora_gmt'], $latRLeff, $lonRLeff
            );
            if ($temaNatale) $valRL = $engine->valuta($temaNatale, $temaRL, $condizione);
        }
    } catch (Throwable $e) {
        // RL non disponibile — sezione sarà vuota
    }
}

if (in_array('riloc', $moduli)) {
    $latRilocEff = $latRiloc ?: $latNasc;
    $lonRilocEff = $lonRiloc ?: $lonNasc;
    $temaRiloc   = $swe->calcolaTema($g, $m, $a, $oraGmt, $latRilocEff, $lonRilocEff);
}

// ── Dimensioni pagina Dompdf ────────────────────────────────────────────
list($dompdfPaper, $dompdfOrient) = _formatoToDompdf($formato);

// ── Costruzione HTML del report ─────────────────────────────────────────
$html = _buildHtmlReport(
    $soggetto, $moduli, $formato,
    $pngNatale, $pngRS, $pngRL, $pngRiloc,
    $temaNatale, $temaRS, $temaRL, $temaRiloc,
    $valRS, $valRL,
    $rsGmt, $rlGmt, $luogoRS, $luogoRiloc,
    $annoRS, $rlIndex, $condizione
);

// ── Generazione PDF con Dompdf ──────────────────────────────────────────
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('isFontSubsettingEnabled', true);
$options->set('defaultMediaType', 'print');
$options->setChroot(dirname(__DIR__));

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper($dompdfPaper, $dompdfOrient);
$dompdf->render();

// ── Nome file ────────────────────────────────────────────────────────────
$nomeSoggetto = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $soggetto['nome']);
$dataOggi     = date('Ymd');
$nomeFile     = "AstrologiaAttiva_{$nomeSoggetto}_{$dataOggi}.pdf";

$dompdf->stream($nomeFile, ['Attachment' => true]);
exit;


// ════════════════════════════════════════════════════════════════════════
//  FUNZIONI HELPER
// ════════════════════════════════════════════════════════════════════════

/**
 * Valida una stringa PNG in formato data-URL base64.
 * Accetta "data:image/png;base64,..." e restituisce la stringa intatta
 * se valida, altrimenti stringa vuota.
 */
function _validaPng(string $raw): string {
    $raw = trim($raw);
    if (empty($raw)) return '';
    if (!str_starts_with($raw, 'data:image/png;base64,')) return '';
    return $raw;
}

/**
 * Converte il formato scelto nei parametri Dompdf.
 */
function _formatoToDompdf(string $formato): array {
    switch ($formato) {
        case 'a4-portrait':
            return ['A4', 'portrait'];
        case 'a4-landscape':
            return ['A4', 'landscape'];
        case 'a3-portrait':
            return ['A3', 'portrait'];
        case 'a3-landscape':
            return ['A3', 'landscape'];
        default:
            return ['A4', 'portrait'];
    }
}

/**
 * Costruisce la tabella pianeti HTML per il PDF.
 */
function _tabellaPianeti(?array $pianeti, string $title): string {
    if (!$pianeti) return '';
    $nomi = [
        0=>'☉ Sole',1=>'☽ Luna',2=>'☿ Mercurio',3=>'♀ Venere',4=>'♂ Marte',
        5=>'♃ Giove',6=>'♄ Saturno',7=>'♅ Urano',8=>'♆ Nettuno',9=>'♇ Plutone',
        11=>'☊ Nodo N.'
    ];
    $case = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
             7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
    $rows = '';
    foreach ($pianeti as $p) {
        $nomeCasa = isset($case[$p['casa']]) ? $case[$p['casa']] : $p['casa'];
        $retro    = $p['retrogrado'] ? '<span style="color:#CC3333">&#8477;</span>' : '';
        $nomePianeta = isset($nomi[$p['id']]) ? $nomi[$p['id']] : htmlspecialchars($p['nome']);
        $rows .= "<tr>
            <td>{$nomePianeta}</td>
            <td>{$p['posizione']['stringa']}</td>
            <td>{$nomeCasa}</td>
            <td>{$retro}</td>
        </tr>";
    }
    return "
    <div class='report-tabella-wrap'>
        <div class='report-tabella-title'>" . htmlspecialchars($title) . "</div>
        <table class='report-tabella-pianeti'>
            <thead><tr><th>Pianeta</th><th>Posizione</th><th>Casa</th><th></th></tr></thead>
            <tbody>{$rows}</tbody>
        </table>
    </div>";
}

/**
 * Costruisce la tabella cuspidi case HTML per il PDF.
 */
function _tabellaCase(?array $temaCase, string $title): string {
    if (!$temaCase) return '';
    $nomiCase = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
                 7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
    $angolari = [1, 4, 7, 10];
    $rows = '';
    for ($c = 1; $c <= 12; $c++) {
        if (!isset($temaCase[$c])) continue;
        $isAng = in_array($c, $angolari);
        $style = $isAng ? 'style="font-weight:bold;color:#2C3E6B"' : '';
        $rows .= "<tr {$style}>
            <td>{$nomiCase[$c]}</td>
            <td>{$temaCase[$c]['posizione']['stringa']}</td>
        </tr>";
    }
    return "
    <div class='report-tabella-wrap'>
        <div class='report-tabella-title'>" . htmlspecialchars($title) . "</div>
        <table class='report-tabella-pianeti'>
            <thead><tr><th>Casa</th><th>Cuspide</th></tr></thead>
            <tbody>{$rows}</tbody>
        </table>
    </div>";
}

/**
 * Costruisce il blocco valutazione RS/RL HTML per il PDF.
 */
function _valutazioneHtml(?array $val): string {
    if (!$val) return '';
    $stelle = str_repeat('&#9733;', $val['stelline']) . str_repeat('&#9734;', 5 - $val['stelline']);
    $valStr = isset($val['val']) ? htmlspecialchars($val['val']) : '';
    $cond   = isset($val['condizione']) ? htmlspecialchars($val['condizione']) : '';

    $veti = '';
    if (isset($val['veti']) && is_array($val['veti'])) {
        foreach ($val['veti'] as $v) {
            $veti .= "<div class='val-item-print val-veto-print'>&#9940; " . htmlspecialchars($v) . "</div>";
        }
    }
    $bonus = '';
    if (isset($val['bonus']) && is_array($val['bonus'])) {
        foreach ($val['bonus'] as $b) {
            $nota = isset($b['nota']) ? htmlspecialchars($b['nota']) : '';
            $bonus .= "<div class='val-item-print val-bonus-print'><b>" . htmlspecialchars($b['codice']) .
                      "</b> " . $nota . "</div>";
        }
    }
    $pen = '';
    if (isset($val['penalita']) && is_array($val['penalita'])) {
        foreach ($val['penalita'] as $p) {
            $nota = isset($p['nota']) ? htmlspecialchars($p['nota']) : '';
            $pen .= "<div class='val-item-print val-pen-print'><b>" . htmlspecialchars($p['codice']) .
                    "</b> " . $nota . "</div>";
        }
    }

    $colsHtml = "<table width='100%'><tr>
        <td width='50%' valign='top'>{$bonus}</td>
        <td width='50%' valign='top'>{$pen}</td>
    </tr></table>";

    return "
    <div class='report-valutazione'>
        <div class='val-header-print'>
            <span class='stelle-print' style='color:#C8960C;font-size:14pt'>{$stelle}</span>
            &nbsp;
            <span class='val-str-print'>{$valStr}</span>
            &nbsp;
            <span class='val-cond-print'>Cond.: {$cond}</span>
        </div>
        <div class='val-body-print'>
            {$veti}
            {$colsHtml}
        </div>
    </div>";
}

/**
 * Assembla l'HTML completo del report per Dompdf.
 * Le immagini delle ruote zodiacali sono PNG base64 (<img>) — Dompdf
 * le renderizza in modo nativo e affidabile, a differenza degli SVG inline.
 */
function _buildHtmlReport(
    array   $soggetto,
    array   $moduli,
    string  $formato,
    string  $pngNatale,
    string  $pngRS,
    string  $pngRL,
    string  $pngRiloc,
    ?array  $temaNatale,
    ?array  $temaRS,
    ?array  $temaRL,
    ?array  $temaRiloc,
    ?array  $valRS,
    ?array  $valRL,
    string  $rsGmt,
    string  $rlGmt,
    string  $luogoRS,
    string  $luogoRiloc,
    int     $annoRS,
    int     $rlIndex,
    string  $condizione
): string {

    $nomeSoggetto = htmlspecialchars($soggetto['nome']);
    $dataStr      = date('d/m/Y', strtotime($soggetto['data_nascita']));
    $oraLoc       = substr($soggetto['ora_nascita'], 0, 5);
    $luogoNasc    = htmlspecialchars($soggetto['luogo_nascita'] . ' ' . $soggetto['nazione_nascita']);
    $dataOggi     = date('d/m/Y H:i');
    $luogoRSEsc   = $luogoRS ? htmlspecialchars($luogoRS) : '—';
    $luogoRilocEsc= $luogoRiloc ? htmlspecialchars($luogoRiloc) : '—';

    $css = _getCSSInline();

    // Helper inline: restituisce <img> se PNG disponibile, altrimenti placeholder testo
    $imgTag = function(string $png, string $alt, string $width = '100%'): string {
        if ($png) {
            return "<img src=\"{$png}\" alt=\"{$alt}\" style=\"width:{$width};height:auto;display:block;margin:0 auto\">";
        }
        return "<div style=\"text-align:center;color:#aaa;font-size:8pt;padding:10pt\">[{$alt}]</div>";
    };

    $header = "
    <div class='report-header'>
        <table width='100%'><tr>
            <td valign='top'>
                <div class='report-titolo'>&#9737; Astrologia Attiva</div>
                <div class='report-sottotitolo'>Scuola di Ciro Discepolo &mdash; Rivoluzioni Solari Mirate</div>
            </td>
            <td valign='top' align='right'>
                <div class='report-soggetto'>{$nomeSoggetto}</div>
                <div class='report-dati'>Nato/a il {$dataStr} &mdash; {$oraLoc} (loc.)</div>
                <div class='report-dati'>Luogo nascita: {$luogoNasc}</div>
                " . ($luogoRS ? "<div class='report-dati'>RS {$annoRS} &mdash; {$luogoRSEsc} &mdash; Cond.: " . htmlspecialchars($condizione) . "</div>" : '') . "
            </td>
        </tr></table>
    </div>";

    $body = '';

    $hasNatale = in_array('natale', $moduli) && !empty($pngNatale);
    $hasRS     = in_array('rs',     $moduli) && !empty($pngRS);

    if ($hasNatale || $hasRS) {
        if ($hasNatale && $hasRS) {
            $ascNat  = $temaNatale['case']['ASC']['posizione']['stringa'] ?? '?';
            $mcNat   = $temaNatale['case']['MC']['posizione']['stringa']  ?? '?';
            $ascRS   = $temaRS['case']['ASC']['posizione']['stringa']     ?? '?';
            $mcRS    = $temaRS['case']['MC']['posizione']['stringa']      ?? '?';

            $body .= "
            <table class='ruote-affiancate-pdf' width='100%'>
            <tr>
                <td width='50%' valign='top' style='padding-right:6pt'>
                    <div class='ruota-title'>&#9737; Tema Natale &mdash; {$nomeSoggetto}</div>
                    <div class='ruota-img-wrap'>" . $imgTag($pngNatale, 'Tema Natale') . "</div>
                    <div class='ruota-info'>ASC: {$ascNat} &nbsp;&middot;&nbsp; MC: {$mcNat}</div>
                    " . _tabellaPianeti($temaNatale['pianeti'] ?? null, 'Pianeti natali') . "
                    " . _tabellaCase($temaNatale['case'] ?? null, 'Case Placido &mdash; nascita') . "
                </td>
                <td width='50%' valign='top' style='padding-left:6pt;border-left:1px solid #D0C8BC'>
                    <div class='ruota-title'>&#8635; RS {$annoRS} &mdash; {$luogoRSEsc}</div>
                    <div class='ruota-img-wrap'>" . $imgTag($pngRS, 'RS ' . $annoRS) . "</div>
                    <div class='ruota-info'>
                        ASC: {$ascRS} &nbsp;&middot;&nbsp; MC: {$mcRS}<br>
                        GMT: " . htmlspecialchars($rsGmt) . "
                    </div>
                    " . _valutazioneHtml($valRS) . "
                    " . _tabellaPianeti($temaRS['pianeti'] ?? null, 'Pianeti RS') . "
                    " . _tabellaCase($temaRS['case'] ?? null, 'Case Placido &mdash; RS') . "
                </td>
            </tr>
            </table>";
        } elseif ($hasNatale) {
            $ascNat = $temaNatale['case']['ASC']['posizione']['stringa'] ?? '?';
            $mcNat  = $temaNatale['case']['MC']['posizione']['stringa']  ?? '?';
            $body .= "
            <div class='ruota-title'>&#9737; Tema Natale &mdash; {$nomeSoggetto}</div>
            <div class='ruota-img-wrap'>" . $imgTag($pngNatale, 'Tema Natale', '60%') . "</div>
            <div class='ruota-info'>ASC: {$ascNat} &nbsp;&middot;&nbsp; MC: {$mcNat}</div>
            " . _tabellaPianeti($temaNatale['pianeti'] ?? null, 'Pianeti natali') . "
            " . _tabellaCase($temaNatale['case'] ?? null, 'Case Placido &mdash; nascita');
        } else {
            $ascRS = $temaRS['case']['ASC']['posizione']['stringa'] ?? '?';
            $mcRS  = $temaRS['case']['MC']['posizione']['stringa']  ?? '?';
            $body .= "
            <div class='ruota-title'>&#8635; RS {$annoRS} &mdash; {$luogoRSEsc}</div>
            <div class='ruota-img-wrap'>" . $imgTag($pngRS, 'RS ' . $annoRS, '60%') . "</div>
            <div class='ruota-info'>ASC: {$ascRS} &nbsp;&middot;&nbsp; MC: {$mcRS} &nbsp;GMT: " . htmlspecialchars($rsGmt) . "</div>
            " . _valutazioneHtml($valRS) . "
            " . _tabellaPianeti($temaRS['pianeti'] ?? null, 'Pianeti RS') . "
            " . _tabellaCase($temaRS['case'] ?? null, 'Case Placido &mdash; RS');
        }
    }

    if (in_array('rl', $moduli) && ($pngRL || $temaRL)) {
        if ($hasNatale || $hasRS) {
            $body .= "<div style='page-break-before:always'></div>";
        }
        $ascRL = $temaRL['case']['ASC']['posizione']['stringa'] ?? '?';
        $mcRL  = $temaRL['case']['MC']['posizione']['stringa']  ?? '?';
        $body .= "
        <div class='report-section'>
            <div class='report-section-title'>&#9789; Rivoluzione Lunare &mdash; RL " . ($rlIndex + 1) . "</div>
            <table width='100%'><tr>
                <td width='50%' valign='top' style='padding-right:6pt'>
                    <div class='ruota-title'>&#9789; Tema RL</div>
                    <div class='ruota-img-wrap'>" . $imgTag($pngRL, 'RL ' . ($rlIndex + 1)) . "</div>
                    <div class='ruota-info'>GMT RL: " . htmlspecialchars($rlGmt) . "<br>ASC: {$ascRL} &middot; MC: {$mcRL}</div>
                    " . _valutazioneHtml($valRL) . "
                </td>
                <td width='50%' valign='top' style='padding-left:6pt;border-left:1px solid #D0C8BC'>
                    " . _tabellaPianeti($temaRL['pianeti'] ?? null, 'Pianeti RL') . "
                    " . _tabellaCase($temaRL['case'] ?? null, 'Case Placido &mdash; RL') . "
                </td>
            </tr></table>
        </div>";
    }

    if (in_array('riloc', $moduli) && ($pngRiloc || $temaRiloc)) {
        if ($hasNatale || $hasRS || in_array('rl', $moduli)) {
            $body .= "<div style='page-break-before:always'></div>";
        }
        $ascRiloc = $temaRiloc['case']['ASC']['posizione']['stringa'] ?? '?';
        $mcRiloc  = $temaRiloc['case']['MC']['posizione']['stringa']  ?? '?';
        $body .= "
        <div class='report-section'>
            <div class='report-section-title'>&#9791; Rilocazione &mdash; {$luogoRilocEsc}</div>
            <table width='100%'><tr>
                <td width='50%' valign='top' style='padding-right:6pt'>
                    <div class='ruota-title'>Tema Natale (nascita)</div>
                    <div class='ruota-img-wrap'>" . $imgTag($pngNatale, 'Tema Natale') . "</div>
                </td>
                <td width='50%' valign='top' style='padding-left:6pt;border-left:1px solid #D0C8BC'>
                    <div class='ruota-title'>Tema Natale rilocato &mdash; {$luogoRilocEsc}</div>
                    <div class='ruota-img-wrap'>" . $imgTag($pngRiloc, 'Rilocazione') . "</div>
                    <div class='ruota-info'>ASC: {$ascRiloc} &middot; MC: {$mcRiloc}</div>
                    " . _tabellaCase($temaRiloc['case'] ?? null, 'Case Placido &mdash; rilocato') . "
                </td>
            </tr></table>
        </div>";
    }

    $footer = "
    <div class='report-footer'>
        Astrologia Attiva &middot; Scuola di Ciro Discepolo
        &middot; Generato il {$dataOggi}
        &middot; Swiss Ephemeris AGPL &middot; Uso personale/familiare
    </div>";

    return "<!DOCTYPE html>
<html lang='it'>
<head>
    <meta charset='UTF-8'>
    <title>Report Astrologico &mdash; {$nomeSoggetto}</title>
    <style>{$css}</style>
</head>
<body>
    {$header}
    {$body}
    {$footer}
</body>
</html>";
}

function _getCSSInline(): string {
    return "
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-size: 9pt;
        color: #2C2C2C;
        background: white;
        line-height: 1.4;
    }
    .report-header {
        border-bottom: 2pt solid #2C3E6B;
        padding-bottom: 8pt;
        margin-bottom: 12pt;
    }
    .report-titolo {
        font-size: 16pt;
        font-weight: bold;
        color: #2C3E6B;
        letter-spacing: 0.06em;
    }
    .report-sottotitolo {
        font-size: 8pt;
        color: #666;
        margin-top: 2pt;
    }
    .report-soggetto {
        font-size: 13pt;
        font-weight: bold;
        color: #2C3E6B;
    }
    .report-dati {
        font-size: 8pt;
        color: #666;
        margin-top: 2pt;
    }
    .ruota-title {
        font-size: 9pt;
        font-weight: bold;
        color: #2C3E6B;
        text-align: center;
        background: #F2EDE4;
        padding: 3pt 6pt;
        margin-bottom: 4pt;
    }
    .ruota-img-wrap {
        text-align: center;
    }
    .ruota-img-wrap img {
        width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    .ruota-info {
        font-size: 8pt;
        text-align: center;
        color: #666;
        margin: 3pt 0 6pt;
    }
    .ruote-affiancate-pdf {
        width: 100%;
        border-collapse: collapse;
    }
    .ruote-affiancate-pdf td { vertical-align: top; }
    .report-tabella-wrap { margin-top: 6pt; }
    .report-tabella-title {
        font-size: 8pt;
        font-weight: bold;
        text-transform: uppercase;
        color: #2C3E6B;
        letter-spacing: 0.04em;
        margin-bottom: 2pt;
    }
    .report-tabella-pianeti {
        width: 100%;
        border-collapse: collapse;
        font-size: 7pt;
    }
    .report-tabella-pianeti th {
        background-color: #2C3E6B;
        color: #D4C9A8;
        padding: 2pt 4pt;
        text-align: left;
        font-size: 7pt;
    }
    .report-tabella-pianeti td {
        padding: 2pt 4pt;
        border-bottom: 0.5pt solid #EDE8E0;
    }
    .report-valutazione {
        background: #F8F6F0;
        border: 0.5pt solid #D0C8BC;
        padding: 5pt 7pt;
        margin: 5pt 0;
    }
    .val-header-print { margin-bottom: 4pt; }
    .stelle-print  { font-size: 12pt; color: #C8960C; }
    .val-str-print {
        font-family: Courier, monospace;
        font-size: 9pt;
        color: #2C3E6B;
        background: #EEF0FF;
        padding: 1pt 4pt;
    }
    .val-cond-print { font-size: 8pt; color: #666; font-style: italic; }
    .val-body-print { font-size: 7pt; }
    .val-item-print {
        padding: 1pt 4pt;
        margin-bottom: 1pt;
        font-size: 7pt;
    }
    .val-veto-print  { background: #FFEBEE; color: #B71C1C; border-left: 2pt solid #F44336; }
    .val-bonus-print { background: #E8F5E9; color: #2E7D32; border-left: 2pt solid #4CAF50; }
    .val-pen-print   { background: #FFF3E0; color: #E65100; border-left: 2pt solid #FF9800; }
    .report-section {
        margin-top: 10pt;
        padding-top: 8pt;
        border-top: 0.5pt solid #D0C8BC;
    }
    .report-section-title {
        font-size: 11pt;
        font-weight: bold;
        color: #2C3E6B;
        margin-bottom: 8pt;
    }
    .report-footer {
        margin-top: 16pt;
        padding-top: 5pt;
        border-top: 0.5pt solid #D0C8BC;
        font-size: 7pt;
        color: #888;
        text-align: center;
    }
    ";
}

function _paginaErroreDompdf(): string {
    return "<!DOCTYPE html>
<html lang='it'><head><meta charset='UTF-8'><title>Dompdf non installato</title>
<style>body{font-family:Verdana,sans-serif;background:#F2EDE4;display:flex;
align-items:center;justify-content:center;min-height:100vh;margin:0}
.box{background:white;border-radius:10px;padding:40px;max-width:540px;
box-shadow:0 4px 20px rgba(0,0,0,.15)}h2{color:#2C3E6B}pre{background:#F8F8F8;
padding:12px;border-radius:6px;font-size:13px;overflow:auto}</style></head>
<body><div class='box'>
<h2>📦 Dompdf non installato</h2>
<p style='margin:16px 0;color:#444;line-height:1.7'>
Per generare PDF nativi è necessario installare <strong>Dompdf</strong>
nel container Docker del progetto.</p>
<p style='color:#444;margin-bottom:8px'>Esegui nel container <code>astro-web</code>:</p>
<pre>docker compose exec astro-web bash
cd /var/www/html
composer require dompdf/dompdf:^2.0</pre>
<p style='margin-top:16px;color:#444;line-height:1.7'>
In alternativa, usa il pulsante <strong>\"Stampa da Browser\"</strong> nella
pagina Report e seleziona \"Salva come PDF\" nella finestra di stampa del browser
(Chrome e Firefox supportano questa funzione nativamente).</p>
<p style='margin-top:20px'><a href='javascript:window.close()'
style='color:#2C3E6B'>← Chiudi questa scheda</a></p>
</div></body></html>";
}