<?php include '../header.php'; ?>
<?php include '../sidebar-admin.php'; ?>

<main class="staff-page">
  <h1>Manajemen Pegawai</h1>
  <button id="btnAddStaff">Tambah Pegawai Baru</button>

  <table>
    <thead>
      <tr>
        <th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody id="staff-list">
      <tr><td colspan="5">Tidak ada data</td></tr>
    </tbody>
  </table>
</main>

<?php include '../footer.php'; ?>
