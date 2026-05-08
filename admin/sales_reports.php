<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit; }

$branch = $_SESSION['branch_id'];
$period = $_GET['period'] ?? 'monthly';

/* ── Helper: build sales data ── */
function salesData($conn, $branch, $period) {
    $select = match($period) {
        'daily'   => "DATE_FORMAT(oi.created_at, '%b %d') AS label, SUM(oi.total_amount) AS total",
        'weekly'  => "CONCAT('Week ', WEEK(oi.created_at)) AS label, SUM(oi.total_amount) AS total",
        'monthly' => "DATE_FORMAT(oi.created_at, '%b %Y') AS label, SUM(oi.total_amount) AS total",
        'yearly'  => "YEAR(oi.created_at) AS label, SUM(oi.total_amount) AS total",
        default   => "DATE_FORMAT(oi.created_at, '%b %Y') AS label, SUM(oi.total_amount) AS total"
    };
    $limit = match($period) { 'daily'=>30, 'weekly'=>12, 'monthly'=>12, 'yearly'=>5, default=>12 };
    $res = $conn->query("
        SELECT $select
        FROM order_items oi
        JOIN custom_orders co ON co.id = oi.order_id
        WHERE co.branch_id = $branch
        GROUP BY label
        ORDER BY MIN(oi.created_at) DESC
        LIMIT $limit
    ");
    $labels = []; $values = [];
    if ($res) { while ($r = $res->fetch_assoc()) { $labels[] = $r['label']; $values[] = (float)$r['total']; } }
    return ['labels' => array_reverse($labels), 'values' => array_reverse($values)];
}

$sales = salesData($conn, $branch, $period);
$totalRevenue = array_sum($sales['values']);

/* ── Staff project loads ── */
$staffLoad = $conn->query("
    SELECT u.name, u.role,
        COUNT(DISTINCT t.order_id) AS active_projects,
        COUNT(DISTINCT CASE WHEN co.status = 'Completed' THEN co.id END) AS completed
    FROM users u
    LEFT JOIN tasks t ON t.assigned_to = u.id
    LEFT JOIN custom_orders co ON co.id = t.order_id
    WHERE u.branch_id = $branch AND u.role IN ('staff','welder') AND u.status = 'active'
    GROUP BY u.id
    ORDER BY active_projects DESC
")->fetch_all(MYSQLI_ASSOC);

/* ── Top customers ── */
$topCustomers = $conn->query("
    SELECT u.name, COUNT(co.id) AS total_orders, SUM(oi.total_amount) AS total_spent
    FROM custom_orders co
    JOIN users u ON u.id = co.customer_id
    LEFT JOIN order_items oi ON oi.order_id = co.id
    WHERE co.branch_id = $branch
    GROUP BY co.customer_id
    ORDER BY total_spent DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Reports – Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.rpt-page { padding:28px 32px; }
.rpt-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.rpt-header h2 { margin:0; font-size:22px; color:#0F172A; }

/* PERIOD TABS */
.period-tabs { display:flex; gap:8px; margin-bottom:24px; }
.p-tab { padding:8px 20px; border-radius:20px; border:2px solid #E2E8F0; font-weight:700; font-size:13px; text-decoration:none; color:#64748B; transition:all .2s; }
.p-tab:hover { border-color:#F59E0B; color:#F59E0B; }
.p-tab.active { background:#0F172A; border-color:#0F172A; color:#fff; }

/* SUMMARY CARDS */
.rpt-summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:28px; }
.rpt-s-card { background:#fff; border-radius:16px; border:1px solid #E2E8F0; padding:20px 24px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.rpt-s-card .rn { font-size:26px; font-weight:800; }
.rpt-s-card .rl { font-size:13px; color:#64748B; }

/* CHART CARD */
.chart-wrap { background:#fff; border-radius:16px; border:1px solid #E2E8F0; padding:24px; margin-bottom:24px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.chart-wrap h3 { margin:0 0 20px; font-size:16px; color:#0F172A; }

/* TWO-COL GRID */
.two-col { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
@media(max-width:900px) { .two-col { grid-template-columns:1fr; } }

/* STAFF TABLE */
.mini-table { width:100%; border-collapse:collapse; }
.mini-table th { font-size:11px; text-transform:uppercase; color:#94A3B8; padding:10px 14px; text-align:left; background:#F8FAFC; border-bottom:1px solid #E2E8F0; }
.mini-table td { padding:12px 14px; font-size:14px; border-bottom:1px solid #F1F5F9; }
.mini-table tr:last-child td { border-bottom:none; }
.mini-table tr:hover td { background:#FFFBEB; }
.load-bar { height:6px; background:#E2E8F0; border-radius:3px; overflow:hidden; }
.load-fill { height:100%; background:#F59E0B; }

/* ROLE TAG */
.role-tag { font-size:11px; font-weight:800; padding:3px 8px; border-radius:6px; }
.rt-staff  { background:#DBEAFE; color:#1E40AF; }
.rt-welder { background:#FEF3C7; color:#92400E; }
</style>
</head>
<body>
<div class="main rpt-page">

    <div class="rpt-header">
        <h2><i class="fas fa-chart-line" style="color:#F59E0B;margin-right:8px;"></i>Sales Reports</h2>
    </div>

    <!-- PERIOD TABS -->
    <div class="period-tabs">
        <?php foreach (['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','yearly'=>'Yearly'] as $k => $v): ?>
            <a href="?period=<?= $k ?>" class="p-tab <?= $period===$k?'active':'' ?>"><?= $v ?></a>
        <?php endforeach; ?>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="rpt-summary">
        <div class="rpt-s-card">
            <div class="rn" style="color:#10B981;">₱<?= number_format($totalRevenue, 2) ?></div>
            <div class="rl">Total Revenue (<?= ucfirst($period) ?>)</div>
        </div>
        <?php
            $txCount = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE branch_id = $branch AND status = 'Completed'")->fetch_assoc()['c'];
            $pending = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE branch_id = $branch AND status NOT IN ('Completed','Cancelled')")->fetch_assoc()['c'];
        ?>
        <div class="rpt-s-card">
            <div class="rn" style="color:#3B82F6;"><?= $txCount ?></div>
            <div class="rl">Completed Orders</div>
        </div>
        <div class="rpt-s-card">
            <div class="rn" style="color:#F59E0B;"><?= $pending ?></div>
            <div class="rl">Active Projects</div>
        </div>
        <div class="rpt-s-card">
            <div class="rn" style="color:#0F172A;"><?= count($staffLoad) ?></div>
            <div class="rl">Active Staff / Welders</div>
        </div>
    </div>

    <!-- SALES CHART -->
    <div class="chart-wrap">
        <h3><i class="fas fa-chart-area" style="color:#F59E0B;margin-right:8px;"></i><?= ucfirst($period) ?> Sales Trend</h3>
        <canvas id="salesChart" height="100"></canvas>
    </div>

    <!-- TWO COLUMN SECTION -->
    <div class="two-col">

        <!-- STAFF PERFORMANCE -->
        <div class="chart-wrap" style="margin-bottom:0;">
            <h3><i class="fas fa-users-cog" style="color:#F59E0B;margin-right:8px;"></i>Staff &amp; Welder Project Load</h3>
            <table class="mini-table">
                <thead><tr><th>Name</th><th>Role</th><th>Active</th><th>Done</th><th>Load</th></tr></thead>
                <tbody>
                <?php if (empty($staffLoad)): ?>
                    <tr><td colspan="5" style="text-align:center;color:#94A3B8;padding:30px;">No data</td></tr>
                <?php else: ?>
                    <?php $maxProj = max(array_column($staffLoad,'active_projects')) ?: 1; ?>
                    <?php foreach ($staffLoad as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                            <td><span class="role-tag rt-<?= $s['role'] ?>"><?= ucfirst($s['role']) ?></span></td>
                            <td><?= $s['active_projects'] ?></td>
                            <td><?= $s['completed'] ?></td>
                            <td style="min-width:80px;">
                                <div class="load-bar">
                                    <div class="load-fill" style="width:<?= round($s['active_projects']/$maxProj*100) ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TOP CUSTOMERS -->
        <div class="chart-wrap" style="margin-bottom:0;">
            <h3><i class="fas fa-star" style="color:#F59E0B;margin-right:8px;"></i>Top Customers by Spending</h3>
            <table class="mini-table">
                <thead><tr><th>Customer</th><th>Orders</th><th>Total Spent</th></tr></thead>
                <tbody>
                <?php if (empty($topCustomers)): ?>
                    <tr><td colspan="3" style="text-align:center;color:#94A3B8;padding:30px;">No data</td></tr>
                <?php else: ?>
                    <?php foreach ($topCustomers as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= $c['total_orders'] ?></td>
                            <td><strong style="color:#10B981;">₱<?= number_format($c['total_spent'],2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<script>
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($sales['labels']) ?>,
        datasets: [{
            label: 'Revenue (₱)',
            data: <?= json_encode($sales['values']) ?>,
            borderColor: '#F59E0B',
            backgroundColor: 'rgba(245,158,11,0.12)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#F59E0B',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0F172A',
                callbacks: {
                    label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2})
                }
            }
        },
        scales: {
            y: { ticks: { callback: v => '₱' + v.toLocaleString() } }
        }
    }
});
</script>
</body>
</html>
