<?php include '../header.php'; ?>
<?php include '../sidebar-admin.php'; ?>

<main class="transaction-history-page">
  <h1>Riwayat Transaksi</h1>

  <div class="filter-section">
    <label>Dari Tanggal</label>
    <input type="date" id="start-date">

    <label>Sampai Tanggal</label>
    <input type="date" id="end-date">

    <button id="btnFilterTransactions">Terapkan</button>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID Transaksi</th>
        <th>Tanggal</th>
        <th>Kasir</th>
        <th>Total</th>
        <th>Metode Pembayaran</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody id="transactions-list">
      <tr>
        <td colspan="6">Tidak ada data</td>
      </tr>
    </tbody>
  </table>

  <button class="export-btn">Ekspor Laporan</button>
</main>

<?php include '../footer.php'; ?>
