<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$branch = $_SESSION['branch_id'];

/* Status filter */
$allowed = ['active','completed','cancelled','backjobs','Appointment','Initial Payment','On-going','For Delivery'];
$sf = $_GET['status'] ?? '';
if (!in_array($sf, $allowed)) $sf = '';

$where = match($sf) {
    'active'    => "AND o.status IN ('Appointment','Initial Payment','On-going','For Delivery')",
    'completed' => "AND o.status = 'Completed'",
    'cancelled' => "AND o.status = 'Cancelled'",
    'backjobs'  => "AND o.status = 'Backjobs'",
    default     => ''
};

/* Pagination */
$limit  = 10;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$totalOrders = $conn->query("SELECT COUNT(DISTINCT o.id) total FROM custom_orders o WHERE o.branch_id=$branch $where")->fetch_assoc()['total'];
$totalPages  = (int)ceil($totalOrders / $limit);

/* Orders */
$orders = $conn->query("
    SELECT o.id, o.status, o.order_type, o.customer_name, o.created_at, o.expected_date,
           u.name staff_name,
           GROUP_CONCAT(CONCAT(i.name,' x',oi2.qty) SEPARATOR ', ') products
    FROM custom_orders o
    LEFT JOIN users u ON u.id = o.user_id
    LEFT JOIN (SELECT order_id,item_id,SUM(quantity) qty FROM order_items GROUP BY order_id,item_id) oi2 ON oi2.order_id=o.id
    LEFT JOIN items i ON i.id=oi2.item_id
    WHERE o.branch_id=$branch $where
    GROUP BY o.id ORDER BY o.created_at DESC
    LIMIT $limit OFFSET $offset
");

$pageTitle = match($sf) { 'active'=>'Active Orders', 'completed'=>'Completed Orders', 'cancelled'=>'Cancelled Orders', 'backjobs'=>'Backjobs', default=>'All Custom Orders' };
?>

<div class="rh-main">

    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1><?= $pageTitle ?></h1>
            <p><?= $totalOrders ?> order(s) found</p>
        </div>
        <button class="btn btn-outline-secondary" onclick="history.back()">
            <i class="fas fa-arrow-left me-1"></i>Back
        </button>
    </div>

    <!-- FILTER TABS -->
    <div class="rh-tabs mb-4">
        <a href="?status=" class="rh-tab <?= $sf===''?'active':'' ?>">All</a>
        <a href="?status=active" class="rh-tab <?= $sf==='active'?'active':'' ?>">Active</a>
        <a href="?status=completed" class="rh-tab <?= $sf==='completed'?'active':'' ?>">Completed</a>
        <a href="?status=backjobs" class="rh-tab <?= $sf==='backjobs'?'active':'' ?>">Backjobs</a>
        <a href="?status=cancelled" class="rh-tab <?= $sf==='cancelled'?'active':'' ?>">Cancelled</a>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Items / Products</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <?php while ($o = $orders->fetch_assoc()):
                        $cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$o['status']));
                    ?>
                    <tr>
                        <td class="text-muted small">#<?= $o['id'] ?></td>
                        <td>
                            <div class="fw-700"><?= htmlspecialchars($o['customer_name'] ?? '—') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($o['staff_name'] ?? '') ?></div>
                        </td>
                        <td style="max-width:220px;">
                            <div class="text-truncate small" title="<?= htmlspecialchars($o['products'] ?? '') ?>">
                                <?= htmlspecialchars($o['products'] ?? 'No items') ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= ucfirst($o['order_type'] ?? '—') ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $cls ?>"><?= $o['status'] ?></span>
                        </td>
                        <td class="small text-muted">
                            <?= $o['created_at'] ? date('M d, Y', strtotime($o['created_at'])) : '—' ?>
                        </td>
                        <td class="small text-muted">
                            <?= !empty($o['expected_date']) ? date('M d, Y', strtotime($o['expected_date'])) : '—' ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <?php if ($_SESSION['role'] === 'staff'): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        Status
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php foreach (['Appointment','Initial Payment','On-going','For Delivery','Backjobs','Completed','Cancelled'] as $st): ?>
                                        <li>
                                            <form method="POST" action="update_status.php">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $st ?>">
                                                <button type="submit" class="dropdown-item <?= $o['status']===$st?'fw-800 text-amber':'' ?>">
                                                    <?= $st === $o['status'] ? '✓ ' : '' ?><?= $st ?>
                                                </button>
                                            </form>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                                <a href="view_order.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-dark">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-2 mb-2 d-block opacity-25"></i>
                            No orders found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?></small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?= $sf ?>&page=<?= $page-1 ?>">‹ Prev</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
                        <li class="page-item <?= $i==$page?'active':'' ?>">
                            <a class="page-link" href="?status=<?= $sf ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?= $sf ?>&page=<?= $page+1 ?>">Next ›</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    </div>
</div>

</body></html>