<?php
session_start();

// ✅ USE CORRECT DB FILE
include __DIR__ . '/config/database.php';

header('Content-Type: application/json');

// FAIL SAFE
if (!isset($conn) || !$conn) {
    echo json_encode([]);
    exit;
}

// SESSION
$role = $_SESSION['role'] ?? 'guest';
$user_id = $_SESSION['user_id'] ?? 0;

// SECURITY
if ($role === 'customer' && empty($user_id)) {
    echo json_encode([]);
    exit;
}

// INPUT
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$q = mysqli_real_escape_string($conn, $q);

$results = [];

// ================= ADMIN =================
if ($role === 'admin') {

    $sql = "SELECT id, customer_name, status, order_type
            FROM custom_orders 
            WHERE 
                customer_name LIKE '%$q%' 
                OR status LIKE '%$q%' 
                OR order_type LIKE '%$q%'
                OR id LIKE '%$q%'
            LIMIT 5";

    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'type' => 'Order',
            'title' => !empty($row['customer_name']) 
                ? $row['customer_name'] 
                : 'Order #' . $row['id'],
            'sub' => $row['status'],
            'link' => '<?= BASE_URL ?>admin/order_view.php?id=' . $row['id']
        ];
    }

    // TASKS
    $sql = "SELECT task_name, status, order_id 
            FROM tasks 
            WHERE 
                task_name LIKE '%$q%' 
                OR status LIKE '%$q%'
                OR order_id LIKE '%$q%'
            LIMIT 5";

    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'type' => 'Task',
            'title' => $row['task_name'],
            'sub' => $row['status'],
            'link' => '<?= BASE_URL ?>staff/task_view.php?task=' . urlencode($row['task_name'])
        ];
    }

    // INVENTORY
    $sql = "SELECT name, stock 
            FROM items 
            WHERE name LIKE '%$q%' 
            LIMIT 5";

    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'type' => 'Item',
            'title' => $row['name'],
            'sub' => 'Stock: ' . $row['stock'],
            'link' => '#'
        ];
    }
}

// ================= STAFF =================
elseif ($role === 'staff') {

    $sql = "SELECT id, customer_name, status
            FROM custom_orders 
            WHERE user_id = '$user_id'
            AND (
                customer_name LIKE '%$q%' 
                OR status LIKE '%$q%'
                OR id LIKE '%$q%'
            )
            LIMIT 5";

    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'type' => 'Order',
            'title' => !empty($row['customer_name']) 
                ? $row['customer_name'] 
                : 'Order #' . $row['id'],
            'sub' => $row['status'],
            'link' => '<?= BASE_URL ?>staff/order_view.php?id=' . $row['id']
        ];
    }

    $sql = "SELECT task_name, status 
            FROM tasks 
            WHERE assigned_to = '$user_id'
            AND (
                task_name LIKE '%$q%' 
                OR status LIKE '%$q%'
            )
            LIMIT 5";

    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'type' => 'Task',
            'title' => $row['task_name'],
            'sub' => $row['status'],
            'link' => '<?= BASE_URL ?>staff/task_view.php?task=' . urlencode($row['task_name'])
        ];
    }
}

// ================= CUSTOMER =================
elseif ($role === 'customer') {

    $sql = "SELECT id, customer_name, status 
            FROM custom_orders 
            WHERE user_id = '$user_id'
            AND (
                customer_name LIKE '%$q%' 
                OR status LIKE '%$q%'
                OR id LIKE '%$q%'
            )
            LIMIT 5";

    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'type' => 'Order',
            'title' => !empty($row['customer_name']) 
                ? $row['customer_name'] 
                : 'Order #' . $row['id'],
            'sub' => $row['status'],
            'link' => '<?= BASE_URL ?>customer/order_details.php?id=' . $row['id']
        ];
    }
}

echo json_encode($results);
exit;