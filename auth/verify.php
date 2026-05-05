<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['verify_email'])) {
    header("Location: /rholance_pms/index.php");
    exit;
}

$email = $_SESSION['verify_email'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $code = trim($_POST['code']);

    if ($code === '') {
        $message = "Please enter the verification code.";
    } else {

        $stmt = $conn->prepare("
            SELECT verification_code, code_expiry 
            FROM users 
            WHERE email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            $message = "User not found.";
        } else {

            // ❗ CHECK CODE MATCH
            if ($code !== $result['verification_code']) {
                $message = "Invalid verification code.";
            }

            // ❗ CHECK EXPIRY
            elseif (strtotime($result['code_expiry']) < time()) {
                $message = "Code expired. Please request a new one.";
            }

            else {
                // ✅ SUCCESS → VERIFY USER
                $update = $conn->prepare("
                    UPDATE users 
                    SET is_verified = 1,
                        verification_code = NULL,
                        code_expiry = NULL
                    WHERE email = ?
                ");
                $update->bind_param("s", $email);
                $update->execute();

                unset($_SESSION['verify_email']);

                $_SESSION['login_success'] = "Email verified successfully!";
                header("Location: /rholance_pms/index.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Email</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body">

<div class="login-container">
    <div class="login-modal-content">

        <h2>Verify Your Email</h2>
        <p>Enter the 6-digit code sent to your email</p>

        <?php if (!empty($message)): ?>
            <div class="login-error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="code" maxlength="6" placeholder="Enter code" required>
            <button type="submit" class="login-btn">Verify</button>
        </form>

        <div class="login-footer">
            Didn’t receive code?
            <a href="resend_code.php">Resend</a>
        </div>

    </div>
</div>

</body>
</html>