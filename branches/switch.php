<?php
session_start();

if (isset($_POST['branch_id'])) {

    $branch = (int) $_POST['branch_id'];

    if (in_array($branch, [1,2])) {
        $_SESSION['branch_id'] = $branch;
    }
}

/* Fallback if no referrer */
$redirect = $_SERVER['HTTP_REFERER'] ?? '/rholance_pms/admin/dashboard.php';

header("Location: " . $redirect);
exit;