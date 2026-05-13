<?php
require_once '../includes/auth_check.php';
if ($_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit; }

include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$branch = $_SESSION['branch_id'];
$branchName = $branch == 1 ? 'Dasmariñas, Cavite' : 'Biñan, Laguna';

/* ── PIPELINE COUNTS ── */
function cnt($conn,$branch,$status){
    if ($conn->connect_error) return 0;
    $res = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE branch_id=$branch AND status='$status'");
    return $res ? $res->fetch_assoc()['c'] : 0;
}

$appointment = $initial = $ongoing = $delivery = $backjobs = $cancelled = $completed = 0;
$activeOrders = $completedOrders = $totalUsers = $totalInventory = 0;
$appointments = $bestSelling = $statusQuery = $productSales = $recentOrders = false;
$bestLabels = $bestValues = $statusLabels = $statusValues = [];

if (!$conn->connect_error) {
    $appointment = cnt($conn,$branch,'Appointment');
    $initial     = cnt($conn,$branch,'Initial Payment');
    $ongoing     = cnt($conn,$branch,'On-going');
    $delivery    = cnt($conn,$branch,'For Delivery');
    $backjobs    = cnt($conn,$branch,'Backjobs');
    $cancelled   = cnt($conn,$branch,'Cancelled');
    $completed   = cnt($conn,$branch,'Completed');

    $activeOrders    = ($res = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE branch_id=$branch AND status NOT IN ('Completed','Cancelled')")) ? $res->fetch_assoc()['c'] : 0;
    $completedOrders = ($res = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE branch_id=$branch AND status='Completed'")) ? $res->fetch_assoc()['c'] : 0;
    $totalUsers      = ($res = $conn->query("SELECT COUNT(*) c FROM users WHERE branch_id=$branch")) ? $res->fetch_assoc()['c'] : 0;
    $totalInventory  = ($res = $conn->query("SELECT COUNT(*) c FROM inventory WHERE branch_id=$branch")) ? $res->fetch_assoc()['c'] : 0;

    $appointments = $conn->query("SELECT a.*,b.name branch_name FROM appointments a LEFT JOIN branches b ON b.id=a.branch_id ORDER BY a.appointment_date ASC LIMIT 8");

    $bestSelling = $conn->query("SELECT i.name,SUM(oi.quantity) qty FROM order_items oi JOIN items i ON i.id=oi.item_id WHERE oi.order_id IN (SELECT id FROM custom_orders WHERE branch_id=$branch) GROUP BY i.name ORDER BY qty DESC LIMIT 5");
    if($bestSelling) while($r=$bestSelling->fetch_assoc()){$bestLabels[]=$r['name'];$bestValues[]=(int)$r['qty'];}

    $statusQuery = $conn->query("SELECT status,COUNT(*) total FROM custom_orders WHERE branch_id=$branch GROUP BY status");
    if($statusQuery) while($r=$statusQuery->fetch_assoc()){$statusLabels[]=$r['status'];$statusValues[]=(int)$r['total'];}

    $productSales = $conn->query("SELECT i.name,SUM(oi.quantity) qty,SUM(oi.total_amount) total FROM order_items oi JOIN items i ON i.id=oi.item_id WHERE oi.order_id IN (SELECT id FROM custom_orders WHERE branch_id=$branch) GROUP BY i.name ORDER BY qty DESC LIMIT 10");

    $recentOrders = $conn->query("SELECT co.id,co.status,GROUP_CONCAT(i.name SEPARATOR ', ') products,co.customer_name,co.created_at FROM custom_orders co LEFT JOIN order_items oi ON oi.order_id=co.id LEFT JOIN items i ON i.id=oi.item_id WHERE co.branch_id=$branch GROUP BY co.id ORDER BY co.created_at DESC LIMIT 5");
}

if(empty($bestLabels)){$bestLabels=['No Data'];$bestValues=[0];}
if(empty($statusLabels)){$statusLabels=['No Data'];$statusValues=[0];}
?>

<div class="rh-main">

    <!-- PAGE HEADER -->
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h1>Admin Dashboard</h1>
            <p><i class="fas fa-map-marker-alt me-1 text-amber"></i><?= $branchName ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="../orders/orders.php" class="btn btn-primary">
                <i class="fas fa-clipboard-list me-1"></i>Manage Orders
            </a>
            <a href="sales_reports.php" class="btn btn-outline-secondary">
                <i class="fas fa-chart-line me-1"></i>Reports
            </a>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="../orders/orders.php?status=active" class="rh-stat-card text-decoration-none d-flex">
                <div class="rh-stat-icon bg-blue"><i class="fas fa-box-open"></i></div>
                <div><div class="rh-stat-label">Active Orders</div><div class="rh-stat-value"><?= $activeOrders ?></div></div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="../orders/orders.php?status=completed" class="rh-stat-card text-decoration-none d-flex">
                <div class="rh-stat-icon bg-green"><i class="fas fa-circle-check"></i></div>
                <div><div class="rh-stat-label">Completed</div><div class="rh-stat-value"><?= $completedOrders ?></div></div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="user_management.php" class="rh-stat-card text-decoration-none d-flex">
                <div class="rh-stat-icon bg-purple"><i class="fas fa-users"></i></div>
                <div><div class="rh-stat-label">Users</div><div class="rh-stat-value"><?= $totalUsers ?></div></div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="../inventory/index.php" class="rh-stat-card text-decoration-none d-flex">
                <div class="rh-stat-icon bg-amber"><i class="fas fa-boxes-stacked"></i></div>
                <div><div class="rh-stat-label">Inventory Items</div><div class="rh-stat-value"><?= $totalInventory ?></div></div>
            </a>
        </div>
    </div>

    <!-- ORDER PIPELINE -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-route me-2 text-amber"></i>Order Pipeline</div>
        <div class="card-body">
            <div class="d-flex gap-2 overflow-auto pb-2" style="scrollbar-width:thin;">
                <?php
                $pipe = [
                    ['Appointment',   $appointment, 'bg-secondary', 'fa-calendar-check'],
                    ['Initial Payment',$initial,    'bg-primary',   'fa-money-bill-wave'],
                    ['On-going',      $ongoing,     'bg-warning text-dark','fa-gear'],
                    ['For Delivery',  $delivery,    'bg-success',   'fa-truck'],
                    ['Backjobs',      $backjobs,    'bg-danger',    'fa-rotate-left'],
                    ['Cancelled',     $cancelled,   'bg-dark',      'fa-ban'],
                    ['Completed',     $completed,   'bg-success',   'fa-flag-checkered'],
                ];
                foreach ($pipe as [$label, $count, $bg, $icon]): ?>
                <a href="../orders/orders.php?status=<?= urlencode($label) ?>"
                   class="text-decoration-none flex-shrink-0 text-center" style="min-width:100px;">
                    <div class="badge <?= $bg ?> d-flex flex-column align-items-center gap-1 p-3 rounded-3 mb-1 w-100">
                        <i class="fas <?= $icon ?> fs-5"></i>
                        <span class="fs-4 fw-800 lh-1"><?= $count ?></span>
                    </div>
                    <div class="small fw-600 text-muted"><?= $label ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="row g-4 mb-4">
        <!-- BEST SELLING -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="fas fa-fire me-2 text-amber"></i>Top Selling Items</div>
                <div class="card-body"><canvas id="bestChart" height="160"></canvas></div>
            </div>
        </div>
        <!-- STATUS DONUT -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="fas fa-chart-pie me-2 text-amber"></i>Order Status Distribution</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="statusChart" style="max-height:220px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- SALES CHART -->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span><i class="fas fa-chart-line me-2 text-amber"></i>Sales Graph</span>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary filter-btn" data-filter="daily"   onclick="updateChart(event,'daily')">Daily</button>
                <button class="btn btn-sm btn-outline-secondary filter-btn" data-filter="monthly" onclick="updateChart(event,'monthly')">Monthly</button>
                <button class="btn btn-sm btn-outline-secondary filter-btn active bg-dark text-white" data-filter="yearly" onclick="updateChart(event,'yearly')">Yearly</button>
            </div>
        </div>
        <div class="card-body"><canvas id="salesChart" height="80"></canvas></div>
    </div>

    <!-- BOTTOM ROW: Appointments + Product Sales -->
    <div class="row g-4 mb-4">

        <!-- APPOINTMENTS TABLE -->
        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-calendar-check me-2 text-amber"></i>All Appointments</span>
                    <a href="../staff/appointment.php" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Customer</th><th>Date</th><th>Time</th><th>Branch</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($appointments && $appointments->num_rows > 0): ?>
                            <?php while ($row = $appointments->fetch_assoc()):
                                $bc = 'badge-'.strtolower($row['status']); ?>
                            <tr>
                                <td class="fw-600"><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td class="small"><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                                <td class="small"><?= htmlspecialchars($row['appointment_time']) ?></td>
                                <td class="small"><?= htmlspecialchars($row['branch_name'] ?? '—') ?></td>
                                <td><span class="badge <?= $bc ?>"><?= $row['status'] ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No appointments found</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PRODUCT SALES TABLE -->
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fas fa-table me-2 text-amber"></i>Product Sales Summary</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Product</th><th>Qty</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($productSales && $productSales->num_rows > 0): ?>
                            <?php while ($r = $productSales->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-600 small"><?= htmlspecialchars($r['name']) ?></td>
                                <td><?= $r['qty'] ?></td>
                                <td class="fw-700 text-success small">₱<?= number_format($r['total'],2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">No data</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- RECENT ORDERS -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-clock-rotate-left me-2 text-amber"></i>Recent Orders</span>
            <a href="../orders/orders.php" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>#</th><th>Customer</th><th>Items</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                    <?php while ($o = $recentOrders->fetch_assoc()):
                        $bc = 'badge-'.strtolower(str_replace([' ','/'],'-',$o['status'])); ?>
                    <tr>
                        <td class="text-muted small">#<?= $o['id'] ?></td>
                        <td class="fw-600"><?= htmlspecialchars($o['customer_name'] ?? '—') ?></td>
                        <td class="small text-truncate" style="max-width:200px;">
                            <?= htmlspecialchars($o['products'] ?? 'No items') ?>
                        </td>
                        <td><span class="badge <?= $bc ?>"><?= $o['status'] ?></span></td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No recent orders</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /.rh-main -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* BEST SELLING BAR CHART */
new Chart(document.getElementById('bestChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($bestLabels) ?>,
        datasets: [{ label: 'Qty Sold', data: <?= json_encode($bestValues) ?>,
            backgroundColor: ['#F59E0B','#10B981','#3B82F6','#8B5CF6','#EF4444'],
            borderRadius: 8 }]
    },
    options: { responsive:true, plugins:{ legend:{display:false} }, scales:{ y:{beginAtZero:true} } }
});

/* STATUS DONUT CHART */
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{ data: <?= json_encode($statusValues) ?>,
            backgroundColor: ['#F59E0B','#10B981','#3B82F6','#8B5CF6','#EF4444','#64748B','#0EA5E9'],
            borderWidth: 0, hoverOffset: 8 }]
    },
    options: { responsive:true, cutout:'65%', plugins:{ legend:{ position:'bottom', labels:{ usePointStyle:true, padding:12, font:{size:11} } } } }
});

