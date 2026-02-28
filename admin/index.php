<?php
require "../config/db.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

include "../partials/header.php";
?>

<section class="dashboard fade-in">
    <h1>⚙ Admin Dashboard</h1>
    <p>Manage LunaSteps system.</p>

    <div class="card-box">
        <div class="card hover-zoom">
            <h3>Manage Users</h3>
            <button class="btn pulse">Open</button>
        </div>

        <div class="card hover-zoom">
            <h3>Manage Products</h3>
            <button class="btn">Open</button>
        </div>
    </div>
</section>

<?php include "../partials/footer.php"; ?>