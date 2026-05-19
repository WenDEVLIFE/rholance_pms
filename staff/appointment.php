<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

include '../includes/header.php';
include '../includes/sidebar.php';

$branch = $_SESSION['branch_id'] ?? 1;
$filterDate = $_GET['date'] ?? date('Y-m-d');
$branchName = $branch == 1 ? 'Cavite (Dasmariñas)' : 'Laguna (Biñan)';

// Match only Cavite location if branch=1, and Laguna if branch=2 to avoid duplication
$addressSearch = $branch == 1 ? '%cavite%' : '%laguna%';

// Fetch Appointments specifically for this branch based on branch_id OR address location
$stmt = $conn->prepare("
    SELECT a.*, u.email cust_email, u.phone cust_phone, b.name branch_name, w.name welder_name
    FROM appointments a
    LEFT JOIN users u ON u.id = a.user_id
    LEFT JOIN branches b ON b.id = a.branch_id
    LEFT JOIN users w ON w.id = a.welder_id
    WHERE a.appointment_date = ?
      AND (a.branch_id = ? OR (a.branch_id IS NULL AND a.address LIKE ?))
      AND a.customer_name IS NOT NULL AND a.customer_name != ''
    ORDER BY FIELD(a.status,'Pending','Approved','Completed','Rejected'), a.appointment_time ASC
");
$stmt->bind_param("sis", $filterDate, $branch, $addressSearch);
$stmt->execute();
$appts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch active welders in this branch for assignment
$welderRes = $conn->query("SELECT id, name FROM users WHERE role='welder' AND branch_id=$branch AND status='active' ORDER BY name ASC");
$welders = $welderRes ? $welderRes->fetch_all(MYSQLI_ASSOC) : [];

// Date strip: 3 days before + 14 days ahead
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
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1>Appointment Desk</h1>
            <p>Managing daily appointments for the <strong><?= $branchName ?></strong>. Location scope is strictly Cavite and Laguna.</p>
        </div>
        <button class="btn btn-primary shadow-sm animate-btn" data-bs-toggle="modal" data-bs-target="#walkinModal">
            <i class="fas fa-user-plus me-2"></i>Walk-in Booking
        </button>
    </div>

    <!-- DATE FILTERS & TIMELINE STRIP -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3 px-4">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label mb-1 small fw-700">Jump to Specific Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($filterDate) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-dark btn-sm fw-700"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
            </form>

            <div class="d-flex gap-2 overflow-auto pb-2" style="scrollbar-width:thin;">
                <?php foreach ($dateStrip as $d): ?>
                    <?php $active = $d === $filterDate; ?>
                    <a href="?date=<?= $d ?>"
                       class="btn btn-sm flex-shrink-0 text-center <?= $active ? 'btn-warning fw-800 shadow-sm' : 'btn-outline-secondary' ?>"
                       style="min-width:75px; padding:6px 10px;">
                        <div style="font-size:.65rem; font-weight:800; text-transform:uppercase;"><?= date('D', strtotime($d)) ?></div>
                        <div style="font-size:.8rem;"><?= date('M d', strtotime($d)) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- APPOINTMENTS TABLE -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <span class="fw-800 text-light-emphasis">
                <i class="fas fa-calendar-check me-2 text-amber"></i>Appointments for <?= date('l, F d, Y', strtotime($filterDate)) ?>
            </span>
            <span class="badge bg-secondary px-3 py-1 rounded-pill"><?= count($appts) ?> Scheduled</span>
        </div>

        <?php if (empty($appts)): ?>
            <div class="card-body text-center py-5 text-muted border-top">
                <i class="fas fa-calendar-xmark fs-1 mb-3 d-block opacity-25"></i>
                No customer appointments set for this date in your branch.
            </div>
        <?php else: ?>
        <div class="table-responsive border-top">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Customer Details</th>
                        <th>Time Window</th>
                        <th>Phone / Contact</th>
                        <th>Address / Location</th>
                        <th>Assigned Welder</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($appts as $row):
                    $addr = strtolower($row['address'] ?? '');
                    // Verify if location is Cavite or Laguna
                    $isCavite = strpos($addr, 'cavite') !== false;
                    $isLaguna = strpos($addr, 'laguna') !== false;
                    $isValidLocation = $isCavite || $isLaguna;
                ?>
                    <tr>
                        <!-- Customer Details -->
                        <td class="ps-4">
                            <div class="fw-700 text-light-emphasis"><?= htmlspecialchars($row['customer_name']) ?></div>
                            <span class="text-muted small" style="font-size:0.7rem;">ID Reference: #<?= $row['id'] ?></span>
                            <?php if (!$isValidLocation): ?>
                                <span class="badge bg-danger-subtle text-danger d-block mt-1" style="font-size:0.6rem; width:fit-content;">
                                    <i class="fas fa-exclamation-triangle"></i> Location Not Cavite/Laguna
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Time -->
                        <td class="small fw-600 text-light-emphasis"><?= htmlspecialchars($row['appointment_time']) ?></td>

                        <!-- Contact / Phone -->
                        <td class="small text-light-emphasis">
                            <div class="fw-700"><?= htmlspecialchars($row['contact_person'] ?? '—') ?></div>
                            <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($row['cust_phone'] ?? '—') ?></div>
                        </td>

                        <!-- Address -->
                        <td class="small text-light-emphasis" style="max-width:200px;">
                            <div class="text-truncate" title="<?= htmlspecialchars($row['address'] ?? '') ?>">
                                <?= htmlspecialchars($row['address'] ?? 'No address set') ?>
                            </div>
                            <span class="text-muted small" style="font-size:0.7rem;">Landmark: <?= htmlspecialchars($row['landmark'] ?? 'None') ?></span>
                        </td>

                        <!-- Assigned Welder -->
                        <td class="small">
                            <?php if (!empty($row['welder_name'])): ?>
                                <span class="badge bg-amber-subtle text-amber fw-700"><i class="fas fa-hard-hat me-1"></i><?= htmlspecialchars($row['welder_name']) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">No Welder Assigned</span>
                            <?php endif; ?>
                        </td>

                        <!-- Status Badge -->
                        <td><?= statusBadge($row['status']) ?></td>

                        <!-- Actions (Staff cannot cancel or reject, only approve, assign welder, and complete to ongoing) -->
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end align-items-center">
                                
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <!-- Approve button triggers welder assignment modal -->
                                    <button class="btn btn-sm btn-success fw-700 px-3 shadow-sm" onclick='openApprovalModal(<?= json_encode($row) ?>)'>
                                        <i class="fas fa-check-circle me-1"></i>Approve &amp; Assign
                                    </button>
                                
                                <?php elseif ($row['status'] === 'Approved'): ?>
                                    <!-- Complete and direct to Ongoing Projects -->
                                    <a href="process_appointment.php?id=<?= $row['id'] ?>&status=Completed" class="btn btn-sm btn-primary fw-700 px-3 shadow-sm" onclick="return confirm('Mark as Met/Done? This will automatically direct this customer to your Active Ongoing Projects.')">
                                        <i class="fas fa-flag-checkered me-1"></i>Complete &amp; Direct to Projects
                                    </a>
                                
                                <?php elseif ($row['status'] === 'Completed'): ?>
                                    <!-- Already complete, direct view of order -->
                                    <a href="../staff/project_management.php" class="btn btn-sm btn-outline-primary fw-700 px-2">
                                        <i class="fas fa-eye me-1"></i>View Ongoing Project
                                    </a>
                                
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

    <!-- SET AVAILABLE APPOINTMENT SLOTS FOR CUSTOMERS -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0"><i class="fas fa-clock me-2 text-amber"></i>Declare Available Scheduling Slot</div>
        <div class="card-body border-top">
            <form action="add_slot.php" method="POST" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-700">Available Date</label>
                    <input type="date" name="appointment_date" class="form-control" value="<?= $filterDate ?>" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-700">Available Time Window</label>
                    <select name="appointment_time" class="form-select" required>
                        <option value="">Select Time Slot</option>
                        <?php foreach (['09:00 AM','10:00 AM','11:00 AM','01:00 PM','02:00 PM','03:00 PM'] as $t): ?>
                            <option><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-dark w-100 fw-700">
                        <i class="fas fa-plus-circle me-1"></i>Add Available Slot
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- ASSIGN WELDER & APPROVE MODAL -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-800"><i class="fas fa-user-check me-2"></i>Approve Appointment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_appointment.php" method="POST">
                <input type="hidden" name="status" value="Approved">
                <input type="hidden" name="id" id="approveApptId">
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <span class="text-muted small">Approve Appointment for</span>
                        <h5 class="fw-800 text-light-emphasis m-0" id="approveCustName">—</h5>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-700">Assign Welder for Inspection</label>
                        <select name="welder_id" class="form-select fw-700" required>
                            <option value="">-- Choose Welder --</option>
                            <?php foreach ($welders as $welder): ?>
                                <option value="<?= $welder['id'] ?>"><?= htmlspecialchars($welder['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small text-muted">The assigned welder will visit the customer and finalize fabrication project dimensions &amp; materials.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-success w-100 fw-800 py-2">Approve &amp; Dispatch Welder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WALK-IN APPOINTMENT MODAL -->
<div class="modal fade" id="walkinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800"><i class="fas fa-user-plus me-2 text-amber"></i>Add Walk-in Appointment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="save_walkin.php" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-700">Customer Full Name</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="e.g. Angel Cruz" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-700">Appointment Date</label>
                            <input type="date" name="appointment_date" class="form-control" value="<?= $filterDate ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-700">Time Window</label>
                            <select name="appointment_time" class="form-select" required>
                                <option value="">Select Time</option>
                                <?php foreach (['09:00 AM','10:00 AM','11:00 AM','01:00 PM','02:00 PM','03:00 PM'] as $t): ?>
                                    <option><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-700">Contact Number</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="e.g. 09171234567" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Address Location <span class="text-danger small">(Cavite or Laguna only)</span></label>
                            <input type="text" name="address" class="form-control" placeholder="Full residential location..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Landmark</label>
                            <input type="text" name="landmark" class="form-control" placeholder="e.g. Near Brgy Hall">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-800 shadow-sm"><i class="fas fa-save me-1"></i>Create Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const approvalModal = new bootstrap.Modal(document.getElementById('approvalModal'));

function openApprovalModal(appt) {
    document.getElementById('approveApptId').value = appt.id;
    document.getElementById('approveCustName').textContent = appt.customer_name;
    approvalModal.show();
}
</script>
</body></html>