<?php

class UploadController {

    // max file size (bytes) — 4MB default
    const MAX_FILE_SIZE = 4 * 1024 * 1024;

    public static function uploadImage($file, $folder = "products") {
        // Basic error checks
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Size check
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return null;
        }

        // Use realpath base and ensure folder exists
        $baseDir = realpath(__DIR__ . "/../../../Frontend/assets/images");
        if ($baseDir === false) {
            // fallback to relative path
            $baseDir = __DIR__ . "/../../Frontend/assets/images";
        }
        $targetDir = $baseDir . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        // Validate MIME type using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!array_key_exists($mime, $allowedMimes)) {
            return null;
        }

        $ext = $allowedMimes[$mime];
        $filename = bin2hex(random_bytes(12)) . '.' . $ext;

        $targetPath = $targetDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return null;
        }

        // Optional: set file permissions
        @chmod($targetPath, 0644);

        // Return relative path used by frontend, e.g. "products/abcdef.jpg"
        return $folder . '/' . $filename;
    }
}
