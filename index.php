<?php include "partials/header.php"; ?>

<section class="hero">
    <div class="moon"></div>
    <div class="stars"></div>

    <div class="hero-content fade-in">
        <?php if(isset($_SESSION['username'])): ?>
            <h3 class="welcome-text">
                Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 🌙
            </h3>
        <?php endif; ?>
        <h1>LunaSteps</h1>
        <p>Walk Under the Moonlight</p>
        <a href="customer/shop.php" class="btn pulse">Shop Now</a>
    </div>
</section>

<?php include "partials/footer.php"; ?>
