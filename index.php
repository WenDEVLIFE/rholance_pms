<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/database.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Rholance Trading PMS - Metal and industrial materials management and custom metal fabrication projects.">
    <title>Rholance Trading | Metal &amp; Industrial Materials</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/output.css">
    <link rel="icon" href="<?= BASE_URL ?>favicon2.ico">
    <link rel="shortcut icon" href="<?= BASE_URL ?>favicon2.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;family=Outfit:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing-dark.css">
</head>
<body>

<header class="navbar">

    <!-- LEFT: LOGO -->
<div class="nav-left">
    <a href="#" class="logo-link" data-section="home">
        <img src="<?= BASE_URL ?>assets/images/logoo.png" class="logo">
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

    <!-- DARK MODE TOGGLE -->
    <button class="landing-theme-btn" id="landingThemeToggle" title="Toggle dark/light mode" aria-label="Toggle dark mode">
        <i class="fa-solid fa-moon" id="themeIcon"></i>
    </button>

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
                        <img src="<?= BASE_URL ?>assets/images/products/h1.png" class="slide">
                        <img src="<?= BASE_URL ?>assets/images/products/h2.png" class="slide active">
                        <img src="<?= BASE_URL ?>assets/images/products/h3.png" class="slide">
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
            <img src="<?= BASE_URL ?>assets/images/about.jpg" alt="Rholance Trading">
        </div>

    </div>

</section>

