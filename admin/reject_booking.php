<?php
session_start();
include("../config/db.php");
include("../includes/mailer.php");

/* 🔐 Admin Check */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$booking_id = intval($_GET['id']);

/* ===============================
   Fetch booking (Custom OR Recommended)
=============================== */
$infoQ = $conn->prepare("
    SELECT 
        u.email,
        u.username,
        p.package_name AS normal_package,
        r.package_name AS rec_package,
        b.total_amount,
        b.booking_status
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    LEFT JOIN packages p ON b.package_id = p.package_id
    LEFT JOIN recommended_packages r ON b.rec_id = r.rec_id
    WHERE b.booking_id = ?
");

$infoQ->bind_param("i", $booking_id);
$infoQ->execute();
$booking = $infoQ->get_result()->fetch_assoc();

if (!$booking) {
    die("Booking not found");
}

/* Stop if not Pending */
if ($booking['booking_status'] !== 'Pending') {
    header("Location: bookings.php");
    exit;
}

/* Decide package name safely */
$package = !empty($booking['normal_package']) 
            ? $booking['normal_package'] 
            : $booking['rec_package'];

$userEmail = $booking['email'];
$userName  = $booking['username'];
$amount    = number_format($booking['total_amount'], 2);

/* ===============================
   Handle rejection submission
=============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reason = trim($_POST['reason']);

    if ($reason === '') {
        $error = "Rejection reason is required.";
    } else {

        /* Update booking */
        $stmt = $conn->prepare("
            UPDATE bookings
            SET booking_status='Rejected', rejection_reason=?
            WHERE booking_id=? AND booking_status='Pending'
        ");
        $stmt->bind_param("si", $reason, $booking_id);
        $stmt->execute();

        /* Rejection Email */
        $message = "
        <html>
        <body style='font-family:Arial;background:#f4f6f9;padding:30px'>
        <table align='center' width='100%' style='max-width:600px;background:#ffffff;padding:25px;border-radius:10px'>
        <tr><td>

        <h2 style='color:#dc2626'>❌ Booking Rejected</h2>

        <p>Dear <b>$userName</b>,</p>

        <p>Your booking has been rejected by our team.</p>

        <p>
        <b>Package:</b> $package <br>
        <b>Amount:</b> ₹$amount
        </p>

        <p>
        <b>Reason:</b><br>
        $reason
        </p>

        <p>Your refund will be processed within <b>24 hours</b>.</p>

        <p style='margin-top:20px'>
        Regards,<br>
        <b>Travel Agency Team</b>
        </p>

        </td></tr>
        </table>
        </body>
        </html>
        ";

        sendMail($userEmail, "❌ Booking Rejected – Travel Agency", $message);

        header("Location: bookings.php?rejected=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reject Booking</title>

    <!-- ⚠️ Make sure this path matches your project structure -->
    <link rel="stylesheet" href="css/admin.css?v=3">
</head>

<body class="admin-page">

<div class="reject-wrapper">
    <div class="reject-card">

        <h2>Reject Booking</h2>
        <p class="reject-subtitle">Provide a reason for rejection</p>

        <?php if (!empty($error)) { ?>
            <p class="error-text"><?= htmlspecialchars($error) ?></p>
        <?php } ?>

        <form method="post">
            <textarea name="reason"
                      rows="5"
                      required
                      class="reject-textarea"
                      placeholder="Enter rejection reason..."></textarea>

            <div class="reject-actions">
                <button type="submit" class="reject-btn">
                    Reject Booking
                </button>

                <a href="bookings.php" class="cancel-btn">
                    Cancel
                </a>
            </div>
        </form>

    </div>
</div>

</body>
</html>