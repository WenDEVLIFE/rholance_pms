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

            // Fetch appointment details to create the custom order for the welder
            $appt = $conn->query("SELECT * FROM appointments WHERE id = $id")->fetch_assoc();
            if ($appt) {
                $cust_id = (int)($appt['user_id'] ?? 0);
                $branch_id = (int)$appt['branch_id'];
                $cust_name = $conn->real_escape_string($appt['customer_name']);
                $v_date = $visit_date ?: $conn->real_escape_string($appt['appointment_date']);
                $v_time = $visit_time ?: $conn->real_escape_string($appt['appointment_time']);
                $proj_name = $cust_name . " - Customized Fabrication Project";
                
                // We create the custom_order instantly so the welder can see it on their dashboard and quote it
                // We check if it already exists by name/customer_id to avoid duplicates on re-assign
                $existing = $conn->query("SELECT id FROM custom_orders WHERE customer_id=$cust_id AND project_name='$proj_name' AND status IN ('Appointment', 'Pending Review') AND assigned_welder_id=$welder_id LIMIT 1")->fetch_assoc();
                
                if (!$existing) {
                    $conn->query("
                        INSERT INTO custom_orders (customer_id, branch_id, customer_name, project_name, status, order_type, material, dimensions, instructions, assigned_welder_id, welder_visit_date, welder_visit_time)
                        VALUES ($cust_id, $branch_id, '$cust_name', '$proj_name', 'Appointment', 'online', 'TBD', 'TBD', 'Pending welder input of project dimensions and materials.', $welder_id, '$v_date', '$v_time')
                    ");
                } else {
                    // Just update visit dates
                    $conn->query("UPDATE custom_orders SET welder_visit_date='$v_date', welder_visit_time='$v_time' WHERE id=" . $existing['id']);
                }
            }

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
                $cust_name = $conn->real_escape_string($appt['customer_name']);
                $proj_name = $cust_name . " - Customized Fabrication Project";

                // Update the existing custom_order status to On-going instead of creating a new one
                $conn->query("UPDATE custom_orders SET status='On-going' WHERE customer_id=$cust_id AND project_name='$proj_name' AND status='Appointment'");
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
