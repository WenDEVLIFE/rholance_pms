<?php
session_start();
include __DIR__ . '/../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!isset($conn)) {
    die("Database connection error.");
}

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);

$name = $user['name'];
$email = $user['email'];
$avatar = $user['avatar'] ?? '';
?>

<div class="profile-wrapper">

    <div class="profile-card">

        <!-- LEFT: AVATAR -->
        <div class="profile-left">
            <div class="profile-avatar-large">
                <?php if ($avatar): ?>
                    <img src="/rholance_pms/uploads/<?= $avatar ?>">
                <?php else: ?>
                    <?= strtoupper(substr($name, 0, 1)) ?>
                <?php endif; ?>
            </div>

            <h3><?= htmlspecialchars($name) ?></h3>
            <p><?= htmlspecialchars($email) ?></p>

            <a href="avatar.php" class="btn-outline">Change Avatar</a>
        </div>

        <!-- RIGHT: FORM -->
        <div class="profile-right">

            <h2>Profile Settings</h2>

            <form action="update_profile.php" method="POST">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" placeholder="Leave blank if unchanged">
                </div>

                <button class="btn-primary">Save Changes</button>

            </form>

        </div>

    </div>

</div>