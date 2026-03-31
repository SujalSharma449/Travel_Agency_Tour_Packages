<?php
include("../config/db.php");

$id = intval($_GET['id']);

$q = $conn->query("
SELECT b.*, u.username, u.email, p.package_name
FROM bookings b
JOIN users u ON b.user_id=u.user_id
JOIN packages p ON b.package_id=p.package_id
WHERE b.booking_id=$id
");

$row = $q->fetch_assoc();
?>

<h2>Booking Details</h2>

<p>User: <?= $row['username'] ?> (<?= $row['email'] ?>)</p>
<p>Package: <?= $row['package_name'] ?></p>
<p>Persons: <?= $row['persons'] ?></p>
<p>Hotel: <?= $row['hotel_type'] ?></p>
<p>Rooms: <?= $row['room_qty'] ?></p>
<p>Places: <?= $row['selected_places'] ?></p>
<p>Total: ₹<?= $row['total_amount'] ?></p>
<p>Status: <?= $row['booking_status'] ?></p>