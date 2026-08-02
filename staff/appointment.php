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
$welderRes = $conn->query("SELECT id, name, phone FROM users WHERE role='welder' AND branch_id=$branch AND status='active' ORDER BY name ASC");
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
                        <td>
                            <?= statusBadge($row['status']) ?>
                            <?php if ($row['status'] === 'Pending'): ?>
                                <div class="badge bg-danger text-white d-block mt-1" style="font-size:0.6rem;"><i class="fas fa-bell me-1"></i>New Booking - Needs Welder Assignment</div>
                            <?php endif; ?>
                        </td>

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

<!-- MANAGE APPOINTMENT MODAL FLOW -->
<div class="modal fade" id="manageApptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- HEADER -->
            <div class="bg-dark text-white p-3 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-50 end-0 translate-middle-y me-3" data-bs-dismiss="modal"></button>
                <div class="fw-800" style="letter-spacing:1px; font-size:.85rem;">
                    APPOINTMENT: <span id="mDateBadge" class="text-amber">JULY 13, 2026</span>
                </div>
            </div>

            <!-- VIEW 1: SUMMARY DETAILS -->
            <div id="viewSummary">
                <div class="modal-body p-4">
                    <div class="d-flex flex-column gap-3 mb-2" id="summaryDetailsList">
                        <!-- Filled by JS -->
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn flex-fill fw-800" style="background:#10B981;color:#fff;" data-bs-dismiss="modal">EXIT</button>
                    <button type="button" class="btn flex-fill fw-800" style="background:#F43F5E;color:#fff;" onclick="toggleApptEditor(true)">MANAGE</button>
                </div>
            </div>

            <!-- VIEW 2: ASSIGNMENT EDITOR -->
            <div id="viewEditor" style="display:none;">
                <form action="process_appointment.php" method="POST">
                    <input type="hidden" name="id" id="editApptId">
                    <input type="hidden" name="status" id="editApptStatus" value="Approved">
                    
                    <div class="modal-body p-4">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-800 text-success"><i class="fas fa-check-circle me-1"></i>WELDER ASSIGNED</label>
                            <input type="hidden" name="welder_id" id="editWelderId" required>
                            <button type="button" class="btn btn-outline-success w-100 fw-700 text-start d-flex justify-content-between align-items-center" id="assignedWelderBtn" onclick="openWelderSelector()">
                                <span>[SELECT WELDER]</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-800 text-success"><i class="fas fa-check-circle me-1"></i>CONTACT NUMBER (WELDER)</label>
                            <div class="input-group">
                                <input type="text" id="editWelderPhone" name="welder_phone" class="form-control border-success shadow-sm" placeholder="Auto-filled / Editable">
                                <span class="input-group-text bg-success-subtle border-success text-success fw-700" style="font-size:0.75rem;">EDIT</span>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-800 text-success"><i class="fas fa-check-circle me-1"></i>DATE ARRIVED</label>
                                <input type="date" name="visit_date" id="editVisitDate" class="form-control border-success shadow-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-800 text-success"><i class="fas fa-check-circle me-1"></i>TIME</label>
                                <input type="time" name="visit_time" id="editVisitTime" class="form-control border-success shadow-sm" required>
                            </div>
                        </div>

                        <div id="initialPaymentTrigger" style="display:none;" class="mt-4 p-3 rounded bg-light border">
                            <div class="fw-800 text-success mb-2"><i class="fas fa-money-bill-wave me-1"></i>INITIAL PAYMENT</div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="markOngoingCheck" onchange="togglePaymentBtn(this)">
                                <label class="form-check-label small fw-700" for="markOngoingCheck">Welder confirmed layout and settled? Mark Ongoing.</label>
                            </div>
                            <a href="#" id="staffPaymentBtn" class="btn btn-success fw-800 w-100 disabled"><i class="fas fa-receipt me-1"></i>FOR INITIAL PAYMENT</a>
                        </div>
                        
                    </div>
                    
                    <div class="modal-footer border-0 px-4 pb-4 gap-2">
                        <button type="button" class="btn flex-fill fw-800" style="background:#10B981;color:#fff;" onclick="toggleApptEditor(false)">BACK</button>
                        <button type="submit" class="btn flex-fill fw-800" style="background:#10B981;color:#fff;">SAVED</button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>

