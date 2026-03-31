<?php
session_start();
session_unset();
session_destroy();
header("Location: /Travel_Agency_Tour_Packages/index.php");
exit;