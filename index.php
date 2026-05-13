<?php include 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rholance Trading | Metal & Industrial Materials</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/output.css">
    <link rel="icon" href="<?= BASE_URL ?>favicon2.ico">
    <link rel="shortcut icon" href="<?= BASE_URL ?>favicon2.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="navbar">

    <!-- LEFT: LOGO -->
<div class="nav-left">
    <a href="#" class="logo-link" data-section="home">
        <img src="assets/images/logoo.png" class="logo">
    </a>
</div>

    <!-- CENTER: NAV LINKS -->
   <nav class="nav-center">
    <a href="#" data-section="home"><i class="fa-solid fa-house"></i> Home</a>
    <a href="#" data-section="about"><i class="fa-solid fa-circle-info"></i> About</a>
    <a href="#" data-section="products"><i class="fa-solid fa-box-open"></i> Products</a>
    <a href="#" data-section="faq"><i class="fa-solid fa-question-circle"></i> FAQ</a>
    <a href="#" data-section="contact"><i class="fa-solid fa-envelope"></i> Contact</a>
</nav>

    <!-- RIGHT: SEARCH + USER -->
    <div class="nav-right">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search material...">
    </div>

    <i class="fa-solid fa-user user-icon user-link" id="openLoginModal"></i>
</div>

</header>

<!-- HERO -->
<section id="home" class="hero-clean page-section active-section">

    <div class="hero-bg"></div>

    <div class="hero-inner">

        <!-- LEFT -->
        <div class="hero-text">
            <h1>
                Metal & Industrial Materials<br>
                <span>Management System</span>
            </h1>

            <p>
                Rholance Trading offers a wide selection of metal and
                industrial materials, including metal scraps, stainless steel, 
                iron, aluminum, copper, and aluminum glass materials.
            </p>

            <a href="#" class="btn-main" id="heroLoginBtn">Request Custom Order</a>
            <a href="#" class="btn-outline" id="heroTrackBtn">Track Order</a>
        </div>

        <!-- RIGHT -->
        <div class="hero-visual">

            <div class="floating-carousel">

                <button class="arrow left" id="heroPrev">‹</button>

                <div class="carousel-viewport">
                    <div class="carousel-track" id="heroTrack">
                        <img src="assets/images/products/h1.png" class="slide">
                        <img src="assets/images/products/h2.png" class="slide active">
                        <img src="assets/images/products/h3.png" class="slide">
                    </div>
                </div>

                <button class="arrow right" id="heroNext">›</button>

            </div>

        </div>

    </div>

</section>

<!-- ABOUT -->
<section id="about" class="about-section ">

    <div class="about-card">

        <!-- TEXT -->
        <div class="about-text">
            <span class="about-tag">Who We Are</span>
            <h2>About Rholance Trading</h2>

            <p>
                At Rholance Trading, we bring quality and reliability together.
                From our humble beginnings as a small local trading business in 2015,
                we have grown through perseverance, consistency, and dedication to serve
                a wider range of customers and products.
            </p>

            <p>
                Backed by years of experience, we are known for our dependable service,
                quality materials, and personalized approach, continuing our commitment
                to integrity, sustainable growth, and innovation in every transaction.
            </p>

            <div class="about-highlights">
                <span>✔ Quality Materials</span>
                <span>✔ Trusted Service</span>
                <span>✔ Custom Solutions</span>
            </div>
        </div>

        <!-- IMAGE -->
        <div class="about-image">
            <img src="assets/images/about.jpg" alt="Rholance Trading">
        </div>

    </div>

</section>

<!-- =========================
PRODUCT SECTION (COMPLETE - ALL ITEMS INCLUDED)
========================= -->
<section id="products" class="products-section">

<div class="products-wrapper">
<div class="p2-container">

<!-- HEADER -->
<div class="p2-header">
    <h2>Our Products</h2>
    <p>High-quality metal and industrial materials for every need</p>
</div>

