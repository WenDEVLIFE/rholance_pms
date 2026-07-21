<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { exit("Unauthorized"); }

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$status = $_GET['status'] ?? $_POST['status'] ?? '';
$welder_id = isset($_POST['welder_id']) ? (int)$_POST['welder_id'] : 0;

if ($id && $status) {
    $allowed = ['Approved', 'Completed', 'Pending'];
    if (in_array($status, $allowed)) {
        
        if ($status === 'Approved' && $welder_id > 0) {
            $visit_date = isset($_POST['visit_date']) ? $conn->real_escape_string($_POST['visit_date']) : '';
            $visit_time = isset($_POST['visit_time']) ? $conn->real_escape_string($_POST['visit_time']) : '';
            
            // Format time if needed (e.g. 13:00 to 01:00 PM)
            if ($visit_time) {
                $visit_time = date('h:i A', strtotime($visit_time));
            }
            
            $updateQ = "UPDATE appointments SET status = 'Approved', welder_id = $welder_id";
            if ($visit_date) $updateQ .= ", appointment_date = '$visit_date'";
            if ($visit_time) $updateQ .= ", appointment_time = '$visit_time'";
            $updateQ .= " WHERE id = $id";
            
            $conn->query($updateQ);
        } 
        elseif ($status === 'Completed') {
            $visit_date = isset($_POST['visit_date']) ? $conn->real_escape_string($_POST['visit_date']) : '';
            $visit_time = isset($_POST['visit_time']) ? $conn->real_escape_string($_POST['visit_time']) : '';
            if ($visit_time) { $visit_time = date('h:i A', strtotime($visit_time)); }

            // Mark Met / Done
            $conn->query("UPDATE appointments SET status = 'Completed', welder_id = IF($welder_id > 0, $welder_id, welder_id) WHERE id = $id");
            
            // Get appointment details
            $appt = $conn->query("SELECT * FROM appointments WHERE id = $id")->fetch_assoc();
            if ($appt) {
                $cust_id = (int)($appt['user_id'] ?? 0);
                $branch_id = (int)$appt['branch_id'];
                $cust_name = $conn->real_escape_string($appt['customer_name']);
                $address = $conn->real_escape_string($appt['address'] ?? '');
                $welder = $welder_id > 0 ? $welder_id : (int)$appt['welder_id'];
                
                $v_date = $visit_date ?: $conn->real_escape_string($appt['appointment_date']);
                $v_time = $visit_time ?: $conn->real_escape_string($appt['appointment_time']);

                // Create custom order (Ongoing project status)
                $proj_name = $cust_name . " - Customized Fabrication Project";
                $conn->query("
                    INSERT INTO custom_orders (customer_id, branch_id, customer_name, project_name, status, order_type, material, dimensions, instructions, assigned_welder_id, welder_visit_date, welder_visit_time)
                    VALUES ($cust_id, $branch_id, '$cust_name', '$proj_name', 'On-going', 'online', 'TBD', 'TBD', 'Pending welder input of project dimensions and materials.', $welder, '$v_date', '$v_time')
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
