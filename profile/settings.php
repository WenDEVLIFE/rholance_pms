<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$uid = $_SESSION['user_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $conn->real_escape_string($_POST['name']);
        if ($conn->query("UPDATE users SET name='$name' WHERE id=$uid")) {
            $_SESSION['name'] = $name;
            $msg = '<div class="alert alert-success">Profile name updated!</div>';
        }
    } elseif (isset($_POST['change_password'])) {
        $old = $_POST['old_pass'];
        $new = $_POST['new_pass'];
        $cfrm = $_POST['confirm_pass'];

        $res = $conn->query("SELECT password FROM users WHERE id=$uid");
        $user = $res->fetch_assoc();

        if (password_verify($old, $user['password'])) {
            if ($new === $cfrm) {
                $hashed = password_hash($new, PASSWORD_BCRYPT);
                if ($conn->query("UPDATE users SET password='$hashed' WHERE id=$uid")) {
                    $msg = '<div class="alert alert-success">Password changed successfully!</div>';
                }
            } else {
                $msg = '<div class="alert alert-danger">New passwords do not match.</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">Current password is incorrect.</div>';
        }
    }
}

$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
?>

<div class="rh-main">
    <div class="rh-page-header">
        <h1>Profile Settings</h1>
        <p>Manage your account details and security.</p>
    </div>

    <?= $msg ?>

    <div class="row g-4">
        <!-- GENERAL INFO -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3"><i class="fas fa-user-circle me-2 text-amber"></i>General Information</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-700">Display Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-700">Email Address</label>
                            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            <div class="form-text">Email cannot be changed. Contact admin for assistance.</div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-700">Role</label>
                                <input type="text" class="form-control bg-light small" value="<?= ucfirst($user['role']) ?>" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-700">Branch ID</label>
                                <input type="text" class="form-control bg-light small" value="<?= $user['branch_id'] == 1 ? 'Cavite' : 'Laguna' ?>" readonly>
                            </div>
                        </div>
                        <hr class="my-4 opacity-10">
                        <button type="submit" name="update_profile" class="btn btn-primary px-4 fw-700">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PASSWORD CHANGE -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3"><i class="fas fa-key me-2 text-amber"></i>Security</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-700">Current Password</label>
                            <input type="password" name="old_pass" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-700">New Password</label>
                            <input type="password" name="new_pass" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-700">Confirm New Password</label>
                            <input type="password" name="confirm_pass" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-dark px-4 fw-700">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body></html>