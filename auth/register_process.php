<?php
require "../config/db.php";

$errors = [];
$old = $_POST;

$username = trim($_POST['username']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];

/* ===== NAME ===== */
if ($username === "") {
    $errors['username'] = "Username is required.";
}
elseif (strlen($username) < 3 || strlen($username) > 20 ||
    !preg_match("/^[A-Za-z0-9_]+$/", $username)) {
    $errors['username'] = "Username must be 3–20 letters, numbers or underscore only.";
}

/* ===== CHECK DUPLICATE USERNAME ===== */
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $errors['username'] = "Username already taken.";
}


/* ===== EMAIL ===== */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format.";
}
elseif (!preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/", $email)) {
    $errors['email'] = "Email must contain valid domain.";
}

/* duplicate email */
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $errors['email'] = "Email already registered.";
}

/* ===== PHONE ===== */
if (!preg_match("/^[0-9]{10,11}$/", $phone)) {
    $errors['phone'] = "Phone must be 10–11 digits.";
}

/* ===== PASSWORD ===== */
$missing = [];

if (strlen($password) < 8) $missing[] = "at least 8 characters";
if (!preg_match("/[A-Z]/", $password)) $missing[] = "one uppercase letter";
if (!preg_match("/[a-z]/", $password)) $missing[] = "one lowercase letter";
if (!preg_match("/[0-9]/", $password)) $missing[] = "one number";
if (!preg_match("/[\W]/", $password)) $missing[] = "one symbol";

if (!empty($missing)) {
    $errors['password'] = "Password must include: " . implode(", ", $missing) . ".";
}

if ($password !== $confirm) {
    $errors['confirm_password'] = "Passwords do not match.";
}

/* ===== IF ERROR ===== */
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    header("Location: register.php");
    exit();
}

/* INSERT */
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, email, phone, password_hash) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $phone, $hashed);
$stmt->execute();

$user_id = $stmt->insert_id;

$stmt2 = $conn->prepare("INSERT INTO user_roles (user_id,role_id) VALUES (?,3)");
$stmt2->bind_param("i",$user_id);
$stmt2->execute();

header("Location: login.php");
exit();