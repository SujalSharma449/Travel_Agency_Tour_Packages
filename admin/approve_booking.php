<?php
session_start();
include("../config/db.php");
include("../includes/mailer.php");

/* ===============================
   🔐 Admin Session Check
=============================== */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$booking_id = intval($_GET['id']);

/* ===============================
   1️⃣ Check Current Status First
=============================== */
$check = $conn->prepare("
    SELECT booking_status 
    FROM bookings 
    WHERE booking_id = ?
");
$check->bind_param("i", $booking_id);
$check->execute();
$current = $check->get_result()->fetch_assoc();

if (!$current) {
    die("Booking not found");
}

/* 🚨 Stop if already processed */
if ($current['booking_status'] !== 'Pending') {
    header("Location: bookings.php");
    exit;
}

/* ===============================
   2️⃣ Update Only If Pending
=============================== */
$update = $conn->prepare("
    UPDATE bookings 
    SET booking_status='Approved' 
    WHERE booking_id=? AND booking_status='Pending'
");
$update->bind_param("i", $booking_id);
$update->execute();

/* ===============================
   3️⃣ Fetch Booking (Custom OR Recommended)
=============================== */
$sql = "
SELECT 
    u.email,
    u.username,

    p.package_name AS normal_package,
    r.package_name AS rec_package,

    b.total_amount,
    d.travel_start_date,
    d.travel_end_date

FROM bookings b
JOIN users u ON b.user_id = u.user_id

LEFT JOIN packages p 
    ON b.package_id = p.package_id

LEFT JOIN recommended_packages r 
    ON b.rec_id = r.rec_id

LEFT JOIN booking_details d 
    ON b.booking_id = d.booking_id

WHERE b.booking_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: bookings.php");
    exit;
}

/* Decide Package Name */
$package = $data['normal_package'] ?? $data['rec_package'];

$username = $data['username'];
$email    = $data['email'];
$amount   = $data['total_amount'];
$start    = $data['travel_start_date'];
$end      = $data['travel_end_date'];

/* ===============================
   4️⃣ Generate Invoice Link
=============================== */
$invoiceLink = "http://192.168.29.11/Travel_Agency_Tour_Packages/user/invoice.php?id=".$booking_id;

/* ===============================
   5️⃣ Send Approval Email
=============================== */
$message = "
<html>
<body style='background:#0f172a;color:white;font-family:Arial;padding:30px;'>
<table width='100%' align='center'>
<tr><td align='center'>
<table style='max-width:600px;background:#111827;padding:30px;border-radius:12px;'>
<tr><td>

<h2 style='color:#22c55e;'>🎉 Booking Approved</h2>

<p>Dear <b>$username</b>,</p>

<p>Your booking has been approved successfully.</p>

<p>
<b>Package:</b> $package <br>
<b>Travel Dates:</b> $start → $end <br>
<b>Total Paid:</b> ₹".number_format($amount,2)."
</p>

<p style='margin:25px 0; text-align:center;'>
<a href='$invoiceLink'
   style='display:inline-block;padding:14px 28px;background:#16a34a;color:white;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;'>
   📄 Download Invoice
</a>
</p>

<p>Thank you for booking with us.</p>
<p><b>Travel Agency Team</b></p>

</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
";

sendMail($email, "🎉 Booking Approved – Travel Agency", $message);

/* ===============================
   6️⃣ Redirect
=============================== */
header("Location: bookings.php");
exit;