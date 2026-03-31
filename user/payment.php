<?php
session_start();
include("../config/db.php");

/* LOGIN CHECK */
if (!isset($_SESSION['user_email'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* STORE BOOKING DATA */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $_SESSION['booking_data'] = [
        'package_id'        => $_POST['package_id'] ?? null,
        'rec_id'            => $_POST['rec_id'] ?? null,
        'amount'            => (float)$_POST['total_amount'],
        'persons'           => (int)$_POST['persons'],
        'places'            => $_POST['places'] ?? '',
        'nights'            => $_POST['nights'] ?? '',
        'hotel_type'        => $_POST['hotel_type'] ?? '',
        'bed_type'          => $_POST['bed_type'] ?? '',
        'room_qty'          => (int)($_POST['room_qty'] ?? 1),
        'travel_start_date' => $_POST['travel_start_date'],
        'duration_days'     => (int)$_POST['duration_days'],
        'travel_end_date'   => $_POST['travel_end_date']
    ];
}

/* CHECK SESSION */
if (!isset($_SESSION['booking_data'])) {
    die("Invalid payment request");
}

$data = $_SESSION['booking_data'];
$amount = $data['amount'];
$amount_paise = round($amount * 100);

$key_id = "rzp_test_RyZOw6jHETDEqj";

/* FETCH PACKAGE NAME */
if (!empty($data['package_id'])) {
    $stmt = $conn->prepare("SELECT package_name FROM packages WHERE package_id=?");
    $stmt->bind_param("i", $data['package_id']);
} elseif (!empty($data['rec_id'])) {
    $stmt = $conn->prepare("SELECT package_name FROM recommended_packages WHERE rec_id=?");
    $stmt->bind_param("i", $data['rec_id']);
} else {
    die("Invalid package");
}

$stmt->execute();
$pkg = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>

<body style="background:#f3f4f6;font-family:sans-serif;">

<div style="max-width:550px;margin:120px auto;background:white;padding:40px;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.08);">

<h2>Complete Payment</h2>
<hr>

<p><b>Package:</b> <?= htmlspecialchars($pkg['package_name']) ?></p>
<p><b>Persons:</b> <?= $data['persons'] ?></p>

<p>
<b>Travel:</b><br>
<?= $data['travel_start_date'] ?>
 →
<?= $data['travel_end_date'] ?>
 (<?= $data['duration_days'] ?> days)
</p>

<div style="background:#ecfdf5;padding:20px;border-radius:12px;margin:25px 0;border-left:5px solid #10b981;">
<strong>Total Amount:</strong><br>
₹<?= number_format($amount,2) ?>
</div>

<button id="payBtn" style="
background:#10b981;
color:white;
padding:14px 35px;
border:none;
border-radius:10px;
font-size:16px;
font-weight:600;
cursor:pointer;
width:100%;
">
Pay Now
</button>

</div>

<script>
var options = {
    key: "<?= $key_id ?>",
    amount: "<?= $amount_paise ?>",
    currency: "INR",
    name: "Travel Booking",
    description: "Package Payment",

    handler: function (response) {

        if (!response.razorpay_payment_id) {
            alert("Payment failed");
            return;
        }

        // 🔥 SEND DATA USING POST
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "process_payment.php";

        var input = document.createElement("input");
        input.type = "hidden";
        input.name = "payment_id";
        input.value = response.razorpay_payment_id;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
};

document.getElementById("payBtn").onclick = function(e){
    var rzp = new Razorpay(options);
    rzp.open();
    e.preventDefault();
};
</script>

</body>
</html>