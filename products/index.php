<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

/* =========================
   PAGINATION
========================= */
$limit = 12;
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
$products = $conn->query("
    SELECT id, name, category, image, price
    FROM items
    $categoryFilter
    ORDER BY name
    LIMIT $limit OFFSET $offset
");

$total = $conn->query("SELECT COUNT(*) as total FROM items $categoryFilter")->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);
?>

<div class="rh-main">
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1><?= htmlspecialchars($title) ?></h1>
            <p>Browse our collection of high-quality materials and products.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <button class="btn btn-dark fw-800" data-bs-toggle="modal" data-bs-target="#prodModal" onclick="prepAddProd()">
                    <i class="fas fa-plus me-2"></i>Add Product
                </button>
            <?php elseif ($_SESSION['role'] === 'customer'): ?>
                <a href="<?= BASE_URL ?>customer/customize.php" class="btn btn-warning fw-800">
                    <i class="fas fa-pen-ruler me-2"></i>Request Custom Design
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- CATEGORY SWITCH -->
    <div class="rh-tabs mb-4">
        <a href="?type=industrial" class="rh-tab <?= $type=='industrial'?'active':'' ?>">Industrial Materials</a>
        <a href="?type=custom" class="rh-tab <?= $type=='custom'?'active':'' ?>">Fabricated Products</a>
    </div>

    <!-- PRODUCT GRID -->
    <?php if ($products && $products->num_rows > 0): ?>
    <div class="row g-4 mb-4">
        <?php while($p = $products->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card h-100 rh-proj-card border-0 shadow-sm">
                <div class="rh-proj-thumb" style="height: 180px;">
                    <img src="<?= BASE_URL ?>assets/images/products/<?= htmlspecialchars($p['name']) ?>.png"
                         onerror="this.src='<?= BASE_URL ?>assets/images/no-image.png'" alt="">
                    <span class="badge bg-dark status-float">₱<?= number_format($p['price'],0) ?></span>
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-800 mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                    <div class="text-muted small"><?= htmlspecialchars($p['category']) ?></div>
                </div>
                <div class="card-footer bg-transparent border-0 p-3 pt-0">
                    <?php if ($_SESSION['role'] === 'customer'): ?>
                        <a href="<?= BASE_URL ?>customer/customize.php?template=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning w-100 fw-700">Customize This</a>
                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-dark w-100" onclick='prepEditProd(<?= json_encode($p) ?>)'>Edit</button>
                            <form action="process_products.php" method="POST" onsubmit="return confirm('Delete item?')" class="w-100">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">Del</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <a href="#" class="btn btn-sm btn-outline-dark w-100 disabled">View Info</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <nav class="d-flex justify-content-center">
        <ul class="pagination pagination-sm">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?type=<?= $type ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php else: ?>
    <div class="card p-5 text-center text-muted border-0 shadow-sm">
        <i class="fas fa-box-open fs-1 mb-3 opacity-25"></i>
        <h4>No Items Found</h4>
        <p>There are no items listed in this category yet.</p>
    </div>
    <?php endif; ?>

    <!-- FEEDBACK -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm mt-4"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

</div>

<!-- PRODUCT MODAL -->
<div class="modal fade" id="prodModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800" id="prodModalTitle">Add Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_products.php" method="POST">
                <input type="hidden" name="action" id="prodAction" value="add_product">
                <input type="hidden" name="product_id" id="prodId">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-700">Name</label>
                        <input type="text" name="name" id="prodName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-700">Category</label>
                        <select name="category" id="prodCat" class="form-select" required>
                            <option value="Industrial Materials">Industrial Materials</option>
                            <option value="Fabricated Product">Fabricated Product</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-700">Price (₱)</label>
                        <input type="number" step="0.01" name="price" id="prodPrice" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100 fw-800" id="prodSubmitBtn">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const prodModal = new bootstrap.Modal(document.getElementById('prodModal'));

function prepAddProd() {
    document.getElementById('prodModalTitle').textContent = 'Add New Product';
    document.getElementById('prodAction').value = 'add_product';
    document.getElementById('prodId').value = '';
    document.getElementById('prodName').value = '';
    document.getElementById('prodPrice').value = '';
}

function prepEditProd(p) {
    document.getElementById('prodModalTitle').textContent = 'Edit Product';
    document.getElementById('prodAction').value = 'update_product';
    document.getElementById('prodId').value = p.id;
    document.getElementById('prodName').value = p.name;
    document.getElementById('prodCat').value = p.category;
    document.getElementById('prodPrice').value = p.price;
    prodModal.show();
}
</script>
</body></html>