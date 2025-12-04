<?php

class UploadController {

    public static function uploadImage($file, $folder) {
        if ($file["error"] !== 0) return null;

        $targetDir = "../Frontend/assets/images/" . $folder . "/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($file["name"]);
        $targetFile = $targetDir . $fileName;

        move_uploaded_file($file["tmp_name"], $targetFile);

        return $fileName;
    }
}
