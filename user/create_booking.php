<?php
session_start();
include("../config/db.php");

/* ================================
   LOGIN CHECK
================================ */
if (!isset($_SESSION['user_email'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* ================================
   VALIDATE REQUIRED FIELDS
================================ */
if (
    empty($_POST['package_id']) ||
    empty($_POST['total_amount']) ||
    empty($_POST['persons']) ||
    empty($_POST['nights']) ||
    empty($_POST['hotel_type']) ||
    empty($_POST['bed_type']) ||
    empty($_POST['room_qty']) ||
    empty($_POST['travel_start_date']) ||
    empty($_POST['duration_days']) ||
    empty($_POST['travel_end_date'])
) {
    die("Invalid booking request");
}

/* ================================
   GET USER
================================ */
$user_email = $_SESSION['user_email'];

$userQ = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$userQ->bind_param("s", $user_email);
$userQ->execute();
$user = $userQ->get_result()->fetch_assoc();

if (!$user) {
    die("User not found");
}

$user_id = $user['user_id'];

/* ================================
   SANITIZE VALUES
================================ */
$package_id       = intval($_POST['package_id']);
$total_amount     = intval($_POST['total_amount']);
$persons          = intval($_POST['persons']);
$selected_places  = $_POST['places'] ?? '';
$nights           = $_POST['nights'];
$hotel_type       = $_POST['hotel_type'];
$bed_type         = $_POST['bed_type'];
$room_qty         = intval($_POST['room_qty']);
$travel_start     = $_POST['travel_start_date'];
$duration_days    = intval($_POST['duration_days']);
$travel_end       = $_POST['travel_end_date'];

/* ================================
   INSERT INTO BOOKINGS TABLE
================================ */
$insertBooking = $conn->prepare("
    INSERT INTO bookings 
    (user_id, package_id, total_amount, booking_status, booking_date)
    VALUES (?, ?, ?, 'Pending', NOW())
");

$insertBooking->bind_param("iii", $user_id, $package_id, $total_amount);

if (!$insertBooking->execute()) {
    die("Booking failed: " . $conn->error);
}

$booking_id = $conn->insert_id;

/* ================================
   INSERT INTO BOOKING_DETAILS
================================ */
$insertDetails = $conn->prepare("
    INSERT INTO booking_details
    (booking_id, persons, selected_places, nights, hotel_type, bed_type, room_qty, travel_start_date, duration_days, travel_end_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insertDetails->bind_param(
    "iissssisss",
    $booking_id,
    $persons,
    $selected_places,
    $nights,
    $hotel_type,
    $bed_type,
    $room_qty,
    $travel_start,
    $duration_days,
    $travel_end
);

if (!$insertDetails->execute()) {
    die("Booking details failed: " . $conn->error);
}

/* ================================
   REDIRECT TO PAYMENT
================================ */
header("Location: payment.php?booking_id=$booking_id&amount=$total_amount");
exit;
?>