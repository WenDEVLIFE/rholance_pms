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
    <title>Inventory Availability</title>
    <link rel="stylesheet" href="../assets/css/staff-dashboard.css">
</head>
<body>

<div class="layout">
    <?php include '../includes/staff_sidebar.php'; ?>

    <main class="dashboard">

        <h1>Inventory Availability</h1>
        <p class="page-sub">Current stock for your branch (read-only).</p>

        <?php
        // Fetch inventory availability (branch-based)
        $inventory_stmt = $conn->prepare("
            SELECT 
                item_id,
                current_stock
            FROM inventory
            WHERE branch_id = ?
            ORDER BY item_id ASC
        ");
        $inventory_stmt->bind_param("i", $_SESSION['branch_id']);
        $inventory_stmt->execute();
        $inventory = $inventory_stmt->get_result();
        ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Available Stock</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($inventory->num_rows > 0): ?>
                    <?php while ($item = $inventory->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_id']) ?></td>
                            <td><?= htmlspecialchars($item['current_stock']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="empty-state">
                            No inventory records found for this branch.
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