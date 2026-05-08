<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';

// Ensure role is customer
if ($_SESSION['role'] !== 'customer') {
    header("Location: ../dashboard/index.php");
    exit;
}

$customerId = $_SESSION['user_id'];

/* COUNT ORDERS */
$orderCountQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM custom_orders 
    WHERE customer_id = '$customerId'
");
$orderCount = mysqli_fetch_assoc($orderCountQuery)['total'];

/* COUNT APPOINTMENTS */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM appointments
    WHERE user_id = ?
    AND status IN ('Pending','Approved')
");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$appointmentCount = $stmt->get_result()->fetch_assoc()['total'];


/* GET ALL ORDERS */
$orders = mysqli_query($conn, "
    SELECT 
        co.id AS order_id,
        co.material,
        co.dimensions,
        co.status,
        co.created_at,
        oi.quantity,
        oi.total_amount,
        i.name AS item_name
    FROM custom_orders co
    LEFT JOIN order_items oi ON co.id = oi.order_id
    LEFT JOIN items i ON oi.item_id = i.id
    WHERE co.customer_id = '$customerId'
    ORDER BY co.created_at DESC
");


/* ===============================
   PROJECT COUNTS
=============================== */
$stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN status != 'Completed' THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed
    FROM custom_orders
    WHERE customer_id = ?
");

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$activeProjects = $result['active'] ?? 0;
$completedProjects = $result['completed'] ?? 0;

include __DIR__ . '/../includes/sidebar.php';

function orderProgress($status) {
    $map = [
        'Order Received' => 15,
        'Measurement'    => 30,
        'Cutting'        => 50,
        'Quality Check'  => 70,
        'Ready'          => 85,
        'Completed'      => 100
    ];
    return $map[$status] ?? 0;
}
?>

<div class="main customer-dashboard">

<!-- HEADER -->
<div class="dashboard-header">
    <h1>DASHBOARD</h1>
    <p class="subtitle">
        Track your orders, schedules, and request custom services.
    </p>
</div>

<!-- HERO -->
<div class="hero-card glass-premium">
    <div class="hero-content">
        <h2>Start a Custom Order</h2>
        <p>Submit a request and choose your preferred schedule. Our staff will handle the details.</p>
        <a href="customize.php" class="btn-modern">
            <i class="fas fa-plus-circle"></i> Request Custom Order
        </a>
    </div>
</div>

<!-- STATS -->
<div class="header-cards">

    <!-- MY PROJECTS -->
    <a href="my_projects.php" class="stat-card glass-premium clickable-card">
        <div class="stat-icon">
            <i class="fas fa-diagram-project"></i>
        </div>
        <div class="stat-info">
            <span>My Projects</span>
            <div class="stat-breakdown">
                <span class="active-tag"><?= $activeProjects ?> Active</span>
                <span class="completed-tag"><?= $completedProjects ?> Done</span>
            </div>
        </div>
    </a>

    <!-- APPOINTMENTS -->
    <a href="my_appointment.php" class="stat-card glass-premium clickable-card"> 
        <div class="stat-icon">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <span>Appointments</span>
            <h2 id="appointmentCount"><?= $appointmentCount ?></h2>
        </div>
    </a>

</div>

<!-- ORDER STATUS FLOW -->
<div class="card">
    <h3>Order Status Overview</h3>

    <div class="progress-steps">
        <span>Request</span>
        <span>Scheduled</span>
        <span>In Progress</span>
        <span>For Delivery</span>
        <span class="done">Completed</span>
    </div>
</div>

<!-- PRODUCTS ACCESS -->
<div class="card">
    <h3>Explore Available Products</h3>
    <p>Browse available materials and product options for your custom requests.</p>

    <a href="products.php" class="btn-secondary">
        View Products
    </a>
</div>

<!-- TABLE -->
<div class="card">
    <h3>My Custom Orders</h3>

    <?php if (mysqli_num_rows($orders) === 0): ?>
        <p class="empty">You haven’t placed any custom orders yet.</p>
    <?php else: ?>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Details</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($o = mysqli_fetch_assoc($orders)): ?>
        <?php $progress = orderProgress($o['status']); ?>

        <tr>
            <td><?= htmlspecialchars($o['item_name'] ?? 'Custom Order') ?></td>

            <td>
                <?= htmlspecialchars($o['material']) ?><br>
                <small><?= htmlspecialchars($o['dimensions']) ?></small>
            </td>

            <td><?= $o['quantity'] ?? '-' ?></td>

            <td>Php<?= isset($o['total_amount']) ? number_format($o['total_amount'], 2) : '0.00' ?></td>

            <td>
                <span class="status-badge status-<?= strtolower(str_replace(' ','-',$o['status'])) ?>">
                    <?= $o['status'] ?>
                </span>

                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                </div>
            </td>

            <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
        </tr>

        <?php endwhile; ?>
        </tbody>
    </table>

    <?php endif; ?>
</div>

</div>

<script src="/rholance_pms/assets/js/darkmode.js"></script>

<script>
function fetchDashboardStats() {
    fetch("api/dashboard-stats.php")
        .then(res => res.json())
        .then(data => {
            document.getElementById("orderCount").textContent = data.orders;
            document.getElementById("appointmentCount").textContent = data.appointments;
        })
        .catch(err => console.error("Error:", err));
}

/* AUTO REFRESH EVERY 5 SECONDS */
setInterval(fetchDashboardStats, 5000);
</script>

</body>
</html>