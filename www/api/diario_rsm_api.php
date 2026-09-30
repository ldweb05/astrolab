<?php
require_once __DIR__ . '/../includes/bootstrap.php';
/**
 * diario_rsm_api.php - API del Diario RSM
 * docs/roadmaps/ROADMAP_DIARIO_RSM.md, blocco C, Fase 2 (D1-D19).
 *
 * Utenti ammessi: astrologo loggato (utente_id) oppure soggetto loggato con il
 * proprio CODICE e accesso attivo (accesso_soggetto).
 *
 * Lettura (GET, ?action=...):
 *   token, nazioni, cerca, nazione, scheda, miei_viaggi, miei_contributi, contributo
 * Scrittura (POST, Content-Type: application/json, header X-CSRF-Token):
 *   viaggio_salva, viaggio_elimina, viaggio_collega_sessione,
 *   contributo_salva, contributo_elimina, contributo_visibilita (solo admin)
 *
 * Regole: i contributi condivisi non contengono mai dati del soggetto; il codice
 * ISO della nazione resta interno (l'interfaccia mostra sempre il nome italiano);
 * l'astrologo vede ma non modifica i viaggi dei propri soggetti; solo l'autore
 * modifica o elimina i propri viaggi e contributi.
 */
require_once __DIR__ . '/../includes/Auth.php';

header('Content-Type: application/json; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');

const DIARIO_MAX_CONTRIBUTI_GIORNO = 10;
const DIARIO_MAX_VIAGGI_GIORNO     = 20;
const DIARIO_MAX_TRATTE            = 15;

