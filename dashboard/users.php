<?php
require_once '../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$userRole = $_SESSION['role'];
$branch = $_SESSION['branch_id'];

/* =========================
   FILTERS
========================= */

$roleFilter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

/* =========================
   SAFE WHERE BUILDER
========================= */

$where = [];
$where[] = "status != 'archived'";

/* ROLE FILTER */
if ($roleFilter !== '') {
    $roleSafe = mysqli_real_escape_string($conn, $roleFilter);
    $where[] = "TRIM(LOWER(role)) = TRIM(LOWER('$roleSafe'))";
}

/* SEARCH FILTER */
if ($search !== '') {
    $searchSafe = mysqli_real_escape_string($conn, $search);
    $where[] = "name LIKE '%$searchSafe%'";
}

/* BRANCH FILTER */
if ($userRole !== 'admin') {
    $where[] = "branch_id = $branch";
}

/* FINAL WHERE */
$baseWhere = "WHERE " . implode(" AND ", $where);
/* =========================
   PAGINATION (ONLY WHEN FILTERED)
========================= */

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* =========================
   TOTAL COUNT (ONLY IF FILTERED)
========================= */

if ($roleFilter !== '') {

    $countQuery = "SELECT COUNT(*) as total FROM users $baseWhere";
    $countResult = $conn->query($countQuery);
    $totalUsers = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalUsers / $limit);

} else {
    $totalPages = 1; // no pagination
}

/* =========================
   FETCH USERS
========================= */

$query = "
    SELECT id, name, role, status, is_verified
    FROM users
    $baseWhere
";

/*  FIX: ORDER DEPENDS ON FILTER */
if ($roleFilter !== '') {
    $query .= " ORDER BY name ASC";  // ONLY SORT BY NAME WHEN FILTERED
} else {
    $query .= " ORDER BY role ASC, name ASC"; // GROUP ONLY WHEN ALL ROLES
}

/* PAGINATION ONLY WHEN FILTERED */
if ($roleFilter !== '') {
    $query .= " LIMIT $limit OFFSET $offset";
}

$users = $conn->query($query);
?>

<div class="main">


<div class="page-header">
    <div>
        <h1>Users</h1>
        <p class="subtitle">Manage system users</p>
    </div>

    <a href="archived_users.php" class="btn-secondary">
        View Archived Users
    </a>
</div>

<div class="users-card">

<form method="GET" class="users-filter">

    <div class="filter-item">
        <select name="role" class="filter-control" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="admin" <?= ($roleFilter=='admin')?'selected':'' ?>>Admin</option>
            <option value="staff" <?= ($roleFilter=='staff')?'selected':'' ?>>Staff</option>
            <option value="welder" <?= ($roleFilter=='welder')?'selected':'' ?>>Welder</option>
            <option value="customer" <?= ($roleFilter=='customer')?'selected':'' ?>>Customer</option>
        </select>
    </div>

    <div class="filter-item search-wrapper">
    <i class="fa-solid fa-magnifying-glass search-icon"></i>
    <input 
        type="text" 
        name="search" 
        placeholder="Search user..." 
        value="<?= htmlspecialchars($search) ?>"
        class="filter-control search-input"
    >
</div>

    <div class="filter-item">
        <button type="submit" class="filter-control filter-btn">
            Search
        </button>
    </div>

</form>

<table class="modern-table">

<thead>
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Role</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php if($users && $users->num_rows > 0): ?>

    <?php 
$currentRole = '';
$counter = ($roleFilter !== '') ? $offset + 1 : 1; // IMPORTANT
?>

<?php 
$currentRole = '';

while($u = $users->fetch_assoc()): 

    if($roleFilter === '' && $currentRole !== $u['role']):
        $currentRole = $u['role'];
?>

<tr class="role-header">
    <td colspan="5" class="role-header-cell">
        <div class="role-label">
            <?= htmlspecialchars(ucfirst($currentRole)) ?>
        </div>
    </td>
</tr>

<?php endif; ?>

<tr>
   <td><?= $counter++ ?></td>
<td><?= htmlspecialchars($u['name']) ?></td>
    <td>
        <span class="role-badge role-<?= strtolower($u['role']) ?>">
            <?= htmlspecialchars($u['role']) ?>
        </span>
    </td>

    <td>
        <span class="status-badge status-<?= $u['status'] ?>">
            <?= ucfirst($u['status']) ?>
        </span>
    </td>

    <td>

    <?php if(strtolower($u['role']) === 'admin'): ?>

        <span class="owner-badge">Owner</span>

    <?php else: ?>

 <button class="btn-action info btn-view"
    data-id="<?= $u['id'] ?>"
    data-name="<?= htmlspecialchars($u['name']) ?>"
    data-role="<?= htmlspecialchars($u['role']) ?>"
    data-status="<?= htmlspecialchars($u['status']) ?>"
    data-verified="<?= $u['is_verified'] ?>"
