<?php
$db = include __DIR__ . "/../core/database.php";

class StaffModel {

    public static function getAll() {
        global $db;

        $query = $db->query("
            SELECT
                id_pegawai AS id,
                nama AS name,
                username,
                role,
                CASE WHEN status = 'aktif' THEN 1 ELSE 0 END AS active
            FROM pegawai
        ");

        return $query->fetchAll();
    }
}
