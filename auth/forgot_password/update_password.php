<?php
require "../../config/db.php";

$token = $_POST['token'] ?? "";
$new = $_POST['new_password'] ?? "";
$confirm = $_POST['confirm_password'] ?? "";

$errors = [];

if ($token === "") {
    $_SESSION['reset_error'] = "Invalid reset request.";
    header("Location: forgot_password.php");
    exit();
}

/* find user by token */
$stmt = $db->prepare("
    SELECT user_id, password_hash
    FROM users
    WHERE reset_token = :token
      AND reset_token_expiry > NOW()
    LIMIT 1
");
$stmt->execute([
    ':token' => $token
]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['reset_error'] = "This reset link is invalid or expired.";
    header("Location: forgot_password.php");
    exit();
}

/* new password validation */
$newPasswordErrors = [];

if (strlen($new) < 8) {
    $newPasswordErrors[] = "At least 8 characters.";
}

if (!preg_match("/[A-Z]/", $new)) {
    $newPasswordErrors[] = "One uppercase letter.";
}

if (!preg_match("/[a-z]/", $new)) {
    $newPasswordErrors[] = "One lowercase letter.";
}

if (!preg_match("/[0-9]/", $new)) {
    $newPasswordErrors[] = "One number.";
}

if (preg_match('/\s/', $new)) {
    $newPasswordErrors[] = "No spaces allowed.";
}

if (password_verify($new, $user['password_hash'])) {
    $newPasswordErrors[] = "New password cannot be the same as current password.";
}

if (!empty($newPasswordErrors)) {
    $errors['new_password'] = $newPasswordErrors;
}

if ($new !== $confirm) {
    $errors['confirm_password'] = "Passwords do not match.";
}

if (!empty($errors)) {
    $_SESSION['password_errors'] = $errors;
    header("Location: reset_form.php?token=" . urlencode($token));
    exit();
}

/* update password */
$new_hash = password_hash($new, PASSWORD_DEFAULT);

$updateStmt = $db->prepare("
    UPDATE users
    SET password_hash = :password_hash,
        reset_token = NULL,
        reset_token_expiry = NULL
    WHERE user_id = :user_id
");
$updateStmt->execute([
    ':password_hash' => $new_hash,
    ':user_id' => $user['user_id']
]);

$_SESSION['password_success'] = "Password updated successfully.";
header("Location: ../login/login.php");
exit();