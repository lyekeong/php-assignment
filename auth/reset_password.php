<?php
require "../config/db.php";

$email = trim($_POST['email'] ?? "");

if ($email === "") {
    $_SESSION['forgot_error'] = "Email is required.";
    header("Location: forgot_password.php");
    exit();
}

/* ===== EMAIL FORMAT VALIDATION ===== */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['forgot_error'] = "Invalid email format.";
    header("Location: forgot_password.php");
    exit();
}

/* ===== CHECK EXISTENCE ===== */
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['forgot_error'] = "Email not found.";
    header("Location: forgot_password.php");
    exit();
}

$user = $result->fetch_assoc();
$_SESSION['reset_user_id'] = $user['user_id'];

include "../partials/header.php";
?>

<div class="auth-container">
  <div class="auth-card">
    <h2>Reset Password</h2>

    <form action="reset_password_process.php" method="POST">

      <div class="form-group">
        <input type="password" name="new_password" placeholder="New Password" required>
      </div>

      <div class="form-group">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      </div>

      <button class="btn">Reset Password</button>
    </form>

  </div>
</div>

<?php include "../partials/footer.php"; ?>