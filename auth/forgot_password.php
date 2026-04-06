<?php
session_start();
include "../partials/header.php";

$error = $_SESSION['forgot_error'] ?? "";
$success = $_SESSION['forgot_success'] ?? "";
unset($_SESSION['forgot_error'], $_SESSION['forgot_success']);
?>

<div class="auth-container">
  <div class="auth-card">
    <h2>Forgot Password</h2>

    <?php if($error): ?>
      <div class="error-box"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
      <div class="success-box"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form action="reset_password.php" method="POST">
      <div class="form-group">
        <input type="email" name="email" placeholder="Enter your registered email" required>
      </div>

      <button type="submit" class="btn">Continue</button>
    </form>

  </div>
</div>

<?php include "../partials/footer.php"; ?>