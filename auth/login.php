<?php
session_start();
require_once __DIR__ . '/../config/database.php';

/* =========================
   ONLY ALLOW POST REQUEST
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /rholance_pms/index.php");
    exit;
}

/* =========================
   INPUT VALIDATION
========================= */
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = "Email and password are required.";
    header("Location: /rholance_pms/index.php");
    exit;
}

/* =========================
   FETCH USER FROM DATABASE
========================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");

if (!$stmt) {
    // DB prepare error
    $_SESSION['login_error'] = "System error. Please try again.";
    header("Location: /rholance_pms/index.php");
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

/* =========================
   CHECK IF USER EXISTS
========================= */
if ($user) {

    /* =========================
       EMAIL VERIFICATION CHECK
    ========================= */
    if ((int)$user['is_verified'] === 0) {
        $_SESSION['login_error'] = "Please verify your email first.";
        header("Location: /rholance_pms/index.php");
        exit;
    }

    /* =========================
       ACCOUNT STATUS CHECK
    ========================= */
    if (strtolower($user['status']) === 'blocked') {
        $_SESSION['blocked_user'] = true;
        header("Location: /rholance_pms/index.php");
        exit;
    }

    /* =========================
       PASSWORD VERIFICATION
    ========================= */
    if (password_verify($password, $user['password'])) {

        /* =========================
           SESSION SETUP
        ========================= */
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = strtolower($user['role']);
        $_SESSION['branch_id'] = $user['branch_id'];
        $_SESSION['name'] = $user['name']; 

        /* =========================
           ROLE-BASED REDIRECTION
        ========================= */
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: /rholance_pms/admin/dashboard.php");
                break;

            case 'staff':
                header("Location: /rholance_pms/staff/dashboard.php");
                break;

            case 'welder':
                header("Location: /rholance_pms/welder/dashboard.php");
                break;

            default:
                header("Location: /rholance_pms/customer/dashboard.php");
                break;
        }

        exit;
    }
}

/* =========================
   FAILED LOGIN (SAFE FALLBACK)
========================= */
$_SESSION['login_error'] = "Invalid email or password.";
header("Location: /rholance_pms/index.php");
exit;