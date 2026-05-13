<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_item') {
        $name     = $conn->real_escape_string($_POST['name']);
        $cat      = $conn->real_escape_string($_POST['category']);
        $price    = (float)$_POST['price'];
        $min      = (int)$_POST['min_stock'];
        $branch   = (int)$_POST['branch_id'];
        $init     = (int)$_POST['initial_stock'];

        // 1. Create item in items table
        $conn->query("INSERT INTO items (name, category, price) VALUES ('$name', '$cat', $price)");
        $itemId = $conn->insert_id;

        // 2. Add to inventory for specific branch
        $conn->query("INSERT INTO inventory (item_id, branch_id, current_stock, min_stock) 
                     VALUES ($itemId, $branch, $init, $min)");

        header("Location: index.php?msg=Item added successfully");
    }
    elseif ($action === 'update_item') {
        $id    = (int)$_POST['item_id'];
        $name  = $conn->real_escape_string($_POST['name']);
        $cat   = $conn->real_escape_string($_POST['category']);
        $price = (float)$_POST['price'];
        $min   = (int)$_POST['min_stock'];
        $branch = $_SESSION['branch_id'];

        $conn->query("UPDATE items SET name='$name', category='$cat', price=$price WHERE id=$id");
        $conn->query("UPDATE inventory SET min_stock=$min WHERE item_id=$id AND branch_id=$branch");

        header("Location: index.php?msg=Item updated successfully");
    }
    elseif ($action === 'stock_adjust') {
        $id     = (int)$_POST['item_id'];
        $qty    = (int)$_POST['qty']; // Positive for IN, Negative for OUT
        $branch = (int)$_POST['branch_id'];
        $notes  = $conn->real_escape_string($_POST['notes']);
        $type   = $_POST['type']; // 'in' or 'out'

        if ($type === 'out') $qty = -abs($qty);
        else $qty = abs($qty);

        // Update stock
        $conn->query("UPDATE inventory SET current_stock = current_stock + $qty 
                     WHERE item_id=$id AND branch_id=$branch");
        
        // Log history (assuming stock_history table exists or we just redirect)
        header("Location: index.php?msg=Stock updated successfully");
    }
}
?>
