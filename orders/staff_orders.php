<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

/* Ensure staff only */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../auth/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
    <link rel="stylesheet" href="../assets/css/staff-dashboard.css">
</head>
<body>

<div class="layout">
    <?php include '../includes/staff_sidebar.php'; ?>

    <main class="dashboard">

        <h1>Orders</h1>
        <p class="page-sub">Read-only list of branch orders.</p>

        <?php
        // Fetch branch orders (read-only for staff)
        $order_stmt = $conn->prepare("
            SELECT 
                id,
                material,
                status,
                estimated_completion
            FROM custom_orders
            WHERE branch_id = ?
            ORDER BY created_at DESC
        ");
        $order_stmt->bind_param("i", $_SESSION['branch_id']);
        $order_stmt->execute();
        $orders = $order_stmt->get_result();
        ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Material</th>
                        <th>Status</th>
                        <th>Est. Completion</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['material']) ?></td>
                            <td>
                                <span class="status <?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= $order['estimated_completion']
                                    ? htmlspecialchars(date('M d, Y', strtotime($order['estimated_completion'])))
                                    : '—'
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-state">
                            No orders found for this branch.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>