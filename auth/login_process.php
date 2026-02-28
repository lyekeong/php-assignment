<?php
require "../config/db.php";

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT u.user_id, u.password_hash, r.role_name
        FROM users u
        JOIN user_roles ur ON u.user_id = ur.user_id
        JOIN roles r ON ur.role_id = r.role_id
        WHERE u.email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()) {

    if(password_verify($password, $row['password_hash'])) {

        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role_name'];

        if($row['role_name'] == "customer"){
            header("Location: ../customer/index.php");
        } elseif($row['role_name'] == "admin"){
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../staff/index.php");
        }
        exit();

    } else {
        echo "Wrong password";
    }

} else {
    echo "User not found";
}
?>