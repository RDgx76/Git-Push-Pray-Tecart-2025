<?php
include "../core/session.php";
requireLogin();

class AdminController {

    public static function inventory() {
        include "../routes/admin.php?page=inventory";
    }

    public static function staff() {
        include "../routes/admin.php?page=staff";
    }

    public static function performance() {
        include "../routes/admin.php?page=performance";
    }

    public static function dashboard() {
        include "../routes/admin.php?page=dashboard";
    }
}
