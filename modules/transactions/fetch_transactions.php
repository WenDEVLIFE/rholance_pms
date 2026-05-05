<?php
require_once '../../config/database.php';
session_start();

/* =========================
   PAGINATION
========================= */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

/* =========================
   FETCH DATA
========================= */
$query = "
SELECT 
    t.id AS transaction_id,
    t.total_amount,
    t.status,
    t.created_at,
    co.project_name,
    co.category,
    co.material,
    co.image
FROM transactions t
LEFT JOIN custom_orders co ON t.order_id = co.id
ORDER BY t.created_at DESC
LIMIT $limit OFFSET $offset
";

$result = $conn->query($query);

/* =========================
   TOTAL COUNT
========================= */
$countQuery = "SELECT COUNT(*) as total FROM transactions";
$countResult = $conn->query($countQuery);
$total = $countResult->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);
?>

<!-- TRANSACTIONS -->
<?php while($t = $result->fetch_assoc()): ?>

<div class="transaction-card">

    <div class="transaction-header">
        <span>Transaction #<?= $t['transaction_id'] ?></span>
        <span class="status"><?= htmlspecialchars($t['status']) ?></span>
    </div>

    <div class="transaction-body">

        <?php if (!empty($t['project_name'])): ?>

        <div class="item-info">
            <img 
                src="/rholance_pms/assets/images/orders/<?= htmlspecialchars($t['image']) ?>"
                onerror="this.src='/rholance_pms/assets/images/default.png';"
            >
            <div>
                <strong><?= htmlspecialchars($t['project_name']) ?></strong>
                <small><?= htmlspecialchars($t['category']) ?> • <?= htmlspecialchars($t['material']) ?></small>
            </div>
        </div>

        <?php else: ?>

        <div class="item-row empty">
            No linked order data
        </div>

        <?php endif; ?>

    </div>

    <div class="transaction-footer">
        <strong>Total: ₱<?= number_format($t['total_amount'],2) ?></strong>
        <small><?= date("F d, Y • h:i A", strtotime($t['created_at'])) ?></small>
    </div>

</div>

<?php endwhile; ?>

<!-- PAGINATION -->
<div class="pagination">
    <?php for($i = 1; $i <= $totalPages; $i++): ?>
        <button onclick="loadTransactions(<?= $i ?>)" class="<?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </button>
    <?php endfor; ?>
</div>