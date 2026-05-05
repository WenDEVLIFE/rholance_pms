<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

/* Ensure staff only */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../auth/login.php');
    exit;
}

/* ===============================
   DATE HANDLING (FIXED)
   =============================== */
$selected_date = $_GET['date'] ?? date('Y-m-d');
$display_date  = date('F j, Y', strtotime($selected_date));

$is_today   = ($selected_date == date('Y-m-d'));
$is_editable = $is_today;

/* ===============================
   BRANCH
   =============================== */
$selected_branch = $_GET['branch_id'] ?? $_SESSION['branch_id'];

/* ===============================
   FETCH DATA (FIXED)
   =============================== */
$attendance_stmt = $conn->prepare("
    SELECT 
        u.id,
        u.name,
        u.role,
        a.status
    FROM users u
    LEFT JOIN attendance a 
        ON a.user_id = u.id AND a.date = ?
    WHERE u.role = 'staff'
      AND u.branch_id = ?
    ORDER BY u.name ASC
");

$attendance_stmt->bind_param("si", $selected_date, $selected_branch);
$attendance_stmt->execute();
$attendance = $attendance_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Attendance</title>
    <link rel="stylesheet" href="../assets/css/staff-dashboard.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="layout">
<?php include '../includes/sidebar.php'; ?>

<main class="dashboard">

<div class="card">

    <!-- HEADER -->
    <div class="attendance-header">
        <h1>Daily Attendance</h1>

        <div class="attendance-date">
            <i class="fa-solid fa-calendar-day"></i>
            <span><?= $display_date ?></span>

            <?php if ($is_today): ?>
                <span class="today-badge">Today</span>
            <?php endif; ?>
        </div>
    </div>

    <p class="page-sub">
        Mark and view staff attendance.
    </p>

    <!-- FILTERS -->
    <form method="GET" class="attendance-filters">

        <select name="branch_id" onchange="this.form.submit()">
            <option value="1" <?= $selected_branch == 1 ? 'selected' : '' ?>>Laguna</option>
            <option value="2" <?= $selected_branch == 2 ? 'selected' : '' ?>>Bautista</option>
        </select>

        <input type="date" 
               name="date" 
               value="<?= $selected_date ?>"
               onchange="this.form.submit()">
    </form>

    <!-- TABLE -->
    <table class="table attendance-table">
        <thead>
            <tr>
                <th>Name</th>
                <th style="text-align:center;">Attendance</th>
            </tr>
        </thead>

        <tbody>
        <?php if ($attendance->num_rows > 0): ?>
            <?php while ($row = $attendance->fetch_assoc()): ?>
                <tr class="attendance-row">

                    <td class="staff-name">
                        <?= htmlspecialchars($row['name']) ?>
                    </td>

                    <td class="attendance-actions">

                        <button 
                            class="btn-present <?= $row['status'] === 'Present' ? 'active' : '' ?> <?= !$is_editable ? 'disabled-btn' : '' ?>"
                            <?= !$is_editable ? 'disabled' : '' ?>
                            onclick="updateAttendance(<?= $row['id'] ?>, 'Present', this)">
                            PRESENT
                        </button>

                        <button 
                            class="btn-absent <?= $row['status'] === 'Absent' ? 'active' : '' ?> <?= !$is_editable ? 'disabled-btn' : '' ?>"
                            <?= !$is_editable ? 'disabled' : '' ?>
                            onclick="updateAttendance(<?= $row['id'] ?>, 'Absent', this)">
                            ABSENT
                        </button>

                    </td>

                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="2" class="empty-state">
                    No staff found.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

</main>
</div>

<!-- ===============================
     AJAX SCRIPT (FIXED)
=============================== -->
<script>
function updateAttendance(userId, status, button) {

    const selectedDate = document.querySelector('input[name="date"]').value;

    fetch('update_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `user_id=${userId}&status=${status}&date=${selectedDate}`
    })
    .then(response => response.text())
    .then(data => {

        if (data !== "success") {
            console.log("Attendance update response:", data);
            return;
        }

        const parent = button.parentElement;

        parent.querySelectorAll('button').forEach(btn => {
            btn.classList.remove('active');
        });

        button.classList.add('active');
    });
}
</script>

</body>
</html>