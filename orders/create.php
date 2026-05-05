<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';

/* AUTO-FILL MATERIAL IF FROM PRODUCT */
$itemName = '';

if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    $result = mysqli_query($conn, "SELECT name FROM items WHERE id = '$item_id'");
    $item = mysqli_fetch_assoc($result);

    if ($item) {
        $itemName = $item['name'];
    }
}

include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<!-- LOAD FORM CSS -->
<link rel="stylesheet" href="../assets/css/forms.css">

<div class="main">

<h1 class="section-title">Create Custom Order</h1>

<form method="POST" action="store.php" enctype="multipart/form-data" class="custom-form">

    <!-- ROW 1 -->
    <div class="form-row">
        <div class="form-group">
            <label>Material</label>
            <input type="text" name="material" 
                   value="<?= htmlspecialchars($itemName) ?>" required>
        </div>

        <div class="form-group">
            <label>Dimensions</label>
            <input type="text" name="dimensions" placeholder="e.g. 5ft x 3ft">
        </div>
    </div>

    <!-- INSTRUCTIONS -->
    <div class="form-group">
        <label>Instructions</label>
        <textarea name="instructions" rows="3" placeholder="Additional details..."></textarea>
    </div>

    <!-- ROW 2 -->
    <div class="form-row">
        <div class="form-group">
            <label>Estimated Completion</label>
            <input type="date" name="estimated_completion">
        </div>

        <div class="form-group">
            <label>Appointment Date</label>
            <input type="date" name="appointment_date">
        </div>
    </div>

    <!-- IMAGE -->
    <div class="form-group">
        <label>Reference Image</label>
        <input type="file" name="reference_image" accept="image/*">
    </div>

    <!-- ROW 3 (NEW) -->
<div class="form-row">
    <div class="form-group">
        <label>Customer Name</label>
        <input type="text" name="customer_name" required placeholder="Enter customer name">
    </div>

    <div class="form-group">
        <label>Order Type</label>
        <select name="order_type" required>
            <option value="walk-in">Walk-in</option>
            <option value="online">Online</option>
        </select>
    </div>
</div>

<!-- SUBMIT -->
<button type="submit" class="btn-submit">
    Submit Order
</button>
</form>

</div>