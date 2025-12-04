<?php
include "../core/session.php";
requireLogin();

include "../models/StoreModel.php";
include "../utils/sanitizer.php";

class StoreController {

    public static function update() {
        StoreModel::updateSettings([
            "store_name" => clean($_POST['store_name']),
            "address"    => clean($_POST['address']),
            "phone"      => clean($_POST['phone']),
            "theme"      => clean($_POST['theme'])
        ]);

        header("Location: ../routes/admin.php?page=settings");
    }
}
