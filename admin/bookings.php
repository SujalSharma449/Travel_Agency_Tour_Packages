<?php
session_start();
include("../config/db.php");

/* 🔐 Admin session check */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

/* 🔹 Fetch ALL bookings (Custom + Recommended) */
$sql = "
    SELECT 
        b.booking_id,
        u.username,

        COALESCE(p.package_name, r.package_name) AS package_name,

        bd.persons,
        b.total_amount,
        b.booking_status,
        b.booking_date

    FROM bookings b

    JOIN booking_details bd 
        ON b.booking_id = bd.booking_id

    JOIN users u 
        ON b.user_id = u.user_id

    LEFT JOIN packages p 
        ON b.package_id = p.package_id

    LEFT JOIN recommended_packages r 
        ON b.rec_id = r.rec_id

    ORDER BY b.booking_id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

<div class="header">Manage Bookings</div>

<div class="container">

    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php" class="active">Manage Bookings</a>
        <a href="packages.php">Manage Packages</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">

        <h2>All Bookings</h2>
        <p style="color: gray;">Approve or reject user bookings</p><br>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Package</th>
                    <th>Persons</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Booking Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result && $result->num_rows > 0) { ?>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['booking_id']; ?></td>

                        <td><?= htmlspecialchars($row['username']); ?></td>

                        <td><?= htmlspecialchars($row['package_name']); ?></td>

                        <td><?= (int)$row['persons']; ?></td>

                        <td>₹<?= number_format($row['total_amount']); ?></td>

                        <!-- Status -->
                        <td>
                            <?php
                            if ($row['booking_status'] === 'Pending') {
                                echo "<span class='status pending'>Pending</span>";
                            } elseif ($row['booking_status'] === 'Approved') {
                                echo "<span class='status approved'>Approved</span>";
                            } elseif ($row['booking_status'] === 'Rejected') {
                                echo "<span class='status rejected'>Rejected</span>";
                            } else {
                                echo "<span class='status rejected'>Cancelled</span>";
                            }
                            ?>
                        </td>

                        <td><?= date("d M Y", strtotime($row['booking_date'])); ?></td>

                        <!-- Actions -->
                        <td>
                            <?php if ($row['booking_status'] === 'Pending') { ?>
                                <a class="btn approve"
                                   href="approve_booking.php?id=<?= $row['booking_id']; ?>"
                                   onclick="return confirm('Approve this booking?')">
                                   Approve
                                </a>

                                <a class="btn reject"
                                   href="reject_booking.php?id=<?= $row['booking_id']; ?>"
                                   onclick="return confirm('Reject this booking?')">
                                   Reject
                                </a>
                            <?php } else { ?>
                                —
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="8" style="text-align:center;">No bookings found</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>