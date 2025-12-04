<?php include '../header.php'; ?>
<?php include '../sidebar-admin.php'; ?>

<main class="performance-page">
  <h1>Kinerja Pegawai</h1>

  <label>Pilih Rentang Tanggal</label>
  <input type="date" id="start-date">
  <input type="date" id="end-date">
  <button id="btnLoadPerformance">Tampilkan</button>

  <table>
    <thead>
      <tr>
        <th>Nama Pegawai</th><th>Jumlah Transaksi</th><th>Total Penjualan</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody id="performance-list">
      <tr><td colspan="4">Tidak ada data</td></tr>
    </tbody>
  </table>
</main>

<?php include '../footer.php'; ?>
