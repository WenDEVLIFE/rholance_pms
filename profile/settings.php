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
        $updateQuery = "UPDATE users SET name='$name'";
        
        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $targetDir = "../uploads/avatars/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileExt = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
            $newFileName = "avatar_" . $uid . "_" . time() . "." . $fileExt;
            $targetFile = $targetDir . $newFileName;

            // Validate image
            $check = getimagesize($_FILES["avatar"]["tmp_name"]);
            if($check !== false) {
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
                    $dbPath = "avatars/" . $newFileName;
                    $updateQuery .= ", avatar='$dbPath'";
                    $_SESSION['avatar'] = $dbPath;
                } else {
                    $msg .= '<div class="alert alert-danger">Error uploading image file.</div>';
                }
            } else {
                $msg .= '<div class="alert alert-danger">Selected file is not a valid image.</div>';
            }
        }
        
        $updateQuery .= " WHERE id=$uid";
        if (empty($msg)) {
            if ($conn->query($updateQuery)) {
                $_SESSION['name'] = $name;
                $msg = '<div class="alert alert-success">Profile updated successfully!</div>';
            } else {
                $msg = '<div class="alert alert-danger">Error updating database records.</div>';
            }
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
        <h1>Profile & Settings</h1>
        <p>Customize your personal avatar, basic details, and change your password all in one place.</p>
    </div>

    <?= $msg ?>

    <div class="row g-4">
        <!-- GENERAL INFO & AVATAR UPLOAD -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="fas fa-user-circle me-2 text-amber" style="font-size: 20px;"></i>
                    <h5 class="m-0 fw-700">Account Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        
                        <!-- CURRENT AVATAR VIEW -->
                        <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
                            <div class="rh-avatar" style="width:90px;height:90px;font-size:2.5rem;flex-shrink:0;">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-700">Profile Photo</h6>
                                <p class="text-muted small mb-2">Upload a square photo to personalize your profile picture across the application.</p>
                                <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>

                        <!-- BASIC FIELDS -->
                        <div class="mb-3">
                            <label class="form-label small fw-700">Display Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-700">Email Address</label>
                            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly style="cursor: not-allowed;">
                            <div class="form-text small text-muted">Email address cannot be changed directly. Please contact our support team.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-700">Account Role</label>
                                <input type="text" class="form-control bg-light small" value="<?= ucfirst($user['role']) ?>" readonly style="cursor: not-allowed;">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-700">Assigned Branch</label>
                                <input type="text" class="form-control bg-light small" value="<?= $user['branch_id'] == 1 ? 'Cavite Branch' : 'Laguna Branch' ?>" readonly style="cursor: not-allowed;">
                            </div>
                        </div>

                        <hr class="my-4" style="opacity: 0.1;">
                        <button type="submit" name="update_profile" class="btn btn-primary px-4 fw-700">
                            <i class="fas fa-check me-2"></i>Save Account Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- SECURITY CARD -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="fas fa-key me-2 text-amber" style="font-size: 18px;"></i>
                    <h5 class="m-0 fw-700">Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-700">Current Password</label>
                            <input type="password" name="old_pass" class="form-control" placeholder="Enter current password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-700">New Password</label>
                            <input type="password" name="new_pass" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-700">Confirm New Password</label>
                            <input type="password" name="confirm_pass" class="form-control" placeholder="Re-type new password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-dark w-100 fw-700 py-2">
                            <i class="fas fa-lock me-2"></i>Change Security Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>