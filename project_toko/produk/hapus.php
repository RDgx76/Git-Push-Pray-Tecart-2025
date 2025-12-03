<?php
require_once '../auth.php';
require_once '../db.php';

// Cek role admin
if ($_SESSION['role'] != 'admin') {
    redirect_with_message('list.php', 'Akses ditolak! Hanya admin yang bisa menghapus produk.', 'error');
}

// Cek parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect_with_message('list.php', 'ID produk tidak valid!', 'error');
}

$id = intval($_GET['id']);

// Cek apakah produk ada
$sql = "SELECT nama_produk FROM produk WHERE id_produk = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    redirect_with_message('list.php', 'Produk tidak ditemukan!', 'error');
}

// Cek apakah produk digunakan dalam transaksi
$sql_check = "SELECT COUNT(*) as total FROM detail_transaksi WHERE id_produk = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$check = mysqli_fetch_assoc($result_check);

// Jika produk digunakan dalam transaksi, jangan hapus
if ($check['total'] > 0) {
    redirect_with_message('list.php', 'Produk tidak dapat dihapus karena sudah digunakan dalam transaksi!', 'error');
}

// Hapus produk
$sql_delete = "DELETE FROM produk WHERE id_produk = ?";
$stmt_delete = mysqli_prepare($conn, $sql_delete);
mysqli_stmt_bind_param($stmt_delete, "i", $id);

if (mysqli_stmt_execute($stmt_delete)) {
    $message = "Produk '" . $produk['nama_produk'] . "' berhasil dihapus!";
    redirect_with_message('list.php', $message, 'success');
} else {
    redirect_with_message('list.php', 'Gagal menghapus produk: ' . mysqli_error($conn), 'error');
}
?>