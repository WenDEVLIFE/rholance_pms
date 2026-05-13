<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit; }

$branch = $_SESSION['branch_id'] ?? 1;

$items = $conn->query("
    SELECT i.id, i.name, i.category, i.price,
           COALESCE(inv.current_stock, 0) AS stock,
           COALESCE(inv.min_stock, 5) AS min_stock
    FROM items i
    LEFT JOIN inventory inv ON inv.item_id = i.id AND inv.branch_id = $branch
    ORDER BY i.category ASC, i.name ASC
");

$rows = $items ? $items->fetch_all(MYSQLI_ASSOC) : [];

/* Group by category */
$grouped = [];
foreach ($rows as $r) {
    $cat = $r['category'] ?? 'Uncategorized';
    $grouped[$cat][] = $r;
}

$totalCount = count($rows);
$lowStock   = count(array_filter($rows, fn($r) => $r['stock'] > 0 && $r['stock'] <= $r['min_stock']));
$outOfStock = count(array_filter($rows, fn($r) => $r['stock'] == 0));
?>

<div class="rh-main">
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1>Inventory Management</h1>
            <p>Monitor and manage stock levels for <strong><?= $branch == 1 ? 'Dasmariñas Branch' : 'Biñan Branch' ?></strong></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print Report
            </button>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="prepAddItem()">
                <i class="fas fa-plus-circle me-1"></i>Add Material
            </button>
        </div>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="rh-stat-card border-0 shadow-sm">
                <div class="rh-stat-icon bg-dark text-white"><i class="fas fa-boxes-stacked"></i></div>
                <div><div class="rh-stat-label">Unique Items</div><div class="rh-stat-value"><?= $totalCount ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card border-0 shadow-sm">
                <div class="rh-stat-icon bg-green text-white"><i class="fas fa-check-double"></i></div>
                <div><div class="rh-stat-label">In Stock</div><div class="rh-stat-value text-success"><?= $totalCount - $lowStock - $outOfStock ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card border-0 shadow-sm">
                <div class="rh-stat-icon bg-amber text-white"><i class="fas fa-triangle-exclamation"></i></div>
                <div><div class="rh-stat-label">Low Stock</div><div class="rh-stat-value text-warning"><?= $lowStock ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card border-0 shadow-sm">
                <div class="rh-stat-icon bg-red text-white"><i class="fas fa-circle-xmark"></i></div>
                <div><div class="rh-stat-label">Out of Stock</div><div class="rh-stat-value text-danger"><?= $outOfStock ?></div></div>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center g-2">
                <div class="col-md-6">
                    <div class="input-group input-group-sm border-0">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="adminInvSearch" class="form-control border-0" placeholder="Search by material name or category...">
                    </div>
                </div>
                <div class="col-md-6 text-md-end text-muted small">
                    <i class="fas fa-info-circle me-1"></i>Showing inventory for current active branch
                </div>
            </div>
        </div>
    </div>

    <!-- INVENTORY TABLES -->
    <?php if (empty($grouped)): ?>
        <div class="card p-5 text-center text-muted border-0 shadow-sm">
            <i class="fas fa-box-open fs-1 mb-3 opacity-25"></i>
            <h4>No Inventory Data</h4>
            <p>Add items to the system or record a stock entry to get started.</p>
        </div>
    <?php else: ?>
        <?php foreach ($grouped as $cat => $catItems): ?>
        <div class="mb-5 cat-section">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="bg-amber text-dark fw-800 px-3 py-1 rounded-pill small"><?= htmlspecialchars($cat) ?></div>
                <div class="text-muted small"><?= count($catItems) ?> Items</div>
                <div class="flex-grow-1 border-bottom" style="opacity:0.1"></div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Material Name</th>
                                <th>Category</th>
                                <th>Unit Price</th>
                                <th>Stock Level</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($catItems as $item):
                                $statusCls = 'bg-success';
                                $statusTxt = 'Available';
                                if ($item['stock'] == 0) {
                                    $statusCls = 'bg-danger'; $statusTxt = 'Out of Stock';
                                } elseif ($item['stock'] <= $item['min_stock']) {
                                    $statusCls = 'bg-warning text-dark'; $statusTxt = 'Low Stock';
                                }
                            ?>
                            <tr class="inv-row" data-name="<?= strtolower($item['name']) ?>" data-cat="<?= strtolower($cat) ?>">
                                <td class="ps-4">
                                    <div class="fw-700"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="text-muted small">ID: #<?= $item['id'] ?></div>
                                </td>
                                <td><span class="text-muted small"><?= htmlspecialchars($cat) ?></span></td>
                                <td class="fw-600">₱<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <div class="fw-800 fs-5"><?= $item['stock'] ?></div>
                                    <div class="text-muted" style="font-size:0.65rem;">THRESHOLD: <?= $item['min_stock'] ?></div>
                                </td>
                                <td><span class="badge <?= $statusCls ?>"><?= $statusTxt ?></span></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-dark" title="Edit Item" 
                                                onclick='prepEditItem(<?= json_encode($item) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-dark" title="Stock Adjustment"
                                                onclick='prepStock(<?= json_encode($item) ?>)'>
                                            <i class="fas fa-boxes-packing"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- FEEDBACK -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

</div>

<!-- ITEM MODAL (ADD/EDIT) -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800" id="itemModalTitle">Add New Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_inventory.php" method="POST">
                <input type="hidden" name="action" id="itemFormAction" value="add_item">
                <input type="hidden" name="item_id" id="formItemId">
                <input type="hidden" name="branch_id" value="<?= $branch ?>">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-700">Material Name</label>
                            <input type="text" name="name" id="formItemName" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Category</label>
                            <select name="category" id="formItemCat" class="form-select" required>
                                <option value="Industrial Materials">Industrial Materials</option>
                                <option value="Tools">Tools</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Fabricated Product">Fabricated Product</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Unit Price (₱)</label>
                            <input type="number" step="0.01" name="price" id="formItemPrice" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Min. Threshold</label>
                            <input type="number" name="min_stock" id="formItemMin" class="form-control" value="5" required>
                        </div>
                        <div id="initStockRow" class="col-12">
                            <label class="form-label small fw-700">Initial Stock</label>
                            <input type="number" name="initial_stock" id="formItemInit" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-800" id="itemSubmitBtn">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- STOCK ADJUST MODAL -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-amber text-dark">
                <h5 class="modal-title fw-800">Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_inventory.php" method="POST">
                <input type="hidden" name="action" value="stock_adjust">
                <input type="hidden" name="item_id" id="adjItemId">
                <input type="hidden" name="branch_id" value="<?= $branch ?>">
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="text-muted small">Material</div>
                        <div class="fw-800" id="adjItemName"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-700">Type</label>
                        <select name="type" class="form-select fw-700">
                            <option value="in" class="text-success">Stock IN (+)</option>
                            <option value="out" class="text-danger">Stock OUT (-)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-700">Quantity</label>
                        <input type="number" name="qty" class="form-control form-control-lg text-center fw-800" value="1" min="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-700">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Supplier delivery"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-warning w-100 fw-800 py-2">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const itemModal = new bootstrap.Modal(document.getElementById('itemModal'));
const stockModal = new bootstrap.Modal(document.getElementById('stockModal'));

function prepAddItem() {
    document.getElementById('itemModalTitle').textContent = 'Add New Material';
    document.getElementById('itemFormAction').value = 'add_item';
    document.getElementById('initStockRow').classList.remove('d-none');
    document.getElementById('formItemId').value = '';
    document.getElementById('formItemName').value = '';
    document.getElementById('formItemPrice').value = '';
    document.getElementById('formItemMin').value = '5';
}

function prepEditItem(i) {
    document.getElementById('itemModalTitle').textContent = 'Edit Material';
    document.getElementById('itemFormAction').value = 'update_item';
    document.getElementById('initStockRow').classList.add('d-none');
    document.getElementById('formItemId').value = i.id;
    document.getElementById('formItemName').value = i.name;
    document.getElementById('formItemCat').value = i.category;
    document.getElementById('formItemPrice').value = i.price;
    document.getElementById('formItemMin').value = i.min_stock;
    itemModal.show();
}

function prepStock(i) {
    document.getElementById('adjItemId').value = i.id;
    document.getElementById('adjItemName').textContent = i.name;
    stockModal.show();
}

document.getElementById('adminInvSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.inv-row').forEach(row => {
        const text = row.dataset.name + ' ' + row.dataset.cat;
        row.style.display = text.includes(q) ? '' : 'none';
    });
    
    document.querySelectorAll('.cat-section').forEach(section => {
        const visible = [...section.querySelectorAll('.inv-row')].some(r => r.style.display !== 'none');
        section.style.display = visible ? '' : 'none';
    });
});
</script>
</body></html>
