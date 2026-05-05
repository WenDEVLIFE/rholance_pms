<?php
$pageTitle = "Reports";
include "../includes/header.php";
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';


$branch = $_SESSION['branch_id'];

$filter = $_GET['filter'] ?? 'daily';

if($filter == 'daily'){

    $salesQuery = "
    SELECT DATE(order_items.created_at) period,
    SUM(order_items.total_amount) total
    FROM order_items
    JOIN custom_orders ON custom_orders.id = order_items.order_id
    WHERE custom_orders.branch_id = $branch
    GROUP BY period
    ORDER BY period DESC
    LIMIT 10";

}

elseif($filter == 'weekly'){

    $salesQuery = "
    SELECT YEARWEEK(order_items.created_at) period,
    SUM(order_items.total_amount) total
    FROM order_items
    JOIN custom_orders ON custom_orders.id = order_items.order_id
    WHERE custom_orders.branch_id = $branch
    GROUP BY period
    ORDER BY period DESC
    LIMIT 10";

}

elseif($filter == 'monthly'){

    $salesQuery = "
    SELECT DATE_FORMAT(order_items.created_at,'%Y-%m') period,
    SUM(order_items.total_amount) total
    FROM order_items
    JOIN custom_orders ON custom_orders.id = order_items.order_id
    WHERE custom_orders.branch_id = $branch
    GROUP BY period
    ORDER BY period DESC
    LIMIT 12";

}

else{

    $salesQuery = "
    SELECT YEAR(order_items.created_at) period,
    SUM(order_items.total_amount) total
    FROM order_items
    JOIN custom_orders ON custom_orders.id = order_items.order_id
    WHERE custom_orders.branch_id = $branch
    GROUP BY period
    ORDER BY period DESC";
}

$salesResult = $conn->query($salesQuery);

/* ======================
KEY METRICS
====================== */

