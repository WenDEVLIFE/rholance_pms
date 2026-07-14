import re

with open('orders/view_order.php', 'r') as f:
    content = f.read()

# 1. Fetch available welders for Cashier to assign
welder_query = """
// Fetch available welders
$availableWelders = [];
if (in_array($userRole, ['admin', 'staff'])) {
    $weldersQ = $conn->query("SELECT id, name FROM users WHERE role='welder' AND status='active'");
    while ($w = $weldersQ->fetch_assoc()) {
        $availableWelders[] = $w;
    }
}
"""
content = re.sub(r'(\$itemsQuery = \$conn->query\(")', welder_query + r'\n\1', content)

# 2. Add Workflow Cards to Right Column
workflow_cards = """
            <!-- PROJECT WORKFLOW ACTION CENTER -->
            <div class="card mb-4 border-0 shadow-sm border-top border-warning border-4">
                <div class="card-header bg-white py-3 border-0">
                    <span class="fw-800 text-light-emphasis"><i class="fas fa-tasks me-2 text-amber"></i>Workflow Actions</span>
                </div>
                <div class="card-body border-top">
                    
                    <?php if (in_array($userRole, ['admin', 'staff']) && empty($order['assigned_welder_id'])): ?>
                        <!-- 1. Assign Welder Form -->
                        <div class="alert alert-info border-0 rounded-3 small">
                            <form action="assign_welder.php" method="POST">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                <label class="fw-700 mb-1">Assign Welder to Visit:</label>
                                <select name="welder_id" class="form-select form-select-sm mb-2" required>
                                    <option value="">Select Welder...</option>
                                    <?php foreach ($availableWelders as $w): ?>
                                        <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label class="fw-700 mb-1">Visit Date:</label>
                                <input type="date" name="visit_date" class="form-control form-control-sm mb-2" required>
                                <label class="fw-700 mb-1">Visit Time:</label>
                                <input type="time" name="visit_time" class="form-control form-control-sm mb-3" required>
                                <button class="btn btn-sm btn-primary w-100 fw-700">Assign Welder</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($order['assigned_welder_id'])): ?>
                        <div class="mb-3 p-3 bg-light rounded-3 small">
                            <strong class="d-block mb-1">Scheduled Welder Visit:</strong>
                            <i class="fas fa-calendar text-muted me-1"></i> <?= $order['welder_visit_date'] ? date('M d, Y', strtotime($order['welder_visit_date'])) : 'TBD' ?> 
                            at <?= htmlspecialchars($order['welder_visit_time']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($userRole === 'welder' && $order['assigned_welder_id'] == $userId && $order['quote_status'] === 'Pending Review' && empty($order['quoted_price'])): ?>
                        <!-- 2. Welder Submits Quote -->
                        <div class="alert alert-warning border-0 rounded-3 small">
                            <form action="submit_quote.php" method="POST">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                <label class="fw-700 mb-1">Quoted Price (₱):</label>
                                <input type="number" step="0.01" name="quoted_price" class="form-control form-control-sm mb-2" required>
                                <label class="fw-700 mb-1">Estimated Deadline:</label>
                                <input type="date" name="quoted_deadline" class="form-control form-control-sm mb-2" required>
                                <label class="fw-700 mb-1">Pricing Breakdown / Specifications:</label>
                                <textarea name="quoted_breakdown" class="form-control form-control-sm mb-3" rows="3" required></textarea>
                                <button class="btn btn-sm btn-warning w-100 fw-700">Submit Quotation to Cashier</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($order['quoted_price'])): ?>
                        <div class="mb-3 p-3 bg-light rounded-3 small border">
                            <strong class="d-block mb-1">Welder's Quotation:</strong>
                            <span class="d-block">Price: ₱<?= number_format($order['quoted_price'], 2) ?></span>
                            <span class="d-block">Deadline: <?= date('M d, Y', strtotime($order['quoted_deadline'])) ?></span>
                            <span class="d-block mt-2 text-muted"><?= nl2br(htmlspecialchars($order['quoted_breakdown'])) ?></span>
                            <div class="mt-2 text-end fw-800">Status: <?= $order['quote_status'] ?></div>
                            
                            <?php if (in_array($userRole, ['admin', 'staff']) && $order['quote_status'] === 'Pending Review'): ?>
                                <hr>
                                <form action="approve_quote.php" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <button name="action" value="approve" class="btn btn-sm btn-success flex-grow-1 fw-700">Approve Quote</button>
                                    <button name="action" value="reject" class="btn btn-sm btn-danger fw-700">Reject</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($order['quote_status'] === 'Approved' && $order['payment_status'] !== 'Paid'): ?>
                        <?php if ($userRole === 'customer'): ?>
                            <!-- 3. Customer Uploads Receipt -->
                            <div class="alert alert-success border-0 rounded-3 small">
                                <form action="upload_receipt.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <label class="fw-700 mb-1">Upload Payment Receipt:</label>
                                    <input type="file" name="receipt" class="form-control form-control-sm mb-3" required accept="image/*">
                                    <button class="btn btn-sm btn-success w-100 fw-700">Submit Payment</button>
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (in_array($userRole, ['admin', 'staff']) && !empty($order['payment_receipt'])): ?>
                            <div class="mb-3 p-3 bg-light rounded-3 small">
                                <strong class="d-block mb-2">Customer Payment Receipt:</strong>
                                <a href="../<?= htmlspecialchars($order['payment_receipt']) ?>" target="_blank">
                                    <img src="../<?= htmlspecialchars($order['payment_receipt']) ?>" class="img-fluid rounded mb-2" style="max-height: 150px;">
                                </a>
                                <form action="verify_payment.php" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <button class="btn btn-sm btn-success w-100 fw-700">Verify Payment as Paid</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($order['payment_status'] === 'Paid'): ?>
                        <div class="alert alert-success border-0 rounded-3 small fw-700 text-center">
                            <i class="fas fa-check-circle me-1"></i> Payment Verified - Project Ongoing
                        </div>

                        <?php if ($userRole === 'welder' && $order['assigned_welder_id'] == $userId): ?>
                            <!-- 4. Welder Updates Progress -->
                            <div class="mb-3 p-3 bg-light rounded-3 small border border-warning">
                                <form action="update_progress.php" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <label class="fw-700 mb-1">Update Progress (%):</label>
                                    <input type="number" min="0" max="100" name="progress_percent" class="form-control form-control-sm mb-2" value="<?= $order['progress_percent'] ?>" required>
                                    <label class="fw-700 mb-1">Progress Details:</label>
                                    <textarea name="progress_details" class="form-control form-control-sm mb-3" rows="2" required></textarea>
                                    <button class="btn btn-sm btn-warning w-100 fw-700">Submit Progress for Approval</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array($userRole, ['admin', 'staff']) && $order['progress_status'] === 'Pending Approval'): ?>
                            <div class="alert alert-warning border-0 rounded-3 small">
                                <strong>Welder requests progress update to <?= $order['progress_percent'] ?>%</strong>
                                <form action="approve_progress.php" method="POST" class="mt-2">
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <button class="btn btn-sm btn-success w-100 fw-700">Approve Progress</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>
"""

