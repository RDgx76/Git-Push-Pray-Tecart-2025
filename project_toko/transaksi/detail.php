<?php
require_once '../auth.php';
require_once '../db.php';

// Cek parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect_with_message('list.php', 'ID transaksi tidak valid!', 'error');
}

$id_transaksi = intval($_GET['id']);

// Ambil data transaksi
$sql = "SELECT t.*, u.username as nama_kasir 
        FROM transaksi t
        LEFT JOIN user u ON t.id_user = u.id_user
        WHERE t.id_transaksi = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_transaksi);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result);

if (!$transaksi) {
    redirect_with_message('list.php', 'Transaksi tidak ditemukan!', 'error');
}

// Cek hak akses (admin bisa lihat semua, kasir hanya transaksi miliknya)
if ($_SESSION['role'] == 'kasir' && $transaksi['id_user'] != $_SESSION['user_id']) {
    redirect_with_message('list.php', 'Anda tidak memiliki akses untuk melihat transaksi ini!', 'error');
}

// Ambil detail transaksi
$sql_detail = "SELECT dt.*, p.nama_produk, dt.harga_satuan 
               FROM detail_transaksi dt
               JOIN produk p ON dt.id_produk = p.id_produk
               WHERE dt.id_transaksi = ?";
$stmt_detail = mysqli_prepare($conn, $sql_detail);
mysqli_stmt_bind_param($stmt_detail, "i", $id_transaksi);
mysqli_stmt_execute($stmt_detail);
$detail_result = mysqli_stmt_get_result($stmt_detail);

