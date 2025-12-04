<?php
include "../core/session.php";
requireLogin();

include "../utils/export.php";
include "../models/SalesModel.php";

class ReportController {

    public static function salesReport() {
        $data = SalesModel::getHistory();
        exportCSV("laporan_penjualan", $data);
    }
}
