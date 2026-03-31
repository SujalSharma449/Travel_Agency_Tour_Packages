<?php
include("../config/db.php");
include("../includes/header.php");

$id = intval($_GET['id'] ?? 0);

$pkgQ = $conn->prepare("SELECT * FROM packages WHERE package_id = ? AND status='Active'");
$pkgQ->bind_param("i", $id);
$pkgQ->execute();
$pkg = $pkgQ->get_result()->fetch_assoc();

if (!$pkg) {
    echo "<p style='text-align:center;margin-top:100px;'>Package not found</p>";
    include("../includes/footer.php");
    exit;
}

/* FETCH PLACES */
$placesQ = $conn->prepare("
    SELECT p.place_id, p.place_name
    FROM package_places pp
    JOIN places p ON pp.place_id = p.place_id
    WHERE pp.package_id = ?
");
$placesQ->bind_param("i", $id);
$placesQ->execute();
$placesResult = $placesQ->get_result();
?>

<div style="max-width:1100px; margin:120px auto 80px; padding:20px;">

    <div style="
        display:flex;
        gap:50px;
        align-items:flex-start;
        flex-wrap:wrap;
        background:#ffffff;
        padding:40px;
        border-radius:18px;
        box-shadow:0 10px 30px rgba(0,0,0,0.08);
    ">

        <!-- IMAGE SECTION -->
        <div style="flex:1; min-width:320px;">
            <img src="../uploads/<?= htmlspecialchars($pkg['image']) ?>"
                 style="
                 width:100%;
                 height:400px;
                 object-fit:cover;
                 border-radius:15px;
                 ">
        </div>

        <!-- CONTENT SECTION -->
        <div style="flex:1; min-width:320px;">

            <!-- TITLE -->
            <h2 style="
                font-size:30px;
                font-weight:700;
                margin-bottom:20px;
                color:#1f2937;
            ">
                <?= htmlspecialchars($pkg['package_name']) ?>
            </h2>

            <!-- DESCRIPTION -->
            <p style="
                font-size:16px;
                line-height:1.8;
                color:#4b5563;
                margin-bottom:30px;
            ">
                <?= htmlspecialchars($pkg['description']) ?>
            </p>

            <!-- PRICE CARD -->
            <div style="
                background:#f9fafb;
                padding:18px 25px;
                border-radius:12px;
                margin-bottom:30px;
                border-left:5px solid #10b981;
            ">
                <span style="font-size:18px;color:#374151;">
                    Base Price:
                </span>
                <span style="
                    font-size:22px;
                    font-weight:700;
                    color:#16a34a;
                    margin-left:12px;
                ">
                    ₹<?= number_format($pkg['price'],2) ?>
                </span>
            </div>

            <!-- PLACES INCLUDED -->
            <h4 style="
                font-size:20px;
                margin-bottom:15px;
                color:#111827;
                border-bottom:2px solid #e5e7eb;
                padding-bottom:8px;
            ">
                Places Included
            </h4>

            <ul style="
                list-style:none;
                padding:0;
                margin-bottom:35px;
            ">
            <?php while($pl=$placesResult->fetch_assoc()){ ?>
                <li style="margin-bottom:12px;">
                    <a href="place_details.php?id=<?= $pl['place_id'] ?>&pkg=<?= $pkg['package_id'] ?>&type=custom"
                       target="_blank"
                       style="
                            color:#2563eb;
                            font-weight:600;
                            text-decoration:none;
                            font-size:15px;
                       "
                       onmouseover="this.style.textDecoration='underline'"
                       onmouseout="this.style.textDecoration='none'">
                       🔹 <?= htmlspecialchars($pl['place_name']) ?>
                    </a>
                </li>
            <?php } ?>
            </ul>

            <!-- BOOK BUTTON -->
            <a href="book_package.php?id=<?= $pkg['package_id'] ?>"
               style="
                    display:inline-block;
                    background:#10b981;
                    color:white;
                    padding:14px 35px;
                    border-radius:10px;
                    text-decoration:none;
                    font-weight:600;
                    font-size:16px;
                    transition:0.3s ease;
               "
               onmouseover="this.style.background='#059669'"
               onmouseout="this.style.background='#10b981'">
               Make Booking
            </a>

        </div>

    </div>

</div>

<?php include("../includes/footer.php"); ?>