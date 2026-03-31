<?php
session_start();
include("../config/db.php");

/* 🔐 USER LOGIN CHECK */
if (!isset($_SESSION['user_email'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* 🧾 VALIDATE BOOKING ID */
if (!isset($_GET['id'])) {
    die("Invalid request");
}

$booking_id = (int)$_GET['id'];
$email = $_SESSION['user_email'];

/* 🔎 VERIFY BOOKING BELONGS TO USER */
$sql = "
    SELECT b.booking_status 
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    WHERE b.booking_id = ? AND u.email = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $booking_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Unauthorized action");
}

$booking = $result->fetch_assoc();

/* ❌ ALLOW ONLY PENDING OR APPROVED */
if (!in_array($booking['booking_status'], ['Pending', 'Approved'])) {
    die("This booking cannot be cancelled");
}

/* 🔄 UPDATE STATUS */
$update = $conn->prepare(
    "UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ?"
);
$update->bind_param("i", $booking_id);
$update->execute();

/* ✅ REDIRECT WITH MESSAGE */
header("Location: my_bookings.php?cancelled=1");
exit;