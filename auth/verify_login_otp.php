<?php
// ✅ Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/db.php");

// ❌ If user opens page directly or session expired
if (
    !isset($_SESSION['login_otp']) ||
    !isset($_SESSION['login_email'])
) {
    $_SESSION['error'] = "Session expired. Please login again.";
    header("Location: login.php");
    exit;
}

$error = "";

// ✅ Handle OTP submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ✅ Force string comparison
    $entered_otp = trim((string)$_POST['otp']);
    $session_otp = trim((string)$_SESSION['login_otp']);

    if ($entered_otp === "") {
        $error = "Please enter OTP";
    }
    elseif ($entered_otp === $session_otp) {

        $email = $_SESSION['login_email'];

        // ✅ FETCH USER FROM DATABASE
        $stmt = $conn->prepare(
            "SELECT username, email FROM users WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // ✅ Set logged-in user session
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name']  = $user['username'];

            // ✅ Clear OTP session (VERY IMPORTANT)
            unset($_SESSION['login_otp'], $_SESSION['login_email']);

            // ✅ Optional success message
            $_SESSION['success'] = "Login successful!";

            // ✅ Redirect to home
            header("Location: ../index.php");
            exit;

        } else {
            $error = "User not found. Please login again.";
        }

    } else {
        $error = "Invalid OTP";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Login OTP</title>
    <link rel="stylesheet" href="/Travel_Agency_Tour_Packages/assets/css/style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Verify OTP</h2>

        <?php if ($error !== "") { ?>
            <div class="error-msg"><?= htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="post">
            <input
                type="text"
                name="otp"
                placeholder="Enter OTP"
                maxlength="6"
                required
            >
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</div>

</body>
</html>