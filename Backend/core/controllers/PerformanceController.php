<?php
include "../core/session.php";
requireLogin();

include "../models/PerformanceModel.php";
include "../utils/formatter.php";

class PerformanceController {

    public static function viewDetail() {
        $cashier_id = $_GET["id"];

        $performance = PerformanceModel::getCashierPerformance($cashier_id);

        include "../Frontend/templates/admin/performance_detail.php";
    }
}
