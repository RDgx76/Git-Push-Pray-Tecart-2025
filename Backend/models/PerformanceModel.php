<?php
$db = include __DIR__ . "/../core/database.php";

class PerformanceModel {

    public static function getCashierPerformance($cashier_id) {
        global $db;

        $stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total_transactions,
                SUM(total_price) AS total_sales
            FROM sales
            WHERE cashier_id = ?
        ");

        $stmt->execute([$cashier_id]);
        return $stmt->fetch();
    }

    public static function getStoreKPI() {
        global $db;

        return $db->query("
            SELECT 
                SUM(total_price) AS total_income,
                COUNT(id) AS total_transactions
            FROM sales
        ")->fetch();
    }
}
