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
            "SELECT id, username, email, password_hash, ruolo, attivo,
                    account_status, piano
             FROM utenti WHERE username = ? LIMIT 1"
        );
        $stmt->execute([trim($username)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['attivo']) {
            return ['ok' => false, 'errore' => 'Credenziali non valide.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['ok' => false, 'errore' => 'Credenziali non valide.'];
        }

        // Aggiorna ultimo_accesso
        $this->pdo->prepare(
            "UPDATE utenti SET ultimo_accesso = NOW() WHERE id = ?"
        )->execute([$user['id']]);

        // Rigenera session ID per prevenire session fixation
        session_regenerate_id(true);

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
                 SELECT ?, ?, ?, 'user', TRUE, 'pending_email', id
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

            return ['ok' => true, 'id' => (int)$id];
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                return ['ok' => false, 'errore' => 'Username o email già registrati.'];
            }
            return ['ok' => false, 'errore' => 'Registrazione non disponibile.'];
        }
    }

    // ── GESTIONE UTENTI (admin) ───────────────────────────────────

    public function creaUtente(
        string $username,
        string $email,
        string $password,
        string $ruolo          = 'astrologo',
        string $nomeCompleto   = '',
        string $telefono       = '',
        string $note           = ''
    ): array {
        if (strlen($password) < 8) {
            return ['ok' => false, 'errore' => 'Password troppo corta (min 8 caratteri).'];
        }
        if (!in_array($ruolo, ['admin', 'astrologo'])) {
            return ['ok' => false, 'errore' => 'Ruolo non valido.'];
        }
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->pdo->prepare(
                "INSERT INTO utenti (username, email, password_hash, ruolo,
                                     nome_completo, telefono, note)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                trim($username),
                trim($email),
                $hash,
                $ruolo,
                trim($nomeCompleto) ?: null,
                trim($telefono)     ?: null,
                trim($note)         ?: null,
            ]);
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
            "SELECT id, username, email, ruolo, attivo,
                    nome_completo, telefono, note,
                    created_at, ultimo_accesso,
                    (SELECT COUNT(*) FROM soggetti WHERE utente_id = utenti.id) AS n_soggetti
             FROM utenti ORDER BY ruolo DESC, username"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUtente(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT id, username, email, ruolo, attivo,
                    nome_completo, telefono, note,
                    created_at, ultimo_accesso
             FROM utenti WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function toggleAttivo(int $utenteId): bool {
        // Non disattivare se stai tentando di disattivare te stesso
        if ($utenteId === $this->getCurrentUserId()) return false;
        $this->pdo->prepare(
            "UPDATE utenti SET attivo = NOT attivo WHERE id = ?"
        )->execute([$utenteId]);
        return true;
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

    public function aggiornaRuolo(int $utenteId, string $ruolo): bool {
        if (!in_array($ruolo, ['admin', 'astrologo'])) return false;
        $this->pdo->prepare(
            "UPDATE utenti SET ruolo = ? WHERE id = ?"
        )->execute([$ruolo, $utenteId]);
        return true;
    }
}