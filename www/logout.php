<?php
require_once __DIR__ . '/includes/bootstrap.php';
/**
 * logout.php — Distrugge la sessione e reindirizza al login
 */
require_once 'includes/Auth.php';

$pdo = db_connect();
$auth = new Auth($pdo);
$auth->logout();

header('Location: login.php?logout=1');
exit;
