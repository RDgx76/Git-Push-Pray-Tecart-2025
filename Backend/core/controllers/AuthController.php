<?php
include "../core/session.php";
include "../core/auth.php";
include "../utils/sanitizer.php";

class AuthController {

    public static function login() {
        $username = clean($_POST['username']);
        $password = clean($_POST['password']);

        if (login($username, $password)) {
            if ($_SESSION["role"] === "admin") {
                header("Location: ../routes/admin.php");
            } else {
                header("Location: ../routes/kasir.php");
            }
            exit;
        }

        header("Location: ../Frontend/templates/auth/login.php?error=1");
        exit;
    }

    public static function logout() {
        logout();
        header("Location: ../Frontend/templates/auth/login.php");
        exit;
    }
}
