<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

/* ── Date filter ── */
$filterDate = $_GET['date'] ?? date('Y-m-d');
$filterBranch = $_SESSION['branch_id'] ?? null;

/* ── Scope check: Cavite = branch 1, Laguna = branch 2 ── */
$scopeBranches = [1, 2];

/* ── Fetch appointments for selected date ── */
$stmt = $conn->prepare("
    SELECT a.*, u.email AS customer_email, b.name AS branch_name
    FROM appointments a
    LEFT JOIN users u   ON u.id = a.user_id
    LEFT JOIN branches b ON b.id = a.branch_id
    WHERE a.appointment_date = ?
    AND (a.branch_id = ? OR a.branch_id IS NULL)
    AND a.customer_name IS NOT NULL AND a.customer_name != ''
    ORDER BY FIELD(a.status,'Pending','Approved','Completed','Rejected'), a.appointment_time ASC
");
$stmt->bind_param("si", $filterDate, $filterBranch);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ── Date list for picker (next 14 days) ── */
$dates = [];
for ($i = -3; $i <= 14; $i++) {
    $dates[] = date('Y-m-d', strtotime("$i days"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointments – Staff</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/staff-dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ── PAGE LAYOUT ── */
.appt-page { padding: 28px 32px; }
.appt-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:14px; }
.appt-header h2 { margin:0; font-size:22px; color:#0F172A; }

/* ── DATE STRIP ── */
.date-strip { display:flex; gap:8px; overflow-x:auto; padding-bottom:6px; margin-bottom:28px; }
.date-chip { min-width:80px; text-align:center; padding:10px 14px; border-radius:14px; border:2px solid #E2E8F0; background:#fff; cursor:pointer; font-weight:600; font-size:13px; text-decoration:none; color:#64748B; transition:all .25s; flex-shrink:0; }
.date-chip:hover { border-color:#F59E0B; color:#F59E0B; }
.date-chip.active { background:#F59E0B; border-color:#F59E0B; color:#fff; box-shadow:0 4px 10px rgba(245,158,11,.3); }
.date-chip small { display:block; font-size:10px; font-weight:400; opacity:.8; }

/* ── CUSTOM DATE INPUT ── */
.date-input-row { display:flex; gap:12px; align-items:center; margin-bottom:24px; }
.date-input-row input[type=date] { padding:10px 14px; border-radius:10px; border:1px solid #E2E8F0; font-family:inherit; font-size:14px; }
.date-input-row button { background:#0F172A; color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer; }

/* ── WALK-IN CARD ── */
.walkin-card { background:#fff; border-radius:16px; border:1px solid #E2E8F0; padding:24px; margin-bottom:28px; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.walkin-card h3 { margin:0 0 18px; font-size:16px; color:#0F172A; }
.walkin-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:14px; }
.walkin-grid input, .walkin-grid select { padding:10px 14px; border-radius:10px; border:1px solid #E2E8F0; font-family:inherit; font-size:14px; }
.walkin-grid input:focus, .walkin-grid select:focus { border-color:#F59E0B; outline:none; }

/* ── TABLE ── */
.appt-table-wrapper { background:#fff; border-radius:16px; border:1px solid #E2E8F0; padding:24px; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.appt-table-wrapper h3 { margin:0 0 18px; font-size:16px; color:#0F172A; }
table.appt-table { width:100%; border-collapse:collapse; }
table.appt-table th { font-size:11px; text-transform:uppercase; color:#94A3B8; padding:10px 16px; text-align:left; border-bottom:2px solid #F1F5F9; letter-spacing:.5px; }
table.appt-table td { padding:14px 16px; font-size:14px; color:#1E293B; border-bottom:1px solid #F1F5F9; vertical-align:middle; }
table.appt-table tr:last-child td { border-bottom:none; }
table.appt-table tr:hover td { background:#F8FAFC; }

/* ── STATUS PILL ── */
.pill { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.pill.pending  { background:#FEF3C7; color:#92400E; }
.pill.approved { background:#D1FAE5; color:#065F46; }
.pill.rejected { background:#FEE2E2; color:#991B1B; }
.pill.completed{ background:#DBEAFE; color:#1E40AF; }

/* ── OUT OF SCOPE BADGE ── */
.scope-warn { display:inline-flex; align-items:center; gap:5px; background:#FFF7ED; color:#C2410C; font-size:11px; font-weight:700; padding:3px 10px; border-radius:8px; border:1px solid #FED7AA; }

/* ── ACTION BTNS ── */
.action-btns { display:flex; gap:8px; flex-wrap:wrap; }
.btn-app { padding:6px 14px; border-radius:8px; font-size:12px; font-weight:700; border:none; cursor:pointer; transition:all .2s; }
.btn-approve { background:#10B981; color:#fff; } .btn-approve:hover { background:#059669; }
.btn-reject  { background:#EF4444; color:#fff; } .btn-reject:hover  { background:#DC2626; }

/* ── CUSTOMER INFO POPOVER ── */
.info-cell { position:relative; }
.info-btn { background:none; border:none; cursor:pointer; color:#F59E0B; font-size:16px; padding:4px; }
.info-pop { display:none; position:absolute; z-index:99; top:100%; left:0; background:#fff; border:1px solid #E2E8F0; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,.12); padding:14px; min-width:240px; }
.info-pop p { margin:4px 0; font-size:13px; color:#1E293B; } 
.info-pop p span { font-weight:700; }
.info-cell:hover .info-pop { display:block; }

/* ── SLOT CARD ── */
.slot-card { background:#fff; border-radius:16px; border:1px solid #E2E8F0; padding:24px; margin-bottom:28px; box-shadow:0 2px 8px rgba(0,0,0,.05); }

/* ── EMPTY STATE ── */
.empty-state { text-align:center; padding:48px; color:#94A3B8; }
.empty-state i { font-size:40px; margin-bottom:12px; display:block; opacity:.4; }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="main appt-page">

    <div class="appt-header">
        <h2><i class="fa-solid fa-calendar-check" style="color:#F59E0B;margin-right:8px;"></i>Appointment Management</h2>
        <div style="display:flex;gap:10px;align-items:center;">
            <span style="font-size:13px;color:#64748B;">Scope: <strong style="color:#0F172A;">Cavite &amp; Laguna Only</strong></span>
        </div>
    </div>

    <!-- ── DATE PICKER ── -->
    <form method="GET" class="date-input-row">
        <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>">
        <button type="submit"><i class="fas fa-search"></i> Filter</button>
    </form>

    <!-- ── DATE STRIP ── -->
    <div class="date-strip">
        <?php foreach ($dates as $d): ?>
            <?php $active = ($d === $filterDate) ? 'active' : ''; ?>
            <a href="?date=<?= $d ?>" class="date-chip <?= $active ?>">
                <?= date('D', strtotime($d)) ?>
                <small><?= date('M d', strtotime($d)) ?></small>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ── WALK-IN FORM ── -->
    <div class="walkin-card">
        <h3><i class="fa-solid fa-user-plus" style="color:#F59E0B;margin-right:8px;"></i>Add Walk-in Appointment</h3>
        <form action="save_walkin.php" method="POST">
            <div class="walkin-grid">
                <input type="text" name="customer_name" placeholder="Customer Name" required>
                <input type="date" name="appointment_date" value="<?= $filterDate ?>" required>
                <select name="appointment_time" required>
                    <option value="">Select Time</option>
                    <option>09:00 AM</option><option>10:00 AM</option><option>11:00 AM</option>
                    <option>01:00 PM</option><option>02:00 PM</option><option>03:00 PM</option>
                </select>
                <input type="text" name="address" placeholder="Address (Cavite or Laguna only)">
                <input type="text" name="contact_person" placeholder="Contact Person">
            </div>
            <button type="submit" class="btn-app btn-approve" style="margin-top:16px;padding:10px 22px;font-size:14px;">
                <i class="fa fa-save"></i> Save Appointment
            </button>
        </form>
    </div>

    <!-- ── APPOINTMENT TABLE ── -->
    <div class="appt-table-wrapper">
        <h3>
            <i class="fa-solid fa-list" style="color:#F59E0B;margin-right:8px;"></i>
            Appointments for <?= date('F d, Y', strtotime($filterDate)) ?>
            <span style="font-size:13px;font-weight:400;color:#94A3B8;margin-left:10px;"><?= count($appointments) ?> record(s)</span>
        </h3>

        <?php if (empty($appointments)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-calendar-xmark"></i>
                No appointments found for this date.
            </div>
        <?php else: ?>
        <table class="appt-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Time</th>
                    <th>Address</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Info</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $row): ?>
                <?php
                    /* ── Check if address mentions Cavite or Laguna ── */
                    $addr = strtolower($row['address'] ?? '');
                    $inScope = (str_contains($addr,'cavite') || str_contains($addr,'laguna') || !empty($row['branch_id']));
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($row['customer_name']) ?></strong>
                        <?php if (!$inScope && !empty($row['address'])): ?>
                            <br><span class="scope-warn"><i class="fas fa-exclamation-triangle"></i> Out of Scope</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                    <td><?= htmlspecialchars($row['address'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['branch_name'] ?? '—') ?></td>
                    <td><span class="pill <?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>

                    <!-- ── CUSTOMER CONTACT INFO ── -->
                    <td class="info-cell">
                        <button class="info-btn" title="Customer Info"><i class="fas fa-info-circle"></i></button>
                        <div class="info-pop">
                            <p><span>Address:</span> <?= htmlspecialchars($row['address'] ?? 'N/A') ?></p>
                            <p><span>Landmark:</span> <?= htmlspecialchars($row['landmark'] ?? 'N/A') ?></p>
                            <p><span>Email:</span> <?= htmlspecialchars($row['customer_email'] ?? 'N/A') ?></p>
                            <p><span>Contact:</span> <?= htmlspecialchars($row['contact_person'] ?? 'N/A') ?></p>
                            <?php if (!$inScope && !empty($row['address'])): ?>
                                <p style="color:#C2410C;font-weight:700;margin-top:8px;"><i class="fas fa-exclamation-triangle"></i> Address may be out of scope. Only Cavite &amp; Laguna are accepted.</p>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- ── ACTIONS ── -->
                    <td>
                        <div class="action-btns">
                            <?php if ($row['status'] === 'Pending'): ?>
                                <a href="approve_appointment.php?id=<?= $row['id'] ?>&date=<?= $filterDate ?>" class="btn-app btn-approve"><i class="fas fa-check"></i> Approve</a>
                                <a href="reject_appointment.php?id=<?= $row['id'] ?>&date=<?= $filterDate ?>"  class="btn-app btn-reject"><i class="fas fa-times"></i> Reject</a>
                            <?php elseif ($row['status'] === 'Approved'): ?>
                                <a href="complete_appointment.php?id=<?= $row['id'] ?>&date=<?= $filterDate ?>" class="btn-app btn-approve"><i class="fas fa-flag-checkered"></i> Complete</a>
                            <?php elseif ($row['status'] === 'Completed'): ?>
                                <a href="../orders/create.php?appointment_id=<?= $row['id'] ?>" class="btn-app" style="background:#3B82F6;color:#fff;">Proceed to Order</a>
                            <?php else: ?>
                                <span style="color:#94A3B8;font-size:13px;">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- ── ADD SLOT CARD ── -->
    <div class="slot-card" style="margin-top:28px;">
        <h3><i class="fa-solid fa-clock" style="color:#F59E0B;margin-right:8px;"></i>Set Available Slot</h3>
        <form action="add_slot.php" method="POST">
            <div class="walkin-grid" style="max-width:500px;">
                <input type="date" name="appointment_date" value="<?= $filterDate ?>" required>
                <select name="appointment_time" required>
                    <option value="">Select Time</option>
                    <option>09:00 AM</option><option>10:00 AM</option><option>11:00 AM</option>
                    <option>01:00 PM</option><option>02:00 PM</option><option>03:00 PM</option>
                </select>
            </div>
            <button type="submit" class="btn-app btn-approve" style="margin-top:16px;padding:10px 22px;font-size:14px;">
                <i class="fa fa-plus"></i> Add Slot
            </button>
        </form>
    </div>

</div>
</body>
</html>