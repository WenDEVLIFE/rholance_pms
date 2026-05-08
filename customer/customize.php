<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';

// Ensure role is customer
if ($_SESSION['role'] !== 'customer') {
    header("Location: ../dashboard/index.php");
    exit;
}

$customerId = $_SESSION['user_id'];
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main customer-dashboard">

    <div class="dashboard-header">
        <h1>CUSTOMIZE PRODUCT</h1>
        <p class="subtitle">Design your project and we'll bring it to life.</p>
    </div>

    <div class="card glass-premium">
        <form action="api/store_custom_order.php" method="POST" enctype="multipart/form-data" class="modern-form">
            
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Project Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Project Name</label>
                        <input type="text" name="project_name" placeholder="e.g. Modern Sliding Gate" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="" disabled selected>Select Category</option>
                            <option value="Gates">Gates</option>
                            <option value="Railings">Railings</option>
                            <option value="Window Grills">Window Grills</option>
                            <option value="Trusses">Trusses</option>
                            <option value="Furniture">Industrial Furniture</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-tools"></i> Material & Specifications</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Preferred Material</label>
                        <select name="material" required>
                            <option value="" disabled selected>Select Material</option>
                            <option value="Stainless Steel (304)">Stainless Steel (304)</option>
                            <option value="Stainless Steel (201)">Stainless Steel (201)</option>
                            <option value="Iron / Mild Steel">Iron / Mild Steel</option>
                            <option value="Aluminum">Aluminum</option>
                            <option value="Galvanized Steel">Galvanized Steel</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dimensions (L x W x H)</label>
                        <input type="text" name="dimensions" placeholder="e.g. 10ft x 6ft" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Additional Instructions / Description</label>
                    <textarea name="description" rows="4" placeholder="Describe your project requirements in detail..."></textarea>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-camera"></i> Reference Image (Expectation)</h3>
                <div class="form-group">
                    <label class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Upload Reference Design</span>
                        <input type="file" name="reference_image" accept="image/*" class="file-input">
                    </label>
                    <small>Upload a photo of the design you want us to replicate.</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-modern">
                    <i class="fas fa-paper-plane"></i> Submit Custom Request
                </button>
            </div>

        </form>
    </div>

</div>

<script src="/rholance_pms/assets/js/darkmode.js"></script>
<style>
.modern-form {
    display: flex;
    flex-direction: column;
    gap: 30px;
    padding: 20px;
}
.form-section h3 {
    font-size: 18px;
    margin-bottom: 20px;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 2px solid var(--accent);
    padding-bottom: 10px;
    width: fit-content;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.form-group label {
    font-weight: 600;
    font-size: 14px;
    color: var(--text);
}
.form-group input, 
.form-group select, 
.form-group textarea {
    padding: 12px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #fff;
    font-family: inherit;
    transition: border-color 0.3s;
}
.form-group input:focus, 
.form-group select:focus, 
.form-group textarea:focus {
    border-color: var(--accent);
    outline: none;
}
.file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    border: 2px dashed var(--border);
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.3s;
    background: rgba(245, 158, 11, 0.05);
}
.file-upload-label:hover {
    border-color: var(--accent);
    background: rgba(245, 158, 11, 0.1);
}
.file-upload-label i {
    font-size: 32px;
    color: var(--accent);
    margin-bottom: 10px;
}
.file-input {
    display: none;
}
.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

</body>
</html>
