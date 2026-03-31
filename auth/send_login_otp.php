<?php
// ✅ Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



/* ===== PHPMailer includes ===== */
require __DIR__ . "/../phpmailer/PHPMailer.php";
require __DIR__ . "/../phpmailer/SMTP.php";
require __DIR__ . "/../phpmailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);

    // ✅ Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: login.php");
        exit;
    }

    // ✅ Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows !== 1) {
        $_SESSION['error'] = "Email not registered.";
        header("Location: login.php");
        exit;
    }

    // ✅ Generate NEW OTP
    $otp = (string) rand(100000, 999999);
    $otp_type = 'login';
    // ✅ Expiry time (5 minutes)
    // $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // ✅ Store in SESSION (optional but useful)
    $_SESSION['login_otp']   = $otp;
    $_SESSION['login_email'] = $email;

    // 🔥 INSERT OTP INTO DATABASE
    $insertStmt = $conn->prepare("
        INSERT INTO otp_verification (email, otp_type, otp, expires_at)
        VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
    ");
    $insertStmt->bind_param("sss", $email, $otp_type, $otp);

    if (!$insertStmt->execute()) {
        $_SESSION['error'] = "Database Error: " . $insertStmt->error;
        header("Location: login.php");
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sharmasujal45718@gmail.com';
        $mail->Password   = 'qnqzojwxvlerpkpf'; // ⚠️ Change this later for security
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('sharmasujal45718@gmail.com', 'Travel Booking');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Login OTP';
        $mail->Body    = "
            <h2>Login OTP</h2>
            <p>Your OTP for login is:</p>
            <h1>$otp</h1>
            <p>This OTP is valid for 5 minutes.</p>
        ";

        $mail->send();

        header("Location: verify_login_otp.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "OTP could not be sent. Please try again.";
        header("Location: login.php");
        exit;
    }
}