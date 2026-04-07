<?php
session_start();
$_SESSION['user_id'] = 1; 
$_SESSION['role'] = 'customer'; 
?>
<?php
require '../database/db.php';

// Get Product ID from URL
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    die("Product not found.");
}

// 1. Fetch main product details
$stmt = $_db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product does not exist.");
}

// Fetch unique color names from the product_variants table
$stmt_colors = $_db->prepare("SELECT DISTINCT color FROM product_variants WHERE product_id = ?");
$stmt_colors->execute([$product_id]);
$color_list = $stmt_colors->fetchAll();

// We'll use the first color found as the "default" active one if needed
$active_color = $color_list[0]->color ?? '';

// 2. Fetch all images for this product
$stmt_images = $_db->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmt_images->execute([$product_id]);
$images = $stmt_images->fetch();

// 3. Fetch variants (Sizes and Stocks)
$stmt_variants = $_db->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$stmt_variants->execute([$product_id]);
$variants = $stmt_variants->fetchAll();

// Fetch variants (Sizes and Stocks)
$stmt_variants = $_db->prepare("SELECT size, stock FROM product_variants WHERE product_id = ?");
$stmt_variants->execute([$product_id]);
$raw_variants = $stmt_variants->fetchAll();

$variants_by_color = [];
foreach ($variants as $v) {
    $variants_by_color[$v->color][$v->size] = [
        'stock' => $v->stock,
        'id' => $v->id
    ];
}
// Convert to JSON so JavaScript can read it easily
$variants_json = json_encode($variants_by_color);

// DEFINE THE MASTER RANGE (US 4 to US 12, including halves)
$master_size_list = [
    'US 4', 'US 4.5', 'US 5', 'US 5.5', 'US 6', 'US 6.5', 'US 7', 'US 7.5',
    'US 8', 'US 8.5', 'US 9', 'US 9.5', 'US 10', 'US 10.5', 'US 11', 'US 11.5', 'US 12'
];

