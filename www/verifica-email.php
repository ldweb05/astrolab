<?php
declare(strict_types=1);

require_once __DIR__ . "/includes/bootstrap.php";
require_once __DIR__ . "/includes/Auth.php";

session_start();

$pdo = db_connect();
$auth = new Auth($pdo);

$token = trim((string)($_GET["token"] ?? ""));
$result = $token !== ""
    ? $auth->verificaEmailToken($token)
    : ["ok" => false, "errore" => "Token di verifica mancante."];

$ok = ($result["ok"] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verifica email — AstroLab</title>
<link rel="stylesheet" href="css/style.css">
<style>
body{
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:100vh;
    background:#F2EDE4;
}
.box{
    width:100%;
    max-width:440px;
    padding:40px 48px;
    background:#fff;
    border-radius:10px;
    box-shadow:0 4px 24px rgba(44,62,107,.15);
}
h1{
    color:#2C3E6B;
    font-size:22px;
    font-weight:normal;
    text-align:center;
}
.message{
    margin:24px 0;
    padding:12px 16px;
    border-left:3px solid;
    border-radius:4px;
}
.ok{
    background:#E8F5E9;
    border-color:#388E3C;
    color:#1B5E20;
}
.err{
    background:#FFEBEE;
    border-color:#F44336;
    color:#B71C1C;
}
.footer{
    margin-top:24px;
    text-align:center;
}
</style>
</head>
<body>
<main class="box">
<h1>☉ AstroLab</h1>

<?php if ($ok): ?>
<div class="message ok">
La tua email è stata verificata con successo.<br>
Il tuo account è ora attivo.
</div>
<?php else: ?>
<div class="message err">
<?= htmlspecialchars((string)($result["errore"] ?? "Verifica non disponibile."), ENT_QUOTES, "UTF-8") ?>
</div>
<?php endif; ?>

<div class="footer">
<a href="login.php">Accedi al tuo account</a>
</div>

</main>
</body>
</html>