<!-- WELDER SELECTION POPUP MODAL -->
<div class="modal fade" id="welderSelectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="bg-dark text-white p-3 text-center">
                <div class="fw-800" style="letter-spacing:1px; font-size:.85rem;">
                    SELECT WELDER
                </div>
            </div>
            <div class="modal-body p-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-light border"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="welderSearchInput" class="form-control border-start-0" placeholder="Search welder..." onkeyup="filterWeldersList()">
                </div>
                
                <div class="list-group list-group-flush overflow-auto" style="max-height: 250px;" id="welderListContainer">
                    <?php foreach ($welders as $w): ?>
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center fw-600 py-2 border-0 rounded mb-1 welder-list-item" 
                                data-id="<?= $w['id'] ?>" 
                                data-name="<?= htmlspecialchars($w['name']) ?>" 
                                data-phone="<?= htmlspecialchars($w['phone'] ?? '') ?>"
                                onclick="selectWelder(this)">
                            <span><?= htmlspecialchars($w['name']) ?></span>
                            <span class="badge bg-success-subtle text-success fs-8 fw-700 select-status" style="display:none;">ASSIGNED</span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-fill fw-700" data-bs-dismiss="modal">CANCEL</button>
                <button type="button" class="btn btn-primary flex-fill fw-800" onclick="confirmWelderSelection()">SAVE</button>
            </div>
        </div>
    </div>
</div>

<script>
const manageApptModal = new bootstrap.Modal(document.getElementById('manageApptModal'));
const welderSelectModal = new bootstrap.Modal(document.getElementById('welderSelectModal'));
let currentAppt = null;
let selectedWelderId = '';
let selectedWelderName = '';
let selectedWelderPhone = '';

// The welders data mapped by ID for auto-filling phone
const weldersData = {
    <?php foreach ($welders as $w): ?>
    "<?= $w['id'] ?>": "<?= htmlspecialchars($w['phone'] ?? '') ?>",
    <?php endforeach; ?>
};

function openWelderSelector() {
    selectedWelderId = document.getElementById('editWelderId').value;
    
    // Highlight currently assigned
    document.querySelectorAll('.welder-list-item').forEach(item => {
        const itemId = item.getAttribute('data-id');
        const badge = item.querySelector('.select-status');
        if (itemId === selectedWelderId) {
            item.classList.add('bg-success-subtle');
            badge.style.display = 'block';
        } else {
            item.classList.remove('bg-success-subtle');
            badge.style.display = 'none';
        }
    });

    document.getElementById('welderSearchInput').value = '';
    filterWeldersList();
    
    manageApptModal.hide();
    welderSelectModal.show();
}

