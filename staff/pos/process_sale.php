<?php
include(__DIR__ . '/../../config/db.php');

$data = json_decode(file_get_contents("php://input"), true);

$conn->begin_transaction();

try {

    $total = $data['total'];
    $payment = $data['payment'];
    $change = $payment - $total;

    $conn->query("INSERT INTO sales (total, payment, change_amount)
                  VALUES ($total, $payment, $change)");

    $sale_id = $conn->insert_id;

    foreach ($data['cart'] as $item) {

        $id = $item['id'];
        $qty = $item['qty'];
        $price = $item['price'];

        // Insert sale item
        $conn->query("INSERT INTO sale_items (sale_id, product_id, quantity, price)
                      VALUES ($sale_id, $id, $qty, $price)");

        // Deduct stock
        $conn->query("UPDATE products
                      SET stock = stock - $qty
                      WHERE id = $id");
    }

    $conn->commit();

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["error" => $e->getMessage()]);
}