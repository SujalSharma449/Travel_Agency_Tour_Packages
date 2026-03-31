<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

require_once("../config/db.php");

/* PHPMailer */
require "../phpmailer/PHPMailer.php";
require "../phpmailer/SMTP.php";
require "../phpmailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_POST['email'])) {
    die("Email required");
}

$email = trim($_POST['email']);

/* ================================
   Check if admin exists
   ================================ */
$stmt = $conn->prepare(
    "SELECT admin_id, username FROM admins WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    die("Admin not found");
}

$admin = $res->fetch_assoc();

/* ================================
   Generate OTP
   ================================ */
$otp = (string) rand(100000, 999999);
$otp_type = 'admin_login';

/* ================================
   Insert OTP into database
   ================================ */
$insertStmt = $conn->prepare("
    INSERT INTO otp_verification (email, otp_type, otp, expires_at)
    VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
");

$insertStmt->bind_param("sss", $email, $otp_type, $otp);

if (!$insertStmt->execute()) {
    die("Database Error: " . $insertStmt->error);
}

/* Store minimal session info */
$_SESSION['admin_email'] = $email;
$_SESSION['admin_username'] = $admin['username'];

/* ================================
   Send OTP Email
   ================================ */
try {

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "sharmasujal45718@gmail.com";
    $mail->Password = "qnqzojwxvlerpkpf"; // ⚠️ Change this later
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom("sharmasujal45718@gmail.com", "Travel Booking Admin");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Admin Login OTP";
    $mail->Body = "
        <h3>Hello {$admin['username']},</h3>
        <p>Your Admin Login OTP is:</p>
        <h2>$otp</h2>
        <p>This OTP is valid for 5 minutes.</p>
    ";

    $mail->send();

    header("Location: verify_admin_otp.php");
    exit;

} catch (Exception $e) {
    die("OTP could not be sent. Please try again.");
}