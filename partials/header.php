<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LunaSteps</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="/WEB_BASED/assets/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>

<header class="navbar">
    <div class="logo">
        <a href="/WEB_BASED/" class="logo-link">
            🌙 LunaSteps
        </a>
    </div>

    <nav>
        <a href="/WEB_BASED/">Home</a>

        <?php if(isset($_SESSION['role'])): ?>

            <?php if($_SESSION['role'] == "customer"): ?>
                <a href="/WEB_BASED/profile.php">Profile</a>

            <?php elseif($_SESSION['role'] == "admin"): ?>
                <a href="/WEB_BASED/admin/index.php">Admin</a>
                <a href="/WEB_BASED/auth/logout.php" class="btn logout-btn">Logout</a>

            <?php elseif($_SESSION['role'] == "staff"): ?>
                <a href="/WEB_BASED/staff/index.php">Staff</a>
                <a href="/WEB_BASED/auth/logout.php" class="btn logout-btn">Logout</a>

            <?php endif; ?>

        <?php else: ?>
            <a href="/WEB_BASED/auth/login.php" class="btn">Login</a>
        <?php endif; ?>
    </nav>
</header>