<?php
$db = include __DIR__ . "/../core/database.php";

class ProductModel {

    public static function getAll() {
        global $db;
        return $db->query("
            SELECT
                id_produk AS id,
                nama_produk AS name,
                harga_jual AS price,
                harga_beli AS purchase_price,
                stok AS stock,
                kategori AS category,
                barcode,
                gambar AS image,
                deskripsi AS description
            FROM produk
            ORDER BY id_produk DESC
        ")->fetchAll();
    }

    public static function getById($id) {
        global $db;
        $stmt = $db->prepare("
            SELECT
                id_produk AS id,
                nama_produk AS name,
                harga_jual AS price,
                harga_beli AS purchase_price,
                stok AS stock,
                kategori AS category,
                barcode,
                gambar AS image,
                deskripsi AS description
            FROM produk
            WHERE id_produk = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function search($keyword) {
        global $db;
        $term = "%$keyword%";
        $stmt = $db->prepare("
            SELECT
                id_produk AS id,
                nama_produk AS name,
                harga_jual AS price,
                stok AS stock,
                kategori AS category,
                barcode,
                gambar AS image
            FROM produk
            WHERE nama_produk LIKE ? OR barcode LIKE ?
        ");
        $stmt->execute([$term, $term]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        global $db;

        $stmt = $db->prepare("
            INSERT INTO produk
            (nama_produk, deskripsi, kategori, harga_beli, harga_jual, stok, barcode, gambar)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data["name"],
            $data["description"],
            $data["category"],
            $data["purchase_price"],
            $data["price"],
            $data["stock"],
            $data["barcode"],
            $data["image"]
        ]);
    }

    public static function update($id, $data) {
        global $db;

        $stmt = $db->prepare("
            UPDATE produk SET
                nama_produk = ?,
                harga_jual = ?,
                harga_beli = ?,
                stok = ?,
                kategori = ?,
                deskripsi = ?,
                barcode = ?,
                gambar = ?
            WHERE id_produk = ?
        ");

        return $stmt->execute([
            $data["name"],
            $data["price"],
            $data["purchase_price"],
            $data["stock"],
            $data["category"],
            $data["description"],
            $data["barcode"],
            $data["image"],
            $id
        ]);
    }

    public static function delete($id) {
        global $db;
        $stmt = $db->prepare("DELETE FROM produk WHERE id_produk=?");
        return $stmt->execute([$id]);
    }
}
