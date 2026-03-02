<?php
include "../partials/header.php";

$error = $_SESSION['login_error'] ?? "";
$old_login = $_SESSION['old_login'] ?? "";

unset($_SESSION['login_error']);
unset($_SESSION['old_login']);
?>

<div class="auth-container">
  <div class="auth-card fade-in">
    <h2>🌙 LunaSteps Login</h2>

    <?php if($error): ?>
      <div class="error-box"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="login_process.php" method="POST">
      <div class="form-group">
        <input
          type="text"
          name="login"
          placeholder="Email or Username"
          value="<?= htmlspecialchars($old_login) ?>"
          required
        >
      </div>

      <div class="form-group">
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <button class="btn pulse">Login</button>
    </form>
    
    <p class="switch-text">
      <a href="forgot_password.php">Forgot Password?</a>
    </p>

    <p class="switch-text">
      Don't have account?
      <a href="register.php">Register</a>
    </p>
  </div>
</div>

<?php include "../partials/footer.php"; ?>