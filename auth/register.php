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

        <form action="register_process.php" method="POST" id="registerForm" novalidate>

            <!-- Username -->
            <div class="form-group">
                <input
                    type="text"
                    name="username"
                    id="username"
                    value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                    placeholder="Username"
                >
                <small class="error-text" id="usernameError">
                    <?= htmlspecialchars($errors['username'] ?? '') ?>
                </small>
            </div>

            <!-- Email -->
            <div class="form-group">
                <input
                    type="text"
                    name="email"
                    id="email"
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                    placeholder="Email"
                >
                <small class="error-text" id="emailError">
                    <?= htmlspecialchars($errors['email'] ?? '') ?>
                </small>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <input
                    type="text"
                    name="phone"
                    id="phone"
                    value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                    placeholder="Phone Number"
                >
                <small class="error-text" id="phoneError">
                    <?= htmlspecialchars($errors['phone'] ?? '') ?>
                </small>
            </div>

            <!-- Password -->
            <div class="form-group">
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Password"
                >
                <small class="error-text" id="passwordError">
                    <?= htmlspecialchars($errors['password'] ?? '') ?>
                </small>

                <div class="password-rules">
                    <div class="password-rule invalid" id="ruleLength">✕ Minimum of 8 characters</div>
                    <div class="password-rule invalid" id="ruleComplex">✕ Uppercase, lowercase letters, and one number</div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <input
                    type="password"
                    name="confirm_password"
                    id="confirm_password"
                    placeholder="Confirm Password"
                >
                <small class="error-text" id="confirmPasswordError">
                    <?= htmlspecialchars($errors['confirm_password'] ?? '') ?>
                </small>
            </div>

            <button class="btn pulse" type="submit">Register</button>
        </form>
    </div>
</div>

