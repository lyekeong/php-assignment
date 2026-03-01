<?php include "../partials/header.php"; ?>

<div class="auth-container">
    <div class="auth-card fade-in">
        <h2>🌙 LunaSteps Login</h2>

        <form action="login_process.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button class="btn pulse">Login</button>
        </form>

        <p class="switch-text">
            Don't have account?
            <a href="register.php">Register</a>
        </p>
    </div>
</div>

<?php include "../partials/footer.php"; ?>