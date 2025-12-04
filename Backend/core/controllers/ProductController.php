<?php
include "../core/session.php";
requireLogin();

include "../models/ProductModel.php";
include "../utils/sanitizer.php";
include "../utils/validator.php";
include "./UploadController.php";

class ProductController {

    public static function create() {
        if (!required($_POST['name']) || !isNumber($_POST['price'])) {
            die("Invalid input");
        }

        $image = UploadController::uploadImage($_FILES['image'], "products");

        ProductModel::create([
            "name" => clean($_POST['name']),
            "price" => clean($_POST['price']),
            "stock" => clean($_POST['stock']),
            "category" => clean($_POST['category']),
            "image" => $image
        ]);

        header("Location: ../routes/admin.php?page=inventory");
    }

    public static function update() {
        $id = $_POST['id'];

        ProductModel::update($id, [
            "name" => clean($_POST['name']),
            "price" => clean($_POST['price']),
            "stock" => clean($_POST['stock']),
            "category" => clean($_POST['category'])
        ]);

        header("Location: ../routes/admin.php?page=inventory");
    }

    public static function delete() {
        $id = $_GET['id'];
        ProductModel::delete($id);

        header("Location: ../routes/admin.php?page=inventory");
    }
}
