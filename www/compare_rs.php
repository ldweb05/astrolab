<?php
require_once __DIR__ . '/includes/bootstrap.php';

session_start();
require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->richiediLogin();

$isAdmin = $auth->isAdmin();
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comparator RS</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php $paginaAttiva = ''; include 'includes/header_nav.php'; ?>

<main>
<div class="page-title">
<h2>Comparator RS</h2>
</div>

<div id="compare-output">
<p>Caricamento dati...</p>
</div>
</main>

<script>
const out = document.getElementById('compare-output');
const raw = sessionStorage.getItem('astroDssConfrontoRs');

if (!raw) {
    out.innerHTML = '<p><strong>Nessun dato di confronto disponibile.</strong></p>';
} else {
    const pre = document.createElement('pre');
    pre.textContent = JSON.stringify(JSON.parse(raw), null, 2);
    out.replaceChildren(pre);
}
</script>

</body>
</html>
