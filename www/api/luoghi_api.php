<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * luoghi_api.php — Ricerca nazioni e città dalla tabella aeroporti
 *
 * GET ?action=nazioni
 *   → array di { code, nome } ordinati per codice ISO
 *
 * GET ?action=citta&nazione=IT&q=nap
 *   → array di { id, nome, citta, iata, icao, lat, lon, nazione }
 *     filtrati per nazione e stringa di ricerca (min 2 char)
 *
 * GET ?action=coords&iata=NAP
 *   → { lat, lon, citta, nome, nazione }
 */
header('Content-Type: application/json; charset=UTF-8');
session_start();

require_once '../includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autenticato.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {

    // ── Lista nazioni uniche ──────────────────────────────────────────────
    case 'nazioni':
        $rows = $pdo->query(
            "SELECT DISTINCT nazione AS code
             FROM aeroporti
             WHERE attivo = true AND nazione IS NOT NULL AND nazione <> ''
             ORDER BY nazione"
        )->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
        break;

    // ── Ricerca città per nazione + stringa ───────────────────────────────
    case 'citta':
        $nazione = strtoupper(trim($_GET['nazione'] ?? ''));
        $q       = trim($_GET['q'] ?? '');

        if ($nazione === '') {
            echo json_encode([]);
            break;
        }

        // Base query: grandi e medi aeroporti con IATA nella nazione scelta
        // Se q ≥ 2 char, filtra su citta o nome aeroporto
        $params = [$nazione];
        $where  = ["nazione = ?", "attivo = true",
                   "tipo IN ('large_airport','medium_airport','small_airport')",
                   "militare = false"];

        if (strlen($q) >= 2) {
            $where[]  = "(LOWER(citta) LIKE LOWER(?) OR LOWER(nome) LIKE LOWER(?))";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql = "SELECT DISTINCT ON (citta)
                    icao_code AS icao, iata_code AS iata,
                    nome, citta, nazione,
                    latitudine AS lat, longitudine AS lon,
                    tipo
                FROM aeroporti
                WHERE " . implode(' AND ', $where) . "
                ORDER BY citta,
                    CASE tipo
                        WHEN 'large_airport'  THEN 1
                        WHEN 'medium_airport' THEN 2
                        ELSE 3
                    END
                LIMIT 50";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
        break;

    // ── Coordinate per codice IATA o ICAO ────────────────────────────────
    case 'coords':
        $iata = strtoupper(trim($_GET['iata'] ?? ''));
        $icao = strtoupper(trim($_GET['icao'] ?? ''));

        if ($iata !== '') {
            $stmt = $pdo->prepare(
                "SELECT latitudine AS lat, longitudine AS lon,
                        citta, nome, nazione, iata_code AS iata, icao_code AS icao
                 FROM aeroporti WHERE iata_code = ? AND attivo = true LIMIT 1"
            );
            $stmt->execute([$iata]);
        } elseif ($icao !== '') {
            $stmt = $pdo->prepare(
                "SELECT latitudine AS lat, longitudine AS lon,
                        citta, nome, nazione, iata_code AS iata, icao_code AS icao
                 FROM aeroporti WHERE icao_code = ? AND attivo = true LIMIT 1"
            );
            $stmt->execute([$icao]);
        } else {
            echo json_encode(['errore' => 'Parametro iata o icao richiesto.']);
            break;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row ?: ['errore' => 'Non trovato.']);
        break;

    default:
        echo json_encode(['errore' => 'Azione non valida.']);
}
