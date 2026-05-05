<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

/* =========================
   PAGINATION
========================= */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

/* =========================
   CATEGORY FILTER
========================= */
$type = isset($_GET['type']) ? $_GET['type'] : 'industrial';

if ($type === 'custom') {
    $categoryFilter = "WHERE category = 'Fabricated Product'";
    $title = "Customized Products";
} else {
    $categoryFilter = "WHERE category = 'Industrial Materials'";
    $title = "Industrial Materials";
}

/* =========================
   FETCH PRODUCTS
========================= */
$query = "
SELECT id, name, category, stock, image, price
FROM items
$categoryFilter
ORDER BY name
LIMIT $limit OFFSET $offset
";

$products = $conn->query($query);

/* =========================
   TOTAL COUNT
========================= */
$countQuery = "SELECT COUNT(*) as total FROM items $categoryFilter";
$countResult = $conn->query($countQuery);
$total = $countResult->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);
?>

<div class="main clean-container">

    <h1 class="section-title"><?= htmlspecialchars($title) ?></h1>

    <!-- CATEGORY SWITCH -->
    <div class="category-tabs">
        <a href="?type=industrial" class="<?= $type=='industrial'?'active':'' ?>">Industrial</a>
        <a href="?type=custom" class="<?= $type=='custom'?'active':'' ?>">Customized</a>
    </div>

    <!-- PRODUCT GRID -->
    <div class="product-wrapper">
        <?php while($p = $products->fetch_assoc()): ?>

        <div class="product-card">

            <div class="card-image">
                <img 
                    src="/rholance_pms/assets/images/products/<?= htmlspecialchars($p['name']) ?>.png"
                    alt="<?= htmlspecialchars($p['name']) ?>"
                    onerror="
                        this.onerror=null;
                        this.src='/rholance_pms/assets/images/products/<?= htmlspecialchars($p['name']) ?>.jpg';
                        this.onerror=function(){
                            this.onerror=null;
                            this.src='/rholance_pms/assets/images/products/<?= strtolower(str_replace(' ', '_', $p['name'])) ?>.png';
                            this.onerror=function(){
                                this.src='/rholance_pms/assets/images/default.png';
                            }
                        };
                    ">
            </div>

            <div class="card-content">
                <h4><?= htmlspecialchars($p['name']) ?></h4>
                <p class="price">₱<?= number_format($p['price'],2) ?></p>
            </div>

        </div>

        <?php endwhile; ?>
    </div>

    <!-- PAGINATION -->
    <div class="pagination clean-pagination">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?type=<?= $type ?>&page=<?= $i ?>"
               class="<?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

</div>