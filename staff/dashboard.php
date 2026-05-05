<?php
require_once '../config/database.php';

/* COUNTS */
function getCount($conn, $query){
    $res = $conn->query($query);
    return $res ? $res->fetch_assoc()['total'] : 0;
}

$todayCount = getCount($conn,"SELECT COUNT(*) as total FROM appointments WHERE appointment_date = CURDATE()");
$pendingCount = getCount($conn,"SELECT COUNT(*) as total FROM appointments WHERE status='Pending'");
$approvedCount = getCount($conn,"SELECT COUNT(*) as total FROM appointments WHERE status='Approved'");
$completedCount = getCount($conn,"SELECT COUNT(*) as total FROM appointments WHERE status='Completed'");

/* TODAY */
$today = $conn->query("
    SELECT * FROM appointments
    WHERE appointment_date = CURDATE()
    ORDER BY appointment_time ASC
");

/* PENDING */
$pending = $conn->query("
    SELECT * FROM appointments
    WHERE status='Pending'
    ORDER BY id DESC
");

function e($str){
    return htmlspecialchars($str ?? '', ENT_QUOTES);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Dashboard</title>

<link rel="stylesheet" href="../assets/css/staff-dashboard.css">

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- HEADER -->
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- MAIN -->
<div class="dashboard">

<!-- ================= STATS ================= -->
<div class="dashboard-grid">

    <div class="stat-card">
        <h3><?= $todayCount ?></h3>
        <p>Today</p>
    </div>

    <div class="stat-card">
        <h3><?= $pendingCount ?></h3>
        <p>Pending</p>
    </div>

    <div class="stat-card">
        <h3><?= $approvedCount ?></h3>
        <p>Approved</p>
    </div>

    <div class="stat-card">
        <h3><?= $completedCount ?></h3>
        <p>Completed</p>
    </div>

</div>

<!-- ================= QUICK ACTIONS ================= -->
<div class="quick-actions">
    <a href="appointment.php" class="btn-action">Add Walk-in</a>
    <a href="add_slot.php" class="btn-action">Set Slots</a>
    <a href="custom_orders.php" class="btn-action">Custom Orders</a>
</div>

<!-- ================= TODAY ================= -->
<div class="dashboard-card">
    <h3>Today’s Schedule</h3>

    <?php if($today && $today->num_rows): ?>
        <?php while($row = $today->fetch_assoc()): ?>
            <div class="item">
                <strong><?= date("h:i A", strtotime($row['appointment_time'])) ?></strong>
                —
                <?= !empty($row['customer_name']) ? e($row['customer_name']) : 'Walk-in Customer' ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No appointments today</p>
    <?php endif; ?>

</div>

<!-- ================= PENDING ================= -->
<div class="dashboard-card">
    <h3>Pending Approvals</h3>

    <?php if($pending && $pending->num_rows): ?>
        <?php while($row = $pending->fetch_assoc()): ?>
            <div class="item-flex">

                <div>
                    <strong>
                        <?= !empty($row['customer_name']) ? e($row['customer_name']) : 'Walk-in Customer' ?>
                    </strong><br>
                    <small><?= e($row['appointment_date']) ?></small>
                </div>

                <div>
                    <a href="approved_appointment.php?id=<?= $row['id'] ?>" class="btn-approve">Approve</a>
                    <a href="reject_appointment.php?id=<?= $row['id'] ?>" class="btn-reject">Reject</a>
                </div>

            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No pending approvals</p>
    <?php endif; ?>

</div>

</main>

</body>
</html>