<?php
header("Content-Type: application/json");

include "../core/session.php";
include "../models/ProductModel.php";
include "../models/SalesModel.php";

$action = $_GET["action"] ?? "";

switch ($action) {

    case "get_products":
        echo json_encode(ProductModel::getAll());
        break;

    case "get_sales_today":
        echo json_encode(SalesModel::getDailySales());
        break;

    default:
        echo json_encode(["error" => "Unknown API action"]);
}
