<?php
session_start();

if (isset($_POST['branch_id'])) {

    $branch = (int) $_POST['branch_id'];

    // SECURITY: only allow valid branches
    if (in_array($branch, [1,2])) {
        $_SESSION['branch_id'] = $branch;
    }
}

// Redirect back
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;