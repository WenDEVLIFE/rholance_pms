<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../config/database.php';

/* =========================
   USER DATA
========================= */
$user_id  = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['name'] ?? 'User';
$userRole = ucfirst($_SESSION['role'] ?? 'Guest');

$avatar = '';

if ($user_id && isset($conn)) {
    $result = mysqli_query($conn, "SELECT avatar FROM users WHERE id='$user_id'");
    if ($row = mysqli_fetch_assoc($result)) {
        $avatar = $row['avatar'] ?? '';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rholance Trading</title>

    <link rel="icon" href="/rholance_pms/favicon2.ico">
    <link rel="shortcut icon" href="/rholance_pms/favicon2.ico">

    <script>
        if (localStorage.getItem("darkMode") === "enabled") {
            document.documentElement.classList.add("dark");
        }
    </script>

    <!-- ✅ USE CONSISTENT ROOT PATH -->
    <link rel="stylesheet" href="/rholance_pms/assets/css/style.css">
    <link rel="stylesheet" href="/rholance_pms/assets/css/customer-dashboard.css">
    <link rel="stylesheet" href="/rholance_pms/assets/css/product.css?v=2.0">
    <link rel="stylesheet" href="/rholance_pms/assets/css/transactions.css">
    <link rel="icon" href="/rholance_pms/favicon-v2.ico?v=5">
<link rel="shortcut icon" href="/rholance_pms/favicon-v2.ico?v=5">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="admin">

<div class="topbar">

    <!-- SEARCH -->
    <div class="topbar-search">
        <i class="fa fa-search"></i>
        <input 
            type="text" 
            id="liveSearch" 
            placeholder="Search orders, inventory, tasks..."
            autocomplete="off"
        >
        <div id="searchResults" class="search-dropdown"></div>
    </div>

    <div class="topbar-right">

        <!-- DARK MODE -->
        <div class="dark-toggle">
            <input type="checkbox" id="darkModeToggle">
            <label for="darkModeToggle" class="toggle-ui">
                <i class="fas fa-sun icon sun"></i>
                <i class="fas fa-moon icon moon"></i>
                <div class="toggle-ball"></div>
            </label>
        </div>

        <!-- NOTIFICATIONS -->
        <div class="topbar-icon">
            <i class="fa-regular fa-bell"></i>
            <span class="notif-dot"></span>
        </div>

        <!-- USER -->
        <div class="user" id="userMenu">

            <!-- AVATAR -->
            <div class="user-avatar">
                <?php if (!empty($avatar)): ?>
                    <img src="/rholance_pms/uploads/<?= htmlspecialchars($avatar) ?>" alt="Avatar">
                <?php else: ?>
                    <?= strtoupper(substr($userName, 0, 1)) ?>
                <?php endif; ?>
            </div>

            <!-- NAME + ROLE -->
            <div class="user-text-inline">
                <span class="user-name-inline">
                    <?= htmlspecialchars($userName) ?>
                </span>
                <span class="user-role-inline">
                    • <?= htmlspecialchars($userRole) ?>
                </span>
            </div>

            <!-- DROPDOWN -->
            <span class="dropdown-arrow">
                <i class="fa-solid fa-chevron-down"></i>
            </span>

            <div class="user-dropdown" id="userDropdown">

                <a href="/rholance_pms/profile/settings.php" class="dropdown-item">
                    <i class="fa-solid fa-user-gear"></i> Profile Settings
                </a>

                <a href="/rholance_pms/profile/avatar.php" class="dropdown-item">
                    <i class="fa-solid fa-image"></i> Upload Avatar
                </a>

                <div class="dropdown-divider"></div>

                <a href="/rholance_pms/auth/logout.php" class="dropdown-item logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>

            </div>

        </div>

    </div>
</div>

<script src="/rholance_pms/assets/js/darkmode.js"></script>
<script src="/rholance_pms/assets/js/search.js"></script>
<script src="/rholance_pms/assets/js/user-dropdown.js"></script>

</body>
</html>