<?php
require_once '../config/database.php';
session_start();

$customerId = $_SESSION['user_id'];

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("
    SELECT id, project_name, category, dimensions, description, image, status, created_at
    FROM custom_orders
    WHERE customer_id = ?
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii",$customerId,$limit,$offset);
$stmt->execute();
$result = $stmt->get_result();

while($p=$result->fetch_assoc()):
$status = $p['status'];
$statusClass = strtolower(str_replace(' ','-',$status));

$progress = match($status){
    'Pending'=>10,
    'Approved'=>30,
    'On-going'=>60,
    'For Delivery'=>85,
    'Completed'=>100,
    default=>0
};
?>

<tr>
<td>
<div class="project-cell">
<img src="<?= $p['image'] ? '../'.$p['image'] : '../assets/img/default-project.png' ?>">
<div>
<strong><?= htmlspecialchars($p['project_name'] ?? 'Unnamed') ?></strong><br>
<span><?= htmlspecialchars($p['category'] ?? 'General') ?></span>
</div>
</div>
</td>

<td>
<span class="status-badge status-<?= $statusClass ?>">
<?= $status ?>
</span>
</td>

<td>
<div class="progress-bar">
<div style="width:<?= $progress ?>%"></div>
</div>
</td>

<td><?= date('M d, Y', strtotime($p['created_at'])) ?></td>

<td>
<button class="btn-view" onclick="toggleDetails(<?= $p['id'] ?>)">View</button>
</td>
</tr>

<tr id="details-<?= $p['id'] ?>" class="details-row">
<td colspan="5">
<div class="details-box">
<div class="detail-item">
<span>Dimensions</span>
<strong><?= htmlspecialchars($p['dimensions'] ?? 'N/A') ?></strong>
</div>
<div class="detail-item">
<span>Description</span>
<strong><?= htmlspecialchars($p['description'] ?? 'No description') ?></strong>
</div>
</div>
</td>
</tr>

<?php endwhile; ?>