<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

include '../includes/header.php';
include '../includes/sidebar.php';

$branch     = $_SESSION['branch_id'] ?? null;
$filterDate = $_GET['date'] ?? date('Y-m-d');

/* Appointments for this date & branch */
$stmt = $conn->prepare("
    SELECT a.*, u.email cust_email, b.name branch_name
    FROM appointments a
    LEFT JOIN users u ON u.id = a.user_id
    LEFT JOIN branches b ON b.id = a.branch_id
    WHERE a.appointment_date = ?
      AND (a.branch_id = ? OR a.branch_id IS NULL)
      AND a.customer_name IS NOT NULL AND a.customer_name != ''
    ORDER BY FIELD(a.status,'Pending','Approved','Completed','Rejected'), a.appointment_time ASC
");
$stmt->bind_param("si", $filterDate, $branch);
$stmt->execute();
$appts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* Date strip: 3 days before + 14 days ahead */
$dateStrip = [];
for ($i = -3; $i <= 14; $i++) {
    $dateStrip[] = date('Y-m-d', strtotime("$i days"));
}

function statusBadge($s) {
    $map = ['Pending'=>'badge-pending','Approved'=>'badge-approved','Completed'=>'badge-completed','Rejected'=>'badge-rejected'];
    $cls = $map[$s] ?? 'bg-secondary';
    return "<span class=\"badge $cls\">$s</span>";
}
?>

<div class="rh-main">

    <!-- PAGE HEADER -->
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>Appointment Management</h1>
            <p>Scope: <strong>Cavite &amp; Laguna branches only</strong></p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#walkinModal">
            <i class="fas fa-user-plus me-2"></i>Walk-in Appointment
        </button>
    </div>

    <!-- DATE FILTER -->
    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label class="form-label mb-1 small fw-700">Jump to Date</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-dark"><i class="fas fa-search me-1"></i>Filter</button>
        </div>
    </form>

    <!-- DATE STRIP -->
    <div class="d-flex gap-2 overflow-auto pb-2 mb-4" style="scrollbar-width:thin;">
        <?php foreach ($dateStrip as $d): ?>
            <?php $active = $d === $filterDate; ?>
            <a href="?date=<?= $d ?>"
               class="btn btn-sm flex-shrink-0 text-center <?= $active ? 'btn-warning fw-800' : 'btn-outline-secondary' ?>"
               style="min-width:70px;padding:6px 10px;">
                <div style="font-size:.7rem;font-weight:800;"><?= date('D',$t=strtotime($d)) ?></div>
                <div style="font-size:.75rem;"><?= date('M d',$t) ?></div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- APPOINTMENTS TABLE -->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-list me-2 text-amber"></i>
                Appointments — <?= date('F d, Y', strtotime($filterDate)) ?>
            </span>
            <span class="badge bg-secondary"><?= count($appts) ?> records</span>
        </div>

        <?php if (empty($appts)): ?>
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-calendar-xmark fs-1 mb-3 d-block opacity-25"></i>
                No appointments found for this date.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Time</th>
                        <th>Address</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($appts as $row):
                    $addr    = strtolower($row['address'] ?? '');
                    $inScope = str_contains($addr,'cavite') || str_contains($addr,'laguna') || !empty($row['branch_id']);
                ?>
                    <tr>
                        <td>
                            <div class="fw-700"><?= htmlspecialchars($row['customer_name']) ?></div>
                            <?php if (!$inScope && !empty($row['address'])): ?>
                                <span class="rh-scope-warn mt-1 d-inline-block">
                                    <i class="fas fa-exclamation-triangle"></i> Out of Scope
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= htmlspecialchars($row['appointment_time']) ?></td>
                        <td class="small"><?= htmlspecialchars($row['address'] ?? 'N/A') ?></td>
                        <td class="small"><?= htmlspecialchars($row['branch_name'] ?? '—') ?></td>
                        <td><?= statusBadge($row['status']) ?></td>

                        <!-- CONTACT INFO POPOVER -->
                        <td class="rh-info-cell">
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-info-circle text-amber"></i>
                            </button>
                            <div class="rh-info-pop" style="min-width:220px;">
                                <p><strong>Address:</strong> <?= htmlspecialchars($row['address'] ?? 'N/A') ?></p>
                                <p><strong>Landmark:</strong> <?= htmlspecialchars($row['landmark'] ?? 'N/A') ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($row['cust_email'] ?? 'N/A') ?></p>
                                <p><strong>Contact:</strong> <?= htmlspecialchars($row['contact_person'] ?? 'N/A') ?></p>
                                <?php if (!$inScope && !empty($row['address'])): ?>
                                    <p class="text-danger fw-700 mt-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Address may be out of scope. Only Cavite &amp; Laguna accepted.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <a href="process_appointment.php?id=<?= $row['id'] ?>&status=Approved"
                                       class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></a>
                                    <a href="process_appointment.php?id=<?= $row['id'] ?>&status=Rejected"
                                       class="btn btn-sm btn-danger" title="Reject"><i class="fas fa-times"></i></a>
                                <?php elseif ($row['status'] === 'Approved'): ?>
                                    <a href="process_appointment.php?id=<?= $row['id'] ?>&status=Completed"
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-flag-checkered me-1"></i>Complete
                                    </a>
                                <?php elseif ($row['status'] === 'Completed'): ?>
                                    <a href="../orders/orders.php?status=active"
                                       class="btn btn-sm btn-outline-primary">View Orders →</a>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ADD SLOT CARD -->
    <div class="card">
        <div class="card-header"><i class="fas fa-clock me-2 text-amber"></i>Set Available Slot</div>
        <div class="card-body">
            <form action="add_slot.php" method="POST" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" name="appointment_date" class="form-control" value="<?= $filterDate ?>" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Time</label>
                    <select name="appointment_time" class="form-select" required>
                        <option value="">Select Time</option>
                        <?php foreach (['09:00 AM','10:00 AM','11:00 AM','01:00 PM','02:00 PM','03:00 PM'] as $t): ?>
                            <option><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="fas fa-plus me-1"></i>Add Slot
                    </button>
                </div>
            </form>
        </div>
    </div>

</div><!-- /.rh-main -->

<!-- WALK-IN MODAL -->
<div class="modal fade" id="walkinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-amber"></i>Add Walk-in Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="save_walkin.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Full name" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="appointment_date" class="form-control" value="<?= $filterDate ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Time</label>
                            <select name="appointment_time" class="form-select" required>
                                <option value="">Select Time</option>
                                <?php foreach (['09:00 AM','10:00 AM','11:00 AM','01:00 PM','02:00 PM','03:00 PM'] as $t): ?>
                                    <option><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="Contact name">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address <span class="text-muted small">(Cavite or Laguna only)</span></label>
                            <input type="text" name="address" class="form-control" placeholder="Full address">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Landmark</label>
                            <input type="text" name="landmark" class="form-control" placeholder="Near...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body></html>