<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$users = $conn->query("
SELECT id, name, role, status
FROM users
WHERE status = 'archived'
ORDER BY name ASC
");
?>

<div class="main">

<div class="page-header">
    <div>
        <h1>Archived Users</h1>
        <p class="subtitle">Manage and restore archived accounts</p>
    </div>

    <a href="users.php" class="btn-secondary">
        ← Back to Users
    </a>
</div>

<div class="card">

<?php if($users && $users->num_rows > 0): ?>

<div class="table-wrapper">

<table class="modern-table">

<thead>
<tr>
    <th>Name</th>
    <th>Role</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php while($u = $users->fetch_assoc()): ?>

<tr>
    <td><?= htmlspecialchars($u['name']) ?></td>

    <td>
        <span class="role-badge role-<?= strtolower($u['role']) ?>">
            <?= htmlspecialchars($u['role']) ?>
        </span>
    </td>

    <td>
        <span class="status-badge status-archived">
            Archived
        </span>
    </td>

    <td>
        <button 
            class="btn-action success btn-restore" 
            data-id="<?= $u['id'] ?>">
            Restore
        </button>
    </td>
</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>

<?php else: ?>

<div class="empty-state-modern">
    <i class="fa-solid fa-box-archive"></i>
    <p>No archived users found</p>
</div>

<?php endif; ?>

</div> <!-- card -->
</div> <!-- main -->

<!-- =========================
   CLEAN SINGLE SCRIPT (FIXED)
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
    .then(response => {

        if(response !== "success"){
            alert("Something went wrong.");
            return;
        }

        if(action === 'restore'){
            // Smooth remove
            const row = button.closest('tr');
            row.style.transition = "0.3s";
            row.style.opacity = "0";
            setTimeout(() => row.remove(), 300);
        }

    })
    .catch(() => {
        alert("Server error.");
    });
}

/* EVENT BINDING */
document.querySelectorAll('.btn-restore').forEach(btn=>{
    btn.addEventListener('click', function(){

        if(!confirm("Restore this user?")) return;

        handleAction('restore', this.dataset.id, this);

    });
});
</script>