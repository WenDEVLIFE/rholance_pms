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
    // If connection fails, show a clear message for the user
    die("Database Connection Error: " . $conn->connect_error . ". Please check your Hostinger MySQL settings.");
}

if (!$conn->connect_error) {
    $conn->set_charset("utf8mb4");
}

/* ── DYNAMIC BASE URL (FIXED FOR ALL PAGES) ── */
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $server_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Get the physical path of the project root
    $project_root_path = realpath(__DIR__ . '/..');
    $doc_root = realpath($_SERVER['DOCUMENT_ROOT']);
    
    // Calculate the web path by removing the document root from the project root path
    $web_path = str_replace($doc_root, '', $project_root_path);
    
    // Clean up slashes
    $web_path = str_replace('\\', '/', $web_path);
    $web_path = '/' . trim($web_path, '/') . '/';
    if ($web_path === '//') $web_path = '/';
    
    define('BASE_URL', $protocol . $server_host . $web_path);
}
?>