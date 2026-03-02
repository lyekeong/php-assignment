<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

$user_id = $_SESSION['reset_user_id'];
$new = $_POST['new_password'] ?? "";
$confirm = $_POST['confirm_password'] ?? "";

$errors = [];

/* password validation */
if (strlen($new) < 8 ||
    !preg_match("/[A-Z]/", $new) ||
    !preg_match("/[a-z]/", $new) ||
    !preg_match("/[0-9]/", $new) ||
    !preg_match("/[\W]/", $new)) {

    $errors[] = "Password must be 8+ chars, include upper, lower, number, symbol.";
}

if ($new !== $confirm) {
    $errors[] = "Passwords do not match.";
}

if (!empty($errors)) {
    $_SESSION['forgot_error'] = implode("<br>", $errors);
    header("Location: forgot_password.php");
    exit();
}

$new_hash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
$stmt->bind_param("si", $new_hash, $user_id);
$stmt->execute();

unset($_SESSION['reset_user_id']);

header("Location: login.php");
exit();