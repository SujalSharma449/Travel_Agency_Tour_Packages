<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user_email'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

$pkgQ = $conn->prepare("SELECT * FROM packages WHERE package_id=?");
$pkgQ->bind_param("i",$id);
$pkgQ->execute();
$pkg = $pkgQ->get_result()->fetch_assoc();

if(!$pkg){
    echo "<p style='text-align:center;margin-top:100px;'>Package not found</p>";
    include("../includes/footer.php");
    exit;
}

$basePrice = (int)$pkg['price'];

/* Fetch Places */
$placeStmt = $conn->prepare("
    SELECT p.place_id, p.place_name, pp.extra_price
    FROM places p
    INNER JOIN package_places pp 
        ON p.place_id = pp.place_id
    WHERE pp.package_id = ?
");
$placeStmt->bind_param("i", $pkg['package_id']);
$placeStmt->execute();
$placesResult = $placeStmt->get_result();
?>

<div style="max-width:950px;margin:120px auto;padding:30px;">
<div style="background:white;padding:40px;border-radius:18px;box-shadow:0 10px 35px rgba(0,0,0,0.08);">

<h2 style="margin-bottom:30px;color:#1f2937;">
Booking: <?= htmlspecialchars($pkg['package_name']) ?>
</h2>

<form action="payment.php" method="POST" onsubmit="return validateForm()">

<input type="hidden" name="package_id" value="<?= $pkg['package_id'] ?>">
<input type="hidden" name="total_amount" id="payAmount">
<input type="hidden" name="travel_end_date" id="hiddenEndDate">
<input type="hidden" name="duration_days" id="hiddenDuration">
<input type="hidden" name="places" id="finalPlaces">

<div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;">

<div>
<label>No. of Persons</label>
<select name="persons" required>
<?php for($i=1;$i<=10;$i++){ ?>
<option value="<?= $i ?>"><?= $i ?></option>
<?php } ?>
</select>
</div>

<div>
<label>Travel Start Date</label>
<input type="date" name="travel_start_date" id="startDate" required>
</div>

<div>
<label>Duration</label>
<select id="duration">
<option value="2">2 Days</option>
<option value="3" selected>3 Days</option>
<option value="4">4 Days</option>
<option value="5">5 Days</option>
</select>
</div>

<div>
<label>Travel End Date</label>
<input type="date" id="endDate" readonly>
</div>

<div>
<label>Hotel Type</label>
<select name="hotel_type" id="hotelType" required>
<option value="Standard">Standard</option>
<option value="Deluxe">Deluxe</option>
<option value="Luxury">Luxury</option>
</select>
</div>

<div>
<label>Room Capacity</label>
<select name="bed_type" id="bedType" required>
<option value="2 Bed">2 Bed</option>
<option value="3 Bed">3 Bed</option>
<option value="4 Bed">4 Bed</option>
</select>
</div>

<div>
<label>No. of Rooms</label>
<select name="room_qty" id="roomQty" required>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
</select>
</div>

</div>

<br>

<!-- Places -->
<div style="margin-top:30px;">
<label><strong>Select Places to Visit:</strong></label><br><br>

<?php while($place = $placesResult->fetch_assoc()): ?>
<label style="display:block;margin-bottom:8px;">
    <input type="checkbox"
           class="placeCheck"
           value="<?= htmlspecialchars($place['place_name']) ?>"
           data-price="<?= $place['extra_price'] ?>">
    <?= htmlspecialchars($place['place_name']) ?>
    (+₹<?= $place['extra_price'] ?>)
</label>
<?php endwhile; ?>
</div>

<br>

<div style="background:#f9fafb;padding:20px;border-radius:12px;border-left:5px solid #10b981;">
<strong>Total Amount:</strong>
₹<span id="totalDisplay"><?= number_format($basePrice,2) ?></span>
</div>

<br>

<button style="
background:#10b981;
color:white;
padding:14px 35px;
border:none;
border-radius:10px;
font-size:16px;
font-weight:600;
cursor:pointer;
">
Proceed to Payment
</button>

</form>
</div>
</div>

<script>

let basePrice = <?= $basePrice ?>;

const hotel = document.getElementById("hotelType");
const bed = document.getElementById("bedType");
const rooms = document.getElementById("roomQty");
const duration = document.getElementById("duration");
const startDate = document.getElementById("startDate");
const endDate = document.getElementById("endDate");

const totalDisplay = document.getElementById("totalDisplay");
const payAmount = document.getElementById("payAmount");
const hiddenEndDate = document.getElementById("hiddenEndDate");
const hiddenDuration = document.getElementById("hiddenDuration");

const placeChecks = document.querySelectorAll(".placeCheck");

const roomPricing = {
    Standard: {"2 Bed":1000,"3 Bed":1500,"4 Bed":2000},
    Deluxe: {"2 Bed":2000,"3 Bed":3000,"4 Bed":4000},
    Luxury: {"2 Bed":3500,"3 Bed":4500,"4 Bed":6000}
};

function calculateTotal(){

    let total = basePrice;

    let roomCost = roomPricing[hotel.value][bed.value] || 0;
    let days = parseInt(duration.value);
    let roomCount = parseInt(rooms.value);

    total += roomCost * roomCount * days;

    placeChecks.forEach(cb=>{
        if(cb.checked){
            total += parseInt(cb.dataset.price);
        }
    });

    totalDisplay.innerText = total.toLocaleString();
    payAmount.value = total;
}

function calculateEndDate(){

    if(!startDate.value) return;

    let d = new Date(startDate.value);
    d.setDate(d.getDate() + parseInt(duration.value));

    let end = d.toISOString().split("T")[0];

    endDate.value = end;
    hiddenEndDate.value = end;
    hiddenDuration.value = duration.value;
}

function updateSelectedPlaces(){
    let selected = [];
    placeChecks.forEach(cb=>{
        if(cb.checked){
            selected.push(cb.value);
        }
    });
    document.getElementById("finalPlaces").value = selected.join(", ");
}

function validateForm(){

    if(!startDate.value){
        alert("Please select travel start date");
        return false;
    }

    if([...placeChecks].filter(cb=>cb.checked).length === 0){
        alert("Please select at least one place.");
        return false;
    }

    updateSelectedPlaces();
    return true;
}

hotel.addEventListener("change", calculateTotal);
bed.addEventListener("change", calculateTotal);
rooms.addEventListener("change", calculateTotal);
duration.addEventListener("change", function(){
    calculateEndDate();
    calculateTotal();
});
startDate.addEventListener("change", calculateEndDate);

placeChecks.forEach(cb=>{
    cb.addEventListener("change", function(){
        calculateTotal();
        updateSelectedPlaces();
    });
});

calculateEndDate();
calculateTotal();

</script>

<?php include("../includes/footer.php"); ?>