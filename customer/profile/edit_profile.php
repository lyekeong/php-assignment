<?php
require "../../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login/login.php");
    exit();
}

$stmt = $db->prepare("
    SELECT profile_photo
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
.container {
    max-width: 500px;
    margin: 50px auto;
    text-align: center;
}

.profile-img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 20px;
}

.error {
    color: red;
    font-size: 13px;
    margin-top: 10px;
}
</style>

<div class="container">

    <h2>Edit Profile Photo</h2>

    <img id="preview" src="<?= htmlspecialchars($photo) ?>" class="profile-img">

    <br>

    <input type="file" id="fileInput" accept=".jpg,.jpeg,.png">

    <div class="error" id="errorMsg"></div>

    <br>

    <button id="saveBtn" class="btn">Save</button>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function () {

    let selectedFile = null;

    $("#fileInput").on("change", function () {

        const file = this.files[0];
        $("#errorMsg").text("");

        if (!file) return;

        const name = file.name.toLowerCase();

        if (!(name.endsWith(".jpg") || name.endsWith(".jpeg") || name.endsWith(".png"))) {
            $("#errorMsg").text("Only JPG/PNG allowed");
            return;
        }

        if (file.size > 1024 * 1024) {
            $("#errorMsg").text("Max 1MB only");
            return;
        }

        selectedFile = file;

        const reader = new FileReader();
        reader.onload = function (e) {
            $("#preview").attr("src", e.target.result);
        };
        reader.readAsDataURL(file);
    });

    $("#saveBtn").on("click", function () {

        if (!selectedFile) {
            $("#errorMsg").text("Please select a file");
            return;
        }

        const formData = new FormData();
        formData.append("photo", selectedFile);

        $.ajax({
            url: "upload_photo.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.trim() === "success") {
                    window.location.href = "profile.php";
                } else {
                    $("#errorMsg").text(res);
                }
            },
            error: function () {
                $("#errorMsg").text("Upload failed");
            }
        });
    });

});
</script>

<?php include "../../partials/footer.php"; ?>