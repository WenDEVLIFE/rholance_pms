<?php
session_start();
include '../config/database.php';
include '../includes/auth_check.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'customer') { header("Location: ../index.php"); exit; }

$user_id          = $_SESSION['user_id'];
$userName         = $_SESSION['name'] ?? 'Customer';
$showSuccess      = isset($_GET['success']);
$prefilledService = $_GET['service'] ?? '';

/* ── CALENDAR: build availability for displayed month ── */
$calYear  = (int)($_GET['year']  ?? date('Y'));
$calMonth = (int)($_GET['month'] ?? date('n'));
if ($calMonth < 1)  { $calMonth = 12; $calYear--; }
if ($calMonth > 12) { $calMonth = 1;  $calYear++; }

$firstDay     = mktime(0,0,0,$calMonth,1,$calYear);
$daysInMonth  = (int)date('t', $firstDay);
$startWeekday = (int)date('w', $firstDay); // 0=Sun

// Fetch customer's branch
$custInfo = $conn->query("SELECT branch_id, address FROM users WHERE id = $user_id")->fetch_assoc();
$customer_branch = (int)($custInfo['branch_id'] ?? 1);
$customer_address = $custInfo['address'] ?? '';

// Cavite=12, Laguna=24 max slots per day
$maxSlots = ($customer_branch == 2) ? 24 : 12;

$timeOptions = ['08:00 AM','09:00 AM','10:00 AM','11:00 AM','01:00 PM','02:00 PM','03:00 PM','04:00 PM'];

// Availability map for entire month
$availMap = [];
for ($d = 1; $d <= $daysInMonth; $d++) {
    $dt  = sprintf('%04d-%02d-%02d', $calYear, $calMonth, $d);
    
    // Count how many appointments this branch already has for this day
    $bkdCount = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE appointment_date='$dt' AND branch_id=$customer_branch AND status IN('Pending','Approved','Completed')")->fetch_assoc()['c'];
    
    $availCount = max(0, $maxSlots - $bkdCount);
    
    // If not full, we just pass the standard time options. (We aren't strictly locking specific hours, just the daily limit).
    $availMap[$dt] = [
        'slots' => $availCount > 0 ? $timeOptions : [], 
        'count' => $availCount, 
        'hasSlots' => true
    ];
}

