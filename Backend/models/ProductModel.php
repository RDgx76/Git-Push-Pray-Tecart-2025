<?php
$db = include __DIR__ . "/../core/database.php";

class ProductModel {

    public static function getAll() {
        global $db;
        $query = $db->query("SELECT * FROM products ORDER BY id DESC");
        return $query->fetchAll();
    }

    public static function getById($id) {
        global $db;
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        global $db;
        $stmt = $db->prepare("INSERT INTO products (name, price, stock, category, image) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$data['name'], $data['price'], $data['stock'], $data['category'], $data['image']]);
    }

    public static function update($id, $data) {
        global $db;
        $stmt = $db->prepare("UPDATE products SET name=?, price=?, stock=?, category=? WHERE id=?");
        return $stmt->execute([$data['name'], $data['price'], $data['stock'], $data['category'], $id]);
    }

    public static function delete($id) {
        global $db;
        $stmt = $db->prepare("DELETE FROM products WHERE id=?");
        return $stmt->execute([$id]);
    }
}
