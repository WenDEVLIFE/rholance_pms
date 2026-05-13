<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

if ($_SESSION['role'] !== 'customer') { header("Location: ../index.php"); exit; }
$cid = $_SESSION['user_id'];
$sf  = $_GET['status'] ?? 'ongoing';
$map = [
    'ongoing'  => "'Appointment','Initial Payment','On-going','For Delivery','Backjobs'",
    'finished' => "'Completed'",
    'old'      => "'Cancelled'"
];
$filter = $map[$sf] ?? $map['ongoing'];
$stmt = $conn->prepare("SELECT * FROM custom_orders WHERE customer_id = ? AND status IN ($filter) ORDER BY created_at DESC");
$stmt->bind_param("i",$cid); $stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="rh-main">
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>My Projects</h1>
            <p>Track all your custom fabrication orders.</p>
        </div>
        <a href="customize.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Order</a>
    </div>

    <!-- FILTER TABS -->
    <div class="rh-tabs mb-4">
        <a href="?status=ongoing"  class="rh-tab <?= $sf==='ongoing' ?'active':'' ?>">Ongoing</a>
        <a href="?status=finished" class="rh-tab <?= $sf==='finished'?'active':'' ?>">Finished</a>
        <a href="?status=old"      class="rh-tab <?= $sf==='old'     ?'active':'' ?>">Old Transactions</a>
    </div>

    <?php if (empty($projects)): ?>
        <div class="card p-5 text-center text-muted">
            <i class="fas fa-folder-open fs-1 mb-3 opacity-25 d-block"></i>
            No projects in this category.
        </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($projects as $p):
            $pct = ['Appointment'=>10,'Initial Payment'=>30,'On-going'=>60,'For Delivery'=>85,'Backjobs'=>50,'Completed'=>100][$p['status']] ?? 0;
            $cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$p['status']));
        ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="rh-proj-card">
                <div class="rh-proj-thumb">
                    <img src="../<?= $p['image'] ?? 'assets/images/no-image.png' ?>"
                         onerror="this.src='../assets/images/no-image.png'" alt="">
                    <span class="badge <?= $cls ?> status-float"><?= $p['status'] ?></span>
                </div>
                <div class="rh-proj-body">
                    <h6><?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?></h6>
                    <p class="proj-meta">
                        <span class="badge bg-light text-dark fw-600 me-1"><?= htmlspecialchars($p['category'] ?? '') ?></span>
                        <?= htmlspecialchars($p['material'] ?? '') ?>
                    </p>
                    <div class="d-flex justify-content-between mb-1" style="font-size:.75rem;font-weight:700;">
                        <span>Progress</span><span><?= $pct ?>%</span>
                    </div>
                    <div class="progress mb-3" style="height:7px;">
                        <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                    </div>
                    <div class="rh-proj-footer">
                        <small class="text-muted"><i class="far fa-calendar me-1"></i><?= date('M d, Y',strtotime($p['created_at'])) ?></small>
                        <a href="project_details.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning fw-700">View Details</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

</body></html>