<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$branch = $_SESSION['branch_id'];

/* FETCH ITEMS */
$items = $conn->query("SELECT id, name FROM items ORDER BY name ASC");

/* HANDLE SUBMIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $item = (int)$_POST['item_id'];
    $qty = (int)$_POST['qty'];

    if ($qty > 0) {

        $conn->query("
            UPDATE inventory 
            SET current_stock = current_stock + $qty 
            WHERE item_id=$item AND branch_id=$branch
        ");

        $success = "Stock successfully updated!";
    } else {
        $error = "Quantity must be greater than 0.";
    }
}
?>

<div class="main">

<div class="page-header">

    <div>
        <h1>Stock In</h1>
        <p class="subtitle">Add inventory stock</p>
    </div>

    <a href="index.php" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i>
        Back to Inventory
    </a>

</div>
    <div class="card stock-card">

        <?php if(!empty($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if(!empty($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="stock-form">

            <!-- ITEM -->
            <div class="form-group">
                <label>Select Item</label>
                <select name="item_id" required>
                    <option value="">-- Choose Item --</option>
                    <?php while($i = $items->fetch_assoc()): ?>
                        <option value="<?= $i['id'] ?>">
                            <?= htmlspecialchars($i['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- QUANTITY -->
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="qty" min="1" required>
            </div>

            <!-- BUTTON -->
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-plus"></i>
                Add Stock
            </button>

        </form>

    </div>

</div>