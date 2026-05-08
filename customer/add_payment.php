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

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main customer-dashboard">

    <div class="dashboard-header">
        <a href="project_details.php?id=<?= $projectId ?>" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Details</a>
        <h1>UPLOAD PAYMENT</h1>
    </div>

    <div class="card glass-premium" style="max-width: 600px; margin: 0 auto;">
        <form action="api/store_payment.php" method="POST" enctype="multipart/form-data" class="modern-form">
            <input type="hidden" name="project_id" value="<?= $projectId ?>">

            <div class="form-group">
                <label>Payment Method</label>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Cash" checked>
                        <div class="option-content">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Cash</span>
                        </div>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="GCash">
                        <div class="option-content">
                            <i class="fas fa-mobile-alt"></i>
                            <span>GCash</span>
                        </div>
                    </label>
                </div>
            </div>

            <div id="gcashSection" class="form-section" style="display: none;">
                <div class="gcash-guide">
                    <h4>GCash Payment Instructions</h4>
                    <p>Please send the amount to: <strong>0995 774 2174 (Rholance Trading)</strong></p>
                </div>
                <div class="form-group">
                    <label class="file-upload-label">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Upload GCash Receipt (Screenshot)</span>
                        <input type="file" name="payment_proof" accept="image/*" class="file-input">
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Remarks / Reference Number</label>
                <input type="text" name="remarks" placeholder="Optional notes or Ref #">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-modern w-full">
                    <i class="fas fa-check-circle"></i> Submit Payment Proof
                </button>
            </div>
        </form>
    </div>

</div>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const gcashSection = document.getElementById('gcashSection');
        if (this.value === 'GCash') {
            gcashSection.style.display = 'block';
        } else {
            gcashSection.style.display = 'none';
        }
    });
});
</script>

<style>
.payment-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
.payment-option {
    cursor: pointer;
}
.payment-option input {
    display: none;
}
.option-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px;
    border-radius: 12px;
    border: 2px solid var(--border);
    transition: all 0.3s;
}
.payment-option input:checked + .option-content {
    border-color: var(--accent);
    background: rgba(245, 158, 11, 0.1);
    color: var(--accent);
}
.option-content i {
    font-size: 24px;
}
.gcash-guide {
    background: #EFF6FF;
    border-left: 4px solid #3B82F6;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.gcash-guide h4 {
    margin: 0 0 5px;
    color: #1E40AF;
}
.gcash-guide p {
    margin: 0;
    font-size: 14px;
    color: #1E3A8A;
}
</style>

</body>
</html>