<!-- FILTER -->
<div class="p2-filters">
    <button class="p2-btn active" data-filter="all">All</button>
    <button class="p2-btn" data-filter="industrial">Industrial</button>
    <button class="p2-btn" data-filter="tools">Tools</button>
    <button class="p2-btn" data-filter="equipment">Equipment</button>
    <button class="p2-btn" data-filter="finished">Finished</button>
    <button class="p2-btn" data-filter="customized">Customized</button>
</div>

<!-- CAROUSEL -->
<div class="p2-carousel-wrapper">

<button class="p2-nav left" id="p2Prev"><i class="fa-solid fa-chevron-left"></i></button>

<div id="p2Carousel" class="p2-carousel">

<!-- ================= INDUSTRIAL ================= -->
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Corrugated Roof.png"><h3>Corrugated Roof</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Roof ridge cap.png"><h3>Roof Ridge Cap</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Wall flashing.png"><h3>Wall Flashing</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Perforated metal plates.png"><h3>Perforated Metal Plates</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Stainless plain sheet.png"><h3>Stainless Plain Sheet</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Round tube.png"><h3>Round Tube</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Square tube.png"><h3>Square Tube</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Twisted Steel Bars .png"><h3>Twisted Steel Bars</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Welded wire mesh panels.png"><h3>Welded Wire Mesh Panels</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Metal Pall Rings.png"><h3>Metal Pall Rings</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Roller Chain.png"><h3>Roller Chain</h3></div>

<!-- FASTENERS -->
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Hex Bolts.png"><h3>Hex Bolts</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Hex Coupling.png"><h3>Hex Coupling</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Flange Nuts.png"><h3>Flange Nuts</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Flange square cover.png"><h3>Flange Square Cover</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Flange cover.png"><h3>Flange Cover</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Flange base.png"><h3>Flange Base</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Flat Washers.png"><h3>Flat Washers</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Galvanized Eye Bolts.png"><h3>Galvanized Eye Bolts</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Self-Tapping Screws.png"><h3>Self-Tapping Screws</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Tekscrews.png"><h3>Tekscrews</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Wire Nails.png"><h3>Wire Nails</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Black Drywall Screws.png"><h3>Black Drywall Screws</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Expand nails with screw.png"><h3>Expand Nails with Screw</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Blind Rivet.png"><h3>Blind Rivet</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Locking rings.png"><h3>Locking Rings</h3></div>
<div class="product-card p2-card" data-category="industrial"><img src="assets/images/products/Tie Wire.png"><h3>Tie Wire</h3></div>

<!-- ================= TOOLS ================= -->
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Combination Wrench.png"><h3>Combination Wrench</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/L-Type Socket Wrench.png"><h3>L-Type Socket Wrench</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Magnetic Nut Setter Set.png"><h3>Magnetic Nut Setter Set</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Paint brush.png"><h3>Paint Brush</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Chalk line reel.png"><h3>Chalk Line Reel</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Hand Riveter.png"><h3>Hand Riveter</h3></div>

<!-- CUTTING -->
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Abrasive Flap Wheel.png"><h3>Abrasive Flap Wheel</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Cutting Wheel (Abrasive Cutting Disc).png"><h3>Cutting Wheel</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Diamond Cutting Disc.png"><h3>Diamond Cutting Disc</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Sintered Diamond Cutting Disc.png"><h3>Sintered Diamond Cutting Disc</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Carbide Multi-Wheel Cutting Disc.png"><h3>Carbide Cutting Disc</h3></div>
<div class="product-card p2-card" data-category="tools"><img src="assets/images/products/Buffing pad.png"><h3>Buffing Pad</h3></div>

