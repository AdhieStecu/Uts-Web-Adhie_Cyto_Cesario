<?php
// File: pages/google-login.php
require_once __DIR__ . '/../inc/functions.php';

if (isLoggedIn()) {
    redirect(APP_URL . '/pages/dashboard.php');
}

if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
    setFlash('error', 'Google SSO belum dikonfigurasi dengan benar.');
    redirect(APP_URL . '/pages/login.php');
}

// Generate secure state token to prevent CSRF attacks
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

// Build Google OAuth authorization URL
$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'response_type' => 'code',
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'scope'         => 'openid email profile',
    'state'         => $state,
    'prompt'        => 'select_account'
]);

redirect($authUrl);
