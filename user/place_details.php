<?php
include("../config/db.php");
include("../includes/header.php");

/* ======================
   VALIDATE PLACE ID
====================== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='center-text'>Invalid place</p>";
    include("../includes/footer.php");
    exit;
}

$place_id = (int)$_GET['id'];

/* ======================
   FETCH PLACE DETAILS
====================== */
$stmt = $conn->prepare("
    SELECT place_name, description, location, best_time, image
    FROM places
    WHERE place_id = ?
");
$stmt->bind_param("i", $place_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='center-text'>Place not found</p>";
    include("../includes/footer.php");
    exit;
}

$place = $result->fetch_assoc();

/* ======================
   PACKAGE ID + TYPE
====================== */
$pkgId = isset($_GET['pkg']) && is_numeric($_GET['pkg'])
    ? (int)$_GET['pkg']
    : 0;

$type = $_GET['type'] ?? ''; // custom OR recommended
?>

<div class="page-container1">
  <div class="details-wrapper">
    <div class="place-details-container">

      <h2><?= htmlspecialchars($place['place_name']) ?></h2>

      <!-- PLACE IMAGE -->
      <img
        src="../uploads/places/<?= htmlspecialchars($place['image']) ?>"
        alt="<?= htmlspecialchars($place['place_name']) ?>"
        class="place-image"
      >

      <!-- DESCRIPTION -->
      <p class="place-description">
        <?= nl2br(htmlspecialchars($place['description'])) ?>
      </p>

      <!-- META INFO -->
      <p><b>📍 Location:</b> <?= htmlspecialchars($place['location']) ?></p>
      <p><b>🕒 Best Time to Visit:</b> <?= htmlspecialchars($place['best_time']) ?></p>

      <!-- BACK BUTTON LOGIC -->
      <?php if ($pkgId > 0) { ?>

          <?php if ($type === 'recommended') { ?>
              <a href="recommendation_details.php?id=<?= $pkgId ?>" class="btn-book">
                ← Back to Recommended Package
              </a>
          <?php } else { ?>
              <a href="package_details.php?id=<?= $pkgId ?>" class="btn-book">
                ← Back to Package
              </a>
          <?php } ?>

      <?php } else { ?>

          <a href="packages.php" class="btn-book">
            ← Back to Packages
          </a>

      <?php } ?>

    </div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>