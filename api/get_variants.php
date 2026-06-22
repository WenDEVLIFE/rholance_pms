<?php
include '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['product_name'])) {
    echo json_encode(['success' => false, 'message' => 'Product name is required']);
    exit;
}

$product_name = $conn->real_escape_string($_GET['product_name']);

$query = "SELECT * FROM custom_product_variants WHERE product_name = '$product_name' ORDER BY created_at DESC";
$result = $conn->query($query);

$variants = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $variants[] = [
            'id' => $row['id'],
            'variant_name' => $row['variant_name'],
            'description' => $row['description'],
            'image_url' => BASE_URL . $row['image_url']
        ];
    }
}

echo json_encode([
    'success' => true,
    'product_name' => $product_name,
    'variants' => $variants
]);
?>
