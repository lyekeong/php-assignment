<?php
require '../database/db.php';
session_start();

// Ensure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Fetch cart items with variant and product details
// Note: We include v.id as variant_id to pass to the JS sync function
$query = "SELECT c.id AS cart_id, c.quantity, v.id AS variant_id, v.size, v.color, v.stock, 
                 p.name, p.price, p.brand, p.image 
          FROM cart c
          JOIN product_variants v ON c.variant_id = v.id
          JOIN products p ON v.product_id = p.id
          WHERE c.user_id = ?";

$stmt = $_db->prepare($query);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Initial Calculation for Page Load
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item->price * $item->quantity;
}
$total = $subtotal;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bag | LunaSteps</title>
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../js/customer.js"></script>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="cart-page-container">
        <div class="bag-section">
            <h1>Bag</h1>
            
            <?php if (empty($cart_items)): ?>
                <p>There are no items in your bag.</p>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="bag-item" 
                         data-cart-id="<?= $item->cart_id ?>" 
                         data-variant-id="<?= $item->variant_id ?>" 
                         data-max-stock="<?= $item->stock ?>"
                         data-price="<?= $item->price ?>"> 
                        
                        <div class="item-img">
                            <img src="images/<?= $item->image ?>" alt="<?= $item->name ?>">
                        </div>
                        
                        <div class="item-info">
                            <div class="info-top">
                                <h3><?= htmlspecialchars($item->name) ?></h3>
                                <p class="item-price-total">RM <?= number_format($item->price, 2) ?></p>
                            </div>
                            <p class="meta"><?= htmlspecialchars($item->brand) ?> | <?= htmlspecialchars($item->color) ?> | Size <?= htmlspecialchars($item->size) ?></p>
                            
                            <div class="item-actions">
                                <div class="qty-selector">
                                    <button class="qty-btn" onclick="updateQty(<?= $item->cart_id ?>, -1)">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    
                                    <input type="text" class="cart-qty-input" value="<?= $item->quantity ?>" readonly 
                                           style="width: 30px; border: none; text-align: center; font-weight: 600; background: transparent;">
                                    
                                    <button class="qty-btn" onclick="updateQty(<?= $item->cart_id ?>, 1)">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                                <button class="action-icon" onclick="updateQty(<?= $item->cart_id ?>, -999)">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="summary-section">
            <h2>Summary</h2>
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="summary-subtotal">RM <?= number_format($subtotal, 2) ?></span>
            </div>
            <hr>
            <div class="summary-row total-row">
                <span>Total</span>
                <span id="summary-total">RM <?= number_format($total, 2) ?></span>
            </div>
            <a href="checkout.php" style="text-decoration: none;">
                <button class="member-checkout-btn" <?= ($subtotal == 0) ? 'disabled' : '' ?>>
                    Checkout
                </button>
            </a>
        </div>
    </div>
</body>
</html>