content = re.sub(r'(<!-- CUSTOMER DETAILS PANEL \(FOR ADMIN & STAFF REVIEW\) -->)', workflow_cards + r'\n\n            \1', content)

# 3. Customer Upload Sketch (Left column)
sketch_form = """
                    <!-- CUSTOMER SKETCH UPLOAD -->
                    <div class="border-top pt-4 mt-4">
                        <span class="text-muted d-block small fw-800 mb-3" style="letter-spacing:0.5px;">PROJECT SKETCHES / IMAGES</span>
                        
                        <?php if (!empty($order['customer_sketch'])): ?>
                            <a href="../<?= htmlspecialchars($order['customer_sketch']) ?>" target="_blank">
                                <img src="../<?= htmlspecialchars($order['customer_sketch']) ?>" class="img-fluid rounded border mb-3" style="max-height: 250px;">
                            </a>
                        <?php else: ?>
                            <div class="text-muted small mb-3">No sketches uploaded yet.</div>
                        <?php endif; ?>

                        <?php if ($userRole === 'customer'): ?>
                            <form action="upload_sketch.php" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                <input type="file" name="sketch" class="form-control form-control-sm" accept="image/*" required>
                                <button class="btn btn-sm btn-primary fw-700">Upload</button>
                            </form>
                        <?php endif; ?>
                    </div>
"""
content = re.sub(r'(<?php if \(!empty\(\$order\[\'description\'\]\)\): ?>)', sketch_form + r'\n\n                    \1', content)

with open('orders/view_order.php', 'w') as f:
    f.write(content)
