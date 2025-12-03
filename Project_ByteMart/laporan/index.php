<?php
require_once '../auth.php';
require_once '../db.php';

// Hanya admin yang boleh akses
if ($_SESSION['role'] != 'admin') {
    redirect_with_message('../dashboard/index.php', 'Akses ditolak! Hanya admin yang bisa mengakses laporan.', 'error');
}

// Set default periode (bulan ini)
$bulan_sekarang = date('Y-m');
$tahun_sekarang = date('Y');

// Tangkap parameter filter
$periode = $_GET['periode'] ?? 'bulan'; // hari, minggu, bulan, tahun, custom
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01'); // awal bulan ini
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t'); // akhir bulan ini
$bulan = $_GET['bulan'] ?? $bulan_sekarang;
$tahun = $_GET['tahun'] ?? $tahun_sekarang;
$kasir_id = $_GET['kasir'] ?? 'all';

// Build WHERE clause
$where_conditions = ["1=1"];
$params = [];

// Filter tanggal berdasarkan periode
if ($periode == 'hari') {
    $tgl_awal = date('Y-m-d');
    $tgl_akhir = date('Y-m-d');
    $where_conditions[] = "DATE(t.tanggal) = CURDATE()";
} elseif ($periode == 'minggu') {
    $tgl_awal = date('Y-m-d', strtotime('monday this week'));
    $tgl_akhir = date('Y-m-d', strtotime('sunday this week'));
    $where_conditions[] = "DATE(t.tanggal) BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
} elseif ($periode == 'bulan') {
    $where_conditions[] = "DATE(t.tanggal) BETWEEN ? AND ?";
    $params[] = $bulan . '-01';
    $params[] = date('Y-m-t', strtotime($bulan . '-01'));
    $tgl_awal = $bulan . '-01';
    $tgl_akhir = date('Y-m-t', strtotime($bulan . '-01'));
} elseif ($periode == 'tahun') {
    $where_conditions[] = "YEAR(t.tanggal) = ?";
    $params[] = $tahun;
    $tgl_awal = $tahun . '-01-01';
    $tgl_akhir = $tahun . '-12-31';
} elseif ($periode == 'custom') {
    if ($tgl_awal && $tgl_akhir) {
        $where_conditions[] = "DATE(t.tanggal) BETWEEN ? AND ?";
        $params[] = $tgl_awal;
        $params[] = $tgl_akhir;
    }
}

// Filter kasir
if ($kasir_id != 'all' && is_numeric($kasir_id)) {
    $where_conditions[] = "t.id_user = ?";
    $params[] = $kasir_id;
}

$where_clause = implode(' AND ', $where_conditions);

// Ambil data kasir untuk filter
$sql_kasir = "SELECT id_user, username FROM user WHERE role = 'kasir' ORDER BY username";
$result_kasir = mysqli_query($conn, $sql_kasir);

// ============================================
// STATISTIK UTAMA
// ============================================

// Total pendapatan
$sql_total = "SELECT 
                COUNT(DISTINCT t.id_transaksi) as total_transaksi,
                SUM(t.total_harga) as total_pendapatan,
                AVG(t.total_harga) as rata_transaksi,
                COUNT(dt.id_detail) as total_item_terjual
              FROM transaksi t
              LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
              WHERE $where_clause";
$stmt_total = mysqli_prepare($conn, $sql_total);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_total, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt_total);
$result_total = mysqli_stmt_get_result($stmt_total);
$stats = mysqli_fetch_assoc($result_total);

// ============================================
// TOP 5 PRODUK TERLARIS
// ============================================

$sql_top_produk = "SELECT 
                    p.nama_produk,
                    SUM(dt.jumlah) as total_terjual,
                    SUM(dt.subtotal) as total_pendapatan
                  FROM detail_transaksi dt
                  JOIN produk p ON dt.id_produk = p.id_produk
                  JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
                  WHERE $where_clause
                  GROUP BY dt.id_produk
                  ORDER BY total_terjual DESC
                  LIMIT 5";
$stmt_top = mysqli_prepare($conn, $sql_top_produk);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_top, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt_top);
$top_produk = mysqli_stmt_get_result($stmt_top);

// ============================================
// PERFORMA KASIR
// ============================================

