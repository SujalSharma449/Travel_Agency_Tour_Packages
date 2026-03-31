<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../config/db.php");
include("../includes/header.php");
?>

<div class="recommend-wrapper">

    <div class="recommend-section">

        <div class="recommend-card">

            <h2 class="recommend-title">Find Your Perfect Trip ✈️</h2>
            <p class="recommend-subtitle">
                Smart travel recommendations based on your preferences
            </p>

            <form action="recommendation_result.php" method="POST" class="recommend-form">

                <!-- Budget -->
                <div class="form-group">
                    <label>Budget Type</label>
                    <select name="budget" required>
                        <option value="">Select Budget</option>
                        <option value="Low">Low Budget</option>
                        <option value="Medium">Medium Budget</option>
                        <option value="High">Luxury Budget</option>
                    </select>
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="category" onchange="togglePersons()" required>
                        <option value="">Select Category</option>
                        <option value="Family">Family Trip</option>
                        <option value="Honeymoon">Honeymoon</option>
                        <option value="Adventure">Adventure</option>
                    </select>
                </div>

                <!-- Persons -->
                <div class="form-group" id="personsField">
                    <label>No. of Persons</label>
                    <select name="persons" required>
                        <option value="">Select Persons</option>
                        <option value="Small">Less than 4</option>
                        <option value="Large">More than 4</option>
                    </select>
                </div>

                <button type="submit" class="recommend-btn">
                    🔍 Get Recommendation
                </button>

            </form>

        </div>

    </div>

</div>
<script>
function togglePersons() {

    var category = document.getElementById("category").value;
    var personsField = document.getElementById("personsField");

    if(category === "Honeymoon") {
        personsField.style.display = "none";
    } else {
        personsField.style.display = "block";
    }

}
</script>

<?php include("../includes/footer.php"); ?>