<?php
require_once '../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$branch = $_SESSION['branch_id'];

/* =========================
   FILTER HANDLING
========================= */

$allowedFilters = [
    'active','completed','cancelled','backjobs',
    'Appointment','Initial Payment','On-going','For Delivery'
];

$statusFilter = $_GET['status'] ?? '';

if(!in_array($statusFilter, $allowedFilters)){
    $statusFilter = '';
}

$where = "";

if ($statusFilter === 'active') {
    $where = "AND o.status IN ('Appointment','Initial Payment','On-going','For Delivery')";
} elseif ($statusFilter === 'completed') {
    $where = "AND o.status = 'Completed'";
} elseif ($statusFilter === 'cancelled') {
    $where = "AND o.status = 'Cancelled'";
} elseif ($statusFilter === 'backjobs') {
    $where = "AND o.status = 'Backjobs'";
}

/* =========================
   PAGINATION
========================= */

$limit = 10; // items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;


$totalResult = $conn->query("
    SELECT COUNT(DISTINCT o.id) as total
    FROM custom_orders o
    WHERE o.branch_id = $branch
    $where
");

$totalRow = $totalResult->fetch_assoc();
$totalOrders = $totalRow['total'];

$totalPages = ceil($totalOrders / $limit);

/* =========================
   FETCH ORDERS
========================= */

$orders = $conn->query("
SELECT 
    o.id,
    o.status,
    o.order_type,
    o.customer_name,
    o.created_at,
    o.expected_date,
    u.name AS staff_name,

    GROUP_CONCAT(
        CONCAT(i.name, ' x', item_summary.qty)
        SEPARATOR ', '
    ) AS products

FROM custom_orders o

LEFT JOIN users u ON o.user_id = u.id

LEFT JOIN (
    SELECT 
        order_id,
        item_id,
        SUM(quantity) AS qty
    FROM order_items
    GROUP BY order_id, item_id
) item_summary ON item_summary.order_id = o.id

LEFT JOIN items i ON i.id = item_summary.item_id

WHERE o.branch_id = $branch
$where

GROUP BY o.id
ORDER BY o.created_at DESC

LIMIT $limit OFFSET $offset
");

?>



<div class="main">

<div class="page-top-bar">
    <button class="btn-back" onclick="goBack()">
        <i class="fa-solid fa-arrow-left"></i>
        Back to Dashboard
    </button>
</div>

<h2>
<?php
if($statusFilter === 'active') echo "Active Orders";
elseif($statusFilter === 'completed') echo "Completed Orders";
else echo "All Orders";
?>
</h2>

<div class="card">

<table class="modern-table">

<tr>
    <th>Material</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($o = $orders->fetch_assoc()): ?>

<tr>
    <!-- MATERIAL -->
    <td class="truncate">

<?php
$products = explode(',', $o['products'] ?? '');
$basePath = "/rholance_pms/assets/images/orders/";

$maxDisplay = 3;
$totalItems = count($products);
$currentIndex = 0;
?>

<div class="order-item">

    <!-- MULTIPLE IMAGES -->
    <div class="order-images">

<?php foreach($products as $p): ?>

    <?php
    if ($currentIndex >= $maxDisplay) break;

    $p = trim(strtolower($p));
    $name = preg_replace('/ x\d+/', '', $p);
    $name = ucwords(trim($name));

    $img = $basePath . $name . ".jpg";
    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $img)) {
        $img = $basePath . $name . ".png";
    }
    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $img)) {
        $img = $basePath . "default.jpg";
    }

    $currentIndex++;
    ?>

   <img 
    src="<?= $img ?>" 
    class="order-thumb"
    onclick="openPreview(this)"
    data-images='<?php echo json_encode($products); ?>'
>

<?php endforeach; ?>

<?php if ($totalItems > $maxDisplay): ?>
    <div class="more-count">
        +<?= $totalItems - $maxDisplay ?>
    </div>
<?php endif; ?>

</div>

    <!-- TEXT -->
    <div class="order-info">
        <span><?= htmlspecialchars($o['products'] ?? 'No Items') ?></span>
    </div>

</div>

