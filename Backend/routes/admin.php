<?php
include "../core/session.php";
requireLogin();

if ($_SESSION["role"] !== "admin") {
    die("Unauthorized access");
}

// Routing halaman admin
$page = $_GET["page"] ?? "dashboard";

switch ($page) {
    case "inventory":
        include "../Frontend/templates/admin/inventory.php";
        break;

    case "staff":
        include "../Frontend/templates/admin/staff.php";
        break;

    case "performance":
        include "../Frontend/templates/admin/performance.php";
        break;

    default:
        include "../Frontend/templates/admin/dashboard.php";
}
