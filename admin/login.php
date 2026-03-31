<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("../includes/header.php"); ?>

<div class="auth-container">
<div class="auth-box">
    <h2>Admin Login</h2>

    <form method="post" action="send_admin_otp.php">
        <input type="email" name="email" placeholder="Admin Email" required>
        <button type="submit">Send OTP</button>
    </form>
</div>
</div>

</body>
</html>
