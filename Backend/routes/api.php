<?php
header("Content-Type: application/json");
include "../core/session.php";

include "../models/ProductModel.php";
include "../models/SalesModel.php";
include "../models/PerformanceModel.php";
include "../models/StaffModel.php";
include "../core/controllers/UploadController.php";

$action = $_GET["action"] ?? "";

// ------------------------------------------------------------
// PRODUCT ENDPOINTS
// ------------------------------------------------------------
switch ($action) {

    case "get_products":
        $keyword = $_GET["q"] ?? "";

        if ($keyword) {
            $products = ProductModel::search($keyword);
        } else {
            $products = ProductModel::getAll();
        }

        echo json_encode($products);
        break;

    case "search_product":
        $keyword = $_GET["q"] ?? "";
        echo json_encode(ProductModel::search($keyword));
        break;

    case "get_product":
        $id = $_GET["id"] ?? 0;
        echo json_encode(ProductModel::getById($id));
        break;

    case "upload_product":

        $image = null;
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
            $image = UploadController::uploadImage($_FILES["image"], "products");
        }

        ProductModel::create([
            "name"           => $_POST["name"],
            "description"    => $_POST["description"],
            "category"       => $_POST["category"],
            "purchase_price" => $_POST["purchase_price"],
            "price"          => $_POST["price"],
            "stock"          => $_POST["stock"],
            "barcode"        => $_POST["barcode"],
            "image"          => $image
        ]);

        echo json_encode(["success" => true]);
        break;

    case "update_product":
        $id = $_POST["id"];

        $image = $_POST["old_image"] ?? null;

        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
            $image = UploadController::uploadImage($_FILES["image"], "products");
        }

        ProductModel::update($id, [
            "name"           => $_POST["name"],
            "description"    => $_POST["description"],
            "category"       => $_POST["category"],
            "purchase_price" => $_POST["purchase_price"],
            "price"          => $_POST["price"],
            "stock"          => $_POST["stock"],
            "barcode"        => $_POST["barcode"],
            "image"          => $image
        ]);

        echo json_encode(["success" => true]);
        break;

    case "delete_product":
        ProductModel::delete($_POST["id"]);
        echo json_encode(["success" => true]);
        break;

    // ------------------------------------------------------------
    // SALES
    // ------------------------------------------------------------
    case "get_sales_today":
        echo json_encode(SalesModel::getDailySales());
        break;

    // ------------------------------------------------------------
    // STAFF
    // ------------------------------------------------------------
    case "get_staff":
        echo json_encode(StaffModel::getAll());
        break;

    // ------------------------------------------------------------
    // PERFORMANCE
    // ------------------------------------------------------------
    case "get_performance":
        $from = $_GET["from"] ?? date("Y-m-01");
        $to   = $_GET["to"] ?? date("Y-m-d");
        echo json_encode(PerformanceModel::getPerformanceByDate($from, $to));
        break;

    default:
        echo json_encode(["error" => "Unknown API action: $action"]);
        break;
}
