<?php
require "../config/db.php";

$login = trim($_POST['email'] ?? ""); // input name 保持 email
$password = $_POST['password'] ?? "";

if ($login === "" || $password === "") {
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

if ($row = $result->fetch_assoc()) {

    if (password_verify($password, $row['password_hash'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role_name'];
        $_SESSION['username'] = $row['username'];

        header("Location: ../index.php");
        exit();
    }
}

header("Location: login.php");
exit();