// Calculate total stock
$total_stock = array_sum(array_column($variants, 'stock'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product->name); ?> | Details</title>
    <link rel="stylesheet" href="customer.css">
    <script src="../js/customer.js"></script>
</head>

<body>
    <?php include '../header.php'; ?>

    <div class="product-container">
        <div class="image-section">
            <div class="thumbnail-list">
                <img src="images/<?php echo $product->image; ?>" class="thumb active">
                <?php if($images): ?>
                    <?php if($images->image_path): ?><img src="images/<?php echo $images->image_path; ?>" class="thumb"><?php endif; ?>
                    <?php if($images->image_path_2): ?><img src="images/<?php echo $images->image_path_2; ?>" class="thumb"><?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="main-image">
                <img src="images/<?php echo $product->image; ?>" id="current-img" alt="Product">
                <button class="nav-btn prev">❮</button>
                <button class="nav-btn next">❯</button>
            </div>
        </div>

        <div class="info-section">
            <span class="stock-badge">Total Stock: <?php echo $total_stock; ?></span>
            
            <h1 class="product-title"><?php echo htmlspecialchars($product->name); ?></h1>
            <p class="product-meta"><?php echo htmlspecialchars($product->brand); ?> | <?php echo htmlspecialchars($images->color ?? 'Standard'); ?></p>
            
            <h2 class="product-price">RM <?php echo number_format($product->price, 2); ?></h2>

            <hr>

        <div class="variant-selector">
            <p class="label">COLOUR</p>
            <div class="color-text-container">
                <?php foreach ($color_list as $index => $c): ?>
                    <div class="color-tag <?php echo ($index === 0) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($c->color); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="variant-selector">
            <p class="label">SELECT SIZE (US)</p>
            <div class="size-pill-container" id="size-container">
                <?php foreach ($master_size_list as $size_label): ?>
                    <div class="size-pill" data-size="<?php echo $size_label; ?>">
                        <span class="size-name"><?php echo htmlspecialchars($size_label); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

            <form id="add-to-cart-form" method="POST" action="cart_process.php">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="selected_color" id="selected-color" value="<?php echo htmlspecialchars($active_color); ?>">
                <input type="hidden" name="selected_size" id="selected-size" value="">

                <div id="stock-status-container" class="stock-status" style="display: none;">
                    <span class="status-dot"></span>
                    <span id="stock-message"></span>
                </div>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
                    <button type="submit" class="add-btn" id="add-to-cart-btn" disabled>Add to Cart</button>
                <?php else: ?>
                    <div class="restriction-notice" style="padding: 15px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; margin-top: 20px;">
                        <p><strong>Note:</strong> Please <a href="login.php">login as a customer</a> to purchase this item.</p>
                    </div>
                    <button id="add-to-cart-btn" style="display:none;" disabled></button>
                <?php endif; ?>
            </form></br>

            <div class="description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($product->description)); ?></p>
            </div>


        </div>
    </div>

    <div class="cart-drawer-overlay" id="cart-overlay"></div>
    <div class="cart-drawer" id="cart-drawer">
        <div class="cart-header">
            <h2>CART</h2>
            <button class="close-cart" id="close-cart-btn">&times;</button>
        </div>

        <div class="cart-items-container" id="cart-items-container">
            <?php
            // Fetch what's ALREADY in the database for this user
            $drawer_query = "SELECT c.id AS cart_id, c.quantity, v.id AS variant_id, v.size, v.color, v.stock, 
                                    p.name, p.price, p.image 
                            FROM cart c
                            JOIN product_variants v ON c.variant_id = v.id
                            JOIN products p ON v.product_id = p.id
                            WHERE c.user_id = ?";
            $drawer_stmt = $_db->prepare($drawer_query);
            $drawer_stmt->execute([$_SESSION['user_id']]);
            $existing_cart = $drawer_stmt->fetchAll();

            foreach ($existing_cart as $item): 
                // Create the same ID format that your JavaScript uses to prevent duplicates
                $itemID = str_replace(' ', '', $item->name . "-" . $item->color . "-" . $item->size);
            ?>
                <div class="cart-item" 
                    data-cart-id="<?= $itemID ?>" 
                    data-variant-id="<?= $item->variant_id ?>" 
                    data-max-stock="<?= $item->stock ?>" 
                    data-price="<?= $item->price ?>">
                    <img src="images/<?= $item->image ?>" alt="<?= htmlspecialchars($item->name) ?>">
                    <div class="item-details">
                        <h3><?= htmlspecialchars($item->name) ?></h3>
                        <p>Color: <?= htmlspecialchars($item->color) ?></p>
                        <p>Size: <?= htmlspecialchars($item->size) ?></p>
                        <div class="quantity-controls">
                            <button type="button" class="qty-btn minus" onclick="updateQty('<?= $itemID ?>', -1)">-</button>
                            <input type="text" class="cart-qty-input" value="<?= $item->quantity ?>" readonly>
                            <button type="button" class="qty-btn plus" onclick="updateQty('<?= $itemID ?>', 1)">+</button>
                        </div>
                    </div>
                    <p class="item-price">RM <?= number_format($item->price, 2) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-footer">
            <div class="subtotal-row">
                <span>SUBTOTAL</span>
                <span class="subtotal-price" id="cart-subtotal">RM 0.00</span>
            </div>
            <p class="shipping-info">Shipping, taxes, and discount codes calculated at checkout.</p>
            <div class="terms-row">
                <input type="checkbox" id="terms-check">
                <label for="terms-check">I AGREE WITH THE <a href="#">TERMS AND CONDITIONS</a></label>
            </div>
            <a href="checkout.php" style="text-decoration: none;">
                <button class="checkout-btn" id="checkout-main-btn" disabled>CHECK OUT</button>
            </a>        
        </div>
    </div>
    <script>
    const productVariants = <?php echo $variants_json; ?>;
    let currentActiveColor = "<?php echo $active_color; ?>";
    
    // ADD THIS LINE: It tells the JS who is logged in
    const userRole = "<?php echo $_SESSION['role'] ?? 'guest'; ?>";
</script>
</body>
</html>