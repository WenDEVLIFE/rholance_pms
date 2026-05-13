<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit; }

// Basic Action Processing (Block/Unblock/Archive)
if (isset($_GET['action'], $_GET['id'])) {
    $uid = (int)$_GET['id'];
    match($_GET['action']) {
        'block'   => $conn->query("UPDATE users SET status='blocked'  WHERE id=$uid"),
        'unblock' => $conn->query("UPDATE users SET status='active'   WHERE id=$uid"),
        'archive' => $conn->query("UPDATE users SET status='archived' WHERE id=$uid"),
        default   => null
    };
    header("Location: user_management.php"); exit;
}

$roleF   = $conn->real_escape_string($_GET['role']   ?? 'all');
$branchF = (int)($_GET['branch'] ?? 0);
$q       = $conn->real_escape_string($_GET['q'] ?? '');

$where = "WHERE u.id != {$_SESSION['user_id']}";
if ($roleF !== 'all') $where .= " AND u.role='$roleF'";
if ($branchF)         $where .= " AND u.branch_id=$branchF";
if ($q)               $where .= " AND (u.name LIKE '%$q%' OR u.email LIKE '%$q%')";

$users = $conn->query("
    SELECT u.*, b.name AS branch_name,
        (SELECT COUNT(*) FROM custom_orders co WHERE co.customer_id=u.id) orders
    FROM users u LEFT JOIN branches b ON b.id=u.branch_id
    $where ORDER BY FIELD(u.role,'admin','staff','welder','customer'), u.name ASC
");

$counts = ['admin'=>0, 'staff'=>0, 'welder'=>0, 'customer'=>0];
$roleCounts = $conn->query("SELECT role, COUNT(*) c FROM users GROUP BY role");
if ($roleCounts) {
    while ($r = $roleCounts->fetch_assoc()) $counts[$r['role']] = $r['c'];
}
?>

<div class="rh-main">
    <div class="rh-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1>User Management</h1>
            <p>Manage accounts across all roles and branches.</p>
        </div>
        <button class="btn btn-primary px-4 fw-800 shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="prepAdd()">
            <i class="fas fa-user-plus me-2"></i>Add New User
        </button>
    </div>

    <!-- FEEDBACK -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <?php $summaries = [['admin','Admin','bg-purple'],['staff','Staff','bg-blue'],['welder','Welder','bg-amber'],['customer','Customer','bg-green']];
        foreach ($summaries as [$role,$label,$bg]): ?>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card border-0 shadow-sm">
                <div class="rh-stat-icon <?= $bg ?>"><i class="fas fa-user"></i></div>
                <div>
                    <div class="rh-stat-label"><?= $label ?>s</div>
                    <div class="rh-stat-value"><?= $counts[$role] ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- FILTER FORM -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-700">Search</label>
                    <input type="text" name="q" class="form-control" placeholder="Name or email..." value="<?= htmlspecialchars($_GET['q']??'') ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-700">Role</label>
                    <select name="role" class="form-select">
                        <option value="all">All Roles</option>
                        <?php foreach (['admin','staff','welder','customer'] as $r): ?>
                        <option value="<?= $r ?>" <?= $roleF===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-700">Branch</label>
                    <select name="branch" class="form-select">
                        <option value="0">All Branches</option>
                        <option value="1" <?= $branchF==1?'selected':'' ?>>Cavite (Bautista)</option>
                        <option value="2" <?= $branchF==2?'selected':'' ?>>Laguna</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-dark w-100"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr><th class="ps-4">User</th><th>Role</th><th>Branch</th><th>Orders</th><th>Status</th><th class="text-end pe-4">Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($users && $users->num_rows > 0): ?>
                    <?php while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rh-avatar">
                                    <?php if (!empty($u['avatar'])): ?>
                                        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($u['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <?= strtoupper(substr($u['name'],0,1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="fw-700 small"><?= htmlspecialchars($u['name']) ?></div>
                                    <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td class="small"><?= htmlspecialchars($u['branch_name'] ?? '—') ?></td>
                        <td><?= $u['orders'] ?></td>
                        <td><span class="badge badge-status-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm btn-outline-dark" onclick='prepEdit(<?= json_encode($u) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($u['status']==='active'): ?>
                                    <a href="?action=block&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" title="Block User"><i class="fas fa-ban"></i></a>
                                <?php else: ?>
                                    <a href="?action=unblock&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-success" title="Unblock User"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No users found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- USER MODAL (ADD/EDIT) -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800" id="modalTitle">Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_user.php" method="POST">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="user_id" id="formUserId">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-700">Full Name</label>
                            <input type="text" name="name" id="formName" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Email Address</label>
                            <input type="email" name="email" id="formEmail" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Role</label>
                            <select name="role" id="formRole" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="welder">Welder</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Branch</label>
                            <select name="branch_id" id="formBranch" class="form-select" required>
                                <option value="1">Cavite</option>
                                <option value="2">Laguna</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Password</label>
                            <input type="password" name="password" id="formPass" class="form-control" placeholder="Enter password">
                            <div class="form-text small" id="passHint">Required for new users.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-800 shadow-sm" id="submitBtn">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const userModal = new bootstrap.Modal(document.getElementById('userModal'));

function prepAdd() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('formAction').value = 'create';
    document.getElementById('submitBtn').textContent = 'Create User';
    document.getElementById('formUserId').value = '';
    document.getElementById('formName').value = '';
    document.getElementById('formEmail').value = '';
    document.getElementById('formRole').value = 'customer';
    document.getElementById('formBranch').value = '1';
    document.getElementById('formPass').required = true;
    document.getElementById('passHint').textContent = 'Required for new users.';
}

function prepEdit(u) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('formAction').value = 'update';
    document.getElementById('submitBtn').textContent = 'Save Changes';
    document.getElementById('formUserId').value = u.id;
    document.getElementById('formName').value = u.name;
    document.getElementById('formEmail').value = u.email;
    document.getElementById('formRole').value = u.role;
    document.getElementById('formBranch').value = u.branch_id;
    document.getElementById('formPass').required = false;
    document.getElementById('passHint').textContent = 'Leave blank to keep current password.';
    userModal.show();
}
</script>
</body></html>
