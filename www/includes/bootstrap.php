<?php
/**
 * includes/bootstrap.php — Configurazione centralizzata dell'applicazione
 * Astrologia Attiva — Scuola Ciro Discepolo
 *
 * Questo file è l'unico punto in cui vengono lette le credenziali del
 * database e le impostazioni di ambiente. Tutti gli altri file PHP lo
 * includono tramite:
 *
 * require_once __DIR__ . '/bootstrap.php';   (da dentro includes/)
 * require_once 'includes/bootstrap.php';     (dalla root www/)
 * require_once '../includes/bootstrap.php';  (da api/ o cartelle figlie)
 *
 * Variabili d'ambiente supportate (con fallback ai valori Docker Compose):
 * DB_HOST       default: astro-db
 * DB_PORT       default: 5432
 * DB_NAME       default: astrologia
 * DB_USER       default: astro
 * DB_PASS       Letta esclusivamente dall'ambiente (Docker / .env)
 * APP_ENV       default: production   (valori: development | production)
 * APP_DEBUG     default: false
 *
 * Impostare le variabili nel docker-compose.yml oppure in un file .env
 * caricato dal container (es. env_file: [.env]).
 *
 * SICUREZZA: questo file non va mai committato con credenziali reali.
 * Usare variabili d'ambiente in produzione; il .env deve stare in .gitignore.
 */

declare(strict_types=1);

// ── Protezione: impedisce inclusione diretta dal browser ──────────────────
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

// ── Ambiente ──────────────────────────────────────────────────────────────
define('APP_ENV',   getenv('APP_ENV')   ?: 'production');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

// ── Feature flag: allineamento MyAstral (roadmap docs/ROADMAP_MYASTRAL_UX.md) ──
// Attiva il punteggio "Discepolo parziale" calcolato da RuleEngineExtended.php,
// IN AGGIUNTA alle stelline esistenti — non le sostituisce mai.
// Default OFF: a flag disattivo il comportamento dell'app resta identico a
// prima di questa feature. Decisione UX-0001 (docs/ux-myastral/DECISION_LOG_ux.md).
define('MYASTRAL_ALIGNMENT_MODE', filter_var(getenv('MYASTRAL_ALIGNMENT_MODE') ?: 'false', FILTER_VALIDATE_BOOLEAN));

// ── Configurazione database ───────────────────────────────────────────────
// bootstrap.php — FASE DEFINITIVA
// Fallback rimosso: la credenziale reale vive esclusivamente in .env
// (dev) o nelle variabili d'ambiente iniettate dall'orchestratore (prod).
// Se DB_PASS non è impostata, l'app deve fallire in modo esplicito
// piuttosto che connettersi con una password sbagliata/di default.

if (getenv('DB_PASS') === false) {
    throw new RuntimeException(
        'DB_PASS non impostata. Verificare che il file .env sia presente ' .
        '(vedi .env.example) o che le variabili d\'ambiente siano configurate.'
    );
}

define('DB_HOST', getenv('DB_HOST') ?: 'astro-db');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 5432));
define('DB_NAME', getenv('DB_NAME') ?: 'astrologia');
define('DB_USER', getenv('DB_USER') ?: 'astro');
define('DB_PASS', getenv('DB_PASS'));

/** DSN PostgreSQL pronto per new PDO() */
define('DB_DSN',  sprintf(
    'pgsql:host=%s;port=%d;dbname=%s',
    DB_HOST, DB_PORT, DB_NAME
));

// ── Timezone di sistema (sempre UTC per i calcoli astronomici) ────────────
date_default_timezone_set('UTC');

// ── Reporting errori ──────────────────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors',     '1');
}

// ── Helper: crea una connessione PDO con le impostazioni standard ─────────
/**
 * Restituisce una nuova istanza PDO configurata con le costanti del bootstrap.
 * Attributi impostati di default:
 * - ERRMODE_EXCEPTION: lancia PDOException su errori SQL
 * - DEFAULT_FETCH_MODE: FETCH_ASSOC (array associativo)
 * - EMULATE_PREPARES: false (statement nativi PostgreSQL)
 *
 * Uso:
 * $pdo = db_connect();
 */
function db_connect(): PDO
{
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}