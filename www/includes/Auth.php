<?php
/**
 * Auth.php — Classe di autenticazione e gestione sessione
 * Astrologia Attiva — Ciro Discepolo
 */
class Auth {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ── LOGIN / LOGOUT ────────────────────────────────────────────

    public function login(string $username, string $password): array {
        $stmt = $this->pdo->prepare(
            "SELECT u.id, u.username, u.email, u.password_hash, u.ruolo, u.attivo,
                    u.account_status, p.code AS piano
             FROM utenti u
             LEFT JOIN piani p ON p.id = u.plan_id
             WHERE LOWER(TRIM(u.username)) = LOWER(TRIM(?)) LIMIT 1"
        );
        $stmt->execute([trim($username)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['attivo']) {
            return ['ok' => false, 'errore' => 'Credenziali non valide.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['ok' => false, 'errore' => 'Credenziali non valide.'];
        }

        if ($user['account_status'] === 'pending_email') {
            return [
                'ok' => false,
                'errore' => 'Devi verificare il tuo indirizzo email prima di accedere.',
            ];
        }

        if ($user['account_status'] !== 'active') {
            return [
                'ok' => false,
                'errore' => 'Account non disponibile.',
            ];
        }

        // Aggiorna ultimo_accesso
        $this->pdo->prepare(
            "UPDATE utenti SET ultimo_accesso = NOW() WHERE id = ?"
        )->execute([$user['id']]);

        // Rigenera session ID per prevenire session fixation
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['utente'] = [
            'id'             => (int)$user['id'],
            'username'       => $user['username'],
            'email'          => $user['email'],
            'ruolo'          => $user['ruolo'],
            'account_status' => $user['account_status'],
            'piano'          => $user['piano'],
        ];

        // Chiavi legacy mantenute per retrocompatibilità.
        $_SESSION['utente_id']       = $user['id'];
        $_SESSION['utente_username'] = $user['username'];
        $_SESSION['utente_ruolo']    = $user['ruolo'];
        $_SESSION['soggetto_id']     = null;
        $_SESSION['soggetto_nome']   = null;

        return [
            'ok'       => true,
            'id'       => $user['id'],
            'username' => $user['username'],
            'ruolo'    => $user['ruolo'],
        ];
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }
        session_destroy();
    }

    // ── LOGIN SOGGETTI ────────────────────────────────────────────
    // docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md, blocco B (C9-C12).
    // Credenziali in accessi_soggetti, username = soggetti.codice. La sessione
    // del soggetto usa solo $_SESSION['accesso_soggetto'] e non contiene mai
    // utente_id: tutte le pagine protette da richiediLogin() restano chiuse.

    private const SOGGETTO_MAX_TENTATIVI = 5;
    private const SOGGETTO_BLOCCO_MINUTI = 15;
    // Hash bcrypt di una stringa casuale, usato solo per uguagliare i tempi di
    // risposta quando il codice non esiste (nessuna password corrisponde).
    private const SOGGETTO_HASH_FITTIZIO = '$2y$10$HWE6uECEKb3kBVxzhiNpKuZHMZPjNbuKNpAhC22MFmJCmu4vg2U3.';

    /** Vero se il testo ha la forma di un CODICE soggetto (2 lettere + cifre). */
    public function isFormaCodiceSoggetto(string $testo): bool
    {
        return $this->usernameFormaCodice($testo);
    }

    public function loginSoggetto(string $codice, string $password, string $ip): array
    {
        $erroreGenerico = ['ok' => false, 'errore' => 'Credenziali non valide.'];
        $erroreBlocco   = ['ok' => false, 'errore' => 'Troppi tentativi di accesso. Riprova tra qualche minuto.'];
        $ipHash = hash('sha256', $ip);
        $codice = strtoupper(trim($codice));

        // Pulizia dei tentativi piu' vecchi di un giorno.
        $this->pdo->exec(
            "DELETE FROM tentativi_login_soggetti WHERE creato_il < NOW() - INTERVAL '1 day'"
        );

        // Limite per IP: impedisce di provare molti codici in sequenza.
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM tentativi_login_soggetti
              WHERE ip_hash = ? AND creato_il > NOW() - (? * INTERVAL '1 minute')"
        );
        $stmt->execute([$ipHash, self::SOGGETTO_BLOCCO_MINUTI]);
        if ((int)$stmt->fetchColumn() >= self::SOGGETTO_MAX_TENTATIVI) {
            return $erroreBlocco;
        }

        $stmt = $this->pdo->prepare(
            "SELECT a.soggetto_id, a.password_hash, a.attivo, a.deve_cambiare_password,
                    (a.bloccato_fino IS NOT NULL AND a.bloccato_fino > NOW()) AS bloccato,
                    s.codice
               FROM accessi_soggetti a
               JOIN soggetti s ON s.id = a.soggetto_id
              WHERE UPPER(TRIM(s.codice)) = ?
              LIMIT 1"
        );
        $stmt->execute([$codice]);
        $accesso = $stmt->fetch(PDO::FETCH_ASSOC);

        // Limite per account.
        if ($accesso && $accesso['bloccato']) {
            return $erroreBlocco;
        }

        // Verifica sempre una password, anche per un codice inesistente, cosi'
        // i tempi di risposta non rivelano quali codici esistono.
        $hash = $accesso
            ? (string)$accesso['password_hash']
            : self::SOGGETTO_HASH_FITTIZIO;
        $passwordValida = password_verify($password, $hash);

        if (!$accesso || !$accesso['attivo'] || !$passwordValida) {
            $this->pdo->prepare(
                "INSERT INTO tentativi_login_soggetti (ip_hash) VALUES (?)"
            )->execute([$ipHash]);
            if ($accesso) {
                $stmt = $this->pdo->prepare(
                    "UPDATE accessi_soggetti SET tentativi_falliti = tentativi_falliti + 1
                      WHERE soggetto_id = ? RETURNING tentativi_falliti"
                );
                $stmt->execute([(int)$accesso['soggetto_id']]);
                if ((int)$stmt->fetchColumn() >= self::SOGGETTO_MAX_TENTATIVI) {
                    $this->pdo->prepare(
                        "UPDATE accessi_soggetti
                            SET tentativi_falliti = 0,
                                bloccato_fino = NOW() + (? * INTERVAL '1 minute')
                          WHERE soggetto_id = ?"
                    )->execute([self::SOGGETTO_BLOCCO_MINUTI, (int)$accesso['soggetto_id']]);
                }
            }
            return $erroreGenerico;
        }

        $this->pdo->prepare(
            "UPDATE accessi_soggetti
                SET tentativi_falliti = 0, bloccato_fino = NULL, ultimo_accesso = NOW()
              WHERE soggetto_id = ?"
        )->execute([(int)$accesso['soggetto_id']]);

        // Rigenera session ID per prevenire session fixation
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        // Nessuna chiave da astrologo nella sessione del soggetto.
        $_SESSION = [];
        $_SESSION['accesso_soggetto'] = [
            'soggetto_id'            => (int)$accesso['soggetto_id'],
            'codice'                 => (string)$accesso['codice'],
            'deve_cambiare_password' => (bool)$accesso['deve_cambiare_password'],
        ];

        return [
            'ok'                     => true,
            'deve_cambiare_password' => (bool)$accesso['deve_cambiare_password'],
        ];
    }

