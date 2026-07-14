<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$branch = $_SESSION['branch_id'];

/* Status filter */
$allowed = ['active','completed','cancelled','backjobs'];
$sf = $_GET['status'] ?? '';
if (!in_array($sf, $allowed)) $sf = '';

$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to']   ?? '';

$where = "WHERE o.branch_id=$branch";
$where .= match($sf) {
    'active'    => " AND o.status IN ('Appointment','Initial Payment','On-going','For Delivery')",
    'completed' => " AND o.status = 'Completed'",
    'cancelled' => " AND o.status = 'Cancelled'",
    'backjobs'  => " AND o.status = 'Backjobs'",
    default     => ''
};
if ($dateFrom) $where .= " AND DATE(o.created_at) >= '" . $conn->real_escape_string($dateFrom) . "'";
if ($dateTo)   $where .= " AND DATE(o.created_at) <= '" . $conn->real_escape_string($dateTo) . "'";

/* Orders - load all (DataTables handles pagination/search) */
$orders = $conn->query("
    SELECT o.id, o.status, o.order_type, o.customer_name, o.created_at, o.expected_date,
           o.payment_status, o.quote_status,
           u.name staff_name,
           w.name welder_name,
           GROUP_CONCAT(CONCAT(i.name,' x',oi2.qty) SEPARATOR ', ') products
    FROM custom_orders o
    LEFT JOIN users u ON u.id = o.user_id
    LEFT JOIN users w ON w.id = o.assigned_welder_id
    LEFT JOIN (SELECT order_id,item_id,SUM(quantity) qty FROM order_items GROUP BY order_id,item_id) oi2 ON oi2.order_id=o.id
    LEFT JOIN items i ON i.id=oi2.item_id
    $where
    GROUP BY o.id ORDER BY o.created_at DESC
");
$totalOrders = $orders ? $orders->num_rows : 0;

$pageTitle = match($sf) { 'active'=>'Active Projects', 'completed'=>'Completed Projects', 'cancelled'=>'Cancelled Projects', 'backjobs'=>'Backjobs', default=>'All Custom Orders & Projects' };
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
    <div class="rh-tabs mb-3">
        <a href="?status=" class="rh-tab <?= $sf===''?'active':'' ?>">All</a>
        <a href="?status=active" class="rh-tab <?= $sf==='active'?'active':'' ?>">Active</a>
        <a href="?status=completed" class="rh-tab <?= $sf==='completed'?'active':'' ?>">Completed</a>
        <a href="?status=backjobs" class="rh-tab <?= $sf==='backjobs'?'active':'' ?>">Backjobs</a>
        <a href="?status=cancelled" class="rh-tab <?= $sf==='cancelled'?'active':'' ?>">Cancelled</a>
    </div>

    <!-- DATE FILTER -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-2">
            <form method="GET" class="d-flex flex-wrap align-items-end gap-2">
                <input type="hidden" name="status" value="<?= $sf ?>">
                <div>
                    <label class="form-label small fw-700 mb-1">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div>
                    <label class="form-label small fw-700 mb-1">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <button type="submit" class="btn btn-sm btn-dark fw-700"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="?status=<?= $sf ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="ordersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Items / Products</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Welder</th>
                        <th>Booked</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <?php while ($o = $orders->fetch_assoc()):
                        $cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$o['status']));
                        $payBadge = match($o['payment_status'] ?? 'Unpaid') {
                            'Paid'                 => '<span class="badge bg-success-subtle text-success">Paid</span>',
                            'Pending Verification' => '<span class="badge bg-warning-subtle text-warning">Awaiting Verification</span>',
                            default                => '<span class="badge bg-light text-muted border">Unpaid</span>'
                        };
                    ?>
                    <tr>
                        <td class="text-muted small">#<?= $o['id'] ?></td>
                        <td>
                            <div class="fw-700"><?= htmlspecialchars($o['customer_name'] ?? '—') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($o['staff_name'] ?? '') ?></div>
                        </td>
                        <td style="max-width:180px;">
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
                        <td><?= $payBadge ?></td>
                        <td class="small text-muted"><?= htmlspecialchars($o['welder_name'] ?? '—') ?></td>
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
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-2 mb-2 d-block opacity-25"></i>
                            No orders found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#ordersTable').DataTable({
        pageLength: 15,
        order: [[7, 'desc']],
        columnDefs: [{ orderable: false, targets: [9] }],
        language: { search: 'Quick Search:', lengthMenu: 'Show _MENU_ entries' }
    });
});
</script>
</body></html>