$totalSales = $conn->query("
SELECT IFNULL(SUM(order_items.total_amount),0) total
FROM order_items
JOIN custom_orders ON custom_orders.id = order_items.order_id
WHERE custom_orders.branch_id = $branch
")->fetch_assoc()['total'];

$totalOrders = $conn->query("
SELECT COUNT(*) total
FROM custom_orders
WHERE branch_id = $branch
")->fetch_assoc()['total'];

$totalUsers = $conn->query("
SELECT COUNT(*) total
FROM users
WHERE branch_id = $branch
")->fetch_assoc()['total'];


/* ======================
MONTHLY SALES SUMMARY
====================== */

$salesMonthly = $conn->query("
SELECT DATE_FORMAT(order_items.created_at,'%Y-%m') month,
SUM(order_items.total_amount) total
FROM order_items
JOIN custom_orders ON custom_orders.id = order_items.order_id
WHERE custom_orders.branch_id = $branch
GROUP BY month
ORDER BY month DESC
");

/* ======================
DAILY INCOME
====================== */

$dailyIncome = $conn->query("
SELECT DATE(order_items.created_at) day,
SUM(order_items.total_amount) income
FROM order_items
JOIN custom_orders ON custom_orders.id = order_items.order_id
WHERE custom_orders.branch_id = $branch
GROUP BY day
ORDER BY day DESC
LIMIT 7
");


/* ======================
TOP 3 BEST SELLING
====================== */

$bestSelling = $conn->query("
SELECT items.name,
SUM(order_items.quantity) qty
FROM order_items
JOIN items ON items.id = order_items.item_id
JOIN custom_orders ON custom_orders.id = order_items.order_id
WHERE custom_orders.branch_id = $branch
GROUP BY items.name
ORDER BY qty DESC
LIMIT 3
");


/* ======================
TOP PRODUCTS BY REVENUE
====================== */

$topRevenue = $conn->query("
SELECT items.name,
SUM(order_items.total_amount) revenue
FROM order_items
JOIN items ON items.id = order_items.item_id
JOIN custom_orders ON custom_orders.id = order_items.order_id
WHERE custom_orders.branch_id = $branch
GROUP BY items.name
ORDER BY revenue DESC
LIMIT 3
");


/* ======================
ORDER STATUS DISTRIBUTION
====================== */

$statusStats = $conn->query("
SELECT status, COUNT(*) total
FROM custom_orders
WHERE branch_id = $branch
GROUP BY status
");


/* ======================
LOW STOCK ITEMS
====================== */

$lowStock = $conn->query("
SELECT name, stock
FROM items
WHERE stock <= 100
ORDER BY stock ASC
LIMIT 5
");
?>

<div class="main">

<h1>Administrative Report</h1>


<!-- ======================
KEY METRICS
====================== -->

<div class="stats-grid">

<div class="stat-card accent-orange" onclick="goToPage('orders.php?status=active')">
    <div class="stat-icon bg-orange">
        <i class="fa-solid fa-peso-sign"></i>
    </div>
    <div class="stat-info">
        <span class="stat-label">Total Sales</span>
        <h2>Php<?= number_format($totalSales,2) ?></h2>
    </div>
</div>

<div class="stat-card accent-blue">
    <div class="stat-icon bg-blue">
        <i class="fa-solid fa-box"></i>
    </div>
    <div class="stat-info">
        <span class="stat-label">Total Orders</span>
        <h2><?= $totalOrders ?></h2>
    </div>
</div>

<div class="stat-card accent-green">
    <div class="stat-icon bg-green">
        <i class="fa-solid fa-users"></i>
    </div>
    <div class="stat-info">
        <span class="stat-label">Total Users</span>
        <h2><?= $totalUsers ?></h2>
    </div>
</div>

</div>



<div class="card report-card">

<div class="card-header">

<h3 class="report-title">
<i class="fa-solid fa-chart-line report-icon"></i>
Sales Summary
</h3>

<div class="card report-card report-section">

<label>Filter by:</label>

<select id="salesFilter">

<option value="daily" <?= $filter=='daily'?'selected':'' ?>>Daily</option>
<option value="weekly" <?= $filter=='weekly'?'selected':'' ?>>Weekly</option>
<option value="monthly" <?= $filter=='monthly'?'selected':'' ?>>Monthly</option>
<option value="yearly" <?= $filter=='yearly'?'selected':'' ?>>Yearly</option>

</select>

</div>

</div>

<table class="report-table">

<thead>
<tr>
<th>Period</th>
<th>Total Sales (Php)</th>
</tr>
</thead>

<tbody>

<?php while($row = $salesResult->fetch_assoc()): ?>

<tr>

<td>

<?php

if($filter == 'daily'){
    echo date("M d, Y", strtotime($row['period']));
}
elseif($filter == 'weekly'){
    echo "Week ".$row['period'];
}
elseif($filter == 'monthly'){
    echo $row['period'];
}
else{
    echo $row['period'];
}

?>

</td>

<td>Php<?= number_format($row['total'],2) ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<p class="report-note">
The above table shows aggregated sales based on the selected filter.
</p>

</div>

<!-- ======================
BEST SELLING PRODUCTS
====================== -->

<div class="card report-card report-section">
    <h3 class="report-title">
<i class="fa-solid fa-trophy report-icon"></i>
Top 3 Best-Selling Products
</h3>

<table class="report-table">

<tr>
<th>Product</th>
<th>Quantity Sold</th>
</tr>

<?php while($row = $bestSelling->fetch_assoc()): ?>

<tr>
<td><?=$row['name']?></td>
<td><?=$row['qty']?></td>
</tr>

<?php endwhile; ?>

</table>

</div>



<!-- ======================
TOP PRODUCTS BY REVENUE
====================== -->

<div class="card report-card report-section">
<h3 class="report-title">
<i class="fa-solid fa-coins report-icon"></i>
Top Products by Revenue
</h3>


<table class="report-table">

<tr>
<th>Product</th>
<th>Total Revenue (Php)</th>
</tr>

<?php while($row = $topRevenue->fetch_assoc()): ?>

<tr>
<td><?=$row['name']?></td>
<td><?=number_format($row['revenue'],2)?></td>
</tr>

<?php endwhile; ?>

</table>

</div>



<!-- ======================
ORDER STATUS
====================== -->

<div class="card report-card report-section">
<h3 class="report-title">
<i class="fa-solid fa-chart-pie report-icon"></i>
Order Status Distribution
</h3>

<table class="report-table">

<tr>
<th>Status</th>
<th>Number of Orders</th>
</tr>

<?php while($row = $statusStats->fetch_assoc()): ?>

<tr>
<td><?=$row['status']?></td>
<td><?=$row['total']?></td>
</tr>

<?php endwhile; ?>

</table>

</div>



<!-- ======================
LOW STOCK PRODUCTS
====================== -->

<div class="card report-card report-section">
<h3 class="report-title">
<i class="fa-solid fa-triangle-exclamation report-icon"></i>
Low Stock Products
</h3>

<table class="report-table">

<tr>
<th>Product</th>
<th>Stock Remaining</th>
</tr>

<?php while($row = $lowStock->fetch_assoc()): ?>

<tr>
<td><?=$row['name']?></td>
<td><?=$row['stock']?></td>
</tr>

<?php endwhile; ?>

</table>

</div>

</div>
<script>

document.getElementById("salesFilter").addEventListener("change", function(){

    const filter = this.value;

    window.location.href = "?filter=" + filter;

});

</script>