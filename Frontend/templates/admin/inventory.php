<?php include '../header.php'; ?>
<?php include '../sidebar-admin.php'; ?>

<main class="inventory-page">
  <h1>Manajemen Inventaris</h1>
  <button id="btnAddProduct">Tambah Produk Baru</button>

  <table>
    <thead>
      <tr>
        <th>Nama</th><th>Kategori</th><th>Harga Jual</th><th>Stok</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody id="inventory-list">
      <tr>
        <td colspan="5">Tidak ada data</td>
      </tr>
    </tbody>
  </table>
</main>

<?php include '../footer.php'; ?>
