<?php
require "../../config/db.php";

$errors = [];
$old = $_POST;

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

/* ===== USERNAME ===== */
if ($username === '') {
    $errors['username'] = "Username is required.";
} elseif (
    strlen($username) < 3 ||
    strlen($username) > 20 ||
    !preg_match("/^[A-Za-z0-9_]+$/", $username)
) {
    $errors['username'] = "Username must be 3–20 letters, numbers or underscore only.";
} else {
    $stmt = $db->prepare("
        SELECT user_id
        FROM users
        WHERE username = :username
        LIMIT 1
    ");
    $stmt->execute([
        ':username' => $username
    ]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $errors['username'] = "Username already taken.";
    }
}

/* ===== EMAIL ===== */
if ($email === '') {
    $errors['email'] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format.";
} elseif (!preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/", $email)) {
    $errors['email'] = "Email must contain valid domain.";
} else {
    $stmt = $db->prepare("
        SELECT user_id
        FROM users
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute([
        ':email' => $email
    ]);
    $existingEmail = $stmt->fetch();

    if ($existingEmail) {
        $errors['email'] = "Email already registered.";
    }
}

/* ===== PHONE ===== */
if ($phone === '') {
    $errors['phone'] = "Phone number is required.";
} elseif (!preg_match("/^[0-9]{10,11}$/", $phone)) {
    $errors['phone'] = "Phone must be 10–11 digits.";
}

/* ===== PASSWORD ===== */
$missing = [];

if ($password === '') {
    $errors['password'] = "Password is required.";
} else {
    if (strlen($password) < 8) $missing[] = "at least 8 characters";
    if (!preg_match("/[A-Z]/", $password)) $missing[] = "one uppercase letter";
    if (!preg_match("/[a-z]/", $password)) $missing[] = "one lowercase letter";
    if (!preg_match("/[0-9]/", $password)) $missing[] = "one number";
    if (preg_match('/\s/', $password)) $missing[] = "no spaces allowed";

    if (!empty($missing)) {
        $errors['password'] = "Password must include: " . implode(", ", $missing) . ".";
    }
}

if ($confirm === '') {
    $errors['confirm_password'] = "Please confirm your password.";
} elseif ($password !== $confirm) {
    $errors['confirm_password'] = "Passwords do not match.";
}

/* ===== IF ERROR ===== */
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    header("Location: register.php");
    exit();
}

/* ===== INSERT USER ===== */
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("
    INSERT INTO users (username, email, phone, password_hash, profile_photo)
    VALUES (:username, :email, :phone, :password_hash, :profile_photo)
");

$stmt->execute([
    ':username' => $username,
    ':email' => $email,
    ':phone' => $phone,
    ':password_hash' => $hashed,
    ':profile_photo' => '/customer/images/default_profile_picture.jpg'
]);

$user_id = $db->lastInsertId();

/* ===== ASSIGN CUSTOMER ROLE ===== */
$stmt2 = $db->prepare("
    INSERT INTO user_roles (user_id, role_id)
    VALUES (:user_id, 3)
");
$stmt2->execute([
    ':user_id' => $user_id
]);

header("Location: ../login/login.php");
exit();