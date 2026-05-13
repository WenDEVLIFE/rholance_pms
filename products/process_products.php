<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SESSION['role'] !== 'admin') { exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_product') {
        $name  = $conn->real_escape_string($_POST['name']);
        $cat   = $conn->real_escape_string($_POST['category']);
        $price = (float)$_POST['price'];
        
        $sql = "INSERT INTO items (name, category, price) VALUES ('$name', '$cat', $price)";
        if ($conn->query($sql)) {
            header("Location: index.php?msg=Product added successfully");
        }
    }
    elseif ($action === 'update_product') {
        $id    = (int)$_POST['product_id'];
        $name  = $conn->real_escape_string($_POST['name']);
        $cat   = $conn->real_escape_string($_POST['category']);
        $price = (float)$_POST['price'];
        
        $sql = "UPDATE items SET name='$name', category='$cat', price=$price WHERE id=$id";
        if ($conn->query($sql)) {
            header("Location: index.php?msg=Product updated successfully");
        }
    }
    elseif ($action === 'delete_product') {
        $id = (int)$_POST['product_id'];
        if ($conn->query("DELETE FROM items WHERE id=$id")) {
            header("Location: index.php?msg=Product removed");
        }
    }
}
?>
