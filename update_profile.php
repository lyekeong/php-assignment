<?php
require "config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$name = trim($_POST['full_name']);
$phone = trim($_POST['phone']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE users SET full_name=?, phone=? WHERE user_id=?");
$stmt->bind_param("ssi", $name, $phone, $user_id);
$stmt->execute();

$_SESSION['full_name'] = $name;

header("Location: profile.php");
exit();