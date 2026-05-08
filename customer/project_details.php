<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';

if ($_SESSION['role'] !== 'customer') {
    header("Location: ../dashboard/index.php");
    exit;
}

$projectId = $_GET['id'] ?? null;
if (!$projectId) {
    header("Location: my_projects.php");
    exit;
}

// Fetch Project Details
$stmt = $conn->prepare("SELECT * FROM custom_orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $projectId, $_SESSION['user_id']);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    header("Location: my_projects.php");
    exit;
}

// Fetch Assigned Staff (Welders)
$stmt = $conn->prepare("SELECT u.name FROM tasks t JOIN users u ON t.assigned_to = u.id WHERE t.order_id = ?");
$stmt->bind_param("i", $projectId);
$stmt->execute();
$staff = $stmt->get_result();

// Fetch Materials Breakdown
$stmt = $conn->prepare("SELECT oi.*, i.name AS item_name FROM order_items oi JOIN items i ON oi.item_id = i.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $projectId);
$stmt->execute();
$materials = $stmt->get_result();

$totalMaterials = 0;

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main customer-dashboard">

    <div class="dashboard-header">
        <a href="my_projects.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Projects</a>
        <h1>PROJECT DETAILS</h1>
    </div>

    <div class="details-grid">
        
        <!-- LEFT: INFO & MATERIALS -->
        <div class="details-left">
            
            <div class="card glass-premium">
                <div class="project-header-info">
                    <h2><?= htmlspecialchars($project['project_name'] ?? 'Custom Project') ?></h2>
                    <span class="status-badge status-<?= strtolower(str_replace(' ','-',$project['status'])) ?>">
                        <?= $project['status'] ?>
                    </span>
                </div>
                
                <div class="info-list">
                    <div class="info-item">
                        <label>Category</label>
                        <span><?= htmlspecialchars($project['category'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Materials</label>
                        <span><?= htmlspecialchars($project['material'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Dimensions</label>
                        <span><?= htmlspecialchars($project['dimensions'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Estimated Completion</label>
                        <span><?= $project['estimated_completion'] ? date('M d, Y', strtotime($project['estimated_completion'])) : 'TBD' ?></span>
                    </div>
                </div>

                <div class="description-box">
                    <label>Description</label>
                    <p><?= nl2br(htmlspecialchars($project['description'] ?? $project['instructions'] ?? 'No description provided.')) ?></p>
                </div>
            </div>

            <div class="card glass-premium">
                <h3><i class="fas fa-list-ul"></i> Materials Breakdown</h3>
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($materials->num_rows > 0): ?>
                            <?php while($m = $materials->fetch_assoc()): ?>
                                <?php $totalMaterials += $m['total_amount']; ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['item_name']) ?></td>
                                    <td><?= $m['quantity'] ?></td>
                                    <td>₱<?= number_format($m['price'], 2) ?></td>
                                    <td>₱<?= number_format($m['total_amount'], 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <tr class="total-row">
                                <td colspan="3">Total Material Cost</td>
                                <td>₱<?= number_format($totalMaterials, 2) ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="empty">No materials listed yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- RIGHT: PROGRESS & VISUALS -->
        <div class="details-right">
            
            <div class="card glass-premium">
                <h3><i class="fas fa-tasks"></i> Project Team</h3>
                <div class="staff-list">
                    <?php if ($staff->num_rows > 0): ?>
                        <?php while($s = $staff->fetch_assoc()): ?>
                            <div class="staff-item">
                                <i class="fas fa-user-cog"></i>
                                <span><?= htmlspecialchars($s['name']) ?> (Welder)</span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="empty">Personnel not yet assigned.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card glass-premium">
                <h3><i class="fas fa-eye"></i> Expectation vs Reality</h3>
                <div class="visual-comparison">
                    <div class="visual-box">
                        <label>Expectation (Reference)</label>
                        <img src="../<?= $project['image'] ?? 'assets/images/no-image.png' ?>" alt="Expectation">
                    </div>
                    <div class="visual-box">
                        <label>Reality (Actual Progress)</label>
                        <img src="../<?= $project['reference_image'] ?? 'assets/images/no-image.png' ?>" alt="Reality">
                    </div>
                </div>
            </div>

            <div class="card glass-premium">
                <h3><i class="fas fa-credit-card"></i> Payment</h3>
                <div class="payment-info">
                    <p>Total Balance: <span class="price">₱<?= number_format($totalMaterials * 1.5, 2) ?></span> <small>(incl. labor)</small></p>
                    <a href="add_payment.php?id=<?= $project['id'] ?>" class="btn-modern w-full">
                        <i class="fas fa-upload"></i> Upload Payment Proof
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>

<script src="/rholance_pms/assets/js/darkmode.js"></script>
<style>
.btn-back {
    text-decoration: none;
    color: var(--text-muted);
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 10px;
    display: block;
}
.details-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 25px;
    margin-top: 20px;
}
.project-header-info {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
}
.project-header-info h2 {
    margin: 0;
    font-size: 24px;
    color: var(--primary);
}
.info-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}
.info-item {
    display: flex;
    flex-direction: column;
}
.info-item label {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    font-weight: 700;
}
.info-item span {
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
}
.description-box {
    padding: 15px;
    background: #F8FAFC;
    border-radius: 10px;
}
.description-box label {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 8px;
    display: block;
}
.description-box p {
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}
.staff-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.staff-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: var(--text);
}
.staff-item i {
    color: var(--accent);
}
.visual-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
.visual-box label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    margin-bottom: 8px;
    display: block;
}
.visual-box img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--border);
}
.payment-info {
    text-align: center;
}
.payment-info p {
    margin-bottom: 15px;
    font-weight: 600;
}
.payment-info .price {
    font-size: 20px;
    color: var(--primary);
}
.w-full {
    width: 100%;
    justify-content: center;
}
.total-row {
    font-weight: 700;
    background: #F1F5F9;
}
@media (max-width: 1024px) {
    .details-grid {
        grid-template-columns: 1fr;
    }
}
</style>

</body>
</html>
