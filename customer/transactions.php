<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

if ($_SESSION['role'] !== 'customer') { header("Location: ../index.php"); exit; }

$cid   = $_SESSION['user_id'];
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$total = $conn->prepare("SELECT COUNT(*) c FROM transactions t JOIN custom_orders co ON co.id=t.order_id WHERE co.customer_id=?");
$total->bind_param("i",$cid); $total->execute();
$totalRows  = $total->get_result()->fetch_assoc()['c'];
$totalPages = (int)ceil($totalRows / $limit);

$txStmt = $conn->prepare("
    SELECT t.*, co.project_name, co.status order_status
    FROM transactions t
    JOIN custom_orders co ON co.id = t.order_id
    WHERE co.customer_id = ?
    ORDER BY t.id DESC
    LIMIT ? OFFSET ?
");
$txStmt->bind_param("iii", $cid, $limit, $offset);
$txStmt->execute();
$txRows = $txStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="rh-main">

    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>My Transactions</h1>
            <p>Payment history for all your projects.</p>
        </div>
        <button class="btn btn-warning fw-800" data-bs-toggle="modal" data-bs-target="#payModal">
            <i class="fas fa-file-invoice-dollar me-2"></i>Submit Payment Proof
        </button>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Project</th>
                        <th>Payment Method</th>
                        <th>Remarks</th>
                        <th>Order Status</th>
                        <th>Date</th>
                        <th>Proof</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($txRows)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fs-2 mb-2 d-block opacity-25"></i>
                            No transactions yet. <a href="customize.php">Submit a project</a> to get started.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($txRows as $tx):
                        $osCls = 'badge-'.strtolower(str_replace([' ','/'],'-',$tx['order_status']));
                        $pmIcon = $tx['payment_method'] === 'GCash' ? 'fas fa-mobile-alt text-primary' : 'fas fa-money-bill-wave text-success';
                    ?>
                    <tr>
                        <td class="text-muted small">#<?= $tx['id'] ?></td>
                        <td class="fw-700"><?= htmlspecialchars($tx['project_name'] ?? '—') ?></td>
                        <td>
                            <?php if ($tx['payment_method']): ?>
                                <i class="<?= $pmIcon ?> me-1"></i>
                                <?= htmlspecialchars($tx['payment_method']) ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= htmlspecialchars($tx['remarks'] ?? '—') ?></td>
                        <td><span class="badge <?= $osCls ?>"><?= $tx['order_status'] ?></span></td>
                        <td class="small text-muted">
                            <?= isset($tx['created_at']) ? date('M d, Y', strtotime($tx['created_at'])) : '—' ?>
                        </td>
                        <td>
                            <?php if (!empty($tx['payment_proof'])): ?>
                                <a href="../uploads/<?= htmlspecialchars($tx['payment_proof']) ?>"
                                   target="_blank" class="btn btn-sm btn-outline-dark">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?></small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page-1 ?>">‹ Prev</a>
                    </li>
                    <?php endif; ?>
                    <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
                    <li class="page-item <?= $i==$page?'active':'' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?>">Next ›</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    <!-- FEEDBACK -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm mt-4"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

</div>

<!-- PAYMENT MODAL -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800">Submit Payment Proof</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/submit_payment.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-700">Select Project</label>
                        <select name="order_id" class="form-select" required>
                            <option value="" disabled selected>-- Select Ongoing Project --</option>
                            <?php
                            $projects = $conn->query("SELECT id, project_name FROM custom_orders WHERE customer_id=$cid AND status != 'Completed'");
                            while ($p = $projects->fetch_assoc()):
                            ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['project_name']) ?> (ID: <?= $p['id'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-700">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="GCash">GCash</option>
                            <option value="Bank Transfer">Bank Transfer / Online</option>
                            <option value="Other">Other Digital Payment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-700">Upload Proof (Receipt/Screenshot)</label>
                        <input type="file" name="payment_proof" class="form-control" accept="image/*" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-700">Reference / Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Reference No. 123456"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning w-100 fw-800">Submit Payment Details</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body></html>