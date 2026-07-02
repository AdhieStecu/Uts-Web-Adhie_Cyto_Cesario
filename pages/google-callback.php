<?php
// File: pages/google-callback.php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect(APP_URL . '/pages/dashboard.php');
}

// 1. Verify state to prevent CSRF attacks
$state = $_GET['state'] ?? '';
$savedState = $_SESSION['google_oauth_state'] ?? '';

if (empty($state) || empty($savedState) || $state !== $savedState) {
    unset($_SESSION['google_oauth_state']);
    setFlash('error', 'Autentikasi gagal: State tidak valid (Kemungkinan CSRF).');
    redirect(APP_URL . '/pages/login.php');
}

// Clear state after single use
unset($_SESSION['google_oauth_state']);

// 2. Check if authorization code is returned
$code = $_GET['code'] ?? '';
if (empty($code)) {
    setFlash('error', 'Autentikasi gagal: Kode otorisasi tidak ditemukan.');
    redirect(APP_URL . '/pages/login.php');
}

try {
    // 3. Exchange authorization code for access token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postFields = [
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'grant_type'    => 'authorization_code',
        'code'          => $code
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ensure compatibility in local environment (XAMPP Windows)

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Gagal menghubungi server Google: ' . curl_error($ch));
    }
    curl_close($ch);

    $tokenData = json_decode($response, true);
    if (!isset($tokenData['access_token'])) {
        $errorMsg = $tokenData['error_description'] ?? $tokenData['error'] ?? 'Tidak dapat memperoleh Access Token.';
        throw new Exception('Google Error: ' . $errorMsg);
    }

    $accessToken = $tokenData['access_token'];

    // 4. Retrieve user info using access token
    $userinfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $userinfoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ensure compatibility in local environment

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Gagal mengambil informasi profil pengguna: ' . curl_error($ch));
    }
    curl_close($ch);

    $profile = json_decode($response, true);
    if (!isset($profile['sub']) || !isset($profile['email'])) {
        throw new Exception('Profil Google tidak lengkap.');
    }

    $email = filter_var($profile['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Email Google tidak valid.');
    }

    $google_id = $profile['sub'];
    $name = sanitize($profile['name'] ?? '');
    $picture = filter_var($profile['picture'] ?? '', FILTER_VALIDATE_URL) ? $profile['picture'] : 'default.png';

    // 5. Authenticate user in database
    
    // Check if google_id is already linked
    $user = db()->fetchOne("SELECT * FROM users WHERE google_id = ? LIMIT 1", 's', $google_id);

    if ($user) {
        // User exists, log them in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        setFlash('success', 'Selamat datang kembali, ' . $user['username'] . '! 👋');
        redirect(APP_URL . '/pages/dashboard.php');
    }

    // Check if email already exists (manual registration previously)
    $user = db()->fetchOne("SELECT * FROM users WHERE email = ? LIMIT 1", 's', $email);

    if ($user) {
        // Link google_id to the existing account and log them in
        $updateAvatar = ($user['avatar'] === 'default.png' || empty($user['avatar'])) ? $picture : $user['avatar'];
        db()->execute("UPDATE users SET google_id = ?, avatar = ? WHERE id = ?", 'ssi', $google_id, $updateAvatar, $user['id']);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        setFlash('success', 'Selamat datang kembali! Akun Google Anda telah berhasil ditautkan. 👋');
        redirect(APP_URL . '/pages/dashboard.php');
    }

    // Register a new user
    // Generate unique username from email prefix
    $base_username = strstr($email, '@', true);
    $base_username = preg_replace('/[^a-zA-Z0-9_]/', '', $base_username);
    if (strlen($base_username) < 4) {
        $base_username = 'user_' . $base_username;
    }

    $username = $base_username;
    $counter = 0;
    while (true) {
        $existing = db()->fetchOne("SELECT id FROM users WHERE username = ? LIMIT 1", 's', $username);
        if (!$existing) {
            break;
        }
        $counter++;
        $username = $base_username . $counter . rand(10, 99);
    }

    $random_pass = bin2hex(random_bytes(16));
    $hashed_pass = password_hash($random_pass, PASSWORD_DEFAULT);

    $userId = db()->insert(
        "INSERT INTO users (username, email, password, full_name, avatar, is_verified, google_id) VALUES (?, ?, ?, ?, ?, 1, ?)",
        'ssssss', $username, $email, $hashed_pass, $name, $picture, $google_id
    );

    if ($userId) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'user';
        
        sendNotification($userId, 'Selamat Datang! 🎉', 'Akun kamu berhasil dibuat menggunakan Google SSO. Selamat berbelanja!', 'success');
        setFlash('success', 'Akun berhasil dibuat dengan Google! Selamat belanja di BoloTopup.ID 🎮');
        redirect(APP_URL . '/pages/dashboard.php');
    } else {
        throw new Exception('Gagal membuat user baru di database.');
    }

} catch (Exception $e) {
    setFlash('error', 'Login Google gagal: ' . $e->getMessage());
    redirect(APP_URL . '/pages/login.php');
}
