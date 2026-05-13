<?php
session_start();
include '../config/database.php';
include '../includes/auth_check.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$user_id    = $_SESSION['user_id'];
$showSuccess = isset($_GET['success']);

/* ── Build 14-day availability grid ── */
$calDays = [];
for ($i = 0; $i < 14; $i++) {
    $date    = date('Y-m-d', strtotime("+$i days"));
    $display = date('M d', strtotime($date));
    $dayName = date('D',   strtotime($date));

    $allSlotsQ = mysqli_query($conn, "SELECT appointment_time FROM appointment_slots WHERE appointment_date='$date' AND status='Available'");
    $allSlots  = mysqli_fetch_all($allSlotsQ, MYSQLI_ASSOC);
    $allTimes  = array_column($allSlots, 'appointment_time');

    $bookedQ   = mysqli_query($conn, "SELECT appointment_time FROM appointments WHERE appointment_date='$date' AND status IN ('Pending','Completed')");
    $booked    = array_column(mysqli_fetch_all($bookedQ, MYSQLI_ASSOC), 'appointment_time');

    $available = array_diff($allTimes, $booked);

    if (empty($allTimes))     $status = 'disabled';
    elseif (empty($available)) $status = 'full';
    else                       $status = 'available';

    $calDays[] = ['date'=>$date,'display'=>$display,'dayName'=>$dayName,'available'=>$available,'status'=>$status];
}

/* ── Customer's appointments ── */
$myAppts = mysqli_query($conn, "SELECT * FROM appointments WHERE user_id=$user_id ORDER BY appointment_date DESC");
?>

<div class="rh-main">

    <!-- SUCCESS TOAST -->
    <?php if ($showSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fas fa-circle-check"></i>
        <strong>Success!</strong> Your appointment request has been submitted.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>window.history.replaceState(null,'',window.location.pathname);</script>
    <?php endif; ?>

    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>Appointments</h1>
            <p>Book a schedule — <strong>Cavite &amp; Laguna branches only</strong></p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookModal">
            <i class="fas fa-calendar-plus me-2"></i>Book Appointment
        </button>
    </div>

    <!-- ── 14-DAY CALENDAR GRID ── -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-calendar me-2 text-amber"></i>Available Dates (Next 14 Days)</div>
        <div class="card-body">
            <div class="row g-2">
                <?php foreach ($calDays as $day): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <?php if ($day['status'] === 'available'): ?>
                        <button class="btn btn-outline-warning w-100 py-3 day-pick"
                                data-date="<?= $day['date'] ?>"
                                data-slots='<?= json_encode(array_values($day['available'])) ?>'>
                            <div class="small fw-800"><?= $day['dayName'] ?></div>
                            <div class="fs-6"><?= $day['display'] ?></div>
                            <div class="badge bg-success mt-1"><?= count($day['available']) ?> open</div>
                        </button>
                    <?php elseif ($day['status'] === 'full'): ?>
                        <div class="btn btn-outline-danger w-100 py-3 disabled opacity-75">
                            <div class="small fw-800"><?= $day['dayName'] ?></div>
                            <div class="fs-6"><?= $day['display'] ?></div>
                            <div class="badge bg-danger mt-1">Full</div>
                        </div>
                    <?php else: ?>
                        <div class="btn btn-light w-100 py-3 disabled opacity-50">
                            <div class="small fw-800"><?= $day['dayName'] ?></div>
                            <div class="fs-6"><?= $day['display'] ?></div>
                            <div class="badge bg-secondary mt-1">No slots</div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── MY APPOINTMENTS ── -->
    <div class="card">
        <div class="card-header"><i class="fas fa-list me-2 text-amber"></i>My Appointments</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Date</th><th>Time</th><th>Address</th><th>Landmark</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($myAppts) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($myAppts)):
                        $bc = 'badge-' . strtolower($row['status']); ?>
                    <tr>
                        <td class="fw-600"><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                        <td class="small"><?= htmlspecialchars($row['appointment_time']) ?></td>
                        <td class="small"><?= htmlspecialchars($row['address'] ?? '—') ?></td>
                        <td class="small"><?= htmlspecialchars($row['landmark'] ?? '—') ?></td>
                        <td><span class="badge <?= $bc ?>"><?= $row['status'] ?></span></td>
                        <td>
                            <?php if ($row['status'] === 'Pending'): ?>
                                <a href="cancel_appointment.php?id=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Cancel this appointment?')">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                            <?php elseif ($row['status'] === 'Approved'): ?>
                                <span class="badge badge-approved"><i class="fas fa-check me-1"></i>Confirmed</span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-xmark fs-2 mb-2 d-block opacity-25"></i>
                            No appointments yet.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── BOOK APPOINTMENT MODAL ── -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-plus me-2 text-amber"></i>Book Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="request_appointment.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" id="bookDate" name="appointment_date" class="form-control"
                                   min="<?= date('Y-m-d') ?>" required readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Time</label>
                            <select name="appointment_time" id="bookTime" class="form-select" required>
                                <option value="">— Select a date first —</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select" required>
                                <option value="" disabled selected>Select branch</option>
                                <option value="1">Dasmariñas, Cavite</option>
                                <option value="2">Biñan, Laguna</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Your Address <span class="text-muted fw-400">(Cavite or Laguna only)</span></label>
                            <input type="text" name="address" class="form-control" placeholder="Full address" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Landmark</label>
                            <input type="text" name="landmark" class="form-control" placeholder="Near...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="Name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Request Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/* Calendar day → modal pre-fill */
const allDayData = <?= json_encode(array_column($calDays, null, 'date')) ?>;
const bookModal  = new bootstrap.Modal(document.getElementById('bookModal'));

document.querySelectorAll('.day-pick').forEach(btn => {
    btn.addEventListener('click', () => {
        const date  = btn.dataset.date;
        const slots = JSON.parse(btn.dataset.slots);

        document.getElementById('bookDate').value = date;
        const sel = document.getElementById('bookTime');
        sel.innerHTML = '';
        slots.forEach(s => {
            const o = document.createElement('option');
            o.value = o.textContent = s;
            sel.appendChild(o);
        });
        bookModal.show();
    });
});
</script>
</body></html>