$sql_kasir_stats = "SELECT 
                      u.username,
                      COUNT(t.id_transaksi) as jumlah_transaksi,
                      SUM(t.total_harga) as total_penjualan,
                      AVG(t.total_harga) as rata_transaksi
                    FROM transaksi t
                    JOIN user u ON t.id_user = u.id_user
                    WHERE $where_clause AND u.role = 'kasir'
                    GROUP BY t.id_user
                    ORDER BY total_penjualan DESC";
$stmt_kasir = mysqli_prepare($conn, $sql_kasir_stats);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_kasir, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt_kasir);
$kasir_stats = mysqli_stmt_get_result($stmt_kasir);

// ============================================
// TREN HARIAN
// ============================================

$sql_tren = "SELECT 
               DATE(t.tanggal) as tanggal,
               COUNT(t.id_transaksi) as jumlah_transaksi,
               SUM(t.total_harga) as total_pendapatan
             FROM transaksi t
             WHERE $where_clause
             GROUP BY DATE(t.tanggal)
             ORDER BY tanggal DESC
             LIMIT 30";
$stmt_tren = mysqli_prepare($conn, $sql_tren);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_tren, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt_tren);
$tren_data = mysqli_stmt_get_result($stmt_tren);

// ============================================
// TRANSAKSI TERBARU
// ============================================

$sql_transaksi = "SELECT 
                    t.id_transaksi,
                    t.tanggal,
                    u.username as kasir,
                    t.total_harga
                  FROM transaksi t
                  JOIN user u ON t.id_user = u.id_user
                  WHERE $where_clause
                  ORDER BY t.tanggal DESC
                  LIMIT 10";
$stmt_trans = mysqli_prepare($conn, $sql_transaksi);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_trans, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt_trans);
$transaksi_terbaru = mysqli_stmt_get_result($stmt_trans);

