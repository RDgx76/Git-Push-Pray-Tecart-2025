<?php
require_once '../auth.php';
require_once '../db.php';

// Cek role kasir
if ($_SESSION['role'] != 'kasir') {
    redirect_with_message('tambah.php', 'Akses ditolak! Hanya kasir yang bisa membuat transaksi.', 'error');
}

// Cek apakah form sudah submit
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    redirect_with_message('tambah.php', 'Akses tidak valid!', 'error');
}

// Validasi input
if (!isset($_POST['id_produk']) || !is_numeric($_POST['id_produk'])) {
    redirect_with_message('tambah.php', 'ID produk tidak valid!', 'error');
}

if (!isset($_POST['jumlah']) || !is_numeric($_POST['jumlah'])) {
    redirect_with_message('tambah.php', 'Jumlah tidak valid!', 'error');
}

$id_produk = intval($_POST['id_produk']);
$jumlah = intval($_POST['jumlah']);
$id_user = $_SESSION['user_id'];

// Validasi data
if ($jumlah < 1) {
    redirect_with_message('tambah.php', 'Jumlah minimal 1 unit!', 'error');
}

// Mulai transaksi database
mysqli_begin_transaction($conn);

try {
    // 1. Ambil data produk dengan lock untuk menghindari race condition
    $sql_produk = "SELECT * FROM produk WHERE id_produk = ? FOR UPDATE";
    $stmt_produk = mysqli_prepare($conn, $sql_produk);
    mysqli_stmt_bind_param($stmt_produk, "i", $id_produk);
    mysqli_stmt_execute($stmt_produk);
    $result_produk = mysqli_stmt_get_result($stmt_produk);
    $produk = mysqli_fetch_assoc($result_produk);
    
    if (!$produk) {
        throw new Exception('Produk tidak ditemukan!');
    }
    
    // 2. Cek stok cukup
    if ($produk['stok'] < $jumlah) {
        throw new Exception("Stok tidak cukup! Stok tersedia: {$produk['stok']} unit");
    }
    
    // 3. Hitung total harga
    $total_harga = $produk['harga'] * $jumlah;
    
    // 4. Insert ke tabel transaksi
    $sql_transaksi = "INSERT INTO transaksi (total_harga, id_user) VALUES (?, ?)";
    $stmt_transaksi = mysqli_prepare($conn, $sql_transaksi);
    mysqli_stmt_bind_param($stmt_transaksi, "ii", $total_harga, $id_user);
    
    if (!mysqli_stmt_execute($stmt_transaksi)) {
        throw new Exception('Gagal menyimpan transaksi!');
    }
    
    $id_transaksi = mysqli_insert_id($conn);
    
    // 5. Insert ke tabel detail_transaksi
    $sql_detail = "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga_satuan, subtotal) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt_detail = mysqli_prepare($conn, $sql_detail);
    mysqli_stmt_bind_param($stmt_detail, "iiiis", $id_transaksi, $id_produk, $jumlah, $produk['harga'], $total_harga);
    
    if (!mysqli_stmt_execute($stmt_detail)) {
        throw new Exception('Gagal menyimpan detail transaksi!');
    }
    
    // 6. Update stok produk
    $stok_baru = $produk['stok'] - $jumlah;
    $sql_update_stok = "UPDATE produk SET stok = ? WHERE id_produk = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update_stok);
    mysqli_stmt_bind_param($stmt_update, "ii", $stok_baru, $id_produk);
    
    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception('Gagal mengupdate stok produk!');
    }
    
    // Commit transaksi
    mysqli_commit($conn);
    
    // Redirect ke detail transaksi dengan pesan sukses
    $success_message = "Transaksi berhasil! Total: Rp " . number_format($total_harga, 0, ',', '.');
    redirect_with_message("detail.php?id=$id_transaksi", $success_message, 'success');
    
} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($conn);
    redirect_with_message('tambah.php', 'Error: ' . $e->getMessage(), 'error');
}
?>