/* SALES LINE CHART */
let salesChart;
function loadChart(filter) {
    fetch('../admin/get_sales.php?filter=' + filter)
    .then(r => r.json()).then(data => {
        if (salesChart) salesChart.destroy();
        salesChart = new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{ label:'Sales (₱)', data: data.data,
                    borderColor:'#F59E0B', backgroundColor:'rgba(245,158,11,.1)',
                    tension:.4, fill:true, borderWidth:3,
                    pointBackgroundColor:'#F59E0B', pointRadius:4 }]
            },
            options: { responsive:true, plugins:{ legend:{display:false},
                tooltip:{ backgroundColor:'#0F172A', callbacks:{ label: c=>' ₱'+c.parsed.y.toLocaleString() } }
            }, scales:{ y:{ ticks:{ callback: v=>'₱'+v.toLocaleString() } } } }
        });
    });
}

function updateChart(e, filter) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active','bg-dark','text-white'));
    e.target.classList.add('active','bg-dark','text-white');
    localStorage.setItem('salesFilter', filter);
    loadChart(filter);
}

document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('salesFilter') || 'monthly';
    document.querySelectorAll('.filter-btn').forEach(b => {
        if (b.dataset.filter === saved) b.classList.add('active','bg-dark','text-white');
        else b.classList.remove('active','bg-dark','text-white');
    });
    loadChart(saved);
});
</script>

</body></html>