<section id="products" class="products-section">
    <div class="products-wrapper">
        <div class="p2-container">
            <!-- HEADER -->
            <div class="p2-header">
                <h2>Customized Fabricated Projects</h2>
                <p>Tailored high-quality metal and stainless steel works created to match your exact specifications and designs.</p>
            </div>

            <!-- PROJECTS GRID -->
            <div class="projects-grid">
                <!-- 1. Gate -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/gate.png" alt="Gate">
                    </div>
                    <div class="product-info">
                        <h3>1. Gate</h3>
                        <p>Durable gates designed with modern elegance and high-quality structural steel or stainless steel components.</p>
                    </div>
                </div>

                <!-- 2. Water Tank (Stainless) -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/water_tank.png" alt="Water Tank (Stainless)">
                    </div>
                    <div class="product-info">
                        <h3>2. Water Tank (Stainless)</h3>
                        <p>Corrosion-resistant stainless steel water storage tanks fabricated for maximum structural life and purity.</p>
                    </div>
                </div>

                <!-- 3. Table -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/table.png" alt="Table">
                    </div>
                    <div class="product-info">
                        <h3>3. Table</h3>
                        <p>Beautifully crafted customized metal and stainless steel frame tables suitable for dining, office, or industrial use.</p>
                    </div>
                </div>

                <!-- 4. Lababo (Sink) -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/lababo.jpg" alt="Lababo (Sink)">
                    </div>
                    <div class="product-info">
                        <h3>4. Lababo (Sink)</h3>
                        <p>Premium customized single, double, or triple basin stainless steel sinks, perfect for commercial kitchens or residential spaces.</p>
                    </div>
                </div>

                <!-- 5. Stainless Letters -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/stainless_letters.png" alt="Stainless Letters">
                    </div>
                    <div class="product-info">
                        <h3>5. Stainless Letters</h3>
                        <p>Polished or brushed custom 3D stainless steel signage letters for modern professional building facades or reception displays.</p>
                    </div>
                </div>

                <!-- 6. Windows (Metal Frame) -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/windows.png" alt="Windows (Metal Frame)">
                    </div>
                    <div class="product-info">
                        <h3>6. Windows (Metal Frame)</h3>
                        <p>Robust window metal structures and secure frames designed with aesthetic charm and heavy-duty durability.</p>
                    </div>
                </div>

                <!-- 7. Handrail -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/handrail.jpg" alt="Handrail">
                    </div>
                    <div class="product-info">
                        <h3>7. Handrail</h3>
                        <p>Elegant stainless steel or wrought iron handrails fabricated perfectly for safe stairs, terraces, and commercial walkspaces.</p>
                    </div>
                </div>

                <!-- 8. Push Cart -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/push_cart.jpg" alt="Push Cart">
                    </div>
                    <div class="product-info">
                        <h3>8. Push Cart</h3>
                        <p>Sturdy multi-purpose push carts built for medical clinics, high-end salons, industrial warehouses, or catering use.</p>
                    </div>
                </div>

                <!-- 9. Carrier (Push Cart) -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/carrier.jpg" alt="Carrier (Push Cart)">
                    </div>
                    <div class="product-info">
                        <h3>9. Carrier (Push Cart)</h3>
                        <p>Heavy-duty flatbed towable utility carrier carts fabricated to handle massive loads and rough outdoor terrains.</p>
                    </div>
                </div>

                <!-- 10. Terrace (Metal Structure) -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/terrace.png" alt="Terrace (Metal Structure)">
                    </div>
                    <div class="product-info">
                        <h3>10. Terrace (Metal Structure)</h3>
                        <p>Structural metal framing, reliable trusses, and safety deck structures for balconies, rooftops, and outdoor terraces.</p>
                    </div>
                </div>

                <!-- 11. Upuan (Chair) -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/upuan.jpg" alt="Upuan (Chair)">
                    </div>
                    <div class="product-info">
                        <h3>11. Upuan (Chair)</h3>
                        <p>Custom polished minimal stainless steel chairs built with sleek silhouettes, modern geometry, and extreme lifetime durability.</p>
                    </div>
                </div>

                <!-- 12. Laboratory Cabinet -->
                <div class="product-card">
                    <div class="img-wrapper">
                        <img src="<?= BASE_URL ?>assets/images/products/customized/cabinet.jpg" alt="Laboratory Cabinet">
                    </div>
                    <div class="product-info">
                        <h3>12. Laboratory Cabinet</h3>
                        <p>Hygienic, chemical-resistant custom industrial stainless steel cabinets designed for modern laboratories and medical labs.</p>
                    </div>
                </div>
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
        <span class="faq-text">1. What products does Rholance Trading offer?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Rholance Trading offers metal and industrial materials such as stainless steel, aluminum, iron, and customized fabricated products.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">2. Do you accept custom fabrication requests?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Yes, we accept customized projects based on your required design, size, and specifications.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">3. How can I request a custom order?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>You can submit a custom order through the system by providing your project details, materials, and specifications.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">4. Can I choose the project location for customized projects?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Yes, you can specify the project location when submitting a customized project request. However, the system is only applicable for projects located in Cavite and Laguna branches.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">5. How long does it take to complete a custom order?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>The completion time usually takes 2–3 days, depending on the complexity of the project.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">6. Can I track my order status?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Yes, you can track your order through the system dashboard under the Order Tracking section.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">7. What payment methods are accepted?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>We accept cash payments and GCash payments. For GCash, you need to upload a screenshot as proof of payment.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">8. Do I need to pay before the project starts?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Yes, an initial downpayment is required before the project starts, and the remaining balance is paid after completion or installation.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">9. Can I schedule an appointment?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>Yes, you can set an appointment through the system for consultation, measurement, or project discussion.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">10. Where are your branches located?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>We have branches in Dasmariñas, Cavite and Langkiwa, Laguna.</p>
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        <span class="faq-text">11. What happens if there is an issue with my order?</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="faq-answer">
        <p>If there is a problem, the project may be reviewed and corrected (backjob) to meet the agreed requirements.</p>
    </div>
</div>
</div>

        <!-- RIGHT IMAGE -->
        <div class="faq-right">
            <img src="<?= BASE_URL ?>assets/images/faq.png" alt="FAQ Illustration">
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
            <img src="<?= BASE_URL ?>assets/images/logoo.png" alt="Rholance Logo" style="display:block;margin:0 auto 12px;">
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
            <img src="<?= BASE_URL ?>assets/images/logoo.png" alt="Rholance Logo" style="display:block;margin:0 auto 12px;">
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

