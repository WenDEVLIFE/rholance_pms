<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$cur_page    = basename($_SERVER['PHP_SELF']);
$cur_dir     = basename(dirname($_SERVER['PHP_SELF']));
$_role       = $_SESSION['role'] ?? '';          // ← safe key access (no warnings)

function rh_nav($href, $icon, $label, $active, $badge = 0) {
    $cls = $active ? 'rh-nav-link active' : 'rh-nav-link';
    $badgeHtml = $badge > 0 ? "<span class=\"rh-badge\">$badge</span>" : '';
    echo "<a href=\"$href\" class=\"$cls\"><i class=\"$icon\"></i><span>$label</span>$badgeHtml</a>";
}

/* Sidebar notification counts (staff only) */
$_sidebarPendingQuotes   = 0;
$_sidebarPendingPayments = 0;
$_sidebarLowStock        = 0;
if (($_role ?? '') === 'staff' && isset($conn) && !$conn->connect_error) {
    $branch = $_SESSION['branch_id'] ?? 1;
    $r = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE quote_status='Pending Review'");
    if ($r) $_sidebarPendingQuotes = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM custom_orders WHERE payment_status='Pending Verification'");
    if ($r) $_sidebarPendingPayments = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM inventory inv WHERE inv.branch_id=$branch AND inv.current_stock <= inv.min_stock");
    if ($r) $_sidebarLowStock = $r->fetch_assoc()['c'];
}
$_sidebarOrdersBadge = $_sidebarPendingQuotes + $_sidebarPendingPayments;
?>

