<?php
require_once '../includes/auth_check.php';
if ($_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit; }

include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM custom_product_variants WHERE id = $id");
    header("Location: custom_variants.php?msg=deleted");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $variant_name = $conn->real_escape_string($_POST['variant_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $image_url = '';

    // Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/variants/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $filename = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = 'uploads/variants/' . $filename;
        }
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Edit
        $id = intval($_POST['id']);
        if ($image_url) {
            $conn->query("UPDATE custom_product_variants SET product_name='$product_name', variant_name='$variant_name', description='$description', image_url='$image_url' WHERE id=$id");
        } else {
            $conn->query("UPDATE custom_product_variants SET product_name='$product_name', variant_name='$variant_name', description='$description' WHERE id=$id");
        }
        header("Location: custom_variants.php?msg=updated");
    } else {
        // Add
        if (!$image_url) $image_url = 'assets/images/placeholder.png';
        $conn->query("INSERT INTO custom_product_variants (product_name, variant_name, description, image_url) VALUES ('$product_name', '$variant_name', '$description', '$image_url')");
        header("Location: custom_variants.php?msg=added");
    }
    exit;
}

// Fetch all variants
$variants = $conn->query("SELECT * FROM custom_product_variants ORDER BY product_name ASC, created_at DESC");
?>

<div class="rh-main">
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h1>Custom Product Variants</h1>
            <p>Manage the design variations available for custom products.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#variantModal" onclick="resetForm()">
            <i class="fas fa-plus me-1"></i> Add Variant
        </button>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
        if ($_GET['msg'] == 'added') echo 'Variant added successfully!';
        if ($_GET['msg'] == 'updated') echo 'Variant updated successfully!';
        if ($_GET['msg'] == 'deleted') echo 'Variant deleted successfully!';
        ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Variant Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($variants && $variants->num_rows > 0): ?>
                            <?php while ($row = $variants->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><img src="<?= BASE_URL . $row['image_url'] ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                                <td><?= htmlspecialchars($row['variant_name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick='editVariant(<?= json_encode($row) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="custom_variants.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this variant?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No variants found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="variantModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="custom_variants.php" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Variant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="var_id">
                <div class="mb-3">
                    <label>Product Name</label>
                    <select name="product_name" id="var_product" class="form-select" required>
                        <option value="Gate">Gate</option>
                        <option value="Water Tank (Stainless)">Water Tank (Stainless)</option>
                        <option value="Table">Table</option>
                        <option value="Lababo (Sink)">Lababo (Sink)</option>
                        <option value="Stainless Letters">Stainless Letters</option>
                        <option value="Windows (Metal Frame)">Windows (Metal Frame)</option>
                        <option value="Handrail">Handrail</option>
                        <option value="Push Cart">Push Cart</option>
                        <option value="Carrier (Push Cart)">Carrier (Push Cart)</option>
                        <option value="Terrace (Metal Structure)">Terrace (Metal Structure)</option>
                        <option value="Upuan (Chair)">Upuan (Chair)</option>
                        <option value="Laboratory Cabinet">Laboratory Cabinet</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Variant Name</label>
                    <input type="text" name="variant_name" id="var_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Image (Upload new to replace)</label>
                    <input type="file" name="image" id="var_image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" id="var_desc" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Variant</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Add Variant';
    document.getElementById('var_id').value = '';
    document.getElementById('var_product').value = 'Gate';
    document.getElementById('var_name').value = '';
    document.getElementById('var_image').required = true;
    document.getElementById('var_desc').value = '';
}

function editVariant(data) {
    document.getElementById('modalTitle').textContent = 'Edit Variant';
    document.getElementById('var_id').value = data.id;
    document.getElementById('var_product').value = data.product_name;
    document.getElementById('var_name').value = data.variant_name;
    document.getElementById('var_image').required = false;
    document.getElementById('var_desc').value = data.description;
    new bootstrap.Modal(document.getElementById('variantModal')).show();
}
</script>

</body>
</html>
