<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main">
    <h1>Settings</h1>

    <div class="card">
        <h3>Appearance</h3>

        <div class="setting-row">
            <div>
                <strong>Dark Mode</strong>
                <p class="setting-desc">
                    Enable dark theme for better visibility during extended use.
                </p>
            </div>

            <label class="switch">
                <input type="checkbox" id="darkModeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>
</div>

</body>
</html>
