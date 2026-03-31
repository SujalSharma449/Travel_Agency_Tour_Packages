<?php  
error_reporting(E_ALL);  
ini_set('display_errors', 1);  
include("../config/db.php");  
include("../includes/header.php");  

$budget   = $_POST['budget'] ?? '';  
$category = $_POST['category'] ?? '';  
$persons  = $_POST['persons'] ?? '';  

if (!$budget || !$category || !$persons) {  
    echo "<h3 style='text-align:center; margin-top:100px;'>Invalid Request</h3>";  
    include("../includes/footer.php");  
    exit;  
}  

$stmt = $conn->prepare("
    SELECT rec_id, package_name, description, persons,
           total_days, total_nights, hotel_type,
           bed_type, room_qty, total_price, image
    FROM recommended_packages
    WHERE budget_type = ?
    AND FIND_IN_SET(?, category)
    AND FIND_IN_SET(?, person_type)
");

$stmt->bind_param("sss", $budget, $category, $persons);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="recres-wrapper">

    <div class="recres-container">

        <h2 class="recres-title">
            Smart Recommendations For You
        </h2>

        <p class="recres-subtitle">
            Based on your preferences:
            <b><?= htmlspecialchars($budget) ?></b> Budget,
            <b><?= htmlspecialchars($category) ?></b> Category,
            <b><?= htmlspecialchars($persons) ?></b> Group
        </p>

        <?php if($result->num_rows == 0){ ?>

            <div class="recres-noresult">
                <h3>No packages found 😔</h3>
                <a href="recommendation.php" class="recres-trybtn">
                    Try Again
                </a>
            </div>

        <?php } else { ?>

        <div class="recres-grid">

        <?php while($row = $result->fetch_assoc()){ 

            $image = (!empty($row['image']))
                ? htmlspecialchars($row['image'])
                : "default.jpg";
        ?>

            <div class="recres-card">

                <img src="../uploads/recommended/<?= $image ?>" 
                     class="recres-image">

                <div class="recres-body">

                    <h3 class="recres-packagename">
                        <?= htmlspecialchars($row['package_name']) ?>
                    </h3>

                    <p class="recres-desc">
                        <?= htmlspecialchars($row['description']) ?>
                    </p>

                    <div class="recres-details">
                        <p><b>Duration:</b> <?= (int)$row['total_days'] ?> Days / <?= (int)$row['total_nights'] ?> Nights</p>
                        <p><b>Persons:</b> <?= (int)$row['persons'] ?></p>
                        <p><b>Hotel:</b> <?= htmlspecialchars($row['hotel_type']) ?></p>
                        <p><b>Room:</b> <?= htmlspecialchars($row['bed_type']) ?> × <?= (int)$row['room_qty'] ?></p>
                    </div>

                    <div class="recres-price">
                        ₹<?= number_format((int)$row['total_price']) ?>
                    </div>

                    <a href="recommendation_details.php?id=<?= (int)$row['rec_id'] ?>"
                       class="recres-viewbtn">
                        View Details
                    </a>

                </div>
            </div>

        <?php } ?>

        </div>

        <?php } ?>

    </div>
</div>

<?php include("../includes/footer.php"); ?>