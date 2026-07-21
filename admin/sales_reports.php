<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit; }

$branch = $_SESSION['branch_id'];
$period = $_GET['period'] ?? 'monthly';
$welderFilter = $_GET['welder_filter'] ?? 'all';

// --- SALES REVENUE TREND DATA ---
function salesData($conn, $branch, $period) {
    $sel = match($period) {
        'daily'  => "DATE_FORMAT(oi.created_at,'%b %d') AS lbl",
        'weekly' => "CONCAT('W', WEEK(oi.created_at)) AS lbl",
        'yearly' => "YEAR(oi.created_at) AS lbl",
        default  => "DATE_FORMAT(oi.created_at,'%b %Y') AS lbl"
    };
    $lim = match($period) { 'daily' => 30, 'weekly' => 12, 'yearly' => 5, default => 12 };
    
    $res = $conn->query("
        SELECT $sel, SUM(oi.total_amount) tot 
        FROM order_items oi 
        JOIN custom_orders co ON co.id = oi.order_id 
        WHERE co.branch_id = $branch 
        GROUP BY lbl 
        ORDER BY MIN(oi.created_at) DESC 
        LIMIT $lim
    ");
    $l = []; $v = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $l[] = $r['lbl'];
            $v[] = (float)$r['tot'];
        }
    }
    return ['labels' => array_reverse($l), 'values' => array_reverse($v)];
}

$sales = salesData($conn, $branch, $period);

