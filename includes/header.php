<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isHome = basename($_SERVER['PHP_SELF']) === 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Travel Booking</title>
    <link rel="stylesheet" href="/Travel_Agency_Tour_Packages/assets/css/style.css?v=999">
</head>

<body class="<?= $isHome ? 'home-page' : ''; ?>">

<!-- ================= NAVBAR ================= -->
<nav class="navbar">
    <div class="logo">Travel Booking</div>

    <div class="nav-right">
        <div class="nav-links">
            <a href="/Travel_Agency_Tour_Packages/index.php">Home</a>
            <a href="/Travel_Agency_Tour_Packages/user/packages.php">Packages</a>


            <?php if (isset($_SESSION['user_email']) && !isset($_SESSION['admin_logged_in'])) { ?>
                <a href="/Travel_Agency_Tour_Packages/user/my_bookings.php">My Bookings</a>
            <?php } ?>

            <?php if (isset($_SESSION['admin_logged_in'])) { ?>
                <a href="/Travel_Agency_Tour_Packages/admin/dashboard.php">Admin Dashboard</a>
                <a href="/Travel_Agency_Tour_Packages/admin/bookings.php">Manage Bookings</a>
                <a href="/Travel_Agency_Tour_Packages/admin/packages.php">Manage Packages</a>
            <?php } ?>
        </div>

        <?php if (isset($_SESSION['user_email']) && !isset($_SESSION['admin_logged_in'])) { ?>
            <div class="user-box">
                <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                <a href="/Travel_Agency_Tour_Packages/auth/logout.php" class="logout-btn">Logout</a>
            </div>

        <?php } elseif (isset($_SESSION['admin_logged_in'])) { ?>
            <div class="user-box">
                <span class="user-name">Admin</span>
                <a href="/Travel_Agency_Tour_Packages/admin/logout.php" class="logout-btn">Logout</a>
            </div>

        <?php } else { ?>
            <div class="auth-links">
                <a href="/Travel_Agency_Tour_Packages/auth/login.php" class="btn btn-user">Login</a>
                <a href="/Travel_Agency_Tour_Packages/admin/login.php" class="btn btn-admin">Admin</a>
                <a href="/Travel_Agency_Tour_Packages/auth/register.php" class="btn btn-register">Register</a>
            </div>
        <?php } ?>
    </div>
</nav>

<!-- ================= PAGE CONTENT START ================= -->
<main class="<?= $isHome ? 'home-main' : 'site-main'; ?>">

<!-- ================= NAVBAR SCROLL JS ================= -->
<script>
let lastScroll = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll <= 0) {
        navbar.classList.remove('hide');
        return;
    }

    if (currentScroll > lastScroll && currentScroll > 80) {
        // scrolling down
        navbar.classList.add('hide');
    } else {
        // scrolling up
        navbar.classList.remove('hide');
    }

    lastScroll = currentScroll;
});
</script>