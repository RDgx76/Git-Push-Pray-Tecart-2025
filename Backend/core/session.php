<?php
// Session hardening: secure cookie flags, SameSite, regeneration
$config = include __DIR__ . "/config.php";

// Determine if secure cookies should be used (use true on HTTPS)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
$httponly = true;
$samesite = 'Lax'; // Lax is good default for most apps

// PHP < 7.3 compatibility: set cookie params carefully
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => $config['token_lifetime'],
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    // Fallback: use header for SameSite for older PHP
    session_set_cookie_params($config['token_lifetime'], "/; samesite={$samesite}", $_SERVER['HTTP_HOST'] ?? '', $secure, $httponly);
}

session_name($config["session_name"]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session id periodically to prevent fixation
if (!isset($_SESSION['CREATED'])) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 600) {
    // regenerate every 10 minutes
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// Session idle timeout handling
if (isset($_SESSION['LAST_ACTIVE']) && (time() - $_SESSION['LAST_ACTIVE'] > $config['token_lifetime'])) {
    session_unset();
    session_destroy();
    // Start fresh session to avoid warnings in other includes
    session_start();
}
$_SESSION['LAST_ACTIVE'] = time();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../Frontend/templates/auth/login.php");
        exit;
    }
}
