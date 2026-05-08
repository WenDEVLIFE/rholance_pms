<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit; }

/* Handle block/unblock/delete actions */
if (isset($_GET['action'], $_GET['id'])) {
    $uid = (int)$_GET['id'];
    match($_GET['action']) {
        'block'   => $conn->query("UPDATE users SET status = 'blocked'  WHERE id = $uid"),
        'unblock' => $conn->query("UPDATE users SET status = 'active'   WHERE id = $uid"),
        'archive' => $conn->query("UPDATE users SET status = 'archived' WHERE id = $uid"),
        default   => null
    };
    header("Location: user_management.php"); exit;
}

$roleFilter   = $_GET['role'] ?? 'all';
$branchFilter = $_GET['branch'] ?? 'all';
$search       = $conn->real_escape_string($_GET['q'] ?? '');

$where = "WHERE u.id != {$_SESSION['user_id']}";
if ($roleFilter !== 'all')   $where .= " AND u.role = '$roleFilter'";
if ($branchFilter !== 'all') $where .= " AND u.branch_id = " . (int)$branchFilter;
if ($search)                 $where .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%')";

$users = $conn->query("
    SELECT u.*, b.name AS branch_name,
        (SELECT COUNT(*) FROM custom_orders co WHERE co.customer_id = u.id) AS order_count
    FROM users u
    LEFT JOIN branches b ON b.id = u.branch_id
    $where
    ORDER BY FIELD(u.role,'admin','staff','welder','customer'), u.name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management – Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.um-page { padding:28px 32px; }
.um-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.um-header h2 { margin:0; font-size:22px; color:#0F172A; }

/* FILTER BAR */
.filter-bar { display:flex; gap:12px; align-items:center; margin-bottom:22px; flex-wrap:wrap; }
.filter-bar input { padding:10px 16px; border-radius:10px; border:1px solid #E2E8F0; font-size:14px; width:220px; }
.filter-bar select { padding:10px 14px; border-radius:10px; border:1px solid #E2E8F0; font-size:14px; }
.filter-bar button { background:#0F172A; color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:700; cursor:pointer; }

/* TABLE */
.um-table-wrap { background:#fff; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.04); }
table.um-table { width:100%; border-collapse:collapse; }
table.um-table th { font-size:11px; text-transform:uppercase; color:#94A3B8; padding:14px 18px; text-align:left; background:#F8FAFC; border-bottom:1px solid #E2E8F0; }
table.um-table td { padding:14px 18px; font-size:14px; border-bottom:1px solid #F1F5F9; vertical-align:middle; }
table.um-table tr:last-child td { border-bottom:none; }
table.um-table tr:hover td { background:#FFFBEB; }

/* AVATAR */
.user-av { width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid #E2E8F0; }
.user-av-placeholder { width:38px; height:38px; border-radius:50%; background:#F1F5F9; display:inline-flex; align-items:center; justify-content:center; font-weight:700; color:#64748B; font-size:14px; }
.user-cell { display:flex; align-items:center; gap:12px; }
.user-cell-text .uname { font-weight:700; color:#0F172A; font-size:14px; }
.user-cell-text .uemail { font-size:12px; color:#64748B; }

/* ROLE PILL */
.role-pill { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:800; text-transform:uppercase; }
.rp-admin    { background:#EDE9FE; color:#5B21B6; }
.rp-staff    { background:#DBEAFE; color:#1E40AF; }
.rp-welder   { background:#FEF3C7; color:#92400E; }
.rp-customer { background:#D1FAE5; color:#065F46; }

/* STATUS PILL */
.status-pill { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; }
.sp-active   { background:#D1FAE5; color:#065F46; }
.sp-blocked  { background:#FEE2E2; color:#991B1B; }
.sp-archived { background:#F1F5F9; color:#64748B; }

/* ACTION LINKS */
.action-links { display:flex; gap:6px; flex-wrap:wrap; }
.a-btn { padding:5px 12px; border-radius:8px; font-size:12px; font-weight:700; border:none; cursor:pointer; text-decoration:none; }
.a-block  { background:#FEE2E2; color:#991B1B; }
.a-unblock{ background:#D1FAE5; color:#065F46; }
.a-archive{ background:#F1F5F9; color:#374151; }

/* SUMMARY ROW */
.um-summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:22px; }
.um-sum-card { background:#fff; border-radius:12px; border:1px solid #E2E8F0; padding:16px; }
.um-sum-card .n { font-size:24px; font-weight:800; color:#0F172A; }
.um-sum-card .l { font-size:13px; color:#64748B; }
</style>
</head>
<body>
<div class="main um-page">

    <div class="um-header">
        <h2><i class="fas fa-users" style="color:#F59E0B;margin-right:8px;"></i>User Management</h2>
    </div>

    <?php
        $counts = ['admin'=>0,'staff'=>0,'welder'=>0,'customer'=>0];
        $allUsers = $conn->query("SELECT role, COUNT(*) c FROM users GROUP BY role")->fetch_all(MYSQLI_ASSOC);
        foreach ($allUsers as $r) $counts[$r['role']] = $r['c'];
    ?>
    <div class="um-summary">
        <div class="um-sum-card"><div class="n" style="color:#5B21B6;"><?= $counts['admin'] ?></div><div class="l">Admins</div></div>
        <div class="um-sum-card"><div class="n" style="color:#1E40AF;"><?= $counts['staff'] ?></div><div class="l">Staff (Cashier)</div></div>
        <div class="um-sum-card"><div class="n" style="color:#92400E;"><?= $counts['welder'] ?></div><div class="l">Welders</div></div>
        <div class="um-sum-card"><div class="n" style="color:#065F46;"><?= $counts['customer'] ?></div><div class="l">Customers</div></div>
    </div>

    <!-- FILTER -->
    <form method="GET" class="filter-bar">
        <input type="text" name="q" placeholder="Search name or email..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <select name="role">
            <option value="all" <?= $roleFilter==='all'?'selected':'' ?>>All Roles</option>
            <option value="admin"    <?= $roleFilter==='admin'?'selected':'' ?>>Admin</option>
            <option value="staff"    <?= $roleFilter==='staff'?'selected':'' ?>>Staff</option>
            <option value="welder"   <?= $roleFilter==='welder'?'selected':'' ?>>Welder</option>
            <option value="customer" <?= $roleFilter==='customer'?'selected':'' ?>>Customer</option>
        </select>
        <select name="branch">
            <option value="all">All Branches</option>
            <option value="1" <?= $branchFilter==='1'?'selected':'' ?>>Cavite (Bautista)</option>
            <option value="2" <?= $branchFilter==='2'?'selected':'' ?>>Laguna</option>
        </select>
        <button type="submit"><i class="fas fa-search"></i> Filter</button>
    </form>

    <!-- TABLE -->
    <div class="um-table-wrap">
        <table class="um-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($users && $users->num_rows > 0): ?>
                <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <?php if ($u['avatar']): ?>
                                <img class="user-av" src="../uploads/<?= htmlspecialchars($u['avatar']) ?>" alt="Avatar" onerror="this.style.display='none'">
                            <?php else: ?>
                                <div class="user-av-placeholder"><?= strtoupper(substr($u['name'],0,1)) ?></div>
                            <?php endif; ?>
                            <div class="user-cell-text">
                                <div class="uname"><?= htmlspecialchars($u['name']) ?></div>
                                <div class="uemail"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="role-pill rp-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= htmlspecialchars($u['branch_name'] ?? 'N/A') ?></td>
                    <td><?= $u['order_count'] ?></td>
                    <td><span class="status-pill sp-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
                    <td>
                        <div class="action-links">
                            <?php if ($u['status'] === 'active'): ?>
                                <a href="?action=block&id=<?= $u['id'] ?>&role=<?= $roleFilter ?>" class="a-btn a-block" onclick="return confirm('Block this user?')">Block</a>
                            <?php else: ?>
                                <a href="?action=unblock&id=<?= $u['id'] ?>&role=<?= $roleFilter ?>" class="a-btn a-unblock">Unblock</a>
                            <?php endif; ?>
                            <?php if ($u['status'] !== 'archived'): ?>
                                <a href="?action=archive&id=<?= $u['id'] ?>&role=<?= $roleFilter ?>" class="a-btn a-archive" onclick="return confirm('Archive this user?')">Archive</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;padding:40px;color:#94A3B8;">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
