<?php
// CSRF helper: generate, verify, and output hidden input
// Usage:
//  - in forms: echo csrf_input();
//  - in processing endpoints: if (!verify_csrf($_POST['_csrf'] ?? '')) { // abort }

function generate_csrf_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token_time'] = time();
    }
    return $_SESSION['_csrf_token'];
}

function csrf_input() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf($token, $max_age = 3600) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($token) || empty($_SESSION['_csrf_token'])) {
        return false;
    }
    $valid = hash_equals($_SESSION['_csrf_token'], $token);
    if (!$valid) return false;
    // Optional: expire token after $max_age seconds
    if (!empty($_SESSION['_csrf_token_time']) && (time() - $_SESSION['_csrf_token_time']) > $max_age) {
        // token expired
        unset($_SESSION['_csrf_token']);
        unset($_SESSION['_csrf_token_time']);
        return false;
    }
    return true;
}
