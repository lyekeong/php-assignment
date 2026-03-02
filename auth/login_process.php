<?php
require "../config/db.php";

$login = trim($_POST['login'] ?? "");
$password = $_POST['password'] ?? "";

$_SESSION['old_login'] = $login; // keep last input

if ($login === "" || $password === "") {
    $_SESSION['login_error'] = "Please enter login and password.";
    header("Location: login.php");
    exit();
}

$sql = "SELECT u.user_id, u.username, u.password_hash, r.role_name
        FROM users u
        JOIN user_roles ur ON u.user_id = ur.user_id
        JOIN roles r ON ur.role_id = r.role_id
        WHERE u.email = ? OR u.username = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $login, $login);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    $_SESSION['login_error'] = "Account not found.";
    header("Location: login.php");
    exit();
}

if (!password_verify($password, $row['password_hash'])) {
    $_SESSION['login_error'] = "Wrong password.";
    header("Location: login.php");
    exit();
}

// success
session_regenerate_id(true);

$_SESSION['user_id'] = $row['user_id'];
$_SESSION['role'] = $row['role_name'];
$_SESSION['username'] = $row['username'];

unset($_SESSION['old_login']);

header("Location: ../index.php");  // everyone goes homepage
exit();