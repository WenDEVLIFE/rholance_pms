<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!in_array($_SESSION['role'], ['admin', 'staff'])) { header("Location: ../index.php"); exit; }

// Process Archive/Unarchive Action
if (isset($_GET['action'], $_GET['id'])) {
    $uid = (int)$_GET['id'];
    match($_GET['action']) {
        'archive'   => $conn->query("UPDATE users SET status='archived' WHERE id=$uid"),
        'unarchive' => $conn->query("UPDATE users SET status='active' WHERE id=$uid"),
        default     => null
    };
    header("Location: user_management.php?msg=User status updated successfully"); exit;
}

$roleF   = $conn->real_escape_string($_GET['role']   ?? 'all');
$branchF = (int)($_GET['branch'] ?? 0);
$q       = $conn->real_escape_string($_GET['q'] ?? '');

$where = "WHERE u.id != {$_SESSION['user_id']}";
if ($roleF !== 'all') $where .= " AND u.role='$roleF'";
if ($branchF)         $where .= " AND u.branch_id=$branchF";
if ($q)               $where .= " AND (u.name LIKE '%$q%' OR u.email LIKE '%$q%' OR u.phone LIKE '%$q%' OR u.address LIKE '%$q%')";

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
            <h1>User Account Management</h1>
            <p>Monitor and view accounts, details, and access logs across all branches.</p>
        </div>
        <button class="btn btn-primary px-4 fw-800 shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="prepAdd()">
            <i class="fas fa-user-plus me-2"></i>Add New Account
        </button>
    </div>

    <!-- FEEDBACK MESSAGES -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['err']) ?></div>
    <?php endif; ?>

    <!-- SUMMARY CARDS (clickable role filters) -->
    <div class="row g-3 mb-4">
        <?php $summaries = [['admin','Admin','bg-purple'],['staff','Staff','bg-blue'],['welder','Welder','bg-amber'],['customer','Customer','bg-green']];
        foreach ($summaries as [$role,$label,$bg]): ?>
        <div class="col-6 col-md-3">
            <a href="?role=<?= $role ?>" class="text-decoration-none">
            <div class="rh-stat-card border-0 shadow-sm" style="cursor:pointer;transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 0 0 2px var(--rh-amber)'" onmouseout="this.style.boxShadow=''">
                <div class="rh-stat-icon <?= $bg ?> text-white"><i class="fas fa-user-shield"></i></div>
                <div>
                    <div class="rh-stat-label"><?= $label ?>s</div>
                    <div class="rh-stat-value"><?= $counts[$role] ?></div>
                </div>
            </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- FILTER BAR -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-700">Search Accounts</label>
                    <input type="text" name="q" class="form-control" placeholder="Name, email, phone, address..." value="<?= htmlspecialchars($_GET['q']??'') ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-700">Filter Role</label>
                    <select name="role" class="form-select">
                        <option value="all">All Roles</option>
                        <?php foreach (['admin','staff','welder','customer'] as $r): ?>
                        <option value="<?= $r ?>" <?= $roleF===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-700">Filter Branch</label>
                    <select name="branch" class="form-select">
                        <option value="0">All Branches</option>
                        <option value="1" <?= $branchF==1?'selected':'' ?>>Cavite (Bautista)</option>
                        <option value="2" <?= $branchF==2?'selected':'' ?>>Laguna</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-dark w-100 fw-700"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">User Info</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($users && $users->num_rows > 0): ?>
                    <?php while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rh-avatar">
                                    <?php if (!empty($u['avatar'])): ?>
                                        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($u['avatar']) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                    <?php else: ?>
                                        <?= strtoupper(substr($u['name'],0,1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="fw-700 small text-light-emphasis"><?= htmlspecialchars($u['name']) ?></div>
                                    <div class="text-muted small" style="font-size:.75rem;"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td class="small text-light-emphasis"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                        <td class="small text-light-emphasis text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($u['address'] ?? '') ?>">
                            <?= htmlspecialchars($u['address'] ?? '—') ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($u['status']) ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end align-items-center">
                                <!-- View Info Button -->
                                <button class="btn btn-sm btn-outline-info" onclick='viewInfo(<?= json_encode($u) ?>)' title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <!-- Edit Account Button -->
                                <button class="btn btn-sm btn-outline-dark" onclick='prepEdit(<?= json_encode($u) ?>)' title="Edit Account">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- Archive Button (Replaced Block with Archive) -->
                                <?php if ($u['status'] === 'active'): ?>
                                    <a href="?action=archive&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" title="Archive User" onclick="return confirm('Are you sure you want to archive this user?')">
                                        <i class="fas fa-archive"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?action=unarchive&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-success" title="Unarchive User">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No accounts found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- USER DETAILS VIEW MODAL -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800"><i class="fas fa-user-circle me-2 text-amber"></i>Account Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="rh-avatar mx-auto mb-3" style="width:90px;height:90px;font-size:2.5rem;" id="detAvatar">U</div>
                    <h4 class="fw-800 m-0" id="detName">—</h4>
                    <span class="badge id="detRoleBadge" style="margin-top:5px; font-size: 0.8rem;">—</span>
                </div>
                <div class="row g-3">
                    <div class="col-12 border-bottom pb-2">
                        <span class="text-muted d-block small fw-700">EMAIL ADDRESS</span>
                        <span class="fw-600 text-light-emphasis" id="detEmail">—</span>
                    </div>
                    <div class="col-6 border-bottom pb-2">
                        <span class="text-muted d-block small fw-700">PHONE NUMBER</span>
                        <span class="fw-600 text-light-emphasis" id="detPhone">—</span>
                    </div>
                    <div class="col-6 border-bottom pb-2">
                        <span class="text-muted d-block small fw-700">BRANCH</span>
                        <span class="fw-600 text-light-emphasis" id="detBranch">—</span>
                    </div>
                    <div class="col-12">
                        <span class="text-muted d-block small fw-700">HOME ADDRESS</span>
                        <span class="fw-600 text-light-emphasis" id="detAddress">—</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4 fw-700" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- USER CREATE/EDIT MODAL -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800" id="modalTitle">Add New Account</h5>
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
                        <div class="col-12">
                            <label class="form-label small fw-700">Phone Number</label>
                            <input type="text" name="phone" id="formPhone" class="form-control" placeholder="e.g. 09171234567">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Home Address</label>
                            <textarea name="address" id="formAddress" class="form-control" rows="2" placeholder="Full residential address..."></textarea>
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
                    <button type="submit" class="btn btn-primary px-4 fw-800 shadow-sm" id="submitBtn">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const userModal = new bootstrap.Modal(document.getElementById('userModal'));
const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));

