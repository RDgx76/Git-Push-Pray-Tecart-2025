<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

// Only admin
if (!check_role('admin')) {
    redirect_with_message('../dashboard/index.php', 'Akses ditolak! Hanya admin yang boleh mengakses user management.', 'error');
}

// Fetch users
$sql = "SELECT id_user, username, role, created_at FROM user ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manajemen Pegawai - Users</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div style="max-width:900px;margin:30px auto;padding:20px;background:#fff;border-radius:10px;">
        <h2>👥 Manajemen Pegawai</h2>
        <p><a href="../dashboard/index.php">← Kembali ke Dashboard</a> | <a href="tambah.php">➕ Tambah Pegawai</a></p>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;margin-top:10px;">
            <thead>
                <tr><th>ID</th><th>Username</th><th>Role</th><th>Created At</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['id_user']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <!-- Edit & Nonaktifkan (to implement) -->
                            <a href="edit.php?id=<?php echo $row['id_user']; ?>">Edit</a>
                            |
                            <a href="delete.php?id=<?php echo $row['id_user']; ?>" onclick="return confirm('Hapus user?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