// Ambil pesan flash jika ada
$flash_msg = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Sistem Toko</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            max-width: 1600px;
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
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
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
            flex-wrap: wrap;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
            border-top: 5px solid;
            position: relative;
            overflow: hidden;
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
        
        .stat-card:nth-child(4) {
            border-top-color: #dc3545;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: inherit;
            border-top-color: inherit;
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
            opacity: 0.8;
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
        
        .stat-card:nth-child(4) .stat-number {
            color: #dc3545;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-trend {
            font-size: 12px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .trend-up {
            color: #28a745;
        }
        
        .trend-down {
            color: #dc3545;
        }
        
        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 1200px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Chart Card */
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            height: 400px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-container {
            width: 100%;
            height: 300px;
            position: relative;
        }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            overflow-x: auto;
            max-height: 400px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        thead {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
            position: sticky;
            top: 0;
        }
        
        th {
            padding: 16px 15px;
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
            padding: 14px 15px;
            color: #555;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-primary {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-success {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .badge-warning {
            background: #fff3e0;
            color: #f57c00;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            flex-wrap: wrap;
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
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-info {
            background: #17a2b8;
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
        
        /* Loading */
        .loading {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        /* Period Info */
        .period-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid #2196f3;
        }
        
        .period-label {
            font-size: 14px;
            color: #1976d2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .period-value {
            font-size: 18px;
            font-weight: 600;
            color: #0d47a1;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-card {
                height: auto;
                min-height: 350px;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
        <!-- Back Button -->
        <div class="back-button">
            <a href="../dashboard/index.php" class="btn-back">
                ← Kembali ke Dashboard
            </a>
        </div>
        
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title">📊 Laporan Penjualan</h1>
            <p class="page-subtitle">Analisis dan statistik penjualan toko</p>
        </div>
        
        <!-- Flash Message -->
        <?php if ($flash_msg): ?>
            <div class="flash-message <?php echo $flash_msg['type'] == 'error' ? 'flash-error' : ''; ?>">
                <?php echo htmlspecialchars($flash_msg['text']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Period Info -->
        <div class="period-info">
            <div class="period-label">Periode Laporan</div>
            <div class="period-value">
                <?php 
                $periode_text = '';
                switch($periode) {
                    case 'hari': $periode_text = 'Hari Ini (' . date('d/m/Y') . ')'; break;
                    case 'minggu': $periode_text = 'Minggu Ini (' . date('d/m/Y', strtotime($tgl_awal)) . ' - ' . date('d/m/Y', strtotime($tgl_akhir)) . ')'; break;
                    case 'bulan': $periode_text = date('F Y', strtotime($bulan . '-01')); break;
                    case 'tahun': $periode_text = 'Tahun ' . $tahun; break;
                    case 'custom': $periode_text = date('d/m/Y', strtotime($tgl_awal)) . ' - ' . date('d/m/Y', strtotime($tgl_akhir)); break;
                }
                echo $periode_text;
                ?>
                <?php if ($kasir_id != 'all'): ?>
                    <?php 
                    $kasir_name = 'Semua Kasir';
                    mysqli_data_seek($result_kasir, 0);
                    while ($k = mysqli_fetch_assoc($result_kasir)) {
                        if ($k['id_user'] == $kasir_id) {
                            $kasir_name = $k['username'];
                            break;
                        }
                    }
                    ?>
                    | Kasir: <?php echo htmlspecialchars($kasir_name); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <h3 class="filter-title">🔍 Filter Laporan</h3>
            <form method="GET" action="" id="filterForm">
                <div class="filter-grid">
                    <div class="form-group">
                        <label class="form-label">Jenis Periode</label>
                        <select name="periode" id="periodeSelect" class="form-control" onchange="toggleCustomDate()">
                            <option value="hari" <?php echo $periode == 'hari' ? 'selected' : ''; ?>>Hari Ini</option>
                            <option value="minggu" <?php echo $periode == 'minggu' ? 'selected' : ''; ?>>Minggu Ini</option>
                            <option value="bulan" <?php echo $periode == 'bulan' ? 'selected' : ''; ?>>Bulan Ini</option>
                            <option value="tahun" <?php echo $periode == 'tahun' ? 'selected' : ''; ?>>Tahun Ini</option>
                            <option value="custom" <?php echo $periode == 'custom' ? 'selected' : ''; ?>>Rentang Kustom</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="bulanGroup" style="display: <?php echo $periode == 'bulan' ? 'block' : 'none'; ?>;">
                        <label class="form-label">Pilih Bulan</label>
                        <input type="month" name="bulan" class="form-control" value="<?php echo htmlspecialchars($bulan); ?>">
                    </div>
                    
                    <div class="form-group" id="tahunGroup" style="display: <?php echo $periode == 'tahun' ? 'block' : 'none'; ?>;">
                        <label class="form-label">Pilih Tahun</label>
                        <select name="tahun" class="form-control">
                            <?php for ($y = 2023; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo $tahun == $y ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" id="customDateGroup" style="display: <?php echo $periode == 'custom' ? 'block' : 'none'; ?>;">
                        <label class="form-label">Rentang Tanggal</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="date" name="tgl_awal" class="form-control" value="<?php echo htmlspecialchars($tgl_awal); ?>">
                            <input type="date" name="tgl_akhir" class="form-control" value="<?php echo htmlspecialchars($tgl_akhir); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Filter Kasir</label>
                        <select name="kasir" class="form-control">
                            <option value="all">Semua Kasir</option>
                            <?php 
                            mysqli_data_seek($result_kasir, 0);
                            while ($kasir = mysqli_fetch_assoc($result_kasir)): 
                            ?>
                                <option value="<?php echo $kasir['id_user']; ?>" 
                                    <?php echo ($kasir_id == $kasir['id_user']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kasir['username']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">
                        🔎 Terapkan Filter
                    </button>
                    <button type="button" onclick="printReport()" class="btn btn-secondary">
                        🖨️ Cetak Laporan
                    </button>
                    <button type="button" onclick="exportToExcel()" class="btn btn-success">
                        📊 Export Excel
                    </button>
                    <a href="index.php" class="btn btn-danger">
                        🔄 Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number">Rp <?php echo number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-trend trend-up">
                    📈 <?php echo $stats['total_transaksi'] > 0 ? number_format($stats['rata_transaksi'] ?? 0, 0, ',', '.') : '0'; ?> /transaksi
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo $stats['total_transaksi'] ?? 0; ?></div>
                <div class="stat-label">Jumlah Transaksi</div>
                <div class="stat-trend">
                    📦 <?php echo $stats['total_item_terjual'] ?? 0; ?> item terjual
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-number">Rp <?php echo number_format($stats['rata_transaksi'] ?? 0, 0, ',', '.'); ?></div>
                <div class="stat-label">Rata-rata Transaksi</div>
                <div class="stat-trend">
                    ⚡ Efisiensi penjualan
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo mysqli_num_rows($kasir_stats); ?></div>
                <div class="stat-label">Kasir Aktif</div>
                <div class="stat-trend">
                    👨‍💼 Performa tim
                </div>
            </div>
        </div>
        
        <!-- Main Grid -->
        <div class="main-grid">
            <!-- Chart: Tren Harian -->
            <div class="chart-card">
                <h3 class="card-title">📈 Tren Penjualan Harian</h3>
                <div class="chart-container">
                    <canvas id="trenChart"></canvas>
                </div>
            </div>
            
            <!-- Top Products -->
            <div class="chart-card">
                <h3 class="card-title">🏆 Top 5 Produk Terlaris</h3>
                <div class="chart-container">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Additional Grid -->
        <div class="main-grid">
            <!-- Kasir Performance -->
            <div class="table-container">
                <h3 style="padding: 20px; margin: 0; color: #495057; border-bottom: 2px solid #f0f0f0;">
                    👨‍💼 Performa Kasir
                </h3>
                <?php if (mysqli_num_rows($kasir_stats) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Kasir</th>
                                <th class="text-center">Transaksi</th>
                                <th class="text-right">Total Penjualan</th>
                                <th class="text-right">Rata-rata</th>
                                <th class="text-center">Rank</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            mysqli_data_seek($kasir_stats, 0);
                            while ($kasir = mysqli_fetch_assoc($kasir_stats)): 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($kasir['username']); ?></td>
                                    <td class="text-center"><?php echo $kasir['jumlah_transaksi']; ?></td>
                                    <td class="text-right">Rp <?php echo number_format($kasir['total_penjualan'], 0, ',', '.'); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($kasir['rata_transaksi'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $rank <= 3 ? 'badge-success' : 'badge-primary'; ?>">
                                            #<?php echo $rank++; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">👥</div>
                        <p>Tidak ada data kasir pada periode ini</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Transactions -->
            <div class="table-container">
                <h3 style="padding: 20px; margin: 0; color: #495057; border-bottom: 2px solid #f0f0f0;">
                    ⏰ Transaksi Terbaru
                </h3>
                <?php if (mysqli_num_rows($transaksi_terbaru) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tanggal</th>
                                <th>Kasir</th>
                                <th class="text-right">Total</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($transaksi_terbaru, 0);
                            while ($trans = mysqli_fetch_assoc($transaksi_terbaru)): 
                            ?>
                                <tr>
                                    <td>#<?php echo $trans['id_transaksi']; ?></td>
                                    <td><?php echo date('d/m H:i', strtotime($trans['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($trans['kasir']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($trans['total_harga'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <a href="../transaksi/detail.php?id=<?php echo $trans['id_transaksi']; ?>" 
                                           class="btn-small btn-info" style="padding: 5px 10px; font-size: 12px;">
                                            👁️ Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">⏰</div>
                        <p>Tidak ada transaksi pada periode ini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button onclick="printReport()" class="btn btn-secondary">
                🖨️ Cetak Laporan Lengkap
            </button>
            <button onclick="exportToExcel()" class="btn btn-success">
                📊 Export Data ke Excel
            </button>
            <a href="../dashboard/index.php" class="btn btn-primary">
                🏠 Kembali ke Dashboard
            </a>
        </div>
    </div>
    
    <script src="../assets/script.js"></script>
    <script>
        // Toggle custom date inputs
        function toggleCustomDate() {
            const periode = document.getElementById('periodeSelect').value;
            document.getElementById('bulanGroup').style.display = periode === 'bulan' ? 'block' : 'none';
            document.getElementById('tahunGroup').style.display = periode === 'tahun' ? 'block' : 'none';
            document.getElementById('customDateGroup').style.display = periode === 'custom' ? 'block' : 'none';
        }
        
        // Print report
        function printReport() {
            const originalContent = document.body.innerHTML;
            const printContent = document.querySelector('.container').innerHTML;
            
            document.body.innerHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Laporan Penjualan - <?php echo date('d/m/Y'); ?></title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
                        .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 8px; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th { background: #f2f2f2; padding: 10px; text-align: left; }
                        td { padding: 8px; border-bottom: 1px solid #ddd; }
                        .footer { margin-top: 30px; text-align: center; font-size: 12px; }
                        @media print {
                            .no-print { display: none !important; }
                            body { padding: 10px !important; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Laporan Penjualan - Toko Sederhana</h2>
                        <p>Periode: <?php echo $periode_text; ?></p>
                        <p>Tanggal Cetak: <?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                    ${printContent}
                    <div class="footer">
                        <p>Dicetak oleh: <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $_SESSION['role']; ?>)</p>
                        <p>Halaman dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                    </div>
                </body>
                </html>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
        }
        
        // Export to Excel
        function exportToExcel() {
            // Collect all table data
            let csv = [];
            
            // Add header
            csv.push(['LAPORAN PENJUALAN - TOKO SEDERHANA']);
            csv.push(['Periode:', '<?php echo $periode_text; ?>']);
            csv.push(['Tanggal Export:', new Date().toLocaleString('id-ID')]);
            csv.push([]);
            
            // Add stats
            csv.push(['STATISTIK UTAMA']);
            csv.push(['Total Pendapatan', 'Rp <?php echo number_format($stats["total_pendapatan"] ?? 0, 0, ",", "."); ?>']);
            csv.push(['Jumlah Transaksi', '<?php echo $stats["total_transaksi"] ?? 0; ?>']);
            csv.push(['Item Terjual', '<?php echo $stats["total_item_terjual"] ?? 0; ?>']);
            csv.push(['Rata-rata Transaksi', 'Rp <?php echo number_format($stats["rata_transaksi"] ?? 0, 0, ",", "."); ?>']);
            csv.push([]);
            
            // Add kasir performance
            csv.push(['PERFORMA KASIR']);
            csv.push(['Kasir', 'Jumlah Transaksi', 'Total Penjualan', 'Rata-rata']);
            <?php 
            mysqli_data_seek($kasir_stats, 0);
            while ($kasir = mysqli_fetch_assoc($kasir_stats)): 
            ?>
                csv.push([
                    '<?php echo $kasir["username"]; ?>',
                    '<?php echo $kasir["jumlah_transaksi"]; ?>',
                    'Rp <?php echo number_format($kasir["total_penjualan"], 0, ",", "."); ?>',
                    'Rp <?php echo number_format($kasir["rata_transaksi"], 0, ",", "."); ?>'
                ]);
            <?php endwhile; ?>
            
            csv.push([]);
            
            // Add recent transactions
            csv.push(['TRANSAKSI TERBARU']);
            csv.push(['ID', 'Tanggal', 'Kasir', 'Total']);
            <?php 
            mysqli_data_seek($transaksi_terbaru, 0);
            while ($trans = mysqli_fetch_assoc($transaksi_terbaru)): 
            ?>
                csv.push([
                    '#<?php echo $trans["id_transaksi"]; ?>',
                    '<?php echo date("d/m/Y H:i", strtotime($trans["tanggal"])); ?>',
                    '<?php echo $trans["kasir"]; ?>',
                    'Rp <?php echo number_format($trans["total_harga"], 0, ",", "."); ?>'
                ]);
            <?php endwhile; ?>
            
            // Convert to CSV string
            const csvString = csv.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
            const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            
            // Create download link
            const link = document.createElement('a');
            link.href = url;
            link.download = `Laporan_Penjualan_<?php echo date('Ymd_His'); ?>.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            alert('Laporan berhasil diexport!');
        }
        
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Tren Harian Chart
            <?php
            $tren_labels = [];
            $tren_data = [];
            mysqli_data_seek($tren_data, 0);
            while ($tren = mysqli_fetch_assoc($tren_data)) {
                $tren_labels[] = date('d/m', strtotime($tren['tanggal']));
                $tren_data[] = $tren['total_pendapatan'];
            }
            ?>
            
            const trenCtx = document.getElementById('trenChart').getContext('2d');
            new Chart(trenCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($tren_labels); ?>,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: <?php echo json_encode($tren_data); ?>,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            }
                        }
                    }
                }
            });
            
            // Top Products Chart
            <?php
            $product_labels = [];
            $product_data = [];
            mysqli_data_seek($top_produk, 0);
            while ($product = mysqli_fetch_assoc($top_produk)) {
                $product_labels[] = $product['nama_produk'];
                $product_data[] = $product['total_terjual'];
            }
            ?>
            
            const productsCtx = document.getElementById('topProductsChart').getContext('2d');
            new Chart(productsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($product_labels); ?>,
                    datasets: [{
                        label: 'Jumlah Terjual',
                        data: <?php echo json_encode($product_data); ?>,
                        backgroundColor: [
                            '#28a745',
                            '#007bff',
                            '#ffc107',
                            '#dc3545',
                            '#6f42c1'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
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
        });
    </script>
</body>
</html>