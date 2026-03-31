<?php
session_start();
include("../config/db.php");

/* 🔐 Admin session check */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

/* Fetch packages */
$packages = $conn->query("SELECT * FROM packages ORDER BY package_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Packages</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

<!-- 🔷 Header -->
<div class="header">Manage Packages</div>

<!-- 🔷 Layout -->
<div class="container">

    <!-- 🔹 Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php">Manage Bookings</a>
        <a href="packages.php" class="active">Manage Packages</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- 🔹 Main Content -->
    <div class="main">

        <h2>Packages</h2>
        <p style="color: gray;">Add, update or remove tour packages</p><br>

        <a class="btn edit" href="add_packages.php">+ Add New Package</a>
        <br><br>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Package Name</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($packages->num_rows > 0) { ?>
                    <?php while ($p = $packages->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $p['package_id']; ?></td>
                            <td><?= htmlspecialchars($p['package_name']); ?></td>
                            <td>₹<?= number_format($p['price']); ?></td>
                            <td>
                                <a class="btn edit" 
                                   href="edit_packages.php?id=<?= $p['package_id']; ?>">
                                   Edit
                                </a>

                                <a class="btn delete" 
                                   href="delete_packages.php?id=<?= $p['package_id']; ?>"
                                   onclick="return confirm('Are you sure you want to delete this package?')">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No packages available</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>