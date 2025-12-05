<?php
$db = include __DIR__ . "/../core/database.php";

class SalesModel {

    public static function createTransaction($data) {
        global $db;

        $stmt = $db->prepare("
            INSERT INTO penjualan
            (total, diskon, pajak, metode_pembayaran, uang_diterima, kembalian, id_pegawai)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data["total"],
            $data["discount"],
            $data["tax"],          // ← ditambahkan
            $data["payment_method"],
            $data["received"],
            $data["change"],
            $data["staff_id"]
        ]);
    }

    public static function getDailySales() {
        global $db;

        return $db->query("
            SELECT SUM(total) AS total_sales,
                   SUM(diskon) AS total_discount,
                   SUM(pajak) AS total_tax,
                   COUNT(*) AS count
            FROM penjualan
            WHERE DATE(dibuat_pada) = CURDATE()
        ")->fetch();
    }
}
