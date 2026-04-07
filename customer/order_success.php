<?php
require '../database/db.php';
session_start();

$order_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$order_id || !$user_id) {
    header("Location: shop.php");
    exit;
}

// Verify this order actually belongs to this user
$stmt = $_db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success | LunaSteps</title>
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 40px;
            border: 1px solid #eee;
            border-radius: 15px;
        }
        .success-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .order-number {
            font-weight: bold;
            font-size: 20px;
            margin: 20px 0;
            color: #555;
        }
        .btn-home {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 30px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="success-container">
        <i class="fa-solid fa-circle-check success-icon"></i>
        <h1>Thank You for Your Order!</h1>
        <p>Your payment has been received and your sneakers are being prepared for shipping.</p>
        
        <div class="order-number">
            Order #LS-<?= str_pad($order->id, 5, '0', STR_PAD_LEFT) ?>
        </div>

        <p>A confirmation email has been sent to your registered address.</p>
        
        <a href="shop.php" class="btn-home">Continue Shopping</a>
    </div>
</body>
</html>