function viewInfo(u) {
    document.getElementById('detName').textContent = u.name;
    document.getElementById('detEmail').textContent = u.email;
    document.getElementById('detPhone').textContent = u.phone ? u.phone : '—';
    document.getElementById('detAddress').textContent = u.address ? u.address : '—';
    document.getElementById('detBranch').textContent = u.branch_id == 1 ? 'Cavite' : 'Laguna';
    
    // Set role badge
    const badge = document.getElementById('detRoleBadge');
    badge.textContent = u.role.toUpperCase();
    badge.className = 'badge badge-role-' + u.role;

    // Avatar setup
    const av = document.getElementById('detAvatar');
    if (u.avatar) {
        av.innerHTML = `<img src="${BASE_URL}uploads/${u.avatar}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
    } else {
        av.textContent = u.name.substring(0, 1).toUpperCase();
        av.innerHTML = u.name.substring(0, 1).toUpperCase();
    }
    
    detailsModal.show();
}

function prepAdd() {
    document.getElementById('modalTitle').textContent = 'Add New Account';
    document.getElementById('formAction').value = 'create';
    document.getElementById('submitBtn').textContent = 'Create Account';
    document.getElementById('formUserId').value = '';
    document.getElementById('formName').value = '';
    document.getElementById('formEmail').value = '';
    document.getElementById('formPhone').value = '';
    document.getElementById('formAddress').value = '';
    document.getElementById('formRole').value = 'customer';
    document.getElementById('formBranch').value = '1';
    document.getElementById('formPass').required = true;
    document.getElementById('passHint').textContent = 'Required for new accounts.';
}

function prepEdit(u) {
    document.getElementById('modalTitle').textContent = 'Edit Account';
    document.getElementById('formAction').value = 'update';
    document.getElementById('submitBtn').textContent = 'Save Changes';
    document.getElementById('formUserId').value = u.id;
    document.getElementById('formName').value = u.name;
    document.getElementById('formEmail').value = u.email;
    document.getElementById('formPhone').value = u.phone ? u.phone : '';
    document.getElementById('formAddress').value = u.address ? u.address : '';
    document.getElementById('formRole').value = u.role;
    document.getElementById('formBranch').value = u.branch_id;
    document.getElementById('formPass').required = false;
    document.getElementById('passHint').textContent = 'Leave blank to keep current password.';
    userModal.show();
}
</script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        pageLength: 20,
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: [5] }],
        language: { search: 'Search accounts:' }
    });
});
</script>
</body></html>