<style>
    .error-text {
        display: block;
        min-height: 18px;
        margin-top: 6px;
        font-size: 13px;
        color: #e74c3c;
        visibility: hidden;
    }

    .error-text.show {
        visibility: visible;
    }

    .password-rules {
        margin-top: 8px;
    }

    .password-rule {
        margin-top: 4px;
        font-size: 13px;
        line-height: 1.4;
    }

    .password-rule.invalid {
        color: #e74c3c;
    }

    .password-rule.valid {
        color: #2ecc71;
    }

    .input-invalid {
        border: 1px solid #e74c3c !important;
        box-shadow: none !important;
    }

    .input-valid {
        border: 1px solid #2ecc71 !important;
        box-shadow: none !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function () {
    const $form = $("#registerForm");

    const $username = $("#username");
    const $email = $("#email");
    const $phone = $("#phone");
    const $password = $("#password");
    const $confirmPassword = $("#confirm_password");

    const $usernameError = $("#usernameError");
    const $emailError = $("#emailError");
    const $phoneError = $("#phoneError");
    const $passwordError = $("#passwordError");
    const $confirmPasswordError = $("#confirmPasswordError");

    const $ruleLength = $("#ruleLength");
    const $ruleComplex = $("#ruleComplex");

    const touched = {
        username: false,
        email: false,
        phone: false,
        password: false,
        confirm_password: false
    };

    function showError($error, message) {
        $error.text(message).addClass("show");
    }

    function hideError($error) {
        $error.text("").removeClass("show");
    }

    function setInvalid($input, $error, message, shouldShowMessage) {
        $input.removeClass("input-valid").addClass("input-invalid");

        if (shouldShowMessage) {
            showError($error, message);
        } else {
            hideError($error);
        }

        return false;
    }

    function setValid($input, $error) {
        $input.removeClass("input-invalid").addClass("input-valid");
        hideError($error);
        return true;
    }

    function validateUsername(showMessage = false) {
        const value = $username.val().trim();

        if (value === "") {
            return setInvalid($username, $usernameError, "Username is required", showMessage);
        }

        if (value.length < 3) {
            return setInvalid($username, $usernameError, "Username must be at least 3 characters", showMessage);
        }

        return setValid($username, $usernameError);
    }

    function validateEmail(showMessage = false) {
        const value = $email.val().trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (value === "") {
            return setInvalid($email, $emailError, "Email is required", showMessage);
        }

        if (!emailPattern.test(value)) {
            return setInvalid($email, $emailError, "Invalid email format", showMessage);
        }

        return setValid($email, $emailError);
    }

    function validatePhone(showMessage = false) {
        const value = $phone.val().trim();
        const phonePattern = /^[0-9+\-\s]{8,15}$/;

        if (value === "") {
            return setInvalid($phone, $phoneError, "Phone number is required", showMessage);
        }

        if (!phonePattern.test(value)) {
            return setInvalid($phone, $phoneError, "Invalid phone number", showMessage);
        }

        return setValid($phone, $phoneError);
    }

    function updatePasswordRules() {
        const value = $password.val();

        const hasMinLength = value.length >= 8;
        const hasUpper = /[A-Z]/.test(value);
        const hasLower = /[a-z]/.test(value);
        const hasNumber = /[0-9]/.test(value);

        if (hasMinLength) {
            $ruleLength
                .text("✓ Minimum of 8 characters")
                .removeClass("invalid")
                .addClass("valid");
        } else {
            $ruleLength
                .text("✕ Minimum of 8 characters")
                .removeClass("valid")
                .addClass("invalid");
        }

        if (hasUpper && hasLower && hasNumber) {
            $ruleComplex
                .text("✓ Uppercase, lowercase letters, and one number")
                .removeClass("invalid")
                .addClass("valid");
        } else {
            $ruleComplex
                .text("✕ Uppercase, lowercase letters, and one number")
                .removeClass("valid")
                .addClass("invalid");
        }

        return {
            hasMinLength: hasMinLength,
            hasUpper: hasUpper,
            hasLower: hasLower,
            hasNumber: hasNumber
        };
    }

    function validatePassword(showMessage = false) {
        const value = $password.val();
        const rules = updatePasswordRules();

        if (value === "") {
            return setInvalid($password, $passwordError, "Password is required", showMessage);
        }

        if (!rules.hasMinLength || !rules.hasUpper || !rules.hasLower || !rules.hasNumber) {
            return setInvalid($password, $passwordError, "Password does not meet the requirements", showMessage);
        }

        return setValid($password, $passwordError);
    }

    function validateConfirmPassword(showMessage = false) {
        const value = $confirmPassword.val();
        const passwordValue = $password.val();

        if (value === "") {
            return setInvalid($confirmPassword, $confirmPasswordError, "Please confirm your password", showMessage);
        }

        if (value !== passwordValue) {
            return setInvalid($confirmPassword, $confirmPasswordError, "Passwords do not match", showMessage);
        }

        return setValid($confirmPassword, $confirmPasswordError);
    }

    $username.on("blur", function () {
        touched.username = true;
        validateUsername(true);
    });

    $email.on("blur", function () {
        touched.email = true;
        validateEmail(true);
    });

    $phone.on("blur", function () {
        touched.phone = true;
        validatePhone(true);
    });

    $password.on("blur", function () {
        touched.password = true;
        validatePassword(true);
    });

    $confirmPassword.on("blur", function () {
        touched.confirm_password = true;
        validateConfirmPassword(true);
    });

    $username.on("input", function () {
        if (touched.username) {
            validateUsername(true);
        } else {
            validateUsername(false);
        }
    });

    $email.on("input", function () {
        if (touched.email) {
            validateEmail(true);
        } else {
            validateEmail(false);
        }
    });

    $phone.on("input", function () {
        if (touched.phone) {
            validatePhone(true);
        } else {
            validatePhone(false);
        }
    });

    $password.on("input", function () {
        if (touched.password) {
            validatePassword(true);
        } else {
            validatePassword(false);
        }

        if ($confirmPassword.val() !== "") {
            if (touched.confirm_password) {
                validateConfirmPassword(true);
            } else {
                validateConfirmPassword(false);
            }
        }
    });

    $confirmPassword.on("input", function () {
        if (touched.confirm_password) {
            validateConfirmPassword(true);
        } else {
            validateConfirmPassword(false);
        }
    });

    $form.on("submit", function (e) {
        touched.username = true;
        touched.email = true;
        touched.phone = true;
        touched.password = true;
        touched.confirm_password = true;

        const isUsernameValid = validateUsername(true);
        const isEmailValid = validateEmail(true);
        const isPhoneValid = validatePhone(true);
        const isPasswordValid = validatePassword(true);
        const isConfirmPasswordValid = validateConfirmPassword(true);

        if (!isUsernameValid || !isEmailValid || !isPhoneValid || !isPasswordValid || !isConfirmPasswordValid) {
            e.preventDefault();
        }
    });

    hideError($usernameError);
    hideError($emailError);
    hideError($phoneError);
    hideError($passwordError);
    hideError($confirmPasswordError);

    updatePasswordRules();
});
</script>

<?php include "../partials/footer.php"; ?>