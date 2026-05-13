<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

if ($_SESSION['role'] !== 'customer') { header("Location: ../index.php"); exit; }
?>

<div class="rh-main">

    <div class="rh-page-header">
        <h1>Customize Product</h1>
        <p>Design your project and we'll bring it to life.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9 col-xl-8">
            <form action="api/store_custom_order.php" method="POST" enctype="multipart/form-data">

                <!-- PROJECT DETAILS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-2 text-amber"></i>Project Details
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Project Name</label>
                                <input type="text" name="project_name" class="form-control"
                                       placeholder="e.g. Modern Sliding Gate" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="" disabled selected>Select Category</option>
                                    <option>Gates</option>
                                    <option>Railings</option>
                                    <option>Window Grills</option>
                                    <option>Trusses</option>
                                    <option>Furniture</option>
                                    <option>Others</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MATERIAL & SPECS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-tools me-2 text-amber"></i>Material &amp; Specifications
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Preferred Material</label>
                                <select name="material" class="form-select" required>
                                    <option value="" disabled selected>Select Material</option>
                                    <option>Stainless Steel (304)</option>
                                    <option>Stainless Steel (201)</option>
                                    <option>Iron / Mild Steel</option>
                                    <option>Aluminum</option>
                                    <option>Galvanized Steel</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Dimensions (L × W × H)</label>
                                <input type="text" name="dimensions" class="form-control"
                                       placeholder="e.g. 10ft × 6ft">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Additional Instructions / Description</label>
                                <textarea name="description" class="form-control" rows="4"
                                          placeholder="Describe your project requirements in detail..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REFERENCE IMAGE -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-camera me-2 text-amber"></i>Reference Image <span class="text-muted fw-400">(Expectation)</span>
                    </div>
                    <div class="card-body">
                        <label class="rh-upload-zone w-100" for="refImg">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div class="fw-700 mb-1">Upload Reference Design</div>
                            <div class="text-muted small">PNG, JPG up to 5MB — shows us what you want</div>
                            <input type="file" id="refImg" name="reference_image" accept="image/*" class="d-none">
                        </label>
                        <div id="imgPreview" class="mt-2 text-center d-none">
                            <img id="previewImg" src="" class="img-fluid rounded-3" style="max-height:200px;">
                        </div>
                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-paper-plane me-2"></i>Submit Custom Request
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('refImg').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imgPreview').classList.remove('d-none');
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
</body></html>
