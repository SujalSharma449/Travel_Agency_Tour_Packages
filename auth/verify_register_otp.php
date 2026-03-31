<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');
require_once("../config/db.php");

if (!isset($_SESSION['register_email'], $_SESSION['register_name'])) {
    header("Location: register.php");
    exit;
}

$error = "";

$email = $_SESSION['register_email'];
$name  = $_SESSION['register_name'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $enteredOtp = trim($_POST['otp']);

    if (empty($enteredOtp)) {
        $error = "Please enter OTP.";
    } else {

        $stmt = $conn->prepare("
            SELECT otp_id 
            FROM otp_verification 
            WHERE email = ? 
            AND otp = ? 
            AND otp_type = 'register'
            AND expires_at >= NOW()
            ORDER BY otp_id DESC
            LIMIT 1
        ");

        $stmt->bind_param("ss", $email, $enteredOtp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $row = $result->fetch_assoc();

            // ✅ Delete OTP after successful verification
            $deleteStmt = $conn->prepare("DELETE FROM otp_verification WHERE otp_id = ?");
            $deleteStmt->bind_param("i", $row['otp_id']);
            $deleteStmt->execute();

            // ✅ Insert user into users table
            $insert = $conn->prepare(
                "INSERT INTO users (username, email) VALUES (?, ?)"
            );
            $insert->bind_param("ss", $name, $email);
            $insert->execute();

            // Clear session
            unset(
                $_SESSION['register_name'],
                $_SESSION['register_email']
            );

            $_SESSION['success'] = "Registration successful! Please login.";

            header("Location: login.php");
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
    <title>Verify Registration OTP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("../includes/header.php"); ?>

<div class="auth-container">
    <div class="auth-box">

        <h2>Verify Registration OTP</h2>

        <?php if (!empty($error)) { ?>
            <div class="error-msg"><?= $error ?></div>
        <?php } ?>

        <form method="post">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify OTP</button>
        </form>

        <div class="link">
            Already registered?
            <a href="login.php">Login here</a>
        </div>

    </div>
</div>

</body>
</html>