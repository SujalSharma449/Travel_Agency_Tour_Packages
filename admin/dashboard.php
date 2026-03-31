<?php
session_start();
include("../config/db.php");

/* 🔐 Admin session check */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

/* 🔹 Admin name */
$adminName = $_SESSION['admin_name'] ?? "Admin";

/* 🔹 Dashboard statistics */
$totalRow    = $conn->query("SELECT COUNT(*) AS total FROM bookings")->fetch_assoc();
$pendingRow  = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE booking_status='Pending'")->fetch_assoc();
$approvedRow = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE booking_status='Approved'")->fetch_assoc();
$rejectedRow = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE booking_status='Rejected'")->fetch_assoc();
$cancelledRow = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE booking_status='Cancelled'")->fetch_assoc();

$total    = (int)($totalRow['total'] ?? 0);
$pending  = (int)($pendingRow['total'] ?? 0);
$approved = (int)($approvedRow['total'] ?? 0);
$rejected = (int)($rejectedRow['total'] ?? 0);
$cancelled = (int)($cancelledRow['total'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

<!-- 🔷 Header -->
<div class="header">
    Travel Agency Admin Panel
</div>

<!-- 🔷 Layout -->
<div class="container">

    <!-- 🔹 Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="bookings.php">Manage Bookings</a>
        <a href="packages.php">Manage Packages</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- 🔹 Main Content -->
    <div class="main">

        <h2>Welcome, <?= htmlspecialchars($adminName) ?> 👋</h2>
        <p style="color: gray;">Overview of booking activities</p><br>

        <!-- 🔷 Dashboard Cards -->
        <div class="cards">

            <div class="card">
                <h2><?= $total ?></h2>
                <p>Total Bookings</p>
            </div>

            <div class="card">
                <h2><?= $pending ?></h2>
                <p>Pending Bookings</p>
            </div>

            <div class="card">
                <h2><?= $approved ?></h2>
                <p>Approved Bookings</p>
            </div>

            <div class="card">
                <h2><?= $rejected ?></h2>
                <p>Rejected Bookings</p>
            </div>

            <div class="card">
                <h2><?= $cancelled ?></h2>
                <p>Cancelled Bookings (by user)</p>
            </div>

        </div>

    </div>
</div>

</body>
</html>