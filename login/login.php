<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
<button class="back-btn" onclick="window.history.back()">Back</button>
<div class="form-container">
  <h2>Login</h2>
  <form id="loginForm" autocomplete="off">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required placeholder="Enter your email">
    <label for="password">Password:</label>
    <div class="password-wrapper">
      <input type="password" id="password" name="password" required placeholder="Enter your password">
      <button type="button" id="togglePassword"><span id="toggleIcon">Show</span></button>
    </div>
    <div style="margin: 16px 0 8px 0; display: flex; align-items: center;">
      <input type="checkbox" id="rememberMe" name="rememberMe" style="margin-right:8px;">
      <label for="rememberMe" style="margin:0; font-weight:normal;">Remember me</label>
    </div>
    <button class="btn" type="submit">Login</button>
  </form>
  <p>Don't have an account? <a href="../register/register.php">Register</a></p>
</div>
<div id="toast-container"></div>
<script src="login.js"></script>
</body>
</html>
