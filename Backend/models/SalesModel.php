<?php
$db = include __DIR__ . "/../core/database.php";

class SalesModel {

    public static function createSale($data) {
        global $db;
        $stmt = $db->prepare("INSERT INTO sales (product_id, qty, total_price, cashier_id, date) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([
            $data['product_id'], 
            $data['qty'], 
            $data['total_price'], 
            $data['cashier_id']
        ]);
    }

    public static function getDailySales() {
        global $db;
        $stmt = $db->query("SELECT * FROM sales WHERE DATE(date) = CURDATE()");
        return $stmt->fetchAll();
    }

    public static function getHistory() {
        global $db;
        $stmt = $db->query("SELECT * FROM sales ORDER BY id DESC");
        return $stmt->fetchAll();
    }
}
