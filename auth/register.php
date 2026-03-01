<?php
include "../partials/header.php";

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

unset($_SESSION['errors']);
unset($_SESSION['old']);
?>

<div class="auth-container">
    <div class="auth-card fade-in">
        <h2>✨ Create Account</h2>

        <form action="register_process.php" method="POST">

            <!-- Full Name -->
            <div class="form-group">
                <input type="text" name="full_name"
                    value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                    placeholder="Full Name" required>
                <?php if(isset($errors['full_name'])): ?>
                    <small class="error-text"><?= $errors['full_name'] ?></small>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="form-group">
                <input type="email" name="email"
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                    placeholder="Email" required>
                <?php if(isset($errors['email'])): ?>
                    <small class="error-text"><?= $errors['email'] ?></small>
                <?php endif; ?>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <input type="text" name="phone"
                    value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                    placeholder="Phone Number" required>
                <?php if(isset($errors['phone'])): ?>
                    <small class="error-text"><?= $errors['phone'] ?></small>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
                <?php if(isset($errors['password'])): ?>
                    <small class="error-text"><?= $errors['password'] ?></small>
                <?php endif; ?>
            </div>

            <!-- Confirm -->
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <?php if(isset($errors['confirm_password'])): ?>
                    <small class="error-text"><?= $errors['confirm_password'] ?></small>
                <?php endif; ?>
            </div>

            <button class="btn pulse">Register</button>
        </form>
    </div>
</div>

<?php include "../partials/footer.php"; ?>