// Ambil pesan flash jika ada
$flash_msg = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - Sistem Toko</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .page-title {
            font-size: 28px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .page-subtitle {
            opacity: 0.9;
            font-size: 16px;
        }
        
        /* Back Button */
        .back-button {
            margin-bottom: 20px;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #5a6268;
            transform: translateX(-5px);
        }
        
        /* Flash Message */
        .flash-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #28a745;
            animation: slideIn 0.5s ease;
        }
        
        .flash-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Transaction Info */
        .transaction-info {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #667eea;
        }
        
        .info-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .info-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        /* Total Card */
        .total-card {
            background: linear-gradient(to right, #28a745, #20c997);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
            text-align: center;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
        }
        
        .total-label {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .total-value {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .total-words {
            font-size: 16px;
            opacity: 0.9;
        }
        
        /* Products Table */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        thead {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
        }
        
        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        
        tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.3s;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        td {
            padding: 16px 15px;
            color: #555;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .product-name {
            font-weight: 500;
            color: #495057;
        }
        
        .product-price {
            font-weight: 600;
            color: #28a745;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state-title {
            font-size: 24px;
            margin-bottom: 10px;
            color: #495057;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .total-value {
                font-size: 32px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <div class="back-button">
            <a href="list.php" class="btn-back">
                ← Kembali ke Daftar Transaksi
            </a>
        </div>
        
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div>
                    <h1 class="page-title">🧾 Detail Transaksi</h1>
                    <p class="page-subtitle">Informasi lengkap transaksi #<?php echo htmlspecialchars($transaksi['id_transaksi']); ?></p>
                </div>
                <div>
                    <div style="background: rgba(255, 255, 255, 0.2); padding: 15px 25px; border-radius: 15px;">
                        <div style="font-size: 14px; opacity: 0.9;">Tanggal Cetak</div>
                        <div style="font-size: 18px; font-weight: 600;"><?php echo date('d/m/Y H:i:s'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Flash Message -->
        <?php if ($flash_msg): ?>
            <div class="flash-message <?php echo $flash_msg['type'] == 'error' ? 'flash-error' : ''; ?>">
                <?php echo htmlspecialchars($flash_msg['text']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Transaction Info -->
        <div class="transaction-info">
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">ID Transaksi</div>
                    <div class="info-value">#<?php echo htmlspecialchars($transaksi['id_transaksi']); ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Tanggal & Waktu</div>
                    <div class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($transaksi['tanggal'])); ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Kasir</div>
                    <div class="info-value"><?php echo htmlspecialchars($transaksi['nama_kasir']); ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Status Transaksi</div>
                    <div class="info-value">
                        <span style="color: #28a745; font-weight: 600;">✅ Selesai</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Card -->
        <div class="total-card">
            <div class="total-label">Total Pembayaran</div>
            <div class="total-value">Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?></div>
            <div class="total-words">
                <?php
                // Fungsi konversi angka ke terbilang (opsional)
                function terbilang($x) {
                    $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
                    if ($x < 12)
                        return " " . $angka[$x];
                    elseif ($x < 20)
                        return terbilang($x - 10) . " belas";
                    elseif ($x < 100)
                        return terbilang($x / 10) . " puluh" . terbilang($x % 10);
                    elseif ($x < 200)
                        return " seratus" . terbilang($x - 100);
                    elseif ($x < 1000)
                        return terbilang($x / 100) . " ratus" . terbilang($x % 100);
                    elseif ($x < 2000)
                        return " seribu" . terbilang($x - 1000);
                    elseif ($x < 1000000)
                        return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
                    elseif ($x < 1000000000)
                        return terbilang($x / 1000000) . " juta" . terbilang($x % 1000000);
                }
                ?>
                <?php echo ucfirst(terbilang($transaksi['total_harga'])); ?> rupiah
            </div>
        </div>
        
        <!-- Products Table -->
        <div class="table-container">
            <h3 style="padding: 20px; margin: 0; color: #495057; border-bottom: 2px solid #f0f0f0;">
                📦 Produk yang Dibeli
            </h3>
            
            <?php if (mysqli_num_rows($detail_result) > 0): ?>
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Produk</th>
                            <th width="15%">Harga Satuan</th>
                            <th width="10%">Jumlah</th>
                            <th width="20%">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($detail_result)): 
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td class="product-name"><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td class="text-right product-price">
                                    Rp <?php echo number_format($row['harga_satuan'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center"><?php echo htmlspecialchars($row['jumlah']); ?> unit</td>
                                <td class="text-right product-price">
                                    Rp <?php echo number_format($row['subtotal'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <h2 class="empty-state-title">Tidak ada produk</h2>
                    <p>Detail produk tidak ditemukan untuk transaksi ini.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Summary -->
        <div class="transaction-info">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h3 style="color: #495057; margin-bottom: 10px;">Ringkasan</h3>
                    <p style="color: #6c757d;">
                        Total item: <strong><?php echo mysqli_num_rows($detail_result); ?> produk</strong>
                    </p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 14px; color: #6c757d;">Total Transaksi</div>
                    <div style="font-size: 24px; font-weight: 700; color: #28a745;">
                        Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button onclick="printReceipt()" class="btn btn-primary">
                🖨️ Cetak Struk
            </button>
            <a href="list.php" class="btn btn-secondary">
                📋 Kembali ke Daftar
            </a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="../dashboard/index.php" class="btn btn-success">
                    🏠 Dashboard
                </a>
            <?php else: ?>
                <a href="tambah.php" class="btn btn-success">
                    🛒 Transaksi Baru
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/script.js"></script>
    <script>
        // Print receipt
        function printReceipt() {
            const originalContent = document.body.innerHTML;
            
            // Buat konten struk
            const strukContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Struk #<?php echo $transaksi['id_transaksi']; ?></title>
                    <style>
                        body { 
                            font-family: 'Courier New', monospace; 
                            width: 80mm; 
                            margin: 0 auto; 
                            padding: 10px;
                            font-size: 12px;
                        }
                        .header { text-align: center; margin-bottom: 15px; }
                        .store-name { font-weight: bold; font-size: 16px; }
                        .store-address { font-size: 10px; }
                        .divider { border-top: 1px dashed #000; margin: 10px 0; }
                        .transaction-info { margin-bottom: 10px; }
                        .transaction-info div { display: flex; justify-content: space-between; }
                        .items-table { width: 100%; border-collapse: collapse; }
                        .items-table td { padding: 3px 0; }
                        .total { font-weight: bold; font-size: 14px; }
                        .footer { text-align: center; margin-top: 20px; font-size: 10px; }
                        .thank-you { font-weight: bold; margin: 10px 0; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="store-name">TOKO SEDERHANA</div>
                        <div class="store-address">Jl. Contoh No. 123, Kota</div>
                        <div class="store-address">Telp: 0812-3456-7890</div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="transaction-info">
                        <div>
                            <span>No. Transaksi:</span>
                            <span>#<?php echo $transaksi['id_transaksi']; ?></span>
                        </div>
                        <div>
                            <span>Tanggal:</span>
                            <span><?php echo date('d/m/Y H:i:s', strtotime($transaksi['tanggal'])); ?></span>
                        </div>
                        <div>
                            <span>Kasir:</span>
                            <span><?php echo htmlspecialchars($transaksi['nama_kasir']); ?></span>
                        </div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <table class="items-table">
                        <?php 
                        mysqli_data_seek($detail_result, 0);
                        $item_no = 1;
                        while ($item = mysqli_fetch_assoc($detail_result)): 
                        ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($item['nama_produk']); ?><br>
                                <small><?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?> x <?php echo $item['jumlah']; ?></small>
                            </td>
                            <td align="right"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                    
                    <div class="divider"></div>
                    
                    <div class="transaction-info">
                        <div>
                            <span>Total:</span>
                            <span class="total">Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="footer">
                        <div class="thank-you">Terima kasih atas kunjungannya!</div>
                        <div>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</div>
                        <div>Struk ini sah sebagai bukti pembayaran</div>
                        <div>Dicetak: <?php echo date('d/m/Y H:i:s'); ?></div>
                    </div>
                </body>
                </html>
            `;
            
            // Ganti konten body dengan struk
            document.body.innerHTML = strukContent;
            
            // Print
            window.print();
            
            // Kembalikan konten asli
            document.body.innerHTML = originalContent;
            window.location.reload();
        }
        
        // Auto-hide flash message
        setTimeout(function() {
            const flashMsg = document.querySelector('.flash-message');
            if (flashMsg) {
                flashMsg.style.transition = 'opacity 0.5s';
                flashMsg.style.opacity = '0';
                setTimeout(() => flashMsg.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>