<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/SMTP.php';
require_once __DIR__ . '/../phpmailer/Exception.php';

function sendMail($to, $subject, $message)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP CONFIG
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sharmasujal45718@gmail.com';   // your email
        $mail->Password   = 'qnqzojwxvlerpkpf';            // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // EMAIL SETTINGS
        $mail->setFrom('sharmasujal45718@gmail.com', 'Travel Agency');
        $mail->addAddress($to);

        $mail->isHTML(true);              // VERY IMPORTANT
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;

        // ❌ DO NOT use nl2br here
        $mail->Body = $message;

        // Optional fallback for non-HTML clients
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}