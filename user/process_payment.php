<?php
session_start();
include("../config/db.php");

/* ======================
   VALIDATION
====================== */
if (
    !isset(
        $_SESSION['user_email'],
        $_SESSION['booking_data'],
        $_POST['payment_id']   // ✅ FIXED (POST instead of GET)
    )
) {
    die("Invalid payment response");
}

$user_email = $_SESSION['user_email'];
$razorpay_payment_id = $_POST['payment_id'];  // ✅ FIXED
$data = $_SESSION['booking_data'];

/* ======================
   EXTRACT SESSION DATA
====================== */
$package_id = !empty($data['package_id']) ? $data['package_id'] : NULL;
$rec_id     = !empty($data['rec_id']) ? $data['rec_id'] : NULL;

$amount     = (float)$data['amount'];
$persons    = $data['persons'] ?? 1;
$places     = $data['places'] ?? '';
$nights     = $data['nights'] ?? '';
$hotelType  = $data['hotel_type'] ?? '';
$bedType    = $data['bed_type'] ?? '';
$roomQty    = $data['room_qty'] ?? 1;
$startDate  = $data['travel_start_date'] ?? NULL;
$duration   = $data['duration_days'] ?? 0;
$endDate    = $data['travel_end_date'] ?? NULL;

/* ======================
   GET USER
====================== */
$stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found");
}

$user_id   = $user['user_id'];
$user_name = $user['username'];

/* ======================
   START TRANSACTION
====================== */
$conn->begin_transaction();

try {

    /* ======================
       INSERT INTO BOOKINGS
    ====================== */
    $book = $conn->prepare("
        INSERT INTO bookings
        (user_id, package_id, rec_id, total_amount, booking_status, booking_date)
        VALUES (?, ?, ?, ?, 'Pending', NOW())
    ");

    $book->bind_param("iiid", $user_id, $package_id, $rec_id, $amount);

    if (!$book->execute()) {
        throw new Exception("Booking insert failed: " . $book->error);
    }

    $booking_id = $book->insert_id;

    /* Trigger feedback popup */
    $_SESSION['show_feedback'] = true;
    $_SESSION['last_booking_id'] = $booking_id;

    /* ======================
       INSERT BOOKING DETAILS
    ====================== */
    $details = $conn->prepare("
        INSERT INTO booking_details
        (
            booking_id,
            persons,
            selected_places,
            nights,
            hotel_type,
            bed_type,
            room_qty,
            travel_start_date,
            duration_days,
            travel_end_date
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $details->bind_param(
        "iissssisis",
        $booking_id,
        $persons,
        $places,
        $nights,
        $hotelType,
        $bedType,
        $roomQty,
        $startDate,
        $duration,
        $endDate
    );

    if (!$details->execute()) {
        throw new Exception("Booking details insert failed: " . $details->error);
    }

    /* ======================
       INSERT PAYMENT
    ====================== */
    $pay = $conn->prepare("
        INSERT INTO payments
        (
            booking_id,
            razorpay_payment_id,
            amount,
            payment_status,
            payment_date
        )
        VALUES (?, ?, ?, 'Paid', NOW())
    ");

    $pay->bind_param("isd", $booking_id, $razorpay_payment_id, $amount);

    if (!$pay->execute()) {
        throw new Exception("Payment insert failed: " . $pay->error);
    }

    /* ======================
       COMMIT
    ====================== */
    $conn->commit();

    unset($_SESSION['booking_data']);

    header("Location: my_bookings.php?success=1");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Transaction failed: " . $e->getMessage());
}
?>