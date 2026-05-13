<?php
date_default_timezone_set('Asia/Manila');
mysqli_report(MYSQLI_REPORT_OFF);

/* ── PRODUCTION CREDENTIALS ── */
$host = "localhost";
$user = "u467106394_rholance";
$pass = "/dY11wbIoPg4";
$db   = "u467106394_rholance";

/* ── LOCALHOST (DEVELOPMENT) ── 
$host = "localhost";
$user = "root";
$pass = "innovatechph";
$db   = "rholance_pms";
*/

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
}

if (!$conn->connect_error) {
    $conn->set_charset("utf8mb4");
}

/* ── DYNAMIC BASE URL ── */
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $server_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Get the directory of the current script, then go up one level to reach project root
    // Since this file is in /config/database.php, we need to remove 'config/database.php' from the path
    $script_path = $_SERVER['SCRIPT_NAME'] ?? '';
    $project_root = str_replace('config/database.php', '', $script_path);
    
    // Ensure it ends with a single slash
    $project_root = rtrim($project_root, '/') . '/';
    
    define('BASE_URL', $protocol . $server_host . $project_root);
}
?>