>
    View
</button>

        <button class="btn-action warning btn-archive" data-id="<?= $u['id'] ?>">
            Archive
        </button>

    <?php endif; ?>

    </td>
</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
    <td colspan="5" style="text-align:center;">No users found.</td>
</tr>

<?php endif; ?>

</tbody>
</table>

<?php if ($roleFilter !== ''): ?>

<div class="pagination">

    <?php if ($page > 1): ?>
        <a href="?role=<?= $roleFilter ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" class="page-btn nav-btn">
            ←
        </a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>

        <?php if ($i == $page): ?>
            <span class="page-btn active"><?= $i ?></span>
        <?php else: ?>
            <a href="?role=<?= $roleFilter ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>" class="page-btn">
                <?= $i ?>
            </a>
        <?php endif; ?>

    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?role=<?= $roleFilter ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" class="page-btn nav-btn">
            →
        </a>
    <?php endif; ?>

</div>

<?php endif; ?>



</div>
<!-- VIEW USER MODAL -->
<div id="viewModal" class="custom-modal">
    <div class="custom-modal-content">

        <span class="close-modal" id="closeViewModal">&times;</span>

        <h2>User Information</h2>

        <div class="modal-body">
            <p><strong>Name:</strong> <span id="viewName"></span></p>
            <p><strong>Role:</strong> <span id="viewRole"></span></p>
            <p><strong>Status:</strong> <span id="viewStatus"></span></p>
            <p><strong>Verified:</strong> <span id="viewVerified"></span></p>
        </div>

    </div>
</div>
</div>
</div>

<!-- =========================
   AJAX SCRIPT (NO RELOAD)
========================= -->
<script>
function handleAction(action, id, button){

    fetch('user_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=${action}&id=${id}`
    })
    .then(res => res.text())
    .then(() => {
         
        if(action === 'restore'){
    // Remove row from archived list instantly
    button.closest('tr').remove();
}

        if(action === 'archive'){
            button.closest('tr').remove();
        }

        if(action === 'block'){
            button.textContent = 'Unblock';
            button.classList.remove('danger');
            button.classList.add('success');
            button.classList.remove('btn-block');
            button.classList.add('btn-unblock');
            button.onclick = () => handleAction('unblock', id, button);
        }

        if(action === 'unblock'){
            button.textContent = 'Block';
            button.classList.remove('success');
            button.classList.add('danger');
            button.classList.remove('btn-unblock');
            button.classList.add('btn-block');
            button.onclick = () => handleAction('block', id, button);
        }

    });
}

/* EVENT BINDING */

document.querySelectorAll('.btn-block').forEach(btn=>{
    btn.addEventListener('click', function(){
        handleAction('block', this.dataset.id, this);
    });
});

document.querySelectorAll('.btn-unblock').forEach(btn=>{
    btn.addEventListener('click', function(){
        handleAction('unblock', this.dataset.id, this);
    });
});

document.querySelectorAll('.btn-archive').forEach(btn=>{
    btn.addEventListener('click', function(){

        if(!confirm("Are you sure you want to archive this user?")) return;

        handleAction('archive', this.dataset.id, this);
    });
});

document.querySelectorAll('.btn-restore').forEach(btn=>{
    btn.addEventListener('click', function(){

        if(!confirm("Restore this user?")) return;

        handleAction('restore', this.dataset.id, this);

    });
});


const viewModal = document.getElementById("viewModal");
const closeViewModal = document.getElementById("closeViewModal");

document.querySelectorAll('.btn-view').forEach(btn => {

    btn.addEventListener('click', function(){

        document.getElementById('viewName').textContent = this.dataset.name;
        document.getElementById('viewRole').textContent = this.dataset.role;
        document.getElementById('viewStatus').textContent = this.dataset.status;

        document.getElementById('viewVerified').textContent =
            this.dataset.verified == 1 ? "Yes" : "No";

        viewModal.style.display = "flex";
    });

});

closeViewModal.addEventListener("click", () => {
    viewModal.style.display = "none";
});

window.addEventListener("click", (e) => {
    if(e.target === viewModal){
        viewModal.style.display = "none";
    }
});

</script>