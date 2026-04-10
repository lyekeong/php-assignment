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
</style>

<div class="profile-container">
    <div class="profile-card">

        <div>
            <img src="<?= htmlspecialchars($photo) ?>" class="profile-img">
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

<?php include "../../partials/footer.php"; ?>