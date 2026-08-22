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
        string $password
    ): array {
        $username = trim($username);
        $email = mb_strtolower(trim($email));

        if (preg_match('/^[a-zA-Z0-9._-]{3,60}$/', $username) !== 1) {
            return ['ok' => false, 'errore' => 'Username non valido: usa 3-60 caratteri, lettere, numeri, punto, trattino o underscore.'];
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
                    account_status, plan_id
                 )
                 SELECT ?, ?, ?, 'user', TRUE, 'active', id
                 FROM piani
                 WHERE code = 'free' AND is_active = TRUE
                 LIMIT 1
                 RETURNING id"
            );
            $stmt->execute([$username, $email, $hash]);
            $id = $stmt->fetchColumn();

            if ($id === false) {
                return ['ok' => false, 'errore' => 'Piano gratuito non disponibile.'];
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

    public function eliminaUtente(int $utenteId, int $trasferisciA = 1): bool {
        if ($utenteId === $this->getCurrentUserId()) return false;
        // Trasferisci i soggetti
        $this->pdo->prepare(
            "UPDATE soggetti SET utente_id = ? WHERE utente_id = ?"
        )->execute([$trasferisciA, $utenteId]);
        $this->pdo->prepare(
            "DELETE FROM utenti WHERE id = ?"
        )->execute([$utenteId]);
        return true;
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