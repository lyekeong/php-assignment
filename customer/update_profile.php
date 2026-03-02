<?php
session_start();
require "../config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$errors = [];
$username = trim($_POST['username'] ?? "");
$phone = trim($_POST['phone'] ?? "");

/* ===== USERNAME VALIDATION ===== */
if ($username === "") {
    $errors['username'] = "Username is required.";
}
elseif (strlen($username) < 3 || strlen($username) > 20 ||
    !preg_match("/^[A-Za-z0-9_]+$/", $username)) {
    $errors['username'] = "Username must be 3–20 letters, numbers or underscore.";
}
else {
    // check duplicate except self
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=? AND user_id!=?");
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors['username'] = "Username already taken.";
    }
}

/* ===== PHONE VALIDATION ===== */
if (!preg_match("/^[0-9]{10,11}$/", $phone)) {
    $errors['phone'] = "Phone must be 10–11 digits.";
}

/* ===== IF ERROR ===== */
if (!empty($errors)) {
    $_SESSION['profile_errors'] = $errors;
    header("Location: profile.php");
    exit();
}

/* ===== UPDATE ===== */
$stmt = $conn->prepare("UPDATE users SET username=?, phone=? WHERE user_id=?");
$stmt->bind_param("ssi", $username, $phone, $user_id);

if (!$stmt->execute()) {
    die("Update error: " . $stmt->error);
}

$_SESSION['username'] = $username;

header("Location: profile.php");
exit();