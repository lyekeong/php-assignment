<?php
require "../../config/db.php";
require "../../lib/SimpleImage.php";

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$user_id = $_SESSION['user_id'];

if (!isset($_FILES['photo'])) {
    exit("No file");
}

$file = $_FILES['photo'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    exit("Upload error");
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
    exit("Invalid type");
}

if ($file['size'] > 1024 * 1024) {
    exit("Max 1MB");
}

/* create uploads folder */
$uploadDir = "../../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$filename = "profile_" . $user_id . "_" . time() . "." . $ext;
$path = $uploadDir . $filename;
$dbPath = "/uploads/" . $filename;

/* 🔥 image processing */
try {
    $image = new SimpleImage($file['tmp_name']);
    $image->autoOrient();
    $image->thumbnail(300, 300);
    $image->toFile($path);
} catch (Exception $e) {
    exit("Image error");
}

/* delete old */
$stmt = $db->prepare("SELECT profile_photo FROM users WHERE user_id = :id");
$stmt->execute([':id' => $user_id]);
$old = $stmt->fetch();

if (!empty($old['profile_photo']) &&
    $old['profile_photo'] !== '/customer/images/default_profile_picture.jpg') {

    $oldPath = "../../" . ltrim($old['profile_photo'], '/');
    if (file_exists($oldPath)) unlink($oldPath);
}

/* update db */
$stmt = $db->prepare("
    UPDATE users
    SET profile_photo = :photo
    WHERE user_id = :id
");
$stmt->execute([
    ':photo' => $dbPath,
    ':id' => $user_id
]);

echo "success";