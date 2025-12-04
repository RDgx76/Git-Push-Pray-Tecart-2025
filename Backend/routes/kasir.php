<?php
include "../core/session.php";
requireLogin();

if ($_SESSION["role"] !== "kasir") {
    die("Unauthorized access");
}

$page = $_GET["page"] ?? "sales";

switch ($page) {
    case "history":
        include "../Frontend/templates/kasir/personal_history.php";
        break;

    default:
        include "../Frontend/templates/kasir/sales.php";
}
