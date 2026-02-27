<?php
// Clear the user_name cookie
setcookie("user_name", "", time() - 3600, "/");
// Optionally destroy session if used
// session_start();
// session_destroy();
// Redirect to login page
header("Location: login/login.php");
exit();
