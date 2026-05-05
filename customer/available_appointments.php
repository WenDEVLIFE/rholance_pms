<?php
session_start();
include '../config/database.php';
include '../includes/auth_check.php';

/* ===============================
   USER
=============================== */
$user_id = $_SESSION['user_id'];

/* ===============================
   SUCCESS MESSAGE FIX
=============================== */
$showSuccess = isset($_GET['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointments</title>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/customer-dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="app-layout">

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/header.php'; ?>

<div class="main customer-dashboard">

<!-- SUCCESS -->
<?php if ($showSuccess): ?>
<div id="successAlert" class="success-alert">
    Appointment request submitted successfully!
</div>

<script>
    // Remove ?success after showing
    window.history.replaceState(null, null, window.location.pathname);
</script>
<?php endif; ?>


<!-- ===============================
     AVAILABLE DATES
================================ -->
<div class="card">

    <h2>Available Appointment Dates</h2>
    <p class="card-subtitle">Check available schedule before requesting a service</p>

    <div class="calendar">

    <?php
    for ($i = 0; $i < 14; $i++):
        $date = date('Y-m-d', strtotime("+$i days"));
        $display = date('M d', strtotime($date));

        /* ALL SLOTS */
        $slotsQuery = mysqli_query($conn, "
            SELECT appointment_time 
            FROM appointment_slots 
            WHERE appointment_date = '$date'
            AND status = 'Available'
        ");

        $allSlots = [];
        while ($row = mysqli_fetch_assoc($slotsQuery)) {
            $allSlots[] = trim($row['appointment_time']);
        }

        /* BOOKED */
        $bookedQuery = mysqli_query($conn, "
            SELECT appointment_time 
            FROM appointments 
            WHERE appointment_date = '$date'
            AND status IN ('Pending','Completed')
        ");

        $bookedSlots = [];
        while ($row = mysqli_fetch_assoc($bookedQuery)) {
            $bookedSlots[] = trim($row['appointment_time']);
        }

        $availableSlots = array_diff($allSlots, $bookedSlots);

        /* STATUS */
        if (empty($allSlots)) {
            $class = 'disabled';
        } elseif (empty($availableSlots)) {
            $class = 'full';
        } else {
            $class = 'available';
        }
    ?>

    <div class="day <?= $class ?>">

        <strong><?= $display ?></strong>

        <?php if (empty($allSlots)): ?>
            <span>No Schedule</span>

        <?php elseif (empty($availableSlots)): ?>
            <span>Fully Booked</span>

        <?php else: ?>
            <span>Available</span>
        <?php endif; ?>

        <?php if (!empty($availableSlots)): ?>
            <div class="time-slots">
                <?php foreach ($availableSlots as $time): ?>
                    <div class="slot book-slot"
                         data-date="<?= $date ?>"
                         data-time="<?= htmlspecialchars($time) ?>">
                        <?= htmlspecialchars($time) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <?php endfor; ?>

    </div>

    <div class="legend">
        <span class="available">Available</span>
        <span class="full">Fully Booked</span>
        <span class="disabled">No Schedule</span>
    </div>

</div>


<!-- ===============================
     MY APPOINTMENTS (FIXED)
================================ -->
<div class="card">

    <h2>My Appointments</h2>
    <p class="card-subtitle">Track your scheduled appointments</p>

    <?php
    /* 🔥 FETCH HERE (IMPORTANT FIX) */
    $result = mysqli_query($conn, "
        SELECT a.*, b.name AS branch_name
        FROM appointments a
        LEFT JOIN branches b ON a.branch_id = b.id
        WHERE a.user_id = '$user_id'
        ORDER BY a.appointment_date DESC
    ");
    ?>

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

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
    <td><?= htmlspecialchars($row['appointment_time']) ?></td>
    <td><?= htmlspecialchars($row['branch_name'] ?? 'N/A') ?></td>

    <!-- STATUS -->
    <td>
        <span class="status <?= strtolower($row['status']) ?>">
            <?= htmlspecialchars($row['status']) ?>
        </span>
    </td>

    <!-- ✅ ACTIONS (ADD THIS) -->
    <td>
        <?php if ($row['status'] === 'Pending' || $row['status'] === 'Approved'): ?>

            <button class="btn-reschedule"
                data-id="<?= $row['id'] ?>"
                data-date="<?= $row['appointment_date'] ?>"
                data-time="<?= $row['appointment_time'] ?>">
                Reschedule
            </button>

            <button class="btn-cancel"
                data-id="<?= $row['id'] ?>">
                Cancel
            </button>

        <?php else: ?>
            —
        <?php endif; ?>
    </td>

</tr>
<?php endwhile; ?>
                  

            <?php else: ?>

                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i><br><br>
                        No appointments yet
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

</div>
</div>


<!-- ===============================
     MODAL
================================ -->
<div id="bookingModal" class="booking-modal">
    <div class="modal-content">

        <h3>Book Appointment</h3>

        <form method="POST" action="request_appointment.php">

            <input type="hidden" name="appointment_date" id="modalDate">
            <input type="hidden" name="appointment_time" id="modalTime">

            <p><strong>Date:</strong> <span id="displayDate"></span></p>
            <p><strong>Time:</strong> <span id="displayTime"></span></p>

            <p><strong>Customer:</strong> <?= $_SESSION['full_name'] ?? $_SESSION['name'] ?></p>

            <textarea name="address" placeholder="Address" required></textarea>
            <input type="text" name="landmark" placeholder="Landmark">

            <button type="submit" class="btn-primary">Confirm Booking</button>
            <button type="button" id="closeModal" class="btn-secondary">Cancel</button>

        </form>

    </div>
</div>


<!-- ===============================
     JS
================================ -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById('bookingModal');
    modal.style.display = "none";

    document.querySelectorAll('.book-slot').forEach(slot => {
        slot.addEventListener('click', function () {

            document.getElementById('modalDate').value = this.dataset.date;
            document.getElementById('modalTime').value = this.dataset.time;

            document.getElementById('displayDate').innerText = this.dataset.date;
            document.getElementById('displayTime').innerText = this.dataset.time;

            modal.style.display = 'flex';
        });
    });

    document.getElementById('closeModal').onclick = () => modal.style.display = 'none';

    window.onclick = (e) => {
        if (e.target === modal) modal.style.display = 'none';
    };

    /* SUCCESS AUTO HIDE */
    const alertBox = document.getElementById('successAlert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }, 1500);
    }

});

/* =========================
   CANCEL APPOINTMENT
========================= */
document.querySelectorAll('.btn-cancel').forEach(btn => {
    btn.addEventListener('click', function () {

        const id = this.dataset.id;

        if (!confirm("Are you sure you want to cancel this appointment?")) return;

        fetch('cancel_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'appointment_id=' + id
        })
        .then(res => res.text())
        .then(() => {
            location.reload(); // refresh after cancel
        });
    });
});


/* =========================
   RESCHEDULE
========================= */
document.querySelectorAll('.btn-reschedule').forEach(btn => {
    btn.addEventListener('click', function () {

        const id = this.dataset.id;
        const date = this.dataset.date;
        const time = this.dataset.time;

        if (!confirm("Reschedule this appointment? Your current slot will be cancelled.")) return;

        // Cancel first
        fetch('cancel_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'appointment_id=' + id
        })
        .then(() => {

            // Open modal again for new booking
            document.getElementById('modalDate').value = date;
            document.getElementById('modalTime').value = '';

            document.getElementById('displayDate').innerText = date;
            document.getElementById('displayTime').innerText = "Select new time";

            document.getElementById('bookingModal').style.display = 'flex';

        });
    });
});
</script>

</body>
</html>