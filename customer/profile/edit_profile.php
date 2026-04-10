<?php
require "../../config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===== GET USER ===== */
$stmt = $db->prepare("
    SELECT username, email, phone
    FROM users
    WHERE user_id = :user_id
");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch();

/* ===== HANDLE SUBMIT ===== */
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    /* ===== USERNAME ===== */
    if ($username === '') {
        $errors['username'] = "Username is required.";
    } elseif (
        strlen($username) < 3 ||
        strlen($username) > 20 ||
        !preg_match("/^[A-Za-z0-9_]+$/", $username)
    ) {
        $errors['username'] = "Username must be 3–20 letters, numbers or underscore only.";
    } else {
        $stmt = $db->prepare("
            SELECT user_id FROM users
            WHERE username = :username AND user_id != :user_id
        ");
        $stmt->execute([
            ':username' => $username,
            ':user_id' => $user_id
        ]);
        if ($stmt->fetch()) {
            $errors['username'] = "Username already taken.";
        }
    }

    /* ===== EMAIL ===== */
    if ($email === '') {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        $stmt = $db->prepare("
            SELECT user_id FROM users
            WHERE email = :email AND user_id != :user_id
        ");
        $stmt->execute([
            ':email' => $email,
            ':user_id' => $user_id
        ]);
        if ($stmt->fetch()) {
            $errors['email'] = "Email already registered.";
        }
    }

    /* ===== PHONE ===== */
    if ($phone === '') {
        $errors['phone'] = "Phone number is required.";
    } elseif (!preg_match("/^[0-9]{10,11}$/", $phone)) {
        $errors['phone'] = "Phone must be 10–11 digits.";
    }

    /* ===== UPDATE ===== */
    if (empty($errors)) {
        $stmt = $db->prepare("
            UPDATE users
            SET username = :username,
                email = :email,
                phone = :phone
            WHERE user_id = :user_id
        ");

        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':phone' => $phone,
            ':user_id' => $user_id
        ]);

        // refresh data
        $user['username'] = $username;
        $user['email'] = $email;
        $user['phone'] = $phone;
        $_SESSION['toast'] = "Profile updated successfully.";
        header("Location: profile.php");
        exit();
    }
}

include "../../partials/header.php";
?>

<div class="auth-container">
    <div class="auth-card fade-in">
        <h2>Edit Profile</h2>

        <?php if (!empty($success)): ?>
            <div class="success-text"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" id="editForm" novalidate>

            <!-- USERNAME -->
            <div class="form-group">
                <small class="error-text" id="usernameError"><?= $errors['username'] ?? '' ?></small>
                <input
                    type="text"
                    name="username"
                    id="username"
                    value="<?= htmlspecialchars($user['username']) ?>"
                    placeholder="Username"
                    class="input"
                >
            </div>

            <!-- EMAIL -->
            <div class="form-group">
                <small class="error-text" id="emailError"><?= $errors['email'] ?? '' ?></small>
                <input
                    type="text"
                    name="email"
                    id="email"
                    value="<?= htmlspecialchars($user['email']) ?>"
                    placeholder="Email"
                    class="input"
                >
            </div>

            <!-- PHONE -->
            <div class="form-group">
                <small class="error-text" id="phoneError"><?= $errors['phone'] ?? '' ?></small>
                <input
                    type="text"
                    name="phone"
                    id="phone"
                    value="<?= htmlspecialchars($user['phone']) ?>"
                    placeholder="Phone Number"
                    class="input"
                >
            </div>

            <button class="btn" type="submit">Save Changes</button>
            <button type="button" onclick="window.location='profile.php'" class="btn secondary-btn">Cancel</button>
        </form>
    </div>
</div>

<style>
.success-text {
    color: #2ecc71;
    margin-bottom: 10px;
    font-size: 14px;
}

.error-text {
    display: block;
    min-height: 18px;
    margin-top: 6px;
    font-size: 13px;
    color: #e74c3c;
}

.input-invalid {
    border: 1px solid #e74c3c !important;
}

.input-valid {
    border: 1px solid #2ecc71 !important;
}

.input {
    width: 100%;
    margin-top: 10px;
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function () {

    function setInvalid($input, $error, msg) {
        $input.removeClass("input-valid").addClass("input-invalid");
        $error.text(msg);
        return false;
    }

    function setValid($input, $error) {
        $input.removeClass("input-invalid").addClass("input-valid");
        $error.text("");
        return true;
    }

    function validateUsername() {
        const val = $("#username").val().trim();
        if (val === "") return setInvalid($("#username"), $("#usernameError"), "Username is required");
        if (val.length < 3) return setInvalid($("#username"), $("#usernameError"), "Min 3 chars");
        return setValid($("#username"), $("#usernameError"));
    }

    function validateEmail() {
        const val = $("#email").val().trim();
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (val === "") return setInvalid($("#email"), $("#emailError"), "Email required");
        if (!pattern.test(val)) return setInvalid($("#email"), $("#emailError"), "Invalid email");
        return setValid($("#email"), $("#emailError"));
    }

    function validatePhone() {
        const val = $("#phone").val().trim();
        const pattern = /^[0-9]{10,11}$/;
        if (val === "") return setInvalid($("#phone"), $("#phoneError"), "Phone required");
        if (!pattern.test(val)) return setInvalid($("#phone"), $("#phoneError"), "10–11 digits only");
        return setValid($("#phone"), $("#phoneError"));
    }

    $("#username").on("blur input", validateUsername);
    $("#email").on("blur input", validateEmail);
    $("#phone").on("blur input", validatePhone);

    $("#editForm").on("submit", function(e){
        if (!validateUsername() || !validateEmail() || !validatePhone()) {
            e.preventDefault();
        }
    });

});
</script>

<?php include "../../partials/footer.php"; ?>