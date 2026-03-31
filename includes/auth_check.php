<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: /travel_booking_system/auth/login.php");
    exit;
}
?>