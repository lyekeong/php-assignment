<?php
session_start();
require "../config/db.php";

$token = $_POST['token'] ?? "";
$new = $_POST['new_password'] ?? "";
$confirm = $_POST['confirm_password'] ?? "";

$errors = [];

if ($token === "") {
    $_SESSION['reset_error'] = "Invalid reset request.";
    header("Location: forgot_password.php");
    exit();
}

/* ===== FIND USER BY TOKEN ===== */
$stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE reset_token=? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['reset_error'] = "This reset link is invalid or expired.";
    header("Location: forgot_password.php");
    exit();
}

/* ===== NEW PASSWORD VALIDATION ===== */
$newPasswordErrors = [];

if (strlen($new) < 8) {
    $newPasswordErrors[] = "at least 8 characters";
}

if (!preg_match("/[A-Z]/", $new)) {
    $newPasswordErrors[] = "one uppercase letter";
}

if (!preg_match("/[a-z]/", $new)) {
    $newPasswordErrors[] = "one lowercase letter";
}

if (!preg_match("/[0-9]/", $new)) {
    $newPasswordErrors[] = "one number";
}

if (preg_match('/\s/', $new)) {
    $newPasswordErrors[] = "no spaces allowed";
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

/* ===== UPDATE PASSWORD ===== */
$new_hash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password_hash=?, reset_token=NULL, reset_token_expiry=NULL WHERE user_id=?");
$stmt->bind_param("si", $new_hash, $user['user_id']);
$stmt->execute();

$_SESSION['password_success'] = "Password updated successfully.";
header("Location: login.php");
exit();