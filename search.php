<?php
session_start();
include 'config/database.php'; // adjust path if needed

// =========================
// SESSION + INPUT
// =========================
$role = $_SESSION['role'] ?? 'guest';
$user_id = $_SESSION['user_id'] ?? 0;

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    die("No search query provided.");
}

// Prevent SQL Injection
$q = mysqli_real_escape_string($conn, $query);

// =========================
// INITIALIZE VARIABLES
// =========================
$order_result = null;
$item_result = null;
$task_result = null;

// =========================
// ROLE-BASED SEARCH LOGIC
// =========================

if ($role === 'admin') {

    // ================= ORDERS =================
    $order_sql = "
    SELECT * FROM custom_orders
    WHERE customer_name LIKE '%$q%' 
       OR status LIKE '%$q%'
       OR order_type LIKE '%$q%'
    ";
    $order_result = mysqli_query($conn, $order_sql);

    // ================= INVENTORY =================
    $item_sql = "
    SELECT * FROM items
    WHERE name LIKE '%$q%'
    ";
    $item_result = mysqli_query($conn, $item_sql);

    // ================= TASKS =================
    $task_sql = "
    SELECT * FROM tasks
    WHERE task_name LIKE '%$q%' 
       OR status LIKE '%$q%'
    ";
    $task_result = mysqli_query($conn, $task_sql);

} elseif ($role === 'staff') {

    // ================= ORDERS (ASSIGNED ONLY) =================
    $order_sql = "
    SELECT * FROM custom_orders
    WHERE user_id = '$user_id'
    AND (
        customer_name LIKE '%$q%' 
        OR status LIKE '%$q%'
    )
    ";
    $order_result = mysqli_query($conn, $order_sql);

    // ================= INVENTORY =================
    $item_sql = "
    SELECT * FROM items
    WHERE name LIKE '%$q%'
    ";
    $item_result = mysqli_query($conn, $item_sql);

    // ================= TASKS (ASSIGNED ONLY) =================
    $task_sql = "
    SELECT * FROM tasks
    WHERE assigned_to = '$user_id'
    AND (
        task_name LIKE '%$q%' 
        OR status LIKE '%$q%'
    )
    ";
    $task_result = mysqli_query($conn, $task_sql);

} elseif ($role === 'customer') {

    // ================= ORDERS (OWN ONLY) =================
    $order_sql = "
    SELECT * FROM custom_orders
    WHERE customer_id = '$user_id'
    AND (
        customer_name LIKE '%$q%' 
        OR status LIKE '%$q%'
    )
    ";
    $order_result = mysqli_query($conn, $order_sql);

    // ================= TASKS (LINKED TO CUSTOMER ORDERS) =================
    $task_sql = "
    SELECT t.* FROM tasks t
    JOIN custom_orders o ON t.order_id = o.id
    WHERE o.customer_id = '$user_id'
    AND (
        t.task_name LIKE '%$q%' 
        OR t.status LIKE '%$q%'
    )
    ";
    $task_result = mysqli_query($conn, $task_sql);

    // ❌ NO INVENTORY ACCESS
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<h2>Search Results for: "<?= htmlspecialchars($query) ?>"</h2>
<hr>

<!-- ================= ORDERS ================= -->
<?php if ($order_result !== null): ?>
<h3>Orders</h3>

<?php if (mysqli_num_rows($order_result) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($order_result)): ?>
        <div class="search-card">
            <strong>Customer:</strong> <?= htmlspecialchars($row['customer_name']) ?><br>
            <strong>Status:</strong> <?= htmlspecialchars($row['status']) ?><br>
            <strong>Type:</strong> <?= htmlspecialchars($row['order_type']) ?><br>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No orders found.</p>
<?php endif; ?>

<hr>
<?php endif; ?>

<!-- ================= INVENTORY ================= -->
<?php if ($item_result !== null): ?>
<h3>Inventory</h3>

<?php if (mysqli_num_rows($item_result) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($item_result)): ?>
        <div class="search-card">
            <strong>Item:</strong> <?= htmlspecialchars($row['name']) ?><br>
            <strong>Stock:</strong> <?= htmlspecialchars($row['stock']) ?><br>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No inventory found.</p>
<?php endif; ?>

<hr>
<?php endif; ?>

<!-- ================= TASKS ================= -->
<?php if ($task_result !== null): ?>
<h3>Tasks</h3>

<?php if (mysqli_num_rows($task_result) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($task_result)): ?>
        <div class="search-card">
            <strong>Task:</strong> <?= htmlspecialchars($row['task_name']) ?><br>
            <strong>Status:</strong> <?= htmlspecialchars($row['status']) ?><br>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No tasks found.</p>
<?php endif; ?>

<?php endif; ?>

</body>
</html>