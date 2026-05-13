<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SESSION['role'] !== 'admin') { exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name     = $conn->real_escape_string($_POST['name']);
        $email    = $conn->real_escape_string($_POST['email']);
        $role     = $conn->real_escape_string($_POST['role']);
        $branch   = (int)$_POST['branch_id'];
        $pass     = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (name, email, password, role, branch_id, status, is_verified) 
                VALUES ('$name', '$email', '$pass', '$role', $branch, 'active', 1)";
        
        if ($conn->query($sql)) {
            header("Location: user_management.php?msg=User created successfully");
        } else {
            header("Location: user_management.php?err=Error creating user");
        }
    } 
    elseif ($action === 'update') {
        $uid      = (int)$_POST['user_id'];
        $name     = $conn->real_escape_string($_POST['name']);
        $email    = $conn->real_escape_string($_POST['email']);
        $role     = $conn->real_escape_string($_POST['role']);
        $branch   = (int)$_POST['branch_id'];
        
        $sql = "UPDATE users SET name='$name', email='$email', role='$role', branch_id=$branch WHERE id=$uid";
        
        // Optional password update
        if (!empty($_POST['password'])) {
            $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $sql = "UPDATE users SET name='$name', email='$email', role='$role', branch_id=$branch, password='$pass' WHERE id=$uid";
        }

        if ($conn->query($sql)) {
            header("Location: user_management.php?msg=User updated successfully");
        } else {
            header("Location: user_management.php?err=Error updating user");
        }
    }
}
?>
