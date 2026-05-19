<?php
require_once __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    die("Invalid order ID.");
}

// Fetch general order information along with customer details
$orderQuery = $conn->query("
    SELECT co.*, u.name AS staff_name, 
           c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone, c.address AS customer_address
    FROM custom_orders co
    LEFT JOIN users u ON u.id = co.user_id
    LEFT JOIN users c ON c.id = co.customer_id
    WHERE co.id = $orderId
");

$order = $orderQuery ? $orderQuery->fetch_assoc() : null;

if (!$order) {
    die("Order not found.");
}

// Authorization check for customers
if ($userRole === 'customer' && $order['customer_id'] != $userId) {
    header("Location: ../index.php");
    exit;
}

// Fetch assigned welders (who made it) from tasks
$welderQuery = $conn->query("
    SELECT DISTINCT u.name, u.role
    FROM tasks t
    JOIN users u ON u.id = t.assigned_to
    WHERE t.order_id = $orderId
");
$assignedWelders = [];
if ($welderQuery) {
    while ($w = $welderQuery->fetch_assoc()) {
        $assignedWelders[] = $w['name'];
    }
}

// Fetch project materials breakdown
$itemsQuery = $conn->query("
    SELECT oi.*, i.name AS item_name
    FROM order_items oi
    JOIN items i ON i.id = oi.item_id
    WHERE oi.order_id = $orderId
");
$items = [];
$totalMaterialCost = 0;
if ($itemsQuery) {
    while ($row = $itemsQuery->fetch_assoc()) {
        $items[] = $row;
        $totalMaterialCost += $row['total_amount'];
    }
}

// Total revenue / income from the project (Material total + 50% estimated labor markup)
$totalRevenue = $totalMaterialCost * 1.5;

// Progress Percentage Mapping based on Order Status
$progressMap = [
    'Appointment' => 10,
    'Initial Payment' => 30,
    'On-going' => 60,
    'For Delivery' => 85,
    'Backjobs' => 50,
    'Completed' => 100,
    'Cancelled' => 0
];
$progressPercent = (int)($order['progress_percent'] ?? 0);
if ($progressPercent <= 0) {
    $progressPercent = $progressMap[$order['status']] ?? 10;
}
$badgeClass = 'badge-' . strtolower(str_replace([' ', '/'], '-', $order['status']));

// Detailed project milestones checklist for "himay-himayin" requirement
$milestones = [
    ['pct' => 10, 'label' => 'Initial Appointment Scheduled', 'desc' => 'Customer has booked the calendar slot. Cavite/Laguna branch confirmed.'],
    ['pct' => 30, 'label' => 'Dimensions & Material Scope Approved', 'desc' => 'Assigned welder visited site, recorded measurements, materials mapped, and deposit processed.'],
    ['pct' => 60, 'label' => 'Structural Fabrication On-going', 'desc' => 'Raw metal sheets/tubes cut. Framing, welding, and joint structural setup is in progress.'],
    ['pct' => 85, 'label' => 'Finishing, Painting & Quality Prep', 'desc' => 'Anti-rust primer applied. Painted, polished, and queued for logistics dispatch.'],
    ['pct' => 100, 'label' => 'Final Installation & Complete Signoff', 'desc' => 'Delivered to location, mounted/assembled, final balance cleared, and project hand-over complete.']
];
?>

<div class="rh-main">
    <!-- PAGE HEADER -->
    <div class="rh-page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            <h1>Order Blueprint Details</h1>
            <p>Custom Order reference #<strong><?= $orderId ?></strong></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print Invoice
            </button>
        </div>
    </div>

    <!-- MAIN TWO COLUMN GRID -->
    <div class="row g-4">
        
        <!-- LEFT COLUMN: PROJECT INFO & MATERIALS -->
        <div class="col-12 col-lg-7">
            
            <!-- CORE PROJECT DETAILS CARD -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <span class="fw-800 text-light-emphasis"><i class="fas fa-info-circle me-2 text-amber"></i>Project Specifications</span>
                    <span class="badge <?= $badgeClass ?>"><?= $order['status'] ?></span>
                </div>
                <div class="card-body border-top">
                    <h4 class="fw-800 text-light-emphasis mb-2"><?= htmlspecialchars($order['project_name'] ?? 'Custom Material Fabrication') ?></h4>
                    <p class="text-muted small mb-4">Registered on <?= date('F d, Y', strtotime($order['created_at'])) ?></p>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700">FABRICATION CATEGORY</span>
                            <span class="fw-600 text-light-emphasis"><?= htmlspecialchars($order['category'] ?? 'Customized Metal') ?></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700">CORE RAW MATERIAL</span>
                            <span class="fw-600 text-light-emphasis"><?= htmlspecialchars($order['material'] ?? 'Stainless Steel / Iron') ?></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700">DIMENSIONS</span>
                            <span class="fw-600 text-light-emphasis"><?= htmlspecialchars($order['dimensions'] ?? 'Specified in Blueprint') ?></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700">ESTIMATED COMPLETION</span>
                            <span class="fw-600 text-light-emphasis">
                                <?= !empty($order['estimated_completion']) ? date('M d, Y', strtotime($order['estimated_completion'])) : (!empty($order['expected_date']) ? date('M d, Y', strtotime($order['expected_date'])) : 'To Be Scheduled') ?>
                            </span>
                        </div>
                    </div>

                    <!-- PROGRESS METER -->
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-700">FABRICATION PROGRESS</span>
                        <span class="fw-800 text-light-emphasis"><?= $progressPercent ?>%</span>
                    </div>
                    <div class="progress mb-3" style="height:10px; border-radius:5px;">
                        <div class="progress-bar bg-amber" style="width: <?= $progressPercent ?>%; border-radius:5px;"></div>
                    </div>

                    <?php if (!empty($order['progress_details'])): ?>
                        <div class="alert alert-warning border-0 p-3 rounded-3 mb-4" style="background: rgba(245,158,11,0.08); color: var(--rh-amber);">
                            <div class="fw-800 mb-1" style="font-size:0.85rem;"><i class="fas fa-hammer me-2"></i>Active Build Update from Welder</div>
                            <p class="mb-0 small fw-600 text-light-emphasis" style="font-size:0.75rem;"><?= htmlspecialchars($order['progress_details']) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- DETAILED PROGRESS MILESTONES (HIMAY-HIMAY) -->
                    <div class="border-top pt-3">
                        <span class="text-muted d-block small fw-800 mb-3" style="letter-spacing:0.5px;">PROJECT MILESTONES LOG</span>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($milestones as $ms): 
                                $isCompleted = $progressPercent >= $ms['pct'];
                                $isCurrent = ($progressPercent == $ms['pct']) || ($progressPercent > 0 && $progressPercent < $ms['pct'] && ($progressPercent > ($ms['pct'] - 20)));
                                $iconClass = $isCompleted ? 'fa-circle-check text-success' : 'fa-circle text-muted opacity-50';
                                $textClass = $isCompleted ? 'text-light-emphasis fw-700' : 'text-muted';
                            ?>
                            <div class="d-flex gap-3 align-items-start">
                                <i class="fas <?= $iconClass ?> fs-5 mt-1"></i>
                                <div>
                                    <div class="<?= $textClass ?> d-flex align-items-center gap-2" style="font-size:0.9rem;">
                                        <?= htmlspecialchars($ms['label']) ?>
                                        <?php if ($isCurrent && $order['status'] !== 'Completed' && $order['status'] !== 'Cancelled'): ?>
                                            <span class="badge bg-warning text-dark small" style="font-size:0.6rem;">CURRENT STAGE</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="small text-muted mb-0" style="font-size:0.8rem;"><?= htmlspecialchars($ms['desc']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (!empty($order['description'])): ?>
                        <div class="bg-light p-3 rounded-3 mt-4" style="background: rgba(255,255,255,0.03) !important;">
                            <span class="text-muted d-block small fw-700 mb-1">BLUEPRINT INSTRUCTIONS</span>
                            <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($order['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ALLOCATED MATERIALS BREAKDOWN -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <span class="fw-800 text-light-emphasis"><i class="fas fa-list-check me-2 text-amber"></i>Allocated Materials & Costs</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Material Description</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th class="text-end pe-4">Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No materials allocated to this order yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-700 text-light-emphasis"><?= htmlspecialchars($item['item_name']) ?></div>
                                    <span class="text-muted small" style="font-size:0.75rem;">Fabrication Material Item</span>
                                </td>
                                <td class="text-light-emphasis"><?= $item['quantity'] ?> units</td>
                                <td class="text-light-emphasis">₱<?= number_format($item['price'], 2) ?></td>
                                <td class="text-end pe-4 fw-700 text-light-emphasis">₱<?= number_format($item['total_amount'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-warning fw-800">
                                <td colspan="3" class="ps-4">Material Subtotal</td>
                                <td class="text-end pe-4">₱<?= number_format($totalMaterialCost, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: FINANCIALS, ASSIGNED TEAM & IMAGES -->
        <div class="col-12 col-lg-5">
            
            <!-- TOTAL PROJECT INCOME/FINANCIALS (ADMIN & STAFF ONLY) -->
            <?php if (in_array($userRole, ['admin', 'staff'])): ?>
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <span class="fw-800 text-light-emphasis"><i class="fas fa-money-bill-trend-up me-2 text-amber"></i>Project Financial Metrics</span>
                </div>
                <div class="card-body border-top text-center p-4">
                    <span class="text-muted small d-block fw-700 mb-1">TOTAL PROJECT REVENUE / EARNINGS</span>
                    <h2 class="fw-800 text-success m-0">₱<?= number_format($totalRevenue, 2) ?></h2>
                    <span class="text-muted" style="font-size:0.75rem;">(Calculated from raw materials + 50% labor markup)</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- ASSIGNED TEAM (WELDERS WHO MADE IT) -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <span class="fw-800 text-light-emphasis"><i class="fas fa-hard-hat me-2 text-amber"></i>Fabrication Welders Team</span>
                </div>
                <div class="card-body border-top">
                    <?php if (empty($assignedWelders)): ?>
                        <div class="text-muted small"><i class="fas fa-circle-info me-1"></i>No welders assigned to this project's tasks yet.</div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($assignedWelders as $welderName): ?>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rh-avatar bg-amber text-dark" style="width:36px; height:36px; font-weight:800; font-size:0.85rem;">
                                    <?= strtoupper(substr($welderName, 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-700 text-light-emphasis" style="font-size:0.9rem;"><?= htmlspecialchars($welderName) ?></div>
                                    <div class="text-muted small" style="font-size:0.75rem;">Assigned Fabrication Welder</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CUSTOMER DETAILS PANEL (FOR ADMIN & STAFF REVIEW) -->
            <?php if (in_array($userRole, ['admin', 'staff'])): ?>
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <span class="fw-800 text-light-emphasis"><i class="fas fa-user-tie me-2 text-amber"></i>Customer Contact Details</span>
                </div>
                <div class="card-body border-top">
                    <div class="row g-3">
                        <div class="col-12 border-bottom pb-2">
                            <span class="text-muted d-block small fw-700">CLIENT NAME</span>
                            <span class="fw-600 text-light-emphasis"><?= htmlspecialchars($order['customer_name'] ?? 'Guest Customer') ?></span>
                        </div>
                        <div class="col-6 border-bottom pb-2">
                            <span class="text-muted d-block small fw-700">PHONE NUMBER</span>
                            <span class="fw-600 text-light-emphasis"><?= htmlspecialchars($order['customer_phone'] ?? '—') ?></span>
                        </div>
                        <div class="col-6 border-bottom pb-2">
                            <span class="text-muted d-block small fw-700">EMAIL</span>
                            <span class="fw-600 text-light-emphasis"><?= htmlspecialchars($order['customer_email'] ?? '—') ?></span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block small fw-700">CLIENT HOME ADDRESS</span>
                            <span class="fw-600 text-light-emphasis"><?= htmlspecialchars($order['customer_address'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- BLUEPRINT VS ACTUAL FABRICATION IMAGES -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <span class="fw-800 text-light-emphasis"><i class="fas fa-images me-2 text-amber"></i>Fabrication Blueprint vs Progress</span>
                </div>
                <div class="card-body border-top">
                    <div class="row g-2">
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700 mb-1">REFERENCE DESIGN</span>
                            <img src="../<?= $order['image'] ?? 'assets/images/no-image.png' ?>"
                                 class="img-fluid rounded-3 border" style="height:120px; width:100%; object-fit:cover;"
                                 onerror="this.src='../assets/images/no-image.png'" alt="Design Reference">
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700 mb-1">ACTUAL BUILD PROGRESS</span>
                            <img src="../<?= $order['reference_image'] ?? 'assets/images/no-image.png' ?>"
                                 class="img-fluid rounded-3 border" style="height:120px; width:100%; object-fit:cover;"
                                 onerror="this.src='../assets/images/no-image.png'" alt="Actual Progress Build">
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
</body>
</html>