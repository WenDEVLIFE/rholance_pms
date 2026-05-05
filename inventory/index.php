<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$branch = $_SESSION['branch_id'];

$result = $conn->query("
    SELECT inventory.*, items.name 
    FROM inventory 
    JOIN items ON items.id = inventory.item_id 
    WHERE branch_id = $branch
");
?>

<div class="main">
    <h1>Inventory</h1>

    <div style="margin-bottom:15px;">
        <a class="btn" href="stock_in.php">+ Stock In</a>
       
    </div>

    <div class="card">
        <table>
            <div class="inventory-grid">

<?php while ($row = $result->fetch_assoc()): ?>

    <div class="inventory-card">

        <div class="inv-header">
            <h3><?= htmlspecialchars($row['name']); ?></h3>
        </div>

        <div class="inv-body">

            <div class="stock">
                <span class="label">Stock</span>
                <h2><?= $row['current_stock']; ?></h2>
            </div>

            <div class="status">
                <?php if ($row['current_stock'] <= 5): ?>
                    <span class="badge low">Low Stock</span>
                <?php else: ?>
                    <span class="badge ok">Available</span>
                <?php endif; ?>
            </div>

        </div>

    </div>

<?php endwhile; ?>

</div>
        </table>
    </div>
</div>

</body>
</html>
