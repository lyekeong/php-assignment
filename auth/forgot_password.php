<?php
session_start();
include "../partials/header.php";

$error = $_SESSION['forgot_error'] ?? "";
unset($_SESSION['forgot_error']);
?>

<div class="auth-container">
  <div class="auth-card">
    <h2>Forgot Password</h2>

    <?php if($error): ?>
      <div class="error-box"><?= $error ?></div>
    <?php endif; ?>

    <form action="reset_password.php" method="POST">
      <div class="form-group">
        <input type="email" name="email" placeholder="Enter your registered email" required>
      </div>

      <button class="btn">Continue</button>
    </form>

  </div>
</div>

<?php include "../partials/footer.php"; ?>