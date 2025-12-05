<?php include '../header.php'; ?>
<?php include '../sidebar-admin.php'; ?>

<main class="settings-page">
  <h1>Pengaturan Toko</h1>

  <!-- FIX: Mengarah ke Router index.php dengan controller store -->
  <form action="../../Backend/index.php?controller=store&action=update" method="POST">
    <label>Nama Toko</label><input type="text" name="store_name">
    <label>Alamat Toko</label><input type="text" name="address">
    <label>Nomor Telepon</label><input type="text" name="phone">
    <label>Persentase Pajak (%)</label><input type="number" name="tax">
    <label>Header Nota</label><textarea name="receipt_header"></textarea>

    <button type="submit">Simpan Perubahan</button>
    <a href="dashboard.php">Batal</a>
  </form>
</main>

<?php include '../footer.php'; ?>