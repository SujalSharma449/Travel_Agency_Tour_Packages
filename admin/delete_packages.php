<?php
session_start();
require_once("../config/db.php");

$id = (int)$_GET['id'];
$conn->query("DELETE FROM packages WHERE package_id=$id");

header("Location: packages.php");
exit;