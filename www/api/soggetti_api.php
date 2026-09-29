<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * soggetti_api.php — CRUD soggetti con filtro per utente loggato
 *
 * Modifiche vs versione precedente:
 * - Tutte le SELECT filtrano per utente_id (eccetto admin che vede tutto)
 * - INSERT imposta utente_id = utente loggato
 * - UPDATE/DELETE verificano appartenenza
 * - CODICE generato in automatico all'inserimento e non modificabile
 * - accesso_genera / accesso_disattiva: credenziali del soggetto (solo proprietario)
 */
header('Content-Type: application/json');
session_start();

require_once '../includes/Auth.php';
require_once __DIR__ . '/../includes/CodiceSoggetto.php';

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
                        u.ruolo         AS astrologo_ruolo,
                        CASE WHEN a.soggetto_id IS NULL THEN 'nessuno'
                             WHEN a.attivo THEN 'attivo'
                             ELSE 'disattivato' END AS accesso_stato
                 FROM soggetti s
                 LEFT JOIN utenti u ON u.id = s.utente_id
                 LEFT JOIN accessi_soggetti a ON a.soggetto_id = s.id
                 ORDER BY u.username, s.nome"
            )->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare(
                "SELECT s.*,
                        CASE WHEN a.soggetto_id IS NULL THEN 'nessuno'
                             WHEN a.attivo THEN 'attivo'
                             ELSE 'disattivato' END AS accesso_stato
                 FROM soggetti s
                 LEFT JOIN accessi_soggetti a ON a.soggetto_id = s.id
                 WHERE s.utente_id = ?
                 ORDER BY s.nome"
            );
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // Credenziali del soggetto gestibili solo dal suo astrologo (Fase B3).
        foreach ($rows as &$riga) {
            $riga['accesso_gestibile'] = ((int)$riga['utente_id'] === (int)$userId);
        }
        unset($riga);
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
        if (!$isAdmin) {
            $subjectsMax = $auth->getLimiteSoggettiEffettivo($userId);

            $countStmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM soggetti
                WHERE utente_id = ?
            ");
            $countStmt->execute([$userId]);
            $subjectsCount = (int)$countStmt->fetchColumn();

            if ($subjectsMax !== null && $subjectsCount >= $subjectsMax) {
                http_response_code(400);
                echo json_encode([
                    'errore' => 'Hai raggiunto il numero massimo di soggetti consentiti dal tuo piano.'
                ]);
                break;
            }
        }

        // CODICE generato lato server (ROADMAP_CODICE_LOGIN_SOGGETTI.md, C6-C7):
        // il valore eventualmente inviato dal browser viene ignorato.
        try {
            $pdo->beginTransaction();
            $codice = CodiceSoggetto::generaCodice($pdo, $userId);
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
                $codice,   // generato lato server (CodiceSoggetto)
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
            $nuovoId = $pdo->lastInsertId();
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('soggetti_api inserisci: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['errore' => 'Errore durante il salvataggio del soggetto.']);
            break;
        }
        echo json_encode(['ok' => true, 'id' => $nuovoId, 'codice' => $codice]);
        break;

    case 'modifica':
        // Verifica che il soggetto appartenga all'utente
        if (!$auth->verificaSoggetto(intval($data['id']))) {
            echo json_encode(['errore' => 'Non autorizzato.']);
            break;
        }
        $stmt = $pdo->prepare("
            UPDATE soggetti SET
                nome=?, data_nascita=?, ora_nascita=?,
                ora_nascita_gmt=?, luogo_nascita=?, nazione_nascita=?,
                latitudine=?, longitudine=?, timezone=?, offset_gmt=?,
                note=?,
                residenza_luogo=?, residenza_latitudine=?,
                residenza_longitudine=?, residenza_nazione=?,
                modificato_il=NOW()
            WHERE id=?
        ");
        $stmt->execute([
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

    case 'accesso_genera':
    case 'accesso_disattiva':
        // Accesso del soggetto con il proprio CODICE (ROADMAP_CODICE_LOGIN_SOGGETTI.md,
        // Fase B3). Solo POST JSON (un form di un altro sito non puo' inviarlo) e
        // solo per i soggetti dell'astrologo loggato: nemmeno l'admin gestisce
        // le credenziali dei soggetti di altri astrologi.
        $contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || strpos($contentType, 'application/json') !== 0) {
            http_response_code(400);
            echo json_encode(['errore' => 'Richiesta non valida.']);
            break;
        }
        $soggettoId = intval($data['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT id, codice FROM soggetti WHERE id = ? AND utente_id = ?");
        $stmt->execute([$soggettoId, $userId]);
        $soggetto = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$soggetto) {
            http_response_code(403);
            echo json_encode(['errore' => 'Non autorizzato.']);
            break;
        }
        if (trim((string)$soggetto['codice']) === '') {
            echo json_encode(['errore' => 'Il soggetto non ha un codice: impossibile abilitare l\'accesso.']);
            break;
        }
        try {
            if ($action === 'accesso_genera') {
                $result = $auth->abilitaAccessoSoggetto($soggettoId, $userId);
                echo json_encode([
                    'ok'       => true,
                    'codice'   => $soggetto['codice'],
                    'password' => $result['password'],
                ]);
            } else {
                $result = $auth->disattivaAccessoSoggetto($soggettoId);
                echo json_encode($result['ok'] ? ['ok' => true] : ['errore' => $result['errore']]);
            }
        } catch (Throwable $e) {
            error_log('soggetti_api ' . $action . ': ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['errore' => 'Operazione non riuscita.']);
        }
        break;

    default:
        echo json_encode(['errore' => 'Azione non valida.']);
}
