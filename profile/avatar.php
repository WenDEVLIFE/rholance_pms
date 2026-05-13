<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$uid = $_SESSION['user_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $targetDir = "../uploads/avatars/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileExt = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
    $newFileName = "avatar_" . $uid . "_" . time() . "." . $fileExt;
    $targetFile = $targetDir . $newFileName;

    // Validate image
    $check = getimagesize($_FILES["avatar"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
            $dbPath = "avatars/" . $newFileName;
            if ($conn->query("UPDATE users SET avatar='$dbPath' WHERE id=$uid")) {
                $msg = '<div class="alert alert-success">Avatar updated successfully!</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">Error uploading file.</div>';
        }
    } else {
        $msg = '<div class="alert alert-danger">File is not an image.</div>';
    }
}

$user = $conn->query("SELECT avatar, name FROM users WHERE id=$uid")->fetch_assoc();
?>

<div class="rh-main">
    <div class="rh-page-header">
        <h1>Profile Avatar</h1>
        <p>Upload a photo to personalize your account.</p>
    </div>

    <?= $msg ?>

    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mb-4">
                    <div class="rh-avatar mx-auto" style="width:120px;height:120px;font-size:3rem;">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
                        <?php else: ?>
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label fw-700">Select New Photo</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*" required>
                        <div class="form-text small">Recommended: Square image, max 2MB.</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2 fw-800">
                            <i class="fas fa-upload me-2"></i>Upload Now
                        </button>
                        <a href="settings.php" class="btn btn-link text-muted small">Back to Settings</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body></html>