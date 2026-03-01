<?php
require "../config/db.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../auth/login.php");
    exit();
}

include "../partials/header.php";
?>

<section class="dashboard fade-in">
    <h1>🌙 Customer Dashboard</h1>
    <?php if(isset($_SESSION['full_name'])): ?>
        <h3 class="welcome-text">
            Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?> 🌙
        </h3>
    <?php endif; ?>

    <div class="card-box">
        <div class="card hover-zoom">
            <h3>Browse Shoes</h3>
            <button class="btn pulse">Shop Now</button>
        </div>

        <div class="card hover-zoom">
            <h3>My Orders</h3>
            <button class="btn">View Orders</button>
        </div>
    </div>
</section>

<?php include "../partials/footer.php"; ?>