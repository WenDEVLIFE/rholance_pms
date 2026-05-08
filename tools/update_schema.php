<?php
include '../../config/database.php';

// Add columns if they don't exist
$conn->query("ALTER TABLE transactions ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'Cash'");
$conn->query("ALTER TABLE transactions ADD COLUMN IF NOT EXISTS payment_proof VARCHAR(255) DEFAULT NULL");
$conn->query("ALTER TABLE transactions ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(10,2) DEFAULT 0.00");

echo "Schema updated successfully (if columns were missing).";
?>
