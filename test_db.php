<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "project", 3307);
echo $conn ? "DB CONNECTED SUCCESSFULLY" : "DB FAILED";
?>