    public function isLoggedInSoggetto(): bool
    {
        return !empty($_SESSION['accesso_soggetto']['soggetto_id']);
    }

    /**
     * Richiede il login di un soggetto. Ricontrolla nel DB che l'accesso sia
     * ancora attivo (l'astrologo puo' disattivarlo in ogni momento) e, se il
     * cambio password e' ancora dovuto, rimanda alla pagina di cambio.
     */
    public function richiediLoginSoggetto(bool $consentiCambioPassword = false): array
    {
        if (!$this->isLoggedInSoggetto()) {
            header('Location: /login.php');
            exit;
        }
        $stmt = $this->pdo->prepare(
            "SELECT a.attivo, a.deve_cambiare_password, s.codice
               FROM accessi_soggetti a
               JOIN soggetti s ON s.id = a.soggetto_id
              WHERE a.soggetto_id = ?"
        );
        $stmt->execute([(int)$_SESSION['accesso_soggetto']['soggetto_id']]);
        $riga = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$riga || !$riga['attivo']) {
            $this->logout();
            header('Location: /login.php');
            exit;
        }
        $_SESSION['accesso_soggetto']['codice'] = (string)$riga['codice'];
        $_SESSION['accesso_soggetto']['deve_cambiare_password'] = (bool)$riga['deve_cambiare_password'];
        if ($riga['deve_cambiare_password'] && !$consentiCambioPassword) {
            header('Location: /cambio_password_soggetto.php');
            exit;
        }
        return $_SESSION['accesso_soggetto'];
    }

    public function cambiaPasswordSoggetto(int $soggettoId, string $attuale, string $nuova): array
    {
        if (strlen($nuova) < 8) {
            return ['ok' => false, 'errore' => 'La nuova password deve avere almeno 8 caratteri.'];
        }
        if ($nuova === $attuale) {
            return ['ok' => false, 'errore' => 'La nuova password deve essere diversa da quella attuale.'];
        }
        $stmt = $this->pdo->prepare(
            "SELECT password_hash FROM accessi_soggetti WHERE soggetto_id = ? AND attivo"
        );
        $stmt->execute([$soggettoId]);
        $hash = $stmt->fetchColumn();
        if ($hash === false || !password_verify($attuale, (string)$hash)) {
            return ['ok' => false, 'errore' => 'Password attuale non corretta.'];
        }
        $this->pdo->prepare(
            "UPDATE accessi_soggetti
                SET password_hash = ?, deve_cambiare_password = FALSE,
                    password_impostata_il = NOW()
              WHERE soggetto_id = ?"
        )->execute([password_hash($nuova, PASSWORD_DEFAULT), $soggettoId]);

        if (!headers_sent()) {
            session_regenerate_id(true);
        }
        if ($this->isLoggedInSoggetto()) {
            $_SESSION['accesso_soggetto']['deve_cambiare_password'] = false;
        }
        return ['ok' => true];
    }

    /**
     * Abilita (o reimposta) l'accesso di un soggetto con una password
     * provvisoria generata dal sistema (C11). La password viene restituita una
     * sola volta: nel DB resta solo il suo hash. Il chiamante deve aver gia'
     * verificato che il soggetto appartenga all'astrologo $astrologoId.
     */
    public function abilitaAccessoSoggetto(int $soggettoId, int $astrologoId): array
    {
        $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $password = '';
        for ($i = 0; $i < 12; $i++) {
            $password .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
        }
        $this->pdo->prepare(
            "INSERT INTO accessi_soggetti (soggetto_id, password_hash, abilitato_da)
             VALUES (?, ?, ?)
             ON CONFLICT (soggetto_id) DO UPDATE
             SET password_hash = EXCLUDED.password_hash, attivo = TRUE,
                 deve_cambiare_password = TRUE, tentativi_falliti = 0,
                 bloccato_fino = NULL, password_impostata_il = NOW(),
                 abilitato_da = EXCLUDED.abilitato_da"
        )->execute([$soggettoId, password_hash($password, PASSWORD_DEFAULT), $astrologoId]);
        return ['ok' => true, 'password' => $password];
    }

    /**
     * Disattiva l'accesso di un soggetto. Una sessione del soggetto gia' aperta
     * viene chiusa alla richiesta successiva (richiediLoginSoggetto()).
     */
    public function disattivaAccessoSoggetto(int $soggettoId): array
    {
        $stmt = $this->pdo->prepare(
            "UPDATE accessi_soggetti SET attivo = FALSE WHERE soggetto_id = ?"
        );
        $stmt->execute([$soggettoId]);
        if ($stmt->rowCount() !== 1) {
            return ['ok' => false, 'errore' => "L'accesso di questo soggetto non e' abilitato."];
        }
        return ['ok' => true];
    }

    /**
     * Soggetto loggato con accesso ancora attivo, senza reindirizzare (per le API).
     * Restituisce null se non c'e' un soggetto loggato, se l'accesso e' stato
     * disattivato o se il cambio password al primo accesso e' ancora dovuto.
     */
    public function soggettoCorrente(): ?array
    {
        if (!$this->isLoggedInSoggetto()) {
            return null;
        }
        $stmt = $this->pdo->prepare(
            "SELECT a.attivo, a.deve_cambiare_password, s.codice
               FROM accessi_soggetti a
               JOIN soggetti s ON s.id = a.soggetto_id
              WHERE a.soggetto_id = ?"
        );
        $stmt->execute([(int)$_SESSION['accesso_soggetto']['soggetto_id']]);
        $riga = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$riga || !$riga['attivo'] || $riga['deve_cambiare_password']) {
            return null;
        }
        return [
            'soggetto_id' => (int)$_SESSION['accesso_soggetto']['soggetto_id'],
            'codice'      => (string)$riga['codice'],
        ];
    }

    // ── CONTROLLI ─────────────────────────────────────────────────

    public function isLoggedIn(): bool {
        return !empty($_SESSION['utente_id']);
    }

    /**
     * Richiede login. Se non loggato, reindirizza a login.php e termina.
     */
    public function richiediLogin(string $redirectUrl = ''): void {
        if (!$this->isLoggedIn()) {
            $back = $redirectUrl ?: $_SERVER['REQUEST_URI'];
            header('Location: /login.php?next=' . urlencode($back));
            exit;
        }
    }

    public function hasRole(string $ruolo): bool {
        return $this->getCurrentRuolo() === $ruolo;
    }

    public function isAdmin(): bool {
        return $this->hasRole('admin');
    }

    public function hasAccountStatus(string $status): bool {
        return $this->getCurrentAccountStatus() === $status;
    }

    public function hasPiano(string $piano): bool {
        return $this->getCurrentPiano() === $piano;
    }

    /**
     * Restituisce il numero massimo di risultati confrontabili.
     *
     * Il piano gratuito può confrontare fino a 2 risultati.
     * Supporter e amministratori possono confrontarne fino a 3.
     */
    public function getComparatorLimit(): int {
        if ($this->isAdmin() || $this->hasPiano('supporter')) {
            return 3;
        }

        return 2;
    }

    /**
     * Verifica se il piano corrente può utilizzare una funzionalità.
     */
    public function hasFeature(string $feature): bool {
        if ($this->isAdmin()) {
            return true;
        }

        $piano = $this->getCurrentPiano();

        return match ($feature) {
            'airport_search' => true,
            'country_list' => true,
            'locality_search' => $piano === 'supporter',
            'grid_search' => $piano === 'supporter',
            'dynamic_orb' => $piano === 'supporter',
            'astri_in_cuspide' => $piano === 'supporter', // UX-0014
            'foto_profilo' => $piano === 'supporter',
            default => false,
        };
    }

    /**
     * Richiede ruolo admin. Se non admin, reindirizza a index.php e termina.
     */
    public function richiediAdmin(): void {
        $this->richiediLogin();
        if (!$this->isAdmin()) {
            header('Location: /index.php?errore=accesso_negato');
            exit;
        }
    }

    // ── GETTERS ───────────────────────────────────────────────────

    public function getCurrentUser(): ?array {
        $user = $_SESSION['utente'] ?? null;
        return is_array($user) ? $user : null;
    }

    public function getCurrentUserId(): ?int {
        $id = $this->getCurrentUser()['id'] ?? $_SESSION['utente_id'] ?? null;
        return $id !== null ? (int)$id : null;
    }

    public function getCurrentUsername(): string {
        return (string)($this->getCurrentUser()['username'] ?? $_SESSION['utente_username'] ?? '');
    }

    public function getCurrentRuolo(): string {
        return (string)($this->getCurrentUser()['ruolo'] ?? $_SESSION['utente_ruolo'] ?? '');
    }

    public function getCurrentAccountStatus(): string {
        return (string)($this->getCurrentUser()['account_status'] ?? '');
    }

    public function getCurrentPiano(): string {
        return (string)($this->getCurrentUser()['piano'] ?? '');
    }

    // ── SOGGETTO ATTIVO ───────────────────────────────────────────

    /**
     * Imposta il soggetto attivo verificando che appartenga all'utente loggato.
     * L'admin può impostare qualsiasi soggetto.
     */
    public function setSoggettoAttivo(int $soggettoId): bool {
        $userId = $this->getCurrentUserId();
        if (!$userId) return false;

        if ($this->isAdmin()) {
            $stmt = $this->pdo->prepare(
                "SELECT id, nome FROM soggetti WHERE id = ?"
            );
            $stmt->execute([$soggettoId]);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT id, nome FROM soggetti WHERE id = ? AND utente_id = ?"
            );
            $stmt->execute([$soggettoId, $userId]);
        }

        $soggetto = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$soggetto) return false;

        $_SESSION['soggetto_id']   = $soggetto['id'];
        $_SESSION['soggetto_nome'] = $soggetto['nome'];
        return true;
    }

    public function getSoggettoAttivo(): ?int {
        return $_SESSION['soggetto_id'] ?? null;
    }

    public function getSoggettoNome(): string {
        return $_SESSION['soggetto_nome'] ?? '';
    }

    public function clearSoggettoAttivo(): void {
        $_SESSION['soggetto_id']   = null;
        $_SESSION['soggetto_nome'] = null;
    }

    /**
     * Verifica che il soggetto_id passato appartenga all'utente corrente.
     * Usata nelle API per validare i parametri in input.
     * Restituisce i dati del soggetto o null se non autorizzato.
     */
    public function verificaSoggetto(int $soggettoId): ?array {
        $userId = $this->getCurrentUserId();
        if (!$userId) return null;

        if ($this->isAdmin()) {
            $stmt = $this->pdo->prepare("SELECT * FROM soggetti WHERE id = ?");
            $stmt->execute([$soggettoId]);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM soggetti WHERE id = ? AND utente_id = ?"
            );
            $stmt->execute([$soggettoId, $userId]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ── REGISTRAZIONE PUBBLICA ───────────────────────────────────

    public function registraUtentePubblico(
        string $username,
        string $email,
        string $password,
        string $nome = '',
        string $cognome = ''
    ): array {
        $username = trim($username);
        $email = mb_strtolower(trim($email));
        // Nome e Cognome obbligatori, spazi multipli ridotti a uno
        // (ROADMAP_CODICE_LOGIN_SOGGETTI.md, C5).
        $nome    = trim((string)preg_replace('/\s+/u', ' ', $nome));
        $cognome = trim((string)preg_replace('/\s+/u', ' ', $cognome));

        if (preg_match('/^[a-zA-Z0-9._-]{3,60}$/', $username) !== 1) {
            return ['ok' => false, 'errore' => 'Username non valido: usa 3-60 caratteri, lettere, numeri, punto, trattino o underscore.'];
        }
        if ($this->usernameFormaCodice($username)) {
            return ['ok' => false, 'errore' => 'Username non valido: non può avere la forma di un codice soggetto (es. LD001).'];
        }
        if ($nome === '' || $cognome === ''
            || mb_strlen($nome) > 100 || mb_strlen($cognome) > 100
            || preg_match('/\p{L}/u', $nome) !== 1 || preg_match('/\p{L}/u', $cognome) !== 1) {
            return ['ok' => false, 'errore' => 'Inserisci nome e cognome (massimo 100 caratteri ciascuno).'];
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 200) {
            return ['ok' => false, 'errore' => 'Indirizzo email non valido.'];
        }
        if (strlen($password) < 8) {
            return ['ok' => false, 'errore' => 'Password troppo corta (min 8 caratteri).'];
        }

        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->pdo->prepare(
                "INSERT INTO utenti (
                    username, email, password_hash, ruolo, attivo,
                    account_status, plan_id, nome_completo
                 )
                 SELECT ?, ?, ?, 'user', TRUE, 'active', id, ?
                 FROM piani
                 WHERE code = 'free' AND is_active = TRUE
                 LIMIT 1
                 RETURNING id"
            );
            $stmt->execute([$username, $email, $hash, $nome . ' ' . $cognome]);
            $id = $stmt->fetchColumn();

            if ($id === false) {
                return ['ok' => false, 'errore' => 'Piano gratuito non disponibile.'];
            }

            // Prefisso del CODICE soggetti (C1, C3-C5). Un errore qui non blocca
            // la registrazione: il prefisso verra' calcolato al primo soggetto.
            try {
                require_once __DIR__ . '/CodiceSoggetto.php';
                $prefisso = CodiceSoggetto::calcolaPrefisso($this->pdo, $nome, $cognome, (int)$id);
                $this->pdo->prepare(
                    "UPDATE utenti SET prefisso_codice = ? WHERE id = ? AND prefisso_codice IS NULL"
                )->execute([$prefisso, (int)$id]);
            } catch (Throwable $e) {
                error_log('registraUtentePubblico prefisso: ' . $e->getMessage());
            }

            $verificationToken = $this->creaTokenSicurezza(
                (int)$id,
                'email_verification'
            );

            return [
                'ok' => true,
                'id' => (int)$id,
                'verification_token' => $verificationToken,
            ];
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                return ['ok' => false, 'errore' => 'Username o email già registrati.'];
            }
            return ['ok' => false, 'errore' => 'Registrazione non disponibile.'];
        }
    }


    /**
     * Vero se lo username ha la forma di un CODICE soggetto (2 lettere + sole
     * cifre, es. LD001): vietato per gli astrologi (C12), per non creare
     * ambiguita' con il login dei soggetti.
     */
    private function usernameFormaCodice(string $username): bool
    {
        return preg_match('/^[A-Za-z]{2}[0-9]+$/', trim($username)) === 1;
    }

    // ── TOKEN SICUREZZA ───────────────────────────────────────────

    private function creaTokenSicurezza(
        int $userId,
        string $purpose,
        int $validitaOre = 24,
        ?string $ip = null
    ): string {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        $this->pdo->prepare(
            "DELETE FROM token_sicurezza
             WHERE user_id = ?
               AND purpose = ?"
        )->execute([$userId, $purpose]);

        $this->pdo->prepare(
            "INSERT INTO token_sicurezza
                (user_id, purpose, token_hash, expires_at, requested_ip)
             VALUES
                (?, ?, ?, NOW() + (? || ' hours')::interval, ?)"
        )->execute([
            $userId,
            $purpose,
            $hash,
            $validitaOre,
            $ip
        ]);

        return $token;
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function richiediNuovoTokenVerifica(string $email): array
    {
        $email = mb_strtolower(trim($email));

        $stmt = $this->pdo->prepare(
            "SELECT id, account_status
             FROM utenti
             WHERE email = ?
             LIMIT 1"
        );
        $stmt->execute([$email]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$utente) {
            return [
                'ok' => false,
                'errore' => 'Richiesta non disponibile.',
            ];
        }

        if ($utente['account_status'] === 'active') {
            return [
                'ok' => false,
                'errore' => 'Account già verificato.',
            ];
        }

        $token = $this->creaTokenSicurezza(
            (int)$utente['id'],
            'email_verification'
        );

        return [
            'ok' => true,
            'verification_token' => $token,
        ];
    }

    public function richiediResetPassword(string $email): array
    {
        $email = mb_strtolower(trim($email));

        $stmt = $this->pdo->prepare(
            "SELECT id
             FROM utenti
             WHERE email = ?
             LIMIT 1"
        );
        $stmt->execute([$email]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$utente) {
            return [
                'ok' => false,
                'errore' => 'Richiesta non disponibile.',
            ];
        }

        $token = $this->creaTokenSicurezza(
            (int)$utente['id'],
            'password_reset'
        );

        return [
            'ok' => true,
            'reset_token' => $token,
        ];
    }

    public function confermaResetPassword(
        string $token,
        string $nuovaPassword
    ): array {
        if (strlen($nuovaPassword) < 8) {
            return [
                'ok' => false,
                'errore' => 'Password troppo corta (min 8 caratteri).',
            ];
        }

        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return [
                'ok' => false,
                'errore' => 'Token reset non valido.',
            ];
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        try {
            if ($ownsTransaction) {
                $this->pdo->beginTransaction();
            } else {
                $this->pdo->exec('SAVEPOINT conferma_reset_password');
            }

            $stmt = $this->pdo->prepare(
                "SELECT id, user_id
                 FROM token_sicurezza
                 WHERE token_hash = ?
                   AND purpose = 'password_reset'
                   AND used_at IS NULL
                   AND expires_at > NOW()
                 FOR UPDATE"
            );
            $stmt->execute([$this->hashToken($token)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                if ($ownsTransaction) {
                    $this->pdo->rollBack();
                } else {
                    $this->pdo->exec('ROLLBACK TO SAVEPOINT conferma_reset_password');
                    $this->pdo->exec('RELEASE SAVEPOINT conferma_reset_password');
                }

                return [
                    'ok' => false,
                    'errore' => 'Token reset non valido, scaduto o già utilizzato.',
                ];
            }

            $hash = password_hash(
                $nuovaPassword,
                PASSWORD_BCRYPT,
                ['cost' => 12]
            );

            $this->pdo->prepare(
                "UPDATE utenti
                 SET password_hash = ?
                 WHERE id = ?"
            )->execute([
                $hash,
                (int)$row['user_id'],
            ]);

            $this->pdo->prepare(
                "UPDATE token_sicurezza
                 SET used_at = NOW()
                 WHERE id = ?"
            )->execute([
                (int)$row['id'],
            ]);

            if ($ownsTransaction) {
                $this->pdo->commit();
            } else {
                $this->pdo->exec('RELEASE SAVEPOINT conferma_reset_password');
            }

            return [
                'ok' => true,
                'user_id' => (int)$row['user_id'],
            ];

        } catch (Throwable $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            } elseif (!$ownsTransaction && $this->pdo->inTransaction()) {
                try {
                    $this->pdo->exec('ROLLBACK TO SAVEPOINT conferma_reset_password');
                    $this->pdo->exec('RELEASE SAVEPOINT conferma_reset_password');
                } catch (Throwable $ignored) {
                }
            }

            return [
                'ok' => false,
                'errore' => 'Reset password temporaneamente non disponibile.',
            ];
        }
    }

    public function verificaEmailToken(string $token): array
    {
        $token = trim($token);

        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return ['ok' => false, 'errore' => 'Token di verifica non valido.'];
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        try {
            if ($ownsTransaction) {
                $this->pdo->beginTransaction();
            } else {
                $this->pdo->exec('SAVEPOINT verifica_email_token');
            }

            $stmt = $this->pdo->prepare(
                "SELECT id, user_id
                 FROM token_sicurezza
                 WHERE token_hash = ?
                   AND purpose = 'email_verification'
                   AND used_at IS NULL
                   AND expires_at > NOW()
                 FOR UPDATE"
            );
            $stmt->execute([$this->hashToken($token)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                if ($ownsTransaction) {
                    $this->pdo->rollBack();
                } else {
                    $this->pdo->exec('ROLLBACK TO SAVEPOINT verifica_email_token');
                    $this->pdo->exec('RELEASE SAVEPOINT verifica_email_token');
                }

                return [
                    'ok' => false,
                    'errore' => 'Token non valido, scaduto o già utilizzato.',
                ];
            }

            $this->pdo->prepare(
                "UPDATE utenti
                 SET account_status = 'active',
                     email_verified_at = COALESCE(email_verified_at, NOW())
                 WHERE id = ?"
            )->execute([(int)$row['user_id']]);

            $this->pdo->prepare(
                "UPDATE token_sicurezza
                 SET used_at = NOW()
                 WHERE id = ?"
            )->execute([(int)$row['id']]);

            if ($ownsTransaction) {
                $this->pdo->commit();
            } else {
                $this->pdo->exec('RELEASE SAVEPOINT verifica_email_token');
            }

            return [
                'ok' => true,
                'user_id' => (int)$row['user_id'],
            ];
        } catch (Throwable $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            } elseif (!$ownsTransaction && $this->pdo->inTransaction()) {
                try {
                    $this->pdo->exec('ROLLBACK TO SAVEPOINT verifica_email_token');
                    $this->pdo->exec('RELEASE SAVEPOINT verifica_email_token');
                } catch (Throwable $ignored) {
                }
            }

            return [
                'ok' => false,
                'errore' => 'Verifica email temporaneamente non disponibile.',
            ];
        }
    }

    // ── LIMITI EFFETTIVI (piano, override, accesso speciale) ────────

    /**
     * Restituisce il numero massimo di soggetti che l'utente può creare.
     * null = nessun limite (illimitato).
     *
     * Precedenza:
     *   1. accesso speciale permanente (concesso dall'admin) -> illimitato
     *   2. limite personalizzato per il singolo utente (override)
     *   3. limite del piano effettivo (Supporter scaduto -> trattato come free)
     */
    public function getLimiteSoggettiEffettivo(?int $utenteId = null): ?int {
        $utenteId = $utenteId ?? $this->getCurrentUserId();
        if (!$utenteId) {
            return 0;
        }

        $stmt = $this->pdo->prepare(
            "SELECT u.subjects_limit_override, u.accesso_speciale_permanente,
                    u.supporter_scadenza, p.code AS piano
             FROM utenti u
             LEFT JOIN piani p ON p.id = u.plan_id
             WHERE u.id = ?"
        );
        $stmt->execute([$utenteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return 0;
        }

        if ($row['accesso_speciale_permanente']) {
            return null;
        }

        if ($row['subjects_limit_override'] !== null) {
            return (int)$row['subjects_limit_override'];
        }

        $pianoEffettivo = $row['piano'];
        if ($pianoEffettivo === 'supporter'
            && $row['supporter_scadenza'] !== null
            && $row['supporter_scadenza'] < date('Y-m-d')
        ) {
            $pianoEffettivo = 'free';
        }

        $limitStmt = $this->pdo->prepare(
            "SELECT pl.limit_value
             FROM piani p
             JOIN piano_limiti pl ON pl.plan_id = p.id
             WHERE p.code = ? AND pl.feature_code = 'subjects_max' AND pl.enabled = TRUE
             LIMIT 1"
        );
        $limitStmt->execute([$pianoEffettivo]);
        $value = $limitStmt->fetchColumn();

        return ($value !== false && $value !== null) ? (int)$value : null;
    }

    // ── GESTIONE UTENTI (admin) ───────────────────────────────────

    public function creaUtente(
        string $username,
        string $email,
        string $password,
        string $ruolo          = 'user',
        string $nomeCompleto   = '',
        string $telefono       = '',
        string $note           = ''
    ): array {
        if ($this->usernameFormaCodice($username)) {
            return ['ok' => false, 'errore' => 'Username non valido: non può avere la forma di un codice soggetto (es. LD001).'];
        }
        if (strlen($password) < 8) {
            return ['ok' => false, 'errore' => 'Password troppo corta (min 8 caratteri).'];
        }
        if (!in_array($ruolo, ['admin', 'user'], true)) {
            return ['ok' => false, 'errore' => 'Ruolo non valido.'];
        }
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->pdo->prepare(
                "INSERT INTO utenti (
                    username, email, password_hash, ruolo, attivo,
                    account_status, email_verified_at, plan_id,
                    nome_completo, telefono, note
                 )
                 SELECT ?, ?, ?, ?, TRUE,
                        'active', NOW(), id,
                        ?, ?, ?
                 FROM piani
                 WHERE code = 'supporter' AND is_active = TRUE
                 LIMIT 1"
            );
            $stmt->execute([
                trim($username),
                mb_strtolower(trim($email)),
                $hash,
                $ruolo,
                trim($nomeCompleto) ?: null,
                trim($telefono)     ?: null,
                trim($note)         ?: null,
            ]);

            if ($stmt->rowCount() !== 1) {
                return ['ok' => false, 'errore' => 'Piano Supporter non disponibile.'];
            }
            return ['ok' => true, 'id' => (int)$this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'unique')) {
                return ['ok' => false, 'errore' => 'Username già esistente.'];
            }
            return ['ok' => false, 'errore' => 'Errore database.'];
        }
    }

    /**
     * Aggiorna i dati anagrafici di un utente (senza toccare password e ruolo).
     * Usato dall'admin per modificare nome_completo, telefono, note, email.
     */
    public function aggiornaUtente(
        int    $utenteId,
        string $email        = '',
        string $nomeCompleto = '',
        string $telefono     = '',
        string $note         = ''
    ): array {
        try {
            $this->pdo->prepare(
                "UPDATE utenti
                 SET email = ?, nome_completo = ?, telefono = ?, note = ?
                 WHERE id = ?"
            )->execute([
                trim($email)        ?: null,
                trim($nomeCompleto) ?: null,
                trim($telefono)     ?: null,
                trim($note)         ?: null,
                $utenteId,
            ]);
            return ['ok' => true];
        } catch (PDOException $e) {
            return ['ok' => false, 'errore' => 'Errore aggiornamento: ' . $e->getMessage()];
        }
    }

    public function cambiaPassword(int $utenteId, string $nuovaPassword): array {
        if (strlen($nuovaPassword) < 8) {
            return ['ok' => false, 'errore' => 'Password troppo corta (min 8 caratteri).'];
        }
        $hash = password_hash($nuovaPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->pdo->prepare(
            "UPDATE utenti SET password_hash = ? WHERE id = ?"
        )->execute([$hash, $utenteId]);
        return ['ok' => true];
    }

    public function cambiaPropriaPassword(string $vecchia, string $nuova): array {
        $userId = $this->getCurrentUserId();
        if (!$userId) return ['ok' => false, 'errore' => 'Non autenticato.'];

        $stmt = $this->pdo->prepare(
            "SELECT password_hash FROM utenti WHERE id = ?"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !password_verify($vecchia, $row['password_hash'])) {
            return ['ok' => false, 'errore' => 'Password attuale non corretta.'];
        }
        return $this->cambiaPassword($userId, $nuova);
    }

    public function getListaUtenti(): array {
        return $this->pdo->query(
            "SELECT u.id, u.username, u.email, u.ruolo, u.attivo,
                    u.account_status, u.email_verified_at,
                    u.plan_id, p.code AS piano,
                    u.donazione_importo, u.supporter_inizio, u.supporter_scadenza,
                    u.subjects_limit_override, u.accesso_speciale_permanente, u.note_piano,
                    u.nome_completo, u.telefono, u.note,
                    u.created_at, u.ultimo_accesso,
                    (SELECT COUNT(*) FROM soggetti WHERE utente_id = u.id) AS n_soggetti
             FROM utenti u
             LEFT JOIN piani p ON p.id = u.plan_id
             ORDER BY u.ruolo DESC, u.username"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUtente(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT u.id, u.username, u.email, u.ruolo, u.attivo,
                    u.account_status, u.email_verified_at,
                    u.plan_id, p.code AS piano,
                    u.nome_completo, u.telefono, u.note,
                    u.created_at, u.ultimo_accesso
             FROM utenti u
             LEFT JOIN piani p ON p.id = u.plan_id
             WHERE u.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function toggleAttivo(int $utenteId): bool {
        // Non disattivare se stai tentando di disattivare te stesso
        if ($utenteId === $this->getCurrentUserId()) return false;

        $stmt = $this->pdo->prepare(
            "UPDATE utenti
             SET attivo = NOT attivo,
                 account_status = CASE
                     WHEN attivo THEN 'suspended'
                     ELSE 'active'
                 END,
                 suspended_at = CASE
                     WHEN attivo THEN NOW()
                     ELSE NULL
                 END,
                 suspension_reason = CASE
                     WHEN attivo THEN 'Disattivato da amministratore'
                     ELSE NULL
                 END
             WHERE id = ?"
        );
        $stmt->execute([$utenteId]);

        return $stmt->rowCount() === 1;
    }

    /**
     * Verifica manualmente l'email di un utente in stato pending_email,
     * portandolo direttamente ad account_status = 'active'.
     * Usato dall'admin finché l'invio email reale non è attivo (VPS).
     */
    public function verificaManualmente(int $utenteId): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE utenti
             SET account_status = 'active',
                 email_verified_at = NOW()
             WHERE id = ? AND account_status = 'pending_email'"
        );
        $stmt->execute([$utenteId]);

        return $stmt->rowCount() === 1;
    }

    /**
     * Elimina un utente trasferendo i suoi soggetti a un altro utente.
     * Fix 29-09-2026: rifiuta il trasferimento verso lo stesso utente o verso un
     * utente inesistente (prima causava un errore fatale: soggetti.utente_id e'
     * NOT NULL) ed esegue trasferimento ed eliminazione in un'unica transazione.
     */
    public function eliminaUtente(int $utenteId, int $trasferisciA = 1): array {
        if ($utenteId === $this->getCurrentUserId()) {
            return ['ok' => false, 'errore' => 'Impossibile eliminare il proprio account.'];
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM utenti WHERE id = ?");
        $stmt->execute([$utenteId]);
        if ((int)$stmt->fetchColumn() !== 1) {
            return ['ok' => false, 'errore' => 'Utente non trovato.'];
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM soggetti WHERE utente_id = ?");
        $stmt->execute([$utenteId]);
        $nSoggetti = (int)$stmt->fetchColumn();
        if ($nSoggetti > 0) {
            if ($trasferisciA === $utenteId) {
                return ['ok' => false, 'errore' => 'Scegli un utente diverso a cui trasferire i soggetti.'];
            }
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM utenti WHERE id = ?");
            $stmt->execute([$trasferisciA]);
            if ((int)$stmt->fetchColumn() !== 1) {
                return ['ok' => false, 'errore' => 'Utente di destinazione non valido.'];
            }
        }
        try {
            $this->pdo->beginTransaction();
            if ($nSoggetti > 0) {
                $this->pdo->prepare(
                    "UPDATE soggetti SET utente_id = ? WHERE utente_id = ?"
                )->execute([$trasferisciA, $utenteId]);
            }
            $this->pdo->prepare(
                "DELETE FROM utenti WHERE id = ?"
            )->execute([$utenteId]);
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('eliminaUtente: ' . $e->getMessage());
            return ['ok' => false, 'errore' => 'Eliminazione non riuscita.'];
        }
        return ['ok' => true];
    }

    /**
     * Aggiorna la configurazione del piano di un utente (solo admin).
     * Valida sempre lato server: piano attivo, valori non negativi,
     * coerenza tra data inizio e scadenza Supporter.
     */
    public function aggiornaPianoUtente(
        int $utenteId,
        string $pianoCode,
        ?float $donazioneImporto,
        ?string $supporterInizio,
        ?string $supporterScadenza,
        ?int $subjectsLimitOverride,
        bool $accessoSpecialePermanente,
        string $notePiano = ''
    ): array {
        if (!in_array($pianoCode, ['free', 'supporter'], true)) {
            return ['ok' => false, 'errore' => 'Piano non valido.'];
        }
        if ($donazioneImporto !== null && $donazioneImporto < 0) {
            return ['ok' => false, 'errore' => 'L\'importo della donazione non può essere negativo.'];
        }
        if ($subjectsLimitOverride !== null && $subjectsLimitOverride < 0) {
            return ['ok' => false, 'errore' => 'Il limite soggetti personalizzato non può essere negativo.'];
        }
        if ($supporterInizio !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $supporterInizio)) {
            return ['ok' => false, 'errore' => 'Data inizio Supporter non valida.'];
        }
        if ($supporterScadenza !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $supporterScadenza)) {
            return ['ok' => false, 'errore' => 'Data scadenza Supporter non valida.'];
        }
        if ($supporterInizio !== null && $supporterScadenza !== null && $supporterScadenza < $supporterInizio) {
            return ['ok' => false, 'errore' => 'La scadenza non può precedere la data di inizio.'];
        }

        $planStmt = $this->pdo->prepare(
            "SELECT id FROM piani WHERE code = ? AND is_active = TRUE LIMIT 1"
        );
        $planStmt->execute([$pianoCode]);
        $planId = $planStmt->fetchColumn();

        if ($planId === false) {
            return ['ok' => false, 'errore' => 'Il piano selezionato non è attivo o non esiste.'];
        }

        $this->pdo->prepare(
            "UPDATE utenti
             SET plan_id = ?,
                 donazione_importo = ?,
                 supporter_inizio = ?,
                 supporter_scadenza = ?,
                 subjects_limit_override = ?,
                 accesso_speciale_permanente = ?,
                 note_piano = ?,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = ?"
        )->execute([
            $planId,
            $donazioneImporto,
            $supporterInizio,
            $supporterScadenza,
            $subjectsLimitOverride,
            $accessoSpecialePermanente ? 't' : 'f',
            trim($notePiano) ?: null,
            $utenteId,
        ]);

        return ['ok' => true];
    }

    public function aggiornaRuolo(int $utenteId, string $ruolo): bool {
        if (!in_array($ruolo, ['admin', 'user'], true)) return false;
        $this->pdo->prepare(
            "UPDATE utenti SET ruolo = ? WHERE id = ?"
        )->execute([$ruolo, $utenteId]);
        return true;
    }
}