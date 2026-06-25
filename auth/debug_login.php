<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Test the exact accounts
$test_emails = ['nicole.sanchez@rholance.com', 'dondon.cruz@rholance.com'];

foreach ($test_emails as $email) {
    $stmt = $conn->prepare("SELECT id, name, email, role, is_verified, status, LEFT(password,20) as pw_preview FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user) {
        // Get full password for verify test
        $stmt2 = $conn->prepare("SELECT password FROM users WHERE email = ? LIMIT 1");
        $stmt2->bind_param("s", $email);
        $stmt2->execute();
        $full = $stmt2->get_result()->fetch_assoc();
        $verified = password_verify('123456', $full['password']);
        
        echo "=== $email ===\n";
        echo "  Found: YES\n";
        echo "  Role: " . $user['role'] . "\n";
        echo "  is_verified: " . $user['is_verified'] . "\n";
        echo "  status: " . $user['status'] . "\n";
        echo "  pw_preview: " . $user['pw_preview'] . "\n";
        echo "  password_verify('123456'): " . ($verified ? "TRUE ✅" : "FALSE ❌") . "\n\n";
    } else {
        echo "=== $email ===\n  NOT FOUND in DB ❌\n\n";
    }
}
