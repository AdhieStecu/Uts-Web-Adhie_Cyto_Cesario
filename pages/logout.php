<?php
// File: pages/logout.php
require_once __DIR__ . '/../inc/functions.php';

// Hapus semua session
$_SESSION = [];
session_destroy();

// Redirect ke login
setcookie(session_name(), '', time() - 3600, '/');
header('Location: ' . APP_URL . '/pages/login.php');
exit;