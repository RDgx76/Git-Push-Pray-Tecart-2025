<?php
$db = include __DIR__ . "/../core/database.php";

class PerformanceModel {

    public static function getCashierPerformance($cashier_id) {
        global $db;

        $stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total_transactions,
                SUM(total) AS total_sales
            FROM penjualan
            WHERE id_pegawai = ?
        ");
        $stmt->execute([$cashier_id]);
        return $stmt->fetch();
    }

    public static function getStoreKPI() {
        global $db;
        return $db->query("
            SELECT 
                SUM(total) AS total_income,
                COUNT(id_penjualan) AS total_transactions
            FROM penjualan
        ")->fetch();
    }

    // 👇 METHOD BARU (fix fatal error)
    public static function getPerformanceByDate($from, $to) {
        global $db;

        $stmt = $db->prepare("
            SELECT 
                pg.nama AS staff_name,
                COUNT(p.id_penjualan) AS transactions,
                SUM(p.total) AS total
            FROM penjualan p
            LEFT JOIN pegawai pg ON p.id_pegawai = pg.id_pegawai
            WHERE DATE(p.dibuat_pada) BETWEEN ? AND ?
            GROUP BY p.id_pegawai
        ");

        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
    }
}
