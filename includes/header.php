<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/../config/database.php';

$user_id  = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['name']    ?? 'User';
$userRole = ucfirst($_SESSION['role'] ?? 'Guest');
$avatar   = '';

if ($user_id && isset($conn)) {
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $avatar = $row['avatar'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rholance Trading</title>

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>favicon-v2.ico?v=5">

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Rholance Custom CSS (overrides Bootstrap) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">

    <!-- Dark mode init & Global JS Config -->
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.documentElement.classList.add('dark');
            document.body?.classList.add('dark');
        }
    </script>
</head>
<body class="admin">

<!-- ── SIDEBAR MOBILE BACKDROP ── -->
<div class="rh-sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- ── MOBILE TOGGLE BUTTON ── -->
<button class="rh-sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- ── TOPBAR ── -->
<header class="rh-topbar">
    <!-- Search -->
    <div class="rh-topbar-search">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="form-control" id="liveSearch"
               placeholder="Search orders, inventory..." autocomplete="off">
        <div id="searchResults" class="search-dropdown"></div>
    </div>

    <!-- Right side -->
    <div class="rh-topbar-right">

        <!-- Dark mode toggle -->
        <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
            <input class="form-check-input" type="checkbox" id="darkModeToggle"
                   style="width:2.2em;height:1.1em;cursor:pointer;">
            <label class="form-check-label text-muted" for="darkModeToggle" style="font-size:.8rem;">
                <i class="fas fa-moon"></i>
            </label>
        </div>

        <!-- Notification bell -->
        <button class="rh-notif-btn">
            <i class="far fa-bell"></i>
            <span class="rh-notif-dot"></span>
        </button>

        <!-- User dropdown -->
        <div class="dropdown">
            <button class="rh-user-btn dropdown-toggle" id="userMenuBtn"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    style="--bs-btn-focus-shadow-rgb:none;">
                <div class="rh-avatar">
                    <?php if (!empty($avatar)): ?>
                        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($avatar) ?>" alt="Avatar">
                    <?php else: ?>
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="text-start d-none d-md-block">
                    <div class="rh-user-name"><?= htmlspecialchars($userName) ?></div>
                    <div class="rh-user-role"><?= htmlspecialchars($userRole) ?></div>
                </div>
            </button>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="<?= BASE_URL ?>profile/settings.php">
                        <i class="fa-solid fa-user-gear me-2 text-amber"></i> Profile Settings
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= BASE_URL ?>profile/avatar.php">
                        <i class="fa-solid fa-image me-2 text-amber"></i> Upload Avatar
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<!-- Bootstrap 5 JS Bundle (Popper included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* Dark mode toggle */
const dmToggle = document.getElementById('darkModeToggle');
if (localStorage.getItem('darkMode') === 'enabled') dmToggle.checked = true;
dmToggle.addEventListener('change', () => {
    if (dmToggle.checked) {
        document.body.classList.add('dark');
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'enabled');
    } else {
        document.body.classList.remove('dark');
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', 'disabled');
    }
});

/* Mobile sidebar */
const sidebarEl  = document.getElementById('rhSidebar');
const backdrop   = document.getElementById('sidebarBackdrop');
const toggleBtn  = document.getElementById('sidebarToggle');

if (toggleBtn && sidebarEl) {
    toggleBtn.addEventListener('click', () => {
        sidebarEl.classList.toggle('open');
        backdrop.classList.toggle('open');
    });
    backdrop.addEventListener('click', () => {
        sidebarEl.classList.remove('open');
        backdrop.classList.remove('open');
    });
}
</script>