<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

if ($_SESSION['role'] !== 'customer') { header("Location: ../index.php"); exit; }

$pid = (int)($_GET['id'] ?? 0);
if (!$pid) { header("Location: my_projects.php"); exit; }
?>

<div class="rh-main">

    <div class="rh-page-header">
        <a href="project_details.php?id=<?= $pid ?>" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fas fa-arrow-left me-1"></i>Back to Project
        </a>
        <h1>Upload Payment</h1>
        <p>Submit your proof of payment for project #<?= $pid ?>.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            <form action="api/store_payment.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="project_id" value="<?= $pid ?>">

                <!-- PAYMENT METHOD -->
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-credit-card me-2 text-amber"></i>Payment Method</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_method"
                                       id="pmCash" value="Cash" checked>
                                <label class="btn btn-outline-secondary w-100 py-3" for="pmCash">
                                    <i class="fas fa-money-bill-wave d-block fs-4 mb-1 text-success"></i>
                                    <strong>Cash</strong>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_method"
                                       id="pmGcash" value="GCash">
                                <label class="btn btn-outline-secondary w-100 py-3" for="pmGcash">
                                    <i class="fas fa-mobile-alt d-block fs-4 mb-1 text-primary"></i>
                                    <strong>GCash</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GCASH SECTION (hidden by default) -->
                <div id="gcashSection" class="card mb-4 d-none">
                    <div class="card-header"><i class="fas fa-receipt me-2 text-amber"></i>GCash Receipt</div>
                    <div class="card-body">
                        <div class="alert alert-info d-flex align-items-start gap-2 mb-3">
                            <i class="fas fa-info-circle mt-1"></i>
                            <div>
                                Send to: <strong>0995 774 2174 (Rholance Trading)</strong><br>
                                <small class="text-muted">Then upload your GCash screenshot below.</small>
                            </div>
                        </div>
                        <label class="rh-upload-zone w-100" for="proofFile">
                            <i class="fas fa-file-image"></i>
                            <div class="fw-700 mb-1">Upload GCash Screenshot</div>
                            <div class="text-muted small">PNG or JPG — take a screenshot of your GCash receipt</div>
                            <input type="file" id="proofFile" name="payment_proof" accept="image/*" class="d-none">
                        </label>
                        <div id="proofPreview" class="mt-2 text-center d-none">
                            <img id="proofImg" src="" class="img-fluid rounded-3 border" style="max-height:220px;">
                        </div>
                    </div>
                </div>

                <!-- REMARKS -->
                <div class="card mb-4">
                    <div class="card-body">
                        <label class="form-label">Remarks / Reference Number <span class="text-muted fw-400">(optional)</span></label>
                        <input type="text" name="remarks" class="form-control"
                               placeholder="e.g. GCash ref. 123456 or notes...">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 fw-700 fs-6">
                    <i class="fas fa-check-circle me-2"></i>Submit Payment Proof
                </button>

            </form>
        </div>
    </div>
</div>

<script>
/* Toggle GCash section */
document.querySelectorAll('input[name="payment_method"]').forEach(r => {
    r.addEventListener('change', () => {
        document.getElementById('gcashSection').classList.toggle('d-none', r.value !== 'GCash');
    });
});

/* Preview upload */
document.getElementById('proofFile').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('proofImg').src = e.target.result;
            document.getElementById('proofPreview').classList.remove('d-none');
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
</body></html>
