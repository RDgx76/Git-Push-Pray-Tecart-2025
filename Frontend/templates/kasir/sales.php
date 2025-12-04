<?php include '../header.php'; ?>
<?php include '../sidebar-cashier.php'; ?>

<main class="pos-page">
  <h1>Sistem Penjualan (Kasir)</h1>

  <section class="product-search">
    <input type="text" id="search-product" placeholder="Cari produk...">
  </section>

  <section class="product-list" id="product-list">
    <!-- Daftar produk muncul di sini -->
  </section>

  <section class="cart">
    <h2>Keranjang</h2>
    <table id="cart-items">
      <thead>
        <tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <tr><td colspan="4">Keranjang kosong</td></tr>
      </tbody>
    </table>

    <div class="summary">
      <p>Subtotal: <span id="subtotal">$0</span></p>
      <p>Total: <span id="total">$0</span></p>
      <button id="btnPay">Bayar</button>
      <button id="btnCancel">Batalkan Transaksi</button>
    </div>
  </section>
</main>

<?php include '../footer.php'; ?>
