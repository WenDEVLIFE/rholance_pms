<?php
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="rh-main">
    <div class="rh-page-header">
        <h1>User Manual</h1>
        <p>A comprehensive guide for Rholance Trading PMS users.</p>
    </div>

    <div class="row">
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-body">
                    <h6 class="fw-800 mb-3">Contents</h6>
                    <nav class="nav flex-column small gap-2">
                        <a href="#intro" class="text-decoration-none text-dark hover-amber">Introduction</a>
                        <a href="#roles" class="text-decoration-none text-dark hover-amber">User Roles</a>
                        <a href="#registration" class="text-decoration-none text-dark hover-amber">Registration & Login</a>
                        <a href="#orders" class="text-decoration-none text-dark hover-amber">Custom Orders</a>
                        <a href="#appointments" class="text-decoration-none text-dark hover-amber">Appointments</a>
                        <a href="#inventory" class="text-decoration-none text-dark hover-amber">Inventory</a>
                        <a href="#payments" class="text-decoration-none text-dark hover-amber">Payments</a>
                        <a href="#support" class="text-decoration-none text-dark hover-amber">Support</a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card card-body p-4 p-md-5">
                
                <section id="intro" class="mb-5">
                    <h2 class="fw-800 text-amber mb-3">Introduction</h2>
                    <p class="text-muted">This User Manual provides a comprehensive guide on how to use the Rholance Trading Project Management System (PMS). The system is a web-based platform developed to support and organize the business operations of Rholance Trading.</p>
                    <p class="text-muted">It is designed to manage customized orders, monitor inventory, record transactions, schedule appointments, and coordinate tasks within the organization.</p>
                </section>

                <section id="roles" class="mb-5">
                    <h3 class="fw-800 mb-4">User Roles & Access</h3>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="fw-800 mb-2">Customer</h6>
                                <ul class="small text-muted ps-3">
                                    <li>Submit custom requests</li>
                                    <li>Track order progress</li>
                                    <li>Schedule appointments</li>
                                    <li>View payment history</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="fw-800 mb-2">Staff</h6>
                                <ul class="small text-muted ps-3">
                                    <li>Update order status</li>
                                    <li>Manage stock levels</li>
                                    <li>Verify payments (POS)</li>
                                    <li>Manage appointments</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="fw-800 mb-2">Administrator</h6>
                                <ul class="small text-muted ps-3">
                                    <li>Full system oversight</li>
                                    <li>User account management</li>
                                    <li>View sales analytics</li>
                                    <li>Manage branch data</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="registration" class="mb-5 border-top pt-4">
                    <h3 class="fw-800 mb-3">Registration & Login</h3>
                    <p class="text-muted small">To start using the system, click the user icon in the top right of the landing page. New customers can sign up by providing their name, email, and a secure password. Once registered, use these credentials to log in and access your personalized dashboard.</p>
                </section>

                <section id="orders" class="mb-5 border-top pt-4">
                    <h3 class="fw-800 mb-3">Submitting a Custom Order</h3>
                    <p class="text-muted small">Navigate to the "Customize Product" section in your dashboard. Here, you can specify materials (Stainless, Iron, etc.), dimensions, and upload reference images to show us exactly what you want. After submission, our staff will review your request and provide an update.</p>
                </section>

                <section id="appointments" class="mb-5 border-top pt-4">
                    <h3 class="fw-800 mb-3">Appointments & Measurements</h3>
                    <p class="text-muted small">Choose a convenient date and time through the Appointment module. This is typically used for on-site measurements or design consultations. Our team will be notified and confirm your slot through the system.</p>
                </section>

                <section id="inventory" class="mb-5 border-top pt-4">
                    <h3 class="fw-800 mb-3">Inventory Tracking</h3>
                    <p class="text-muted small">Staff members can monitor raw material stock in real-time. The system categorizes items (Industrial, Tools, etc.) and provides alerts for low-stock items to ensure fabrication never stops due to missing parts.</p>
                </section>

                <section id="payments" class="mb-5 border-top pt-4">
                    <h3 class="fw-800 mb-3">Payments</h3>
                    <p class="text-muted small">We support cash payments recorded via our POS terminal and digital payments (e.g., GCash). For digital transactions, please upload a screenshot of your transaction slip through the system for verification.</p>
                </section>

                <section id="support" class="mb-4 border-top pt-4">
                    <h3 class="fw-800 mb-3">Technical Support</h3>
                    <div class="alert bg-amber-light border-amber-light text-dark">
                        <div class="fw-700">Need help?</div>
                        <div class="small">Contact us at <a href="mailto:rholancetrading@gmail.com" class="text-dark fw-700">rholancetrading@gmail.com</a> or call <strong>09957742174</strong>.</div>
                    </div>
                </section>

                <div class="text-center text-muted small mt-5 pt-4 border-top">
                    © 2026 Rholance Trading. Document Version 1.1 — Updated May 2026.
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.hover-amber:hover { color: #F59E0B !important; }
.w-fit { width: fit-content; }
section { scroll-margin-top: 100px; }
</style>
</body></html>