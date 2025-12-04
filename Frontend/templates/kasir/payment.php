<?php include '../header.php'; ?>
<?php include '../sidebar-cashier.php'; ?>

<main class="payment-page">
  <h1>Pembayaran</h1>

  <p>Total Tagihan: <span id="payment-total">$0</span></p>

  <form id="payment-form">
    <label>Jumlah Uang Diterima</label>
    <input type="number" id="amount-received" required>

    <label>Metode Pembayaran</label>
    <select id="payment-method">
      <option value="cash">Tunai</option>
      <option value="card">Kartu</option>
      <option value="qr">QR</option>
    </select>

    <button type="submit">Selesaikan Pembayaran</button>
    <a href="sales.php">Batal</a>
  </form>
</main>

<?php include '../footer.php'; ?>
