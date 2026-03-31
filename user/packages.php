<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

/* Selected category */
$selectedCategory = $_GET['category'] ?? "";

/* Prepare query safely */
if ($selectedCategory != "") {
    $stmt = $conn->prepare("SELECT * FROM packages WHERE status='Active' AND category = ?");
    $stmt->bind_param("s", $selectedCategory);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM packages WHERE status='Active'");
}
?>

<div class="page-container">
    <h2 class="page-title">Tour Packages</h2>

    <!-- Category Filter -->
    <div class="filter-box">
        <form method="GET">
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="Beach" <?= $selectedCategory=="Beach"?"selected":"" ?>>Beach</option>
                <option value="Hill Station" <?= $selectedCategory=="Hill Station"?"selected":"" ?>>Hill Station</option>
                <option value="Heritage" <?= $selectedCategory=="Heritage"?"selected":"" ?>>Heritage</option>
                <option value="Nature" <?= $selectedCategory=="Nature"?"selected":"" ?>>Nature</option>
                <option value="Pilgrimage" <?= $selectedCategory=="Pilgrimage"?"selected":"" ?>>Pilgrimage</option>
                <option value="Adventure" <?= $selectedCategory=="Adventure"?"selected":"" ?>>Adventure</option>
            </select>
        </form>
    </div>

    <div class="package-container">

        <?php if ($result && $result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>

                <div class="package-card">

                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" 
                         alt="<?= htmlspecialchars($row['package_name']) ?>">

                    <h3><?= htmlspecialchars($row['package_name']) ?></h3>

                    <!-- Category badge -->
                    <span class="category-badge">
                        <?= htmlspecialchars($row['category']) ?>
                    </span>

                    <a class="view-btn" 
                       href="package_details.php?id=<?= $row['package_id'] ?>">
                       View Details
                    </a>

                </div>

            <?php } ?>
        <?php } else { ?>
            <p class="no-packages">No packages found for this category.</p>
        <?php } ?>

    </div>
</div>

<!-- ================= AI CHATBOT ================= -->

<style>
/* Floating Button */
#aiChatBtn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: linear-gradient(135deg,#007bff,#00c6ff);
    color: white;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    z-index: 9999;
}

/* Chat Box */
#aiChatBox {
    position: fixed;
    bottom: 100px;
    right: 25px;
    width: 300px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    padding: 20px;
    display: none;
    z-index: 9999;
}

#aiChatBox h4 {
    margin-bottom: 10px;
}

.ai-btn {
    display: block;
    margin-top: 10px;
    padding: 8px;
    text-align: center;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
}

.ai-yes {
    background: #28a745;
    color: white;
}

.ai-no {
    background: #f1f1f1;
    color: #333;
}
</style>

<!-- Floating Button -->
<div id="aiChatBtn" onclick="toggleAIChat()">
    🤖
</div>

<!-- Chat Window -->
<div id="aiChatBox">
    <h4>🤖 Smart Assistant</h4>
    <p>Need help choosing the perfect package?</p>

    <a href="recommendation.php" class="ai-btn ai-yes">
        Yes, Recommend Me
    </a>

    <a href="#" onclick="closeAIChat()" class="ai-btn ai-no">
        No, I’ll Browse
    </a>
</div>

<script>
function toggleAIChat() {
    var box = document.getElementById("aiChatBox");
    box.style.display = box.style.display === "block" ? "none" : "block";
}

function closeAIChat() {
    document.getElementById("aiChatBox").style.display = "none";
}
</script>

<!-- ================= END AI CHATBOT ================= -->

<?php include("../includes/footer.php"); ?>