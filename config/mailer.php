<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
require __DIR__ . '/../PHPMailer/src/LoggerInterface.php';

/* =========================
   BASE MAIL FUNCTION
========================= */
function baseMailer() {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    $mail->Username   = 'rholancetrading2005@gmail.com';
    $mail->Password   = 'wfoemrufepgjfgqh';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->SMTPDebug = 0; // FIXED

    $mail->setFrom('rholancetrading2005@gmail.com', 'Rholance Trading');

    return $mail;
}

/* =========================
   RESET EMAIL
========================= */
function sendResetEmail($toEmail, $resetLink) {
    try {
        $mail = baseMailer();

        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password';

        $mail->Body = "
            <div style='font-family: Arial;'>
                <h2>Rholance Trading</h2>
                <p>Click the button below to reset your password:</p>

                <a href='$resetLink' style='
                    display:inline-block;
                    padding:10px 20px;
                    background:#f59e0b;
                    color:#fff;
                    text-decoration:none;
                    border-radius:5px;
                '>Reset Password</a>

                <p style='margin-top:10px;'>This link expires in 15 minutes.</p>
            </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo); // FIXED
        return false;
    }
}

/* =========================
   VERIFICATION EMAIL
========================= */
function sendVerificationEmail($toEmail, $code) {
    try {
        $mail = baseMailer();

        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';

        $mail->Body = "
            <div style='font-family: Arial;'>
                <h2>Rholance Trading</h2>
                <p>Your verification code is:</p>

                <h1 style='letter-spacing:5px;'>$code</h1>

                <p>This code will expire in 5 minutes.</p>
            </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo); // FIXED
        return false;
    }
}