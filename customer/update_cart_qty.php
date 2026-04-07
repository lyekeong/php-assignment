<?php
require '../database/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$variant_id = $_POST['variant_id'] ?? null;
$new_qty = (int)($_POST['new_qty'] ?? 0);

try {
    if ($new_qty <= 0) {
        // Delete item if quantity is 0
        $stmt = $_db->prepare("DELETE FROM cart WHERE user_id = ? AND variant_id = ?");
        $stmt->execute([$user_id, $variant_id]);
    } else {
        // Check stock one last time for safety
        $stockCheck = $_db->prepare("SELECT stock FROM product_variants WHERE id = ?");
        $stockCheck->execute([$variant_id]);
        $stock = $stockCheck->fetchColumn();

        if ($new_qty > $stock) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock.']);
            exit;
        }

        // Update the quantity
        $stmt = $_db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND variant_id = ?");
        $stmt->execute([$new_qty, $user_id, $variant_id]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}