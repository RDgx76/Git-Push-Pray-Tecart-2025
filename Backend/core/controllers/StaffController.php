<?php
include "../core/session.php";
requireLogin();

include "../models/StaffModel.php";
include "../utils/sanitizer.php";

class StaffController {

    public static function create() {
        StaffModel::create([
            "name" => clean($_POST['name']),
            "phone" => clean($_POST['phone']),
            "role" => clean($_POST['role']),
            "salary" => clean($_POST['salary'])
        ]);

        header("Location: ../routes/admin.php?page=staff");
    }

    public static function delete() {
        $id = $_GET["id"];
        StaffModel::delete($id);

        header("Location: ../routes/admin.php?page=staff");
    }
}
