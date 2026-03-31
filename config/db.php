<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1";   // NOT localhost
$user = "root";
$pass = "";            // no password
$db   = "project1";
$port = 3307;          // VERY IMPORTANT

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

?>