/* ── Fetch customer's own appointments (with welder + order info) ── */
$myAppts = $conn->prepare("
    SELECT a.*,
           b.name branch_name,
           w.name welder_name,
           w.phone welder_phone,
           co.id order_id,
           co.status order_status,
           co.quote_status,
           co.payment_status,
           co.quoted_price,
           co.quoted_deadline,
           co.quoted_breakdown,
           co.assigned_welder_id,
           co.welder_visit_date,
           co.welder_visit_time
    FROM appointments a
    LEFT JOIN branches b ON b.id = a.branch_id
    LEFT JOIN users w ON w.id = a.welder_id
    LEFT JOIN custom_orders co ON co.customer_id = ? AND co.status NOT IN ('Completed','Cancelled')
                               AND co.assigned_welder_id IS NOT NULL
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC
    LIMIT 20
");
$myAppts->bind_param("ii", $user_id, $user_id);
$myAppts->execute();
$apptRows = $myAppts->get_result()->fetch_all(MYSQLI_ASSOC);

$prevMonth = $calMonth - 1 ?: 12;
$prevYear  = $calMonth - 1 ? $calYear : $calYear - 1;
$nextMonth = $calMonth % 12 + 1;
$nextYear  = $calMonth == 12 ? $calYear + 1 : $calYear;
$today     = date('Y-m-d');
$monthName = date('F Y', $firstDay);
?>

<div class="rh-main">

    <?php if ($showSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fas fa-check-circle"></i>
        <strong>Booked!</strong> Your appointment has been submitted. Our team will confirm it shortly.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>window.history.replaceState(null,'',window.location.pathname);</script>
    <?php endif; ?>

    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h1><i class="fas fa-calendar-check me-2 text-amber"></i>Book Appointment</h1>
            <p>Select an available date — <strong>Cavite &amp; Laguna branches only</strong></p>
        </div>
        <button class="btn btn-primary fw-800" data-bs-toggle="modal" data-bs-target="#bookModal">
            <i class="fas fa-calendar-plus me-2"></i>Book Consultation
        </button>
    </div>

    <?php if (!empty($prefilledService)): ?>
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
        <i class="fas fa-info-circle fs-5"></i>
        <div>Booking for: <strong><?= htmlspecialchars($prefilledService) ?></strong> — pre-filled below!</div>
    </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">

        <!-- ═══ CALENDAR ═══ -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?><?= $prefilledService ? '&service='.urlencode($prefilledService) : '' ?>"
                       class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px;padding:0;line-height:30px;text-align:center;">
                        <i class="fas fa-chevron-left" style="font-size:.7rem;"></i>
                    </a>
                    <span class="fw-800 text-light-emphasis"><?= $monthName ?></span>
                    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?><?= $prefilledService ? '&service='.urlencode($prefilledService) : '' ?>"
                       class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px;padding:0;line-height:30px;text-align:center;">
                        <i class="fas fa-chevron-right" style="font-size:.7rem;"></i>
                    </a>
                </div>
                <div class="card-body p-3">
                    <!-- Day Labels -->
                    <div class="rh-cal-grid mb-1">
                        <?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $dn): ?>
                            <div class="rh-cal-dayname"><?= $dn ?></div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Calendar Grid -->
                    <div class="rh-cal-grid">
                        <?php
                        // Empty cells before month starts
                        for ($e = 0; $e < $startWeekday; $e++):
                        ?><div class="rh-cal-cell empty"></div><?php endfor; ?>

                        <?php for ($d = 1; $d <= $daysInMonth; $d++):
                            $dt   = sprintf('%04d-%02d-%02d', $calYear, $calMonth, $d);
                            $info = $availMap[$dt];
                            $isPast  = $dt < $today;
                            $isToday = $dt === $today;
                            $cellCls = 'rh-cal-cell';
                            if ($isPast)          $cellCls .= ' past';
                            elseif (!$info['hasSlots']) $cellCls .= ' no-slots';
                            elseif ($info['count'] === 0) $cellCls .= ' full';
                            else                  $cellCls .= ' available day-pick';
                            if ($isToday)         $cellCls .= ' today';
                        ?>
                        <div class="<?= $cellCls ?>"
                             <?php if (!$isPast && $info['count'] > 0): ?>
                             data-date="<?= $dt ?>"
                             data-slots='<?= json_encode($info['slots']) ?>'
                             title="<?= $info['count'] ?> slot(s) open"
                             <?php endif; ?>>
                            <span class="rh-cal-num"><?= $d ?></span>
                            <?php if (!$isPast && $info['hasSlots']): ?>
                                <?php if ($info['count'] > 0): ?>
                                    <span class="rh-cal-dot dot-green"></span>
                                <?php else: ?>
                                    <span class="rh-cal-dot dot-red"></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Legend -->
                    <div class="d-flex gap-3 mt-3 px-1" style="font-size:.72rem;">
                        <span><span class="rh-cal-dot dot-green d-inline-block"></span> Available</span>
                        <span><span class="rh-cal-dot dot-red d-inline-block"></span> Full</span>
                        <span class="text-muted">Gray = No slots set</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ MY BOOKED APPOINTMENTS ═══ -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <span class="fw-800 text-light-emphasis">
                        <i class="fas fa-list-check me-2 text-amber"></i>My Booked Consultations
                    </span>
                    <span class="badge bg-secondary rounded-pill"><?= count($apptRows) ?> total</span>
                </div>
                <div class="table-responsive border-top">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Date & Time</th>
                                <th>Project</th>
                                <th>Welder</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($apptRows)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-calendar-xmark fs-2 mb-2 d-block opacity-25 text-amber"></i>
                                    No consultations scheduled yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($apptRows as $row):
                                $bc   = 'badge-'.strtolower($row['status']);
                                $isApproved = $row['status'] === 'Approved';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-700"><?= date('M d, Y', strtotime($row['appointment_date'])) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($row['appointment_time'] ?? '—') ?></div>
                                </td>
                                <td class="fw-700 text-amber small"><?= htmlspecialchars($row['requested_project'] ?? 'Consultation') ?></td>
                                <td>
                                    <?php if ($isApproved && !empty($row['welder_name'])): ?>
                                        <div class="fw-700 small"><?= htmlspecialchars($row['welder_name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($row['welder_phone'] ?? '') ?></div>
                                    <?php elseif ($isApproved): ?>
                                        <span class="small text-muted">Pending assignment</span>
                                    <?php else: ?>
                                        <span class="small text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $bc ?>"><?= $row['status'] ?></span></td>
                                <td class="text-end pe-3">
                                    <?php if ($isApproved): ?>
                                        <button class="btn btn-sm btn-outline-success fw-700"
                                                onclick="viewApptDetail(<?= htmlspecialchars(json_encode($row)) ?>)">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </button>
                                    <?php elseif ($row['status'] === 'Pending'): ?>
                                        <a href="cancel_appointment.php?id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-danger fw-700"
                                           onclick="return confirm('Cancel this appointment?')">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /row -->
</div><!-- /.rh-main -->

<!-- ═══════ BOOK APPOINTMENT MODAL ═══════ -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800"><i class="fas fa-calendar-plus me-2 text-amber"></i>Book Consultation Slot</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bookForm" action="request_appointment.php" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-700">Consultation Date</label>
                            <input type="date" id="bookDate" name="appointment_date" class="form-control"
                                   min="<?= date('Y-m-d') ?>" required readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-700">Time Slot</label>
                            <select name="appointment_time" id="bookTime" class="form-select" required>
                                <option value="">— Pick a date first —</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Project Requested</label>
                            <input type="text" name="requested_project" class="form-control fw-700 text-amber"
                                   value="<?= htmlspecialchars($prefilledService) ?>"
                                   placeholder="e.g. Stainless Gate, Steel Railing" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Site Address <span class="text-muted fw-400">(Cavite or Laguna only)</span></label>
                            <input type="text" name="address" class="form-control" placeholder="Complete address for site inspection" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Landmark</label>
                            <input type="text" name="landmark" class="form-control" placeholder="Near Brgy. Hall / House Color">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Contact Person at Site</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="Who will be present" required>
                        </div>
                        <!-- Branch is implicitly assigned based on customer's account branch -->
                        <input type="hidden" name="branch_id" value="<?= $customer_branch ?>">
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-800">
                        <i class="fas fa-paper-plane me-2"></i>Submit Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════ APPOINTMENT DETAIL MODAL ═══════ -->
<div class="modal fade" id="apptDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- Header -->
            <div style="background:linear-gradient(135deg,#065F46,#059669); padding:20px 24px;">
                <div class="text-white fw-800 mb-1" style="font-size:.85rem; letter-spacing:.5px;">
                    <i class="fas fa-calendar-check me-2"></i>APPOINTMENT CONFIRMATION
                </div>
                <div class="text-white opacity-75 small" id="detailGreeting">
                    Hi there! Please check the info of your appointment.
                </div>
            </div>

            <div class="modal-body p-4">

                <!-- Client Info -->
                <div class="mb-3">
                    <div class="fw-800 small text-muted mb-2" style="letter-spacing:.6px;">CLIENT DETAILS</div>
                    <div class="row g-2" id="detailClientRows"><!-- filled by JS --></div>
                </div>

                <!-- Welder Info (shown when approved & welder assigned) -->
                <div id="detailWelderSection" class="mb-3" style="display:none;">
                    <hr class="my-2">
                    <div class="fw-800 small text-muted mb-2" style="letter-spacing:.6px;">ASSIGNED WELDER</div>
                    <div class="row g-2" id="detailWelderRows"><!-- filled by JS --></div>
                </div>

                <!-- Payment Prompt (shown when quote_status = Approved & payment_status != Paid) -->
                <div id="detailPaymentSection" style="display:none;">
                    <hr class="my-2">
                    <div class="p-3 rounded-3" style="background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.2);">
                        <div class="fw-800 small text-success mb-1">
                            <i class="fas fa-money-bill-wave me-1"></i>INITIAL PAYMENT REQUIRED
                        </div>
                        <div class="small text-muted mb-2">
                            Quoted price: <strong class="text-success" id="detailQuotedPrice">₱0.00</strong>
                        </div>
                        <p class="small text-muted mb-2">
                            Please send payment via <strong>GCash or Cash</strong> and upload your receipt as proof to begin fabrication.
                        </p>
                        <a href="#" id="detailPayBtn" class="btn btn-success fw-800 w-100 btn-sm">
                            <i class="fas fa-upload me-1"></i>Upload Payment Receipt
                        </a>
                    </div>
                </div>

                <p class="small text-muted fst-italic mt-3 mb-0 text-center">
                    <i class="fas fa-circle-info me-1"></i>NOTE: Make sure your information is correct.
                </p>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                <button type="button" class="btn fw-800 flex-fill" style="background:#EF4444;color:#fff;"
                        data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i>BACK
                </button>
                <button type="button" class="btn fw-800 flex-fill" style="background:#10B981;color:#fff;"
                        data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i>CONFIRM
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── CALENDAR GRID ── */
.rh-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}
.rh-cal-dayname {
    text-align: center;
    font-size: .68rem;
    font-weight: 800;
    color: #94A3B8;
    padding: 4px 0;
    text-transform: uppercase;
}
.rh-cal-cell {
    position: relative;
    text-align: center;
    border-radius: 8px;
    padding: 6px 2px 10px;
    font-size: .82rem;
    font-weight: 600;
    cursor: default;
    min-height: 44px;
    background: #F8FAFC;
    border: 1px solid transparent;
    transition: all .15s;
}
.rh-cal-cell.empty       { background: transparent; border-color: transparent; }
.rh-cal-cell.past        { opacity: .35; }
.rh-cal-cell.no-slots    { background: #F1F5F9; color: #94A3B8; }
.rh-cal-cell.full        { background: #FEF2F2; border-color: #FECACA; color: #EF4444; }
.rh-cal-cell.available   { background: #F0FDF4; border-color: #BBF7D0; color: #065F46; cursor: pointer; }
.rh-cal-cell.available:hover { background: #DCFCE7; border-color: #4ADE80; transform: scale(1.05); box-shadow: 0 3px 10px rgba(16,185,129,.15); }
.rh-cal-cell.today .rh-cal-num {
    background: #0F172A;
    color: #fff;
    border-radius: 50%;
    width: 22px; height: 22px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .75rem;
}
.rh-cal-num { display: block; }
.rh-cal-dot {
    position: absolute;
    bottom: 4px; left: 50%; transform: translateX(-50%);
    width: 5px; height: 5px;
    border-radius: 50%;
}
.dot-green { background: #22C55E; }
.dot-red   { background: #EF4444; }

/* Dark mode cal */
body.dark .rh-cal-cell          { background: #1E293B; }
body.dark .rh-cal-cell.available { background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.2); color: #6EE7B7; }
body.dark .rh-cal-cell.full      { background: rgba(239,68,68,.1); border-color: rgba(239,68,68,.2); color: #FCA5A5; }
body.dark .rh-cal-cell.no-slots  { background: #0F172A; color: #475569; }
</style>

<script>
const bookModal = new bootstrap.Modal(document.getElementById('bookModal'));

/* Calendar day → pre-fill modal */
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

/* Address validation */
document.getElementById('bookForm').addEventListener('submit', function(e) {
    const addr = document.querySelector('input[name="address"]').value.toLowerCase();
    if (!addr.includes('cavite') && !addr.includes('laguna')) {
        e.preventDefault();
        alert('We only accept appointments for Cavite and Laguna locations.');
    }
});

/* Appointment Detail Modal */
const detailModal = new bootstrap.Modal(document.getElementById('apptDetailModal'));

function viewApptDetail(row) {
    /* Greeting */
    const name = row.customer_name || '<?= htmlspecialchars(explode(' ',$_SESSION['name'])[0]) ?>';
    document.getElementById('detailGreeting').textContent =
        'Hi ' + name + '! We received your appointment. Please check the info below.';

    /* Client detail rows */
    const clientData = [
        ['CLIENT NAME', row.customer_name  || '<?= htmlspecialchars($_SESSION['name']) ?>'],
        ['DATE',        formatDate(row.appointment_date)],
        ['TIME',        row.appointment_time || 'TBD'],
        ['PROJECT',     row.requested_project || 'Custom Consultation'],
        ['ADDRESS',     row.address || '—'],
    ];
    document.getElementById('detailClientRows').innerHTML = clientData.map(([l,v]) => detailRow(l,v)).join('');

    /* Welder section */
    const welderSec = document.getElementById('detailWelderSection');
    if (row.welder_name) {
        welderSec.style.display = 'block';
        const welderData = [
            ['WELDER ASSIGNED', row.welder_name],
            ['CONTACT',         row.welder_phone || '—'],
            ['DATE ARRIVED',    row.welder_visit_date ? formatDate(row.welder_visit_date) : (row.appointment_date ? formatDate(row.appointment_date) : '—')],
            ['TIME',            row.welder_visit_time || row.appointment_time || '—'],
        ];
        document.getElementById('detailWelderRows').innerHTML = welderData.map(([l,v]) => detailRow(l,v)).join('');
    } else {
        welderSec.style.display = 'none';
    }

    /* Payment section */
    const paySec = document.getElementById('detailPaymentSection');
    if (row.quote_status === 'Approved' && row.payment_status !== 'Paid') {
        paySec.style.display = 'block';
        document.getElementById('detailQuotedPrice').textContent =
            '₱' + parseFloat(row.quoted_price || 0).toLocaleString('en-PH', {minimumFractionDigits:2});
        document.getElementById('detailPayBtn').href =
            '../orders/view_order.php?id=' + (row.order_id || '');
    } else {
        paySec.style.display = 'none';
    }

    detailModal.show();
}

function detailRow(label, value) {
    return `<div class="col-12">
        <div class="d-flex gap-2 py-1 border-bottom" style="border-color:rgba(0,0,0,.06)!important;">
            <div class="fw-800" style="font-size:.65rem;color:#94A3B8;letter-spacing:.7px;width:130px;flex-shrink:0;padding-top:2px;">${label}</div>
            <div class="fw-600" style="font-size:.85rem;">${value}</div>
        </div>
    </div>`;
}

function formatDate(str) {
    if (!str) return '—';
    const d = new Date(str + 'T00:00:00');
    return d.toLocaleDateString('en-PH', {month:'long', day:'numeric', year:'numeric'}).toUpperCase();
}
</script>

</body></html>