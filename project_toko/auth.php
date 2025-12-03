<?php
// auth.php - Middleware untuk cek login

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fungsi untuk cek role
function check_role($allowed_roles) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    if (is_array($allowed_roles)) {
        return in_array($_SESSION['role'], $allowed_roles);
    } else {
        return $_SESSION['role'] == $allowed_roles;
    }
}

// Fungsi untuk redirect dengan pesan
function redirect_with_message($url, $message, $type = 'error') {
    $_SESSION['flash_message'] = [
        'text' => $message,
        'type' => $type
    ];
    header("Location: $url");
    exit();
}

// Ambil pesan flash jika ada
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
?>