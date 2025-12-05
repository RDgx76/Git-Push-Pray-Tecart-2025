<?php
/*
|-----------------------------------------------------------------------
| ByteMart Backend Main Router (FIXED)
|-----------------------------------------------------------------------
*/

$controller = $_GET["controller"] ?? null;
$action     = $_GET["action"] ?? null;

if (!$controller || !$action) {
    http_response_code(400);
    echo "Bad request: missing controller or action.";
    exit;
}

$allowedControllers = [
    "auth",
    "product",
    "sales",
    "staff",
    "performance",
    "store",
    "report",
    "upload",
    "admin" // ← DITAMBAHKAN
];

if (!in_array($controller, $allowedControllers)) {
    http_response_code(403);
    echo "Forbidden: Invalid controller.";
    exit;
}

$controllerFile = __DIR__ . "/core/controllers/" . ucfirst($controller) . "Controller.php";

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo "Controller file not found.";
    exit;
}

include $controllerFile;

$controllerClass = ucfirst($controller) . "Controller";

if (!class_exists($controllerClass)) {
    http_response_code(500);
    echo "Controller class not found.";
    exit;
}

if (!method_exists($controllerClass, $action)) {
    http_response_code(500);
    echo "Action method not found.";
    exit;
}

$controllerClass::$action();
