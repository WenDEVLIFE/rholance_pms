<?php
require_once '../config/database.php';

date_default_timezone_set('Asia/Manila');

require_once '../config/database.php';

$branch_id = $_GET['branch_id'] ?? 1;
$week_offset = $_GET['week'] ?? 0;

/* ===============================
   WEEK CALCULATION
   =============================== */
$week_start = date('Y-m-d', strtotime("monday this week $week_offset week"));
$week_end   = date('Y-m-d', strtotime("sunday this week $week_offset week"));

$display_start = date('F j', strtotime($week_start));
$display_end   = date('j, Y', strtotime($week_end));

/* ===============================
   FILE NAME WITH DATE
   =============================== */
$filename = "attendance_{$week_start}_to_{$week_end}.csv";

/* ===============================
   HEADERS
   =============================== */
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=$filename");

$output = fopen("php://output", "w");

/* ===============================
   REPORT HEADER (IMPORTANT)
   =============================== */
fputcsv($output, ["RHOLANCE TRADING SYSTEM"]);
fputcsv($output, ["Attendance Report"]);
fputcsv($output, ["Week: $display_start - $display_end"]);
fputcsv($output, ["Generated on: " . date('F j, Y h:i A')]);
fputcsv($output, []); // empty line

/* ===============================
   TABLE HEADER
   =============================== */
$header = ['Name', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
fputcsv($output, $header);

/* ===============================
   FETCH STAFF
   =============================== */
$staff_stmt = $conn->prepare("
    SELECT id, name 
    FROM users 
    WHERE role = 'staff' AND branch_id = ?
    ORDER BY name ASC
");
$staff_stmt->bind_param("i", $branch_id);
$staff_stmt->execute();
$staff_result = $staff_stmt->get_result();

/* ===============================
   FETCH ATTENDANCE
   =============================== */
$attendance_stmt = $conn->prepare("
    SELECT user_id, date, status 
    FROM attendance 
    WHERE date BETWEEN ? AND ?
");
$attendance_stmt->bind_param("ss", $week_start, $week_end);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();

/* ===============================
   ORGANIZE DATA
   =============================== */
$attendance_data = [];

while ($row = $attendance_result->fetch_assoc()) {
    $attendance_data[$row['user_id']][$row['date']] = $row['status'];
}

/* ===============================
   BUILD CSV ROWS
   =============================== */
while ($staff = $staff_result->fetch_assoc()) {

    $row = [$staff['name']];

    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("$week_start +$i days"));
        $status = $attendance_data[$staff['id']][$date] ?? '';

        if ($status === 'Present') {
            $row[] = 'P';
        } elseif ($status === 'Absent') {
            $row[] = 'A';
        } else {
            $row[] = '';
        }
    }

    fputcsv($output, $row);
}

fclose($output);
exit;