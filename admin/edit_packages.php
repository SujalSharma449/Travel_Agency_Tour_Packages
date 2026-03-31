<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

/* ======================
   FETCH PACKAGE
====================== */
$package = $conn->query(
    "SELECT * FROM packages WHERE package_id = $id"
)->fetch_assoc();

if (!$package) {
    die("Package not found");
}

/* ======================
   FETCH ALL PLACES
====================== */
$places = $conn->query("SELECT * FROM places");

/* ======================
   FETCH SELECTED PLACES
====================== */
$selected = [];
$res = $conn->query(
    "SELECT place_id FROM package_places WHERE package_id = $id"
);
while ($r = $res->fetch_assoc()) {
    $selected[] = $r['place_id'];
}

/* ======================
   UPDATE PACKAGE
====================== */
if (isset($_POST['update_package'])) {

    $package_name = $_POST['package_name'];
    $description  = $_POST['description'];
    $price        = $_POST['price'];

    // Update image if provided
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $imageName);

        $conn->query(
            "UPDATE packages SET image='$imageName' WHERE package_id=$id"
        );
    }

    // Update package table
    $conn->query("
        UPDATE packages SET
            package_name='$package_name',
            description='$description',
            price='$price'
        WHERE package_id=$id
    ");

    /* ======================
       UPDATE PACKAGE PLACES
    ====================== */

    // Remove old relations
    $conn->query(
        "DELETE FROM package_places WHERE package_id = $id"
    );

    // Insert new relations
    if (!empty($_POST['places'])) {
        foreach ($_POST['places'] as $place_id => $extra_price) {

            $stmt = $conn->prepare("
                INSERT INTO package_places
                (package_id, place_id, extra_price)
                VALUES (?, ?, ?)
            ");

            $stmt->bind_param(
                "iii",
                $id,
                $place_id,
                $extra_price
            );

            $stmt->execute();
        }
    }

    header("Location: packages.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Package</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="header">Edit Package</div>

<div class="container">

    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php">Manage Bookings</a>
        <a href="packages.php">Manage Packages</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">

        <h2>Edit Package</h2>

        <form method="POST" enctype="multipart/form-data" class="form">

            <label>Package Name</label>
            <input type="text" name="package_name"
                   value="<?= htmlspecialchars($package['package_name']) ?>" required>

            <label>Description</label>
            <textarea name="description" required>
<?= htmlspecialchars($package['description']) ?>
            </textarea>

            <label>Price (₹)</label>
            <input type="number" name="price"
                   value="<?= $package['price'] ?>" required>

            <label>Places Included</label>
            <div style="margin-bottom:15px;">
                <?php while ($p = $places->fetch_assoc()) { ?>
                    <div style="margin-bottom:6px;">
                        <input type="checkbox"
                               name="places[<?= $p['place_id'] ?>]"
                               value="<?= $p['extra_price'] ?? 1000 ?>"
                               <?= in_array($p['place_id'], $selected) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($p['place_name']) ?>
                    </div>
                <?php } ?>
            </div>

            <label>Change Image (optional)</label>
            <input type="file" name="image">

            <button type="submit" name="update_package" class="btn edit">
                Update Package
            </button>

        </form>

    </div>
</div>

</body>
</html>