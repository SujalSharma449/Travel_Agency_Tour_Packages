<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="auth-container">
    <div class="auth-box">

        <h2>User Login</h2>

        <?php if (isset($_SESSION['error'])) { ?>
            <div class="error-msg">
                <?= $_SESSION['error']; ?>
            </div>
        <?php unset($_SESSION['error']); } ?>

        <?php if (isset($_SESSION['success'])) { ?>
            <div class="success-msg">
                <?= $_SESSION['success']; ?>
            </div>
        <?php unset($_SESSION['success']); } ?>

        <form method="post" action="send_login_otp.php">
            <input 
                type="email" 
                name="email" 
                placeholder="Enter registered email" 
                required
            >
            <button type="submit">Send OTP</button>
        </form>

        <div class="link">
            New user? <a href="register.php">Register here</a>
        </div>

    </div>
</div>

</body>
</html>