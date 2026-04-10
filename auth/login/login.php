<?php
include "../../partials/header.php";

$error = $_SESSION['login_error'] ?? "";
$old_login = $_SESSION['old_login'] ?? "";

unset($_SESSION['login_error']);
unset($_SESSION['old_login']);
?>

<style>
.remember-row {
    margin: 6px 0 10px 0;
}

.remember-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    cursor: pointer;
}
</style>

<div class="auth-container">
    <div class="auth-card fade-in">
        <h2>🌙 LunaSteps Login</h2>

        <?php if ($error): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="login_process.php" method="POST" novalidate id="loginForm">
            <div class="form-group">
                <input
                    type="text"
                    name="login"
                    id="login"
                    placeholder="Email or Username"
                    value="<?= htmlspecialchars($old_login) ?>"
                    required
                >
                <small class="input-error" id="loginError"></small>
            </div>

            <div class="form-group">
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Password"
                    required
                >
                <small class="input-error" id="passwordError"></small>
            </div>

            <div class="form-row remember-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember_me" value="1">
                    Remember Me
                </label>
            </div>

            <button class="btn pulse" type="submit">Login</button>
        </form>

        <p class="switch-text">
            Don't have account?
            <a href="../register/register.php">Register</a>
        </p>

        <p class="switch-text">
            <a href="../forgot_password/forgot_password.php">Forgot Password?</a>
        </p>
    </div>
</div>

<?php include "../../partials/footer.php"; ?>