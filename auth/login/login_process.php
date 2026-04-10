<?php
require "../../config/db.php";

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

$sql = "
    SELECT 
        u.user_id,
        u.username,
        u.email,
        u.password_hash,
        u.failed_login_attempts,
        u.locked_until,
        u.email_verified_at,
        r.role_name
    FROM users u
    JOIN user_roles ur ON u.user_id = ur.user_id
    JOIN roles r ON ur.role_id = r.role_id
    WHERE u.email = :login OR u.username = :login
    LIMIT 1
";

$stmt = $db->prepare($sql);
$stmt->execute([
    ':login' => $login
]);
$row = $stmt->fetch();

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
if (empty($row['email_verified_at'])) {
    $_SESSION['login_error'] = "Please verify your email before login.";
    $_SESSION['old_login'] = $login;
    header("Location: login.php");
    exit();
}
/* verify password */
if (!password_verify($password, $row['password_hash'])) {
    $failed_attempts = (int)$row['failed_login_attempts'] + 1;

    if ($failed_attempts >= $max_attempts) {
        $locked_until = date("Y-m-d H:i:s", strtotime("+{$lock_minutes} minutes"));

        $update = $db->prepare("
            UPDATE users
            SET failed_login_attempts = :failed_login_attempts,
                locked_until = :locked_until
            WHERE user_id = :user_id
        ");
        $update->execute([
            ':failed_login_attempts' => $failed_attempts,
            ':locked_until' => $locked_until,
            ':user_id' => $row['user_id']
        ]);

        $_SESSION['login_error'] = "Too many failed attempts. Please try again later.";
    } else {
        $update = $db->prepare("
            UPDATE users
            SET failed_login_attempts = :failed_login_attempts,
                locked_until = NULL
            WHERE user_id = :user_id
        ");
        $update->execute([
            ':failed_login_attempts' => $failed_attempts,
            ':user_id' => $row['user_id']
        ]);

        $_SESSION['login_error'] = "Invalid username/email or password.";
    }

    header("Location: login.php");
    exit();
}

/* success: reset attempts */
$reset = $db->prepare("
    UPDATE users
    SET failed_login_attempts = 0,
        locked_until = NULL
    WHERE user_id = :user_id
");
$reset->execute([
    ':user_id' => $row['user_id']
]);

session_regenerate_id(true);

$_SESSION['user_id'] = $row['user_id'];
$_SESSION['role'] = $row['role_name'];
$_SESSION['username'] = $row['username'];

/* remember me */
if (!empty($_POST['remember_me'])) {
    // Optional: keep only one token per user
    $deleteOld = $db->prepare("
        DELETE FROM remember_tokens
        WHERE user_id = :user_id
    ");
    $deleteOld->execute([
        ':user_id' => $row['user_id']
    ]);

    $selector = bin2hex(random_bytes(8));
    $validator = bin2hex(random_bytes(32));
    $hashedValidator = hash('sha256', $validator);
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

    $rememberStmt = $db->prepare("
        INSERT INTO remember_tokens (user_id, selector, hashed_validator, expires_at)
        VALUES (:user_id, :selector, :hashed_validator, :expires_at)
    ");
    $rememberStmt->execute([
        ':user_id' => $row['user_id'],
        ':selector' => $selector,
        ':hashed_validator' => $hashedValidator,
        ':expires_at' => $expires
    ]);

    $cookieValue = $selector . ':' . $validator;

    setcookie(
        'remember_me',
        $cookieValue,
        [
            'expires' => time() + (60 * 60 * 24 * 30),
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

unset($_SESSION['old_login']);

header("Location: ../../index.php");
exit();