// --- DETAILED REVENUE BREAKDOWNS ---
// Today (Daily)
$dailyRevenue = (float)$conn->query("
    SELECT SUM(oi.total_amount) AS tot 
    FROM order_items oi 
    JOIN custom_orders co ON co.id = oi.order_id 
    WHERE co.branch_id = $branch AND DATE(oi.created_at) = CURDATE()
")->fetch_assoc()['tot'];

// This Week (Weekly)
$weeklyRevenue = (float)$conn->query("
    SELECT SUM(oi.total_amount) AS tot 
    FROM order_items oi 
    JOIN custom_orders co ON co.id = oi.order_id 
    WHERE co.branch_id = $branch AND YEARWEEK(oi.created_at, 1) = YEARWEEK(CURDATE(), 1)
")->fetch_assoc()['tot'];

// This Month (Monthly)
$monthlyRevenue = (float)$conn->query("
    SELECT SUM(oi.total_amount) AS tot 
    FROM order_items oi 
    JOIN custom_orders co ON co.id = oi.order_id 
    WHERE co.branch_id = $branch AND MONTH(oi.created_at) = MONTH(CURDATE()) AND YEAR(oi.created_at) = YEAR(CURDATE())
")->fetch_assoc()['tot'];

// This Year (Yearly)
$yearlyRevenue = (float)$conn->query("
    SELECT SUM(oi.total_amount) AS tot 
    FROM order_items oi 
    JOIN custom_orders co ON co.id = oi.order_id 
    WHERE co.branch_id = $branch AND YEAR(oi.created_at) = YEAR(CURDATE())
")->fetch_assoc()['tot'];

// Global Revenue calculated from sales trend sum
$revenueSum = array_sum($sales['values']);

// Completed and Active counts
$completed = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE branch_id=$branch AND status='Completed'")->fetch_assoc()['c'];
$active    = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE branch_id=$branch AND status NOT IN('Completed','Cancelled')")->fetch_assoc()['c'];

// --- PROJECT SALES BREAKDOWN ---
$projectSalesBreakdown = $conn->query("
    SELECT 
        i.name AS project_name,
        SUM(oi.quantity) AS qty,
        SUM(oi.total_amount) AS total_revenue,
        MIN(oi.created_at) AS date_range
    FROM order_items oi
    JOIN items i ON i.id = oi.item_id
    JOIN custom_orders co ON co.id = oi.order_id
    WHERE co.branch_id = $branch
    GROUP BY i.id
    ORDER BY total_revenue DESC
")->fetch_all(MYSQLI_ASSOC);

// --- STAFF & WELDER LOADS & COMMISSIONS ---
// Welders are paid a 10% commission on completed projects they held
$staffQueryStr = "
    SELECT 
        u.id,
        u.name,
        u.role,
        COUNT(DISTINCT t.id) AS active_projects_count,
        GROUP_CONCAT(DISTINCT t.task_name SEPARATOR ', ') AS projects_handled,
        COALESCE(SUM(CASE WHEN co.status = 'Completed' THEN oi.total_amount * 0.10 ELSE 0 END), 0) AS commission
    FROM users u 
    LEFT JOIN tasks t ON t.assigned_to = u.id
    LEFT JOIN custom_orders co ON co.id = t.order_id
    LEFT JOIN order_items oi ON oi.order_id = co.id
    WHERE u.branch_id = $branch AND u.role = 'welder' AND u.status = 'active'
    GROUP BY u.id
";

// Applying filter for Welders
if ($welderFilter === 'high_commission') {
    $staffQueryStr .= " ORDER BY commission DESC";
} elseif ($welderFilter === 'high_load') {
    $staffQueryStr .= " ORDER BY active_projects_count DESC";
} else {
    $staffQueryStr .= " ORDER BY commission DESC, name ASC";
}

$staffLoad = $conn->query($staffQueryStr)->fetch_all(MYSQLI_ASSOC);
$topCust = $conn->query("SELECT u.name,COUNT(co.id) orders,SUM(oi.total_amount) spent FROM custom_orders co JOIN users u ON u.id=co.customer_id LEFT JOIN order_items oi ON oi.order_id=co.id WHERE co.branch_id=$branch GROUP BY co.customer_id ORDER BY spent DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// --- HIGH-VALUE PROJECTS DRILL-DOWN ---
// Shows the top projects by quoted price + items cost, with welder & customer info
$highValueProjects = $conn->query("
    SELECT
        co.id,
        COALESCE(co.project_name, 'Custom Project') AS project_name,
        co.category,
        co.status,
        co.created_at,
        COALESCE(co.quoted_price, SUM(oi.total_amount), 0) AS project_value,
        co.quoted_price,
        co.quoted_deadline,
        co.quoted_breakdown,
        co.payment_status,
        cust.name AS customer_name,
        welder.name AS welder_name
    FROM custom_orders co
    LEFT JOIN users cust   ON cust.id = co.customer_id
    LEFT JOIN users welder ON welder.id = co.assigned_welder_id
    LEFT JOIN order_items oi ON oi.order_id = co.id
    WHERE co.branch_id = $branch
    GROUP BY co.id
    ORDER BY project_value DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="rh-main">
    <div class="rh-page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1>Financial & Performance Reports</h1>
            <p>Branch: <strong><?= $_SESSION['branch_id'] == 1 ? 'Dasmariñas, Cavite' : 'Biñan, Laguna' ?></strong></p>
        </div>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i>Print Report
        </button>
    </div>

    <!-- REVENUE TIMEFRAME CARDS -->
    <div class="row g-3 mb-4">
        <!-- Daily -->
        <div class="col-6 col-md-3">
            <div class="rh-stat-card border-0 shadow-sm">
                <div class="rh-stat-icon bg-info text-white"><i class="fas fa-calendar-day"></i></div>
                <div>
                    <div class="rh-stat-label">Daily Revenue</div>
                    <div class="rh-stat-value text-info" style="font-size:1.15rem;">₱<?= number_format($dailyRevenue ?: 0, 2) ?></div>
                </div>
            </div>
        </div>
        <!-- Weekly -->
        <div class="col-6 col-md-3">
            <div class="rh-stat-card border-0 shadow-sm">
                <div class="rh-stat-icon bg-purple text-white"><i class="fas fa-calendar-week"></i></div>
                <div>
                    <div class="rh-stat-label">Weekly Revenue</div>
                    <div class="rh-stat-value text-purple" style="font-size:1.15rem;">₱<?= number_format($weeklyRevenue ?: 0, 2) ?></div>
                </div>
            </div>
        </div>
        <!-- Monthly -->
        <div class="col-6 col-md-3">
            <div class="rh-stat-card border-0 shadow-sm">
                <div class="rh-stat-icon bg-green text-white"><i class="fas fa-calendar-days"></i></div>
                <div>
                    <div class="rh-stat-label">Monthly Revenue</div>
                    <div class="rh-stat-value text-success" style="font-size:1.15rem;">₱<?= number_format($monthlyRevenue ?: 0, 2) ?></div>
                </div>
            </div>
        </div>
        <!-- Yearly -->
        <div class="col-6 col-md-3">
            <div class="rh-stat-card border-0 shadow-sm">
                <div class="rh-stat-icon bg-amber text-white"><i class="fas fa-calendar"></i></div>
                <div>
                    <div class="rh-stat-label">Yearly Revenue</div>
                    <div class="rh-stat-value text-warning" style="font-size:1.15rem;">₱<?= number_format($yearlyRevenue ?: 0, 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRAPH AND FILTER -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span class="fw-800 text-light-emphasis"><i class="fas fa-chart-line me-2 text-amber"></i><?= ucfirst($period) ?> Revenue Analytics</span>
            <div class="rh-tabs m-0 border-0" style="padding:0;">
                <a href="?period=daily" class="rh-tab small py-1 px-3 <?= $period==='daily'?'active':'' ?>">Daily</a>
                <a href="?period=weekly" class="rh-tab small py-1 px-3 <?= $period==='weekly'?'active':'' ?>">Weekly</a>
                <a href="?period=monthly" class="rh-tab small py-1 px-3 <?= $period==='monthly'?'active':'' ?>">Monthly</a>
                <a href="?period=yearly" class="rh-tab small py-1 px-3 <?= $period==='yearly'?'active':'' ?>">Yearly</a>
            </div>
        </div>
        <div class="card-body">
            <canvas id="salesChart" height="70"></canvas>
        </div>
    </div>

    <!-- SALES BREAKDOWN TABLE -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="m-0 fw-800 text-light-emphasis"><i class="fas fa-table-list me-2 text-amber"></i>Custom Project Revenue Share (Breakdown of Total Sales)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Project Name / Category</th>
                        <th>Allocated Sales (Units)</th>
                        <th>Date of First Sale</th>
                        <th class="text-end pe-4">Total Revenue Share</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($projectSalesBreakdown)): ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">No custom project sales recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($projectSalesBreakdown as $ps): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-700 text-light-emphasis"><?= htmlspecialchars($ps['project_name']) ?></div>
                            <span class="text-muted small" style="font-size:0.75rem;">Custom Order Material Fabrication</span>
                        </td>
                        <td class="fw-600 text-light-emphasis"><?= $ps['qty'] ?> orders</td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($ps['date_range'])) ?></td>
                        <td class="text-end pe-4 fw-800 text-success">₱<?= number_format($ps['total_revenue'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STAFF / WELDER PROJECT LOAD & COMMISSIONS -->
    <div class="row g-4 mb-4">
        
        <!-- WELDERS AND COMMISSIONS -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="m-0 fw-800 text-light-emphasis"><i class="fas fa-hard-hat me-2 text-amber"></i>Welder Performance & Commission</h5>
                    
                    <!-- Welder Filter Dropdown -->
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="period" value="<?= $period ?>">
                        <select name="welder_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" <?= $welderFilter==='all'?'selected':'' ?>>All Welders</option>
                            <option value="high_commission" <?= $welderFilter==='high_commission'?'selected':'' ?>>Highest Commission First</option>
                            <option value="high_load" <?= $welderFilter==='high_load'?'selected':'' ?>>Highest Project Load First</option>
                        </select>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Welder Name</th>
                                <th>Active Projects</th>
                                <th>Handled Orders</th>
                                <th class="text-end pe-4">Commission Earned (10%)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($staffLoad)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No welder performance data recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($staffLoad as $s): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-700 text-light-emphasis"><?= htmlspecialchars($s['name']) ?></div>
                                    <span class="badge bg-amber-subtle text-amber small" style="font-size:0.65rem;">Welding Expert</span>
                                </td>
                                <td class="fw-600 text-light-emphasis"><?= $s['active_projects_count'] ?> active jobs</td>
                                <td class="small text-muted text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($s['projects_handled'] ?? '') ?>">
                                    <?= htmlspecialchars($s['projects_handled'] ?? 'No jobs assigned yet') ?>
                                </td>
                                <td class="text-end pe-4 fw-800 text-success">
                                    ₱<?= number_format($s['commission'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TOP SPENDING CUSTOMERS -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="m-0 fw-800 text-light-emphasis"><i class="fas fa-crown me-2 text-amber"></i>Top Spending Customers</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Customer</th>
                                <th>Orders</th>
                                <th class="text-end pe-4">Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($topCust)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">No customer data.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topCust as $c): ?>
                            <tr>
                                <td class="ps-4 fw-700 text-light-emphasis"><?= htmlspecialchars($c['name']) ?></td>
                                <td class="fw-600 text-light-emphasis"><?= $c['orders'] ?> custom jobs</td>
                                <td class="text-end pe-4 fw-800 text-success">₱<?= number_format($c['spent'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Line chart loading with dynamic color variables adapted to night mode
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($sales['labels']) ?>,
        datasets: [{
            label: 'Sales Revenue (₱)',
            data: <?= json_encode($sales['values']) ?>,
            borderColor: '#F59E0B',
            backgroundColor: 'rgba(245, 158, 11, 0.08)',
            borderWidth: 3,
            tension: 0.35,
            fill: true,
            pointBackgroundColor: '#F59E0B',
            pointBorderColor: '#ffffff',
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0F172A',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                callbacks: {
                    label: c => ' ₱' + c.parsed.y.toLocaleString()
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#64748B' }
            },
            y: {
                grid: { color: 'rgba(100, 116, 139, 0.08)' },
                ticks: {
                    color: '#64748B',
                    callback: v => '₱' + v.toLocaleString()
                }
            }
        }
    }
});
</script>
</body>
</html>
