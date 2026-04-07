<?php
require '../database/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['success' => false, 'message' => 'Please login as a customer.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$variant_id = $_POST['variant_id'] ?? null;
$quantity_to_add = 1; 

try {
    // 1. Fetch current stock from the database
    $stmtStock = $_db->prepare("SELECT stock FROM product_variants WHERE id = ?");
    $stmtStock->execute([$variant_id]);
    $variant = $stmtStock->fetch();

    if (!$variant) {
        echo json_encode(['success' => false, 'message' => 'Product variant not found.']);
        exit;
    }

    $available_stock = (int)$variant->stock;

    // 2. Check if the item already exists in this user's cart
    $check = $_db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND variant_id = ?");
    $check->execute([$user_id, $variant_id]);
    $existing = $check->fetch();

    $current_cart_qty = $existing ? (int)$existing->quantity : 0;
    $new_total_qty = $current_cart_qty + $quantity_to_add;

    // 3. CRITICAL: Validate stock
    if ($new_total_qty > $available_stock) {
        echo json_encode([
            'success' => false, 
            'message' => "Sorry, only $available_stock items available in this size. You already have $current_cart_qty in your cart."
        ]);
        exit;
    }

    // 4. Update or Insert
    if ($existing) {
        $stmt = $_db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_total_qty, $existing->id]);
    } else {
        $stmt = $_db->prepare("INSERT INTO cart (user_id, variant_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $variant_id, $quantity_to_add]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}