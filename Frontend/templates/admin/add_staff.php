<?php include '../header.php'; ?>
<?php include '../sidebar-admin.php'; ?>

<main class="add-staff-page">
  <h1>Tambah Pegawai Baru</h1>

  <form action="../../Backend/controllers/StaffController.php" method="POST">

    <label>Nama Pegawai</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Nomor Telepon</label>
    <input type="text" name="phone">

    <label>Peran</label>
    <select name="role" required>
      <option value="kasir">Kasir</option>
      <option value="admin">Admin</option>
    </select>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Simpan</button>
    <a href="staff.php">Batal</a>
  </form>
</main>

<?php include '../footer.php'; ?>
