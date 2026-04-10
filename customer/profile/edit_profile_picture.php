<?php
require "../../config/db.php";
require "../../lib/SimpleImage.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$error = "";
$success = "";

$defaultPhoto = '/customer/images/default_profile_picture.jpg';//define default photo path
$uploadDirFs = $_SERVER['DOCUMENT_ROOT'] . '/customer/uploads/profile/';
$uploadDirDb = '/customer/uploads/profile/';

if (!is_dir($uploadDirFs)) {
    mkdir($uploadDirFs, 0777, true);
}
//
$stmt = $db->prepare("
    SELECT profile_photo
    FROM users
    WHERE user_id = :user_id
");
$stmt->execute([':user_id' => $userId]);//get current user photo from database
$user = $stmt->fetch(PDO::FETCH_ASSOC);//get current photo from database

$currentPhoto = !empty($user['profile_photo']) ? $user['profile_photo'] : $defaultPhoto;//if user has a photo, use it, else use default photo
$hasProfilePhoto = !empty($user['profile_photo']) || !empty($_SESSION['edit_photo_temp']);
if (!isset($_SESSION['edit_photo_temp'])) {

    if ($currentPhoto !== $defaultPhoto) {

        $extension = pathinfo($currentPhoto, PATHINFO_EXTENSION);

        $tempFileName = 'temp_' . $userId . '_' . time() . '.' . $extension;
        $tempFs = $uploadDirFs . $tempFileName;
        $tempDb = $uploadDirDb . $tempFileName;

        $originalFileName = 'original_' . $userId . '_' . time() . '.' . $extension;
        $originalFs = $uploadDirFs . $originalFileName;
        $originalDb = $uploadDirDb . $originalFileName;

        $currentFs = $_SERVER['DOCUMENT_ROOT'] . $currentPhoto;

        if (file_exists($currentFs)) {
            copy($currentFs, $tempFs);
            copy($currentFs, $originalFs);

            $_SESSION['edit_photo_temp'] = $tempDb;
            $_SESSION['edit_photo_original'] = $originalDb;
        } else {
            $_SESSION['edit_photo_temp'] = $defaultPhoto;
            $_SESSION['edit_photo_original'] = $defaultPhoto;
        }

    } else {
        $_SESSION['edit_photo_temp'] = $defaultPhoto;
        $_SESSION['edit_photo_original'] = $defaultPhoto;
    }
}

$tempPhoto = $_SESSION['edit_photo_temp'];
function cleanupUserPhotos($dir, $userId, $keepFiles = []) {
    $files = glob($dir . "*_" . $userId . "_*");

    foreach ($files as $file) {
        $fileDbPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);

        if (!in_array($fileDbPath, $keepFiles) && file_exists($file)) {
            unlink($file);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== 0) {
        $error = "Please choose a photo.";
    } else {
        $file = $_FILES['profile_photo'];

        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 1024 * 1024; // 1MB

        if (!in_array($file['type'], $allowedTypes)) {
            $error = "Only JPG and PNG files are allowed.";
        } else {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }

            $fileName = 'temp_' . $userId . '_' . time() . '.' . $extension;
            $savePathFs = $uploadDirFs . $fileName;
            $savePathDb = $uploadDirDb . $fileName;

            $originalFileName = 'original_' . $userId . '_' . time() . '.' . $extension;
            $originalPathFs = $uploadDirFs . $originalFileName;
            $originalPathDb = $uploadDirDb . $originalFileName;

            try {
                $image = new SimpleImage($file['tmp_name']);

                if ($file['size'] > $maxSize) {
                    // auto resize/compress if file > 1MB
                    $image->bestFit(1200, 1200);

                    if ($extension === 'png') {
                        $image->toFile($savePathFs, 'image/png', ['compression' => 8]);
                        $image->toFile($originalPathFs, 'image/png', ['compression' => 8]);
                    } else {
                        $image->toFile($savePathFs, 'image/jpeg', 75);
                        $image->toFile($originalPathFs, 'image/jpeg', 75);
                    }

                    $error = "Original image was larger than 1MB. It has been resized/compressed automatically.";
                } else {
                    if (move_uploaded_file($file['tmp_name'], $savePathFs)) {
                        copy($savePathFs, $originalPathFs);
                    } else {
                        throw new Exception("Upload failed.");
                    }
                }

                $_SESSION['edit_photo_temp'] = $savePathDb;
                $_SESSION['edit_photo_original'] = $originalPathDb;
                $tempPhoto = $savePathDb;

                $hasProfilePhoto = true;

                if (empty($error)) {
                    $success = "Photo uploaded. You can now edit it.";
                }

            } catch (Exception $e) {
                $error = "Failed to upload photo.";
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_action'])) {//when user clicks an edit action, perform that action on the temp photo
    $action = $_POST['edit_action'];

    if (empty($tempPhoto) || $tempPhoto === $defaultPhoto) {
        $error = "Please upload a photo first.";
    } else {
        $tempPhotoFs = $_SERVER['DOCUMENT_ROOT'] . $tempPhoto;

        if (!file_exists($tempPhotoFs)) {
            $error = "Temporary photo not found.";
        } else {
            try {
                $image = new SimpleImage($tempPhotoFs);

                if ($action === 'rotate_left') {
                    $image->rotate(-90);
                } elseif ($action === 'rotate_right') {
                    $image->rotate(90);
                } elseif ($action === 'flip_horizontal') {
                    $image->flip('x');
                } elseif ($action === 'flip_vertical') {
                    $image->flip('y');
                } elseif ($action === 'reset') {
                    $originalFs = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['edit_photo_original'];
                    $tempFs = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['edit_photo_temp'];

                    if (file_exists($originalFs)) {
                        copy($originalFs, $tempFs);
                        $success = "Photo reset to original uploaded version.";
                    } else {
                        $error = "Original photo not found.";
                    }
                }

                if ($action !== 'reset') {//if not resetting, save the edited photo back to temp path
                    $mimeType = $image->getMimeType();

                    if ($mimeType === 'image/png') {
                        $image->toFile($tempPhotoFs, 'image/png');
                    } else {
                        $image->toFile($tempPhotoFs, 'image/jpeg', 90);
                    }

                    $success = "Photo updated.";
                }

            } catch (Exception $e) {
                $error = "Failed to edit photo.";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Save final photo to database
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_photo'])) {

    if (empty($tempPhoto) || $tempPhoto === $defaultPhoto) {
        $error = "Please upload a photo first.";
    } else {
        try {
            // 旧照片
            $oldPhoto = $currentPhoto;

            $finalUpdate = $db->prepare("
                UPDATE users
                SET profile_photo = :profile_photo
                WHERE user_id = :user_id
            ");
            $finalUpdate->execute([
                ':profile_photo' => $tempPhoto,
                ':user_id' => $userId
            ]);

            // 🔥 清理：只保留当前 temp（最终照片）
            cleanupUserPhotos($uploadDirFs, $userId, [$tempPhoto]);

            $currentPhoto = $tempPhoto;

            // 🔥 删除旧照片（如果不是 default）
            if ($oldPhoto !== $defaultPhoto) {
                $oldFs = $_SERVER['DOCUMENT_ROOT'] . $oldPhoto;
                if (file_exists($oldFs)) {
                    unlink($oldFs);
                }
            }

            // 🔥 清 session
            unset($_SESSION['edit_photo_temp']);
            unset($_SESSION['edit_photo_original']);

            $success = "Profile photo saved successfully.";

            header("Location: profile.php");
            exit();
        } catch (Exception $e) {
            $error = "Failed to save profile photo.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_edit'])) {

    // 🔥 删除所有 temp/original
    cleanupUserPhotos($uploadDirFs, $userId, [$currentPhoto]);

    // 🔥 清 session
    unset($_SESSION['edit_photo_temp']);
    unset($_SESSION['edit_photo_original']);

    header("Location: profile.php");
    exit();
}

include "../../partials/header.php";
?>

<style>
    .no-photo-box {
    width: 280px;
    height: 280px;
    border-radius: 50%;
    border: 2px dashed rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 14px;
    margin: 0 auto 18px;
    text-align: center;
}
.edit-profile-container {
    max-width: 980px;
    margin: 40px auto;
    padding: 0 20px;
}

.edit-profile-card {
    position: relative;
    background: rgba(30, 41, 59, 0.92);
    border-radius: 20px;
    padding: 34px 34px 30px;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.22);
}

.back-link {
    position: absolute;
    top: 24px;
    left: 28px;
    color: #cbd5e1;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
    padding: 8px 14px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.06);
    transition: 0.2s ease;
}

.back-link:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #ffffff;
}

.edit-title {
    text-align: center;
    margin: 0 0 22px;
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
}

.edit-message {
    margin-bottom: 16px;
    font-size: 14px;
    font-weight: 500;
}

.edit-error {
    color: #ff7b7b;
}

.edit-success {
    color: #86efac;
}

.edit-layout {
    display: flex;
    gap: 50px;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-top: 18px;
}

.edit-left {
    flex: 1;
    min-width: 260px;
    text-align: center;
    padding-top: 10px;
}

.edit-right {
    flex: 1;
    min-width: 320px;
    max-width: 470px;
}

.preview-photo {
    width: 280px;
    height: 280px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.14);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    display: block;
    margin: 0 auto 18px;
}

.preview-label {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #ffffff;
}

.form-group {
    margin-bottom: 18px;
}

.section-label {
    display: block;
    margin-bottom: 10px;
    font-size: 15px;
    font-weight: 600;
    color: #ffffff;
}

input[type="file"] {
    width: 100%;
    padding: 14px 16px;
    border: none;
    border-radius: 14px;
    background: #071632;
    color: #ffffff;
    font-size: 14px;
    box-sizing: border-box;
}

.upload-form,
.action-form,
.save-form {
    margin-bottom: 18px;
}

.button-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.action-btn,
.primary-btn,
.secondary-btn {
    height: 46px;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
}

.action-btn {
    background: #334155;
    color: #f8fafc;
}

.action-btn:hover {
    background: #475569;
}

.action-btn-reset {
    background: #3f3f46;
}

.action-btn-reset:hover {
    background: #52525b;
}

.bottom-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-top: 6px;
}

.primary-btn {
    min-width: 150px;
    padding: 0 22px;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #ffffff;
    box-shadow: 0 8px 18px rgba(99, 102, 241, 0.25);
}

.primary-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(99, 102, 241, 0.32);
}

.secondary-btn {
    min-width: 110px;
    padding: 0 18px;
    background: rgba(255,255,255,0.08);
    color: #ffffff;
}

.secondary-btn:hover {
    background: rgba(255,255,255,0.14);
}

.helper-text {
    margin-top: 8px;
    font-size: 13px;
    color: #94a3b8;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .edit-profile-card {
        padding: 28px 20px 24px;
    }

    .back-link {
        position: static;
        display: inline-block;
        margin-bottom: 18px;
    }

    .edit-title {
        font-size: 24px;
    }

    .edit-layout {
        flex-direction: column;
        gap: 28px;
    }

    .edit-right {
        max-width: 100%;
        width: 100%;
    }

    .preview-photo {
        width: 220px;
        height: 220px;
    }

    .button-grid {
        grid-template-columns: 1fr;
    }

    .bottom-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .primary-btn,
    .secondary-btn {
        width: 100%;
    }
}
</style>

<div class="edit-profile-container">
    <div class="edit-profile-card">

        <a href="profile.php" class="back-link">← Back</a>

        <h2 class="edit-title">Edit Profile Photo</h2>

        <?php if ($error): ?>
            <div class="edit-message edit-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="edit-message edit-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="edit-layout">
            <div class="edit-left">
                <?php if ($hasProfilePhoto): ?>
                    <img src="<?= htmlspecialchars($_SESSION['edit_photo_temp']) ?>?t=<?= time() ?>" class="preview-photo">
                <?php else: ?>
                    <div class="no-photo-box">
                        No profile picture yet
                    </div>
                <?php endif; ?>
                <p class="preview-label">Preview Photo</p>
            </div>

            <div class="edit-right">
                <form method="post" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                    <div class="form-group">
                        <label for="profile_photo" class="section-label">Upload Photo</label>
                        <input type="file" name="profile_photo" id="profile_photo" accept=".jpg,.jpeg,.png">
                        <input type="hidden" name="upload_photo" value="1">
                    </div>
                </form>

                <form method="post" class="action-form">
                    <div class="button-grid">
                        <button type="submit" name="edit_action" value="rotate_left" class="action-btn" <?= !$hasProfilePhoto ? 'disabled' : '' ?>>Rotate Left</button>
                        <button type="submit" name="edit_action" value="rotate_right" class="action-btn" <?= !$hasProfilePhoto ? 'disabled' : '' ?>>Rotate Right</button>
                        <button type="submit" name="edit_action" value="flip_horizontal" class="action-btn" <?= !$hasProfilePhoto ? 'disabled' : '' ?>>Flip Horizontal</button>
                        <button type="submit" name="edit_action" value="flip_vertical" class="action-btn" <?= !$hasProfilePhoto ? 'disabled' : '' ?>>Flip Vertical</button>
                        <button type="submit" name="edit_action" value="reset" class="action-btn action-btn-reset" <?= !$hasProfilePhoto ? 'disabled' : '' ?>>Reset</button>
                    </div>
                </form>

                <form method="post" class="save-form">
                    <div class="bottom-actions">
                        <button type="submit" name="save_photo" class="primary-btn" <?= !$hasProfilePhoto ? 'disabled' : '' ?>>Save Photo</button>
                        <button type="submit" name="cancel_edit" class="secondary-btn">Cancel</button>
                    </div>
                </form>

                <p class="helper-text">
                    Max file size: 1MB. Allowed formats: JPG, PNG.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('profile_photo').addEventListener('change', function () {
    if (this.files.length > 0) {
        document.getElementById('uploadForm').submit();
    }
});
</script>

<?php include "../../partials/footer.php"; ?>