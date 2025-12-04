<?php include '../header.php'; ?>

<main class="forgot-password-page">
  <div class="forgot-box">
    <h2>Lupa Password</h2>
    <form action="../../Backend/controllers/AuthController.php" method="POST">
      <label for="email">Email Terdaftar</label>
      <input type="email" id="email" name="email" required>
      <button type="submit">Kirim Link Reset</button>
    </form>
    <a href="login.php">Kembali ke Login</a>
  </div>
</main>

<?php include '../footer.php'; ?>
