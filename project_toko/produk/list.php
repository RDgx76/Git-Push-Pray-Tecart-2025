<?php
require_once '../auth.php';
require_once '../db.php';

// Cek role admin
if ($_SESSION['role'] != 'admin') {
    redirect_with_message('../dashboard/index.php', 'Akses ditolak! Hanya admin yang bisa mengakses halaman ini.', 'error');
}

// Ambil pesan flash jika ada
$flash_msg = get_flash_message();

// Ambil data produk
$sql = "SELECT * FROM produk ORDER BY id_produk DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk - Sistem Toko</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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
        
        .page-title {
            font-size: 28px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .page-subtitle {
            opacity: 0.9;
            font-size: 16px;
        }
        
        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
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
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        /* Search Box */
        .search-box {
            flex-grow: 1;
            max-width: 400px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Table */
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
        
        .stok-cell {
            text-align: center;
        }
        
        .harga-cell {
            text-align: right;
            font-weight: 600;
            color: #28a745;
        }
        
        .actions-cell {
            text-align: center;
            white-space: nowrap;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .btn-small {
            padding: 8px 15px;
            font-size: 14px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        /* Stok Indicators */
        .stok-indicator {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            min-width: 60px;
            text-align: center;
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
        
        /* Flash Message */
        .flash-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #28a745;
            animation: slideIn 0.5s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .close-flash {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        
        .close-flash:hover {
            opacity: 1;
        }
        
        /* Summary */
        .summary {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            font-size: 16px;
            color: #495057;
        }
        
        .summary strong {
            color: #667eea;
            font-size: 18px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
                order: -1;
            }
            
            .btn-group {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            th, td {
                padding: 12px 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title">📦 Daftar Produk</h1>
            <p class="page-subtitle">Kelola semua produk yang dijual di toko</p>
        </div>
        
        <!-- Flash Message -->
        <?php if ($flash_msg): ?>
            <div class="flash-message <?php echo $flash_msg['type'] == 'error' ? 'flash-error' : ''; ?>">
                <span><?php echo htmlspecialchars($flash_msg['text']); ?></span>
                <button class="close-flash" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>
        
        <!-- Action Bar -->
        <div class="action-bar">
            <div class="btn-group">
                <a href="tambah.php" class="btn btn-success">
                    <span>➕</span> Tambah Produk
                </a>
                <a href="../dashboard/index.php" class="btn btn-secondary">
                    <span>🏠</span> Dashboard
                </a>
            </div>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Cari produk..." onkeyup="searchProducts()">
            </div>
        </div>
        
        <!-- Table -->
        <div class="table-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th>Nama Produk</th>
                            <th width="15%">Harga</th>
                            <th width="15%">Stok</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <tr>
                                <td>#<?php echo htmlspecialchars($row['id_produk']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td class="harga-cell">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td class="stok-cell">
                                    <span class="stok-indicator <?php echo $stok_class; ?>">
                                        <?php echo htmlspecialchars($row['stok']); ?> unit
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <div class="action-buttons">
                                        <a href="edit.php?id=<?php echo $row['id_produk']; ?>" class="btn-small btn-primary">
                                            ✏️ Edit
                                        </a>
                                        <a href="hapus.php?id=<?php echo $row['id_produk']; ?>" 
                                           class="btn-small btn-danger"
                                           onclick="return confirm('Yakin hapus produk <?php echo addslashes($row['nama_produk']); ?>?')">
                                            🗑️ Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <h2 class="empty-state-title">Belum ada produk</h2>
                    <p>Mulai dengan menambahkan produk pertama Anda</p>
                    <br>
                    <a href="tambah.php" class="btn btn-success">
                        <span>➕</span> Tambah Produk Pertama
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Summary -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="summary">
                Total: <strong><?php echo mysqli_num_rows($result); ?> produk</strong> 
                | Stok total: <strong><?php 
                    $total_stok = 0;
                    mysqli_data_seek($result, 0);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $total_stok += $row['stok'];
                    }
                    echo number_format($total_stok, 0, ',', '.');
                ?> unit</strong>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../assets/script.js"></script>
    <script>
        // Fungsi pencarian produk
        function searchProducts() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('productsTable');
            
            if (!table) return;
            
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length - 1; j++) { // Exclude action column
                    if (cells[j].textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        }
        
        // Auto-hide flash message setelah 5 detik
        setTimeout(function() {
            const flashMsg = document.querySelector('.flash-message');
            if (flashMsg) {
                flashMsg.style.transition = 'opacity 0.5s';
                flashMsg.style.opacity = '0';
                setTimeout(() => flashMsg.remove(), 500);
            }
        }, 5000);
        
        // Fokus ke search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>