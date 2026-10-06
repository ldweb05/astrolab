<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * login.php — Pagina di login
 * Astrologia Attiva 
 */
session_start();

// Se già loggato, vai alla pagina di destinazione predefinita per il ruolo
if (!empty($_SESSION['utente_id'])) {
    $paginaDefault = ($_SESSION['utente_ruolo'] ?? '') === 'admin'
        ? 'admin_utenti.php'
        : 'index.php';
    header('Location: ' . $paginaDefault);
    exit;
}

// Soggetto gia' loggato con il proprio CODICE: va alla sua area riservata.
if (!empty($_SESSION['accesso_soggetto']['soggetto_id'])) {
    header('Location: area_soggetto.php');
    exit;
}

require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);

$errore = '';
$next   = $_GET['next'] ?? '';

require_once __DIR__ . '/includes/client_ip.php';

function loginClientIp(): string
{
    return astrolab_client_ip();
}

// Limiti di login: solo tentativi falliti, nel DB (Auth::ipBloccatoLogin(),
// Auth::loginAstrologo(); correzione del PUNTO APERTO in docs/roadmaps/ROADMAP.md).

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errore = 'Inserisci username e password.';
    } elseif ($auth->ipBloccatoLogin(loginClientIp())) {
        $errore = 'Troppi tentativi di accesso. Riprova più tardi.';
        // Registro accessi: solo per gli astrologi, non per i CODICE dei soggetti
        if (!$auth->isFormaCodiceSoggetto($username)) {
            $auth->registraAccessoAstrologo($username, 'bloccato', 'ip_bloccato', loginClientIp());
        }
    } elseif ($auth->isFormaCodiceSoggetto($username)) {
        // Login del soggetto con il proprio CODICE (ROADMAP_CODICE_LOGIN_SOGGETTI.md,
        // blocco B). Il parametro next viene ignorato: il soggetto va solo alla
        // propria area.
        $result = $auth->loginSoggetto($username, $password, loginClientIp());
        if ($result['ok']) {
            header('Location: ' . ($result['deve_cambiare_password']
                ? 'cambio_password_soggetto.php'
                : 'area_soggetto.php'));
            exit;
        }
        if (($result['errore'] ?? '') === 'Credenziali non valide.') {
            $auth->registraTentativoFallitoIp(loginClientIp());
        }
        $errore = $result['errore'];
    } else {
        $result = $auth->loginAstrologo($username, $password, loginClientIp());
        if ($result['ok']) {
            // Sicurezza: next deve essere una path relativa, non un URL esterno
            $next = preg_replace('#[^a-zA-Z0-9/_\-\.\?=&]#', '', $next);
            if (empty($next) || str_starts_with($next, '//') || str_contains($next, ':')) {
                // Nessuna destinazione esplicita: pagina predefinita in base al ruolo
                $next = ($result['ruolo'] ?? '') === 'admin'
                    ? 'admin_utenti.php'
                    : 'index.php';
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
    <title>AstroLab — la tua esperienza in Astrologia Attiva</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Eb+Garamond:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            /* background: #F2EDE4; */
            background-color: #FFFFFF;
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
            font-family: 'Eb Garamond', Georgia, serif;
            font-size: 48px;
            line-height: 56px;
            color: #2C3E6B;
            font-weight: 700;
            letter-spacing: -0.02em;
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
        .password-field-wrap {
            position: relative;
        }
        .password-field-wrap input {
            padding-right: 40px !important;
        }
        .password-toggle-btn {
            position: absolute;
            top: 50%;
            right: 6px;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            padding: 6px;
            color: #6b5c4f;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .password-toggle-btn:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <h1>AstroLab</h1>
            <p>Rivoluzioni Solari Mirate — Astrologia Attiva</p>
        </div>

        <?php if ($errore): ?>
        <div class="errore-login">⚠️ <?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php?next=<?= urlencode($next) ?>">
            <div class="form-group">
                <label>Username o codice soggetto</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autocomplete="username" autofocus required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="password-field-wrap">
                    <input type="password" name="password" id="login-password" autocomplete="current-password" required>
                    <button type="button" id="login-toggle-password" class="password-toggle-btn" tabindex="-1" aria-label="Mostra password" title="Mostra password">👁️</button>
                </div>
            </div>
            <button type="submit" class="btn-login">Accedi →</button>
        </form>

        <div class="login-link" style="text-align:center;font-size:13px;margin-top:22px">
            Non hai un account? <a href="registrazione.php">Registrati</a>
        </div>

        <div class="version-note">Uso personale — Swiss Ephemeris AGPL</div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('login-toggle-password');
    const input = document.getElementById('login-password');
    if (!btn || !input) return;

    btn.addEventListener('click', function () {
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.textContent = isHidden ? '🙈' : '👁️';
        btn.setAttribute('aria-label', isHidden ? 'Nascondi password' : 'Mostra password');
        btn.setAttribute('title', isHidden ? 'Nascondi password' : 'Mostra password');
    });
});
</script>
</body>
</html>
