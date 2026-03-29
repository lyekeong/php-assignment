<style>
.header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:15px 50px;
    border-bottom:1px solid #ddd;
}

.nav-left, .nav-right {
    flex: 1; 
}

.nav-left a{
    margin-right:20px;
    text-decoration:none;
    color:black;
    font-size:14px;
}

.nav-right {
    display: flex;
    justify-content: flex-end; 
    gap: 20px;
}

.brand-name {
    flex: 1;
    text-align: center; /* Centers the text perfectly in the middle column */
    font-size: 22px;
    font-weight: bold;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-decoration: none;
    color: black;
}
/* Style for the new profile image */
.icon {
    width: 20px;   /* Adjusted slightly for visual balance */
    height: 20px;
    object-fit: cover; /* Ensures the image doesn't stretch */
    vertical-align: middle; /* Aligns it with the other text icons */
    border-radius: 50%; /* Optional: makes the profile icon circular */
}

.icon{
    text-decoration:none;
    font-size:18px;
    color:black;
}
</style>

<header class="header">
    <nav class="nav-left">
        <a href="#">Home</a>
        <a href="#">Sneakers</a>
        <a href="#">Brands</a>
        <a href="#">Sale</a>
    </nav>

    <a href="/customer/index.php" class="brand-name">Sneakerlah</a>

    <div class="nav-right">
        <a href="#" class="icon"><img src="../image/user.png" alt="personal" class="icon"></a>
        <a href="#" class="icon"><img src="../image/search.png" alt="search" class="icon"></a>
        <a href="#" class="icon"><img src="../image/cart.png" alt="cart" class="icon"></a>
    </div>

</header>

