<?php
require_once "../config/database.php";

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("
    SELECT id, token_expiry 
    FROM users 
    WHERE reset_token=? 
    AND token_expiry > NOW()
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid token.");
}

$user = $result->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expiry=NULL WHERE id=?");
    $stmt->bind_param("si", $newPassword, $user['id']);
    $stmt->execute();

    echo "Password updated successfully. Redirecting to login...";

    header("Location: ../index.php?login=success");
exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Rholance PMS</title>

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/reset-password.css">
</head>
<body>

<div class="reset-container">

    <div class="reset-card">

        <!-- LOGO -->
        <div class="reset-header">
            <img src="../assets/images/logoo.png" alt="Logo">
            <h2>Reset Password</h2>
            <p>Enter your new password below</p>
        </div>

        <!-- FORM -->
        <form method="POST">

            <div class="form-group">
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        placeholder="New Password" 
                        required
                    >
                    <i class="fa-solid fa-eye-slash eye-icon" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" class="reset-btn">Update Password</button>

        </form>

    </div>

</div>

<!-- JS -->
<script>
const toggle = document.getElementById("togglePassword");
const password = document.getElementById("password");

toggle.addEventListener("click", () => {
    const type = password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);
    toggle.classList.toggle("fa-eye");
    toggle.classList.toggle("fa-eye-slash");
});
</script>

</body>
</html>