<!-- ================= VARIANTS MODAL ================= -->
<div id="variantsModal" class="login-modal">
    <div class="login-modal-content variants-modal-content">
        <span class="close-btn" id="closeVariantsModal" style="cursor:pointer;float:right;font-size:24px;">&times;</span>
        <div class="login-header">
            <h2 id="variantsTitle">Product Variants</h2>
            <p>Select your preferred design variation</p>
        </div>
        <div id="variantsContainer" class="variants-grid">
            <!-- Dynamic variants will be injected here -->
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/app.js"></script>

<script>
    // Theme Toggle Logic
    const themeBtn = document.getElementById("landingThemeToggle");
    const themeIcon = document.getElementById("themeIcon");

    // Initialize from local storage or system preference
    if (localStorage.getItem("theme") === "dark" || (!localStorage.getItem("theme") && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
        document.body.classList.add("dark");
        if (themeIcon) {
            themeIcon.classList.replace("fa-moon", "fa-sun");
        }
    } else {
        document.body.classList.remove("dark");
        if (themeIcon) {
            themeIcon.classList.replace("fa-sun", "fa-moon");
        }
    }

    if (themeBtn) {
        themeBtn.addEventListener("click", () => {
            document.body.classList.toggle("dark");
            if (document.body.classList.contains("dark")) {
                localStorage.setItem("theme", "dark");
                if (themeIcon) themeIcon.classList.replace("fa-moon", "fa-sun");
            } else {
                localStorage.setItem("theme", "light");
                if (themeIcon) themeIcon.classList.replace("fa-sun", "fa-moon");
            }
        });
    }

    const params = new URLSearchParams(window.location.search);
    if (params.get("login") === "success") {
        const loginModal = document.getElementById("loginModal");
        if (loginModal) {
            loginModal.classList.add("active");
        }
    }

    // Product Variants Logic
    document.addEventListener("DOMContentLoaded", () => {
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            const h3 = card.querySelector('.product-info h3');
            if (h3) {
                // Remove numbers like "1. Gate" -> "Gate"
                let productName = h3.innerText.replace(/^\d+\.\s*/, '').trim();
                card.style.cursor = 'pointer';
                card.addEventListener('click', () => {
                    openVariantsModal(productName);
                });
            }
        });

        document.getElementById('closeVariantsModal')?.addEventListener('click', () => {
            document.getElementById('variantsModal').classList.remove('active');
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === document.getElementById('variantsModal')) {
                document.getElementById('variantsModal').classList.remove('active');
            }
        });
    });

    function openVariantsModal(productName) {
        document.getElementById('variantsTitle').innerText = productName + ' Variants';
        document.getElementById('variantsContainer').innerHTML = '<p style="text-align:center;width:100%;color:var(--card-text);">Loading variants...</p>';
        document.getElementById('variantsModal').classList.add('active');

        fetch('<?= BASE_URL ?>api/get_variants.php?product_name=' + encodeURIComponent(productName))
            .then(res => res.json())
            .then(data => {
                if (data.success && data.variants.length > 0) {
                    let html = '';
                    data.variants.forEach(v => {
                        html += `
                            <div class="variant-card">
                                <img src="${v.image_url}" alt="${v.variant_name}">
                                <h4>${v.variant_name}</h4>
                                <p>${v.description}</p>
                            </div>
                        `;
                    });
                    document.getElementById('variantsContainer').innerHTML = html;
                } else {
                    document.getElementById('variantsContainer').innerHTML = '<p style="text-align:center;width:100%;color:var(--card-text);">No variants available for this product yet.</p>';
                }
            })
            .catch(err => {
                document.getElementById('variantsContainer').innerHTML = '<p style="text-align:center;width:100%;color:red;">Error loading variants.</p>';
            });
    }
</script>