<?php
declare(strict_types=1);

require_once __DIR__ . "/includes/bootstrap.php";
require_once __DIR__ . "/includes/Auth.php";

session_start();

if (!empty($_SESSION["utente_id"])) {
    header("Location: index.php");
    exit;
}

$pdo = db_connect();
$auth = new Auth($pdo);

$errore = "";
$successo = false;

if (empty($_SESSION["registration_csrf"])) {
    $_SESSION["registration_csrf"] = bin2hex(random_bytes(32));
}

function registrationClientIp(): string
{
    return substr((string)($_SERVER["REMOTE_ADDR"] ?? "unknown"), 0, 64);
}

function registrationRateLimit(string $ip): bool
{
    $windowSeconds = 3600;
    $maxAttempts = 5;
    $path = sys_get_temp_dir() . "/astrolab-registration-" . hash("sha256", $ip) . ".json";
    $handle = fopen($path, "c+");

    if ($handle === false) {
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }

        $raw = stream_get_contents($handle);
        $data = is_string($raw) && $raw !== ""
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim((string)($_POST["username"] ?? ""));
    $email = trim((string)($_POST["email"] ?? ""));
    $password = (string)($_POST["password"] ?? "");
    $passwordConfirm = (string)($_POST["password_confirm"] ?? "");
    $csrf = (string)($_POST["csrf_token"] ?? "");

    if (!hash_equals((string)$_SESSION["registration_csrf"], $csrf)) {
        $errore = "Sessione non valida. Ricarica la pagina e riprova.";
    } elseif (!registrationRateLimit(registrationClientIp())) {
        $errore = "Troppi tentativi di registrazione. Riprova più tardi.";
    } elseif ($password !== $passwordConfirm) {
        $errore = "Le password non coincidono.";
    } else {
        $result = $auth->registraUtentePubblico($username, $email, $password);

        if ($result["ok"] === true) {
            $successo = true;
            $_SESSION["registration_csrf"] = bin2hex(random_bytes(32));
        } else {
            $errore = (string)($result["errore"] ?? "Registrazione non disponibile.");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione — AstroLab</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #F2EDE4;
        }
        .registration-box {
            width: 100%;
            max-width: 440px;
            padding: 40px 48px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(44, 62, 107, 0.15);
        }
        .registration-logo {
            margin-bottom: 28px;
            text-align: center;
        }
        .registration-logo h1 {
            color: #2C3E6B;
            font-size: 22px;
            font-weight: normal;
            letter-spacing: 0.08em;
        }
        .registration-logo p {
            margin-top: 4px;
            color: #999;
            font-size: 12px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 11px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #D0C8BC;
            border-radius: 5px;
            font: inherit;
        }
        .form-group input:focus {
            border-color: #2C3E6B;
            outline: none;
        }
        .btn-registration {
            width: 100%;
            margin-top: 8px;
            padding: 11px;
            border: 0;
            border-radius: 5px;
            background: #2C3E6B;
            color: white;
            cursor: pointer;
            font: inherit;
        }
        .message {
            margin-bottom: 18px;
            padding: 10px 14px;
            border-left: 3px solid;
            border-radius: 4px;
            font-size: 13px;
        }
        .message-error {
            border-color: #F44336;
            background: #FFEBEE;
            color: #B71C1C;
        }
        .message-success {
            border-color: #388E3C;
            background: #E8F5E9;
            color: #1B5E20;
        }
        .login-link {
            margin-top: 22px;
            text-align: center;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <main class="registration-box">
        <div class="registration-logo">
            <h1>☉ AstroLab</h1>
            <p>Crea il tuo account gratuito</p>
        </div>

        <?php if ($errore !== ""): ?>
            <div class="message message-error"><?= htmlspecialchars($errore, ENT_QUOTES, "UTF-8") ?></div>
        <?php endif; ?>

        <?php if ($successo): ?>
            <div class="message message-success">
                Registrazione completata. Il tuo account è già attivo: puoi accedere subito.
            </div>
        <?php else: ?>
            <form method="POST" action="registrazione.php">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION["registration_csrf"], ENT_QUOTES, "UTF-8") ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username"
                           minlength="3" maxlength="60"
                           pattern="[A-Za-z0-9._-]+"
                           autocomplete="username"
                           value="<?= htmlspecialchars($_POST["username"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email"
                           maxlength="200" autocomplete="email"
                           value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password"
                           minlength="8" autocomplete="new-password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Conferma password</label>
                    <input id="password_confirm" type="password" name="password_confirm"
                           minlength="8" autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn-registration">Registrati →</button>
            </form>
        <?php endif; ?>

        <div class="login-link">
            Hai già un account? <a href="login.php">Accedi</a>
        </div>
    </main>
</body>
</html>
