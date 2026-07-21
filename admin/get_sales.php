<?php
session_start();
include '../config/database.php';

$filter = $_GET['filter'] ?? 'monthly';
$branch = $_SESSION['branch_id'] ?? 1;

$labels = [];
$data   = [];

/* =========================
   DAILY SALES
========================= */
if ($filter === 'daily') {
    $result = $conn->query("
        SELECT DATE_FORMAT(o.created_at, '%b %d') as label,
               SUM(oi.total_amount) as total
        FROM custom_orders o
        JOIN order_items oi ON oi.order_id = o.id
        WHERE o.branch_id = $branch
        GROUP BY DATE(o.created_at)
        ORDER BY DATE(o.created_at) ASC
    ");
}
/* =========================
   MONTHLY SALES
========================= */
elseif ($filter === 'monthly') {
    $result = $conn->query("
        SELECT DATE_FORMAT(o.created_at, '%b %Y') as label,
               SUM(oi.total_amount) as total
        FROM custom_orders o
        JOIN order_items oi ON oi.order_id = o.id
        WHERE o.branch_id = $branch
        GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
        ORDER BY MIN(o.created_at) ASC
    ");
}
/* =========================
   YEARLY SALES
========================= */
else {
    $result = $conn->query("
        SELECT DATE_FORMAT(o.created_at, '%Y') as label,
               SUM(oi.total_amount) as total
        FROM custom_orders o
        JOIN order_items oi ON oi.order_id = o.id
        WHERE o.branch_id = $branch
        GROUP BY YEAR(o.created_at)
        ORDER BY YEAR(o.created_at) ASC
    ");
}

/* =========================
   FETCH DATA
========================= */
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['label'];
        $data[]   = (float) $row['total'];
    }
}

/* =========================
   FALLBACK
========================= */
if (empty($labels)) {
    $labels = ['No Data'];
    $data   = [0];
}

echo json_encode(['labels' => $labels, 'data' => $data]);