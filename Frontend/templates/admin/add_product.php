<?php include '../header.php'; ?>
<?php include '../sidebar-admin.php'; ?>

<main class="add-product-page">
  <h1>Tambah Produk Baru</h1>
  <form action="../../Backend/controllers/ProductController.php" method="POST" enctype="multipart/form-data">
    <label>Nama Produk</label><input type="text" name="name" required>
    <label>Deskripsi</label><textarea name="description"></textarea>
    <label>Kategori</label><input type="text" name="category">
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
