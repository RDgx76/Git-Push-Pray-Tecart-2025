<?php include '../header.php'; ?>
<?php include '../sidebar-cashier.php'; ?>

<main class="personal-history-page">
  <h1>Riwayat Transaksi Saya</h1>

  <label>Pilih Tanggal</label>
  <input type="date" id="start-date">
  <input type="date" id="end-date">
  <button id="btnLoadHistory">Tampilkan</button>

  <table>
    <thead>
      <tr><th>ID Transaksi</th><th>Tanggal</th><th>Total</th><th>Aksi</th></tr>
    </thead>
    <tbody id="history-list">
      <tr><td colspan="4">Tidak ada data</td></tr>
    </tbody>
  </table>
</main>

<?php include '../footer.php'; ?>
