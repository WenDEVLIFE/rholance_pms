<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'welder') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$orderId = (int)$_POST['order_id'];
$pct = (int)$_POST['percentage'];
$desc = $conn->real_escape_string($_POST['description']);

// Handle file upload
$uploadDir = __DIR__ . '/../uploads/progress/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$imagePath = '';
if (isset($_FILES['progress_image']) && $_FILES['progress_image']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['progress_image']['name'], PATHINFO_EXTENSION);
    $filename = 'progress_' . $orderId . '_' . $pct . '_' . time() . '.' . $ext;
    $targetFile = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['progress_image']['tmp_name'], $targetFile)) {
        $imagePath = 'uploads/progress/' . $filename;
    } else {
        echo json_encode(['error' => 'Failed to move uploaded file.']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Photo evidence is required for progress updates.']);
    exit;
}

// Insert milestone into project_progress table
$sqlProgress = "INSERT INTO project_progress (project_id, percentage, description, image_path, status) 
                VALUES ($orderId, $pct, '$desc', '$imagePath', 'Approved')";

if ($conn->query($sqlProgress)) {
    // Update main order progress
    $statusUpdate = "";
    if ($pct === 100) {
        $statusUpdate = ", status = 'Completed'";
    }
    
    $conn->query("UPDATE custom_orders SET 
        progress_percent = $pct, 
        progress_details = '$desc' 
        $statusUpdate
        WHERE id = $orderId");
        
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}
exit;
?>
