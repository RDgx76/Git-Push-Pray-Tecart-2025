<?php
require_once '../auth.php';
require_once '../db.php';

// Hanya admin dan kasir yang boleh akses
if (!in_array($_SESSION['role'], ['admin', 'kasir'])) {
    redirect_with_message('../dashboard/index.php', 'Akses ditolak!', 'error');
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Konfigurasi filter
$filter_user = '';
$filter_date = '';
$where_conditions = [];

// Jika kasir, hanya tampilkan transaksi miliknya
if ($user_role == 'kasir') {
    $where_conditions[] = "t.id_user = $user_id";
}

// Filter tanggal jika ada
if (isset($_GET['tanggal']) && !empty($_GET['tanggal'])) {
    $tanggal = mysqli_real_escape_string($conn, $_GET['tanggal']);
    $where_conditions[] = "DATE(t.tanggal) = '$tanggal'";
    $filter_date = $tanggal;
}

// Filter kasir jika admin
if ($user_role == 'admin' && isset($_GET['kasir']) && !empty($_GET['kasir'])) {
    $kasir_id = intval($_GET['kasir']);
    $where_conditions[] = "t.id_user = $kasir_id";
    $filter_user = $kasir_id;
}

// Buat WHERE clause
$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Ambil data kasir untuk filter
$sql_kasir = "SELECT id_user, username FROM user WHERE role = 'kasir' ORDER BY username";
$result_kasir = mysqli_query($conn, $sql_kasir);

// Hitung statistik
$sql_stats = "SELECT 
                COUNT(*) as total_transaksi,
                SUM(total_harga) as total_pendapatan,
                AVG(total_harga) as rata_rata
              FROM transaksi t $where_clause";
$result_stats = mysqli_query($conn, $sql_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Ambil data transaksi
$sql = "SELECT t.*, u.username as nama_kasir 
        FROM transaksi t
        LEFT JOIN user u ON t.id_user = u.id_user
        $where_clause
        ORDER BY t.tanggal DESC
        LIMIT 100";
$result = mysqli_query($conn, $sql);

// Ambil pesan flash jika ada
$flash_msg = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Sistem Toko</title>
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
            max-width: 1400px;
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
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
            text-align: center;
            border-top: 5px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card:nth-child(1) {
            border-top-color: #28a745;
        }
        
        .stat-card:nth-child(2) {
            border-top-color: #007bff;
        }
        
        .stat-card:nth-child(3) {
            border-top-color: #ffc107;
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stat-card:nth-child(1) .stat-number {
            color: #28a745;
        }
        
        .stat-card:nth-child(2) .stat-number {
            color: #007bff;
        }
        
        .stat-card:nth-child(3) .stat-number {
            color: #ffc107;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .filter-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
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
        
        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        /* Table Container */
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
            min-width: 1000px;
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
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        /* Amount */
        .amount {
            font-weight: 700;
            color: #28a745;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
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
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
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
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .page-link {
            padding: 10px 18px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-decoration: none;
            color: #495057;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .page-link:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .page-link.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        /* Export Buttons */
        .export-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-export {
            background: #20c997;
            color: white;
        }
        
        .btn-export:hover {
            background: #17a589;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .filter-buttons {
                justify-content: center;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .export-buttons {
                justify-content: center;
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
            <h1 class="page-title">📋 Riwayat Transaksi</h1>
            <p class="page-subtitle">
                <?php if ($user_role == 'admin'): ?>
                    Semua transaksi yang dilakukan di toko
                <?php else: ?>
                    Transaksi yang Anda buat sebagai kasir
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Flash Message -->
        <?php if ($flash_msg): ?>
            <div class="flash-message <?php echo $flash_msg['type'] == 'error' ? 'flash-error' : ''; ?>">
                <?php echo htmlspecialchars($flash_msg['text']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number"><?php echo number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo $stats['total_transaksi'] ?? 0; ?></div>
                <div class="stat-label">Jumlah Transaksi</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-number"><?php echo number_format($stats['rata_rata'] ?? 0, 0, ',', '.'); ?></div>
                <div class="stat-label">Rata-rata per Transaksi</div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <h3 class="filter-title">🔍 Filter Transaksi</h3>
            <form method="GET" action="" class="filter-form">
                <?php if ($user_role == 'admin'): ?>
                    <div class="form-group">
                        <label class="form-label">Pilih Kasir</label>
                        <select name="kasir" class="form-control">
                            <option value="">-- Semua Kasir --</option>
                            <?php while ($kasir = mysqli_fetch_assoc($result_kasir)): ?>
                                <option value="<?php echo $kasir['id_user']; ?>" 
                                    <?php echo ($filter_user == $kasir['id_user']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kasir['username']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">Filter Tanggal</label>
                    <input type="date" 
                           name="tanggal" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">
                        🔎 Terapkan Filter
                    </button>
                    <a href="list.php" class="btn btn-secondary">
                        🔄 Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Action Bar -->
        <div class="action-bar">
            <div>
                <a href="../dashboard/index.php" class="btn btn-secondary">
                    ← Dashboard
                </a>
                <?php if ($user_role == 'kasir'): ?>
                    <a href="tambah.php" class="btn btn-success">
                        🛒 Transaksi Baru
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="export-buttons">
                <button onclick="printTable()" class="btn btn-export">
                    🖨️ Cetak
                </button>
                <button onclick="exportToExcel()" class="btn btn-export">
                    📊 Export Excel
                </button>
            </div>
        </div>
        
        <!-- Table -->
        <div class="table-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table id="transactionsTable">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th>Tanggal & Waktu</th>
                            <?php if ($user_role == 'admin'): ?>
                                <th>Kasir</th>
                            <?php endif; ?>
                            <th width="15%">Total Harga</th>
                            <th width="10%">Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="text-center">#<?php echo htmlspecialchars($row['id_transaksi']); ?></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($row['tanggal'])); ?></td>
                                <?php if ($user_role == 'admin'): ?>
                                    <td><?php echo htmlspecialchars($row['nama_kasir']); ?></td>
                                <?php endif; ?>
                                <td class="text-right amount">
                                    Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge status-success">
                                        ✅ Selesai
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <a href="detail.php?id=<?php echo $row['id_transaksi']; ?>" 
                                           class="btn-small btn-info">
                                            👁️ Detail
                                        </a>
                                        <button onclick="printReceipt(<?php echo $row['id_transaksi']; ?>)" 
                                                class="btn-small btn-secondary">
                                            🧾 Struk
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <h2 class="empty-state-title">Belum ada transaksi</h2>
                    <p>
                        <?php if ($user_role == 'kasir'): ?>
                            Mulai dengan membuat transaksi pertama Anda
                        <?php else: ?>
                            Tidak ada transaksi yang ditemukan dengan filter ini
                        <?php endif; ?>
                    </p>
                    <br>
                    <?php if ($user_role == 'kasir'): ?>
                        <a href="tambah.php" class="btn btn-success">
                            🛒 Buat Transaksi Pertama
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Summary -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="filter-section" style="text-align: center;">
                <p>
                    Menampilkan <strong><?php echo mysqli_num_rows($result); ?> transaksi</strong>
                    <?php if ($filter_date): ?>
                        pada tanggal <strong><?php echo $filter_date; ?></strong>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../assets/script.js"></script>
    <script>
        // Print receipt
        function printReceipt(transactionId) {
            window.open(`struk.php?id=${transactionId}`, '_blank');
        }
        
        // Print table
        function printTable() {
            const originalContent = document.body.innerHTML;
            const printContent = document.querySelector('.table-container').innerHTML;
            
            document.body.innerHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Laporan Transaksi - <?php echo date('d/m/Y'); ?></title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th { background: #f2f2f2; padding: 10px; text-align: left; }
                        td { padding: 8px; border-bottom: 1px solid #ddd; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .footer { margin-top: 30px; text-align: center; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Laporan Transaksi</h2>
                        <p>Sistem Toko Sederhana</p>
                        <p>Tanggal: <?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                    ${printContent}
                    <div class="footer">
                        <p>Dicetak oleh: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p>Halaman dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                    </div>
                </body>
                </html>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        }
        
        // Export to Excel
        function exportToExcel() {
            const table = document.getElementById('transactionsTable');
            if (!table) {
                alert('Tidak ada data untuk diexport!');
                return;
            }
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let row of rows) {
                const cells = row.querySelectorAll('th, td');
                const rowData = [];
                
                for (let cell of cells) {
                    // Skip action column
                    if (cell.querySelector('.action-buttons')) {
                        continue;
                    }
                    rowData.push(`"${cell.textContent.trim()}"`);
                }
                
                csv.push(rowData.join(','));
            }
            
            const csvString = csv.join('\n');
            const blob = new Blob([csvString], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `transaksi_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            alert('File Excel berhasil diunduh!');
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
        
        // Initialize date picker dengan hari ini
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.querySelector('input[type="date"]');
            if (dateInput && !dateInput.value) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>