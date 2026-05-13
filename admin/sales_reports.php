<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit; }

$branch = $_SESSION['branch_id'];
$period = $_GET['period'] ?? 'monthly';

function salesData($conn,$branch,$period){
    $sel = match($period){
        'daily'  => "DATE_FORMAT(oi.created_at,'%b %d') AS lbl",
        'weekly' => "CONCAT('W',WEEK(oi.created_at)) AS lbl",
        'yearly' => "YEAR(oi.created_at) AS lbl",
        default  => "DATE_FORMAT(oi.created_at,'%b %Y') AS lbl"
    };
    $lim = match($period){'daily'=>30,'weekly'=>12,'yearly'=>5,default=>12};
    $res=$conn->query("SELECT $sel,SUM(oi.total_amount) tot FROM order_items oi JOIN custom_orders co ON co.id=oi.order_id WHERE co.branch_id=$branch GROUP BY lbl ORDER BY MIN(oi.created_at) DESC LIMIT $lim");
    $l=[];$v=[];
    if($res) while($r=$res->fetch_assoc()){$l[]=$r['lbl'];$v[]=(float)$r['tot'];}
    return ['labels'=>array_reverse($l),'values'=>array_reverse($v)];
}

$sales = salesData($conn,$branch,$period);
$revenue = array_sum($sales['values']);
$completed = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE branch_id=$branch AND status='Completed'")->fetch_assoc()['c'];
$active    = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE branch_id=$branch AND status NOT IN('Completed','Cancelled')")->fetch_assoc()['c'];

$staffLoad = $conn->query("SELECT u.name,u.role,COUNT(DISTINCT t.order_id) ap,COUNT(DISTINCT CASE WHEN co.status='Completed' THEN co.id END) done FROM users u LEFT JOIN tasks t ON t.assigned_to=u.id LEFT JOIN custom_orders co ON co.id=t.order_id WHERE u.branch_id=$branch AND u.role IN('staff','welder') AND u.status='active' GROUP BY u.id ORDER BY ap DESC")->fetch_all(MYSQLI_ASSOC);

$topCust = $conn->query("SELECT u.name,COUNT(co.id) orders,SUM(oi.total_amount) spent FROM custom_orders co JOIN users u ON u.id=co.customer_id LEFT JOIN order_items oi ON oi.order_id=co.id WHERE co.branch_id=$branch GROUP BY co.customer_id ORDER BY spent DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<div class="rh-main">
    <div class="rh-page-header">
        <h1>Sales Reports</h1>
        <p>Branch: <strong><?= $_SESSION['branch_id']==1?'Dasmariñas, Cavite':'Biñan, Laguna' ?></strong></p>
    </div>

    <!-- PERIOD TABS -->
    <div class="rh-tabs mb-4">
        <?php foreach (['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','yearly'=>'Yearly'] as $k=>$v): ?>
            <a href="?period=<?= $k ?>" class="rh-tab <?= $period===$k?'active':'' ?>"><?= $v ?></a>
        <?php endforeach; ?>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-green"><i class="fas fa-peso-sign"></i></div>
                <div>
                    <div class="rh-stat-label">Revenue (<?= ucfirst($period) ?>)</div>
                    <div class="rh-stat-value" style="font-size:1.2rem;">₱<?= number_format($revenue,0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-blue"><i class="fas fa-circle-check"></i></div>
                <div><div class="rh-stat-label">Completed</div><div class="rh-stat-value"><?= $completed ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-amber"><i class="fas fa-gear"></i></div>
                <div><div class="rh-stat-label">Active Projects</div><div class="rh-stat-value"><?= $active ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-purple"><i class="fas fa-users-cog"></i></div>
                <div><div class="rh-stat-label">Staff / Welders</div><div class="rh-stat-value"><?= count($staffLoad) ?></div></div>
            </div>
        </div>
    </div>

    <!-- CHART -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-chart-area me-2 text-amber"></i><?= ucfirst($period) ?> Revenue Trend</div>
        <div class="card-body"><canvas id="salesChart" height="80"></canvas></div>
    </div>

    <div class="row g-4">
        <!-- STAFF LOAD -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="fas fa-users-cog me-2 text-amber"></i>Staff & Welder Project Load</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th>Role</th><th>Active</th><th>Done</th><th>Load</th></tr></thead>
                        <tbody>
                        <?php if (empty($staffLoad)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No data</td></tr>
                        <?php else:
                            $max = max(array_column($staffLoad,'ap')) ?: 1; ?>
                            <?php foreach ($staffLoad as $s): ?>
                            <tr>
                                <td class="fw-700 small"><?= htmlspecialchars($s['name']) ?></td>
                                <td><span class="badge badge-role-<?= $s['role'] ?>"><?= ucfirst($s['role']) ?></span></td>
                                <td><?= $s['ap'] ?></td>
                                <td><?= $s['done'] ?></td>
                                <td style="min-width:80px;">
                                    <div class="rh-load-bar">
                                        <div class="rh-load-fill" style="width:<?= round($s['ap']/$max*100) ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TOP CUSTOMERS -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="fas fa-star me-2 text-amber"></i>Top Customers by Spending</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Customer</th><th>Orders</th><th>Total Spent</th></tr></thead>
                        <tbody>
                        <?php if (empty($topCust)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">No data</td></tr>
                        <?php else: ?>
                            <?php foreach ($topCust as $c): ?>
                            <tr>
                                <td class="fw-600"><?= htmlspecialchars($c['name']) ?></td>
                                <td><?= $c['orders'] ?></td>
                                <td class="fw-700 text-success">₱<?= number_format($c['spent'],2) ?></td>
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
new Chart(document.getElementById('salesChart'),{
    type:'line',
    data:{
        labels:<?= json_encode($sales['labels']) ?>,
        datasets:[{
            label:'Revenue (₱)',
            data:<?= json_encode($sales['values']) ?>,
            borderColor:'#F59E0B',
            backgroundColor:'rgba(245,158,11,.1)',
            borderWidth:3, tension:.4, fill:true,
            pointBackgroundColor:'#F59E0B', pointRadius:5
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{display:false},
            tooltip:{backgroundColor:'#0F172A',callbacks:{label:c=>' ₱'+c.parsed.y.toLocaleString()}}
        },
        scales:{y:{ticks:{callback:v=>'₱'+v.toLocaleString()}}}
    }
});
</script>
</body></html>
