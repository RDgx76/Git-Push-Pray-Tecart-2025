<?php
require_once '../auth.php';
require_once '../db.php';

// Cek role admin
if ($_SESSION['role'] != 'admin') {
    redirect_with_message('list.php', 'Akses ditolak! Hanya admin yang bisa mengedit produk.', 'error');
}

// Cek parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect_with_message('list.php', 'ID produk tidak valid!', 'error');
}

$id = intval($_GET['id']);

// Ambil data produk
$sql = "SELECT * FROM produk WHERE id_produk = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($stmt);

// Cek apakah produk ada
if (!$produk) {
    redirect_with_message('list.php', 'Produk tidak ditemukan!', 'error');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = clean_input($_POST['nama']);
    $harga = clean_input($_POST['harga']);
    $stok = clean_input($_POST['stok']);
    
    // Validasi
    if (empty($nama)) {
        $errors[] = 'Nama produk harus diisi';
    }
    
    if (!is_numeric($harga) || $harga < 0) {
        $errors[] = 'Harga harus angka dan tidak boleh negatif';
    }
    
    if (!is_numeric($stok) || $stok < 0) {
        $errors[] = 'Stok harus angka dan tidak boleh negatif';
    }
    
    // Jika tidak ada error, update ke database
    if (empty($errors)) {
        $sql = "UPDATE produk 
                SET nama_produk = ?, harga = ?, stok = ? 
                WHERE id_produk = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "siii", $nama, $harga, $stok, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            redirect_with_message('list.php', 'Produk berhasil diperbarui!', 'success');
        } else {
            $errors[] = 'Gagal memperbarui produk: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Sistem Toko</title>
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
            max-width: 800px;
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
            text-align: center;
        }
        
        .page-title {
            font-size: 28px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
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
        
        /* Product Info */
        .product-info {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #667eea;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        /* Form Container */
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
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
        
        /* Error Messages */
        .error-container {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #dc3545;
        }
        
        .error-container ul {
            margin-left: 20px;
        }
        
        /* Input Group */
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: 500;
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
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
            flex: 1;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        /* Preview Card */
        .preview-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-top: 10px;
            border: 2px dashed #dee2e6;
            display: none;
        }
        
        .preview-card.show {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .preview-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .preview-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .preview-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .preview-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .preview-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <div class="back-button">
            <a href="list.php" class="btn-back">
                ← Kembali ke Daftar Produk
            </a>
        </div>
        
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title">✏️ Edit Produk</h1>
            <p class="page-subtitle">Perbarui informasi produk yang sudah ada</p>
        </div>
        
        <!-- Product Info -->
        <div class="product-info">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">ID Produk</div>
                    <div class="info-value">#<?php echo htmlspecialchars($produk['id_produk']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nama Saat Ini</div>
                    <div class="info-value"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Harga Saat Ini</div>
                    <div class="info-value">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Stok Saat Ini</div>
                    <div class="info-value"><?php echo htmlspecialchars($produk['stok']); ?> unit</div>
                </div>
            </div>
        </div>
        
        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="error-container">
                <strong>⚠️ Terdapat kesalahan:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Form -->
        <div class="form-container">
            <form method="POST" action="" id="productForm">
                <div class="form-group">
                    <label class="form-label" for="nama">
                        Nama Produk <span>*</span>
                    </label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           class="form-control" 
                           placeholder="Masukkan nama produk"
                           required
                           value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : htmlspecialchars($produk['nama_produk']); ?>">
                    <small class="form-text">Nama baru untuk produk ini</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="harga">
                        Harga (Rp) <span>*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" 
                               id="harga" 
                               name="harga" 
                               class="form-control" 
                               placeholder="Masukkan harga"
                               min="0"
                               step="100"
                               required
                               value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : htmlspecialchars($produk['harga']); ?>"
                               oninput="updatePreview()">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <small class="form-text">Harga jual baru per unit</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="stok">
                        Stok <span>*</span>
                    </label>
                    <input type="number" 
                           id="stok" 
                           name="stok" 
                           class="form-control" 
                           placeholder="Masukkan jumlah stok"
                           min="0"
                           required
                           value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : htmlspecialchars($produk['stok']); ?>"
                           oninput="updatePreview()">
                    <small class="form-text">Jumlah stok yang tersedia</small>
                </div>
                
                <!-- Preview -->
                <div class="preview-card" id="previewCard">
                    <div class="preview-title">
                        👁️ Preview Perubahan
                    </div>
                    <div class="preview-content">
                        <div class="preview-item">
                            <div class="preview-label">Nama Produk</div>
                            <div class="preview-value" id="previewNama"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Harga</div>
                            <div class="preview-value" id="previewHarga">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Stok</div>
                            <div class="preview-value" id="previewStok"><?php echo htmlspecialchars($produk['stok']); ?> unit</div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        💾 Simpan Perubahan
                    </button>
                    <a href="list.php" class="btn btn-secondary">
                        ❌ Batal
                    </a>
                    <a href="hapus.php?id=<?php echo $id; ?>" 
                       class="btn btn-warning"
                       onclick="return confirm('Yakin hapus produk ini?')">
                        🗑️ Hapus Produk
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/script.js"></script>
    <script>
        // Update preview card
        function updatePreview() {
            const nama = document.getElementById('nama').value;
            const harga = document.getElementById('harga').value;
            const stok = document.getElementById('stok').value;
            const previewCard = document.getElementById('previewCard');
            
            // Tampilkan preview jika ada perubahan
            const originalNama = "<?php echo htmlspecialchars($produk['nama_produk']); ?>";
            const originalHarga = <?php echo $produk['harga']; ?>;
            const originalStok = <?php echo $produk['stok']; ?>;
            
            const hasChanges = (nama !== originalNama) || 
                               (parseInt(harga) !== originalHarga) || 
                               (parseInt(stok) !== originalStok);
            
            if (hasChanges) {
                previewCard.classList.add('show');
                
                document.getElementById('previewNama').textContent = nama;
                document.getElementById('previewHarga').textContent = harga ? 'Rp ' + formatNumber(harga) : '-';
                document.getElementById('previewStok').textContent = stok ? stok + ' unit' : '-';
                
                // Highlight perubahan
                if (nama !== originalNama) {
                    document.getElementById('previewNama').style.color = '#28a745';
                    document.getElementById('previewNama').innerHTML += ' <small style="color: #6c757d">(berubah)</small>';
                }
                
                if (parseInt(harga) !== originalHarga) {
                    document.getElementById('previewHarga').style.color = '#28a745';
                }
                
                if (parseInt(stok) !== originalStok) {
                    document.getElementById('previewStok').style.color = '#28a745';
                }
            } else {
                previewCard.classList.remove('show');
            }
        }
        
        // Format number dengan separator
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        
        // Fokus ke input pertama
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nama').focus();
            updatePreview();
        });
        
        // Validasi form sebelum submit
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const harga = document.getElementById('harga').value;
            const stok = document.getElementById('stok').value;
            
            if (harga < 0) {
                alert('Harga tidak boleh negatif!');
                e.preventDefault();
                return false;
            }
            
            if (stok < 0) {
                alert('Stok tidak boleh negatif!');
                e.preventDefault();
                return false;
            }
            
            // Cek apakah ada perubahan
            const originalNama = "<?php echo htmlspecialchars($produk['nama_produk']); ?>";
            const originalHarga = <?php echo $produk['harga']; ?>;
            const originalStok = <?php echo $produk['stok']; ?>;
            
            const nama = document.getElementById('nama').value;
            
            if (nama === originalNama && 
                parseInt(harga) === originalHarga && 
                parseInt(stok) === originalStok) {
                const confirmUpdate = confirm('Tidak ada perubahan yang dibuat. Tetap lanjutkan?');
                if (!confirmUpdate) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Show loading
            const submitBtn = this.querySelector('[type="submit"]');
            submitBtn.innerHTML = '⏳ Menyimpan...';
            submitBtn.disabled = true;
            
            return true;
        });
    </script>
</body>
</html>