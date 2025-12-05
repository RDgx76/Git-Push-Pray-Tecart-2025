<?php
// Load konfigurasi
$config = include __DIR__ . "/config.php";

$db_host = $config['db_host'];
$db_name = $config['db_name'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];

try {
    // String Koneksi DSN
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    
    // Opsi PDO:
    $options = [
        // Mode Error: PDO akan melempar EXCEPTION saat terjadi error. Sangat penting untuk debugging.
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Mode Fetch: Hasil query dikembalikan sebagai array asosiatif.
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Nonaktifkan emulasi prepared statement (keamanan dan performa lebih baik)
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Koneksi
    $db = new PDO($dsn, $db_user, $db_pass, $options);
    
    // Kembalikan objek koneksi PDO
    return $db;

} catch (PDOException $e) {
    // Jika koneksi gagal, hentikan eksekusi dan tampilkan pesan error yang jelas (hanya di mode development)
    if ($config['env'] === 'development') {
        die("Koneksi Database Gagal: " . $e->getMessage());
    } else {
        // Di mode production, hanya tampilkan pesan generik
        die("Koneksi Database Gagal. Silakan hubungi administrator.");
    }
}