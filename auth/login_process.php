<?php
session_start();
require "../config/db.php";

$login = trim($_POST['login'] ?? "");
$password = $_POST['password'] ?? "";

$_SESSION['old_login'] = $login;

$max_attempts = 5;
$lock_minutes = 5;

if ($login === "" || $password === "") {
    $_SESSION['login_error'] = "Invalid username/email or password.";
    header("Location: login.php");
    exit();
}

$sql = "SELECT u.user_id, u.username, u.email, u.password_hash, 
               u.failed_login_attempts, u.locked_until, r.role_name
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

/* account not found */
if (!$row) {
    $_SESSION['login_error'] = "Invalid username/email or password.";
    header("Location: login.php");
    exit();
}

/* locked check */
if (!empty($row['locked_until']) && strtotime($row['locked_until']) > time()) {
    $_SESSION['login_error'] = "Too many failed attempts. Please try again later.";
    header("Location: login.php");
    exit();
}

/* verify password */
if (!password_verify($password, $row['password_hash'])) {
    $failed_attempts = (int)$row['failed_login_attempts'] + 1;

    if ($failed_attempts >= $max_attempts) {
        $locked_until = date("Y-m-d H:i:s", strtotime("+{$lock_minutes} minutes"));

        $update = $conn->prepare("UPDATE users 
                                  SET failed_login_attempts = ?, locked_until = ?
                                  WHERE user_id = ?");
        $update->bind_param("isi", $failed_attempts, $locked_until, $row['user_id']);
        $update->execute();

        $_SESSION['login_error'] = "Too many failed attempts. Please try again later.";
    } else {
        $update = $conn->prepare("UPDATE users 
                                  SET failed_login_attempts = ?, locked_until = NULL
                                  WHERE user_id = ?");
        $update->bind_param("ii", $failed_attempts, $row['user_id']);
        $update->execute();

        $_SESSION['login_error'] = "Invalid username/email or password.";
    }

    header("Location: login.php");
    exit();
}

/* success */
$reset = $conn->prepare("UPDATE users 
                         SET failed_login_attempts = 0, locked_until = NULL
                         WHERE user_id = ?");
$reset->bind_param("i", $row['user_id']);
$reset->execute();

session_regenerate_id(true);

$_SESSION['user_id'] = $row['user_id'];
$_SESSION['role'] = $row['role_name'];
$_SESSION['username'] = $row['username'];

unset($_SESSION['old_login']);

header("Location: ../index.php");
exit();