<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

/* ===============================
   FETCH CUSTOMER APPOINTMENTS (SAFE)
=============================== */
$stmt = $conn->prepare("
    SELECT 
        a.*,
        COALESCE(b.name, 'No Branch') AS branch_name
    FROM appointments a
    LEFT JOIN branches b ON a.branch_id = b.id
    WHERE a.user_id = ?
    AND a.status IN ('Pending', 'Approved')
    ORDER BY a.appointment_date DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

/* FETCH ALL DATA ONCE (IMPORTANT FIX) */
$appointments = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Appointments</title>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/customer-dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* Modern Table Enhancements */
.table-wrapper {
    overflow-x: auto;
    margin-top: 15px;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table th {
    text-align: left;
    padding: 12px;
    font-size: 13px;
    opacity: 0.8;
}

.modern-table td {
    padding: 14px 12px;
}

.modern-table tr {
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.modern-table tr:hover {
    background: rgba(255,255,255,0.03);
}

/* Status Styles */
.status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status.pending {
    background: #facc15;
    color: #000;
}

.status.approved {
    background: #3b82f6;
    color: #fff;
}

.status.completed {
    background: #22c55e;
    color: #fff;
}

.status.cancelled {
    background: #ef4444;
    color: #fff;
}

/* Buttons */
.btn-reschedule, .btn-cancel {
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    border: none;
    cursor: pointer;
}

.btn-reschedule {
    background: #3b82f6;
    color: white;
}

.btn-cancel {
    background: #ef4444;
    color: white;
}

.no-action {
    opacity: 0.5;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 20px;
    opacity: 0.7;
}
</style>

</head>

<body>

<div class="app-layout">

    <!-- SIDEBAR -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- HEADER -->
    <?php include '../includes/header.php'; ?>

    <!-- MAIN -->
    <div class="main customer-dashboard">

        <div class="card">
            <h2>My Appointments</h2>
            <p class="card-subtitle">Track your scheduled appointments</p>

            <div class="table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($appointments)): ?>

                        <?php foreach ($appointments as $row): ?>
                            <?php 
                                $status = strtolower(trim($row['status'] ?? 'unknown'));
                            ?>

                            <tr>
                                <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                                <td><?= htmlspecialchars($row['appointment_time'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['branch_name']) ?></td>

                                <td>
                                    <span class="status <?= $status ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (in_array($status, ['pending','approved'])): ?>
                                        <button class="btn-reschedule" data-id="<?= $row['id'] ?>">
                                            Reschedule
                                        </button>

                                        <button class="btn-cancel" data-id="<?= $row['id'] ?>">
                                            Cancel
                                        </button>
                                    <?php else: ?>
                                        <span class="no-action">No actions</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                           <td colspan="5" class="empty-state">
                           No active appointments (Pending/Approved)
                           </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>

        </div>

    </div>

</div>

</body>
</html>