function diario_json(array $dati, int $codice = 200): void
{
    http_response_code($codice);
    echo json_encode($dati, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    exit;
}

function diario_errore(string $messaggio, int $codice = 400): void
{
    diario_json(['errore' => $messaggio], $codice);
}

/** Testo facoltativo: spazi normalizzati, null se vuoto, errore se troppo lungo. */
function diario_testo($valore, int $max, string $campo, bool $multiriga = false): ?string
{
    if ($valore === null) {
        return null;
    }
    if (!is_string($valore) && !is_int($valore) && !is_float($valore)) {
        diario_errore("Valore non valido per il campo \"$campo\".");
    }
    $testo = trim((string)$valore);
    if (!$multiriga) {
        $testo = (string)preg_replace('/\s+/u', ' ', $testo);
    }
    if ($testo === '') {
        return null;
    }
    if (mb_strlen($testo) > $max) {
        diario_errore("Il campo \"$campo\" supera i $max caratteri.");
    }
    return $testo;
}

function diario_numero($valore, string $campo, bool $positivo = true): ?float
{
    if ($valore === null || $valore === '') {
        return null;
    }
    if (!is_numeric($valore)) {
        diario_errore("Valore numerico non valido per il campo \"$campo\".");
    }
    $numero = (float)$valore;
    if ($positivo && $numero < 0) {
        diario_errore("Il campo \"$campo\" non puo' essere negativo.");
    }
    return $numero;
}

function diario_coordinata($valore, float $limite, string $campo): ?float
{
    $numero = diario_numero($valore, $campo, false);
    if ($numero !== null && abs($numero) > $limite) {
        diario_errore("Valore non valido per il campo \"$campo\".");
    }
    return $numero;
}

function diario_data($valore, string $campo): ?string
{
    if ($valore === null || $valore === '') {
        return null;
    }
    $d = DateTime::createFromFormat('!Y-m-d', (string)$valore);
    if (!$d || $d->format('Y-m-d') !== (string)$valore) {
        diario_errore("Data non valida per il campo \"$campo\".");
    }
    return $d->format('Y-m-d');
}

function diario_valuta($valore): string
{
    $valuta = strtoupper(trim((string)($valore ?? 'EUR')));
    if ($valuta === '') {
        return 'EUR';
    }
    if (!preg_match('/^[A-Z]{3}$/', $valuta)) {
        diario_errore('Valuta non valida (usa un codice di 3 lettere, es. EUR).');
    }
    return $valuta;
}

function diario_nazione_valida(PDO $pdo, $iso): string
{
    $iso = strtoupper(trim((string)$iso));
    $stmt = $pdo->prepare("SELECT 1 FROM nazioni WHERE iso = ?");
    $stmt->execute([$iso]);
    if (!preg_match('/^[A-Z]{2}$/', $iso) || !$stmt->fetchColumn()) {
        diario_errore('Nazione non valida.');
    }
    return $iso;
}

/** Condizione SQL e parametro che identificano l'autore corrente. */
function diario_autore(array $attore): array
{
    return $attore['tipo'] === 'soggetto'
        ? ['soggetto_id = ?', $attore['soggetto_id']]
        : ['utente_id = ?', $attore['utente_id']];
}

// ── AUTENTICAZIONE ──────────────────────────────────────────────

$pdo  = db_connect();
$auth = new Auth($pdo);

if ($auth->isLoggedIn()) {
    $attore = [
        'tipo'      => 'astrologo',
        'utente_id' => (int)$auth->getCurrentUserId(),
        'admin'     => $auth->isAdmin(),
    ];
} elseif ($soggetto = $auth->soggettoCorrente()) {
    $attore = [
        'tipo'        => 'soggetto',
        'soggetto_id' => $soggetto['soggetto_id'],
        'codice'      => $soggetto['codice'],
        'admin'       => false,
    ];
} else {
    diario_errore('Non autenticato.', 401);
}

if (empty($_SESSION['csrf_diario'])) {
    $_SESSION['csrf_diario'] = bin2hex(random_bytes(32));
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$dati   = [];

if ($metodo === 'POST') {
    $contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
    if (strpos($contentType, 'application/json') !== 0) {
        diario_errore('Richiesta non valida.');
    }
    $tokenInviato = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals($_SESSION['csrf_diario'], $tokenInviato)) {
        diario_errore('Sessione scaduta: ricarica la pagina e riprova.', 403);
    }
    $dati = json_decode((string)file_get_contents('php://input'), true);
    if (!is_array($dati)) {
        diario_errore('Richiesta non valida.');
    }
    $azione = (string)($dati['action'] ?? '');
} elseif ($metodo === 'GET') {
    $azione = (string)($_GET['action'] ?? '');
} else {
    diario_errore('Richiesta non valida.', 405);
}

$azioniLettura = ['token', 'nazioni', 'cerca', 'nazione', 'scheda', 'miei_viaggi', 'miei_contributi', 'contributo'];
if ($metodo === 'GET' && !in_array($azione, $azioniLettura, true)) {
    diario_errore('Azione non valida.');
}
if ($metodo === 'POST' && in_array($azione, $azioniLettura, true)) {
    diario_errore('Azione non valida.');
}

// Riepilogo delle localita' con contributi visibili per un insieme di nazioni.
// Nome mostrato: la prima grafia con maiuscole (es. "Longyearbyen" e non "longyearbyen").
$sqlLocalita = "
    SELECT c.iso_nazione AS iso,
           n.nome_it     AS nazione,
           c.luogo_chiave AS chiave,
           (ARRAY_AGG(c.luogo ORDER BY (c.luogo <> lower(c.luogo)) DESC, c.id))[1] AS luogo,
           COUNT(*) AS contributi
      FROM contributi_localita c
      JOIN nazioni n ON n.iso = c.iso_nazione
     WHERE c.visibile AND %s
     GROUP BY c.iso_nazione, n.nome_it, c.luogo_chiave
     ORDER BY LOWER((ARRAY_AGG(c.luogo ORDER BY (c.luogo <> lower(c.luogo)) DESC, c.id))[1])";

try {
    switch ($azione) {

    // ── LETTURA ────────────────────────────────────────────────

    case 'token':
        diario_json(['token' => $_SESSION['csrf_diario']]);

    case 'nazioni':
        $righe = $pdo->query(
            "SELECT iso, nome_it AS nome FROM nazioni ORDER BY nome_it"
        )->fetchAll(PDO::FETCH_ASSOC);
        diario_json(['nazioni' => $righe]);

    case 'cerca':
        $q = trim((string)($_GET['q'] ?? ''));
        if ($q === '') {
            // Elenco delle nazioni con contributi, con la nazione di appartenenza
            // per i territori (es. Svalbard e Jan Mayen -> Norvegia).
            $righe = $pdo->query(
                "SELECT n.iso, n.nome_it AS nome, COUNT(*) AS contributi,
                        (SELECT STRING_AGG(p.nome_it, ', ' ORDER BY p.nome_it)
                           FROM nazioni_appartenenza a
                           JOIN nazioni p ON p.iso = a.iso_nazione
                          WHERE a.iso_territorio = n.iso) AS appartiene_a
                   FROM contributi_localita c
                   JOIN nazioni n ON n.iso = c.iso_nazione
                  WHERE c.visibile
                  GROUP BY n.iso, n.nome_it
                  ORDER BY n.nome_it"
            )->fetchAll(PDO::FETCH_ASSOC);
            diario_json(['nazioni_con_contributi' => $righe]);
        }
        if (mb_strlen($q) < 2 || mb_strlen($q) > 100) {
            diario_errore('Scrivi da 2 a 100 caratteri.');
        }
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';

        $stmt = $pdo->prepare(
            "SELECT iso, nome_it AS nome
               FROM nazioni
              WHERE nome_it ILIKE ? OR nome_en ILIKE ?
                 OR EXISTS (SELECT 1 FROM unnest(alias) al WHERE al ILIKE ?)
                 OR similarity(nome_it, ?) > 0.4
              ORDER BY similarity(nome_it, ?) DESC, nome_it
              LIMIT 10"
        );
        $stmt->execute([$like, $like, $like, $q, $q]);
        $nazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($nazioni as &$nazione) {
            $stmtLoc = $pdo->prepare(sprintf($sqlLocalita,
                "c.iso_nazione IN (SELECT ?::char(2) UNION
                                   SELECT iso_territorio FROM nazioni_appartenenza WHERE iso_nazione = ?)"));
            $stmtLoc->execute([$nazione['iso'], $nazione['iso']]);
            $nazione['localita'] = $stmtLoc->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($nazione);

        $stmtLoc = $pdo->prepare(sprintf($sqlLocalita,
            "(c.luogo ILIKE ? OR similarity(c.luogo, ?) > 0.4)"));
        $stmtLoc->execute([$like, $q]);
        diario_json(['nazioni' => $nazioni, 'localita' => $stmtLoc->fetchAll(PDO::FETCH_ASSOC)]);

    case 'nazione':
        $iso = strtoupper(trim((string)($_GET['iso'] ?? '')));
        $stmt = $pdo->prepare("SELECT iso, nome_it AS nome FROM nazioni WHERE iso = ?");
        $stmt->execute([$iso]);
        $nazione = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$nazione) {
            diario_errore('Nazione non trovata.', 404);
        }
        $stmtLoc = $pdo->prepare(sprintf($sqlLocalita,
            "c.iso_nazione IN (SELECT ?::char(2) UNION
                               SELECT iso_territorio FROM nazioni_appartenenza WHERE iso_nazione = ?)"));
        $stmtLoc->execute([$iso, $iso]);
        $nazione['localita'] = $stmtLoc->fetchAll(PDO::FETCH_ASSOC);
        diario_json(['nazione' => $nazione]);

    case 'scheda':
        $iso    = strtoupper(trim((string)($_GET['iso'] ?? '')));
        $chiave = trim((string)($_GET['chiave'] ?? ''));
        if (!preg_match('/^[A-Z]{2}$/', $iso) || $chiave === '' || mb_strlen($chiave) > 200) {
            diario_errore('Localita\' non valida.');
        }
        [$condAutore, $parAutore] = diario_autore($attore);
        $filtroVisibili = $attore['admin'] ? 'TRUE' : 'c.visibile';
        $stmt = $pdo->prepare(
            "SELECT c.id, c.luogo, n.nome_it AS nazione, c.anno_viaggio, c.mese_viaggio,
                    c.alloggio_nome, c.alloggio_tipo, c.alloggio_sito, c.alloggio_fascia_prezzo,
                    c.alloggio_giudizio, c.costo_alloggio_indicativo, c.costo_alloggio_riferimento,
                    c.valuta, c.info_documenti, c.info_clima, c.info_lingua_valuta,
                    c.info_connettivita, c.info_particolarita, c.contatti_utili, c.consigli,
                    c.visibile, c.creato_il, c.aggiornato_il,
                    COALESCE(u.username, s.codice, 'utente non piu'' registrato') AS autore,
                    COALESCE(c.$condAutore, FALSE) AS mio
               FROM contributi_localita c
               JOIN nazioni n ON n.iso = c.iso_nazione
               LEFT JOIN utenti u ON u.id = c.utente_id
               LEFT JOIN soggetti s ON s.id = c.soggetto_id
              WHERE c.iso_nazione = ? AND c.luogo_chiave = ? AND $filtroVisibili
              ORDER BY c.anno_viaggio DESC, c.mese_viaggio DESC NULLS LAST, c.id DESC"
        );
        $stmt->execute([$parAutore, $iso, mb_strtolower($chiave)]);
        $contributi = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmtTratte = $pdo->prepare(
            "SELECT ordine, mezzo, da_luogo, a_luogo, compagnia, durata_indicativa,
                    costo_indicativo, valuta, note
               FROM contributi_tratte WHERE contributo_id = ? ORDER BY ordine"
        );
        foreach ($contributi as &$contributo) {
            $stmtTratte->execute([$contributo['id']]);
            $contributo['tratte'] = $stmtTratte->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($contributo);
        diario_json(['contributi' => $contributi]);

    case 'miei_viaggi':
        $campi = "v.id, v.luogo, v.iso_nazione, n.nome_it AS nazione, v.latitudine, v.longitudine,
                  v.data_arrivo, v.data_partenza, v.albergo, v.costo_alloggio, v.costo_trasporti,
                  v.valuta, v.trasporti, v.note_private, v.creato_il, v.aggiornato_il";
        if ($attore['tipo'] === 'soggetto') {
            // Il soggetto non vede mai le sessioni RS (D16).
            $stmt = $pdo->prepare(
                "SELECT $campi, TRUE AS modificabile
                   FROM viaggi_rsm v JOIN nazioni n ON n.iso = v.iso_nazione
                  WHERE v.soggetto_id = ?
                  ORDER BY v.data_arrivo DESC NULLS LAST, v.id DESC"
            );
            $stmt->execute([$attore['soggetto_id']]);
        } else {
            // Astrologo: i propri viaggi e quelli dei propri soggetti (D15).
            $stmt = $pdo->prepare(
                "SELECT $campi, v.sessione_rs_id, sr.anno AS sessione_anno, sr.luogo_rs AS sessione_luogo,
                        s.id AS soggetto_id, s.codice AS soggetto_codice, s.nome AS soggetto_nome,
                        COALESCE(v.utente_id = ?, FALSE) AS modificabile
                   FROM viaggi_rsm v
                   JOIN nazioni n ON n.iso = v.iso_nazione
                   LEFT JOIN soggetti s ON s.id = v.soggetto_id
                   LEFT JOIN sessioni_rs sr ON sr.id = v.sessione_rs_id
                  WHERE v.utente_id = ?
                     OR v.soggetto_id IN (SELECT id FROM soggetti WHERE utente_id = ?)
                  ORDER BY v.data_arrivo DESC NULLS LAST, v.id DESC"
            );
            $stmt->execute([$attore['utente_id'], $attore['utente_id'], $attore['utente_id']]);
        }
        diario_json(['viaggi' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    case 'miei_contributi':
        [$condAutore, $parAutore] = diario_autore($attore);
        $stmt = $pdo->prepare(
            "SELECT c.id, c.luogo, c.luogo_chiave AS chiave, c.iso_nazione, n.nome_it AS nazione,
                    c.anno_viaggio, c.mese_viaggio, c.visibile, c.aggiornato_il
               FROM contributi_localita c JOIN nazioni n ON n.iso = c.iso_nazione
              WHERE c.$condAutore
              ORDER BY c.aggiornato_il DESC"
        );
        $stmt->execute([$parAutore]);
        diario_json(['contributi' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    case 'contributo':
        // Un proprio contributo completo, per modificarlo (anche se nascosto dall'admin).
        [$condAutore, $parAutore] = diario_autore($attore);
        $stmt = $pdo->prepare(
            "SELECT c.id, c.luogo, c.iso_nazione, n.nome_it AS nazione, c.anno_viaggio, c.mese_viaggio,
                    c.alloggio_nome, c.alloggio_tipo, c.alloggio_sito, c.alloggio_fascia_prezzo,
                    c.alloggio_giudizio, c.costo_alloggio_indicativo, c.costo_alloggio_riferimento,
                    c.valuta, c.info_documenti, c.info_clima, c.info_lingua_valuta,
                    c.info_connettivita, c.info_particolarita, c.contatti_utili, c.consigli,
                    c.visibile, c.luogo_chiave AS chiave
               FROM contributi_localita c JOIN nazioni n ON n.iso = c.iso_nazione
              WHERE c.id = ? AND c.$condAutore"
        );
        $stmt->execute([(int)($_GET['id'] ?? 0), $parAutore]);
        $contributo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$contributo) {
            diario_errore('Contributo non trovato.', 404);
        }
        $stmt = $pdo->prepare(
            "SELECT ordine, mezzo, da_luogo, a_luogo, compagnia, durata_indicativa,
                    costo_indicativo, valuta, note
               FROM contributi_tratte WHERE contributo_id = ? ORDER BY ordine"
        );
        $stmt->execute([$contributo['id']]);
        $contributo['tratte'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        diario_json(['contributo' => $contributo]);

    // ── SCRITTURA: VIAGGI PRIVATI ──────────────────────────────

    case 'viaggio_salva':
        $id    = isset($dati['id']) ? (int)$dati['id'] : 0;
        $luogo = diario_testo($dati['luogo'] ?? null, 200, 'Luogo');
        if ($luogo === null) {
            diario_errore('Indica il luogo del viaggio.');
        }
        $valori = [
            'luogo'           => $luogo,
            'iso_nazione'     => diario_nazione_valida($pdo, $dati['iso_nazione'] ?? ''),
            'latitudine'      => diario_coordinata($dati['latitudine'] ?? null, 90, 'Latitudine'),
            'longitudine'     => diario_coordinata($dati['longitudine'] ?? null, 180, 'Longitudine'),
            'data_arrivo'     => diario_data($dati['data_arrivo'] ?? null, 'Data di arrivo'),
            'data_partenza'   => diario_data($dati['data_partenza'] ?? null, 'Data di partenza'),
            'albergo'         => diario_testo($dati['albergo'] ?? null, 200, 'Albergo'),
            'costo_alloggio'  => diario_numero($dati['costo_alloggio'] ?? null, 'Costo alloggio'),
            'costo_trasporti' => diario_numero($dati['costo_trasporti'] ?? null, 'Costo trasporti'),
            'valuta'          => diario_valuta($dati['valuta'] ?? null),
            'trasporti'       => diario_testo($dati['trasporti'] ?? null, 4000, 'Trasporti', true),
            'note_private'    => diario_testo($dati['note_private'] ?? null, 4000, 'Note', true),
        ];
        if ($valori['data_arrivo'] && $valori['data_partenza'] && $valori['data_partenza'] < $valori['data_arrivo']) {
            diario_errore('La data di partenza non puo\' precedere quella di arrivo.');
        }
        [$condAutore, $parAutore] = diario_autore($attore);

        if ($id > 0) {
            $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($valori)));
            $stmt = $pdo->prepare(
                "UPDATE viaggi_rsm SET $set, aggiornato_il = NOW() WHERE id = ? AND $condAutore"
            );
            $stmt->execute(array_merge(array_values($valori), [$id, $parAutore]));
            if ($stmt->rowCount() !== 1) {
                diario_errore('Viaggio non trovato o non modificabile.', 403);
            }
            diario_json(['ok' => true, 'id' => $id]);
        }

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM viaggi_rsm WHERE $condAutore AND creato_il > NOW() - INTERVAL '1 day'"
        );
        $stmt->execute([$parAutore]);
        if ((int)$stmt->fetchColumn() >= DIARIO_MAX_VIAGGI_GIORNO) {
            diario_errore('Hai raggiunto il numero massimo di viaggi inseribili in un giorno.', 429);
        }
        $colonnaAutore = $attore['tipo'] === 'soggetto' ? 'soggetto_id' : 'utente_id';
        $colonne = array_merge([$colonnaAutore], array_keys($valori));
        $stmt = $pdo->prepare(
            "INSERT INTO viaggi_rsm (" . implode(', ', $colonne) . ")
             VALUES (" . implode(', ', array_fill(0, count($colonne), '?')) . ") RETURNING id"
        );
        $stmt->execute(array_merge([$parAutore], array_values($valori)));
        diario_json(['ok' => true, 'id' => (int)$stmt->fetchColumn()]);

    case 'viaggio_elimina':
        [$condAutore, $parAutore] = diario_autore($attore);
        $stmt = $pdo->prepare("DELETE FROM viaggi_rsm WHERE id = ? AND $condAutore");
        $stmt->execute([(int)($dati['id'] ?? 0), $parAutore]);
        if ($stmt->rowCount() !== 1) {
            diario_errore('Viaggio non trovato o non modificabile.', 403);
        }
        diario_json(['ok' => true]);

    case 'viaggio_collega_sessione':
        // Solo l'astrologo, con una propria sessione RS dello stesso soggetto (D16).
        if ($attore['tipo'] !== 'astrologo') {
            diario_errore('Non autorizzato.', 403);
        }
        $idViaggio = (int)($dati['id'] ?? 0);
        $stmt = $pdo->prepare(
            "SELECT v.id, v.utente_id, v.soggetto_id
               FROM viaggi_rsm v
               LEFT JOIN soggetti s ON s.id = v.soggetto_id
              WHERE v.id = ? AND (v.utente_id = ? OR s.utente_id = ?)"
        );
        $stmt->execute([$idViaggio, $attore['utente_id'], $attore['utente_id']]);
        $viaggio = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$viaggio) {
            diario_errore('Viaggio non trovato.', 403);
        }
        $idSessione = $dati['sessione_rs_id'] ?? null;
        if ($idSessione !== null && $idSessione !== '') {
            $idSessione = (int)$idSessione;
            $condSoggetto = $viaggio['soggetto_id'] !== null ? 'AND soggetto_id = ?' : '';
            $parametri = [$idSessione, $attore['utente_id']];
            if ($viaggio['soggetto_id'] !== null) {
                $parametri[] = (int)$viaggio['soggetto_id'];
            }
            $stmt = $pdo->prepare("SELECT 1 FROM sessioni_rs WHERE id = ? AND utente_id = ? $condSoggetto");
            $stmt->execute($parametri);
            if (!$stmt->fetchColumn()) {
                diario_errore('Sessione RS non valida per questo viaggio.', 403);
            }
        } else {
            $idSessione = null;
        }
        $pdo->prepare("UPDATE viaggi_rsm SET sessione_rs_id = ?, aggiornato_il = NOW() WHERE id = ?")
            ->execute([$idSessione, $idViaggio]);
        diario_json(['ok' => true]);

    // ── SCRITTURA: CONTRIBUTI CONDIVISI ────────────────────────

    case 'contributo_salva':
        $id    = isset($dati['id']) ? (int)$dati['id'] : 0;
        $luogo = diario_testo($dati['luogo'] ?? null, 200, 'Localita\'');
        if ($luogo === null) {
            diario_errore('Indica la localita\'.');
        }
        $annoCorrente = (int)date('Y');
        $anno = (int)($dati['anno_viaggio'] ?? 0);
        if ($anno < 1900 || $anno > $annoCorrente + 1) {
            diario_errore('Anno del viaggio non valido.');
        }
        $mese = $dati['mese_viaggio'] ?? null;
        if ($mese !== null && $mese !== '') {
            $mese = (int)$mese;
            if ($mese < 1 || $mese > 12) {
                diario_errore('Mese del viaggio non valido.');
            }
        } else {
            $mese = null;
        }
        $tipo = diario_testo($dati['alloggio_tipo'] ?? null, 20, 'Tipo di alloggio');
        if ($tipo !== null && !in_array($tipo, ['hotel', 'bb', 'appartamento', 'guesthouse', 'campeggio', 'altro'], true)) {
            diario_errore('Tipo di alloggio non valido.');
        }
        $sito = diario_testo($dati['alloggio_sito'] ?? null, 500, 'Sito dell\'alloggio');
        if ($sito !== null && (!preg_match('~^https?://~i', $sito) || filter_var($sito, FILTER_VALIDATE_URL) === false)) {
            diario_errore('Il sito dell\'alloggio deve iniziare con http:// o https://.');
        }
        if (($dati['dichiarazione_contatti'] ?? false) !== true) {
            diario_errore('Conferma di non inserire dati personali di privati nei contatti.');
        }
        $valori = [
            'luogo'                      => $luogo,
            'iso_nazione'                => diario_nazione_valida($pdo, $dati['iso_nazione'] ?? ''),
            'latitudine'                 => diario_coordinata($dati['latitudine'] ?? null, 90, 'Latitudine'),
            'longitudine'                => diario_coordinata($dati['longitudine'] ?? null, 180, 'Longitudine'),
            'anno_viaggio'               => $anno,
            'mese_viaggio'               => $mese,
            'alloggio_nome'              => diario_testo($dati['alloggio_nome'] ?? null, 200, 'Nome dell\'alloggio'),
            'alloggio_tipo'              => $tipo,
            'alloggio_sito'              => $sito,
            'alloggio_fascia_prezzo'     => diario_testo($dati['alloggio_fascia_prezzo'] ?? null, 50, 'Fascia di prezzo'),
            'alloggio_giudizio'          => diario_testo($dati['alloggio_giudizio'] ?? null, 4000, 'Giudizio', true),
            'costo_alloggio_indicativo'  => diario_numero($dati['costo_alloggio_indicativo'] ?? null, 'Costo indicativo'),
            'costo_alloggio_riferimento' => diario_testo($dati['costo_alloggio_riferimento'] ?? null, 50, 'Riferimento del costo'),
            'valuta'                     => diario_valuta($dati['valuta'] ?? null),
            'info_documenti'             => diario_testo($dati['info_documenti'] ?? null, 4000, 'Documenti e visti', true),
            'info_clima'                 => diario_testo($dati['info_clima'] ?? null, 4000, 'Clima', true),
            'info_lingua_valuta'         => diario_testo($dati['info_lingua_valuta'] ?? null, 4000, 'Lingua e valuta', true),
            'info_connettivita'          => diario_testo($dati['info_connettivita'] ?? null, 4000, 'Connettivita\'', true),
            'info_particolarita'         => diario_testo($dati['info_particolarita'] ?? null, 4000, 'Particolarita\' locali', true),
            'contatti_utili'             => diario_testo($dati['contatti_utili'] ?? null, 4000, 'Contatti utili', true),
            'consigli'                   => diario_testo($dati['consigli'] ?? null, 4000, 'Consigli', true),
            'dichiarazione_contatti'     => true,
        ];

        $tratteIn = $dati['tratte'] ?? [];
        if (!is_array($tratteIn) || count($tratteIn) > DIARIO_MAX_TRATTE) {
            diario_errore('Puoi inserire al massimo ' . DIARIO_MAX_TRATTE . ' tratte.');
        }
        $tratte = [];
        foreach (array_values($tratteIn) as $i => $t) {
            if (!is_array($t)) {
                diario_errore('Tratta non valida.');
            }
            $mezzo = (string)($t['mezzo'] ?? '');
            if (!in_array($mezzo, ['aereo', 'nave', 'treno', 'bus', 'auto', 'altro'], true)) {
                diario_errore('Mezzo di trasporto non valido nella tratta ' . ($i + 1) . '.');
            }
            $tratte[] = [
                $i + 1,
                $mezzo,
                diario_testo($t['da_luogo'] ?? null, 200, 'Partenza della tratta'),
                diario_testo($t['a_luogo'] ?? null, 200, 'Arrivo della tratta'),
                diario_testo($t['compagnia'] ?? null, 150, 'Compagnia'),
                diario_testo($t['durata_indicativa'] ?? null, 50, 'Durata'),
                diario_numero($t['costo_indicativo'] ?? null, 'Costo della tratta'),
                diario_valuta($t['valuta'] ?? null),
                diario_testo($t['note'] ?? null, 2000, 'Note della tratta', true),
            ];
        }

        [$condAutore, $parAutore] = diario_autore($attore);
        $pdo->beginTransaction();
        if ($id > 0) {
            $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($valori)));
            $stmt = $pdo->prepare(
                "UPDATE contributi_localita SET $set, aggiornato_il = NOW() WHERE id = ? AND $condAutore"
            );
            $stmt->execute(array_merge(array_values($valori), [$id, $parAutore]));
            if ($stmt->rowCount() !== 1) {
                $pdo->rollBack();
                diario_errore('Contributo non trovato o non modificabile.', 403);
            }
            $pdo->prepare("DELETE FROM contributi_tratte WHERE contributo_id = ?")->execute([$id]);
        } else {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM contributi_localita
                  WHERE $condAutore AND creato_il > NOW() - INTERVAL '1 day'"
            );
            $stmt->execute([$parAutore]);
            if ((int)$stmt->fetchColumn() >= DIARIO_MAX_CONTRIBUTI_GIORNO) {
                $pdo->rollBack();
                diario_errore('Hai raggiunto il numero massimo di contributi inseribili in un giorno.', 429);
            }
            $colonnaAutore = $attore['tipo'] === 'soggetto' ? 'soggetto_id' : 'utente_id';
            $colonne = array_merge([$colonnaAutore], array_keys($valori));
            $stmt = $pdo->prepare(
                "INSERT INTO contributi_localita (" . implode(', ', $colonne) . ")
                 VALUES (" . implode(', ', array_fill(0, count($colonne), '?')) . ") RETURNING id"
            );
            $stmt->execute(array_merge([$parAutore], array_values($valori)));
            $id = (int)$stmt->fetchColumn();
        }
        $stmtTratta = $pdo->prepare(
            "INSERT INTO contributi_tratte (contributo_id, ordine, mezzo, da_luogo, a_luogo, compagnia,
                                            durata_indicativa, costo_indicativo, valuta, note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        foreach ($tratte as $tratta) {
            $stmtTratta->execute(array_merge([$id], $tratta));
        }
        $pdo->commit();
        diario_json(['ok' => true, 'id' => $id]);

    case 'contributo_elimina':
        [$condAutore, $parAutore] = diario_autore($attore);
        $stmt = $pdo->prepare("DELETE FROM contributi_localita WHERE id = ? AND $condAutore");
        $stmt->execute([(int)($dati['id'] ?? 0), $parAutore]);
        if ($stmt->rowCount() !== 1) {
            diario_errore('Contributo non trovato o non modificabile.', 403);
        }
        diario_json(['ok' => true]);

    case 'contributo_visibilita':
        // Moderazione: solo l'admin nasconde o mostra un contributo (D10).
        if (!$attore['admin']) {
            diario_errore('Non autorizzato.', 403);
        }
        $stmt = $pdo->prepare("UPDATE contributi_localita SET visibile = ?::boolean WHERE id = ?");
        $stmt->execute([($dati['visibile'] ?? false) === true ? 'true' : 'false', (int)($dati['id'] ?? 0)]);
        if ($stmt->rowCount() !== 1) {
            diario_errore('Contributo non trovato.', 404);
        }
        diario_json(['ok' => true]);

    default:
        diario_errore('Azione non valida.');
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('diario_rsm_api ' . $azione . ': ' . $e->getMessage());
    diario_errore('Operazione non riuscita.', 500);
}
