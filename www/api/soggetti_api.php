<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * soggetti_api.php — CRUD soggetti con filtro per utente loggato
 *
 * Modifiche vs versione precedente:
 * - Tutte le SELECT filtrano per utente_id (eccetto admin che vede tutto)
 * - INSERT imposta utente_id = utente loggato
 * - UPDATE/DELETE verificano appartenenza
 */
header('Content-Type: application/json');
session_start();

require_once '../includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);

// Richiede autenticazione
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autenticato.']);
    exit;
}

$userId  = $auth->getCurrentUserId();
$isAdmin = $auth->isAdmin();

$action = $_GET['action'] ?? (json_decode(file_get_contents('php://input'), true)['action'] ?? '');
$data   = json_decode(file_get_contents('php://input'), true) ?? [];

// Helper: clausola WHERE per utente
function whereUtente(bool $isAdmin, int $userId, string $alias = ''): string {
    if ($isAdmin) return '1=1';
    $col = $alias ? "$alias.utente_id" : 'utente_id';
    return "$col = $userId";
}

switch ($action) {

    case 'lista':
        if ($isAdmin) {
            // Admin: JOIN utenti per mostrare il proprietario di ogni soggetto
            $rows = $pdo->query(
                "SELECT s.*,
                        u.username      AS astrologo_username,
                        u.nome_completo AS astrologo_nome_completo,
                        u.id            AS astrologo_id,
                        u.ruolo         AS astrologo_ruolo
                 FROM soggetti s
                 LEFT JOIN utenti u ON u.id = s.utente_id
                 ORDER BY u.username, s.nome"
            )->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare(
                "SELECT * FROM soggetti WHERE utente_id = ? ORDER BY nome"
            );
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($rows);
        break;

    case 'get':
        $id  = intval($_GET['id']);
        $soggetto = $auth->verificaSoggetto($id);
        if (!$soggetto) {
            echo json_encode(['errore' => 'Non trovato o non autorizzato.']);
            break;
        }
        echo json_encode($soggetto);
        break;

    case 'inserisci':
        $stmt = $pdo->prepare("
            INSERT INTO soggetti
            (codice, nome, data_nascita, ora_nascita, ora_nascita_gmt,
             luogo_nascita, nazione_nascita, latitudine, longitudine,
             timezone, offset_gmt, note,
             residenza_luogo, residenza_latitudine, residenza_longitudine,
             residenza_nazione, utente_id)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $data['codice']               ?: null,
            $data['nome'],
            $data['data_nascita'],
            $data['ora_nascita'],
            $data['ora_nascita_gmt'],
            $data['luogo_nascita'],
            $data['nazione_nascita'],
            $data['latitudine'],
            $data['longitudine'],
            $data['timezone']             ?: null,
            $data['offset_gmt'],
            $data['note']                 ?: null,
            $data['residenza_luogo']      ?: null,
            $data['residenza_latitudine'] ? floatval($data['residenza_latitudine']) : null,
            $data['residenza_longitudine']? floatval($data['residenza_longitudine']): null,
            $data['residenza_nazione']    ?: null,
            $userId,   // sempre l'utente loggato
        ]);
        echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'modifica':
        // Verifica che il soggetto appartenga all'utente
        if (!$auth->verificaSoggetto(intval($data['id']))) {
            echo json_encode(['errore' => 'Non autorizzato.']);
            break;
        }
        $stmt = $pdo->prepare("
            UPDATE soggetti SET
                codice=?, nome=?, data_nascita=?, ora_nascita=?,
                ora_nascita_gmt=?, luogo_nascita=?, nazione_nascita=?,
                latitudine=?, longitudine=?, timezone=?, offset_gmt=?,
                note=?,
                residenza_luogo=?, residenza_latitudine=?,
                residenza_longitudine=?, residenza_nazione=?,
                modificato_il=NOW()
            WHERE id=?
        ");
        $stmt->execute([
            $data['codice']               ?: null,
            $data['nome'],
            $data['data_nascita'],
            $data['ora_nascita'],
            $data['ora_nascita_gmt'],
            $data['luogo_nascita'],
            $data['nazione_nascita'],
            $data['latitudine'],
            $data['longitudine'],
            $data['timezone']             ?: null,
            $data['offset_gmt'],
            $data['note']                 ?: null,
            $data['residenza_luogo']      ?: null,
            $data['residenza_latitudine'] ? floatval($data['residenza_latitudine']) : null,
            $data['residenza_longitudine']? floatval($data['residenza_longitudine']): null,
            $data['residenza_nazione']    ?: null,
            intval($data['id']),
        ]);
        echo json_encode(['ok' => true]);
        break;

    case 'elimina':
        $soggettoId = intval($data['id']);
        if (!$auth->verificaSoggetto($soggettoId)) {
            echo json_encode(['errore' => 'Non autorizzato.']);
            break;
        }
        $pdo->prepare("DELETE FROM soggetti WHERE id=?")->execute([$soggettoId]);
        // Se era il soggetto attivo in sessione, rimuovilo
        if ($auth->getSoggettoAttivo() === $soggettoId) {
            $auth->clearSoggettoAttivo();
        }
        echo json_encode(['ok' => true]);
        break;

    case 'set_attivo':
        // Imposta soggetto attivo in sessione
        $id = intval($data['id'] ?? 0);
        $ok = $auth->setSoggettoAttivo($id);
        if ($ok) {
            echo json_encode([
                'ok'            => true,
                'soggetto_id'   => $auth->getSoggettoAttivo(),
                'soggetto_nome' => $auth->getSoggettoNome(),
            ]);
        } else {
            echo json_encode(['ok' => false, 'errore' => 'Soggetto non trovato o non autorizzato.']);
        }
        break;

    default:
        echo json_encode(['errore' => 'Azione non valida.']);
}
