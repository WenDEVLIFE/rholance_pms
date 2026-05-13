<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mailer.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
    $_SESSION['register_error'] = "All fields are required.";
    header("Location: " . BASE_URL . "index.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = "Invalid email format.";
    header("Location: " . BASE_URL . "index.php");
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['register_error'] = "Passwords do not match.";
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    $_SESSION['register_error'] = "Email already exists.";
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$code = strval(rand(100000, 999999));
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

$branch_id = $_SESSION['branch_id'] ?? 1;
$role = 'customer';

$stmt = $conn->prepare("
    INSERT INTO users 
    (name, email, password, role, branch_id, status, is_verified, verification_code, code_expiry)
    VALUES (?, ?, ?, ?, ?, 'active', 0, ?, ?)
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

/* ✅ CORRECT: 7 variables */
$stmt->bind_param(
    "ssssiss",
    $name,
    $email,
    $hashedPassword,
    $role,
    $branch_id,
    $code,
    $expiry
);

if (!$stmt->execute()) {
    die("EXECUTE ERROR: " . $stmt->error);
}

/* EMAIL */
sendVerificationEmail($email, $code);

$_SESSION['verify_email'] = $email;

header("Location: " . BASE_URL . "auth/verify.php");
exit;