<!-- ================= EQUIPMENT ================= -->
<div class="product-card p2-card" data-category="equipment"><img src="assets/images/products/Sliding Window Roller Assembly (35mm).png"><h3>Sliding Window Roller</h3></div>
<div class="product-card p2-card" data-category="equipment"><img src="assets/images/products/Toggle Clamp Latch.png"><h3>Toggle Clamp Latch</h3></div>
<div class="product-card p2-card" data-category="equipment"><img src="assets/images/products/Stainless Steel Hasp and Staple Lock.png"><h3>Hasp & Staple Lock</h3></div>
<div class="product-card p2-card" data-category="equipment"><img src="assets/images/products/Stainless Steel Butt Hinge (4x4).png"><h3>Butt Hinge</h3></div>

<!-- ================= FINISHED ================= -->
<div class="product-card p2-card" data-category="finished"><img src="assets/images/products/Ball caps.png"><h3>Ball Caps</h3></div>
<div class="product-card p2-card" data-category="finished"><img src="assets/images/products/Ball finial.png"><h3>Ball Finial</h3></div>
<div class="product-card p2-card" data-category="finished"><img src="assets/images/products/Conical finials.png"><h3>Conical Finials</h3></div>
<div class="product-card p2-card" data-category="finished"><img src="assets/images/products/Ornamental metal finials.png"><h3>Ornamental Finials</h3></div>
<div class="product-card p2-card" data-category="finished"><img src="assets/images/products/Ornamental scrolls.png"><h3>Ornamental Scrolls</h3></div>
<div class="product-card p2-card" data-category="finished"><img src="assets/images/products/Ornamental baluster.png"><h3>Ornamental Baluster</h3></div>
<div class="product-card p2-card" data-category="finished"><img src="assets/images/products/Decorative socket collars.png"><h3>Socket Collars</h3></div>
<div class="product-card p2-card" data-category="finished"><img src="assets/images/products/Stainless steel numbers.png"><h3>Steel Numbers</h3></div>
<div class="product-card p2-card" data-category="finished"><img src="assets/images/products/triple-basin kitchen sink.png"><h3>Kitchen Sink</h3></div>

<!-- ================= CUSTOMIZED ================= -->
<div class="product-card p2-card" data-category="customized"><img src="assets/images/products/gate.png"><h3>Modern Gate</h3></div>
<div class="product-card p2-card" data-category="customized"><img src="assets/images/products/railing.png"><h3>Stainless Railing</h3></div>
<div class="product-card p2-card" data-category="customized"><img src="assets/images/products/grills.png"><h3>Window Grills</h3></div>
<div class="product-card p2-card" data-category="customized"><img src="assets/images/products/truss.png"><h3>Steel Trusses</h3></div>
<div class="product-card p2-card" data-category="customized"><img src="assets/images/products/furniture.png"><h3>Industrial Furniture</h3></div>

</div>

<button class="p2-nav right" id="p2Next"><i class="fa-solid fa-chevron-right"></i></button>

</div>
</div>
</div>
</section>

<!-- FAQ SECTION -->
<section id="faq" class="page-section">
    <div class="faq-wrapper">

        <!-- LEFT CONTENT -->
        <div class="faq-left">

            <p class="faq-subtitle">SUPPORT</p>
            <h2 class="faq-title">Frequently Asked Questions</h2>

            <!-- SEARCH -->
            <div class="faq-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="faqSearch" placeholder="Search question here">
            </div>

            <p id="faqNoResults" class="faq-no-results" style="display:none;">
                No results found.
            </p>

            <!-- FAQ LIST -->

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">
            What products does Rholance Trading offer?
        </span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>We supply stainless steel, aluminum, iron, copper, and other industrial metal materials, including custom-fabricated products.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">
            Do you accept custom fabrication requests?
        </span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Yes. Customers can submit custom orders through our system, and our team will review and process them accordingly.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">
            How long does it take to complete a custom order?
        </span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Standard custom fabrication typically takes 2–3 working days, depending on complexity and material availability.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">
            Can I track my order status?
        </span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Yes. Registered users can monitor their order status through their dashboard in real time.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">
            What payment methods are accepted?
        </span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>We accept cash payments and agreed business transactions depending on the order arrangement.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">
            Is there a minimum order requirement?
        </span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Minimum order requirements may vary depending on the product type and availability. Please contact us for details.</p>
    </div>
