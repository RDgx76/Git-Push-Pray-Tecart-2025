<?php
// db.php - Koneksi ke database MySQL
// VERSION: Fixed for login issue

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "toko_sederhana";

// Buat koneksi
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset UTF-8 MB4 (fix untuk semua karakter)
mysqli_set_charset($conn, "utf8mb4");

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk query dengan keamanan (prepared statement)
function db_query($sql, $params = []) {
    global $conn;
    
    // Jika tidak ada parameter, gunakan query biasa
    if (empty($params)) {
        return mysqli_query($conn, $sql);
    }
    
    // Gunakan prepared statement
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo "Prepared statement error: " . mysqli_error($conn);
        return false;
    }
    
    // Bind parameters
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    // Execute
    if (!mysqli_stmt_execute($stmt)) {
        echo "Execute error: " . mysqli_stmt_error($stmt);
        return false;
    }
    
    // Get result
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

// Fungsi untuk escape output
function escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk debug (bantu troubleshooting)
function db_debug() {
    global $conn;
    echo "<!-- DB DEBUG: ";
    echo "Connected: " . ($conn ? "YES" : "NO") . " | ";
    echo "Host: " . mysqli_get_host_info($conn) . " | ";
    echo "Charset: " . mysqli_character_set_name($conn);
    echo " -->";
}

// Panggil debug (akan muncul di HTML comment)
db_debug();
?>