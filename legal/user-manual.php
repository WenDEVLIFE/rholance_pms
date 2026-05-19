<?php
include '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manual | Rholance Trading PMS</title>
    <meta name="description" content="Comprehensive guide for Rholance Trading Project Management System users.">
    <link rel="icon" href="<?= BASE_URL ?>favicon2.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0F172A;
            --accent: #F59E0B;
            --accent-hover: #D97706;
            --bg: #F8FAFC;
            --card: #ffffff;
            --border: #E2E8F0;
            --text: #1E293B;
            --muted: #64748B;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── TOP NAV ── */
        .um-nav {
            position: sticky;
            top: 0;
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 40px;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .um-nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text);
        }
        .um-nav-brand img { height: 40px; }
        .um-nav-brand span { font-weight: 700; font-size: 16px; color: var(--primary); }
        .um-nav-back {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .um-nav-back:hover { background: var(--bg); color: var(--accent); }

        /* ── LAYOUT ── */
        .um-layout {
            display: grid;
            grid-template-columns: 240px 1fr;
            max-width: 1100px;
            margin: 40px auto;
            gap: 32px;
            padding: 0 20px;
        }

        /* ── SIDEBAR TOC ── */
        .um-toc {
            position: sticky;
            top: 90px;
            align-self: start;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .um-toc h6 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 16px;
        }
        .um-toc a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
        }
        .um-toc a i { font-size: 11px; width: 16px; text-align: center; }
        .um-toc a:hover, .um-toc a.active {
            background: rgba(245,158,11,0.1);
            color: var(--accent);
        }

        /* ── MAIN CONTENT ── */
        .um-content {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px 48px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .um-section {
            margin-bottom: 48px;
            scroll-margin-top: 100px;
        }
        .um-section:last-child { margin-bottom: 0; }

        .um-badge {
            display: inline-block;
            background: rgba(245,158,11,0.12);
            color: var(--accent);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .um-section h2 {
            font-size: 26px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .um-section h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .um-section p {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .um-divider {
            height: 1px;
            background: var(--border);
            margin: 40px 0;
        }

        /* ── ROLE CARDS ── */
        .role-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 20px;
        }
        .role-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.25s;
        }
        .role-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .role-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(245,158,11,0.12);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 14px;
        }
        .role-card h6 { font-size: 14px; font-weight: 700; margin-bottom: 10px; color: var(--primary); }
        .role-card ul { padding-left: 16px; }
        .role-card ul li { font-size: 13px; color: var(--muted); margin-bottom: 5px; line-height: 1.5; }

        /* ── STEP LIST ── */
        .step-list { margin-top: 16px; }
        .step-item {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }
        .step-num {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .step-body h5 { font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 4px; }
        .step-body p { font-size: 13px; color: var(--muted); line-height: 1.6; margin: 0; }

        /* ── ALERT BOX ── */
        .um-alert {
            background: rgba(245,158,11,0.08);
            border: 1px solid rgba(245,158,11,0.3);
            border-radius: 12px;
            padding: 20px 24px;
            margin-top: 20px;
        }
        .um-alert .alert-title {
            font-weight: 700;
            font-size: 14px;
            color: var(--primary);
            margin-bottom: 6px;
        }
        .um-alert p { font-size: 13px; color: var(--muted); margin: 0; }
        .um-alert a { color: var(--accent); font-weight: 600; text-decoration: none; }
        .um-alert a:hover { text-decoration: underline; }

        /* ── FOOTER ── */
        .um-footer {
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        @media (max-width: 768px) {
            .um-layout { grid-template-columns: 1fr; }
            .um-toc { display: none; }
            .um-content { padding: 28px 20px; }
            .role-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav class="um-nav">
    <a href="<?= BASE_URL ?>index.php" class="um-nav-brand">
        <img src="<?= BASE_URL ?>assets/images/logoo.png" alt="Rholance Logo">
        <span>Rholance Trading</span>
    </a>
    <a href="<?= BASE_URL ?>index.php" class="um-nav-back">
        <i class="fa-solid fa-arrow-left"></i>
        Back to Home
    </a>
</nav>

<!-- LAYOUT -->
<div class="um-layout">

    <!-- SIDEBAR TOC -->
    <aside class="um-toc">
        <h6>Contents</h6>
        <a href="#intro"><i class="fa-solid fa-circle-info"></i> Introduction</a>
        <a href="#roles"><i class="fa-solid fa-users"></i> User Roles</a>
        <a href="#registration"><i class="fa-solid fa-user-plus"></i> Registration & Login</a>
        <a href="#orders"><i class="fa-solid fa-pen-ruler"></i> Custom Orders</a>
        <a href="#appointments"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
        <a href="#inventory"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
        <a href="#payments"><i class="fa-solid fa-receipt"></i> Payments</a>
        <a href="#support"><i class="fa-solid fa-headset"></i> Support</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="um-content">

        <!-- INTRO -->
        <section id="intro" class="um-section">
            <div class="um-badge">Documentation</div>
            <h2>User Manual</h2>
            <p>This User Manual provides a comprehensive guide on how to use the <strong>Rholance Trading Project Management System (PMS)</strong>. The system is a web-based platform developed to support and organize the business operations of Rholance Trading.</p>
            <p>It is designed to manage customized orders, monitor inventory, record transactions, schedule appointments, and coordinate tasks within the organization.</p>
        </section>

        <div class="um-divider"></div>

        <!-- ROLES -->
        <section id="roles" class="um-section">
            <div class="um-badge">Access Control</div>
            <h3>User Roles & Access</h3>
            <p>The system supports four distinct user roles, each with tailored access and permissions:</p>
            <div class="role-grid">
                <div class="role-card">
                    <div class="role-card-icon"><i class="fa-solid fa-user"></i></div>
                    <h6>Customer</h6>
                    <ul>
                        <li>Submit custom requests</li>
                        <li>Track order progress</li>
                        <li>Schedule appointments</li>
                        <li>View payment history</li>
                    </ul>
                </div>
                <div class="role-card">
                    <div class="role-card-icon"><i class="fa-solid fa-user-tie"></i></div>
                    <h6>Staff</h6>
                    <ul>
                        <li>Update order status</li>
                        <li>Manage stock levels</li>
                        <li>Verify payments (POS)</li>
                        <li>Manage appointments</li>
                    </ul>
                </div>
                <div class="role-card">
                    <div class="role-card-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <h6>Administrator</h6>
                    <ul>
                        <li>Full system oversight</li>
                        <li>User account management</li>
                        <li>View sales analytics</li>
                        <li>Manage branch data</li>
                    </ul>
                </div>
            </div>
        </section>

        <div class="um-divider"></div>

        <!-- REGISTRATION & LOGIN -->
        <section id="registration" class="um-section">
            <div class="um-badge">Getting Started</div>
            <h3>Registration & Login</h3>
            <p>To start using the system, click the user icon (<i class="fa-solid fa-user"></i>) in the top right of the landing page.</p>
            <div class="step-list">
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <h5>Create an Account</h5>
                        <p>New customers can sign up by providing their full name, email address, and a secure password.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <h5>Login</h5>
                        <p>Once registered, use your email and password to log in and access your personalized dashboard.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <h5>Forgot Password</h5>
                        <p>Use the "Forgot Password?" link to receive a reset link via email.</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="um-divider"></div>

        <!-- CUSTOM ORDERS -->
        <section id="orders" class="um-section">
            <div class="um-badge">Core Feature</div>
            <h3>Submitting a Custom Order</h3>
            <p>Navigate to the <strong>"Custom Order"</strong> section in your dashboard. Here, you can specify materials (Stainless, Iron, etc.), dimensions, project location, and upload reference images.</p>
            <div class="step-list">
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <h5>Fill Out the Order Form</h5>
                        <p>Provide your project details: material type, dimensions, description, and specifications.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <h5>Upload Reference Images</h5>
                        <p>Attach photos or design references to help our team understand your vision.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <h5>Submit & Track</h5>
                        <p>After submission, our staff will review the request. Monitor progress from the "My Projects" section.</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="um-divider"></div>

        <!-- APPOINTMENTS -->
        <section id="appointments" class="um-section">
            <div class="um-badge">Scheduling</div>
            <h3>Appointments & Measurements</h3>
            <p>Choose a convenient date and time through the <strong>Appointment</strong> module. This is typically used for on-site measurements or design consultations. Our team will be notified and confirm your slot through the system.</p>
        </section>

        <div class="um-divider"></div>

        <!-- INVENTORY -->
        <section id="inventory" class="um-section">
            <div class="um-badge">Stock Management</div>
            <h3>Inventory Tracking</h3>
            <p>Staff members can monitor raw material stock in real-time. The system categorizes items (Industrial, Tools, Equipment, etc.) and provides alerts for low-stock items to ensure fabrication never stops due to missing parts.</p>
        </section>

        <div class="um-divider"></div>

        <!-- PAYMENTS -->
        <section id="payments" class="um-section">
            <div class="um-badge">Transactions</div>
            <h3>Payments</h3>
            <p>We support the following payment methods:</p>
            <div class="step-list">
                <div class="step-item">
                    <div class="step-num"><i class="fa-solid fa-peso-sign" style="font-size:11px;"></i></div>
                    <div class="step-body">
                        <h5>Cash Payment</h5>
                        <p>Pay directly at the branch. Cash payments are recorded via our POS terminal.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num"><i class="fa-solid fa-mobile-screen-button" style="font-size:11px;"></i></div>
                    <div class="step-body">
                        <h5>GCash (Digital)</h5>
                        <p>For digital transactions, upload a screenshot of your GCash receipt through the system for verification.</p>
                    </div>
                </div>
            </div>
            <p style="margin-top: 12px;"><strong>Note:</strong> An initial downpayment is required before the project starts, and the remaining balance is paid after completion or installation.</p>
        </section>

        <div class="um-divider"></div>

        <!-- SUPPORT -->
        <section id="support" class="um-section">
            <div class="um-badge">Help & Support</div>
            <h3>Technical Support</h3>
            <p>If you encounter any issues or have questions about the system, please reach out:</p>
            <div class="um-alert">
                <div class="alert-title"><i class="fa-solid fa-headset" style="color: #F59E0B; margin-right: 8px;"></i>Need help?</div>
                <p>
                    Email: <a href="https://mail.google.com/mail/?view=cm&fs=1&to=rholancetrading@gmail.com" target="_blank">rholancetrading@gmail.com</a><br>
                    Phone: <strong>09957742174</strong><br>
                    Branches: Dasmariñas, Cavite | Langkiwa, Laguna
                </p>
            </div>
        </section>

        <div class="um-footer">
            © 2026 Rholance Trading. Document Version 1.2 — Updated May 2026.
        </div>

    </main>

</div>

</body>
</html>