<?php include '../header.php'; ?>

<main class="receipt-page">
  <h1>Nota Transaksi</h1>

  <section class="receipt-detail">
    <p>ID Transaksi: #12345</p>
    <p>Tanggal: 2025-12-04</p>
    <table>
      <thead>
        <tr><th>Produk</th><th>Qty</th><th>Harga</th></tr>
      </thead>
      <tbody>
        <tr><td>Contoh Produk</td><td>1</td><td>$100</td></tr>
      </tbody>
    </table>
    <p>Total: $100</p>
  </section>

  <button onclick="window.print()">Cetak / Print</button>
  <a href="sales.php">Kembali</a>
</main>

<?php include '../footer.php'; ?>
