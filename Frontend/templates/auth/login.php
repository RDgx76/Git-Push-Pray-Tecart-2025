<?php include '../header.php'; ?>

<main class="login-page">
  <div class="login-box">
    <h1>ByteMart POS</h1>
    <form action="../../Backend/controllers/AuthController.php" method="POST">
      <label for="username">Username / Email</label>
      <input type="text" id="username" name="username" required>
      
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Login</button>
    </form>
    <a href="forgot_password.php">Lupa Password?</a>
  </div>
</main>

<?php include '../footer.php'; ?>
