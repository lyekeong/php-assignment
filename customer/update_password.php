<?php
session_start();
require "../config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];

$current = $_POST['current_password'] ?? "";
$new = $_POST['new_password'] ?? "";
$confirm = $_POST['confirm_password'] ?? "";

/* ===== GET CURRENT HASH ===== */
$stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!password_verify($current, $user['password_hash'])) {
    $errors['current_password'] = "Current password is incorrect.";
}

/* ===== NEW PASSWORD VALIDATION ===== */
if (strlen($new) < 8 ||
    !preg_match("/[A-Z]/", $new) ||
    !preg_match("/[a-z]/", $new) ||
    !preg_match("/[0-9]/", $new) ||
    !preg_match("/[\W]/", $new)) {

    $errors['new_password'] = "Password must be 8+ chars, include upper, lower, number, symbol.";
}

if (password_verify($new, $user['password_hash'])) {
    $errors['new_password'] = "New password cannot be the same as current password.";
}

if ($new !== $confirm) {
    $errors['confirm_password'] = "Passwords do not match.";
}

if (!empty($errors)) {
    $_SESSION['password_errors'] = $errors;
    header("Location: change_password.php");
    exit();
}

/* ===== UPDATE PASSWORD ===== */
$new_hash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
$stmt->bind_param("si", $new_hash, $user_id);
$stmt->execute();

$_SESSION['password_success'] = "Password updated successfully.";
header("Location: change_password.php");
exit();