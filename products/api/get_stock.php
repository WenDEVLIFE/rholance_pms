<?php
include '../../config/database.php';

$result = $conn->query("SELECT id, stock FROM items");

$data = [];

while($row = $result->fetch_assoc()){
    $data[$row['id']] = $row['stock'];
}

echo json_encode($data);