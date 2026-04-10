<?php
require "../../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login/login.php");
    exit();
}

$stmt = $db->prepare("
    SELECT username, email, phone, created_at, profile_photo
    FROM users
    WHERE user_id = :user_id
");
$stmt->execute([
    ':user_id' => $_SESSION['user_id']
]);
$user = $stmt->fetch();

$photo = !empty($user['profile_photo'])
    ? $user['profile_photo']
    : '/customer/images/default_profile_picture.jpg';

include "../../partials/header.php";
?>
<?php if (!empty($_SESSION['toast'])): ?>
    <div id="toast"><?= $_SESSION['toast'] ?></div>
    <?php unset($_SESSION['toast']); ?>
<?php endif; ?>

<style>
.profile-container {
    max-width: 800px;
    margin: 40px auto;
}

.profile-card {
    display: flex;
    gap: 40px;
    background: rgba(30,41,59,0.88);
    padding: 30px;
    border-radius: 16px;
}
.profile-img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    transition: 0.3s;
}

.profile-img:hover {
    transform: scale(1.05);
}
.info-row {
    margin-bottom: 14px;
}

.info-label {
    font-size: 13px;
    color: #94a3b8;
}

.info-value {
    font-size: 16px;
}
.profile-photo-section {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.edit-photo-btn {
    margin-top: 12px;
    padding: 8px 16px;
    font-size: 13px;
    border-radius: 10px;
    background: rgba(255,255,255,0.08);
    color: #fff;
    text-decoration: none;
    transition: 0.2s;
}

.edit-photo-btn:hover {
    background: rgba(255,255,255,0.16);
}
</style>

<div class="profile-container">
    <div class="profile-card">

        <div class="profile-photo-section">
            <img src="<?= htmlspecialchars($photo) ?>" class="profile-img">

            <a href="edit_profile_picture.php" class="edit-photo-btn">
                Edit Photo
            </a>
        </div>

        <div>
            <h2>My Profile</h2>

            <div class="info-row">
                <div class="info-label">Username</div>
                <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Phone</div>
                <div class="info-value"><?= htmlspecialchars($user['phone']) ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Joined On</div>
                <div class="info-value"><?= date('Y-m-d', strtotime($user['created_at'])) ?></div>
            </div>

            <br>
            <a href="edit_profile.php" class="btn">Edit Profile</a>
        </div>

    </div>
</div>

<script>
setTimeout(() => {
    const toast = document.getElementById("toast");
    if (toast) toast.remove();
}, 3000);
</script>
<?php include "../../partials/footer.php"; ?>