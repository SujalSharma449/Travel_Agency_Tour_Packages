<?php
session_start();
require_once("../config/db.php");

date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit;
}

$error = "";
$email = $_SESSION['admin_email'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $entered = trim($_POST['otp']);

    if (empty($entered)) {
        $error = "Please enter OTP.";
    } else {

        $stmt = $conn->prepare("
            SELECT otp_id 
            FROM otp_verification 
            WHERE email = ? 
            AND otp = ? 
            AND otp_type = 'admin_login'
            AND expires_at >= NOW()
            ORDER BY otp_id DESC 
            LIMIT 1
        ");

        $stmt->bind_param("ss", $email, $entered);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $row = $result->fetch_assoc();

            // ✅ Delete OTP after successful use
            $deleteStmt = $conn->prepare("DELETE FROM otp_verification WHERE otp_id = ?");
            $deleteStmt->bind_param("i", $row['otp_id']);
            $deleteStmt->execute();

            // ✅ Login success
            $_SESSION['admin_logged_in'] = true;

            header("Location: dashboard.php");
            exit;

        } else {
            $error = "Invalid or Expired OTP.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Admin OTP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="auth-container">
<div class="auth-box">
    <h2>Verify Admin OTP</h2>

    <?php if (!empty($error)) echo "<div class='error-msg'>$error</div>"; ?>

    <form method="post">
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <button type="submit">Verify</button>
    </form>
</div>
</div>

</body>
</html>