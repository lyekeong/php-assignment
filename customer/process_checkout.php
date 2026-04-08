<?php
require '../database/db.php';
session_start();

// 1. Basic Security Checks
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Unauthorized access.");
}

// 2. Collect Form Data
$method = $_POST['payment_method'] ?? '';
$receiver_name = $_POST['receiver_name'] ?? '';
$address_line = $_POST['address_line'] ?? '';
$postcode = $_POST['postcode'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$total_amount = $_POST['total_amount'] ?? 0;

// 3. Run Your Validations
if ($method === 'Credit Card') {
    $card_num = str_replace(' ', '', $_POST['card_number'] ?? '');
    $expiry = $_POST['card_expiry'] ?? '';
    $cvv = $_POST['card_cvv'] ?? '';

    if (!preg_match('/^\d{16}$/', $card_num)) die("Invalid Card Number format.");
    if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $expiry)) die("Invalid Expiry Date.");
    if (strlen($cvv) < 3) die("Invalid Security Code.");
} 
elseif ($method === 'TNG eWallet') {
    $phone = $_POST['tng_phone'] ?? '';
    $pin = $_POST['tng_password'] ?? '';

    if (strlen($phone) < 9 || !is_numeric($phone)) die("Invalid TNG Phone Number.");
    if (strlen($pin) < 6) die("TNG PIN must be exactly 6 digits.");
} else {
    die("Please select a valid payment method.");
}

// 4. Start Database Transaction
try {
    $_db->beginTransaction();

    // A. Handle "Save to Profile" if checked
    if (isset($_POST['save_to_profile'])) {
        $save_stmt = $_db->prepare("INSERT INTO shipping_addresses (user_id, receiver_name, phone_number, address_line, city, postcode) VALUES (?, ?, ?, ?, ?, ?)");
        $save_stmt->execute([$user_id, $receiver_name, $phone_number, $address_line, $city, $postcode]);
    }

    // B. Create the Order (Snapshot Address)
    $full_address_text = "$receiver_name | $phone_number | $address_line, $postcode $city, $state, Malaysia";
    $order_stmt = $_db->prepare("INSERT INTO orders (user_id, shipping_address_text, total_amount, payment_method, status) VALUES (?, ?, ?, ?, 'Paid')");
    $order_stmt->execute([$user_id, $full_address_text, $total_amount, $method]);
    $order_id = $_db->lastInsertId();

    // C. Move Cart items to Order Items & Deduct Stock
    $cart_stmt = $_db->prepare("SELECT c.variant_id, c.quantity, p.price, v.stock 
                                FROM cart c 
                                JOIN product_variants v ON c.variant_id = v.id 
                                JOIN products p ON v.product_id = p.id 
                                WHERE c.user_id = ?");
    $cart_stmt->execute([$user_id]);
    $items = $cart_stmt->fetchAll();

    $item_insert = $_db->prepare("INSERT INTO order_items (order_id, variant_id, price_at_purchase, quantity) VALUES (?, ?, ?, ?)");
    $update_stock = $_db->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");

    foreach ($items as $item) {
        if ($item->stock < $item->quantity) {
            throw new Exception("One of the items is out of stock.");
        }
        $item_insert->execute([$order_id, $item->variant_id, $item->price, $item->quantity]);
        $update_stock->execute([$item->quantity, $item->variant_id]);
    }

    // D. Clear Cart
    $clear_stmt = $_db->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_stmt->execute([$user_id]);

    // Commit everything
    $_db->commit();

    // Redirect to success page
    header("Location: order_success.php?id=" . $order_id);

} catch (Exception $e) {
    $_db->rollBack();
    die("Checkout Error: " . $e->getMessage());
}