<?php
// TAMBAHKAN INI UNTUK DEBUG:
echo "<pre>";
echo "SESSION DATA:\n";
print_r($_SESSION);
echo "</pre>";

// Cek session ID
echo "Session ID: " . session_id() . "<br>";
echo "Session Name: " . session_name() . "<br>";

// Jika session kosong, berarti ada masalah
if (empty($_SESSION['user_id'])) {
    echo "ERROR: Session is empty! Login failed.<br>";
    echo "Try: <a href='../index.php'>Login again</a>";
    exit();
}

require_once '../auth.php';
require_once '../db.php';

// DEBUG: Tampilkan informasi session untuk troubleshooting
echo "<!-- DEBUG SESSION START -->";
echo "<!-- Session ID: " . session_id() . " -->";
echo "<!-- User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . " -->";
echo "<!-- Username: " . ($_SESSION['username'] ?? 'NOT SET') . " -->";
echo "<!-- Role: " . ($_SESSION['role'] ?? 'NOT SET') . " -->";
echo "<!-- DEBUG SESSION END -->";

// Ambil pesan flash jika ada
$flash_msg = get_flash_message();

// Ambil statistik untuk dashboard
$total_produk = 0;
$total_transaksi = 0;
$total_pendapatan = 0;
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Hitung total produk
$sql_produk = "SELECT COUNT(*) as total FROM produk";
$result = mysqli_query($conn, $sql_produk);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_produk = $row['total'];
}

// Hitung total transaksi (harian)
$today = date('Y-m-d');

if ($user_role == 'admin') {
    // Admin lihat semua transaksi
    $sql_transaksi = "SELECT COUNT(*) as total, SUM(total_harga) as pendapatan 
                      FROM transaksi 
                      WHERE DATE(tanggal) = '$today'";
} else {
    // Kasir hanya lihat transaksi sendiri
    $sql_transaksi = "SELECT COUNT(*) as total, SUM(total_harga) as pendapatan 
                      FROM transaksi 
                      WHERE DATE(tanggal) = '$today' AND id_user = $user_id";
}

$result = mysqli_query($conn, $sql_transaksi);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_transaksi = $row['total'] ?? 0;
    $total_pendapatan = $row['pendapatan'] ? $row['pendapatan'] : 0;
}

