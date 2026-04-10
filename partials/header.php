<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once __DIR__ . '/remember_me.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LunaSteps</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/assets/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<header class="navbar">
    <div class="nav-left">
        <div class="logo">
            <a href="/" class="logo-link" aria-label="Homepage">
                🌙 LunaSteps
            </a>
        </div>

        <nav class="nav-icons-left">
            <a href="/index.php" class="icon-link" aria-label="Home">
                <i class="fa-solid fa-house"></i>
            </a>
            <a href="/customer/shop.php" class="icon-link" aria-label="Shop">
                <i class="fa-solid fa-store"></i>
            </a>
            <a href="/customer/cart.php" class="icon-link" aria-label="Cart">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
        </nav>
    </div>

    <div class="nav-right">
        <nav class="nav-icons-right">
            
        </nav>

        <nav class="nav-auth">
            <?php if (isset($_SESSION['role'])): ?>

                <?php if ($_SESSION['role'] == "customer"): ?>
                    <a href="/customer/order.php">Orders</a>
                    <a href="/customer/profile/profile.php">Profile</a>
                    <a href="/auth/login/logout.php" class="btn logout-btn">Logout</a>

                <?php elseif ($_SESSION['role'] == "admin"): ?>
                    <a href="/admin/index.php">Admin</a>
                    <a href="/auth/login/logout.php" class="btn logout-btn">Logout</a>

                <?php elseif ($_SESSION['role'] == "staff"): ?>
                    <a href="/staff/index.php">Staff</a>
                    <a href="/auth/login/logout.php" class="btn logout-btn">Logout</a>

                <?php endif; ?>

            <?php else: ?>
                <a href="/auth/login/login.php" class="btn">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>