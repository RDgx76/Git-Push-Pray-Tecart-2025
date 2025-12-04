<?php
include "../core/session.php";
requireLogin();

include "../models/SalesModel.php";
include "../utils/sanitizer.php";

class SalesController {

    public static function addItem() {
        $data = [
            "product_id"  => clean($_POST["product_id"]),
            "qty"         => clean($_POST["qty"]),
            "total_price" => clean($_POST["total_price"]),
            "cashier_id"  => $_SESSION["user_id"]
        ];

        SalesModel::createSale($data);

        header("Location: ../routes/kasir.php?page=sales");
    }

    public static function cancelCart() {
        // (cart berbasis session)
        unset($_SESSION['cart']);
        header("Location: ../routes/kasir.php?page=sales");
    }
}
