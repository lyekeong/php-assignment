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
    <p>Welcome to LunaSteps, valued customer.</p>

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