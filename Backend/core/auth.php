<?php
$db = include __DIR__ . "/database.php";

function login($username, $password) {
    global $db;

    $stmt = $db->prepare("SELECT * FROM pegawai WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) return false;

    $stored = $user['password'] ?? '';

    // 1) If password was stored with password_hash()
    if (password_verify($password, $stored)) {
        // Optional: rehash if algorithm updated
        if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $db->prepare("UPDATE pegawai SET password = ? WHERE id_pegawai = ?");
            $upd->execute([$newHash, $user['id_pegawai']]);
        }
        _login_set_session($user);
        return true;
    }

    // 2) Fallback for old MD5 hashes (legacy)
    if (strlen($stored) === 32 && md5($password) === $stored) {
        // Re-hash password with password_hash and store it (migration)
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $db->prepare("UPDATE pegawai SET password = ? WHERE id_pegawai = ?");
        $upd->execute([$newHash, $user['id_pegawai']]);

        _login_set_session($user);
        return true;
    }

    return false;
}

function _login_set_session($user) {
    // Ensure session started
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    $_SESSION["user_id"] = $user["id_pegawai"] ?? $user["id"] ?? null;
    $_SESSION["role"] = $user["role"] ?? null;
    $_SESSION["nama"] = $user["nama"] ?? $user["name"] ?? null;
    // Regenerate session id upon successful login
    session_regenerate_id(true);
}

function logout() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    session_unset();
    session_destroy();
}
