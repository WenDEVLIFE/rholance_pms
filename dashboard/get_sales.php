<?php
include __DIR__ . '/../config/database.php';

$filter = $_GET['filter'] ?? 'monthly';

switch($filter){

    case 'daily':
        $query = "
            SELECT DATE(created_at) label,
            SUM(total_amount) total
            FROM order_items
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at)
        ";
    break;

    case 'yearly':
        $query = "
            SELECT YEAR(created_at) label,
            SUM(total_amount) total
            FROM order_items
            GROUP BY YEAR(created_at)
            ORDER BY YEAR(created_at)
        ";
    break;

    default:
        $query = "
            SELECT DATE_FORMAT(created_at,'%Y-%m') label,
            SUM(total_amount) total
            FROM order_items
            GROUP BY label
            ORDER BY label
        ";
    break;
}

$result = $conn->query($query);

$labels = [];
$data = [];

while($row = $result->fetch_assoc()){
    $labels[] = $row['label'];
    $data[] = $row['total'];
}

echo json_encode([
    'labels' => $labels,
    'data' => $data
]);