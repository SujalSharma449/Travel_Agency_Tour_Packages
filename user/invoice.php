<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_email'])) {
    die("Unauthorized access");
}

if (!isset($_GET['id'])) {
    die("Invoice not found");
}

$booking_id = intval($_GET['id']);

$sql = "
SELECT 
    b.booking_id,
    b.total_amount,
    b.booking_status,
    b.booking_date,
    b.rejection_reason,

    u.username,
    u.email,

    COALESCE(p.package_name, r.package_name) AS package_name,

    d.persons,
    d.selected_places,
    d.hotel_type,
    d.bed_type,
    d.room_qty,
    d.travel_start_date,
    d.travel_end_date,
    d.duration_days,

    pay.razorpay_payment_id

FROM bookings b
JOIN users u ON b.user_id = u.user_id

LEFT JOIN packages p ON b.package_id = p.package_id
LEFT JOIN recommended_packages r ON b.rec_id = r.rec_id

LEFT JOIN booking_details d ON b.booking_id = d.booking_id
LEFT JOIN payments pay ON b.booking_id = pay.booking_id

WHERE b.booking_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invoice not found");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Invoice</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="invoice-body">

<div class="invoice-container">

<h2>Booking Invoice</h2>
<hr>

<h3>Customer Details</h3>
<p><b>Name:</b> <?= htmlspecialchars($row['username']) ?></p>
<p><b>Email:</b> <?= htmlspecialchars($row['email']) ?></p>

<hr>

<h3>Booking Details</h3>

<table class="invoice-table">

<tr><td><b>Booking ID</b></td><td><?= $row['booking_id'] ?></td></tr>
<tr><td><b>Package</b></td><td><?= htmlspecialchars($row['package_name']) ?></td></tr>

<tr>
<td><b>Travel Dates</b></td>
<td>
<?= date("d M Y", strtotime($row['travel_start_date'])) ?> →
<?= date("d M Y", strtotime($row['travel_end_date'])) ?>
(<?= $row['duration_days'] ?> days)
</td>
</tr>

<tr><td><b>Persons</b></td><td><?= $row['persons'] ?? 'N/A' ?></td></tr>
<tr><td><b>Hotel Type</b></td><td><?= htmlspecialchars($row['hotel_type'] ?? 'N/A') ?></td></tr>
<tr><td><b>Bed Type</b></td><td><?= htmlspecialchars($row['bed_type'] ?? 'N/A') ?></td></tr>
<tr><td><b>Rooms</b></td><td><?= $row['room_qty'] ?? 'N/A' ?></td></tr>
<tr><td><b>Places</b></td><td><?= htmlspecialchars($row['selected_places'] ?? 'N/A') ?></td></tr>

<tr>
<td><b>Total Amount</b></td>
<td><b>₹<?= number_format($row['total_amount'],2) ?></b></td>
</tr>

<tr>
<td><b>Payment ID</b></td>
<td><?= htmlspecialchars($row['razorpay_payment_id'] ?? 'Not Available') ?></td>
</tr>

<tr>
<td><b>Status</b></td>
<td>
<span class="invoice-status-<?= strtolower($row['booking_status']) ?>">
<?= $row['booking_status'] ?>
</span>
</td>
</tr>

<tr>
<td><b>Booking Date</b></td>
<td><?= date("d M Y, h:i A", strtotime($row['booking_date'])) ?></td>
</tr>

</table>

<div style="text-align:center;margin-top:25px;">
<button onclick="window.print()" class="invoice-btn">
Download Invoice
</button>
</div>

</div>

</body>
</html>