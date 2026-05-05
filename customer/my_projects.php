<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}

$customerId = $_SESSION['user_id'];

/* SUMMARY DATA */
$summary = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(status='On-going') as ongoing,
        SUM(status='Completed') as completed,
        SUM(status='For Delivery') as delivery
    FROM custom_orders
    WHERE customer_id = ?
");
$summary->bind_param("i", $customerId);
$summary->execute();
$s = $summary->get_result()->fetch_assoc();

/* PAGINATION */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/* TOTAL */
$totalQuery = $conn->prepare("SELECT COUNT(*) as total FROM custom_orders WHERE customer_id=?");
$totalQuery->bind_param("i",$customerId);
$totalQuery->execute();
$totalResult = $totalQuery->get_result()->fetch_assoc();
$totalRows = $totalResult['total'];
$totalPages = ceil($totalRows / $limit);

/* DATA */
$stmt = $conn->prepare("
    SELECT id, project_name, category, dimensions, description, image, status, created_at
    FROM custom_orders
    WHERE customer_id = ?
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $customerId, $limit, $offset);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Projects</title>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/customer-dashboard.css">
<link rel="stylesheet" href="../assets/css/projects.css">
<link rel="stylesheet" href="../assets/css/my_projects.css"> <!-- 🔥 NEW -->
</head>

<body>

<div class="app-layout">
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/header.php'; ?>

<div class="main customer-dashboard">

<div class="card">
<h2>My Projects</h2>
<p>Track your custom fabrication projects</p>


<div class="table-wrapper">

<table class="modern-table">
<thead>
<tr>
<th>Project</th>
<th>Status</th>
<th>Progress</th>
<th>Date</th>
<th>Details</th>
</tr>
</thead>

<tbody id="projects-body">

<?php foreach($projects as $p): 
$status = $p['status'];
$statusClass = strtolower(str_replace(' ','-',$status));

/* PROGRESS LOGIC */
$progress = match($status){
    'Appointment' => 20,
    'Pending' => 30,
    'Approved' => 40,
    'On-going' => 60,
    'For Delivery' => 85,
    'Completed' => 100,
    'Cancelled' => 100, // or 0 depending on your design
    default => 5
};
?>

<tr>
<td>
<div class="project-cell">
<img src="<?= $p['image'] ? '../'.$p['image'] : '../assets/img/default-project.png' ?>">
<div>
<strong><?= htmlspecialchars($p['project_name'] ?? 'Unnamed') ?></strong><br>
<span class="material"><?= htmlspecialchars($p['category'] ?? 'General') ?></span>
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
<div class="progress-bar-fill progress-<?= strtolower(str_replace(' ','-',$status)) ?>"
     style="width:<?= $progress ?>%">
</div>
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

<?php endforeach; ?>

</tbody>
</table>

</div>

<!-- PAGINATION -->
<div class="pagination">
<?php for($i=1;$i<=$totalPages;$i++): ?>
<a href="?page=<?= $i ?>" class="<?= $i==$page?'active':'' ?>">
<?= $i ?>
</a>
<?php endfor; ?>
</div>

</div>
</div>
</div>

<script>
let openRows = new Set();

function toggleDetails(id){
    const row = document.getElementById("details-"+id);
    if(openRows.has(id)){
        openRows.delete(id);
        row.classList.remove("active");
    }else{
        openRows.add(id);
        row.classList.add("active");
    }
}

/* AJAX REFRESH (keeps pagination) */
let lastUpdate = null;

function checkForUpdates(){
    fetch('check_updates.php')
    .then(res => res.json())
    .then(data => {

        if(lastUpdate === null){
            lastUpdate = data.last_update;
            return;
        }

        if(data.last_update !== lastUpdate){
            lastUpdate = data.last_update;

            loadProjects(); // 🔥 ONLY refresh when changed
        }
    });
}

function loadProjects(){
    fetch('fetch_projects.php?page=<?= $page ?>&t=' + Date.now())
    .then(res => res.text())
    .then(html => {
        document.getElementById("projects-body").innerHTML = html;

        // restore expanded rows
        openRows.forEach(id=>{
            const row = document.getElementById("details-"+id);
            if(row) row.classList.add("active");
        });
    });
}

/* CHECK EVERY 5 SECONDS (LIGHTWEIGHT) */
setInterval(checkForUpdates, 5000);

</script>

</body>
</html>