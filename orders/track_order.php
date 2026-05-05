<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

$customerId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    die("Invalid order ID.");
}

/* FETCH ORDER */
$stmt = $conn->prepare("
    SELECT status, created_at 
    FROM custom_orders 
    WHERE id = ? AND customer_id = ?
");
$stmt->bind_param("ii", $orderId, $customerId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

$status = $order['status'];

/* FETCH TASKS (REAL TRACKING) */
$taskStmt = $conn->prepare("
    SELECT task_name, status 
    FROM tasks 
    WHERE order_id = ?
");

$taskStmt->bind_param("i", $orderId);
$taskStmt->execute();
$taskResult = $taskStmt->get_result();

$tasks = [];
while ($row = $taskResult->fetch_assoc()) {
    $tasks[] = $row;
}

/* CALCULATE PROGRESS */
$totalTasks = count($tasks);
$completedTasks = 0;

foreach ($tasks as $t) {
    if ($t['status'] === 'Completed') {
        $completedTasks++;
    }
}

$progressPercent = $totalTasks > 0 
    ? ($completedTasks / $totalTasks) * 100 
    : 0;
?>

<div class="main">

<a href="my_orders.php" class="btn-back">
    <i class="fa fa-arrow-left"></i> Back to My Orders
</a>

<div class="track-container">

<div class="track-card">

    <!-- HEADER -->
    <div class="track-header">
        <h2>Order #<?= htmlspecialchars($orderId) ?></h2>
        <span class="status status-<?= strtolower(str_replace(' ', '-', $status)) ?>">
            <?= htmlspecialchars($status) ?>
        </span>
    </div>

    <!-- STATUS MESSAGE -->
    <p class="status-message">
    <?php
    switch ($status) {
        case 'Pending':
            echo "Your order is waiting for confirmation.";
            break;
        case 'Order Received':
            echo "Your order has been received and queued.";
            break;
        case 'In Progress':
            echo "Your order is currently being processed.";
            break;
        case 'Completed':
            echo "Your order is completed.";
            break;
        case 'Cancelled':
            echo "This order has been cancelled.";
            break;
    }
    ?>
    </p>

    <?php if ($status === 'Cancelled'): ?>

        <!-- CANCELLED -->
        <div class="cancelled-box">
            <i class="fa fa-times-circle"></i>
            <p>This order has been cancelled.</p>
        </div>

    <?php else: ?>

        <!-- PROGRESS BAR -->
        <div class="task-tracker">

            <h3>Progress Overview</h3>

            <div class="progress-bar-wrapper">
                <div class="progress-bar-fill" style="width: <?= $progressPercent ?>%"></div>
            </div>

            <p class="progress-text">
                <?= $completedTasks ?> of <?= $totalTasks ?> tasks completed
            </p>

        </div>

        <!-- TIMELINE -->
        <div class="timeline">

            <h3>Order Timeline</h3>

            <?php if (empty($tasks)): ?>
                <p>No workflow tasks available.</p>
            <?php endif; ?>

            <?php foreach ($tasks as $task): ?>

                <div class="timeline-item">

                    <div class="timeline-icon 
                        <?= $task['status'] === 'Completed' ? 'done' : 'pending' ?>">
                        
                        <?php if ($task['status'] === 'Completed'): ?>
                            <i class="fa fa-check"></i>
                        <?php else: ?>
                            <i class="fa fa-clock"></i>
                        <?php endif; ?>

                    </div>

                    <div class="timeline-content">
                        <h4><?= htmlspecialchars($task['task_name']) ?></h4>
                        <p>Status: <?= htmlspecialchars($task['status']) ?></p>
                        <small>
                            <?= date('M d, Y h:i A', strtotime($task['created_at'])) ?>
                        </small>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

</div>

</div>