<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * api/sessioni_api.php — CRUD sessioni RS/RL salvate
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * Schema reale (verificato via Adminer):
 *   sessioni_rs: id, soggetto_id, anno, data_rs, data_rs_gmt, luogo_rs,
 *                nazione_rs, latitudine, longitudine, altitudine,
 *                timezone_rs, condizione, stelline, val_stringa, note,
 *                creato_il, utente_id
 *   sessioni_rl: id, soggetto_id, sessione_rs_id, numero_mese, data_rl,
 *                data_rl_gmt, luogo_rl, nazione_rl, latitudine, longitudine,
 *                altitudine, timezone_rl, condizione, stelline, val_stringa,
 *                note, creato_il, utente_id, anno_rs
 *
 * Note di implementazione:
 *  - sessione_rs_id è SEMPRE opzionale (RS e RL sono salvabili indipendentemente)
 *  - numero_mese è 1-based (1..13), coerente con la UI "RL 1 di 13"
 *  - data_rs/data_rl (ora locale) usano come placeholder lo stesso valore
 *    di data_rs_gmt/data_rl_gmt, in attesa di integrazione TimeZoneDB server-side
 *  - parsing GMT: formato sorgente "dd/mm/yyyy HH:MM:SS GMT" (da SweCalc::julianAData)
 *
 * GET  ?action=lista_rs&soggetto_id=N
 * GET  ?action=lista_rl&soggetto_id=N[&sessione_rs_id=N]
 * POST {action: 'salva_rs', soggetto_id, anno, condizione, lat, lon, luogo, rs_gmt, stelline, val, note}
 * POST {action: 'salva_rl', soggetto_id, sessione_rs_id?, anno_rs, rl_index, condizione, lat, lon, luogo, rl_gmt, stelline, val, note}
 * POST {action: 'elimina_rs', id}
 * POST {action: 'elimina_rl', id}
 */

session_start();
require_once '../includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autenticato.']);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

$userId = $auth->getCurrentUserId();
$action = $_GET['action'] ?? (json_decode(file_get_contents('php://input'), true)['action'] ?? '');
$data   = json_decode(file_get_contents('php://input'), true) ?? [];

const CONDIZIONI_VALIDE = ['Decima','Lavoro','Amore','Salute','Denaro','Denaro Low','Casa'];

/**
 * Converte la stringa GMT prodotta da SweCalc::julianAData()
 * ("dd/mm/yyyy HH:MM:SS GMT") in formato timestamp Postgres.
 * Fallback: NOW() se il parsing fallisce (non blocca il salvataggio).
 */
function parseGmtTimestamp(?string $gmtStr): string {
    if ($gmtStr) {
        $dt = DateTime::createFromFormat('d/m/Y H:i:s \G\M\T', trim($gmtStr));
        if ($dt !== false) {
            return $dt->format('Y-m-d H:i:s');
        }
        error_log("[sessioni_api] parsing GMT fallito per stringa: $gmtStr — uso NOW() come fallback");
    }
    return date('Y-m-d H:i:s');
}

/**
 * Estrae il codice/nome nazione da una stringa "Citta, NAZIONE".
 * Ritorna null se non presente.
 */
function estraiNazione(?string $luogo): ?string {
    if (!$luogo) return null;
    $parti = array_map('trim', explode(',', $luogo));
    return count($parti) > 1 ? end($parti) : null;
}

