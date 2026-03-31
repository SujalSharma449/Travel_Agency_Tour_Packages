<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

require_once("../config/db.php");

/* ================================
   PHPMailer includes
   ================================ */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../phpmailer/PHPMailer.php";
require_once __DIR__ . "/../phpmailer/SMTP.php";
require_once __DIR__ . "/../phpmailer/Exception.php";

/* ================================
   Handle POST request
   ================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);

    // 🔴 Validation
    if (empty($name) || empty($email)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: register.php");
        exit;
    }

    /* ================================
       STEP 1: Check if email exists
       ================================ */
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered. Please login.";
        header("Location: register.php");
        exit;
    }

    /* ================================
       STEP 2: Generate OTP
       ================================ */
    $otp = (string) rand(100000, 999999);
    $otp_type = 'register';

    // 🔥 Insert OTP into database (5 min expiry)
    $insertStmt = $conn->prepare("
        INSERT INTO otp_verification (email, otp_type, otp, expires_at)
        VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
    ");

    $insertStmt->bind_param("sss", $email, $otp_type, $otp);

    if (!$insertStmt->execute()) {
        $_SESSION['error'] = "Database Error: " . $insertStmt->error;
        header("Location: register.php");
        exit;
    }

    // Store name & email in session (temporary)
    $_SESSION['register_name']  = $name;
    $_SESSION['register_email'] = $email;

    /* ================================
       STEP 3: Send OTP Email
       ================================ */
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sharmasujal45718@gmail.com'; 
        $mail->Password   = 'qnqzojwxvlerpkpf'; // ⚠️ change later
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('sharmasujal45718@gmail.com', 'Travel Booking');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Your Registration OTP';
        $mail->Body    = "
            <h2>Hello $name</h2>
            <p>Your OTP for registration is:</p>
            <h1>$otp</h1>
            <p>This OTP is valid for 5 minutes.</p>
        ";

        $mail->send();

        header("Location: verify_register_otp.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "OTP could not be sent. Please try again.";
        header("Location: register.php");
        exit;
    }
}