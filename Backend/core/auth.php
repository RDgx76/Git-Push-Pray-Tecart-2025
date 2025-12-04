<?php
$db = include __DIR__ . "/database.php";

function login($username, $password) {
    global $db;

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) return false;

    if (password_verify($password, $user['password'])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["role"] = $user["role"];
        return true;
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
}
