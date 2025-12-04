<?php
$db = include __DIR__ . "/../core/database.php";

class StaffModel {

    public static function getAll() {
        global $db;
        return $db->query("SELECT * FROM staff ORDER BY id DESC")->fetchAll();
    }

    public static function create($data) {
        global $db;

        $stmt = $db->prepare("
            INSERT INTO staff (name, phone, role, salary) 
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data["name"], 
            $data["phone"], 
            $data["role"], 
            $data["salary"]
        ]);
    }

    public static function delete($id) {
        global $db;
        $stmt = $db->prepare("DELETE FROM staff WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
