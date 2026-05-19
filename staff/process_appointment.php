<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { exit("Unauthorized"); }

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$status = $_GET['status'] ?? $_POST['status'] ?? '';
$welder_id = isset($_POST['welder_id']) ? (int)$_POST['welder_id'] : 0;

if ($id && $status) {
    // Replaced Cancelled and Rejected from standard staff approval flow
    $allowed = ['Approved', 'Completed', 'Pending'];
    if (in_array($status, $allowed)) {
        
        if ($status === 'Approved' && $welder_id > 0) {
            // Set welder and status to Approved
            $conn->query("UPDATE appointments SET status = 'Approved', welder_id = $welder_id WHERE id = $id");
        } 
        elseif ($status === 'Completed') {
            // Mark Met / Done
            $conn->query("UPDATE appointments SET status = 'Completed' WHERE id = $id");
            
            // Get appointment details
            $appt = $conn->query("SELECT * FROM appointments WHERE id = $id")->fetch_assoc();
            if ($appt) {
                $cust_id = (int)($appt['user_id'] ?? 0);
                $branch_id = (int)$appt['branch_id'];
                $cust_name = $conn->real_escape_string($appt['customer_name']);
                $address = $conn->real_escape_string($appt['address'] ?? '');
                $welder = (int)$appt['welder_id'];
                
                // Create custom order (Ongoing project status)
                $proj_name = $cust_name . " - Customized Fabrication Project";
                $conn->query("
                    INSERT INTO custom_orders (customer_id, branch_id, customer_name, project_name, status, order_type, material, dimensions, instructions)
                    VALUES ($cust_id, $branch_id, '$cust_name', '$proj_name', 'On-going', 'online', 'TBD', 'TBD', 'Pending welder input of project dimensions and materials.')
                ");
                $new_order_id = $conn->insert_id;
                
                // Add initial setup task for the welder
                if ($new_order_id && $welder > 0) {
                    $conn->query("
                        INSERT INTO tasks (order_id, task_name, status, assigned_to)
                        VALUES ($new_order_id, 'Project Estimation & Material Detail Input', 'Pending', $welder)
                    ");
                }
            }
        } 
        else {
            $conn->query("UPDATE appointments SET status = '$status' WHERE id = $id");
        }
    }
}

// Redirect back
$back = $_SERVER['HTTP_REFERER'] ?? 'appointment.php';
header("Location: $back");
exit;
?>
