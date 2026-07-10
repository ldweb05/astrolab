<?php
/**
 * auth_header.php — Include all'inizio di ogni pagina protetta
 *
 * Uso:
 *   session_start();
 *   require_once 'includes/Auth.php';
 *   require_once 'includes/auth_header.php';
 *
 * Mette a disposizione:
 *   $auth          → oggetto Auth
 *   $isAdmin       → bool
 *   $username      → string
 *   $soggettoAttivo → int|null
 *   $soggettoNome  → string
 */

// La connessione PDO deve già essere disponibile come $pdo

$auth         = new Auth($pdo);
$auth->richiediLogin();

$isAdmin       = $auth->isAdmin();
$username      = $auth->getCurrentUsername();
$soggettoAttivo = $auth->getSoggettoAttivo();
$soggettoNome  = $auth->getSoggettoNome();