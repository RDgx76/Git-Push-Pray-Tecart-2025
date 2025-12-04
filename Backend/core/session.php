<?php
$config = include __DIR__ . "/config.php";

session_name($config["session_name"]);
session_start();

// Perpanjang umur session
if (isset($_SESSION['LAST_ACTIVE']) && (time() - $_SESSION['LAST_ACTIVE'] > $config['token_lifetime'])) {
    session_unset();
    session_destroy();
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