</div>
</div>

        <!-- RIGHT IMAGE -->
        <div class="faq-right">
            <img src="assets/images/faq.png" alt="FAQ Illustration">
        </div>

    </div>
</section>


<!-- CONTACT / FOOTER SECTION -->
<section id="contact" class="page-section contact-bg">

    <!-- OVERLAY -->
    <div class="contact-overlay"></div>

    <!-- CONTENT -->
    <div class="contact-content">

        <div class="footer-grid">

            <!-- LOCATIONS -->
            <div class="footer-col">
                <h4>Cavite Branch</h4>

                <p>
                    <i class="fa-solid fa-location-dot"></i>
                    <a href="https://maps.app.goo.gl/gUbqkpCiL3MaaAXS9" target="_blank">
                        Blk 55 Lot 16 Zone XI, Bautista Prop., Sampaloc, Dasmariñas City, Cavite
                    </a>
                </p>

                <iframe
                    src="https://www.google.com/maps/embed?pb=!4v1773403631013!6m8!1m7!1ssXJ-LBgr4Pt4MYHA9YXhEA!2m2!1d14.31275641785308!2d120.9734889513085!3f164.30066107713117!4f-6.9895435408727735!5f0.7820865974627469"
                    loading="lazy">
                </iframe>
            </div>

            <div class="footer-col">
                <h4>Laguna Branch</h4>

                <p>
                    <i class="fa-solid fa-location-dot"></i>
                    <a href="#" target="_blank">
                        Langkiwa, Biñan City, Laguna
                    </a>
                </p>

                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3865.8!2d121.0!3d14.3!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d!2sLangkiwa%2C%20Bi%C3%B1an%2C%20Laguna!5e0!3m2!1sen!2sph!4v1715095000000!5m2!1sen!2sph"
                    loading="lazy">
                </iframe>
            </div>

           <div class="footer-col">
    <h4>Quick Links</h4>

    <a href="#home" class="footer-link">Home</a>
    <a href="#about" class="footer-link">About Us</a>
    <a href="#products" class="footer-link">Products</a>
    <a href="#contact" class="footer-link">Contact</a>
</div>

            <!-- EXTRA LINKS -->
            <div class="footer-col">
                <h4>Extra Links</h4>
                <ul class="footer-links">
                    <li><a href="legal/terms-and-conditions.php">Terms and Conditions</a></li>
                    <li><a href="legal/terms-of-service.php">Terms of Service</a></li>
                    <li><a href="legal/data-privacy-policy.php">Data Privacy Policy</a></li>
                    <li><a href="legal/consumer-act.php">Consumer Act</a></li>
                    <li><a href="legal/user-manual.php">User Manual</a></li>
                </ul>
            </div>

            <!-- CONTACT INFO -->
            <div class="footer-col">
                <h4>Contact Info</h4>
                <p><i class="fa-solid fa-phone"></i> 09957742174</p>

                <p class="footer-email">
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=rholancetrading@gmail.com" target="_blank">
                        <i class="fa-solid fa-envelope"></i>
                        rholancetrading@gmail.com
                    </a>
                </p>
            </div>

        </div>

        <!-- SOCIAL -->
        <div class="footer-social">
            <p>Visit Our Social Media Page:</p>
            <a href="https://www.facebook.com/profile.php?id=61584184051454" class="social-icon">
                <i class="fa-brands fa-facebook-f"></i>
            </a>
        </div>

        <!-- COPYRIGHT -->
        <div class="footer-bottom">
            © 2026 Rholance Trading. All Rights Reserved.
        </div>

    </div>
</section>

