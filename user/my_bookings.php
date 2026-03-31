<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user_email'])) {
    header("Location: ../auth/login.php");
    exit;
}

$email = $_SESSION['user_email'];

/* ================= USER ================= */
$userQ = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$userQ->bind_param("s", $email);
$userQ->execute();
$user = $userQ->get_result()->fetch_assoc();

if (!$user) {
    die("User not found");
}

$user_id = $user['user_id'];

/* ================= FILTER ================= */
$statusFilter = $_GET['status'] ?? '';

$sql = "
SELECT
    b.booking_id,
    b.total_amount,
    b.booking_status,
    b.booking_date,

    COALESCE(p.package_name, r.package_name) AS package_name,
    COALESCE(p.image, r.image) AS package_image,

    d.travel_start_date,
    d.travel_end_date,
    d.duration_days,

    pay.razorpay_payment_id

FROM bookings b
LEFT JOIN packages p ON b.package_id = p.package_id
LEFT JOIN recommended_packages r ON b.rec_id = r.rec_id
LEFT JOIN booking_details d ON b.booking_id = d.booking_id
LEFT JOIN payments pay ON b.booking_id = pay.booking_id
WHERE b.user_id = ?
";

if (!empty($statusFilter)) {
    $sql .= " AND b.booking_status = ?";
}

$sql .= " ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);

if (!empty($statusFilter)) {
    $stmt->bind_param("is", $user_id, $statusFilter);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="bookings-wrapper">

<h2 class="bookings-title">My Bookings</h2>

<div class="bookings-filter">
<form method="get">
    <select name="status" class="filter-select">
        <option value="">All</option>
        <option value="Pending" <?= $statusFilter=='Pending'?'selected':'' ?>>Pending</option>
        <option value="Approved" <?= $statusFilter=='Approved'?'selected':'' ?>>Approved</option>
        <option value="Rejected" <?= $statusFilter=='Rejected'?'selected':'' ?>>Rejected</option>
        <option value="Cancelled" <?= $statusFilter=='Cancelled'?'selected':'' ?>>Cancelled</option>
    </select>
    <button type="submit" class="filter-btn">Filter</button>
</form>
</div>

<?php if ($result->num_rows == 0) { ?>
<p style="text-align:center;color:#777;">No bookings found</p>
<?php } else { ?>

<div class="bookings-grid">

<?php while ($row = $result->fetch_assoc()) {

    $packageName = $row['package_name'];
    $image = $row['package_image'];

    if (empty($image)) {
        $image = "default.jpg";
    }
?>

<div class="booking-card">
    <img src="../uploads/<?= htmlspecialchars($image) ?>" alt="">

    <div class="booking-body">
        <h3><?= htmlspecialchars($packageName) ?></h3>

        <p><b>Total:</b> ₹<?= number_format($row['total_amount'],2) ?></p>

        <p>
            <b>Travel:</b><br>
            <?= date("d M Y", strtotime($row['travel_start_date'])) ?> →
            <?= date("d M Y", strtotime($row['travel_end_date'])) ?><br>
            <?= $row['duration_days'] ?> days
        </p>

        <p><b>Booked:</b> <?= date("d M Y, h:i A", strtotime($row['booking_date'])) ?></p>

        <span class="booking-status status-<?= strtolower($row['booking_status']) ?>">
            <?= $row['booking_status'] ?>
        </span>

        <br><br>

        <a class="booking-btn invoice"
           href="invoice.php?id=<?= $row['booking_id'] ?>">
           Invoice
        </a>

        <?php if (!empty($row['razorpay_payment_id'])) { ?>
            <p><b>Payment ID:</b> <?= htmlspecialchars($row['razorpay_payment_id']) ?></p>
        <?php } ?>
    </div>
</div>

<?php } ?>
</div>
<?php } ?>
</div>


<!-- ================= FEEDBACK MODAL ================= -->
<?php if(isset($_SESSION['show_feedback']) && $_SESSION['show_feedback'] == true): ?>
<div class="feedback-overlay">
    <div class="feedback-modal">

        <h3>Rate Your Experience</h3>

        <form action="submit_feedback.php" method="POST">
            <input type="hidden" name="booking_id" value="<?= $_SESSION['last_booking_id'] ?>">
            <input type="hidden" name="rating" id="ratingValue" required>

            <div class="star-rating">
                <span class="star" data-value="1">&#9733;</span>
                <span class="star" data-value="2">&#9733;</span>
                <span class="star" data-value="3">&#9733;</span>
                <span class="star" data-value="4">&#9733;</span>
                <span class="star" data-value="5">&#9733;</span>
            </div>

            <textarea name="message" placeholder="Write your feedback..." required></textarea>

            <button type="submit" class="feedback-btn">Submit Feedback</button>
        </form>
    </div>
</div>

<style>
.feedback-overlay{
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:9999;
}
.feedback-modal{
    background:white;
    padding:30px;
    border-radius:12px;
    width:400px;
    text-align:center;
}
.star{ cursor:pointer; font-size:30px; color:#ccc; }
.star.active{ color:gold; }
</style>

<script>
const stars = document.querySelectorAll(".star");
const ratingInput = document.getElementById("ratingValue");

stars.forEach(star => {
    star.addEventListener("click", function () {
        let value = this.getAttribute("data-value");
        ratingInput.value = value;

        stars.forEach(s => s.classList.remove("active"));
        for (let i = 0; i < value; i++) {
            stars[i].classList.add("active");
        }
    });
});
</script>

<?php unset($_SESSION['show_feedback']); endif; ?>

<?php include("../includes/footer.php"); ?>