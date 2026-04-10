<?php
require "../../config/db.php";
include "../../partials/header.php";

$token = $_GET['token'] ?? '';
$error = $_SESSION['reset_error'] ?? "";
unset($_SESSION['reset_error']);

if ($token === "") {
    echo "<div class='auth-container'><div class='auth-card'><div class='error-box'>Invalid reset link.</div></div></div>";
    include "../../partials/footer.php";
    exit();
}

$stmt = $db->prepare("
    SELECT user_id
    FROM users
    WHERE reset_token = :token
      AND reset_token_expiry > NOW()
    LIMIT 1
");
$stmt->execute([
    ':token' => $token
]);
$user = $stmt->fetch();

if (!$user) {
    echo "<div class='auth-container'><div class='auth-card'><div class='error-box'>This reset link is invalid or expired.</div></div></div>";
    include "../../partials/footer.php";
    exit();
}

$errors = $_SESSION['password_errors'] ?? [];
unset($_SESSION['password_errors']);

$toastMessages = [];

if (!empty($error)) {
    $toastMessages[] = $error;
}

if (isset($errors['new_password']) && is_array($errors['new_password'])) {
    foreach ($errors['new_password'] as $msg) {
        $toastMessages[] = $msg;
    }
}

if (!empty($errors['confirm_password'])) {
    $toastMessages[] = $errors['confirm_password'];
}
?>

<?php if (!empty($toastMessages)): ?>
  <div class="toast-container" id="toastContainer">
    <?php foreach ($toastMessages as $msg): ?>
      <div class="toast"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<style>
  .auth-container {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 80vh;
    padding: 230px;
  }

  .error-text {
    display: block;
    min-height: 18px;
    margin-top: 1px;
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

  .password-note {
    margin-top: 8px;
    font-size: 13px;
    color: #cbd5e1;
  }

  .form-group {
    margin-bottom: 2px;
    text-align: left;
  }

  .auth-card input {
    width: 100%;
    padding: 12px;
    margin-bottom: 2px;
    border: none;
    border-radius: 8px;
    background: #0f172a;
    color: white;
    transition: 0.3s;
  }

  .btn {
    margin-top: 8px;
  }

  .error-text.show {
    margin-bottom: 15px;
  }

  h2 {
    margin-top: 0px;
  }

  .toast-container {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .toast {
    min-width: 260px;
    max-width: 360px;
    padding: 12px 14px;
    border-radius: 10px;
    color: #fff;
    background: #e74c3c;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    font-size: 13px;
    line-height: 1.4;
    opacity: 0;
    transform: translateY(-10px);
    animation: toastIn 0.25s ease forwards;
  }

  .toast.hide {
    animation: toastOut 0.25s ease forwards;
  }

  @keyframes toastIn {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes toastOut {
    to {
      opacity: 0;
      transform: translateY(-10px);
    }
  }
</style>

<div class="auth-container">
  <div class="auth-card">
    <h2>Reset Password</h2>

    <form action="update_password.php" method="POST" novalidate>
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

      <div class="form-group">
        <input type="password" name="new_password" id="new_password" placeholder="New Password" required>

        <div class="password-note">Do not use spaces in your password.</div>

        <div class="password-rules">
          <div class="password-rule invalid" id="ruleLength">✕ Minimum of 8 characters</div>
          <div class="password-rule invalid" id="ruleComplex">✕ Uppercase, lowercase letters, and one number</div>
        </div>
        <small class="error-text" id="newPasswordError"></small>
      </div>

      <div class="form-group">
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required>
        <small class="error-text" id="confirmPasswordError"></small>
      </div>

      <button type="submit" class="btn">Update Password</button>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
  $(function () {
    const $form = $("form");
    const $newPassword = $("#new_password");
    const $confirmPassword = $("#confirm_password");

    const $newPasswordError = $("#newPasswordError");
    const $confirmPasswordError = $("#confirmPasswordError");

    const $ruleLength = $("#ruleLength");
    const $ruleComplex = $("#ruleComplex");

    const touched = {
      new_password: false,
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

    function updatePasswordRules() {
      const value = $newPassword.val();

      const hasMinLength = value.length >= 8;
      const hasUpper = /[A-Z]/.test(value);
      const hasLower = /[a-z]/.test(value);
      const hasNumber = /[0-9]/.test(value);

      if (hasMinLength) {
        $ruleLength.text("✓ Minimum of 8 characters").removeClass("invalid").addClass("valid");
      } else {
        $ruleLength.text("✕ Minimum of 8 characters").removeClass("valid").addClass("invalid");
      }

      if (hasUpper && hasLower && hasNumber) {
        $ruleComplex.text("✓ Uppercase, lowercase letters, and one number").removeClass("invalid").addClass("valid");
      } else {
        $ruleComplex.text("✕ Uppercase, lowercase letters, and one number").removeClass("valid").addClass("invalid");
      }

      return {
        hasMinLength: hasMinLength,
        hasUpper: hasUpper,
        hasLower: hasLower,
        hasNumber: hasNumber
      };
    }

    function validatePassword(showMessage = false) {
      const value = $newPassword.val();
      const rules = updatePasswordRules();

      if (value === "") {
        return setInvalid($newPassword, $newPasswordError, "Password is required", showMessage);
      }

      if (!rules.hasMinLength || !rules.hasUpper || !rules.hasLower || !rules.hasNumber) {
        return setInvalid($newPassword, $newPasswordError, "Password does not meet the requirements", showMessage);
      }

      if (/\s/.test(value)) {
        return setInvalid($newPassword, $newPasswordError, "No spaces allowed", showMessage);
      }

      return setValid($newPassword, $newPasswordError);
    }

    function validateConfirmPassword(showMessage = false) {
      const value = $confirmPassword.val();
      const passwordValue = $newPassword.val();

      if (value === "") {
        return setInvalid($confirmPassword, $confirmPasswordError, "Please confirm your password", showMessage);
      }

      if (value !== passwordValue) {
        return setInvalid($confirmPassword, $confirmPasswordError, "Passwords do not match", showMessage);
      }

      return setValid($confirmPassword, $confirmPasswordError);
    }

    $newPassword.on("blur", function () {
      touched.new_password = true;
      validatePassword(true);
    });

    $confirmPassword.on("blur", function () {
      touched.confirm_password = true;
      validateConfirmPassword(true);
    });

    $newPassword.on("input", function () {
      if (touched.new_password) {
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
      touched.new_password = true;
      touched.confirm_password = true;

      const isPasswordValid = validatePassword(true);
      const isConfirmPasswordValid = validateConfirmPassword(true);

      if (!isPasswordValid || !isConfirmPasswordValid) {
        e.preventDefault();
      }
    });

    hideError($newPasswordError);
    hideError($confirmPasswordError);
    updatePasswordRules();

    const $toasts = $("#toastContainer .toast");

    if ($toasts.length) {
      setTimeout(function () {
        $toasts.addClass("hide");
        setTimeout(function () {
          $("#toastContainer").remove();
        }, 250);
      }, 2500);
    }
  });
</script>

<?php include "../../partials/footer.php"; ?>