switch ($action) {

    // ════════════════════════════════════════════════════════════
    // LISTA SESSIONI RS
    // ════════════════════════════════════════════════════════════
    case 'lista_rs':
        $soggettoId = intval($_GET['soggetto_id'] ?? 0);
        if (!$auth->verificaSoggetto($soggettoId)) {
            http_response_code(403);
            echo json_encode(['errore' => 'Soggetto non trovato o non autorizzato.']);
            break;
        }
        $stmt = $pdo->prepare(
            "SELECT id, anno, luogo_rs AS luogo, nazione_rs AS nazione,
                    latitudine AS lat, longitudine AS lon,
                    data_rs_gmt, condizione, stelline,
                    val_stringa AS val, note, creato_il
             FROM sessioni_rs
             WHERE soggetto_id = ? AND utente_id = ?
             ORDER BY anno DESC, creato_il DESC"
        );
        $stmt->execute([$soggettoId, $userId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // ════════════════════════════════════════════════════════════
    // LISTA SESSIONI RL
    // ════════════════════════════════════════════════════════════
    case 'lista_rl':
        $soggettoId   = intval($_GET['soggetto_id'] ?? 0);
        $sessioneRsId = isset($_GET['sessione_rs_id']) ? intval($_GET['sessione_rs_id']) : null;

        if (!$auth->verificaSoggetto($soggettoId)) {
            http_response_code(403);
            echo json_encode(['errore' => 'Soggetto non trovato o non autorizzato.']);
            break;
        }

        if ($sessioneRsId !== null) {
            $stmt = $pdo->prepare(
                "SELECT id, sessione_rs_id, anno_rs, numero_mese AS rl_index,
                        luogo_rl AS luogo, nazione_rl AS nazione,
                        latitudine AS lat, longitudine AS lon,
                        data_rl_gmt, condizione, stelline,
                        val_stringa AS val, note, creato_il
                 FROM sessioni_rl
                 WHERE soggetto_id = ? AND utente_id = ? AND sessione_rs_id = ?
                 ORDER BY numero_mese ASC"
            );
            $stmt->execute([$soggettoId, $userId, $sessioneRsId]);
        } else {
            $stmt = $pdo->prepare(
                "SELECT id, sessione_rs_id, anno_rs, numero_mese AS rl_index,
                        luogo_rl AS luogo, nazione_rl AS nazione,
                        latitudine AS lat, longitudine AS lon,
                        data_rl_gmt, condizione, stelline,
                        val_stringa AS val, note, creato_il
                 FROM sessioni_rl
                 WHERE soggetto_id = ? AND utente_id = ?
                 ORDER BY anno_rs DESC NULLS LAST, numero_mese ASC, creato_il DESC"
            );
            $stmt->execute([$soggettoId, $userId]);
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // ════════════════════════════════════════════════════════════
    // SALVA SESSIONE RS
    // ════════════════════════════════════════════════════════════
    case 'salva_rs':
        $soggettoId = intval($data['soggetto_id'] ?? 0);
        if (!$auth->verificaSoggetto($soggettoId)) {
            http_response_code(403);
            echo json_encode(['errore' => 'Soggetto non trovato o non autorizzato.']);
            break;
        }

        $anno       = intval($data['anno'] ?? 0);
        $condizione = $data['condizione'] ?? 'Decima';
        if (!in_array($condizione, CONDIZIONI_VALIDE)) $condizione = 'Decima';

        $lat   = floatval($data['lat'] ?? 0);
        $lon   = floatval($data['lon'] ?? 0);
        $luogo = trim($data['luogo'] ?? '');

        if ($anno <= 0 || $luogo === '') {
            echo json_encode(['ok' => false, 'errore' => 'Anno e luogo sono obbligatori.']);
            break;
        }

        $gmtTimestamp = parseGmtTimestamp($data['rs_gmt'] ?? null);
        $nazione      = estraiNazione($luogo);

        $stmt = $pdo->prepare(
            "INSERT INTO sessioni_rs
                (soggetto_id, utente_id, anno, data_rs, data_rs_gmt,
                 luogo_rs, nazione_rs, latitudine, longitudine, altitudine,
                 timezone_rs, condizione, stelline, val_stringa, note)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             RETURNING id"
        );
        $stmt->execute([
            $soggettoId, $userId, $anno,
            $gmtTimestamp,   // data_rs (placeholder = data_rs_gmt, vedi nota in testa al file)
            $gmtTimestamp,   // data_rs_gmt
            $luogo, $nazione, $lat, $lon, 0, // altitudine default 0
            null,            // timezone_rs — non disponibile lato server in questa fase
            $condizione,
            isset($data['stelline']) ? floatval($data['stelline']) : null,
            $data['val']  ?? null,
            $data['note'] ?? null,
        ]);
        $nuovoId = $stmt->fetchColumn();

        echo json_encode(['ok' => true, 'id' => (int)$nuovoId]);
        break;

    // ════════════════════════════════════════════════════════════
    // SALVA SESSIONE RL
    // ════════════════════════════════════════════════════════════
    case 'salva_rl':
        $soggettoId = intval($data['soggetto_id'] ?? 0);
        if (!$auth->verificaSoggetto($soggettoId)) {
            http_response_code(403);
            echo json_encode(['errore' => 'Soggetto non trovato o non autorizzato.']);
            break;
        }

        $annoRS  = intval($data['anno_rs']  ?? 0);
        // rl_index in arrivo dal frontend è 0-based (_rlIndex) -> convertiamo a 1-based
        $rlIndexZeroBased = intval($data['rl_index'] ?? -1);
        $numeroMese = $rlIndexZeroBased + 1;

        if ($annoRS <= 0 || $numeroMese < 1 || $numeroMese > 13) {
            echo json_encode(['ok' => false, 'errore' => 'anno_rs e rl_index sono obbligatori e validi.']);
            break;
        }

        $condizione = $data['condizione'] ?? 'Decima';
        if (!in_array($condizione, CONDIZIONI_VALIDE)) $condizione = 'Decima';

        $lat   = floatval($data['lat'] ?? 0);
        $lon   = floatval($data['lon'] ?? 0);
        $luogo = trim($data['luogo'] ?? '');

        // sessione_rs_id OPZIONALE — se presente, verifica appartenenza
        $sessioneRsId = null;
        if (!empty($data['sessione_rs_id'])) {
            $candidato = intval($data['sessione_rs_id']);
            $check = $pdo->prepare(
                "SELECT id FROM sessioni_rs WHERE id = ? AND soggetto_id = ? AND utente_id = ?"
            );
            $check->execute([$candidato, $soggettoId, $userId]);
            if ($check->fetchColumn()) {
                $sessioneRsId = $candidato;
            }
            // se non valido/non trovato, resta null silenziosamente
        }

        $gmtTimestamp = parseGmtTimestamp($data['rl_gmt'] ?? null);
        $nazione      = estraiNazione($luogo);

        $stmt = $pdo->prepare(
            "INSERT INTO sessioni_rl
                (soggetto_id, utente_id, sessione_rs_id, anno_rs, numero_mese,
                 data_rl, data_rl_gmt, luogo_rl, nazione_rl,
                 latitudine, longitudine, altitudine, timezone_rl,
                 condizione, stelline, val_stringa, note)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             RETURNING id"
        );
        $stmt->execute([
            $soggettoId, $userId, $sessioneRsId, $annoRS, $numeroMese,
            $gmtTimestamp,   // data_rl (placeholder = data_rl_gmt)
            $gmtTimestamp,   // data_rl_gmt
            $luogo, $nazione, $lat, $lon, 0, // altitudine default 0
            null,            // timezone_rl
            $condizione,
            isset($data['stelline']) ? floatval($data['stelline']) : null,
            $data['val']  ?? null,
            $data['note'] ?? null,
        ]);
        $nuovoId = $stmt->fetchColumn();

        echo json_encode(['ok' => true, 'id' => (int)$nuovoId]);
        break;

    // ════════════════════════════════════════════════════════════
    // ELIMINA SESSIONE RS
    // ════════════════════════════════════════════════════════════
    case 'elimina_rs':
        $id = intval($data['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT id FROM sessioni_rs WHERE id = ? AND utente_id = ?");
        $stmt->execute([$id, $userId]);
        if (!$stmt->fetchColumn()) {
            echo json_encode(['ok' => false, 'errore' => 'Sessione non trovata o non autorizzata.']);
            break;
        }
        $pdo->prepare("DELETE FROM sessioni_rs WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
        break;

    // ════════════════════════════════════════════════════════════
    // ELIMINA SESSIONE RL
    // ════════════════════════════════════════════════════════════
    case 'elimina_rl':
        $id = intval($data['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT id FROM sessioni_rl WHERE id = ? AND utente_id = ?");
        $stmt->execute([$id, $userId]);
        if (!$stmt->fetchColumn()) {
            echo json_encode(['ok' => false, 'errore' => 'Sessione non trovata o non autorizzata.']);
            break;
        }
        $pdo->prepare("DELETE FROM sessioni_rl WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['errore' => 'Azione non valida.']);
}
