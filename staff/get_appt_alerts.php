<?php
/**
 * API: Get new/unread appointment notifications for the logged-in staff.
 * Returns appointments that were assigned to this branch and created/updated
 * within the last 48 hours that haven't been dismissed via session.
 */
session_start();
include '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    echo json_encode(['appointments' => []]);
    exit;
}

$branch     = $_SESSION['branch_id'] ?? 1;
$dismissed  = $_SESSION['dismissed_appt_alerts'] ?? [];

// Build exclusion list
$excl = empty($dismissed) ? '' : ('AND a.id NOT IN (' . implode(',', array_map('intval', $dismissed)) . ')');

$sql = "
    SELECT a.id, a.customer_name, a.appointment_date, a.appointment_time,
           a.address, a.requested_project, a.contact_person,
           u.email cust_email, u.phone cust_phone
    FROM appointments a
    LEFT JOIN users u ON u.id = a.user_id
    WHERE (a.branch_id = ? OR (a.branch_id IS NULL AND a.address LIKE ?))
      AND a.status IN ('Pending','Approved')
      AND a.created_at >= NOW() - INTERVAL 48 HOUR
      $excl
    ORDER BY a.appointment_date ASC
    LIMIT 5
";

$addressSearch = '%' . ($branch == 1 ? 'cavite' : 'laguna') . '%';
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $branch, $addressSearch);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Format dates
foreach ($rows as &$r) {
    $r['date_text'] = strtoupper(date('F j, Y', strtotime($r['appointment_date'])));
    $r['time_text'] = strtoupper($r['appointment_time'] ?? 'TBD');
}
unset($r);

echo json_encode(['appointments' => $rows]);
