<?php
session_start(); // ✅ REQUIRED

include '../config/database.php';

$branch = $_SESSION['branch_id']; // ✅ ADD HERE

$filter = $_GET['filter'] ?? 'monthly';

$labels = [];
$data = [];
/* =========================
   DAILY SALES
========================= */
if ($filter === 'daily') {

 $result = $conn->query("
    SELECT DATE(o.created_at) as label,
           SUM(oi.total_amount) as total
    FROM custom_orders o
    JOIN order_items oi ON oi.order_id = o.id
    WHERE o.branch_id = $branch   --  HERE
    GROUP BY label
    ORDER BY label ASC
");

}

/* =========================
   MONTHLY SALES
========================= */
elseif ($filter === 'monthly') {

    $result = $conn->query("
    SELECT DATE_FORMAT(o.created_at, '%Y-%m') as label,
           SUM(oi.total_amount) as total
    FROM custom_orders o
    JOIN order_items oi ON oi.order_id = o.id
    WHERE o.branch_id = $branch   -- ✅ HERE
    GROUP BY label
    ORDER BY label ASC
");
}

/* =========================
   YEARLY SALES
========================= */
else {

$result = $conn->query("
    SELECT YEAR(o.created_at) as label,
           SUM(oi.total_amount) as total
    FROM custom_orders o
    JOIN order_items oi ON oi.order_id = o.id
    WHERE o.branch_id = $branch   -- ✅ HERE
    GROUP BY label
    ORDER BY label ASC
");
}

/* =========================
   FETCH DATA
========================= */
while($row = $result->fetch_assoc()){
    $labels[] = $row['label'];
    $data[] = (float)$row['total'];
}

/* =========================
   FALLBACK (IMPORTANT)
========================= */
if(empty($labels)){
    $labels = ['No Data'];
    $data = [0];
}

echo json_encode([
    'labels' => $labels,
    'data' => $data
]);