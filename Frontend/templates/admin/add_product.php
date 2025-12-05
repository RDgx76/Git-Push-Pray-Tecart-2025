<?php include '../header.php'; ?>
<?php include '../sidebar-admin.php'; ?>

<main class="add-product-page">
  <h1>Tambah Produk Baru</h1>
  <!-- FIX: Mengarah ke Router index.php dengan controller product -->
  <form action="../../Backend/index.php?controller=product&action=create" method="POST" enctype="multipart/form-data">
    <label>Nama Produk</label><input type="text" name="name" required>
    <label>Deskripsi</label><textarea name="description"></textarea>
    <label>Kategori</label><input type="text" name="category">
    <!-- FIX: Input purchase_price & sale_price agar sesuai Controller -->
    <label>Harga Beli</label><input type="number" name="purchase_price" required>
    <label>Harga Jual</label><input type="number" name="sale_price" required>
    <label>Stok</label><input type="number" name="stock" required>
    <label>Barcode</label><input type="text" name="barcode">
    <label>Gambar</label><input type="file" name="image">

    <button type="submit">Simpan</button>
    <a href="inventory.php">Batal</a>
  </form>
</main>

<?php include '../footer.php'; ?>