function filterWeldersList() {
    const query = document.getElementById('welderSearchInput').value.toLowerCase();
    document.querySelectorAll('.welder-list-item').forEach(item => {
        const name = item.getAttribute('data-name').toLowerCase();
        if (name.includes(query)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function selectWelder(btn) {
    selectedWelderId = btn.getAttribute('data-id');
    selectedWelderName = btn.getAttribute('data-name');
    selectedWelderPhone = btn.getAttribute('data-phone');
    
    document.querySelectorAll('.welder-list-item').forEach(item => {
        item.classList.remove('bg-success-subtle');
        item.querySelector('.select-status').style.display = 'none';
    });
    
    btn.classList.add('bg-success-subtle');
    btn.querySelector('.select-status').style.display = 'block';
}

function confirmWelderSelection() {
    if (selectedWelderId) {
        document.getElementById('editWelderId').value = selectedWelderId;
        document.querySelector('#assignedWelderBtn span').textContent = selectedWelderName;
        document.getElementById('editWelderPhone').value = selectedWelderPhone;
    }
    welderSelectModal.hide();
    manageApptModal.show();
}

// Close child modals gracefully back to parent
document.getElementById('welderSelectModal').addEventListener('hidden.bs.modal', function () {
    if (!document.getElementById('manageApptModal').classList.contains('show') && selectedWelderId === '') {
        manageApptModal.show();
    }
});

function openApprovalModal(appt) {
    currentAppt = appt;
    document.getElementById('editApptId').value = appt.id;
    
    // Set Header Date
    const d = new Date(appt.appointment_date);
    document.getElementById('mDateBadge').textContent = d.toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'}).toUpperCase();

    // Summary Details
    const list = document.getElementById('summaryDetailsList');
    list.innerHTML = `
        ${summaryRow('CLIENT NAME', appt.customer_name)}
        ${summaryRow('EMAIL', appt.cust_email || '—')}
        ${summaryRow('CONTACT NUMBER', appt.cust_phone || '—')}
        ${summaryRow('BOOKED ABOUT', appt.requested_project || 'Fabrication')}
        ${summaryRow('ADDRESS', appt.address || '—')}
    `;
    
    // Pre-fill Editor
    document.getElementById('editWelderId').value = appt.welder_id || '';
    if (appt.welder_id) {
        document.getElementById('editWelderPhone').value = weldersData[appt.welder_id] || '';
        const welderItem = document.querySelector(`.welder-list-item[data-id="${appt.welder_id}"]`);
        if (welderItem) {
            document.querySelector('#assignedWelderBtn span').textContent = welderItem.getAttribute('data-name');
        } else {
            document.querySelector('#assignedWelderBtn span').textContent = appt.welder_name || '[SELECT WELDER]';
        }
    } else {
        document.getElementById('editWelderPhone').value = '';
        document.querySelector('#assignedWelderBtn span').textContent = '[SELECT WELDER]';
    }
    
    document.getElementById('editVisitDate').value = appt.appointment_date;
    
    // Convert '01:00 PM' to '13:00' for input type time
    if (appt.appointment_time) {
        let [time, modifier] = appt.appointment_time.split(' ');
        let [hours, minutes] = time.split(':');
        if (hours === '12') hours = '00';
        if (modifier === 'PM') hours = parseInt(hours, 10) + 12;
        document.getElementById('editVisitTime').value = `${hours}:${minutes}`;
    }

    // Reset view
    toggleApptEditor(false);
    
    // Payment Trigger Section
    const payTrigger = document.getElementById('initialPaymentTrigger');
    if (appt.status === 'Approved') {
        payTrigger.style.display = 'block';
        document.getElementById('editApptStatus').value = 'Completed'; // Marking it complete directs to project
        document.getElementById('staffPaymentBtn').href = `process_appointment.php?id=${appt.id}&status=Completed`;
    } else {
        payTrigger.style.display = 'none';
        document.getElementById('editApptStatus').value = 'Approved';
    }

    manageApptModal.show();
}

function summaryRow(label, val) {
    return `
    <div class="row g-0">
        <div class="col-4 fw-800 text-muted" style="font-size:.7rem;">${label}:</div>
        <div class="col-8 fw-700" style="font-size:.85rem;">${val}</div>
    </div>`;
}

function toggleApptEditor(showEditor) {
    document.getElementById('viewSummary').style.display = showEditor ? 'none' : 'block';
    document.getElementById('viewEditor').style.display = showEditor ? 'block' : 'none';
}

function togglePaymentBtn(cb) {
    const btn = document.getElementById('staffPaymentBtn');
    if (cb.checked) {
        btn.classList.remove('disabled');
        btn.classList.add('shadow');
    } else {
        btn.classList.add('disabled');
        btn.classList.remove('shadow');
    }
}
</script>

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



<!-- SLOT ADDED SUCCESS MODAL -->
<?php if (isset($_GET['success']) && $_GET['success'] === 'slot_added'): ?>
<div class="modal fade" id="slotSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-4">
            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
            <h5 class="fw-800 mb-1">Slot Added!</h5>
            <p class="text-muted small mb-3">The available time slot has been successfully posted for customers.</p>
            <button type="button" class="btn btn-success fw-700 w-100" data-bs-dismiss="modal">Okay</button>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var slotSuccessModal = new bootstrap.Modal(document.getElementById('slotSuccessModal'));
    slotSuccessModal.show();
    
    // Clean up URL
    window.history.replaceState(null, '', window.location.pathname);
});
</script>
<?php endif; ?>

</body></html>