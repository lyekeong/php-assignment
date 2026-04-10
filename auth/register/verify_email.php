<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

require "../../config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    $_SESSION['login_error'] = "Invalid verification link.";
    header("Location: ../login/login.php");
    exit();
}

$stmt = $db->prepare("
    SELECT user_id, email_verified_at
    FROM users
    WHERE email_verify_token = :token
    LIMIT 1
");
$stmt->execute([
    ':token' => $token
]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['login_error'] = "Invalid or expired verification link.";
    header("Location: ../login/login.php");
    exit();
}

if (!empty($user['email_verified_at'])) {
    $_SESSION['register_success'] = "Your email is already verified. Please login.";
    header("Location: ../login/login.php");
    exit();
}

$update = $db->prepare("
    UPDATE users
    SET email_verified_at = NOW(),
        email_verify_token = NULL
    WHERE user_id = :user_id
");
$update->execute([
    ':user_id' => $user['user_id']
]);

$_SESSION['register_success'] = "Email verified successfully. You can now login.";
header("Location: ../login/login.php");
exit();