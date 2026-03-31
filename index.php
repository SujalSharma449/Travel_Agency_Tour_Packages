<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/header.php");
?>

<!-- ================= HERO SECTION ================= -->
<section class="home-hero">
    <div class="home-hero-overlay"></div>

    <div class="home-hero-content">
        <h1>Explore the World With Us</h1>
        <p>Book curated tour packages with secure payments & OTP login</p>

        <?php if (!isset($_SESSION['user_email'])) { ?>
            <a href="auth/register.php" class="hero-btn primary">Get Started</a>
            <a href="user/packages.php" class="hero-btn secondary">View Packages</a>
        <?php } else { ?>
            <a href="user/packages.php" class="hero-btn primary">Explore Packages</a>
        <?php } ?>
    </div>
</section>

<!-- ================= FEATURES ================= -->
<section class="features">
    <div class="feature-box">
        🌍
        <h3>Best Destinations</h3>
        <p>Hand-picked locations across India</p>
    </div>

    <div class="feature-box">
        💳
        <h3>Secure Payments</h3>
        <p>Razorpay integration with OTP login</p>
    </div>

    <div class="feature-box">
        🛎️
        <h3>24×7 Support</h3>
        <p>Dedicated customer assistance</p>
    </div>
</section>

<?php include("includes/footer.php"); ?>