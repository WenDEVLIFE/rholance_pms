<?php
require_once "../config/database.php";
require_once "../config/mailer.php";

$message = "";
$message_type = ""; // success or error

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ✅ SANITIZE INPUT
    $email = trim($_POST['email']);

    if (!empty($email)) {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // ✅ GENERIC RESPONSE (SECURITY BEST PRACTICE)
        if ($result->num_rows > 0) {

            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

            $stmt = $conn->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE email=?");
            $stmt->bind_param("sss", $token, $expiry, $email);
            $stmt->execute();

            $resetLink = "http://localhost/rholance_pms/auth/reset_password.php?token=" . $token;

            sendResetEmail($email, $resetLink);
        }

        // ✅ ALWAYS SAME MESSAGE (NO EMAIL ENUMERATION)
        $message = "a reset link has been sent.";
        $message_type = "success";
    } else {
        $message = "Please enter a valid email.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password | Rholance PMS</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- ✅ FONT AWESOME (FIX ICON ISSUE) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="login-body">

<div class="login-container">
    <div class="login-modal-content">

        <!-- HEADER -->
        <div class="login-header">
            <img src="../assets/images/logoo.png" alt="Rholance Logo">
            <h2>Forgot Password</h2>
            <p>Enter your email to receive a reset link</p>
        </div>

        <!-- MESSAGE -->
        <?php if (!empty($message)): ?>
            <div class="<?= $message_type === 'success' ? 'login-success' : 'login-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form method="POST" action="" id="forgotForm">

            <form method="POST" action="">

    <div class="form-group">
      

        <div class="fp-input-wrapper">
            <i class="fa-solid fa-envelope fp-icon"></i>

            <input 
                type="email" 
                name="email" 
                placeholder="Enter your email"
                required
            >
        </div>
    </div>

    <button type="submit" class="login-btn">
        Send Reset Link
    </button>

</form>
        <!-- BACK -->
        <div class="login-footer">
            Remember your password?
            <a href="login.php" class="register-link">Back to Login</a>
        </div>

    </div>
</div>

<!-- ✅ OPTIONAL UX IMPROVEMENT (LOADING BUTTON) -->
<script>
document.getElementById("forgotForm").addEventListener("submit", function() {
    const btn = document.getElementById("submitBtn");
    btn.innerText = "Sending...";
    btn.disabled = true;
});
</script>

</body>
</html>