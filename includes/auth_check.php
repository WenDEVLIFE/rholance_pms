<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check account status (blocked)
if (isset($conn) && !$conn->connect_error) {
    $result = mysqli_query($conn, "SELECT status, role FROM users WHERE id = '$user_id'");
    if ($result) {
        $user = mysqli_fetch_assoc($result);
        if ($user && $user['status'] === 'blocked') {
            ?>
            <div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:99999;font-family:sans-serif;">
                <div style="background:#fff;padding:30px;border-radius:12px;text-align:center;width:350px;">
                    <h2 style="color:#EF4444;margin-top:0;">Account Blocked</h2>
                    <p style="color:#64748B;">Your access has been restricted. Contact support.</p>
                    <a href="<?= BASE_URL ?>auth/logout.php" style="display:inline-block;padding:10px 20px;background:#0F172A;color:#fff;text-decoration:none;border-radius:6px;">Logout</a>
                </div>
            </div>
            <?php 
            exit; 
        }
    }
}
?>