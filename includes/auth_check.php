<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "
    SELECT status FROM users WHERE id = '$user_id'
");

if (!$result) {
    die("SQL Error: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);

if ($user && $user['status'] === 'blocked'):
?>

<div style="
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.6);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:99999;
">
    <div style="
        background:#fff;
        padding:30px;
        border-radius:12px;
        text-align:center;
        width:350px;
    ">
        <h2 style="color:red;">Account Blocked</h2>
        <p>Your account has been blocked.</p>
        <button onclick="window.location.href='/logout.php'">
            Logout
        </button>
    </div>
</div>

<?php exit; endif; ?>