<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$branch = $_SESSION['branch_id'] ?? null;

// SAFE QUERY
if ($branch) {
    $stmt = $conn->prepare("
        SELECT * 
        FROM custom_orders 
        WHERE branch_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $branch);
    $stmt->execute();
    $orders = $stmt->get_result();
} else {
    // fallback
    $orders = $conn->query("
        SELECT * 
        FROM custom_orders 
        ORDER BY created_at DESC
    ");
}
?>

<div class="main">
    <h1>Custom Orders</h1>

    <a class="btn" href="create.php">+ New Custom Order</a>

    <div class="card">
        <table>
            <tr>
                <th>Material</th>
                <th>Dimensions</th>
                <th>Created</th>
                <th>Status</th>
                <th>Update Status</th>
            </tr>

            <?php if ($orders && $orders->num_rows > 0): ?>

                <?php while ($o = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($o['material']); ?></td>

                        <td><?= htmlspecialchars($o['dimensions']); ?></td>

                        <td><?= date('M d, Y', strtotime($o['created_at'])); ?></td>

                        <td>
                            <?php
                                $statusClass = strtolower(str_replace(' ', '', $o['status']));
                            ?>
                            <span class="badge <?= $statusClass; ?>">
                                <?= htmlspecialchars($o['status']); ?>
                            </span>
                        </td>

                        <td>
                            <?php if ($o['status'] !== 'Completed'): ?>
                                <form method="POST" action="update_status.php" style="display:flex; gap:6px;">
                                    <input type="hidden" name="id" value="<?= $o['id']; ?>">

                                    <select name="status" required>
                                        <option <?= $o['status']=='Order Received'?'selected':''; ?>>Order Received</option>
                                        <option <?= $o['status']=='Measurement'?'selected':''; ?>>Measurement</option>
                                        <option <?= $o['status']=='Cutting'?'selected':''; ?>>Cutting</option>
                                        <option <?= $o['status']=='Ready'?'selected':''; ?>>Ready</option>
                                        <option <?= $o['status']=='Completed'?'selected':''; ?>>Completed</option>
                                    </select>

                                    <button class="btn">Update</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#6b7280; font-size:13px;">Finalized</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>

            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No orders found.</td>
                </tr>
            <?php endif; ?>

        </table>
    </div>
</div>

</body>
</html>