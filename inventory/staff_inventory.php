<?php
require_once '../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) {
    header("Location: ../index.php"); exit;
}

$branch = $_SESSION['branch_id'] ?? null;

/* ── Fetch all items with branch stock ── */
$stmt = $conn->prepare("
    SELECT 
        i.id,
        i.name,
        i.category,
        i.price,
        COALESCE(inv.current_stock, 0) AS stock
    FROM items i
    LEFT JOIN inventory inv ON inv.item_id = i.id AND inv.branch_id = ?
    WHERE i.name IS NOT NULL AND i.name != '' AND i.price IS NOT NULL
    ORDER BY i.category ASC, i.name ASC
");
$stmt->bind_param("i", $branch);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ── Group by category ── */
$grouped = [];
foreach ($items as $item) {
    $cat = $item['category'] ?? 'Uncategorized';
    $grouped[$cat][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory – Staff</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.inv-page { padding:28px 32px; }
.inv-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.inv-header h2 { margin:0; font-size:22px; color:#0F172A; }
.search-bar { display:flex; align-items:center; gap:10px; }
.search-bar input { padding:10px 16px; border-radius:10px; border:1px solid #E2E8F0; font-size:14px; width:260px; }
.search-bar input:focus { outline:none; border-color:#F59E0B; }

.category-block { margin-bottom:32px; }
.category-label { font-size:12px; font-weight:800; text-transform:uppercase; color:#94A3B8; letter-spacing:.8px; margin-bottom:12px; padding-left:4px; }

.inv-table { width:100%; border-collapse:collapse; background:#fff; border-radius:14px; overflow:hidden; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.inv-table th { font-size:11px; text-transform:uppercase; color:#94A3B8; padding:12px 18px; text-align:left; background:#F8FAFC; border-bottom:1px solid #E2E8F0; letter-spacing:.5px; }
.inv-table td { padding:14px 18px; font-size:14px; color:#1E293B; border-bottom:1px solid #F1F5F9; }
.inv-table tr:last-child td { border-bottom:none; }
.inv-table tr:hover td { background:#FFFBEB; }

.stock-pill { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.stock-ok   { background:#D1FAE5; color:#065F46; }
.stock-low  { background:#FEF3C7; color:#92400E; }
.stock-zero { background:#FEE2E2; color:#991B1B; }

.summary-cards { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:16px; margin-bottom:28px; }
.s-card { background:#fff; border-radius:14px; border:1px solid #E2E8F0; padding:18px 22px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.s-card .s-num { font-size:28px; font-weight:800; color:#0F172A; }
.s-card .s-lbl { font-size:13px; color:#64748B; }
</style>
</head>
<body>

<div class="main inv-page">

    <div class="inv-header">
        <h2><i class="fa-solid fa-boxes-stacking" style="color:#F59E0B;margin-right:8px;"></i>Materials Inventory</h2>
        <div class="search-bar">
            <i class="fas fa-search" style="color:#94A3B8;"></i>
            <input type="text" id="invSearch" placeholder="Search material...">
        </div>
    </div>

    <!-- ── SUMMARY ── -->
    <?php
        $total   = count($items);
        $lowStock  = count(array_filter($items, fn($i) => $i['stock'] > 0 && $i['stock'] < 10));
        $outStock  = count(array_filter($items, fn($i) => $i['stock'] == 0));
        $okStock   = $total - $lowStock - $outStock;
    ?>
    <div class="summary-cards">
        <div class="s-card"><div class="s-num"><?= $total ?></div><div class="s-lbl">Total Items</div></div>
        <div class="s-card"><div class="s-num" style="color:#10B981;"><?= $okStock ?></div><div class="s-lbl">In Stock</div></div>
        <div class="s-card"><div class="s-num" style="color:#F59E0B;"><?= $lowStock ?></div><div class="s-lbl">Low Stock (&lt;10)</div></div>
        <div class="s-card"><div class="s-num" style="color:#EF4444;"><?= $outStock ?></div><div class="s-lbl">Out of Stock</div></div>
    </div>

    <!-- ── PER-CATEGORY TABLE ── -->
    <?php foreach ($grouped as $category => $catItems): ?>
    <div class="category-block inv-searchable">
        <div class="category-label"><?= htmlspecialchars($category) ?> (<?= count($catItems) ?>)</div>
        <table class="inv-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Material Name</th>
                    <th>Unit Price</th>
                    <th>Stock (This Branch)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($catItems as $idx => $item): ?>
                <?php
                    if ($item['stock'] == 0)        { $sc = 'stock-zero'; $sl = 'Out of Stock'; }
                    elseif ($item['stock'] < 10)    { $sc = 'stock-low';  $sl = 'Low Stock'; }
                    else                             { $sc = 'stock-ok';   $sl = 'In Stock'; }
                ?>
                <tr class="inv-row">
                    <td style="color:#94A3B8;"><?= $idx + 1 ?></td>
                    <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                    <td>₱<?= number_format($item['price'], 2) ?></td>
                    <td><?= $item['stock'] ?> units</td>
                    <td><span class="stock-pill <?= $sc ?>"><?= $sl ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

</div>

<script>
/* Live search */
document.getElementById('invSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.inv-row').forEach(row => {
        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        row.style.display = name.includes(q) ? '' : 'none';
    });
    /* Hide empty category blocks */
    document.querySelectorAll('.category-block').forEach(block => {
        const visible = [...block.querySelectorAll('.inv-row')].some(r => r.style.display !== 'none');
        block.style.display = visible ? '' : 'none';
    });
});
</script>
</body>
</html>