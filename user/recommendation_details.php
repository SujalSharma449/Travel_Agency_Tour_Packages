<?php
include("../config/db.php");
include("../includes/header.php");

$id = intval($_GET['id'] ?? 0);

/* STEP 1: Get recommended package */
$stmt = $conn->prepare("SELECT * FROM recommended_packages WHERE rec_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pkg = $result->fetch_assoc();

if(!$pkg){
    echo "<h3 style='text-align:center; margin-top:100px;'>Package not found</h3>";
    include("../includes/footer.php");
    exit;
}

/* STEP 2: Get package_id */
$package_id = $pkg['package_id'];

/* STEP 3: Fetch places using package_id */
$placeStmt = $conn->prepare("
    SELECT place_id, place_name 
    FROM places 
    WHERE package_id = ?
");
$placeStmt->bind_param("i", $package_id);
$placeStmt->execute();
$placesResult = $placeStmt->get_result();
?>

<style>
.places-list{
    list-style:none;
    padding:0;
    margin-top:15px;
}

.places-list li{
    background:#f8f9fa;
    padding:10px 15px;
    margin-bottom:8px;
    border-radius:8px;
    border-left:4px solid #2c6ed5;
    font-size:15px;
}

.places-list li a{
    text-decoration:none;
    color:#007bff;
    font-weight:500;
}

.places-list li a:hover{
    text-decoration:underline;
}
</style>

<div style="max-width:1100px; margin:120px auto 60px; padding:20px;">

    <!-- Main Image -->
    <img src="../uploads/recommended/<?= htmlspecialchars($pkg['image']) ?>"
         style="width:100%; height:450px; object-fit:cover; border-radius:15px;">

    <div style="margin-top:30px;">

        <h2 style="font-size:32px; margin-bottom:10px;">
            <?= htmlspecialchars($pkg['package_name']) ?>
        </h2>

        <p style="color:#555; font-size:16px;">
            <?= htmlspecialchars($pkg['description']) ?>
        </p>

        <hr style="margin:25px 0;">

        <!-- PLACES SECTION -->
        <h3>🗺 Places Included</h3>

        <?php if($placesResult->num_rows > 0){ ?>

        <ul class="places-list">

            <?php while($place = $placesResult->fetch_assoc()){ ?>

            <li>
                📍 
                <a href="place_details.php?id=<?= $place['place_id'] ?>&pkg=<?= $pkg['rec_id'] ?>&type=recommended">
                    <?= htmlspecialchars($place['place_name']) ?>
                </a>
            </li>

            <?php } ?>

        </ul>

        <?php } else { ?>

            <p style="color:#888;">No places available for this package.</p>

        <?php } ?>

        <hr style="margin:25px 0;">

        <!-- PACKAGE DETAILS -->
        <h3>📦 Package Details</h3>

        <p><b>Persons:</b> <?= (int)$pkg['persons'] ?></p>
        <p><b>Duration:</b> <?= (int)$pkg['total_days'] ?> Days / <?= (int)$pkg['total_nights'] ?> Nights</p>
        <p><b>Hotel Type:</b> <?= htmlspecialchars($pkg['hotel_type']) ?></p>
        <p><b>Room:</b> <?= htmlspecialchars($pkg['bed_type']) ?> × <?= (int)$pkg['room_qty'] ?></p>

        <h2 style="color:#28a745; margin-top:20px;">
            ₹<?= number_format($pkg['total_price']) ?>
        </h2>

        <a href="book_recommended.php?id=<?= (int)$pkg['rec_id'] ?>"
           style="
           display:inline-block;
           margin-top:20px;
           background:#28a745;
           color:white;
           padding:12px 30px;
           border-radius:8px;
           text-decoration:none;
           font-size:16px;
           font-weight:bold;">
           Book This Package
        </a>

    </div>

</div>

<?php include("../includes/footer.php"); ?>