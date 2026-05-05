<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}

$customerId = $_SESSION['user_id'];

/* ===============================
   SUMMARY
=============================== */
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

/* ===============================
   PAGINATION
=============================== */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$totalQuery = $conn->prepare("SELECT COUNT(*) as total FROM custom_orders WHERE customer_id=?");
$totalQuery->bind_param("i",$customerId);
$totalQuery->execute();
$totalRows = $totalQuery->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* ===============================
   DATA
=============================== */
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
<link rel="stylesheet" href="../assets/css/project.css">
</head>

<body>

<div class="app-layout">
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/header.php'; ?>

<div class="main customer-dashboard">
<div class="projects-page">
<div class="card">

<h2>My Projects</h2>
<p>Track your custom fabrication projects</p>

<!-- ===============================
     SUMMARY CARDS
================================ -->
<div class="summary-cards">

<div class="card-box">
<h4>Total</h4>
<p><?= $s['total'] ?></p>
</div>

<div class="card-box">
<h4>On-going</h4>
<p><?= $s['ongoing'] ?></p>
</div>

<div class="card-box">
<h4>Delivery</h4>
<p><?= $s['delivery'] ?></p>
</div>

<div class="card-box">
<h4>Completed</h4>
<p><?= $s['completed'] ?></p>
</div>

</div>

<!-- ===============================
     FILTERS
================================ -->
<div class="filters">
<button onclick="filterStatus('all')">All</button>
<button onclick="filterStatus('On-going')">On-going</button>
<button onclick="filterStatus('Completed')">Completed</button>
<button onclick="filterStatus('For Delivery')">For Delivery</button>
<button onclick="filterStatus('Appointment')">Appointment</button>
<button onclick="filterStatus('Cancelled')">Cancelled</button>
</div>

<!-- ===============================
     TABLE
================================ -->
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

<?php if(empty($projects)): ?>
<tr>
<td colspan="5" style="text-align:center; padding:30px;">
No projects yet.
</td>
</tr>
<?php endif; ?>

<?php foreach($projects as $p): 
$status = $p['status'];
$statusClass = strtolower(str_replace(' ','-',$status));

$progress = match($status){
    'Appointment'=>20,
    'On-going'=>60,
    'For Delivery'=>85,
    'Completed'=>100,
    'Cancelled'=>100,
    default=>10
};
?>

<tr>
<td>
<div class="project-cell">
<img src="<?= $p['image'] ? '../'.$p['image'] : '../assets/img/default-project.png' ?>">
<div>
<strong><?= htmlspecialchars($p['project_name']) ?></strong><br>
<span class="material"><?= htmlspecialchars($p['category']) ?></span>
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
<div class="progress-bar-fill" style="width:<?= $progress ?>%"></div>
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
<strong><?= htmlspecialchars($p['dimensions']) ?></strong>
</div>

<div class="detail-item">
<span>Description</span>
<strong><?= htmlspecialchars($p['description']) ?></strong>
</div>

</div>
</td>
</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>

<!-- ===============================
     PAGINATION
================================ -->
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
</div>

<script>
function toggleDetails(id){
    const row = document.getElementById("details-"+id);
    row.classList.toggle("active");
}

function filterStatus(status){
    const rows = document.querySelectorAll("#projects-body tr");

    rows.forEach(row=>{
        const badge = row.querySelector(".status-badge");
        if(!badge) return;

        const text = badge.innerText.trim();

        row.style.display = (status==='all' || text===status) ? '' : 'none';
    });
}
</script>

</body>
</html>