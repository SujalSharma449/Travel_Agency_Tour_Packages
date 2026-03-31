<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    die("Unauthorized");
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$_SESSION['last_booking_id'] = intval($_GET['id']);

header("Location: invoice.php");
exit;