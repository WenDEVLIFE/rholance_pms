<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';

if ($_SESSION['role'] !== 'customer') {
    header("Location: ../dashboard/index.php");
    exit;
}

$customerId = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'ongoing';

/* 
   Ongoing: 'Appointment','Initial Payment','On-going','For Delivery','Backjobs'
   Finished: 'Completed'
   Old: 'Cancelled'
*/

$status_map = [
    'ongoing' => "('Appointment','Initial Payment','On-going','For Delivery','Backjobs')",
    'finished' => "('Completed')",
    'old' => "('Cancelled')"
];

$filter_sql = $status_map[$status_filter] ?? $status_map['ongoing'];

$query = "SELECT * FROM custom_orders WHERE customer_id = ? AND status IN $filter_sql ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$projects = $stmt->get_result();

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main customer-dashboard">

    <div class="dashboard-header">
        <h1>MY PROJECTS</h1>
        <div class="filter-tabs">
            <a href="?status=ongoing" class="<?= $status_filter === 'ongoing' ? 'active' : '' ?>">Ongoing</a>
            <a href="?status=finished" class="<?= $status_filter === 'finished' ? 'active' : '' ?>">Finished</a>
            <a href="?status=old" class="<?= $status_filter === 'old' ? 'active' : '' ?>">Old Transactions</a>
        </div>
    </div>

    <div class="project-grid">
        <?php if ($projects->num_rows > 0): ?>
            <?php while ($p = $projects->fetch_assoc()): ?>
                <div class="project-card glass-premium">
                    <div class="project-image">
                        <img src="../<?= $p['image'] ?? 'assets/images/no-image.png' ?>" alt="Project">
                        <span class="status-tag <?= strtolower(str_replace(' ','-',$p['status'])) ?>">
                            <?= $p['status'] ?>
                        </span>
                    </div>
                    <div class="project-info">
                        <h3><?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?></h3>
                        <p class="category"><?= htmlspecialchars($p['category'] ?? 'General') ?></p>
                        
                        <div class="progress-container">
                            <?php 
                                $progress = 0;
                                switch($p['status']) {
                                    case 'Appointment': $progress = 10; break;
                                    case 'Initial Payment': $progress = 30; break;
                                    case 'On-going': $progress = 60; break;
                                    case 'For Delivery': $progress = 90; break;
                                    case 'Completed': $progress = 100; break;
                                    case 'Backjobs': $progress = 80; break;
                                }
                            ?>
                            <div class="progress-label">
                                <span>Progress</span>
                                <span><?= $progress ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>

                        <div class="project-footer">
                            <span class="date"><i class="far fa-calendar"></i> <?= date('M d, Y', strtotime($p['created_at'])) ?></span>
                            <a href="project_details.php?id=<?= $p['id'] ?>" class="btn-details">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>No projects found in this category.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="/rholance_pms/assets/js/darkmode.js"></script>
<style>
.filter-tabs {
    display: flex;
    gap: 15px;
    margin-top: 15px;
}
.filter-tabs a {
    padding: 8px 20px;
    border-radius: 20px;
    background: #fff;
    color: var(--text-muted);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
    border: 1px solid var(--border);
}
.filter-tabs a.active {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
    box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);
}
.project-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 30px;
}
.project-card {
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.project-image {
    height: 180px;
    position: relative;
}
.project-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.status-tag {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: white;
}
.status-appointment { background: #64748B; }
.status-initial-payment { background: #3B82F6; }
.status-on-going { background: #F59E0B; }
.status-for-delivery { background: #10B981; }
.status-backjobs { background: #EF4444; }
.status-completed { background: #059669; }
.status-cancelled { background: #94A3B8; }

.project-info {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}
.project-info h3 {
    margin: 0;
    font-size: 18px;
    color: var(--primary);
}
.project-info .category {
    font-size: 13px;
    color: var(--text-muted);
    margin: 4px 0 20px;
}
.progress-container {
    margin-bottom: 20px;
}
.progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 8px;
}
.progress-bar {
    height: 8px;
    background: #E2E8F0;
    border-radius: 4px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: var(--accent);
    transition: width 0.5s ease-out;
}
.project-footer {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid var(--border);
    padding-top: 15px;
}
.project-footer .date {
    font-size: 12px;
    color: var(--text-muted);
}
.btn-details {
    font-size: 13px;
    font-weight: 700;
    color: var(--accent);
    text-decoration: none;
}
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px;
    color: var(--text-muted);
}
.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.3;
}
</style>

</body>
</html>