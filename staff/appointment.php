<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

/* ================= FETCH APPOINTMENTS ================= */
$query = "
    SELECT *
    FROM appointments
    WHERE customer_name IS NOT NULL
      AND customer_name != ''
      AND customer_name != 'Available Slot'
    ORDER BY 
        FIELD(status, 'Pending','Approved','Completed','Rejected'),
        appointment_date ASC,
        appointment_time ASC
";

$result = $conn->query($query);
if (!$result) die("Query Error: " . $conn->error);

$appointments = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointments</title>

<link rel="stylesheet" href="/rholance_pms/assets/css/staff-dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="dashboard">




<h2 class="appointments-title">Appointments Management</h2>

<!-- ================= TOP SECTION ================= -->
<div class="appointment-container">

    <!-- LEFT: WALK-IN FORM -->
    <div class="appointment-card">
        <h3><i class="fa fa-user"></i> Walk-in Appointment</h3>

        <form action="save_walkin.php" method="POST">

            <div class="form-grid">
                <input type="text" name="customer_name" placeholder="Customer Name" required>
                <input type="date" name="appointment_date" required>

                <select name="appointment_time" required>
                    <option value="">Select Time</option>
                    <option>09:00 AM</option>
                    <option>10:00 AM</option>
                    <option>11:00 AM</option>
                    <option>01:00 PM</option>
                    <option>02:00 PM</option>
                    <option>03:00 PM</option>
                </select>

                <input type="text" name="address" placeholder="Address">
                <input type="text" name="landmark" placeholder="Landmark">
                <input type="text" name="contact_person" placeholder="Assigned Contact Person">
            </div>

            <button type="submit" class="btn-primary">
                <i class="fa fa-save"></i> Save Appointment
            </button>

        </form>
    </div>

    <!-- RIGHT: SLOT -->
    <div class="appointment-card">
        <h3><i class="fa-solid fa-clock"></i> Set Available Slot</h3>

        <form action="add_slot.php" method="POST">

            <div class="form-grid">
                <input type="date" name="appointment_date" required>

                <select name="appointment_time" required>
                    <option value="">Select Time</option>
                    <option>09:00 AM</option>
                    <option>10:00 AM</option>
                    <option>11:00 AM</option>
                    <option>01:00 PM</option>
                    <option>02:00 PM</option>
                    <option>03:00 PM</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fa fa-plus"></i> Add Slot
            </button>

        </form>
    </div>

</div>

<!-- ================= TABLE ================= -->
<h3 class="appointments-title">Incoming Appointments</h3>

<table class="appointments-table">
<thead>
<tr>
<th>Customer</th>
<th>Date</th>
<th>Time</th>
<th>Address</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if (!empty($appointments)): ?>
<?php foreach ($appointments as $row): ?>
<tr>

<td><?= htmlspecialchars($row['customer_name']) ?></td>

<td><?= date("M d, Y", strtotime($row['appointment_date'])) ?></td>

<td><?= date("h:i A", strtotime($row['appointment_time'])) ?></td>

<td><?= htmlspecialchars($row['address'] ?? 'N/A') ?></td>

<td>
<span class="status <?= strtolower($row['status']) ?>">
<?= $row['status'] ?>
</span>
</td>

<td>

<?php if ($row['status'] === 'Pending'): ?>

<a href="approve_appointment.php?id=<?= $row['id'] ?>" class="btn btn-approve">Approve</a>
<a href="reject_appointment.php?id=<?= $row['id'] ?>" class="btn btn-reject">Reject</a>

<?php elseif ($row['status'] === 'Approved'): ?>

<a href="complete_appointment.php?id=<?= $row['id'] ?>" class="btn btn-complete">Complete</a>

<?php elseif ($row['status'] === 'Completed'): ?>

<a href="../orders/create.php?appointment_id=<?= $row['id'] ?>" class="btn btn-order">
Proceed to Order
</a>

<?php endif; ?>

</td>

</tr>
<?php endforeach; ?>
<?php else: ?>

<tr><td colspan="6">No appointments found</td></tr>

<?php endif; ?>

</tbody>
</table>


</div>
</div>


</body>
</html>