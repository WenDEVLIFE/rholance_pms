<h1>Transaction Records</h1>
<p class="page-sub">Operational transaction logging.</p>

<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

/* Ensure staff only */
if ($_SESSION['role'] !== 'staff') {
    header('Location: ../auth/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Records</title>
    <link rel="stylesheet" href="../assets/css/staff-dashboard.css">
</head>
<body>

<div class="layout">
    <?php include '../includes/staff_sidebar.php'; ?>

    <main class="dashboard">

        <h1>Transaction Records</h1>
        <p class="page-sub">Operational transaction logging (non-cash).</p>

        <?php
        // Fetch completed orders for dropdown
        $completed_stmt = $conn->prepare("
            SELECT id
            FROM custom_orders
            WHERE branch_id = ?
              AND status = 'Completed'
            ORDER BY id DESC
        ");
        $completed_stmt->bind_param("i", $_SESSION['branch_id']);
        $completed_stmt->execute();
        $completed_orders = $completed_stmt->get_result();
        ?>

        <div class="card">
            <form method="POST" action="../transactions/store.php">
                <div style="display:flex; gap:12px; flex-wrap:wrap;">

                    <select name="order_id" required>
                        <option value="">Select Completed Order</option>
                        <?php while ($order = $completed_orders->fetch_assoc()): ?>
                            <option value="<?= $order['id'] ?>">
                                Order #<?= $order['id'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <input
                        type="text"
                        name="remarks"
                        placeholder="Remarks (optional)"
                        style="flex:1;"
                    >

                    <button type="submit" class="btn btn-primary">
                        Record Transaction
                    </button>

                </div>
            </form>
        </div>

        <?php
        // Fetch recent transactions by this staff
        $tx_stmt = $conn->prepare("
            SELECT order_id, created_at
            FROM transactions
            WHERE staff_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $tx_stmt->bind_param("i", $_SESSION['user_id']);
        $tx_stmt->execute();
        $transactions = $tx_stmt->get_result();
        ?>

        <?php if ($transactions->num_rows > 0): ?>
            <div class="card">
                <h2>Recent Transactions</h2>
                <ul style="font-size:13px;">
                    <?php while ($tx = $transactions->fetch_assoc()): ?>
                        <li>
                            Order #<?= $tx['order_id'] ?> —
                            <?= date('M d, Y H:i', strtotime($tx['created_at'])) ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>