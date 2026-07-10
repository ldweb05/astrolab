<?php
require_once __DIR__ . '/../includes/bootstrap.php';
// ===== INIZIO AUTH =====
session_start();
require_once '../includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autenticato.']);
    exit;
}
// ===== FINE AUTH =====

/**
 * ⚠️ LEGACY / NON USATO DALLA UI ATTUALE ⚠️
 * ─────────────────────────────────────────────────────────────────────────
 * Questo endpoint non è più richiamato da nessuna pagina del frontend.
 * La ricerca batch attiva (usata da www/ricerca.php) passa esclusivamente
 * da api/ricerca_stream_api.php (SSE), che è la fonte autorevole (single
 * source of truth) per la logica di ricerca ed esclusione delle RS:
 * Rule Map di esclusione radicale, FiltroEsclusione.php, filtri specifici
 * per condizione (Amore/Casa/Salute/Denaro/Denaro Low), dedup geografica.
 *
 * Questo file (e la classe SearchEngine da cui dipende, in
 * includes/search_engine.php) NON implementa quei filtri e può quindi
 * restituire risultati non allineati a ricerca_stream_api.php. È mantenuto
 * per compatibilità ma non va usato come riferimento per la logica di
 * ricerca: per qualunque modifica alle regole di ricerca, l'unico file da
 * aggiornare è api/ricerca_stream_api.php.
 * ─────────────────────────────────────────────────────────────────────────
 */

/**
 * ricerca_api.php — Endpoint JSON per la ricerca batch aeroporti
 * Chiama SearchEngine e restituisce risultati paginati + metadati filtro
 *
 * GET params:
 *   g, m, a, ora_gmt, lat, lon   → dati natali
 *   anno                          → anno RS (default: anno corrente)
 *   condizione                    → condizione tematica (default: Decima)
 *   tipo_ricerca                  → large_medium | tutti | iata_only
 *   escludi_militari              → 1 | 0 (default: 1)
 *   nazione                       → filtro nazione (opzionale, es: "IT")
 *   stelline_min                  → 0-5 (default: 0)
 *   pagina                        → numero pagina 1-based (default: 1)
 *   per_pagina                    → 25 | 50 | 100 (default: 50)
 *   soggetto_id                   → usato solo per passarlo al client
 */
header('Content-Type: application/json');
require_once '../includes/SweCalc.php';
require_once '../includes/RuleEngine.php';
require_once '../includes/search_engine.php';
// ── Parametri natali ──────────────────────────────────────────────────────
$g      = intval($_GET['g']       ?? 1);
$m      = intval($_GET['m']       ?? 1);
$a      = intval($_GET['a']       ?? 1990);
$oraGmt = floatval($_GET['ora_gmt'] ?? 12.0);
$lat    = floatval($_GET['lat']   ?? 41.9);
$lon    = floatval($_GET['lon']   ?? 12.48);

// ── Parametri ricerca ─────────────────────────────────────────────────────
$anno            = intval($_GET['anno']            ?? date('Y'));
$condizione      = $_GET['condizione']             ?? 'Decima';
$tipoRicerca     = $_GET['tipo_ricerca']           ?? 'large_medium';
$escludiMilitari = ($_GET['escludi_militari']      ?? '1') === '1';
$filtroNazione   = trim($_GET['nazione']           ?? '');
$stellineMin     = intval($_GET['stelline_min']    ?? 0);

// ── Paginazione ───────────────────────────────────────────────────────────
$perPagina  = in_array(intval($_GET['per_pagina'] ?? 50), [25, 50, 100])
              ? intval($_GET['per_pagina'])
              : 50;
$pagina     = max(1, intval($_GET['pagina'] ?? 1));

try {
    $engine = new SearchEngine();

    $soggetto = [$g, $m, $a, $oraGmt, $lat, $lon];

    // Chiedi tutti i risultati (SearchEngine ordina già per stelline desc)
    $raw = $engine->cerca(
        $soggetto,
        $anno,
        $condizione,
        $tipoRicerca,
        $escludiMilitari,
        [],       // astriInCasa — non usato dalla UI ricerca
        9999      // limite alto: filtreremo lato PHP
    );

    $tutti = $raw['risultati'];

    // ── Filtri post-engine ────────────────────────────────────────────────
    if ($filtroNazione !== '') {
        $tutti = array_values(array_filter(
            $tutti,
            fn($r) => strtoupper($r['nazione']) === strtoupper($filtroNazione)
        ));
    }

    if ($stellineMin > 0) {
        $tutti = array_values(array_filter(
            $tutti,
            fn($r) => $r['stelline'] >= $stellineMin
        ));
    }

    // ── Raccoglie nazioni uniche per il dropdown filtro ───────────────────
    $nazioniAll = [];
    foreach ($raw['risultati'] as $r) {
        $nazioniAll[$r['nazione']] = true;
    }
    ksort($nazioniAll);
    $nazioniList = array_keys($nazioniAll);

    // ── Paginazione ───────────────────────────────────────────────────────
    $totale     = count($tutti);
    $totPagine  = max(1, (int)ceil($totale / $perPagina));
    $pagina     = min($pagina, $totPagine);
    $offset     = ($pagina - 1) * $perPagina;
    $pagina_ris = array_slice($tutti, $offset, $perPagina);

    // ── Risposta ──────────────────────────────────────────────────────────
    echo json_encode([
        'ok'          => true,
        'rs_gmt'      => $raw['rs_gmt'],
        'condizione'  => $raw['condizione'],
        'totale'      => $totale,
        'pagina'      => $pagina,
        'tot_pagine'  => $totPagine,
        'per_pagina'  => $perPagina,
        'nazioni'     => $nazioniList,
        'risultati'   => $pagina_ris,
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'     => false,
        'errore' => $e->getMessage(),
    ]);
}