<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * rilocazione.php — Tema Natale Rilocato
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * La rilocazione mantiene invariate le longitudini zodiacali di tutti i pianeti
 * e ricalcola solamente le case Placido per la nuova latitudine/longitudine.
 * Si usa tema_api.php?tipo=natale con lat/lon della città target.
 */
session_start();
require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();

$isAdmin        = $auth->isAdmin();
$username       = $auth->getCurrentUsername();
$soggettoAttivo = $auth->getSoggettoAttivo();
$soggettoNome   = $auth->getSoggettoNome();

$id = intval($_GET['id'] ?? $soggettoAttivo ?? 0);
if ($id > 0) { $auth->setSoggettoAttivo($id); $soggettoNome = $auth->getSoggettoNome(); }

$soggetto = null;
if ($id) {
    $soggetto = $auth->verificaSoggetto($id);
}

// Coordinate rilocazione da URL (es. link da ricerca_stream_api)
$latRiloc_Url   = isset($_GET['lat_riloc'])   ? (float)$_GET['lat_riloc']  : null;
$lonRiloc_Url   = isset($_GET['lon_riloc'])   ? (float)$_GET['lon_riloc']  : null;
$luogoRiloc_Url = $_GET['luogo_riloc']        ?? null;

$jsData = null;
if ($soggetto) {
    $date   = new DateTime($soggetto['data_nascita']);
    $oraGmt = explode(':', $soggetto['ora_nascita_gmt']);
    $jsData = [
        'id'          => $soggetto['id'],
        'nome'        => $soggetto['nome'],
        'giorno'      => (int)$date->format('d'),
        'mese'        => (int)$date->format('m'),
        'anno'        => (int)$date->format('Y'),
        'ora_gmt'     => (int)$oraGmt[0] + (int)$oraGmt[1] / 60,
        'lat'         => (float)$soggetto['latitudine'],
        'lon'         => (float)$soggetto['longitudine'],
        'luogo'       => $soggetto['luogo_nascita'],
        'nazione'     => $soggetto['nazione_nascita'],
        // Coordinate rilocazione default: residenza se presente, altrimenti nascita
        'riloc_lat'   => $latRiloc_Url  ?? ($soggetto['residenza_latitudine']  ? (float)$soggetto['residenza_latitudine']  : (float)$soggetto['latitudine']),
        'riloc_lon'   => $lonRiloc_Url  ?? ($soggetto['residenza_longitudine'] ? (float)$soggetto['residenza_longitudine'] : (float)$soggetto['longitudine']),
        'riloc_luogo' => $luogoRiloc_Url ?? ($soggetto['residenza_luogo']
                         ? $soggetto['residenza_luogo'] . ($soggetto['residenza_nazione'] ? ', ' . $soggetto['residenza_nazione'] : '')
                         : ''),
    ];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rilocazione — Astrologia Attiva</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/print.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Symbols+2&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="js/svg_zoom.js"></script>
    <style>
        /* ── Tabella comparativa case ──────────────────────────────── */
        .tabella-comparativa {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 8px;
        }
        .tabella-comparativa th {
            background: #F2EDE4;
            padding: 5px 10px;
            color: #2C3E6B;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: normal;
            border-bottom: 2px solid #D0C8BC;
            text-align: center;
        }
        .tabella-comparativa td {
            padding: 4px 10px;
            border-bottom: 1px solid #EDE8E0;
            text-align: center;
            vertical-align: middle;
        }
        .tabella-comparativa tr:hover td { background: #FAF7F3; }

        /* Evidenzia le righe dove la casa cambia */
        .tabella-comparativa tr.casa-cambia td {
            background: #FFF8E1;
            font-weight: 500;
        }
        .tabella-comparativa tr.casa-cambia td:first-child {
            border-left: 3px solid #FF9800;
        }

        /* Badge delta casa */
        .delta-casa {
            display: inline-block;
            font-size: 10px;
            padding: 1px 6px;
            border-radius: 10px;
            white-space: nowrap;
        }
        .delta-casa.positivo { background: #E8F5E9; color: #2E7D32; }
        .delta-casa.negativo { background: #FFEBEE; color: #B71C1C; }
        .delta-casa.uguale   { color: #999; font-size: 10px; }

        /* ── Header rilocazione ─────────────────────────────────────── */
        .header-riloc {
            background: #3a2c6b;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            margin-bottom: 12px;
            font-size: 12px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .header-riloc span { color: #b8a8d8; }
        .header-riloc b    { color: #e8d4ff; }

        /* ── Loading overlay ruota rilocazione ──────────────────────── */
        .riloc-loading-overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.8);
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 8px;
            font-size: 13px;
            color: #3a2c6b;
        }
        .riloc-loading-overlay.visible { display: flex; }

        /* ── Nota informativa ───────────────────────────────────────── */
        .riloc-nota {
            background: #F0F4FF;
            border: 1px solid #C8D4F0;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 12px;
            color: #3A5090;
            margin-bottom: 14px;
            line-height: 1.6;
        }
        .riloc-nota strong { color: #2C3E6B; }

        /* ── Pulsante Mappa ─────────────────────────────────────────── */
        .btn-mappa {
            background: #2C3E6B;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-mappa:hover { background: #1a2a4a; }
        .btn-mappa.active { background: #6B2C3E; }

        /* ── Mappa fluttuante ───────────────────────────────────────── */
        .map-float-win {
            position: fixed;
            top: 60px;
            right: 30px;
            width: 480px;
            height: 420px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.35);
            z-index: 9999;
            display: none;
            flex-direction: column;
            border: 1px solid #D0C8BC;
            overflow: hidden;
            resize: both;
            min-width: 320px;
            min-height: 280px;
        }
        .map-float-win.visible { display: flex; }

        .map-float-header {
            background: #3a2c6b;
            color: white;
            padding: 8px 14px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            cursor: grab;
            user-select: none;
        }
        .map-float-header:active { cursor: grabbing; }
        .map-float-header h3 {
            margin: 0;
            font-size: 13px;
            font-weight: 500;
            flex: 1;
        }
        .map-float-coords {
            font-size: 11px;
            color: #b8a8d8;
            font-family: monospace;
            white-space: nowrap;
        }
        .btn-close-float {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0 4px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .btn-close-float:hover { opacity: 1; }

        .map-leaflet-inner {
            flex: 1;
            position: relative;
            min-height: 0;
            background: #f0f0f0;
        }
        #leaflet-map {
            width: 100%;
            height: 100%;
        }
        .map-loading-overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.75);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            font-size: 13px;
            color: #3a2c6b;
            font-weight: 500;
        }
        .map-loading-overlay.visible { display: flex; }

        .map-float-footer {
            padding: 6px 14px;
            background: #F8F6F2;
            border-top: 1px solid #E8E4DC;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            font-size: 11px;
            color: #888;
        }
        .btn-usa-pos {
            background: #3a2c6b;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 14px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-usa-pos:hover { background: #2a1c5b; }

        /* ── Controlli con mappa ────────────────────────────────────── */
        .controlli-riloc {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            margin-bottom: 14px;
            background: #FAF8F4;
            padding: 14px 18px;
            border-radius: 8px;
            border: 1px solid #EDE8E0;
        }
        .controlli-riloc .form-group {
            margin: 0;
            flex: 1 1 0;
            min-width: 80px;
        }
        .controlli-riloc .form-group label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #888;
            display: block;
            margin-bottom: 2px;
        }
        .controlli-riloc .form-group input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #D0C8BC;
            border-radius: 4px;
            font-size: 13px;
            background: white;
        }
        .controlli-riloc .form-group input:focus {
            border-color: #3a2c6b;
            outline: none;
        }
        .controlli-riloc .btn-mappa {
            padding: 6px 16px;
            font-size: 12px;
            align-self: center;
        }
        .btn-search {
            background: #D0C8BC;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-search:hover { background: #C0B8AC; }
        .btn-primary {
            background: #3a2c6b;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-primary:hover { background: #2a1c5b; }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

        .luogo-ricerca-wrap {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .luogo-ricerca-wrap input {
            flex: 1;
            min-width: 120px;
        }

        .dropdown-risultati {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #D0C8BC;
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .dropdown-risultati.visible { display: block; }
        .dropdown-item {
            padding: 6px 12px;
            cursor: pointer;
            font-size: 12px;
            border-bottom: 1px solid #F0ECE6;
        }
        .dropdown-item:hover { background: #F0EDE8; }

        .temi-wrapper {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .tema-box {
            flex: 1 1 400px;
            max-width: 500px;
            background: white;
            border-radius: 8px;
            padding: 14px 16px 18px;
            border: 1px solid #E8E4DC;
            position: relative;
        }
        .tema-box-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .tema-box-header h3 {
            margin: 0;
            font-size: 14px;
            color: #2C3E6B;
            font-weight: 500;
        }
        .btn-toggle-gradi {
            background: none;
            border: 1px solid #D0C8BC;
            border-radius: 4px;
            padding: 2px 10px;
            font-size: 10px;
            cursor: pointer;
            color: #667;
            transition: all 0.2s;
        }
        .btn-toggle-gradi:hover { background: #F0EDE8; }
        .btn-toggle-gradi.attivo { background: #3a2c6b; color: white; border-color: #3a2c6b; }

        .tema-info {
            font-size: 11px;
            color: #888;
            text-align: center;
            margin: 6px 0 10px;
        }
        .tabella-pianeti {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .tabella-pianeti th {
            text-align: left;
            padding: 3px 6px;
            font-weight: 500;
            color: #888;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .tabella-pianeti td {
            padding: 3px 6px;
            border-bottom: 1px solid #F0ECE6;
        }
        .tabella-pianeti tr:hover td { background: #FAF8F4; }
        .retro { color: #CC3333; font-weight: bold; font-size: 10px; }

        .card {
            background: white;
            border-radius: 8px;
            padding: 16px 20px;
            border: 1px solid #E8E4DC;
            margin-top: 14px;
        }
        .card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #2C3E6B;
            font-weight: 500;
        }
        .empty { color: #999; font-size: 13px; text-align: center; padding: 30px 0; }
        .header-soggetto {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 24px;
            background: #F8F6F2;
            border-radius: 8px;
            padding: 10px 18px;
            margin-bottom: 16px;
            font-size: 12px;
            border: 1px solid #EDE8E0;
        }
        .header-soggetto span { color: #999; }
        .header-soggetto b { color: #2C3E6B; }

        .form-group {
            position: relative;
        }

        /* ── Sezione ricerca angolari (PATCH BLOCCO 1) ──────────────── */
        #card-ricerca-angolari {
            background: white;
            border-radius: 8px;
            padding: 16px 20px;
            border: 1px solid #E8E4DC;
            margin-top: 20px;
        }

        .angolari-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #E0D8CC;
        }

        .angolari-header h3 {
            font-size: 14px;
            color: #3a2c6b;
            font-weight: 500;
            margin: 0;
            border: none;
            padding: 0;
        }

        .angolari-desc {
            font-size: 12px;
            color: #667;
            line-height: 1.55;
            margin-bottom: 14px;
            background: #F8F5FF;
            border: 1px solid #E0D0FF;
            border-radius: 6px;
            padding: 10px 14px;
        }

        .angolari-controlli {
            display: flex;
            gap: 14px;
            align-items: flex-end;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .angolari-controlli .form-group {
            margin: 0;
        }

        .angolari-controlli label {
            font-size: 10px;
            color: #5A6A8A;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: block;
            margin-bottom: 3px;
        }

        .angolari-controlli select,
        .angolari-controlli input[type="number"] {
            border: 1px solid #C0BAB0;
            border-radius: 4px;
            padding: 5px 8px;
            font-size: 13px;
            font-family: inherit;
            background: white;
        }

        .btn-cerca-angolari {
            background: #6B3EB0;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
            font-family: inherit;
        }
        .btn-cerca-angolari:hover { background: #5a2da0; }
        .btn-cerca-angolari:disabled { opacity: 0.6; cursor: not-allowed; }

        /* Barra progress angolari */
        #angolari-progress-wrap {
            display: none;
            margin-bottom: 14px;
        }
        .angolari-progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #3a2c6b;
            margin-bottom: 6px;
        }
        .angolari-bar-outer {
            background: #EDE8E0;
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .angolari-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #6B3EB0, #A87FD8);
            border-radius: 4px;
            width: 0%;
            transition: width 0.3s ease;
        }
        .angolari-progress-sub {
            font-size: 11px;
            color: #888;
            font-style: italic;
        }

        /* Tabella risultati angolari */
        .tabella-angolari {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .tabella-angolari th {
            background: #3a2c6b;
            color: #E8D8FF;
            padding: 8px 10px;
            text-align: left;
            font-weight: normal;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .tabella-angolari td {
            padding: 7px 10px;
            border-bottom: 1px solid #EDE8E0;
            vertical-align: middle;
        }

        .tabella-angolari tr:hover td { background: #FAF5FF; }

        /* Badge pianeta */
        .badge-venere {
            display: inline-block;
            background: #FFE8F5;
            color: #8B1A5C;
            border: 1px solid #F0A0D0;
            border-radius: 10px;
            font-size: 10px;
            padding: 2px 8px;
            white-space: nowrap;
            margin: 1px 2px;
        }
        .badge-giove {
            display: inline-block;
            background: #E8F0FF;
            color: #1a3a8B;
            border: 1px solid #A0C0F0;
            border-radius: 10px;
            font-size: 10px;
            padding: 2px 8px;
            white-space: nowrap;
            margin: 1px 2px;
        }

        /* Badge casa angolare */
        .badge-casa-ang {
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 4px;
            background: #F0EBF8;
            color: #5a1aaa;
            margin-right: 3px;
        }

        /* Distanza numerica colorata */
        .dist-num {
            font-family: monospace;
            font-size: 11px;
        }
        .dist-vicino  { color: #1B5E20; font-weight: bold; }   /* ≤ 1° */
        .dist-medio   { color: #E65100; }                       /* 1-2° */
        .dist-lontano { color: #888; }                          /* > 2° */

        /* Bottone "Usa per rilocazione" */
        .btn-usa-riloc {
            background: #3a2c6b;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 12px;
            font-size: 11px;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .btn-usa-riloc:hover { background: #5a4a8b; }

        /* Empty state */
        .angolari-empty {
            text-align: center;
            padding: 40px 0;
            color: #888;
            font-size: 13px;
        }

        /* Info pianeti (subheader dopo avvio ricerca) */
        .angolari-info-pianeti {
            display: none;
            font-size: 12px;
            color: #5A6A8A;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: #F5F0FF;
            border-radius: 6px;
            border: 1px solid #D8C8F8;
        }

        /* ── Stili Paginazione Aggiuntivi ───────────────────────────── */
        .angolari-paginazione {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #EDE8E0;
            font-size: 12px;
        }
        .angolari-pag-btn {
            background: #3a2c6b;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 12px;
            cursor: pointer;
            font-size: 12px;
        }
        .angolari-pag-btn:disabled {
            background: #D0C8BC;
            cursor: not-allowed;
        }
        .angolari-limite-select {
            border: 1px solid #C0BAB0;
            border-radius: 4px;
            padding: 3px 6px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<?php $paginaAttiva = 'rilocazione'; include 'includes/header_nav.php'; ?>

<main>
<?php if (!$soggetto): ?>
    <div class="card">
        <p class="empty">Seleziona un soggetto dalla <a href="index.php">lista soggetti</a>.</p>
    </div>
<?php else: ?>

    <div class="header-soggetto">
        <div><span>Soggetto: </span><b><?= htmlspecialchars($soggetto['nome']) ?></b></div>
        <div><span>Nato il: </span><b><?= date('d/m/Y', strtotime($soggetto['data_nascita'])) ?></b></div>
        <div><span>Ore: </span><b><?= substr($soggetto['ora_nascita'],0,5) ?> (loc.) — <?= substr($soggetto['ora_nascita_gmt'],0,5) ?> GMT</b></div>
        <div><span>Nascita: </span><b><?= htmlspecialchars($soggetto['luogo_nascita'].' '.$soggetto['nazione_nascita']) ?></b></div>
    </div>

    <div class="riloc-nota">
        ☿ <strong>Rilocazione:</strong> le posizioni zodiacali dei pianeti restano
        <strong>identiche al Tema Natale</strong>. Cambiano solo Ascendente, MC e le
        cuspidi delle case calcolate for la nuova città. Questo permette di vedere
        come si "riposizionano" i pianeti nelle case vivendo altrove.
    </div>

    <div class="controlli-riloc">
        <div class="form-group" style="flex:2;min-width:200px;position:relative">
            <label>Città di Rilocazione</label>
            <div class="luogo-ricerca-wrap">
                <input type="text" id="riloc-luogo-input"
                       placeholder="Cerca città..."
                       value="<?= htmlspecialchars($jsData['riloc_luogo'] ?? '') ?>"
                       onkeydown="if(event.key==='Enter') cercaLuogoRiloc()">
                <button class="btn-search" onclick="cercaLuogoRiloc()">🔍 Cerca</button>
            </div>
            <div id="riloc-risultati" class="dropdown-risultati"></div>
        </div>
        <div class="form-group">
            <label>Lat</label>
            <input type="number" id="riloc-lat" step="0.0001"
                   value="<?= htmlspecialchars($jsData['riloc_lat'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Lon</label>
            <input type="number" id="riloc-lon" step="0.0001"
                   value="<?= htmlspecialchars($jsData['riloc_lon'] ?? '') ?>">
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button class="btn-primary" onclick="calcolaRilocazione()">☿ Calcola Rilocazione</button>
            <button class="btn-mappa" id="btn-apri-mappa" onclick="toggleMappa()" style="display:none">🌍 Mappa</button>
        </div>
    </div>

    <div class="header-riloc" id="header-riloc" style="display:none">
        <div><span>Rilocazione: </span><b id="riloc-citta-label">—</b></div>
        <div><span>ASC: </span><b id="riloc-asc-label">—</b></div>
        <div><span>MC: </span><b id="riloc-mc-label">—</b></div>
        <div><span>ASC natale: </span><b id="riloc-asc-natale-label">—</b></div>
        <div><span>MC natale: </span><b id="riloc-mc-natale-label">—</b></div>
    </div>
    <div class="page-title" style="margin-bottom:10px">
    <button class="btn-stampa-diretta" onclick="stampaPagina('print-riloc')">🖨️ Stampa Rilocazione</button>
    </div>
    <div class="temi-wrapper" id="temi-wrapper">

        <div class="tema-box">
            <div class="tema-box-header">
                <button class="btn-toggle-gradi" id="btn-toggle-cuspidi"
                        onclick="toggleCuspidiCase()">Nascondi Cuspidi</button>
                <h3>Tema Natale</h3>
                <button class="btn-toggle-gradi" id="btn-toggle-gradi"
                        onclick="toggleGradiPianeti()">Mostra Gradi</button>
            </div>
            <svg id="wheel-natale" width="480" height="480"
                 style="max-width:100%;height:auto"></svg>
            <p class="tema-info" id="info-natale">Caricamento...</p>
            <table class="tabella-pianeti" id="tab-natale"></table>
        </div>

        <div class="tema-box" style="position:relative">
            <div class="riloc-loading-overlay" id="riloc-overlay">⟳ Calcolo rilocazione...</div>
            <div class="tema-box-header">
                <button class="btn-toggle-gradi" id="btn-toggle-cuspidi-r"
                        onclick="toggleCuspidiCaseRiloc()">Nascondi Cuspidi</button>
                <h3 id="riloc-titolo">Rilocazione</h3>
                <button class="btn-toggle-gradi" id="btn-toggle-gradi-r"
                        onclick="toggleGradiPianetiRiloc()">Mostra Gradi</button>
            </div>
            <svg id="wheel-riloc" width="480" height="480"
                 style="max-width:100%;height:auto"></svg>
            <p class="tema-info" id="info-riloc">Inserisci una città e premi Calcola.</p>
            <table class="tabella-pianeti" id="tab-riloc"></table>
        </div>
    </div>

    <div id="card-ricerca-angolari">

        <div class="angolari-header">
            <h3>♀♃ Cerca Luoghi — Venere o Giove sulle Cuspidi Angolari</h3>
        </div>

        <div class="angolari-desc">
            Ricerca mondiale dei luoghi in cui — con il <strong>tema natale rilocato</strong> —
            <strong>Venere (♀)</strong> e/o <strong>Giove (♃)</strong> cadono entro la tolleranza
            impostata da una cuspide angolare (<strong>I · IV · VII · X</strong> Casa).<br>
            Le posizioni zodiacali dei pianeti restano invariate rispetto al natale;
            cambiano solo le case Placido in funzione del luogo.
        </div>

        <div class="angolari-info-pianeti" id="angolari-info-pianeti">
            ♀ Venere natale: <b id="ap-venere">—</b>
            &nbsp;&nbsp;&nbsp;
            ♃ Giove natale: <b id="ap-giove">—</b>
            &nbsp;&nbsp;&nbsp;
            Tolleranza: <b id="ap-tol">—</b>
        </div>

        <div class="angolari-controlli">
            <div class="form-group">
                <label>Tolleranza (gradi ±)</label>
                <input type="number" id="ang-tolleranza" min="0.5" max="2.5" step="0.5" value="2.5"
                       style="width:80px">
            </div>
            <div class="form-group">
                <label>Mostra elementi</label>
                <select id="ang-pagine-limite" onchange="window.aggiornaPaginazioneAngolari()">
                    <option value="20">20 per pagina</option>
                    <option value="50" selected>50 per pagina</option>
                    <option value="100">100 per pagina</option>
                </select>
            </div>
            <div class="form-group">
                <label>Aeroporti</label>
                <select id="ang-tipo-ricerca">
                    <option value="large_medium">Grandi + Medi</option>
                    <option value="iata_only">Solo IATA</option>
                    <option value="tutti">Tutti (lento)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Militari</label>
                <select id="ang-escludi-militari">
                    <option value="1">Escludi</option>
                    <option value="0">Includi</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nazione (opz.)</label>
                <input type="text" id="ang-nazione" placeholder="es. IT" maxlength="2"
                       style="width:60px;text-transform:uppercase">
            </div>
            <button class="btn-cerca-angolari" id="btn-cerca-angolari"
                    onclick="avviaRicercaAngolari()">
                🔍 CERCA
            </button>
        </div>

        <div id="angolari-progress-wrap">
            <div class="angolari-progress-header">
                <span id="ang-progress-label">Inizializzazione...</span>
                <span id="ang-progress-perc">0%</span>
            </div>
            <div class="angolari-bar-outer">
                <div class="angolari-bar-fill" id="ang-bar-fill"></div>
            </div>
            <div class="angolari-progress-sub" id="ang-progress-sub">—</div>
        </div>

        <div id="angolari-risultati-area" style="display:none; max-height:550px; overflow-y:auto; border:1px solid #EDE8E0; padding:10px; border-radius:6px; margin-top:15px;">
        </div>

    </div>

    <div class="card" id="card-comparativa" style="display:none">
        <h3>☿ Confronto Case — Natale vs Rilocazione</h3>
        <p style="font-size:11px;color:#888;margin-bottom:10px">
            Le posizioni zodiacali sono identiche. Evidenziate in giallo le case che cambiano.
        </p>
        <div style="overflow-x:auto">
            <table class="tabella-comparativa" id="tab-comparativa">
                <thead>
                    <tr>
                        <th>Pianeta</th>
                        <th>Posizione Zodiacale</th>
                        <th>Casa Natale</th>
                        <th>Casa Rilocata</th>
                        <th>Δ</th>
                    </tr>
                </thead>
                <tbody id="tbody-comparativa"></tbody>
            </table>
        </div>
        <div class="cuspidi-container" style="margin-top:20px">
            <h4 style="font-size:11px;color:#3a2c6b;text-align:center;margin-bottom:8px">
                🏠 Cuspidi Case — Confronto
            </h4>
            <div style="overflow-x:auto">
                <table class="tabella-comparativa" id="tab-cuspidi-comp">
                    <thead>
                        <tr>
                            <th>Casa</th>
                            <th>Cuspide Natale</th>
                            <th>Cuspide Rilocata</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-cuspidi-comp"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="mappa-float" class="map-float-win">
        <div class="map-float-header" id="mappa-drag-handle">
            <h3>🌍 Mappa Rilocazione — trascina il marker</h3>
            <span class="map-float-coords" id="mappa-coords">—</span>
            <button class="btn-close-float" onclick="chiudiMappa()" title="Chiudi">✕</button>
        </div>
        <div class="map-leaflet-inner">
            <div class="map-loading-overlay" id="mappa-ricalcolo">⟳ Ricalcolo...</div>
            <div id="leaflet-map" style="width:100%;height:100%"></div>
        </div>
        <div class="map-float-footer">
            <span class="info-drag">Trascina il marker · la rilocazione si aggiorna in tempo reale</span>
            <button class="btn-usa-pos" onclick="usaPosizioneMappa()">✓ Usa questa posizione</button>
        </div>
    </div>

<?php endif; ?>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/zodiac_wheel.js"></script>
<script src="js/app.js"></script>
<?php if ($soggetto && $jsData): ?>
<script>
'use strict';

const DS = <?= json_encode($jsData) ?>;

// ── Stato delle istanze ZodiacWheel per la ruota rilocata ──────────────
// La ruota rilocata ha controlli toggle indipendenti da quella natale.
// Usiamo un secondo oggetto che replica lo stato separato.
const WheelRiloc = {
    _gradiVisibili:   false,
    _cuspidiVisibili: true,

    applicaGradi: function() {
        document.querySelectorAll('#wheel-riloc .grado-pianeta').forEach(el => {
            el.style.display = this._gradiVisibili ? 'inline' : 'none';
        });
    },
    applicaCuspidi: function() {
        document.querySelectorAll('#wheel-riloc .grado-cuspide').forEach(el => {
            el.style.display = this._cuspidiVisibili ? 'inline' : 'none';
        });
    },
    toggleGradi: function() {
        this._gradiVisibili = !this._gradiVisibili;
        this.applicaGradi();
        const btn = document.getElementById('btn-toggle-gradi-r');
        if (btn) {
            btn.textContent = this._gradiVisibili ? 'Nascondi Gradi' : 'Mostra Gradi';
            btn.classList.toggle('attivo', this._gradiVisibili);
        }
    },
    toggleCuspidi: function() {
        this._cuspidiVisibili = !this._cuspidiVisibili;
        this.applicaCuspidi();
        const btn = document.getElementById('btn-toggle-cuspidi-r');
        if (btn) {
            btn.textContent = this._cuspidiVisibili ? 'Nascondi Cuspidi' : 'Mostra Cuspidi';
            btn.classList.toggle('attivo', !this._cuspidiVisibili);
        }
    }
};

// Espone i toggle per la ruota rilocata (onclick nei pulsanti)
window.toggleGradiPianetiRiloc = () => WheelRiloc.toggleGradi();
window.toggleCuspidiCaseRiloc  = () => WheelRiloc.toggleCuspidi();

// ── Cache tema natale ────────────────────────────────────────────────────
let temaNataleCache = null;

// Nomi pianeti (allineati con SweCalc::PIANETI)
const NOMI_PIANETI = {
    0:'☉ Sole', 1:'☽ Luna', 2:'☿ Mercurio', 3:'♀ Venere', 4:'♂ Marte',
    5:'♃ Giove', 6:'♄ Saturno', 7:'♅ Urano', 8:'♆ Nettuno', 9:'♇ Plutone',
    11:'☊ Nodo N.',
};

const CASE_LABEL = {
    1:'I (ASC)', 2:'II', 3:'III', 4:'IV (FC)', 5:'V', 6:'VI',
    7:'VII (DSC)', 8:'VIII', 9:'IX', 10:'X (MC)', 11:'XI', 12:'XII'
};

// ── Mappa fluttuante ─────────────────────────────────────────────────────
let leafletMap    = null;
let mapMarker     = null;
let mappaAperta   = false;
let ricalcoloTimer= null;
let posMappa      = null;

// ── Drag della finestra ──────────────────────────────────────────────────
(function initDrag() {
    const win    = document.getElementById('mappa-float');
    const handle = document.getElementById('mappa-drag-handle');
    if (!win || !handle) return;
    let dragging = false, offX = 0, offY = 0;

    handle.addEventListener('mousedown', e => {
        if (e.target.classList.contains('btn-close-float')) return;
        dragging = true;
        const rect = win.getBoundingClientRect();
        if (win.style.position !== 'fixed') {
            win.style.position = 'fixed';
            win.style.right    = 'auto';
        }
        win.style.left = rect.left + 'px';
        win.style.top  = rect.top  + 'px';
        offX = e.clientX - rect.left;
        offY = e.clientY - rect.top;
        e.preventDefault();
    });

    document.addEventListener('mousemove', e => {
        if (!dragging) return;
        win.style.left = (e.clientX - offX) + 'px';
        win.style.top  = (e.clientY - offY) + 'px';
        if (leafletMap) leafletMap.invalidateSize();
    });

    document.addEventListener('mouseup', () => { dragging = false; });
})();

new ResizeObserver(() => {
    if (leafletMap && mappaAperta) leafletMap.invalidateSize();
}).observe(document.getElementById('mappa-float'));

// ── Toggle mappa ─────────────────────────────────────────────────────────
function toggleMappa() {
    if (mappaAperta) { chiudiMappa(); return; }
    apriMappa();
}

function apriMappa() {
    const lat = parseFloat(document.getElementById('riloc-lat').value) || 0;
    const lon = parseFloat(document.getElementById('riloc-lon').value) || 0;
    posMappa = {lat, lon};

    const win = document.getElementById('mappa-float');
    win.classList.add('visible');
    mappaAperta = true;

    const btn = document.getElementById('btn-apri-mappa');
    if (btn) { btn.textContent = '🗺️ Chiudi Mappa'; btn.classList.add('active'); }

    if (!leafletMap) {
        setTimeout(() => {
            leafletMap = L.map('leaflet-map').setView([lat, lon], 5);
            L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18
            }).addTo(leafletMap);
            mapMarker = L.marker([lat, lon], {draggable: true}).addTo(leafletMap);

            mapMarker.on('drag', e => {
                const p = e.target.getLatLng();
                posMappa = {lat: p.lat, lon: p.lng};
                document.getElementById('mappa-coords').textContent =
                    p.lat.toFixed(4) + '°  ' + p.lng.toFixed(4) + '°';
            });

            mapMarker.on('dragend', e => {
                const p = e.target.getLatLng();
                posMappa = {lat: p.lat, lon: p.lng};
                document.getElementById('mappa-coords').textContent =
                    p.lat.toFixed(4) + '°  ' + p.lng.toFixed(4) + '°';
                clearTimeout(ricalcoloTimer);
                ricalcoloTimer = setTimeout(() => {
                    // Aggiorna i campi con le coordinate della mappa
                    document.getElementById('riloc-lat').value = posMappa.lat.toFixed(4);
                    document.getElementById('riloc-lon').value = posMappa.lon.toFixed(4);
                    calcolaRilocazioneDaMappa();
                }, 280);
            });

            leafletMap.invalidateSize();
        }, 80);

    } else {
        mapMarker.setLatLng([lat, lon]);
        leafletMap.setView([lat, lon], leafletMap.getZoom());
        setTimeout(() => leafletMap.invalidateSize(), 60);
    }

    document.getElementById('mappa-coords').textContent =
        lat.toFixed(4) + '°  ' + lon.toFixed(4) + '°';
}

function chiudiMappa() {
    const win = document.getElementById('mappa-float');
    win.classList.remove('visible');
    mappaAperta = false;
    clearTimeout(ricalcoloTimer);

    const btn = document.getElementById('btn-apri-mappa');
    if (btn) { btn.textContent = '🌍 Mappa'; btn.classList.remove('active'); }
}

function usaPosizioneMappa() {
    if (!posMappa) return;
    document.getElementById('riloc-lat').value = posMappa.lat.toFixed(4);
    document.getElementById('riloc-lon').value = posMappa.lon.toFixed(4);
    // Aggiorna il campo città con un placeholder generico
    const input = document.getElementById('riloc-luogo-input');
    if (input && !input.value.trim()) {
        input.value = posMappa.lat.toFixed(4) + '° ' + posMappa.lon.toFixed(4) + '°';
    }
    chiudiMappa();
    calcolaRilocazione();
}

/**
 * Aggiorna (se presente in pagina) il link al report/stampa della
 * rilocazione. rilocazione.php non ha un pulsante "Report" come rs.php,
 * quindi la funzione è no-op finché non viene aggiunto l'elemento HTML
 * corrispondente — evita solo l'errore "is not defined".
 */
function _aggiornaLinkReportRiloc(lat, lon, luogo) {
    const wrap = document.getElementById('riloc-btn-report-wrap');
    const link = document.getElementById('riloc-btn-report');
    if (!wrap || !link || typeof DS === 'undefined') return;

    const params = new URLSearchParams({
        id:          DS.id,
        lat_riloc:   lat,
        lon_riloc:   lon,
        luogo_riloc: luogo,
    });
    link.href = 'stampa.php?' + params.toString();
    wrap.style.display = 'block';
}

// Versione speciale di calcolaRilocazione via mappa (evita di ricaricare il tema natale)
function calcolaRilocazioneDaMappa() {
    const lat   = parseFloat(document.getElementById('riloc-lat').value);
    const lon   = parseFloat(document.getElementById('riloc-lon').value);
    const luogo = document.getElementById('riloc-luogo-input').value.trim() || 'Posizione mappa';

    if (isNaN(lat) || isNaN(lon)) return;

    // Mostra overlay di caricamento sulla ruota rilocata
    document.getElementById('riloc-overlay').classList.add('visible');

    const url = 'api/tema_api.php?tipo=natale' +
        '&g='       + DS.giorno +
        '&m='       + DS.mese +
        '&a='       + DS.anno +
        '&ora_gmt=' + DS.ora_gmt +
        '&lat='     + lat +
        '&lon='     + lon;

    // Mostra il loading overlay sulla mappa
    document.getElementById('mappa-ricalcolo').classList.add('visible');

    fetch(url)
        .then(r => r.json())
        .then(temaRiloc => {
            document.getElementById('riloc-overlay').classList.remove('visible');
            document.getElementById('mappa-ricalcolo').classList.remove('visible');

            // Aggiorna titolo e header
            document.getElementById('riloc-titolo').textContent = '☿ Rilocazione — ' + luogo;
            document.getElementById('riloc-citta-label').textContent = luogo;
            document.getElementById('riloc-asc-label').textContent =
                temaRiloc.case?.ASC?.posizione?.stringa ?? '—';
            document.getElementById('riloc-mc-label').textContent =
                temaRiloc.case?.MC?.posizione?.stringa  ?? '—';
            document.getElementById('header-riloc').style.display = 'flex';

            // Disegna ruota rilocata
            ZodiacWheel.disegna('wheel-riloc', temaRiloc, {size: 480});
            initSvgZoom('wheel-riloc');
            WheelRiloc.applicaGradi();
            WheelRiloc.applicaCuspidi();

            document.getElementById('info-riloc').textContent =
                'ASC: ' + (temaRiloc.case?.ASC?.posizione?.stringa ?? '?') +
                ' — MC: ' + (temaRiloc.case?.MC?.posizione?.stringa ?? '?');

            popolaTabellaPianeti('tab-riloc', temaRiloc);

            if (temaNataleCache) {
                _aggiornaLinkReportRiloc(lat, lon, luogo);
                popolaComparativa(temaNataleCache, temaRiloc);
                document.getElementById('card-comparativa').style.display = 'block';
            }
        })
        .catch(err => {
            document.getElementById('riloc-overlay').classList.remove('visible');
            document.getElementById('mappa-ricalcolo').classList.remove('visible');
            document.getElementById('info-riloc').textContent = '❌ Errore: ' + err.message;
        });
}

// ── Caricamento iniziale tema natale ─────────────────────────────────────
function caricaTemaNatale() {
    const url = 'api/tema_api.php?tipo=natale' +
        '&g='       + DS.giorno +
        '&m='       + DS.mese +
        '&a='       + DS.anno +
        '&ora_gmt=' + DS.ora_gmt +
        '&lat='     + DS.lat +
        '&lon='     + DS.lon;

    fetch(url)
        .then(r => r.json())
        .then(tema => {
            temaNataleCache = tema;
            ZodiacWheel.disegna('wheel-natale', tema, {size: 480});
            initSvgZoom('wheel-natale');
            document.getElementById('info-natale').textContent =
                'ASC: ' + (tema.case?.ASC?.posizione?.stringa ?? '?') +
                ' — MC: ' + (tema.case?.MC?.posizione?.stringa ?? '?');
            popolaTabellaPianeti('tab-natale', tema);

            // Aggiorna header natale
            document.getElementById('riloc-asc-natale-label').textContent =
                tema.case?.ASC?.posizione?.stringa ?? '—';
            document.getElementById('riloc-mc-natale-label').textContent =
                tema.case?.MC?.posizione?.stringa  ?? '—';

            // Se ci sono coordinate di rilocazione preimpostate (da URL), calcola subito
            const lat = parseFloat(document.getElementById('riloc-lat').value);
            const lon = parseFloat(document.getElementById('riloc-lon').value);
            if (lat !== 0 || lon !== 0) {
                calcolaRilocazione();
                // Mostra il pulsante mappa dopo il primo calcolo
                document.getElementById('btn-apri-mappa').style.display = 'inline-block';
            }
        })
        .catch(err => {
            document.getElementById('info-natale').textContent = '❌ Errore: ' + err.message;
        });
}

// ── Calcolo rilocazione ──────────────────────────────────────────────────
function calcolaRilocazione() {
    const lat   = parseFloat(document.getElementById('riloc-lat').value);
    const lon   = parseFloat(document.getElementById('riloc-lon').value);
    const luogo = document.getElementById('riloc-luogo-input').value.trim() || 'Posizione';

    if (isNaN(lat) || isNaN(lon)) {
        alert('Inserisci coordinate valide oppure cerca una città.');
        return;
    }

    // Mostra overlay di caricamento sulla ruota rilocata
    document.getElementById('riloc-overlay').classList.add('visible');
    document.getElementById('card-comparativa').style.display = 'none';
    document.getElementById('header-riloc').style.display = 'none';

    // Mostra il pulsante mappa
    document.getElementById('btn-apri-mappa').style.display = 'inline-block';

    // Usa tema_api.php con i dati natali ma le coordinate della città rilocata.
    const url = 'api/tema_api.php?tipo=natale' +
        '&g='       + DS.giorno +
        '&m='       + DS.mese +
        '&a='       + DS.anno +
        '&ora_gmt=' + DS.ora_gmt +
        '&lat='     + lat +
        '&lon='     + lon;

    fetch(url)
        .then(r => r.json())
        .then(temaRiloc => {
            document.getElementById('riloc-overlay').classList.remove('visible');

            // Aggiorna titolo e header
            document.getElementById('riloc-titolo').textContent = '☿ Rilocazione — ' + luogo;
            document.getElementById('riloc-citta-label').textContent = luogo;
            document.getElementById('riloc-asc-label').textContent =
                temaRiloc.case?.ASC?.posizione?.stringa ?? '—';
            document.getElementById('riloc-mc-label').textContent =
                temaRiloc.case?.MC?.posizione?.stringa  ?? '—';
            document.getElementById('header-riloc').style.display = 'flex';

            // Disegna ruota rilocata
            ZodiacWheel.disegna('wheel-riloc', temaRiloc, {size: 480});
            initSvgZoom('wheel-riloc');
            WheelRiloc.applicaGradi();
            WheelRiloc.applicaCuspidi();

            document.getElementById('info-riloc').textContent =
                'ASC: ' + (temaRiloc.case?.ASC?.posizione?.stringa ?? '?') +
                ' — MC: ' + (temaRiloc.case?.MC?.posizione?.stringa ?? '?');

            popolaTabellaPianeti('tab-riloc', temaRiloc);

            // Tabella comparativa
            if (temaNataleCache) {
                _aggiornaLinkReportRiloc(lat, lon, luogo);
                popolaComparativa(temaNataleCache, temaRiloc);
                document.getElementById('card-comparativa').style.display = 'block';
            }

            // Aggiorna la mappa se aperta
            if (mappaAperta && mapMarker) {
                mapMarker.setLatLng([lat, lon]);
                leafletMap.setView([lat, lon], leafletMap.getZoom());
                document.getElementById('mappa-coords').textContent =
                    lat.toFixed(4) + '°  ' + lon.toFixed(4) + '°';
            }
        })
        .catch(err => {
            document.getElementById('riloc-overlay').classList.remove('visible');
            document.getElementById('info-riloc').textContent = '❌ Errore: ' + err.message;
        });
}

// ── Tabella pianeti ───────────────────────────────────────────────────────
function popolaTabellaPianeti(tabId, tema) {
    const el = document.getElementById(tabId);
    if (!el || !tema?.pianeti) return;
    let html = '<table class="tabella-pianeti">' +
        '<thead><tr><th>Pianeta</th><th>Posizione</th><th>Casa</th><th></th></tr></thead><tbody>';
    Object.values(tema.pianeti).forEach(p => {
        html += '<tr>' +
            '<td>' + (NOMI_PIANETI[p.id] ?? p.nome) + '</td>' +
            '<td>' + (p.posizione?.stringa ?? '?') + '</td>' +
            '<td>' + p.casa + '</td>' +
            '<td>' + (p.retrogrado ? '<span class="retro">R</span>' : '') + '</td>' +
        '</tr>';
    });
    html += '</tbody></table>';
    el.innerHTML = html;
}

// ── Tabella comparativa pianeta × casa ───────────────────────────────────
function popolaComparativa(natale, riloc) {
    const tbody = document.getElementById('tbody-comparativa');
    if (!tbody) return;

    let html = '';
    Object.values(natale.pianeti).forEach(pNat => {
        const pRil    = riloc.pianeti[pNat.id];
        const casaNat = pNat.casa;
        const casaRil = pRil ? pRil.casa : '?';
        const delta   = (typeof casaRil === 'number') ? casaRil - casaNat : null;

        const cambiaClasse = (delta !== null && delta !== 0) ? 'casa-cambia' : '';

        let deltaHtml = '<span class="delta-casa uguale">—</span>';
        if (delta !== null && delta !== 0) {
            const cls = delta > 0 ? 'positivo' : 'negativo';
            const seg = delta > 0 ? '+' : '';
            deltaHtml = `<span class="delta-casa ${cls}">${seg}${delta}</span>`;
        }

        html += `<tr class="${cambiaClasse}">
            <td style="text-align:left;padding-left:14px">${NOMI_PIANETI[pNat.id] ?? pNat.nome}</td>
            <td style="font-family:monospace;font-size:11px">${pNat.posizione?.stringa ?? '?'}</td>
            <td><b>${casaNat}</b> <span style="font-size:10px;color:#888">${CASE_LABEL[casaNat] ?? ''}</span></td>
            <td><b>${casaRil}</b> <span style="font-size:10px;color:#888">${CASE_LABEL[casaRil] ?? ''}</span></td>
            <td>${deltaHtml}</td>
        </tr>`;
    });
    tbody.innerHTML = html;

    // Tabella cuspidi a confronto
    const tbodyCusp = document.getElementById('tbody-cuspidi-comp');
    if (!tbodyCusp) return;

    let htmlC = '';
    for (let c = 1; c <= 12; c++) {
        const cNat = natale.case[c];
        const cRil = riloc.case[c];
        if (!cNat) continue;
        const label = CASE_LABEL[c] || String(c);
        htmlC += `<tr>
            <td><b>${label}</b></td>
            <td style="font-family:monospace;font-size:11px">${cNat.posizione?.stringa ?? '—'}</td>
            <td style="font-family:monospace;font-size:11px;color:#3a2c6b">${cRil?.posizione?.stringa ?? '—'}</td>
        </tr>`;
    }
    ['ASC', 'MC'].forEach(k => {
        const cNat = natale.case[k];
        const cRil = riloc.case[k];
        if (!cNat) return;
        htmlC += `<tr>
            <td><b>${k}</b></td>
            <td style="font-family:monospace;font-size:11px">${cNat.posizione?.stringa ?? '—'}</td>
            <td style="font-family:monospace;font-size:11px;color:#3a2c6b">${cRil?.posizione?.stringa ?? '—'}</td>
        </tr>`;
    });
    tbodyCusp.innerHTML = htmlC;
}

// ── Geocoding città di rilocazione ───────────────────────────────────────
let _geocodeTimer = null;

function cercaLuogoRiloc() {
    const q = document.getElementById('riloc-luogo-input').value.trim();
    if (q.length < 3) return;
    fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(q) +
          '&format=json&limit=6&addressdetails=1')
        .then(r => r.json())
        .then(ris => {
            const div = document.getElementById('riloc-risultati');
            if (!ris.length) {
                div.innerHTML = '<div class="dropdown-item">Nessun risultato</div>';
                div.classList.add('visible');
                return;
            }
            div.innerHTML = ris.map(r =>
                `<div class="dropdown-item"
                      onclick="selezionaLuogoRiloc(${r.lat}, ${r.lon}, '${r.display_name.replace(/'/g, "\\'")}')">
                    ${r.display_name}
                </div>`
            ).join('');
            div.classList.add('visible');
        });
}

function selezionaLuogoRiloc(lat, lon, nome) {
    const citta = nome.split(',')[0].trim();
    document.getElementById('riloc-luogo-input').value = citta;
    document.getElementById('riloc-lat').value = parseFloat(lat).toFixed(4);
    document.getElementById('riloc-lon').value = parseFloat(lon).toFixed(4);
    document.getElementById('riloc-risultati').classList.remove('visible');

    // Aggiorna la mappa se aperta
    if (mappaAperta && mapMarker) {
        mapMarker.setLatLng([parseFloat(lat), parseFloat(lon)]);
        leafletMap.setView([parseFloat(lat), parseFloat(lon)], leafletMap.getZoom());
        document.getElementById('mappa-coords').textContent =
            parseFloat(lat).toFixed(4) + '°  ' + parseFloat(lon).toFixed(4) + '°';
    }

    calcolaRilocazione();
}

// ══════════════════════════════════════════════════════════════════════════
//  RICERCA VENERE / GIOVE SULLE CUSPIDI ANGOLARI — PATCH BLOCCO 3
//  Non tocca nessuna variabile o funzione già presente in rilocazione.php
// ══════════════════════════════════════════════════════════════════════════

(function() {
'use strict';

// ── Stato interno ──────────────────────────────────────────────────────────
let _eventoAngolari = null;  // EventSource attivo
let _tuttiRisultati = [];    // Cache dei risultati totali ricevuti
let _paginaCorrente = 1;     // Stato della pagina corrente
let _rilocConfronto = [];    // Max 3 rilocazioni selezionate

function _chiaveRilocConfronto(r) {
    return [
        Number(r.lat).toFixed(4),
        Number(r.lon).toFixed(4),
        r.iata || r.icao || ''
    ].join('|');
}

function _setProgress(perc, label, sub) {
    const fill  = document.getElementById('ang-bar-fill');
    const percEl= document.getElementById('ang-progress-perc');
    const lblEl = document.getElementById('ang-progress-label');
    const subEl = document.getElementById('ang-progress-sub');
    if (fill)   fill.style.width = perc + '%';
    if (percEl) percEl.textContent = perc + '%';
    if (lblEl && label) lblEl.textContent = label;
    if (subEl && sub)   subEl.textContent = sub;
}

function _showProgress(visibile) {
    const el = document.getElementById('angolari-progress-wrap');
    if (el) el.style.display = visibile ? 'block' : 'none';
}

function _setBtnDisabled(dis) {
    const btn = document.getElementById('btn-cerca-angolari');
    if (btn) { btn.disabled = dis; btn.textContent = dis ? '⟳ Ricerca...' : '🔍 CERCA'; }
}

function _distClasse(d) {
    if (d <= 1.0)  return 'dist-vicino';
    if (d <= 2.0)  return 'dist-medio';
    return 'dist-lontano';
}

/** Costruisce i badge per i match di un pianeta */
function _badgeMatch(matches, tipoCls, simbolo) {
    if (!matches || !matches.length) return '';
    return matches.map(m => {
        const dcls = _distClasse(m.distanza);
        return `<span class="${tipoCls}">
            ${simbolo} <span class="badge-casa-ang">${m.nome}</span>
            <span class="dist-num ${dcls}">${m.distanza}°</span>
        </span>`;
    }).join(' ');
}

/** Costruisce una riga della tabella risultati */
function _rigaTabella(r, idx) {
    const bV = _badgeMatch(r.match_venere, 'badge-venere', '♀');
    const bG = _badgeMatch(r.match_giove,  'badge-giove',  '♃');
    const haVenere = r.match_venere && r.match_venere.length > 0;
    const haGiove  = r.match_giove  && r.match_giove.length  > 0;
    const chiave = _chiaveRilocConfronto(r);
    const selezionata = _rilocConfronto.some(
        elemento => _chiaveRilocConfronto(elemento) === chiave
    );

    // Evidenzia riga se entrambi i pianeti hanno match
    const rowCls = (haVenere && haGiove) ? 'style="background:#F8F0FF"' : '';

    return `<tr ${rowCls}>
        <td style="color:#999;font-size:11px;text-align:center">${idx + 1}</td>
        <td><strong>${r.iata || '—'}</strong>
            <br><span style="font-size:10px;color:#999">${r.icao || ''}</span></td>
        <td style="max-width:180px;font-size:12px">${r.nome || ''}</td>
        <td>${r.citta || ''}</td>
        <td>${r.nazione || ''}</td>
        <td style="line-height:1.8">${bV}${bG}</td>
        <td>
            <button class="btn-usa-riloc"
                    onclick="_usaLuogoAngolari(${r.lat}, ${r.lon}, '${(r.citta || '').replace(/'/g,"\\'")}')">
                ☿ Usa
            </button>
        </td>
        <td style="text-align:center">
            <input
                type="checkbox"
                class="compare-riloc-checkbox"
                data-index="${idx}"
                ${selezionata ? 'checked' : ''}>
        </td>
    </tr>`;
}

/** Gestisce il rendering e la paginazione interna dei risultati */
function _renderTabellaPaginata() {
    const area = document.getElementById('angolari-risultati-area');
    if (!area) return;

    if (!_tuttiRisultati || _tuttiRisultati.length === 0) {
        area.innerHTML = '<div class="angolari-empty">Nessuna località che soddisfi le sue esigenze.</div>';
        return;
    }

    const limite = parseInt(document.getElementById('ang-pagine-limite')?.value || '50');
    const totaleRecord = _tuttiRisultati.length;
    const totalePagine = Math.ceil(totaleRecord / limite);

    if (_paginaCorrente > totalePagine) _paginaCorrente = totalePagine;
    if (_paginaCorrente < 1) _paginaCorrente = 1;

    const inizio = (_paginaCorrente - 1) * limite;
    const fine = Math.min(inizio + limite, totaleRecord);
    const risPaginati = _tuttiRisultati.slice(inizio, fine);

    const countStr = totaleRecord === 1 ? '1 luogo trovato' : totaleRecord + ' luoghi trovati';

    let html = `
        <div style="font-size:12px;color:#3a2c6b;font-weight:500;margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
            <span>✅ ${countStr} (ordinati per distanza minima)</span>
            <span>Mostrati ${inizio + 1}-${fine} di ${totaleRecord}</span>
        </div>
        <div style="overflow-x:auto">
        <table class="tabella-angolari">
            <thead>
                <tr>
                    <th>#</th>
                    <th>IATA</th>
                    <th>Aeroporto</th>
                    <th>Città</th>
                    <th>Naz.</th>
                    <th>Match pianeti → cuspidi</th>
                    <th>Rilocazione</th>
                    <th>Confronta</th>
                </tr>
            </thead>
            <tbody>
                ${risPaginati.map((r, i) => _rigaTabella(r, inizio + i)).join('')}
            </tbody>
        </table>
        </div>`;

    // Generazione barra controlli paginazione inferiore
    if (totalePagine > 1) {
        html += `
        <div class="angolari-paginazione">
            <button class="angolari-pag-btn" id="ang-prev-btn" ${_paginaCorrente === 1 ? 'disabled' : ''}>◀ Precedente</button>
            <span>Pagina <b>${_paginaCorrente}</b> di <b>${totalePagine}</b></span>
            <button class="angolari-pag-btn" id="ang-next-btn" ${_paginaCorrente === totalePagine ? 'disabled' : ''}>Successivo ▶</button>
        </div>`;
    }

    area.innerHTML = html;

    document.querySelectorAll('.compare-riloc-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const indice = Number(checkbox.dataset.index);
            const risultato = _tuttiRisultati[indice];

            if (!risultato) {
                checkbox.checked = false;
                return;
            }

            const chiave = _chiaveRilocConfronto(risultato);

            if (checkbox.checked) {
                if (_rilocConfronto.length >= 3) {
                    checkbox.checked = false;
                    alert('Puoi confrontare al massimo 3 rilocazioni.');
                    return;
                }

                if (!_rilocConfronto.some(
                    elemento => _chiaveRilocConfronto(elemento) === chiave
                )) {
                    _rilocConfronto.push(risultato);
                }
            } else {
                _rilocConfronto = _rilocConfronto.filter(
                    elemento => _chiaveRilocConfronto(elemento) !== chiave
                );
            }
        });
    });

    // Aggancio dei listener sui nuovi pulsanti appena creati
    document.getElementById('ang-prev-btn')?.addEventListener('click', () => {
        _paginaCorrente--;
        _renderTabellaPaginata();
        area.scrollTop = 0;
    });
    document.getElementById('ang-next-btn')?.addEventListener('click', () => {
        _paginaCorrente++;
        _renderTabellaPaginata();
        area.scrollTop = 0;
    });
}

/** Permette il cambio di limite dinamico */
window.aggiornaPaginazioneAngolari = function() {
    _paginaCorrente = 1;
    _renderTabellaPaginata();
};

// ── Funzione principale ────────────────────────────────────────────────────

/**
 * Avvia la ricerca SSE Venere/Giove sulle cuspidi angolari.
 * Usa DS (definito nel contesto esterno da rilocazione.php) per i dati natali.
 */
window.avviaRicercaAngolari = function() {
    if (typeof DS === 'undefined') {
        alert('Dati soggetto non disponibili.');
        return;
    }

    // Chiudi eventuale stream precedente
    if (_eventoAngolari) { _eventoAngolari.close(); _eventoAngolari = null; }

    let tolleranza = parseFloat(document.getElementById('ang-tolleranza')?.value || '2.5');
    if (tolleranza > 2.5) {
        tolleranza = 2.5;
        document.getElementById('ang-tolleranza').value = '2.5';
    }

    const tipoRicerca     = document.getElementById('ang-tipo-ricerca')?.value || 'large_medium';
    const escludiMilitari = document.getElementById('ang-escludi-militari')?.value || '1';
    const nazione         = (document.getElementById('ang-nazione')?.value || '').trim().toUpperCase();

    // Reset UI e visualizzazione dell'area di ricerca all'avvio
    const area = document.getElementById('angolari-risultati-area');
    if (area) {
        area.innerHTML = '';
        area.style.display = 'block'; 
    }
    _setBtnDisabled(true);
    _showProgress(true);
    _setProgress(0, 'Calcolo posizioni natali...', '—');

    const infoEl = document.getElementById('angolari-info-pianeti');
    if (infoEl) infoEl.style.display = 'none';

    _tuttiRisultati = [];
    _paginaCorrente = 1;
    _rilocConfronto = [];

    // Costruisci URL SSE
    const params = new URLSearchParams({
        g:                DS.giorno,
        m:                DS.mese,
        a:                DS.anno,
        ora_gmt:          DS.ora_gmt,
        lat:              DS.lat,
        lon:              DS.lon,
        tolleranza:       tolleranza,
        tipo_ricerca:     tipoRicerca,
        escludi_militari: escludiMilitari,
    });
    if (nazione) params.set('nazione', nazione);

    const apiUrl = 'api/riloc_angolari_api.php?' + params.toString();

    _eventoAngolari = new EventSource(apiUrl);

    // ── start ──────────────────────────────────────────────────────────────
    _eventoAngolari.addEventListener('start', e => {
        const d = JSON.parse(e.data);
        _setProgress(1, 'Deduplicazione aeroporti...', 
            d.totale_dedup.toLocaleString() + ' posizioni uniche su ' +
            d.totale.toLocaleString() + ' totali');

        const infoEl = document.getElementById('angolari-info-pianeti');
        if (infoEl) {
            infoEl.style.display = 'block';
            const elV = document.getElementById('ap-venere');
            const elG = document.getElementById('ap-giove');
            const elT = document.getElementById('ap-tol');
            if (elV) elV.textContent = d.venere || '?';
            if (elG) elG.textContent = d.giove  || '?';
            if (elT) elT.textContent = '±' + d.tolleranza + '°';
        }
    });

    // ── progress ───────────────────────────────────────────────────────────
    _eventoAngolari.addEventListener('progress', e => {
        const d = JSON.parse(e.data);
        _setProgress(
            Math.max(2, d.perc),
            'Calcolati ' + d.processed.toLocaleString() + ' / ' + d.totale.toLocaleString(),
            'Trovati finora: ' + d.trovati
        );
    });

    // ── done ───────────────────────────────────────────────────────────────
    _eventoAngolari.addEventListener('done', e => {
        const d = JSON.parse(e.data);
        _eventoAngolari.close(); _eventoAngolari = null;

        _showProgress(false);
        _setBtnDisabled(false);

        _tuttiRisultati = d.risultati || [];

        const area = document.getElementById('angolari-risultati-area');
        const elapsed = ((d.elapsed_ms || 0) / 1000).toFixed(1);
        const statsHtml = `<div style="font-size:11px;color:#888;margin-bottom:8px">
            ⏱ ${elapsed}s · ${(d.totale_calcolati||0).toLocaleString()} posizioni calcolate
            su ${(d.totale_originale||0).toLocaleString()} aeroporti
        </div>`;

        _renderTabellaPaginata();

        if (area && area.firstChild && _tuttiRisultati.length > 0) {
            area.insertAdjacentHTML('afterbegin', statsHtml);
        }
    });

    // ── error ──────────────────────────────────────────────────────────────
    _eventoAngolari.addEventListener('error', e => {
        _eventoAngolari.close(); _eventoAngolari = null;
        _showProgress(false);
        _setBtnDisabled(false);

        let msg = 'Errore durante la ricerca.';
        try { msg = JSON.parse(e.data).message; } catch(x) {}

        const area = document.getElementById('angolari-risultati-area');
        if (area) area.innerHTML = `<div class="angolari-empty" style="color:#CC3333">❌ ${msg}</div>`;
    });
};

// ── Usa luogo per la rilocazione ─────────────────────────────────────────
window._usaLuogoAngolari = function(lat, lon, nomeCitta) {
    const inpLat   = document.getElementById('riloc-lat');
    const inpLon   = document.getElementById('riloc-lon');
    const inpLuogo = document.getElementById('riloc-luogo-input');
    if (inpLat)   inpLat.value   = parseFloat(lat).toFixed(4);
    if (inpLon)   inpLon.value   = parseFloat(lon).toFixed(4);
    if (inpLuogo) inpLuogo.value = nomeCitta || (lat.toFixed(4) + '°, ' + lon.toFixed(4) + '°');

    const controlli = document.querySelector('.controlli-riloc');
    if (controlli) controlli.scrollIntoView({ behavior: 'smooth', block: 'center' });

    if (typeof calcolaRilocazione === 'function') {
        calcolaRilocazione();
    }
};

})(); // fine IIFE modulo angolari

// ── Event listeners ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Autocomplete con debounce
    const input = document.getElementById('riloc-luogo-input');
    if (input) {
        input.addEventListener('input', () => {
            clearTimeout(_geocodeTimer);
            _geocodeTimer = setTimeout(cercaLuogoRiloc, 500);
        });
    }

    // Chiudi dropdown cliccando fuori
    document.addEventListener('click', e => {
        if (!e.target.closest('#riloc-luogo-input') &&
            !e.target.closest('#riloc-risultati')) {
            document.getElementById('riloc-risultati')?.classList.remove('visible');
        }
    });

    // Carica tema natale all'avvio (avvia anche la rilocazione se ci sono coord URL)
    caricaTemaNatale();
});

</script>
<?php endif; ?>
</body>
</html>