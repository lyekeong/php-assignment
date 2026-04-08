<?php
require '../database/db.php';

// 1. Inputs
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$sort = $_GET['sort'] ?? 'newest';
$search = $_GET['q'] ?? '';

// 2. Filter Inputs
$brand_filter = $_GET['brand'] ?? []; 
$cat_filter = $_GET['category'] ?? []; 
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 10000;

// --- CRITICAL: PREPARE URL STRINGS FOR HTML ---
$filter_query_no_sort = "&q=" . urlencode($search) . "&min_price=$min_price&max_price=$max_price";
foreach($brand_filter as $b) { $filter_query_no_sort .= "&brand[]=" . urlencode($b); }
foreach($cat_filter as $c) { $filter_query_no_sort .= "&category[]=" . urlencode($c); }

$full_filter_query = $filter_query_no_sort . "&sort=" . urlencode($sort);
// ----------------------------------------------

// 3. Build Query
$conditions = ["(name LIKE :search OR brand LIKE :search)"];
$params = [':search' => "%$search%"];

if (!empty($brand_filter)) {
    // We use a safe way to handle the IN clause
    $placeholders = [];
    foreach ($brand_filter as $i => $brand) {
        $key = ":brand$i";
        $placeholders[] = $key;
        $params[$key] = $brand;
    }
    $conditions[] = "brand IN (" . implode(',', $placeholders) . ")";
}

if (!empty($cat_filter)) {
    $cat_placeholders = [];
    foreach ($cat_filter as $i => $cat) {
        $key = ":cat$i";
        $cat_placeholders[] = $key;
        $params[$key] = $cat;
    }
    $conditions[] = "category_id IN (" . implode(',', $cat_placeholders) . ")";
}

$conditions[] = "price BETWEEN :min AND :max";
$params[':min'] = $min_price;
$params[':max'] = $max_price;

$query_where = "WHERE " . implode(" AND ", $conditions);

// Sort Logic 
switch ($sort) {
    case 'price_low': 
        $order_by = "price ASC"; 
        break;
    case 'price_high': 
        $order_by = "price DESC"; 
        break;
    case 'date_old': 
        // This was missing! It sorts by ID ascending (first added to DB)
        $order_by = "id ASC"; 
        break;
    default: 
        $order_by = "id DESC"; // Default / 'newest'
        break;
}

// 5. Execution
$total_products_stmt = $_db->prepare("SELECT COUNT(*) FROM products $query_where");
$total_products_stmt->execute($params);
$total_products = $total_products_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

$stmt = $_db->prepare("SELECT * FROM products $query_where ORDER BY $order_by LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

// 6. Data for Filter UI
$categories = $_db->query("SELECT * FROM categories")->fetchAll();
$brands_list = $_db->query("SELECT DISTINCT brand FROM products ORDER BY brand ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sneakers</title>
    <link rel="stylesheet" href="customer.css">
</head>

<body>
    <?php include '../header.php'; ?>

    <div class="container">
        <div class="top-bar">
            <h1>SNEAKERS</h1>

<div class="controls">
    <div class="left-controls">
        <form action="" method="GET" class="search-form">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <div class="search-wrapper">
                <input type="text" name="q" placeholder="Search sneakers..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </form>
        
        <div class="filter-dropdown-container">
            <button type="button" class="filter-btn" id="filterBtn">
                Filter <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i>
            </button>
            
            <div id="filter-menu" class="filter-menu-content">
                <form action="" method="GET">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

                    <div class="filter-section">
                        <p class="filter-title">Brand</p>
                        <?php foreach($brands_list as $b): ?>
                            <label><input type="checkbox" name="brand[]" value="<?= htmlspecialchars($b) ?>" <?= in_array($b, $brand_filter) ? 'checked' : '' ?>> <?= htmlspecialchars($b) ?></label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-section">
                        <p class="filter-title">Category</p>
                        <?php foreach($categories as $c): ?>
                            <label><input type="checkbox" name="category[]" value="<?= $c->id ?>" <?= in_array($c->id, $cat_filter) ? 'checked' : '' ?>> <?= htmlspecialchars($c->name) ?></label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-section">
                        <p class="filter-title">Price Range</p>
                        <div class="price-row">
                            <input type="number" name="min_price" placeholder="Min" value="<?= htmlspecialchars($min_price) ?>">
                            <input type="number" name="max_price" placeholder="Max" value="<?= htmlspecialchars($max_price) ?>">
                        </div>
                    </div>

                    <button type="submit" class="apply-btn-small">Apply</button>
                    <a href="shop.php" class="clear-link">Clear All</a>
                </form>
            </div>
        </div>
    </div>

    <span class="count"><?php echo $total_products; ?> products</span>

    <select class="sort" onchange="location.href='?sort=' + this.value + '<?= $filter_query_no_sort ?>';">
        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Feature</option>
        <option value="date_old" <?= $sort == 'date_old' ? 'selected' : '' ?>>Date, old to new</option>
        <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price, low to high</option>
        <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price, high to low</option>
    </select>
</div>

        <div class="grid">
            <?php foreach ($products as $p): ?>
                <div class="sneaker-item">
                    <a href="detail.php?id=<?php echo $p->id; ?>" class="product-link">
                        <img src="images/<?php echo $p->image; ?>" alt="<?php echo htmlspecialchars($p->name); ?>">
                        <h3><?php echo htmlspecialchars($p->name); ?></h3>
                        <p class="brand"><?php echo htmlspecialchars($p->brand); ?></p>
                        <p class="price">RM <?php echo number_format($p->price, 2); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <?php 
            for ($i = 1; $i <= $total_pages; $i++): 
                if ($i == 1 || $i == $total_pages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                    <a href="?page=<?= $i . $filter_query_no_sort . "&sort=" . urlencode($sort) ?>" 
                       class="<?= ($page == $i) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php elseif ($i == 2 || $i == $total_pages - 1): ?>
                    <span class="dots">...</span>
                <?php endif; 
            endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= ($page + 1) . $filter_query_no_sort . "&sort=" . urlencode($sort) ?>" class="next-btn">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtn = document.getElementById('filterBtn');
    const filterMenu = document.getElementById('filter-menu');

    if (filterBtn && filterMenu) {
        filterBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevents the window click from closing it instantly
            filterMenu.classList.toggle('show');
            console.log("Filter menu toggled");
        });
    }

    // Close menu when clicking anywhere else
    window.addEventListener('click', function(e) {
        if (filterMenu && !filterMenu.contains(e.target) && e.target !== filterBtn) {
            filterMenu.classList.remove('show');
        }
    });
});
</script>
</html>