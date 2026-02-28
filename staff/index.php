<?php
require "../config/db.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "staff"){
    header("Location: ../auth/login.php");
    exit();
}

include "../partials/header.php";
?>

<section class="dashboard fade-in">
    <h1>👟 Staff Dashboard</h1>
    <p>Operational management panel.</p>

    <div class="card-box">
        <div class="card hover-zoom">
            <h3>Order Processing</h3>
            <button class="btn pulse">Open</button>
        </div>

        <div class="card hover-zoom">
            <h3>Inventory</h3>
            <button class="btn">Open</button>
        </div>
    </div>
</section>

<?php include "../partials/footer.php"; ?>