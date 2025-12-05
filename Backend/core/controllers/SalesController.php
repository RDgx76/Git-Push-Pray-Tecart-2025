<?php
include "../core/session.php";
requireLogin();

include "../models/SalesModel.php";
include "../utils/sanitizer.php";

class SalesController {

    // Method ini dipanggil oleh AJAX sales.js (route: save-transaction.php)
    public static function saveTransaction() {
        // Baca JSON input karena JS mengirim JSON, bukan form data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['items'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Keranjang kosong"]);
            exit;
        }

        $receipt_id = SalesModel::createTransaction($data);

        if ($receipt_id) {
            echo json_encode([
                "success" => true, 
                "receipt_id" => $receipt_id,
                "message" => "Transaksi berhasil"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal menyimpan database"]);
        }
    }
}