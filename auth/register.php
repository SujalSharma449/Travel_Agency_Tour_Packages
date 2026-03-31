<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("../includes/header.php"); ?>

<div class="auth-container">
    <div class="auth-box">

        <h2>User Registration</h2>

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

        <form method="post" action="send_register_otp.php">
            <input type="text" name="name" placeholder="Enter Username" required>
            <input type="email" name="email" placeholder="Enter Email" required>
            <button type="submit">Send OTP</button>
        </form>

        <div class="link">
            Already registered?
            <a href="login.php">Login here</a>
        </div>

    </div>
</div>

</body>
</html>