<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">

    <!-- Branch -->
    <div class="sidebar-brand">
    <img src="../assets/images/logoo.png" class="sidebar-logo">

    <div class="brand-text">
        <span class="brand-title">Rholance</span>
        <span class="brand-sub">Trading System</span>
    </div>
</div>


<hr class="sidebar-divider">

    <!-- Branch Selector (OWNER ONLY) -->
   <?php if ($_SESSION['role'] === 'admin'): ?>

<div class="branch-select">
    <span class="branch-label">
        <i class="fa-solid fa-code-branch"></i> All Branches
    </span>

   <form method="POST" action="/rholance_pms/branches/switch.php">
       <select name="branch_id" id="branchSelect">
            <option value="1" <?= $_SESSION['branch_id']==1?'selected':'' ?>>
             Dasmariñas, Cavite 
            </option>
            <option value="2" <?= $_SESSION['branch_id']==2?'selected':'' ?>>
                Biñan, Laguna
            </option>
        </select>
    </form>

</div>

<?php endif; ?>
    

    <!-- ================= CUSTOMER NAVIGATION ================= -->
<?php if ($_SESSION['role'] === 'customer'): ?>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar-nav">

    <!-- DASHBOARD -->
    <a href="../customer/dashboard.php" 
       class="nav-item <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i>
        <span>Dashboard</span>
    </a>

    <!-- PRODUCTS (Product Viewing Module) -->
    <a href="../products/index.php" 
       class="nav-item <?= $currentPage == 'index.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-box"></i>
        <span>Products</span>
    </a>

    <!-- CUSTOM ORDER -->
<a href="../customer/projects.php"
   class="nav-item <?= $currentPage == 'projects.php' ? 'active' : '' ?>">
    <i class="fa-solid fa-pen-ruler"></i>
    <span>My Projects</span>
</a>

    <!-- APPOINTMENT MODULE -->
    <a href="../customer/available_appointments.php" 
       class="nav-item <?= $currentPage == 'available_appointments.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-calendar-check"></i>
        <span>My Appointment</span>
    </a>

    <!-- 🔥 NEW: TRANSACTIONS MODULE -->
    <a href="../customer/transactions.php" 
       class="nav-item <?= $currentPage == 'transactions.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-receipt"></i>
        <span>Transactions</span>
    </a>

</nav>

<?php endif; ?>

    <!-- ================= OWNER NAVIGATION ================= -->
<?php if ($_SESSION['role'] === 'admin'): ?>
<nav class="sidebar-nav">

<a href="../admin/dashboard.php" 
class="nav-item <?= (strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false) ? 'active' : '' ?>">
<i class="fa-solid fa-gauge-high"></i>
<span>Dashboard</span>
</a>

<a href="../dashboard/users.php" 
class="nav-item <?= (strpos($_SERVER['REQUEST_URI'], 'users.php') !== false) ? 'active' : '' ?>">
<i class="fa-solid fa-users"></i>
<span>User Management</span>
</a>

<a href="../orders/orders.php" 
class="nav-item <?= (strpos($_SERVER['REQUEST_URI'], 'orders') !== false) ? 'active' : '' ?>">
<i class="fa-solid fa-file-lines"></i>
<span>Custom Orders</span>
</a>

<a href="../inventory/index.php" 
class="nav-item <?= (strpos($_SERVER['REQUEST_URI'], 'inventory') !== false) ? 'active' : '' ?>">
<i class="fa-solid fa-boxes-stacked"></i>
<span>Inventory</span>
</a>

<a href="../reports/index.php"
class="nav-item <?= (strpos($_SERVER['REQUEST_URI'], 'reports') !== false) ? 'active' : '' ?>">
<i class="fa-solid fa-chart-line"></i>
<span>Reports</span>
</a>

</nav>
<?php endif; ?>

<!-- ================= STAFF NAVIGATION ================= -->

<?php if ($_SESSION['role'] === 'staff'): ?>
<nav class="sidebar-nav">

    <a href="../staff/dashboard.php" 
       class="nav-item <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-chart-line"></i>
        <span>Dashboard</span>
    </a>

    <a href="../staff/appointment.php" 
       class="nav-item <?= ($current_page == 'appointment.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-calendar-check"></i>
        <span>Appointments</span>
    </a>

    <a href="../orders/index.php" 
       class="nav-item <?= ($current_dir == 'orders') ? 'active' : '' ?>">
        <i class="fa-solid fa-file-lines"></i>
        <span>Custom Orders</span>
    </a>

    <a href="../inventory/index.php" 
       class="nav-item <?= ($current_dir == 'inventory') ? 'active' : '' ?>">
        <i class="fa-solid fa-boxes-stacked"></i>
        <span>Inventory</span>
    </a>

    <a href="../staff/pos/index.php" 
       class="nav-item <?= ($current_dir == 'pos') ? 'active' : '' ?>">
        <i class="fa-solid fa-cash-register"></i>
        <span>POS Terminal</span>
    </a>

    <a href="../tasks/index.php" 
       class="nav-item <?= ($current_dir == 'tasks') ? 'active' : '' ?>">
        <i class="fa-solid fa-list-check"></i>
        <span>Task Management</span>
    </a>

</nav>
<?php endif; ?>

<script>
document.getElementById('branchSelect').addEventListener('change', function(){

    const branchId = this.value;

    fetch('/rholance_pms/branches/switch.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'branch_id=' + branchId
    })
    .then(() => {
        // ✅ RELOAD PAGE (CRITICAL)
        window.location.reload();
    });

});
</script>
</div>