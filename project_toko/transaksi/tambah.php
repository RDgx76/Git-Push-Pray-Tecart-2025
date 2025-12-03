<?php
require_once '../auth.php';
require_once '../db.php';

// Cek role kasir
if ($_SESSION['role'] != 'kasir') {
    redirect_with_message('../dashboard/index.php', 'Akses ditolak! Hanya kasir yang bisa membuat transaksi.', 'error');
}

// Ambil produk yang ada stok
$sql = "SELECT * FROM produk WHERE stok > 0 ORDER BY nama_produk";
$result = mysqli_query($conn, $sql);

// Ambil pesan flash jika ada
$flash_msg = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Baru - Sistem Toko</title>
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
            max-width: 1000px;
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
        
        .user-info {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .username {
            font-size: 18px;
            font-weight: 600;
        }
        
        .role {
            font-size: 14px;
            opacity: 0.9;
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
        
        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }
        
        /* Form Card */
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 25px;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 16px;
        }
        
        .form-label span {
            color: #dc3545;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 40px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .form-text {
            display: block;
            margin-top: 5px;
            color: #6c757d;
            font-size: 14px;
        }
        
        /* Info Box */
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
            font-size: 14px;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Price Display */
        .price-display {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
            text-align: center;
            border: 2px dashed #dee2e6;
        }
        
        .price-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .price-value {
            font-size: 24px;
            font-weight: 700;
            color: #28a745;
        }
        
        /* Product List */
        .product-list {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
            padding-right: 10px;
        }
        
        .product-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .product-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .product-list::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 3px;
        }
        
        .product-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .product-card:hover {
            border-color: #667eea;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
        }
        
        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .product-name {
            font-weight: 600;
            color: #495057;
            font-size: 16px;
        }
        
        .product-price {
            font-weight: 700;
            color: #28a745;
            font-size: 16px;
        }
        
        .product-details {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #6c757d;
        }
        
        .product-stok {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .stok-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .stok-tinggi {
            background: #d4edda;
            color: #155724;
        }
        
        .stok-sedang {
            background: #fff3cd;
            color: #856404;
        }
        
        .stok-rendah {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Button */
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
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #28a745, #20c997);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state-title {
            font-size: 20px;
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
            
            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <div class="back-button">
            <a href="../dashboard/index.php" class="btn-back">
                ← Kembali ke Dashboard
            </a>
        </div>
        
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div>
                    <h1 class="page-title">🛒 Transaksi Baru</h1>
                    <p class="page-subtitle">Buat transaksi penjualan baru untuk pelanggan</p>
                </div>
                <div class="user-info">
                    <div class="username">Kasir: <?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div class="role"><?php echo date('d/m/Y H:i:s'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Flash Message -->
        <?php if ($flash_msg): ?>
            <div class="flash-message <?php echo $flash_msg['type'] == 'error' ? 'flash-error' : ''; ?>">
                <?php echo htmlspecialchars($flash_msg['text']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Form Section -->
            <div class="form-card">
                <h2 class="card-title">📝 Form Transaksi</h2>
                
                <?php if (mysqli_num_rows($result) == 0): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <h3 class="empty-state-title">Stok Habis</h3>
                        <p>Tidak ada produk yang tersedia untuk dijual.</p>
                        <p>Silakan hubungi admin untuk menambah stok produk.</p>
                    </div>
                <?php else: ?>
                    <form method="POST" action="simpan.php" id="transactionForm">
                        <div class="form-group">
                            <label class="form-label" for="id_produk">
                                Pilih Produk <span>*</span>
                            </label>
                            <select name="id_produk" 
                                    id="id_produk" 
                                    class="form-control" 
                                    required
                                    onchange="updateProductInfo()">
                                <option value="">-- Pilih Produk --</option>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <?php
                                    // Tentukan kelas stok
                                    $stok_class = '';
                                    if ($row['stok'] >= 50) {
                                        $stok_class = 'stok-tinggi';
                                    } elseif ($row['stok'] >= 10) {
                                        $stok_class = 'stok-sedang';
                                    } else {
                                        $stok_class = 'stok-rendah';
                                    }
                                    ?>
                                    <option value="<?php echo $row['id_produk']; ?>" 
                                            data-nama="<?php echo htmlspecialchars($row['nama_produk']); ?>"
                                            data-harga="<?php echo $row['harga']; ?>"
                                            data-stok="<?php echo $row['stok']; ?>"
                                            data-stok-class="<?php echo $stok_class; ?>">
                                        <?php echo htmlspecialchars($row['nama_produk']); ?> 
                                        - Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?> 
                                        - Stok: <?php echo $row['stok']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <small class="form-text">Pilih produk yang akan dibeli</small>
                        </div>
                        
                        <div class="info-box" id="productInfo" style="display: none;">
                            <span id="selectedProduct"></span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="jumlah">
                                Jumlah Pembelian <span>*</span>
                            </label>
                            <input type="number" 
                                   id="jumlah" 
                                   name="jumlah" 
                                   class="form-control" 
                                   placeholder="Masukkan jumlah"
                                   min="1"
                                   max="1"
                                   value="1"
                                   required
                                   oninput="calculateTotal()">
                            <small class="form-text" id="stokInfo">Stok tersedia: 0 unit</small>
                        </div>
                        
                        <div class="price-display">
                            <div class="price-label">Total Harga</div>
                            <div class="price-value" id="totalHarga">Rp 0</div>
                            <input type="hidden" id="totalHargaInput" name="total_harga" value="0">
                        </div>
                        
                        <div class="form-group">
                            <div class="info-box">
                                ℹ️ Pastikan semua data sudah benar sebelum memproses transaksi.
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            💳 Proses Transaksi
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Product List Section -->
            <div class="form-card">
                <h2 class="card-title">📦 Daftar Produk Tersedia</h2>
                
                <div class="product-list">
                    <?php 
                    // Reset pointer result
                    mysqli_data_seek($result, 0);
                    ?>
                    
                    <?php if (mysqli_num_rows($result) == 0): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">😔</div>
                            <p>Tidak ada produk yang tersedia</p>
                        </div>
                    <?php else: ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            // Tentukan kelas stok
                            $stok_class = '';
                            $stok_label = '';
                            
                            if ($row['stok'] >= 50) {
                                $stok_class = 'stok-tinggi';
                                $stok_label = 'Tinggi';
                            } elseif ($row['stok'] >= 10) {
                                $stok_class = 'stok-sedang';
                                $stok_label = 'Sedang';
                            } else {
                                $stok_class = 'stok-rendah';
                                $stok_label = 'Rendah';
                            }
                            ?>
                            <div class="product-card" 
                                 onclick="selectProduct(<?php echo $row['id_produk']; ?>)"
                                 style="cursor: pointer;">
                                <div class="product-header">
                                    <div class="product-name"><?php echo htmlspecialchars($row['nama_produk']); ?></div>
                                    <div class="product-price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                                </div>
                                <div class="product-details">
                                    <div class="product-stok">
                                        Stok: 
                                        <span class="stok-badge <?php echo $stok_class; ?>">
                                            <?php echo $row['stok']; ?> unit (<?php echo $stok_label; ?>)
                                        </span>
                                    </div>
                                    <div>
                                        ID: #<?php echo $row['id_produk']; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
                
                <a href="../produk/list.php" class="btn btn-secondary">
                    📦 Lihat Semua Produk
                </a>
            </div>
        </div>
    </div>
    
    <script src="../assets/script.js"></script>
    <script>
        // Variabel global
        let selectedProduct = null;
        
        // Update info produk yang dipilih
        function updateProductInfo() {
            const select = document.getElementById('id_produk');
            const productInfo = document.getElementById('productInfo');
            const stokInfo = document.getElementById('stokInfo');
            const jumlahInput = document.getElementById('jumlah');
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                selectedProduct = {
                    id: option.value,
                    nama: option.getAttribute('data-nama'),
                    harga: parseInt(option.getAttribute('data-harga')),
                    stok: parseInt(option.getAttribute('data-stok')),
                    stokClass: option.getAttribute('data-stok-class')
                };
                
                // Tampilkan info produk
                document.getElementById('selectedProduct').innerHTML = 
                    `<strong>${selectedProduct.nama}</strong> - Rp ${formatNumber(selectedProduct.harga)}`;
                productInfo.style.display = 'flex';
                
                // Update info stok
                stokInfo.textContent = `Stok tersedia: ${selectedProduct.stok} unit`;
                
                // Update jumlah input max value
                jumlahInput.max = selectedProduct.stok;
                jumlahInput.value = Math.min(1, selectedProduct.stok);
                
                // Hitung total
                calculateTotal();
            } else {
                productInfo.style.display = 'none';
                stokInfo.textContent = 'Stok tersedia: 0 unit';
                jumlahInput.max = 1;
                selectedProduct = null;
                calculateTotal();
            }
            
            updateSubmitButton();
        }
        
        // Pilih produk dari daftar
        function selectProduct(productId) {
            const select = document.getElementById('id_produk');
            select.value = productId;
            updateProductInfo();
            select.focus();
        }
        
        // Hitung total harga
        function calculateTotal() {
            const jumlahInput = document.getElementById('jumlah');
            const totalHarga = document.getElementById('totalHarga');
            const totalHargaInput = document.getElementById('totalHargaInput');
            
            let jumlah = parseInt(jumlahInput.value) || 1;
            
            // Validasi jumlah
            if (selectedProduct) {
                if (jumlah > selectedProduct.stok) {
                    alert(`Stok tidak cukup! Hanya tersedia ${selectedProduct.stok} unit.`);
                    jumlahInput.value = selectedProduct.stok;
                    jumlah = selectedProduct.stok;
                }
                
                if (jumlah < 1) {
                    jumlahInput.value = 1;
                    jumlah = 1;
                }
                
                // Hitung total
                const total = selectedProduct.harga * jumlah;
                totalHarga.textContent = 'Rp ' + formatNumber(total);
                totalHargaInput.value = total;
            } else {
                totalHarga.textContent = 'Rp 0';
                totalHargaInput.value = 0;
            }
            
            updateSubmitButton();
        }
        
        // Update status tombol submit
        function updateSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');
            const jumlahInput = document.getElementById('jumlah');
            const jumlah = parseInt(jumlahInput.value) || 0;
            
            if (!selectedProduct || jumlah < 1) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Pilih produk terlebih dahulu';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '💳 Proses Transaksi';
            }
        }
        
        // Format number dengan separator
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        
        // Validasi form sebelum submit
        document.getElementById('transactionForm').addEventListener('submit', function(e) {
            if (!selectedProduct) {
                alert('Silakan pilih produk terlebih dahulu!');
                e.preventDefault();
                return false;
            }
            
            const jumlahInput = document.getElementById('jumlah');
            const jumlah = parseInt(jumlahInput.value) || 0;
            
            if (jumlah < 1) {
                alert('Jumlah pembelian minimal 1 unit!');
                e.preventDefault();
                return false;
            }
            
            if (jumlah > selectedProduct.stok) {
                alert(`Stok tidak cukup! Hanya tersedia ${selectedProduct.stok} unit.`);
                e.preventDefault();
                return false;
            }
            
            // Konfirmasi transaksi
            const total = selectedProduct.harga * jumlah;
            const confirmMsg = `Konfirmasi Transaksi:\n\n` +
                              `Produk: ${selectedProduct.nama}\n` +
                              `Harga: Rp ${formatNumber(selectedProduct.harga)}\n` +
                              `Jumlah: ${jumlah} unit\n` +
                              `Total: Rp ${formatNumber(total)}\n\n` +
                              `Apakah data sudah benar?`;
            
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
            
            // Show loading
            const submitBtn = this.querySelector('[type="submit"]');
            submitBtn.innerHTML = '⏳ Memproses...';
            submitBtn.disabled = true;
            
            return true;
        });
        
        // Auto-hide flash message
        setTimeout(function() {
            const flashMsg = document.querySelector('.flash-message');
            if (flashMsg) {
                flashMsg.style.transition = 'opacity 0.5s';
                flashMsg.style.opacity = '0';
                setTimeout(() => flashMsg.remove(), 500);
            }
        }, 5000);
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateProductInfo();
        });
    </script>
</body>
</html>