<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="register.css">
</head>
<body>
<button class="back-btn" onclick="window.history.back()">Back</button>
<div class="form-container">
  <h2>Register</h2>
  <form id="registerForm" action="" method="post" autocomplete="off">
    <label for="name">Your name:</label>
    <input type="text" id="name" name="name"  required placeholder="Please enter your name">

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required placeholder="Please enter your email">
    
    <label for="password">Password:</label>
    <div class="password-wrapper">
      <input type="password" id="password" name="password"  required placeholder="Please enter your password">
      <button type="button" id="togglePassword">
        <span id="toggleIcon">Show</span>
      </button>

    </div>
    <div style="margin: 16px 0 8px 0; display: flex; align-items: center;">
      <input type="checkbox" id="rememberMe" name="rememberMe" style="margin-right:8px;">
      <label for="rememberMe" style="margin:0; font-weight:normal;">Remember me</label>
    </div>
    <button class="btn" type="submit">Create Account</button>
  </form>
  <p>Already have an account? <a href="../login/login.php">Login</a></p>
</div>
<div id="toast-container"></div>
<script src="register.js"></script>
<script>
  // Handle redirect after registration and set session/cookie
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    form.addEventListener('submit', function (e) {
      if (!e.defaultPrevented) {
        e.preventDefault();
        const name = document.getElementById('name').value.trim();
        const rememberMe = document.getElementById('rememberMe').checked;
        // Set cookie for 28 days if rememberMe, else session cookie
        if (rememberMe) {
          document.cookie = `user_name=${encodeURIComponent(name)}; max-age=${28*24*60*60}; path=/`;
        } else {
          document.cookie = `user_name=${encodeURIComponent(name)}; path=/`;
        }
        window.location.href = '../index.php?welcome=' + encodeURIComponent(name);
      }
    });
  });
</script>
</body>
</html>