// DEBUG: Tampilkan query dan hasil
echo "<!-- DEBUG QUERY: $sql_transaksi -->";
echo "<!-- DEBUG RESULT: total=$total_transaksi, pendapatan=$total_pendapatan -->";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Toko Sederhana</title>
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
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .welcome-message h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .welcome-message p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .user-info {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            text-align: right;
        }
        
        .user-info span {
            display: block;
        }
        
        .username {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .role {
            font-size: 14px;
            opacity: 0.9;
            background: white;
            color: #667eea;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
            font-weight: 500;
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
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            border-left: 5px solid #667eea;
        }
        
        .menu-card:hover {
            transform: translateX(10px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.2);
            border-left-color: #764ba2;
        }
        
        .menu-icon {
            font-size: 40px;
            margin-bottom: 20px;
            color: #667eea;
        }
        
        .menu-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .menu-desc {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        /* Flash Message */
        .flash-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid #28a745;
            animation: slideIn 0.5s ease;
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
        
        .flash-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .flash-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }
        
        /* Logout Button */
        .logout-section {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }
        
        .btn-logout {
            background: linear-gradient(to right, #ff6b6b, #ee5a52);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
        }
        
        /* System Info */
        .system-info {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
            text-align: center;
            border: 1px solid #eee;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .dashboard-header {
                padding: 20px;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .user-info {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <div class="welcome-message">
                    <h1>🎉 Selamat Datang!</h1>
                    <p>Anda login di Sistem Toko Sederhana</p>
                </div>
                <div class="user-info">
                    <span class="username">👤 <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
                    <span class="role">Role: <?php echo htmlspecialchars($_SESSION['role'] ?? 'Unknown'); ?></span>
                    <div style="margin-top: 10px; font-size: 12px; opacity: 0.8;">
                        Login: <?php echo date('d/m/Y H:i'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Flash Message -->
        <?php if ($flash_msg): ?>
            <div class="flash-message <?php echo $flash_msg['type'] == 'error' ? 'flash-error' : ($flash_msg['type'] == 'warning' ? 'flash-warning' : ''); ?>">
                <?php echo htmlspecialchars($flash_msg['text']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-number"><?php echo $total_produk; ?></div>
                <div class="stat-label">Total Produk</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number"><?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                <div class="stat-label">Pendapatan Hari Ini</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo $total_transaksi; ?></div>
                <div class="stat-label">Transaksi Hari Ini</div>
            </div>
        </div>
        
        <!-- Menu Grid -->
        <div class="menu-grid">
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <!-- Menu Admin -->
                <a href="../produk/list.php" class="menu-card">
                    <div class="menu-icon">📦</div>
                    <div class="menu-title">Kelola Produk</div>
                    <div class="menu-desc">
                        Tambah, edit, atau hapus produk yang dijual di toko.
                    </div>
                </a>
                
                <a href="../transaksi/list.php" class="menu-card">
                    <div class="menu-icon">📋</div>
                    <div class="menu-title">Riwayat Transaksi</div>
                    <div class="menu-desc">
                        Lihat semua transaksi yang telah dilakukan.
                    </div>
                </a>
                
                <a href="../laporan/index.php" class="menu-card">
                    <div class="menu-icon">📊</div>
                    <div class="menu-title">Laporan Penjualan</div>
                    <div class="menu-desc">
                        Analisis penjualan dan laporan keuangan.
                    </div>
                </a>
                
                <a href="../produk/tambah.php" class="menu-card">
                    <div class="menu-icon">➕</div>
                    <div class="menu-title">Tambah Produk Baru</div>
                    <div class="menu-desc">
                        Tambahkan produk baru ke dalam sistem.
                    </div>
                </a>
                
            <?php else: ?>
                <!-- Menu Kasir -->
                <a href="../transaksi/tambah.php" class="menu-card">
                    <div class="menu-icon">🛒</div>
                    <div class="menu-title">Transaksi Baru</div>
                    <div class="menu-desc">
                        Buat transaksi penjualan baru untuk customer.
                    </div>
                </a>
                
                <a href="../transaksi/list.php" class="menu-card">
                    <div class="menu-icon">📋</div>
                    <div class="menu-title">Riwayat Transaksi</div>
                    <div class="menu-desc">
                        Lihat transaksi yang telah Anda buat.
                    </div>
                </a>
                
                <a href="../produk/list.php" class="menu-card">
                    <div class="menu-icon">📦</div>
                    <div class="menu-title">Daftar Produk</div>
                    <div class="menu-desc">
                        Lihat produk yang tersedia beserta stok.
                    </div>
                </a>
                
                <a href="../dashboard/index.php" class="menu-card">
                    <div class="menu-icon">🏠</div>
                    <div class="menu-title">Dashboard</div>
                    <div class="menu-desc">
                        Kembali ke halaman utama dashboard.
                    </div>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- System Info -->
        <div class="system-info">
            <p>💻 Sistem Toko Sederhana v1.0 | PHP <?php echo phpversion(); ?> | MySQL</p>
            <p>Tanggal Server: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
        
        <!-- Logout -->
        <div class="logout-section">
            <a href="../logout.php" class="btn-logout">🚪 Logout dari Sistem</a>
        </div>
    </div>
    
    <script src="../assets/script.js"></script>
    <script>
        // Auto-hide flash message setelah 5 detik
        setTimeout(function() {
            const flashMsg = document.querySelector('.flash-message');
            if (flashMsg) {
                flashMsg.style.transition = 'opacity 0.5s';
                flashMsg.style.opacity = '0';
                setTimeout(() => flashMsg.remove(), 500);
            }
        }, 5000);
        
        // Welcome animation
        document.addEventListener('DOMContentLoaded', function() {
            const welcomeMessage = document.querySelector('.welcome-message h1');
            if (welcomeMessage) {
                welcomeMessage.style.animation = 'none';
                setTimeout(() => {
                    welcomeMessage.style.animation = 'fadeIn 1s ease';
                }, 100);
            }
        });
    </script>
</body>
</html>