<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * login.php — Pagina di login
 * Astrologia Attiva — Ciro Discepolo
 */
session_start();

// Se già loggato, vai all'indice
if (!empty($_SESSION['utente_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);

$errore = '';
$next   = $_GET['next'] ?? 'index.php';

function loginClientIp(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 64);
}

function loginRateLimit(string $ip): bool
{
    $windowSeconds = 900;
    $maxAttempts = 10;
    $path = sys_get_temp_dir() . '/astrolab-login-' . hash('sha256', $ip) . '.json';
    $handle = fopen($path, 'c+');

    if ($handle === false) {
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }

        $raw = stream_get_contents($handle);
        $data = is_string($raw) && $raw !== ''
            ? json_decode($raw, true)
            : [];

        $now = time();
        $attempts = [];

        if (is_array($data)) {
            foreach ($data as $timestamp) {
                if (is_int($timestamp) && $timestamp > $now - $windowSeconds) {
                    $attempts[] = $timestamp;
                }
            }
        }

        if (count($attempts) >= $maxAttempts) {
            return false;
        }

        $attempts[] = $now;
        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($attempts, JSON_THROW_ON_ERROR));
        fflush($handle);

        return true;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errore = 'Inserisci username e password.';
    } elseif (!loginRateLimit(loginClientIp())) {
        $errore = 'Troppi tentativi di accesso. Riprova più tardi.';
    } else {
        $result = $auth->login($username, $password);
        if ($result['ok']) {
            // Sicurezza: next deve essere una path relativa, non un URL esterno
            $next = preg_replace('#[^a-zA-Z0-9/_\-\.\?=&]#', '', $next);
            if (empty($next) || str_starts_with($next, '//') || str_contains($next, ':')) {
                $next = 'index.php';
            }
            header('Location: ' . $next);
            exit;
        } else {
            $errore = $result['errore'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso — AstroLab</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #F2EDE4;
        }
        .login-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(44,62,107,0.15);
            padding: 40px 48px;
            width: 100%;
            max-width: 400px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-logo h1 {
            font-size: 22px;
            color: #2C3E6B;
            font-weight: normal;
            letter-spacing: 0.08em;
        }
        .login-logo p {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
            letter-spacing: 0.04em;
        }
        .login-box .form-group {
            margin-bottom: 16px;
        }
        .login-box .form-group label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: block;
            margin-bottom: 5px;
        }
        .login-box .form-group input {
            width: 100%;
            border: 1px solid #D0C8BC;
            border-radius: 5px;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .login-box .form-group input:focus {
            outline: none;
            border-color: #2C3E6B;
        }
        .btn-login {
            width: 100%;
            background: #2C3E6B;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 11px;
            font-size: 14px;
            font-family: inherit;
            cursor: pointer;
            letter-spacing: 0.04em;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .btn-login:hover { background: #3A5090; }
        .errore-login {
            background: #FFEBEE;
            color: #B71C1C;
            border-left: 3px solid #F44336;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .version-note {
            text-align: center;
            font-size: 11px;
            color: #BBB;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <h1>☉ AstroLab</h1>
            <p>Rivoluzioni Solari Mirate — Astrologia Attiva</p>
        </div>

        <?php if ($errore): ?>
        <div class="errore-login">⚠️ <?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php?next=<?= urlencode($next) ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autocomplete="username" autofocus required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn-login">Accedi →</button>
        </form>

        <div class="version-note">Uso personale — Swiss Ephemeris AGPL</div>
    </div>
</body>
</html>
