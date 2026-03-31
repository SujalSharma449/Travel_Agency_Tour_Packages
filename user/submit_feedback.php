<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_email'])) {
    die("Unauthorized");
}



$email = $_SESSION['user_email'];

$userQ = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$userQ->bind_param("s", $email);
$userQ->execute();
$user = $userQ->get_result()->fetch_assoc();
$user_id = $user['user_id'];

$booking_id = intval($_POST['booking_id']);
$rating = intval($_POST['rating']);
$message = trim($_POST['message']);

$stmt = $conn->prepare("
    INSERT INTO feedback (booking_id, user_id, rating, message)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiis", $booking_id, $user_id, $rating, $message);
$stmt->execute();

header("Location: my_bookings.php");
exit;