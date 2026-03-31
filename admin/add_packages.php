<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$msg = "";

/* ======================
   ADD PACKAGE LOGIC
====================== */
if (isset($_POST['add_package'])) {

    $package_name = trim($_POST['package_name']);
    $description  = trim($_POST['description']);
    $price        = (int)$_POST['price'];
    $status       = "Active";

    /* IMAGE UPLOAD */
    $imageName = time() . "_" . basename($_FILES['image']['name']);
    $target    = "../uploads/" . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $msg = "Image upload failed!";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO packages
            (package_name, description, price, image, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssiss",
            $package_name,
            $description,
            $price,
            $imageName,
            $status
        );

        if ($stmt->execute()) {

            $package_id = $conn->insert_id;

            /* INSERT SELECTED PLACES */
            if (!empty($_POST['places'])) {
                foreach ($_POST['places'] as $place_id => $extra_price) {

                    $pp = $conn->prepare("
                        INSERT INTO package_places
                        (package_id, place_id, extra_price)
                        VALUES (?, ?, ?)
                    ");
                    $pp->bind_param("iii",
                        $package_id,
                        $place_id,
                        $extra_price
                    );
                    $pp->execute();
                }
            }

            header("Location: packages.php");
            exit;

        } else {
            $msg = "Database Error: " . $conn->error;
        }
    }
}

/* FETCH PLACES */
$places = $conn->query("SELECT place_id, place_name FROM places ORDER BY place_name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Package</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="header">Add New Package</div>

<div class="container">

    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php">Manage Bookings</a>
        <a href="packages.php" class="active">Manage Packages</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">

        <div class="ap-wrapper">

            <div class="ap-card">

                <h2 class="ap-title">Add New Travel Package</h2>

                <?php if ($msg != "") { ?>
                    <div class="ap-error">
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php } ?>

                <form method="POST" enctype="multipart/form-data" class="ap-form">

                    <!-- LEFT SIDE -->
                    <div class="ap-left">

                        <div class="ap-group">
                            <label>Package Name</label>
                            <input type="text" name="package_name" required>
                        </div>

                        <div class="ap-group">
                            <label>Description</label>
                            <textarea name="description" rows="4" required></textarea>
                        </div>

                        <div class="ap-group">
                            <label>Base Price (₹)</label>
                            <input type="number" name="price" required>
                        </div>

                        <div class="ap-group">
                            <label>Package Image</label>
                            <input type="file" name="image" required>
                        </div>

                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="ap-right">

                        <label class="ap-places-title">Select Places</label>

                        <div class="ap-places-box">
                            <?php while ($p = $places->fetch_assoc()) { ?>
                                <label class="ap-place-item">
                                    <input type="checkbox"
                                           name="places[<?= $p['place_id'] ?>]"
                                           value="1000">
                                    <?= htmlspecialchars($p['place_name']) ?> (+₹1000)
                                </label>
                            <?php } ?>
                        </div>

                    </div>

                    <!-- BUTTON -->
                    <div class="ap-button">
                        <button type="submit" name="add_package" class="ap-btn">
                            Add Package
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

</body>
</html>