<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

require "../../config/db.php";
require "../../mailer/SMTP.php";
require "../../mailer/PHPMailer.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: forgot_password.php");
    exit;
}

$email = trim($_POST["email"] ?? "");

if ($email === "") {
    $_SESSION['forgot_error'] = "Please enter your email.";
    header("Location: forgot_password.php");
    exit;
}

$stmt = $db->prepare("
    SELECT user_id, email
    FROM users
    WHERE email = :email
    LIMIT 1
");
$stmt->execute([
    ':email' => $email
]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['forgot_error'] = "No account found with that email.";
    header("Location: forgot_password.php");
    exit;
}

$token = bin2hex(random_bytes(32));
$expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

$updateStmt = $db->prepare("
    UPDATE users
    SET reset_token = :reset_token,
        reset_token_expiry = :reset_token_expiry
    WHERE user_id = :user_id
");
$updateStmt->execute([
    ':reset_token' => $token,
    ':reset_token_expiry' => $expiry,
    ':user_id' => $user['user_id']
]);

$link = "http://localhost:8000/auth/forgot_password/reset_form.php?token=" . urlencode($token);

$mail = new PHPMailer();

$mail->isSMTP();
$mail->Host = "smtp.gmail.com";
$mail->SMTPAuth = true;
$mail->Username = "glebested@gmail.com";
$mail->Password = "kwko vuvg ucvq lshe";
$mail->SMTPSecure = "tls";
$mail->Port = 587;

$mail->setFrom("glebested@gmail.com", "LunaSteps");
$mail->addAddress($email);

$mail->isHTML(true);
$mail->Subject = "LunaSteps Password Reset";
$mail->Body = "
    <p>Hello,</p>
    <p>We received a password reset request.</p>
    <p>Click the link below to reset your password:</p>
    <p><a href='{$link}'>{$link}</a></p>
    <p>This link will expire in 1 hour.</p>
";
$mail->AltBody = "Reset your password using this link: {$link}";

if ($mail->send()) {
    $_SESSION['forgot_success'] = "Reset email sent successfully.";
} else {
    $_SESSION['forgot_error'] = "Mailer Error: " . $mail->ErrorInfo;
}

header("Location: forgot_password.php");
exit;