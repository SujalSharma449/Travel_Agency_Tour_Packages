<?php
session_start();
include("../config/db.php");

/* ======================
   LOGIN CHECK
====================== */
if (!isset($_SESSION['user_email'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* ======================
   GET REC ID
====================== */
$rec_id = intval($_GET['id'] ?? 0);

if ($rec_id <= 0) {
    die("Invalid package ID");
}

/* ======================
   FETCH RECOMMENDED PACKAGE
====================== */
$stmt = $conn->prepare("SELECT * FROM recommended_packages WHERE rec_id=?");
$stmt->bind_param("i", $rec_id);
$stmt->execute();
$pkg = $stmt->get_result()->fetch_assoc();

if (!$pkg) {
    die("Package not found");
}

/* ======================
   FETCH PLACES USING RELATION TABLE
====================== */
$places_stmt = $conn->prepare("
    SELECT GROUP_CONCAT(pl.place_name SEPARATOR ', ') AS places
    FROM package_places pp
    JOIN places pl ON pp.place_id = pl.place_id
    WHERE pp.package_id = ?
");
$places_stmt->bind_param("i", $pkg['package_id']);
$places_stmt->execute();
$places_result = $places_stmt->get_result()->fetch_assoc();

$places = $places_result['places'] ?? '';

/* ======================
   STORE DATA IN SESSION
====================== */
$_SESSION['booking_data'] = [

    'rec_id'     => $pkg['rec_id'],
    'package_id' => null,

    'amount'     => (float)$pkg['total_price'],
    'persons'    => $pkg['persons'],
    'places'     => $places,   // ✅ Now places will store properly
    'nights'     => $pkg['total_nights'],
    'hotel_type' => $pkg['hotel_type'],
    'bed_type'   => $pkg['bed_type'],
    'room_qty'   => $pkg['room_qty'],

    'travel_start_date' => date("Y-m-d"),
    'duration_days'     => $pkg['total_days'],
    'travel_end_date'   => date("Y-m-d", strtotime("+".$pkg['total_days']." days"))
];

/* ======================
   REDIRECT TO PAYMENT
====================== */
header("Location: payment.php");
exit;
?>