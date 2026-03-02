<?php
require "../config/db.php";

$errors = $_SESSION['profile_errors'] ?? [];
unset($_SESSION['profile_errors']);

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email, phone FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include "../partials/header.php";
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>My Profile</h2>

        <form action="update_profile.php" method="POST">

            <div class="form-group">
                <input type="text" name="username"
                    value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                    placeholder="Username">

                <?php if(isset($errors['username'])): ?>
                    <small class="input-error"><?= $errors['username'] ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>

            <div class="form-group">
                <input type="text" name="phone"
                    value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                    placeholder="Phone">

                <?php if(isset($errors['phone'])): ?>
                    <small class="input-error"><?= $errors['phone'] ?></small>
                <?php endif; ?>
            </div>

            <button class="btn">Update Profile</button>
        </form>

        <hr style="margin:25px 0; opacity:0.2;">

        <h3 style="margin-bottom:10px;">Account Security</h3>

        <a href="change_password.php" class="btn btn-secondary">
            Change Password
        </a>
        <br>

        <a href="../auth/logout.php" class="btn logout-btn-large">
            Logout
        </a>
    </div>
</div>

<?php include "../partials/footer.php"; ?>