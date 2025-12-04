<?php
/*
|--------------------------------------------------------------------------
| ByteMart Backend Main Router
|--------------------------------------------------------------------------
| Semua request dari Frontend (form POST, tombol aksi, AJAX)
| diarahkan ke file ini lalu diteruskan ke Controller terkait.
|
| Keamanan:
| - Tidak boleh akses controller secara langsung
| - Hanya index.php yang menjadi pintu masuk
| - Semua input disanitasi di Controller
|--------------------------------------------------------------------------
*/

// Ambil parameter aksi
$controller = $_GET["controller"] ?? null;
$action     = $_GET["action"] ?? null;

// Jika tidak ada controller → tolak akses
if (!$controller || !$action) {
    http_response_code(400);
    echo "Bad request: missing controller or action.";
    exit;
}

// Valid daftar controller agar tidak arbitrary file access
$allowedControllers = [
    "auth",
    "product",
    "sales",
    "staff",
    "performance",
    "store",
    "report",
    "upload"
];

// Cek apakah controller valid
if (!in_array($controller, $allowedControllers)) {
    http_response_code(403);
    echo "Forbidden: Invalid controller.";
    exit;
}

// Tentukan file controller
$controllerFile = __DIR__ . "/controllers/" . ucfirst($controller) . "Controller.php";

// Jika file controller tidak ditemukan
if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo "Controller file not found.";
    exit;
}

// Load controller
include $controllerFile;

// Tentukan nama class Controller
$controllerClass = ucfirst($controller) . "Controller";

// Cek apakah class ada
if (!class_exists($controllerClass)) {
    http_response_code(500);
    echo "Controller class not found.";
    exit;
}

// Cek apakah method aksi ada
if (!method_exists($controllerClass, $action)) {
    http_response_code(500);
    echo "Action method not found.";
    exit;
}

// Jalankan aksi
$controllerClass::$action();
