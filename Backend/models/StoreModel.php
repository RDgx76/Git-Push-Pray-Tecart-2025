<?php
$db = include __DIR__ . "/../core/database.php";

class StoreModel {
    
    public static function getSettings() {
        global $db;
        return $db->query("SELECT * FROM store_settings LIMIT 1")->fetch();
    }

    public static function updateSettings($data) {
        global $db;
        $stmt = $db->prepare("
            UPDATE store_settings 
            SET store_name = ?, address = ?, phone = ?, theme = ?
        ");

        return $stmt->execute([
            $data["store_name"],
            $data["address"],
            $data["phone"],
            $data["theme"]
        ]);
    }
}