<aside class="rh-sidebar" id="rhSidebar">

    <!-- BRAND -->
    <a href="<?= BASE_URL ?>index.php" class="rh-sidebar-brand text-decoration-none">
        <img src="<?= BASE_URL ?>assets/images/logoo.png" alt="Logo"
             onerror="this.style.display='none'">
        <div>
            <div class="brand-name">Rholance</div>
            <div class="brand-sub">Trading System</div>
        </div>
    </a>

    <!-- BRANCH SELECTOR (Admin only) -->
    <?php if ($_role === 'admin'): ?>
    <div class="branch-select px-3 py-2">
        <form method="POST" action="<?= BASE_URL ?>branches/switch.php">
            <div class="input-group input-group-sm">
                <span class="input-group-text"
                      style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);color:#CBD5E1;">
                    <i class="fas fa-code-branch"></i>
                </span>
                <select name="branch_id" id="branchSelect"
                        class="form-select form-select-sm"
                        style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.15);color:#CBD5E1;">
                    <option value="1" <?= ($_SESSION['branch_id']==1)?'selected':'' ?>>Dasmariñas, Cavite</option>
                    <option value="2" <?= ($_SESSION['branch_id']==2)?'selected':'' ?>>Biñan, Laguna</option>
                </select>
            </div>
        </form>
    </div>
    <div class="rh-divider"></div>
    <?php elseif (in_array($_role, ['staff', 'welder'])): ?>
    <div class="branch-select px-3 py-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text"
                  style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);color:#CBD5E1;">
                <i class="fas fa-code-branch"></i>
            </span>
            <input type="text" class="form-control form-control-sm" readonly
                   style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.15);color:#CBD5E1;"
                   value="<?= ($_SESSION['branch_id'] ?? 1) == 1 ? 'Dasmariñas, Cavite' : 'Biñan, Laguna' ?>">
        </div>
    </div>
    <div class="rh-divider"></div>
    <?php endif; ?>

    <!-- ── CUSTOMER NAVIGATION ── -->
    <?php if ($_role === 'customer'): ?>
    <div class="rh-sidebar-section">Menu</div>
    <nav class="flex-column">
        <?php rh_nav(BASE_URL . 'customer/dashboard.php',           'fas fa-house',         'Dashboard',    $cur_page==='dashboard.php'); ?>
        <?php rh_nav(BASE_URL . 'customer/my_projects.php',         'fas fa-diagram-project','My Projects', in_array($cur_page,['my_projects.php','project_details.php'])); ?>
        <?php rh_nav(BASE_URL . 'customer/available_appointments.php','fas fa-calendar-check','Appointments',$cur_page==='available_appointments.php'); ?>
        <?php rh_nav(BASE_URL . 'customer/transactions.php',        'fas fa-receipt',       'Transactions', $cur_page==='transactions.php'); ?>
        <?php rh_nav(BASE_URL . 'products/index.php',               'fas fa-box',           'Products',     $cur_dir==='products'); ?>
    </nav>
    <?php endif; ?>

    <!-- ── ADMIN NAVIGATION ── -->
    <?php if ($_role === 'admin'): ?>
    <div class="rh-sidebar-section">Overview</div>
    <nav class="flex-column">
        <?php rh_nav(BASE_URL . 'admin/dashboard.php',       'fas fa-gauge-high',   'Dashboard',       strpos($_SERVER['REQUEST_URI'],'admin/dashboard')!==false); ?>
        <?php rh_nav(BASE_URL . 'admin/sales_reports.php',   'fas fa-chart-line',   'Sales Reports',   strpos($_SERVER['REQUEST_URI'],'sales_reports')!==false); ?>
    </nav>
    <div class="rh-sidebar-section">Management</div>
    <nav class="flex-column">
        <?php rh_nav(BASE_URL . 'admin/user_management.php', 'fas fa-users',        'User Management', strpos($_SERVER['REQUEST_URI'],'user_management')!==false); ?>
        <?php rh_nav(BASE_URL . 'admin/custom_variants.php', 'fas fa-palette',      'Custom Variants', strpos($_SERVER['REQUEST_URI'],'custom_variants')!==false); ?>
    </nav>
    <?php endif; ?>

    <!-- ── STAFF NAVIGATION ── -->
    <?php if ($_role === 'staff'): ?>
    <div class="rh-sidebar-section">Menu</div>
    <nav class="flex-column">
        <?php rh_nav(BASE_URL . 'staff/dashboard.php',             'fas fa-gauge-high',     'Dashboard',        $cur_page==='dashboard.php'); ?>
        <?php rh_nav(BASE_URL . 'staff/appointment.php',           'fas fa-calendar-check', 'Appointments',     $cur_page==='appointment.php'); ?>
        <?php rh_nav(BASE_URL . 'orders/orders.php',               'fas fa-file-lines',     'Custom Orders & Projects',    $cur_dir==='orders', $_sidebarOrdersBadge); ?>
        <?php rh_nav(BASE_URL . 'inventory/index.php',             'fas fa-boxes-stacked',  'Inventory',        $cur_dir==='inventory', $_sidebarLowStock); ?>
        <?php rh_nav(BASE_URL . 'staff/pos/index.php',             'fas fa-cash-register',  'POS Terminal',     $cur_dir==='pos'); ?>
    </nav>
    <div class="rh-sidebar-section">Management</div>
    <nav class="flex-column">
        <?php rh_nav(BASE_URL . 'admin/custom_variants.php',       'fas fa-palette',        'Custom Variants',  strpos($_SERVER['REQUEST_URI'],'custom_variants')!==false); ?>
    </nav>
    <?php endif; ?>

    <!-- ── WELDER NAVIGATION ── -->
    <?php if ($_role === 'welder'): ?>
    <div class="rh-sidebar-section">Menu</div>
    <nav class="flex-column">
        <?php rh_nav(BASE_URL . 'staff/welder_dashboard.php', 'fas fa-hard-hat',    'My Projects',  $cur_page==='welder_dashboard.php'); ?>
    </nav>
    <?php endif; ?>

    <div class="mt-auto p-3">
    </div>

</aside>

<script>
/* Branch auto-submit */
const bSel = document.getElementById('branchSelect');
if (bSel) {
    bSel.addEventListener('change', function() {
        fetch('<?= BASE_URL ?>branches/switch.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'branch_id=' + this.value
        }).then(() => window.location.reload());
    });
}
</script>