<?php
include "../core/session.php";
requireLogin();

include "../models/ProductModel.php";
include "../core/controllers/UploadController.php";

class ProductController {

    public static function update() {

        $id = $_POST["id"];

        $image = $_POST["old_image"] ?? null;

        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
            $image = UploadController::uploadImage($_FILES["image"], "products");
        }

        ProductModel::update($id, [
            "name"           => $_POST["name"],
            "price"          => $_POST["price"],
            "purchase_price" => $_POST["purchase_price"],
            "stock"          => $_POST["stock"],
            "category"       => $_POST["category"],
            "description"    => $_POST["description"],
            "barcode"        => $_POST["barcode"],
            "image"          => $image
        ]);

        header("Location: ../index.php?controller=admin&action=inventory");
    }
}