</td>

    <!-- STATUS -->
    <td>
    <?php if($_SESSION['role'] === 'staff'): ?>

        <form method="POST" action="update_status.php">
            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">

            <select name="status" class="status-dropdown">
                <option value="Appointment" <?= $o['status']=='Appointment'?'selected':'' ?>>Appointment</option>
                <option value="Initial Payment" <?= $o['status']=='Initial Payment'?'selected':'' ?>>Initial Payment</option>
                <option value="On-going" <?= $o['status']=='On-going'?'selected':'' ?>>On-going</option>
                <option value="For Delivery" <?= $o['status']=='For Delivery'?'selected':'' ?>>For Delivery</option>
                <option value="Backjobs" <?= $o['status']=='Backjobs'?'selected':'' ?>>Backjobs</option>
                <option value="Completed" <?= $o['status']=='Completed'?'selected':'' ?>>Completed</option>
                <option value="Cancelled" <?= $o['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
            </select>

            <button type="submit" class="btn-update">Update</button>
        </form>

    <?php else: ?>

        <span class="status-badge">
            <?= htmlspecialchars($o['status']) ?>
        </span>

    <?php endif; ?>
    </td>

    <!-- ACTION -->
    <td>
        <button class="btn-view" onclick="toggleDetails(<?= $o['id'] ?>)">
            View Details
        </button>
    </td>
</tr>

<tr id="details-<?= $o['id'] ?>" class="details-row no-hover" style="display:none;">
    <td colspan="3">

        <div class="order-details-modern">

    <div class="detail-card neutral">
        <span class="label">Handled By</span>
        <span class="value"><?= htmlspecialchars($o['staff_name'] ?? 'Not Assigned') ?></span>
    </div>

    <div class="detail-card neutral">
        <span class="label">Customer</span>
        <span class="value"><?= htmlspecialchars($o['customer_name'] ?? 'N/A') ?></span>
    </div>

    <div class="detail-card neutral">
        <span class="label">Order Type</span>
        <span class="value"><?= ucfirst(htmlspecialchars($o['order_type'])) ?></span>
    </div>

    <div class="detail-card neutral">
        <span class="label">Status</span>
        <span class="value"><?= htmlspecialchars($o['status']) ?></span>
    </div>

   <div class="detail-card neutral">
        <span class="label">Created</span>
        <span class="value">
            <?= $o['created_at'] ? date('M d, Y • h:i A', strtotime($o['created_at'])) : 'N/A' ?>
        </span>
    </div>

<div class="detail-card neutral">
    <span class="label">Expected Completion</span>
    <span class="value">
        <?= !empty($o['expected_date']) 
            ? date('M d, Y', strtotime($o['expected_date'])) 
            : 'Not Set' ?>
    </span>
</div>

</div>

    </td>
</tr>

<?php endwhile; ?>

<!-- IMAGE PREVIEW MODAL -->
<div id="imagePreviewModal" class="img-modal">

    <div class="img-box">

        <!-- CLOSE BUTTON (INSIDE IMAGE) -->
        <span class="img-close" onclick="closePreview()">×</span>

        <!-- IMAGE -->
        <img id="previewImage">

        <!-- NAV -->
        <button id="prevBtn" class="img-nav left" onclick="prevImage()">❮</button>
        <button id="nextBtn" class="img-nav right" onclick="nextImage()">❯</button>

    </div>

</div>

</table>
<div class="pagination">

<?php if ($page > 1): ?>
    <a href="?status=<?= $statusFilter ?>&page=<?= $page - 1 ?>" class="page-btn">
        ←
    </a>
<?php endif; ?>

<?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?status=<?= $statusFilter ?>&page=<?= $i ?>"
       class="page-btn <?= $i == $page ? 'active' : '' ?>">
        <?= $i ?>
    </a>
<?php endfor; ?>

<?php if ($page < $totalPages): ?>
    <a href="?status=<?= $statusFilter ?>&page=<?= $page + 1 ?>" class="page-btn">
        →
    </a>
<?php endif; ?>

</div>


</div>
</div>

<script>
function toggleDetails(id){
    const row = document.getElementById('details-' + id);
    row.style.display = (row.style.display === "table-row") ? "none" : "table-row";
}

function goBack(){
    window.history.back();
}

let previewImages = [];
let currentIndex = 0;

function openPreview(el) {
    const raw = JSON.parse(el.getAttribute('data-images'));

    previewImages = raw.map(p => {
        let name = p.toLowerCase().trim();
        name = name.replace(/ x\d+/, '');
        name = name.charAt(0).toUpperCase() + name.slice(1);

        let base = "/rholance_pms/assets/images/orders/";

        return base + name + ".jpg|" + base + name + ".png";
    });

    currentIndex = 0;

    // ✅ SHOW / HIDE ARROWS
    const prev = document.getElementById("prevBtn");
    const next = document.getElementById("nextBtn");

    if (previewImages.length > 1) {
        prev.style.display = "block";
        next.style.display = "block";
    } else {
        prev.style.display = "none";
        next.style.display = "none";
    }

    document.getElementById("imagePreviewModal").style.display = "flex";
    updatePreview();
}

function updatePreview() {
    let paths = previewImages[currentIndex].split("|");

    let imgElement = document.getElementById("previewImage");

    imgElement.src = paths[0]; // try jpg first

    imgElement.onerror = function () {
        imgElement.src = paths[1]; // fallback to png
    };
}

function closePreview() {
    document.getElementById("imagePreviewModal").style.display = "none";
}

function nextImage() {
    currentIndex = (currentIndex + 1) % previewImages.length;
    updatePreview();
}

function prevImage() {
    currentIndex = (currentIndex - 1 + previewImages.length) % previewImages.length;
    updatePreview();
}

document.getElementById("imagePreviewModal").addEventListener("click", function(e){
    if(e.target === this){
        closePreview();
    }
});
</script>