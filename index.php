<?php include "partials/header.php"; ?>
<style>
.hero {
    position: relative;
}

.hero-content {
    position: relative;
    z-index: 2;
}
.moon {
    position: absolute;
    top: 15%;
    right: 20%;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle, #fff 40%, #e2e8f0 60%, #cbd5e1 100%);
    border-radius: 50%;
    box-shadow:
        0 0 40px rgba(255,255,255,0.6),
        0 0 80px rgba(255,255,255,0.4),
        0 0 120px rgba(255,255,255,0.2);
    animation: float 6s ease-in-out infinite;
}

.stars {
    position: absolute;
    width: 100%;
    height: 100%;
    background: transparent;
    box-shadow:
        100px 200px white,
        300px 150px white,
        500px 300px white,
        700px 100px white,
        900px 400px white,
        1100px 250px white,
        200px 500px white,
        600px 450px white,
        800px 350px white;
    animation: twinkle 4s infinite alternate;
}
</style>

<section class="hero">
    <div class="stars"></div>
    <div class="moon"></div>

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