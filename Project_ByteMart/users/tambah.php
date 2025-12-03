<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

// Only admin
if (!check_role('admin')) {
    redirect_with_message('../dashboard/index.php', 'Akses ditolak! Hanya admin yang boleh menambah pegawai.', 'error');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($username == '' || $password == '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Hash password with password_hash
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO user (username, password, role) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sss', $username, $hash, $role);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'User berhasil dibuat.';
        } else {
            $error = 'Gagal membuat user: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tambah Pegawai</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div style="max-width:600px;margin:30px auto;padding:20px;background:#fff;border-radius:10px;">
        <h2>➕ Tambah Pegawai</h2>
        <p><a href="list.php">← Kembali ke List Pegawai</a></p>
        <?php if ($error): ?>
            <div style="background:#fee;padding:10px;border-radius:6px;color:#900;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div style="background:#e6ffed;padding:10px;border-radius:6px;color:#080;"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div style="margin-bottom:10px;">
                <label>Username</label><br>
                <input type="text" name="username" required>
            </div>
            <div style="margin-bottom:10px;">
                <label>Password</label><br>
                <input type="password" name="password" required>
            </div>
            <div style="margin-bottom:10px;">
                <label>Role</label><br>
                <select name="role">
                    <option value="kasir">Kasir</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit">Simpan</button>
        </form>
    </div>
</body>
</html>
