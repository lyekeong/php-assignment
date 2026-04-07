<?php
require '../database/db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// 1. Fetch User Addresses
$addr_stmt = $_db->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY is_default DESC");
$addr_stmt->execute([$user_id]);
$addresses = $addr_stmt->fetchAll();

// 2. Fetch Cart Items for Summary
$cart_query = "SELECT c.quantity, v.size, v.color, p.name, p.price, p.image 
               FROM cart c
               JOIN product_variants v ON c.variant_id = v.id
               JOIN products p ON v.product_id = p.id
               WHERE c.user_id = ?";
$cart_stmt = $_db->prepare($cart_query);
$cart_stmt->execute([$user_id]);
$items = $cart_stmt->fetchAll();

if (empty($items)) {
    header("Location: cart.php"); // Don't allow checkout if cart is empty
    exit;
}

$subtotal = 0;
foreach ($items as $item) { $subtotal += $item->price * $item->quantity; }
$delivery = ($subtotal > 500) ? 0 : 15.00;
$total = $subtotal + $delivery;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | LunaSteps</title>
    <link rel="stylesheet" href="customer.css">
    <script src="../js/customer.js"></script>
</head>
<body>
    <?php include '../header.php'; ?>

    <form action="process_checkout.php" method="POST">
        <div class="checkout-grid">
            
            <div class="checkout-form-section">
                <h1>Checkout</h1>

                <div class="delivery-form-container">
                    <h2>Delivery</h2>
                    <p class="section-instruction">Enter your name and address:</p>

                    <div class="input-group">
                        <div class="floating-input">
                            <input type="text" name="receiver_name" placeholder="Full Name *" required>
                        </div>

                        <div class="floating-input">
                            <textarea name="address_line" placeholder="Address Line (Street, House No.) *" required rows="3"></textarea>
                        </div>

                        <div class="row-inputs">
                            <div class="floating-input">
                                <input type="text" name="postcode" placeholder="Postal Code *" required>
                            </div>
                            <div class="floating-input">
                                <input type="text" name="city" placeholder="City / Locality *" required>
                            </div>
                        </div>

                        <div class="floating-input">
                            <select name="state" class="state-select" required>
                                <option value="" disabled selected>State</option>
                                <!-- Peninsular States -->
                                <option value="Johor">Johor</option>
                                <option value="Kedah">Kedah</option>
                                <option value="Kelantan">Kelantan</option>
                                <option value="Melaka">Melaka</option>
                                <option value="Negeri Sembilan">Negeri Sembilan</option>
                                <option value="Pahang">Pahang</option>
                                <option value="Penang">Penang</option>
                                <option value="Perak">Perak</option>
                                <option value="Perlis">Perlis</option>
                                <option value="Selangor">Selangor</option>
                                <option value="Terengganu">Terengganu</option>
                                <option value="Sabah">Sabah</option>
                                <option value="Sarawak">Sarawak</option>
                                <option value="Kuala Lumpur">Kuala Lumpur</option>
                                <option value="Labuan">Labuan</option>
                                <option value="Putrajaya">Putrajaya</option>
                            </select>
                        </div>

                        <div class="floating-input">
                            <input type="text" value="Malaysia" readonly class="readonly-input">
                        </div>

                        <div class="checkbox-row">
                            <input type="checkbox" id="save_profile" name="save_to_profile" checked>
                            <label for="save_profile">Save this address to my profile</label>
                        </div>

                        <div class="floating-input phone-input-box">
                            <input type="text" name="phone_number" placeholder="Phone Number *" required>
                        </div>
                    </div>
                </div>

                <h2 style="margin-top: 40px;">Payment Method</h2>
                <div class="payment-methods">
                    
                    <label class="method-item">
                        <input type="radio" name="payment_method" value="Credit Card" checked onclick="togglePaymentForms('card')"> 
                        Credit Card and Debit Card
                    </label>
                    
                    <div id="form-card" class="payment-details-container">
                        <p class="section-instruction">Enter your payment details:</p>
                        <div class="input-group">
                            <div class="floating-input">
                                <input type="text" name="card_name" placeholder="Name on card *">
                            </div>
                            <div class="floating-input card-number-box">
                                <input type="text" name="card_number" id="card-number" 
                                        placeholder="Card number *" maxlength="19" required
                                        pattern="\d{4} \d{4} \d{4} \d{4}">
                                <i class="fa-solid fa-lock lock-icon"></i>
                            </div>
                            <div class="row-inputs">
                                <div class="floating-input"><input type="text" name="card_expiry" id="card-expiry" 
                                                                    placeholder="MM/YY *" maxlength="5" required
                                                                    pattern="(0[1-9]|1[0-2])\/([0-9]{2})">
                                                                </div>
                                <div class="floating-input"><input type="text" name="card_cvv" id="card-cvv" 
                                                                    placeholder="Security Code *" maxlength="3" required>
                                                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="method-item">
                        <input type="radio" name="payment_method" value="TNG eWallet" onclick="togglePaymentForms('tng')"> 
                        TNG eWallet
                    </label>

                    <div id="form-tng" class="payment-details-container" style="display:none;">
                        <p class="section-instruction">Login to your TNG eWallet:</p>
                        <div class="input-group">
                            <div class="floating-input tng-input-wrapper">
                                <input type="text" name="tng_phone" id="tng-phone" 
                                    placeholder="Phone Number *" maxlength="11" required
                                    pattern="[0-9]{9,11}">                            
                                </div>
                            <div class="floating-input">
                                <input type="password" name="tng_password" id="tng-pin" 
                                    placeholder="6-Digit PIN *" 
                                    maxlength="6" 
                                    pattern="\d{6}" 
                                    inputmode="numeric">
                            </div>
                        </div>
                        <div class="ewallet-safety-note">
                            <i class="fa-solid fa-shield-halved"></i>
                            <p>Your login details are encrypted and processed securely via TNG official gateway.</p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="summary-section">
                <h2>Order Summary</h2>
                
            <div class="order-summary-list">
                <?php foreach ($items as $item): ?>
                    <div class="summary-item">
                        <div class="summary-img">
                            <img src="images/<?= $item->image ?>" alt="<?= htmlspecialchars($item->name) ?>">
                        </div>
                        
                        <div class="summary-details">
                            <h3 class="summary-product-name"><?= htmlspecialchars($item->name) ?></h3>
                            <p class="summary-meta">Qty <?= $item->quantity ?></p>
                            <p class="summary-meta">Color: <?= htmlspecialchars($item->color) ?></p>
                            <p class="summary-meta">Size <?= htmlspecialchars($item->size) ?></p>
                            <p class="summary-meta">RM <?= number_format($item->price, 0) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

                <div class="summary-row"><span>Subtotal</span><span>RM <?= number_format($subtotal, 2) ?></span></div>
                <div class="summary-row"><span>Delivery</span><span><?= $delivery == 0 ? 'Free' : 'RM '.number_format($delivery, 2) ?></span></div>
                <hr>
                <div class="summary-row total-row"><span>Total</span><span>RM <?= number_format($total, 2) ?></span></div>
                
                <input type="hidden" name="total_amount" value="<?= $total ?>">
                <button type="submit" class="member-checkout-btn">Place Order</button>
            </div>
        </div>
    </form>
</body>
</html>