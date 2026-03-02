<?php
require "../config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit();
}

$errors = $_SESSION['password_errors'] ?? [];
$success = $_SESSION['password_success'] ?? "";

unset($_SESSION['password_errors']);
unset($_SESSION['password_success']);

include "../partials/header.php";
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Change Password</h2>

        <?php if($success): ?>
            <div class="success-box"><?= $success ?></div>
        <?php endif; ?>

        <form action="update_password.php" method="POST">

            <div class="form-group">
                <input type="password" name="current_password" placeholder="Current Password">
                <?php if(isset($errors['current_password'])): ?>
                    <small class="input-error"><?= $errors['current_password'] ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <input type="password" name="new_password" placeholder="New Password">
                <?php if(isset($errors['new_password'])): ?>
                    <small class="input-error"><?= $errors['new_password'] ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm New Password">
                <?php if(isset($errors['confirm_password'])): ?>
                    <small class="input-error"><?= $errors['confirm_password'] ?></small>
                <?php endif; ?>
            </div>

            <button class="btn">Update Password</button>
        </form>
    </div>
</div>

<?php include "../partials/footer.php"; ?>