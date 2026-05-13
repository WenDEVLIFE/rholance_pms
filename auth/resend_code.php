<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mailer.php'; // ✅ THIS WAS MISSING

if (!isset($_SESSION['verify_email'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$email = $_SESSION['verify_email'];

/* ================= GENERATE NEW CODE ================= */
$code = strval(rand(100000, 999999));
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

$stmt = $conn->prepare("
    UPDATE users 
    SET verification_code = ?, code_expiry = ?
    WHERE email = ?
");

$stmt->bind_param("sss", $code, $expiry, $email);

if (!$stmt->execute()) {
    die("ERROR: " . $stmt->error);
}

/* ================= SEND EMAIL ================= */
sendVerificationEmail($email, $code); // ✅ NOW WORKS

/* ================= REDIRECT ================= */
header("Location: verify.php");
exit;