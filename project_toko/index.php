<?php
session_start();
require_once 'db.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // DEBUG: Tampilkan password dan hash
    echo "<!-- DEBUG: Username: $username -->";
    echo "<!-- DEBUG: Password input: $password -->";
    echo "<!-- DEBUG: MD5 in PHP: " . md5($password) . " -->";
    
    $sql = "SELECT * FROM user WHERE username = ? AND password = MD5(?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // DEBUG: Cek hasil
    $num_rows = mysqli_num_rows($result);
    echo "<!-- DEBUG: Rows found: $num_rows -->";
    
    if ($num_rows == 1) {
        $user = mysqli_fetch_assoc($result);
        echo "<!-- DEBUG: Login SUCCESS for user ID: " . $user['id_user'] . " -->";
        
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: dashboard/index.php");
        exit();
    } else {
        // Coba debug lebih detail
        echo "<!-- DEBUG: Login FAILED -->";
        
        // Cek apakah username ada
        $sql_check = "SELECT * FROM user WHERE username = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if ($row = mysqli_fetch_assoc($result_check)) {
            echo "<!-- DEBUG: Username EXISTS in DB -->";
            echo "<!-- DEBUG: DB Password hash: " . $row['password'] . " -->";
            echo "<!-- DEBUG: MD5 of input: " . md5($password) . " -->";
            echo "<!-- DEBUG: Match? " . (md5($password) == $row['password'] ? 'YES' : 'NO') . " -->";
        } else {
            echo "<!-- DEBUG: Username NOT FOUND in DB -->";
        }
        
        $error = "Username atau password salah!";
    }
}?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Toko Sederhana</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.8s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #c33;
        }
        
        .demo-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 25px;
            font-size: 13px;
            color: #666;
            text-align: center;
        }
        
        .demo-info strong {
            color: #667eea;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo h1 {
            color: #667eea;
            font-size: 32px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🏪 TokoKu</h1>
            <p>Sistem Penjualan Sederhana</p>
        </div>
        
        <div class="login-header">
            <h2>Masuk ke Sistem</h2>
            <p>Silakan login dengan akun Anda</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">👤 Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-control" 
                       placeholder="Masukkan username"
                       required 
                       autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control" 
                       placeholder="Masukkan password"
                       required>
            </div>
            
            <button type="submit" name="login" class="btn-login">
                🚀 Masuk ke Dashboard
            </button>
        </form>
        
        <div class="demo-info">
            <p><strong>Akun Demo:</strong></p>
            <p>Admin: username: <strong>admin</strong> | password: <strong>admin123</strong></p>
            <p>Kasir: username: <strong>kasir</strong> | password: <strong>kasir123</strong></p>
        </div>
    </div>
    
    <script src="assets/script.js"></script>
    <script>
        // Fokus ke input username saat halaman dimuat
        document.getElementById('username').focus();
        
        // Toggle show/hide password
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function() {
                const btn = this.querySelector('[type="submit"]');
                btn.innerHTML = '⏳ Sedang memproses...';
                btn.disabled = true;
            });
        });
    </script>
</body>
</html>