<!-- ================= LOGIN MODAL ================= -->
<div class="login-modal" id="loginModal">
    <div class="login-modal-content">

        <!-- CLOSE BUTTON -->
        <button class="login-modal-close" id="closeLoginModal">&times;</button>

        <!-- LOGO HEADER -->
        <div class="login-header">
            <img src="assets/images/logoo.png" alt="Rholance Logo">
            <h2>Welcome Back!</h2>
            <p>Please login to your account</p>
        </div>

        <!-- ERROR MESSAGE -->
        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="login-error">
                <?= htmlspecialchars($_SESSION['login_error']) ?>
            </div>
        <?php unset($_SESSION['login_error']); endif; ?>

        <!-- FORM -->
        <form method="POST" action="<?= BASE_URL ?>auth/login.php">

            <!-- EMAIL -->
            <div class="form-group">
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Email" 
                    required
                >
            </div>

            <!-- PASSWORD GROUP (FIXED STRUCTURE) -->
            <div class="form-group">

                <div class="password-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="password-input" 
                        placeholder="Enter password"
                        required
                    >
                    <i class="fa-solid fa-eye-slash eye-icon" id="togglePassword"></i>
                </div>

                <!-- ✅ NOW CORRECTLY PLACED -->
                <div class="forgot-password">
                    <a href="auth/forgot_password.php">Forgot Password?</a>
                </div>

            </div>

            <!-- BUTTON -->
            <button type="submit" class="login-btn">Log In</button>

        </form>

        <!-- FOOTER -->
        <p class="login-footer">
            Don't have an account?
            <span class="register-link" id="openRegisterModal">Sign up</span>
        </p>

        <p class="login-note">Authorized personnel only</p>

    </div>
</div>


<!-- ================= REGISTER MODAL ================= -->
<div class="login-modal" id="registerModal">
    <div class="login-modal-content">

        <button class="login-modal-close" id="closeRegisterModal">&times;</button>

        <div class="login-header">
            <img src="assets/images/logoo.png" alt="Rholance Logo">
            <h2>Create Account</h2>
            <p>Join Rholance and start managing your orders</p>
        </div>

        <?php if (isset($_SESSION['register_error'])): ?>
            <div class="login-error">
                <?= htmlspecialchars($_SESSION['register_error']) ?>
            </div>
        <?php unset($_SESSION['register_error']); endif; ?>

        <?php if (isset($_SESSION['register_success'])): ?>
            <div class="login-success">
                <?= htmlspecialchars($_SESSION['register_success']) ?>
            </div>
        <?php unset($_SESSION['register_success']); endif; ?>

        <form method="POST" action="<?= BASE_URL ?>auth/register.php">

            <!-- FULL NAME -->
            <div class="form-group">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>

            <!-- EMAIL -->
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <!-- PASSWORD -->
            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" name="password" class="password-input" placeholder="Password" required>
                    <i class="fa-solid fa-eye-slash eye-icon toggle-password"></i>
                </div>
            </div>

            <!-- CONFIRM PASSWORD -->
            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" class="password-input" placeholder="Confirm Password" required>
                    <i class="fa-solid fa-eye-slash eye-icon toggle-password"></i>
                </div>
            </div>

            <button type="submit" class="login-btn">Sign up</button>
        </form>

        <p class="login-footer">
            Already have an account?
            <span class="register-link" id="backToLogin">Log in</span>
        </p>

    </div>
</div>

<!-- FORGOT PASSWORD MODAL -->
<div id="forgotModal" class="login-modal">
    <div class="login-modal-content">

        <span class="close-btn" id="closeForgot">&times;</span>

        <div class="login-header">
            <h2>Reset Password</h2>
            <p>Enter your email to receive reset instructions</p>
        </div>

        <form>
            <input type="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>

    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/app.js"></script>

<script>
    const params = new URLSearchParams(window.location.search);

    if (params.get("login") === "success") {
        const loginModal = document.getElementById("loginModal");
        if (loginModal) {
            loginModal.style.display = "flex";
        }
    }
</script>