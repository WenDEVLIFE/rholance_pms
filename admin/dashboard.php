<?php
require_once '../includes/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: staff.php");
    exit;
}


$pageTitle = "Dashboard";



include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$branch = $_SESSION['branch_id'];

/* TOP 3 BEST SELLING */
$bestSelling = $conn->query("
SELECT items.name,
SUM(order_items.quantity) qty
FROM order_items
JOIN items ON items.id = order_items.item_id
WHERE order_items.order_id IN (
    SELECT id FROM custom_orders WHERE branch_id = $branch
)
GROUP BY items.name
ORDER BY qty DESC
LIMIT 3
");

$bestLabels = [];
$bestValues = [];

while($row = $bestSelling->fetch_assoc()){
    $bestLabels[] = $row['name'];
    $bestValues[] = (int)$row['qty'];
}

/* ===============================
   FETCH ALL APPOINTMENTS (ADMIN)
=============================== */
$stmt = $conn->prepare("
    SELECT a.*, b.name AS branch_name
    FROM appointments a
    LEFT JOIN branches b ON a.branch_id = b.id
    ORDER BY a.appointment_date ASC
");

$stmt->execute();
$appointments = $stmt->get_result();


/* TOP 3 PRODUCTS BY REVENUE */
$topRevenue = $conn->query("
SELECT items.name,
SUM(order_items.total_amount) revenue
FROM order_items
JOIN items ON items.id = order_items.item_id
WHERE order_items.order_id IN (
    SELECT id FROM custom_orders WHERE branch_id = $branch
)
GROUP BY items.name
ORDER BY revenue DESC
LIMIT 3
");

$revenueLabels = [];
$revenueValues = [];

while($row = $topRevenue->fetch_assoc()){
    $revenueLabels[] = $row['name'];
    $revenueValues[] = (float)$row['revenue'];
}

/* =========================
ORDER STATUS
========================= */

$statusQuery = $conn->query("
SELECT status, COUNT(*) total
FROM custom_orders
WHERE branch_id = $branch
GROUP BY status
");

$statusLabels = [];
$statusValues = [];

while($row = $statusQuery->fetch_assoc()){
    $statusLabels[] = $row['status'];
    $statusValues[] = (int)$row['total'];
}


/* =========================
PRODUCT SALES TABLE
========================= */
$productSales = $conn->query("
SELECT items.name,
SUM(order_items.quantity) qty,
SUM(order_items.total_amount) total
FROM order_items
JOIN items ON items.id = order_items.item_id
WHERE order_items.order_id IN (
    SELECT id FROM custom_orders WHERE branch_id = $branch
)
GROUP BY items.name
ORDER BY qty DESC
");

if (!$productSales) {
    die("SQL ERROR (Product Sales): " . $conn->error);
}

/* =========================
   STATISTICS
========================= */

// Active orders
$activeOrders = $conn->query("
SELECT COUNT(*) AS total 
FROM custom_orders 
WHERE branch_id = $branch 
AND status NOT IN ('Completed','Cancelled')
")->fetch_assoc()['total'];

// Completed orders
$completedOrders = $conn->query("
    SELECT COUNT(*) AS total 
    FROM custom_orders 
    WHERE branch_id = $branch AND status = 'Completed'
")->fetch_assoc()['total'];

// Total users
$totalUsers = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE branch_id = $branch
")->fetch_assoc()['total'];

// Total inventory items
$totalInventory = $conn->query("
    SELECT COUNT(*) AS total
    FROM inventory
    WHERE branch_id = $branch
")->fetch_assoc()['total'];


/* =========================
   PIPELINE FUNCTION
========================= */

function countStatus($conn,$branch,$status){
    $q = $conn->query("
        SELECT COUNT(*) AS total
        FROM custom_orders
        WHERE branch_id = $branch AND status = '$status'
    ");
    return $q->fetch_assoc()['total'];
}

/* =========================
   PIPELINE COUNTS
========================= */

$appointment = countStatus($conn,$branch,'Appointment');
$initial     = countStatus($conn,$branch,'Initial Payment');
$ongoing     = countStatus($conn,$branch,'On-going');
$delivery    = countStatus($conn,$branch,'For Delivery');
$backjobs    = countStatus($conn,$branch,'Backjobs');
$cancelled   = countStatus($conn,$branch,'Cancelled');
$completed   = countStatus($conn,$branch,'Completed');

if(empty($bestLabels)){
    $bestLabels = ['No Data'];
    $bestValues = [0];
}

if(empty($revenueLabels)){
    $revenueLabels = ['No Data'];
    $revenueValues = [0];
}

if(empty($statusLabels)){
    $statusLabels = ['No Data'];
    $statusValues = [0];
}
?>

<div class="main">
   

<!-- PAGE HEADER -->
<div class="page-header">

<div>
<h1>
Admin Dashboard 
<span style="font-size:14px;color:#6b7280;">
(
<?= ($_SESSION['branch_id'] == 1) ? 'Cavite' : 'Laguna' ?>
)
</span>
</h1>
<p class="subtitle">
Welcome back! Here’s what’s happening at Rholance Trading today.
</p>
</div>

<div class="page-actions">

<a href="../orders/orders.php" class="btn-primary-soft">
    <i class="fa-solid fa-clipboard-list"></i>
    Manage Orders
</a>
</div>

</div>


<!-- ======================
STAT CARDS
====================== -->

<div class="stats-grid">

<!-- Active Orders -->
<div class="stat-card" onclick="goToPage('../orders/orders.php?status=active')">

    <div class="stat-icon bg-blue">
        <i class="fa-solid fa-box-open"></i>
    </div>

    <div class="stat-info">
        <span class="stat-label">Active Orders</span>
        <h2><?= $activeOrders ?></h2>
    </div>

</div>

<!-- Completed Orders -->
<div class="stat-card accent-orange" onclick="goToPage('../orders/orders.php?status=completed')">
<div class="stat-icon bg-green"><i class="fa-solid fa-circle-check"></i>
</div>
<div class="stat-info">
<span class="stat-label">Completed Orders</span>
<h2><?= $completedOrders ?></h2>
</div>
</div>

<!-- Total Users -->
<div class="stat-card accent-blue" onclick="goToPage('/rholance_pms/dashboard/users.php')">
<div class="stat-icon bg-blue">
<i class="fa-solid fa-users"></i>
</div>
<div class="stat-info">
<span class="stat-label">Users</span>
<h2><?= $totalUsers ?></h2>
</div>
</div>

<!-- Backjobs -->
<div class="stat-card" onclick="goToPage('../orders/orders.php?status=backjobs')">
    <div class="stat-icon bg-yellow">
        <i class="fa-solid fa-rotate-left"></i>
    </div>
    <div class="stat-info">
        <span class="stat-label">Backjobs</span>
        <h2><?= $backjobs ?></h2>
    </div>
</div>

<!-- Cancelled -->
<div class="stat-card" onclick="goToPage('../orders/orders.php?status=cancelled')">
    <div class="stat-icon bg-red">
        <i class="fa-solid fa-ban"></i>
    </div>
    <div class="stat-info">
        <span class="stat-label">Cancelled</span>
        <h2><?= $cancelled ?></h2>
    </div>
</div>


<!-- Inventory Items -->
<div class="stat-card accent-purple" onclick="goToPage('inventory.php')">
<div class="stat-icon bg-purple">
<i class="fa-solid fa-boxes-stacked"></i>
</div>
<div class="stat-info">
<span class="stat-label">Inventory Items</span>
<h2><?= $totalInventory ?></h2>
</div>
</div>

</div>




<!-- ======================
ORDER PIPELINE
====================== -->

<div class="card">

<h3>Order Pipeline</h3>

<div class="pipeline">

<div class="pipeline-modern">

    <!-- Appointment -->
    <div class="pipeline-step-modern"
         data-status="Appointment"
         onclick="goToPipeline('Appointment')">
        <div class="circle bg-blue">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <span>Appointment</span>
        <small><?= $appointment ?></small>
    </div>

    <div class="line"></div>

    <!-- Initial Payment -->
    <div class="pipeline-step-modern"
         data-status="Initial Payment"
         onclick="goToPipeline('Initial Payment')">
        <div class="circle bg-indigo">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
        <span>Initial Payment</span>
        <small><?= $initial ?></small>
    </div>

    <div class="line"></div>

    <!-- On-going -->
    <div class="pipeline-step-modern"
         data-status="On-going"
         onclick="goToPipeline('On-going')">
        <div class="circle bg-yellow">
            <i class="fa-solid fa-gear"></i>
        </div>
        <span>On-going</span>
        <small><?= $ongoing ?></small>
    </div>

    <div class="line"></div>

    <!-- For Delivery -->
    <div class="pipeline-step-modern"
         data-status="For Delivery"
         onclick="goToPipeline('For Delivery')">
        <div class="circle bg-cyan">
            <i class="fa-solid fa-truck"></i>
        </div>
        <span>For Delivery</span>
        <small><?= $delivery ?></small>
    </div>

    <div class="line"></div>

    <!-- Backjobs -->
    <div class="pipeline-step-modern"
         data-status="Backjobs"
         onclick="goToPipeline('Backjobs')">
        <div class="circle bg-orange">
            <i class="fa-solid fa-rotate-left"></i>
        </div>
        <span>Backjobs</span>
        <small><?= $backjobs ?></small>
    </div>

    <div class="line"></div>

    <!-- Cancelled -->
    <div class="pipeline-step-modern"
         data-status="Cancelled"
         onclick="goToPipeline('Cancelled')">
        <div class="circle bg-red">
            <i class="fa-solid fa-ban"></i>
        </div>
        <span>Cancelled</span>
        <small><?= $cancelled ?></small>
    </div>

    <div class="line"></div>

    <!-- Completed -->
    <div class="pipeline-step-modern"
         data-status="Completed"
         onclick="goToPipeline('Completed')">
        <div class="circle bg-green">
            <i class="fa-solid fa-flag-checkered"></i>
        </div>
        <span>Completed</span>
        <small><?= $completed ?></small>
    </div>



</div>
</div>
</div>

<div class="card">

    <div class="card-header modern-header">
        <div>
            <h2>All Appointments</h2>
            <p class="card-subtitle">Overview of scheduled customer appointments</p>
        </div>
    </div>

    <div class="table-wrapper">

        <table class="modern-table admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Branch</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($appointments && $appointments->num_rows > 0): ?>

                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>

                        <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>

                        <td><?= htmlspecialchars($row['appointment_time']) ?></td>

                        <td><?= htmlspecialchars($row['branch_name']) ?></td>

                        <td>
                            <span class="status <?= strtolower($row['status']) ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="5" class="empty-state">
                        No appointments found
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>


<div class="chart-grid">

    <!-- BEST SELLING PRODUCTS -->
    <div class="card chart-card">
        <h3>Top 3 Best-Selling Products</h3>

        <div class="chart-container">
            <canvas id="bestSellingChart"></canvas>
        </div>
    </div>

    <!-- PRODUCTS BY REVENUE -->
    <div class="card chart-card">
        <h3 class="report-title">
            <i class="fa-solid fa-coins report-icon"></i>
            Top 3 Products by Revenue
        </h3>

        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

</div>


<div class="dashboard-grid two-columns">

    <!-- SALES GRAPH -->
    <div class="card chart-card">

        <div class="card-header">
            <h3 class="report-title">
                <i class="fa-solid fa-chart-line report-icon"></i>
                Sales Graph
            </h3>

        <div class="report-filter-buttons">

<div class="report-filter-buttons">
    <button class="filter-btn" data-filter="daily" onclick="updateChart(event, 'daily')">Daily</button>
    <button class="filter-btn" data-filter="monthly" onclick="updateChart(event, 'monthly')">Monthly</button>
    <button class="filter-btn" data-filter="yearly" onclick="updateChart(event, 'yearly')">Yearly</button>
</div>
</div>
        </div>

        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>

    </div>

    <!-- ORDER STATUS (PIE / DONUT) -->
    <div class="card chart-card">

        <h3>Order Status Distribution</h3>

        <div class="chart-container pie">
            <canvas id="statusChart"></canvas>
        </div>

    </div>

</div>

<div class="card">

<h3>Total Sold Items per Product</h3>

<table class="report-table">

<tr>
<th>Product Name</th>
<th>Total Quantity Sold</th>
<th>Total Sales (Php)</th>
</tr>

<?php while($row = $productSales->fetch_assoc()): ?>

<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['qty'] ?></td>
<td>Php<?= number_format($row['total'],2) ?></td>
</tr>

<?php endwhile; ?>

</table>

</div>



<!-- ======================
RECENT ORDERS
====================== -->

<div class="card">

<h3>Recent Orders</h3>

<table>

<tr>
<th>Material</th>
<th>Status</th>
</tr>

<?php
$orders = $conn->query("
SELECT 
    co.id,
    co.status,
    GROUP_CONCAT(i.name SEPARATOR ', ') AS products
FROM custom_orders co
LEFT JOIN order_items oi ON oi.order_id = co.id
LEFT JOIN items i ON i.id = oi.item_id
WHERE co.branch_id = $branch
GROUP BY co.id
ORDER BY co.created_at DESC
LIMIT 5
");
?>

<?php while($o = $orders->fetch_assoc()): ?>

<tr>
    <td class="truncate">
        <?= htmlspecialchars($o['products'] ?? 'No Items') ?>
    </td>

    <td>
        <span class="status <?= strtolower($o['status']) ?>">
            <?= htmlspecialchars($o['status']) ?>
        </span>
    </td>
</tr>

<?php endwhile; ?>

</table>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

/* BEST SELLING CHART */

new Chart(document.getElementById('bestSellingChart'),{

type:'bar',

data:{
labels: <?= json_encode($bestLabels) ?>,

datasets:[{
label:'Quantity Sold',
data: <?= json_encode($bestValues) ?>,

backgroundColor:[
'#4CAF50',
'#2196F3',
'#FFC107'
]

}]
},

options:{
responsive:true,
plugins:{
legend:{display:false}
}
}

});


/* REVENUE CHART */

new Chart(document.getElementById('revenueChart'),{

type:'bar',

data:{
labels: <?= json_encode($revenueLabels) ?>,

datasets:[{
label:'Revenue (Php)',
data: <?= json_encode($revenueValues) ?>,

backgroundColor:[
'#4CAF50',
'#2196F3',
'#FFC107'
]

}]
},

options:{
responsive:true,
plugins:{
legend:{display:false}
}
}

});

</script>
<script>
function goToPage(url){
    window.location.href = url;
}
</script>
</body>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const statusChart = new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',

        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                data: <?= json_encode($statusValues) ?>,
                backgroundColor: [
                    '#ef4444',
                    '#22c55e',
                    '#f59e0b',
                    '#3b82f6',
                    '#6366f1'
                ],
                borderWidth: 0,
                hoverOffset: 8,
                spacing: 4
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            layout: {
                padding: 20
            },

            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: '#111827',
                    padding: 10,
                    cornerRadius: 8
                }
            },

            cutout: '65%'
        }
    });

});
</script>

<script>

let salesChart;

/* INITIAL LOAD */
document.addEventListener("DOMContentLoaded", function () {

    const savedFilter = localStorage.getItem('salesFilter') || 'monthly';

    loadChart(savedFilter);

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');

        if(btn.dataset.filter === savedFilter){
            btn.classList.add('active');
        }
    });

});

/* LOAD DATA */
function loadChart(filter){

    fetch('get_sales.php?filter=' + filter)
    .then(res => res.json())
    .then(data => {

        if(salesChart){
            salesChart.destroy();
        }

        const ctx = document.getElementById('salesChart');

        salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: data.labels.map(label => formatLabel(label, filter)),
        datasets: [{
            label: 'Sales (Php)',
            data: data.data,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.15)',
            tension: 0.4,
            fill: true,
            pointRadius: 4
        }]
    },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }},
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }}
                }
            }
        });

    });
}

/* BUTTON CLICK */
function updateChart(e, filter){

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    e.target.classList.add('active');

    localStorage.setItem('salesFilter', filter);

    loadChart(filter);
}

function formatLabel(label, filter){

    if(filter === 'daily'){
        const date = new Date(label);
        return date.toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
        });
    }

    if(filter === 'monthly'){
        const [year, month] = label.split('-');
        const date = new Date(year, month - 1);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            year: 'numeric'
        });
    }

    if(filter === 'yearly'){
        return label;
    }

    return label;
}

</script>

<script>
function goToPipeline(status){
    const encoded = encodeURIComponent(status);
    window.location.href = "../orders/orders.php?status=" + encoded;
}
</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const params = new URLSearchParams(window.location.search);
    const current = params.get('status');

    if(current){
        document.querySelectorAll('.pipeline-step-modern').forEach(step=>{
            if(step.dataset.status === current){
                step.classList.add('active');
            }
        });
    }

});
</script>

<script src="/rholance_pms/assets/js/darkmode.js"></script>
</html>

</div>
