<?php include '../header.php'; ?>
<?php include '../sidebar-admin.php'; ?>

<main class="dashboard-admin">
  <h1>Dashboard Admin</h1>

  <section class="summary-cards">
    <div class="card">Total Penjualan Hari Ini: <span>$0</span></div>
    <div class="card">Jumlah Transaksi: <span>0</span></div>
    <div class="card">Stok Kritis: <span>0 Produk</span></div>
  </section>

  <section class="charts">
    <canvas id="salesChart" width="600" height="300"></canvas>
    <!-- chart-native.js nanti yang menggambar -->
  </section>

  <section class="top-products">
    <h2>Produk Terlaris</h2>
    <table>
      <thead>
        <tr><th>Nama Produk</th><th>Jumlah Terjual</th></tr>
      </thead>
      <tbody>
        <tr><td>-</td><td>0</td></tr>
      </tbody>
    </table>
  </section>
</main>

<?php include '../footer.php'; ?>
