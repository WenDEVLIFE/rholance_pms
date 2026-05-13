<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { header("Location: ../index.php"); exit; }

$branch = $_SESSION['branch_id'] ?? 1;

$items = $conn->prepare("
    SELECT i.id, i.name, i.category, i.price,
           COALESCE(inv.current_stock, 0) AS stock
    FROM items i
    LEFT JOIN inventory inv ON inv.item_id = i.id AND inv.branch_id = ?
    WHERE i.name IS NOT NULL AND i.price IS NOT NULL
    ORDER BY i.category ASC, i.name ASC
");
$items->bind_param("i", $branch);
$items->execute();
$rows = $items->get_result()->fetch_all(MYSQLI_ASSOC);

/* Group by category */
$grouped = [];
foreach ($rows as $r) {
    $cat = $r['category'] ?? 'Uncategorized';
    $grouped[$cat][] = $r;
}

/* Counts */
$total = count($rows);
$low   = count(array_filter($rows, fn($r) => $r['stock'] > 0 && $r['stock'] < 10));
$out   = count(array_filter($rows, fn($r) => $r['stock'] == 0));
$ok    = $total - $low - $out;
?>

<div class="rh-main">

    <!-- PAGE HEADER -->
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>Materials Inventory</h1>
            <p>Stock levels for your branch — read-only view.</p>
        </div>
        <!-- Live search -->
        <div style="width:260px;">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="invSearch" class="form-control" placeholder="Search material...">
            </div>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-dark"><i class="fas fa-boxes-stacking"></i></div>
                <div><div class="rh-stat-label">Total Items</div><div class="rh-stat-value"><?= $total ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-green"><i class="fas fa-check-circle"></i></div>
                <div><div class="rh-stat-label">In Stock</div><div class="rh-stat-value text-success"><?= $ok ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-amber"><i class="fas fa-exclamation-triangle"></i></div>
                <div><div class="rh-stat-label">Low Stock</div><div class="rh-stat-value text-warning"><?= $low ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-red"><i class="fas fa-ban"></i></div>
                <div><div class="rh-stat-label">Out of Stock</div><div class="rh-stat-value text-danger"><?= $out ?></div></div>
            </div>
        </div>
    </div>

    <!-- PER-CATEGORY TABLES -->
    <?php foreach ($grouped as $cat => $catItems): ?>
    <div class="mb-4 cat-block">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-dark fw-700"><?= htmlspecialchars($cat) ?></span>
            <span class="text-muted small"><?= count($catItems) ?> items</span>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Material Name</th>
                            <th>Unit Price</th>
                            <th>Stock (Branch)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($catItems as $idx => $item):
                        if ($item['stock'] == 0)       { $sc = 'stock-zero'; $sl = 'Out of Stock'; }
                        elseif ($item['stock'] < 10)   { $sc = 'stock-low';  $sl = 'Low Stock';    }
                        else                           { $sc = 'stock-ok';   $sl = 'In Stock';     }
                    ?>
                        <tr class="inv-row">
                            <td class="text-muted small"><?= $idx+1 ?></td>
                            <td class="fw-600 inv-name"><?= htmlspecialchars($item['name']) ?></td>
                            <td>₱<?= number_format($item['price'],2) ?></td>
                            <td><?= $item['stock'] ?> units</td>
                            <td><span class="badge <?= $sc ?>"><?= $sl ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<script>
/* Live search */
document.getElementById('invSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.inv-row').forEach(row => {
        row.style.display = row.querySelector('.inv-name').textContent.toLowerCase().includes(q) ? '' : 'none';
    });
    document.querySelectorAll('.cat-block').forEach(block => {
        const visible = [...block.querySelectorAll('.inv-row')].some(r => r.style.display !== 'none');
        block.style.display = visible ? '' : 'none';
    });
});
</script>
</body></html>