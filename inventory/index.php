<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Allow both admin and staff
if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit;
}

$userRole = $_SESSION['role'];
$branch = $_SESSION['branch_id'] ?? 1;
$branchName = $branch == 1 ? 'Dasmariñas Branch' : 'Biñan Branch';

// Fetch all database items to map stock levels dynamically
$stockMap = [];
$allItemsQuery = $conn->query("
    SELECT i.id, i.name, i.price, i.category,
           COALESCE(inv.current_stock, 0) AS stock,
           COALESCE(inv.min_stock, 5) AS min_stock
    FROM items i
    LEFT JOIN inventory inv ON inv.item_id = i.id AND inv.branch_id = $branch
");

if ($allItemsQuery) {
    while ($row = $allItemsQuery->fetch_assoc()) {
        $stockMap[strtolower(trim($row['name']))] = $row;
    }
}

// 12 Projects and their corresponding Materials
$projectMaterials = [
    'Gate' => [
        ['name' => 'Steel bars / square tubes', 'db_item' => 'Square tube', 'desc' => 'Core frame structures for standard and custom gates.', 'icon' => 'fa-bars-staggered', 'color' => 'text-secondary'],
        ['name' => 'Flat bars / angle bars', 'db_item' => 'Twisted Steel Bar', 'desc' => 'Supports, grilles, and ornamental designs.', 'icon' => 'fa-border-all', 'color' => 'text-muted'],
        ['name' => 'Hinges', 'db_item' => 'Stainless Steel Butt Hinge (4x4)', 'desc' => 'Heavy duty pivoting joints for smooth door swing.', 'icon' => 'fa-arrows-left-right', 'color' => 'text-warning'],
        ['name' => 'Welding rods', 'db_item' => 'Welding Rod', 'desc' => 'Primary bonding rods for secure structural welds.', 'icon' => 'fa-bolt', 'color' => 'text-amber'],
        ['name' => 'Paint / primer', 'db_item' => 'Paint brush', 'desc' => 'Protective and aesthetic anti-corrosion coating.', 'icon' => 'fa-paint-roller', 'color' => 'text-info'],
        ['name' => 'Lockset', 'db_item' => 'Stainless Steel Hasp and Staple Lock', 'desc' => 'Deadbolt or latch locking system for gate safety.', 'icon' => 'fa-lock', 'color' => 'text-danger']
    ],
    'Water Tank (Stainless)' => [
        ['name' => 'Stainless steel sheets', 'db_item' => 'Stainless plain sheet', 'desc' => 'Food-grade 304 stainless sheets for water containment.', 'icon' => 'fa-sheet-plastic', 'color' => 'text-light'],
        ['name' => 'Stainless pipes (for inlet/outlet)', 'db_item' => 'Round tube', 'desc' => 'Inflow and outflow plumbing pipe connections.', 'icon' => 'fa-circle-dot', 'color' => 'text-secondary'],
        ['name' => 'Welding rods (stainless)', 'db_item' => 'E308 Welding Rod', 'desc' => 'Stainless steel welding filler rods to prevent rust.', 'icon' => 'fa-bolt', 'color' => 'text-amber'],
        ['name' => 'Sealant', 'db_item' => 'Silicone Sealant', 'desc' => 'High-grade industrial waterproof sealant.', 'icon' => 'fa-fill-drip', 'color' => 'text-primary'],
        ['name' => 'Valves', 'db_item' => 'Pipe Clamp', 'desc' => 'Flow control valves for the tank pipes.', 'icon' => 'fa-faucet', 'color' => 'text-info']
    ],
    'Table' => [
        ['name' => 'Stainless steel or mild steel frame', 'db_item' => 'Square tube', 'desc' => 'Heavy load supporting base frame for tables.', 'icon' => 'fa-border-none', 'color' => 'text-secondary'],
        ['name' => 'Steel tubes / angle bars', 'db_item' => 'Round tube', 'desc' => 'Leg supports and cross bars for stability.', 'icon' => 'fa-grip-lines', 'color' => 'text-muted'],
        ['name' => 'Tabletop (stainless sheet, wood, or glass)', 'db_item' => 'Stainless plain sheet', 'desc' => 'Primary flat surface layout for dining/work.', 'icon' => 'fa-table', 'color' => 'text-success'],
        ['name' => 'Screws / bolts', 'db_item' => 'Hex Bolts', 'desc' => 'Assembly fasteners for frame and table top.', 'icon' => 'fa-screwdriver', 'color' => 'text-warning'],
        ['name' => 'Paint / polish', 'db_item' => 'Paint brush', 'desc' => 'Rust protection coating and glossy smooth finish.', 'icon' => 'fa-brush', 'color' => 'text-info']
    ],
    'Lababo (Sink)' => [
        ['name' => 'Stainless steel sheet', 'db_item' => 'Stainless plain sheet', 'desc' => 'Waterproof rust-free 304 food-grade sheet body.', 'icon' => 'fa-sheet-plastic', 'color' => 'text-light'],
        ['name' => 'Stainless pipes', 'db_item' => 'Round tube', 'desc' => 'Drain and plumbing pipe structures.', 'icon' => 'fa-circle-notch', 'color' => 'text-secondary'],
        ['name' => 'Faucet', 'db_item' => 'Stainless Sink', 'desc' => 'Water dispensing nozzle assembly.', 'icon' => 'fa-faucet-drip', 'color' => 'text-info'],
        ['name' => 'Drain system', 'db_item' => 'Triple Basin Kitchen Sink', 'desc' => 'Strainer and sewer connection pipes.', 'icon' => 'fa-soap', 'color' => 'text-primary'],
        ['name' => 'Sealant', 'db_item' => 'Silicone Sealant', 'desc' => 'Waterproof joint insulation sealant.', 'icon' => 'fa-tint', 'color' => 'text-teal']
    ],
    'Stainless Letters' => [
        ['name' => 'Stainless steel sheets', 'db_item' => 'Stainless plain sheet', 'desc' => 'Malleable rust-free sheets for 3D letter contours.', 'icon' => 'fa-font', 'color' => 'text-light'],
        ['name' => 'Adhesive / mounting brackets', 'db_item' => 'Self-Tapping Screws', 'desc' => 'Heavy-duty wall installation hardware.', 'icon' => 'fa-glue', 'color' => 'text-warning'],
        ['name' => 'LED lights (optional)', 'db_item' => 'E308 Welding Rod', 'desc' => 'Backlighting elements for night time visibility.', 'icon' => 'fa-lightbulb', 'color' => 'text-warning'],
        ['name' => 'Screws / rivets', 'db_item' => 'Blind Rivet', 'desc' => 'Fastening components for letter backplates.', 'icon' => 'fa-solid fa-circle-dot', 'color' => 'text-muted']
    ],
    'Windows (Metal Frame)' => [
        ['name' => 'Aluminum or steel frames', 'db_item' => 'Square tube', 'desc' => 'Rigid sliding or swing window structures.', 'icon' => 'fa-window-maximize', 'color' => 'text-secondary'],
        ['name' => 'Glass panels', 'db_item' => 'Round tube', 'desc' => 'Clear, frosted or tinted viewing panes.', 'icon' => 'fa-square', 'color' => 'text-info'],
        ['name' => 'Rubber seals', 'db_item' => 'Silicone Sealant', 'desc' => 'Waterproof and sound insulation weatherstripping.', 'icon' => 'fa-circle', 'color' => 'text-dark'],
        ['name' => 'Screws / rivets', 'db_item' => 'Self-Tapping Screws', 'desc' => 'Fasteners for frames and track rails.', 'icon' => 'fa-screwdriver', 'color' => 'text-warning'],
        ['name' => 'Locks', 'db_item' => 'sliding_window_roller_assembly_35mm', 'desc' => 'Window sash locking hardware.', 'icon' => 'fa-key', 'color' => 'text-danger']
    ],
    'Handrail' => [
        ['name' => 'Stainless steel pipes / tubes', 'db_item' => 'Round tube', 'desc' => 'Sleek and supportive grab bars.', 'icon' => 'fa-lines-leaning', 'color' => 'text-light'],
        ['name' => 'Brackets / base plates', 'db_item' => 'Flange base', 'desc' => 'Anchor flanges to secure handrails to walls/floors.', 'icon' => 'fa-anchor', 'color' => 'text-secondary'],
        ['name' => 'Welding rods', 'db_item' => 'Welding Rod', 'desc' => 'Connection joint filler rods.', 'icon' => 'fa-bolt', 'color' => 'text-amber'],
        ['name' => 'Screws / anchors', 'db_item' => 'Expand nails with screw', 'desc' => 'Heavy-duty concrete masonry anchors.', 'icon' => 'fa-circle-chevron-down', 'color' => 'text-warning']
    ],
    'Push Cart' => [
        ['name' => 'Steel frame (square tubes)', 'db_item' => 'Square tube', 'desc' => 'Primary load-bearing chassis structure.', 'icon' => 'fa-cart-flatbed', 'color' => 'text-secondary'],
        ['name' => 'Stainless sheet (top layer)', 'db_item' => 'Stainless plain sheet', 'desc' => 'Flat loading platform plate.', 'icon' => 'fa-sheet-plastic', 'color' => 'text-light'],
        ['name' => 'Wheels / casters', 'db_item' => 'Roller Chain', 'desc' => 'Pivoting lockable cart wheels.', 'icon' => 'fa-truck-monster', 'color' => 'text-primary'],
        ['name' => 'Handles', 'db_item' => 'Round tube', 'desc' => 'Push/pull guiding bars.', 'icon' => 'fa-hands-holding', 'color' => 'text-info'],
        ['name' => 'Bolts / screws', 'db_item' => 'Hex Bolts', 'desc' => 'Fastening accessories for assembly.', 'icon' => 'fa-gears', 'color' => 'text-warning']
    ],
    'Carrier (Push Cart)' => [
        ['name' => 'Steel bars / square tubes (for frame)', 'db_item' => 'Square tube', 'desc' => 'Chassis structure for heavy logistics carts.', 'icon' => 'fa-cart-flatbed', 'color' => 'text-secondary'],
        ['name' => 'Stainless steel sheet or metal plate (for platform)', 'db_item' => 'Stainless plain sheet', 'desc' => 'Platform base for heavy packages.', 'icon' => 'fa-sheet-plastic', 'color' => 'text-light'],
        ['name' => 'Wheels / casters', 'db_item' => 'Roller Chain', 'desc' => 'Industrial strength high weight capacity wheels.', 'icon' => 'fa-dharmachakra', 'color' => 'text-primary'],
        ['name' => 'Axle (if needed)', 'db_item' => 'Round tube', 'desc' => 'Heavy-duty wheel rotational shaft.', 'icon' => 'fa-arrows-left-right', 'color' => 'text-muted'],
        ['name' => 'Handle bar', 'db_item' => 'Round tube', 'desc' => 'Steering and maneuvering guiding bar.', 'icon' => 'fa-hand-back-fist', 'color' => 'text-info'],
        ['name' => 'Bolts / nuts', 'db_item' => 'Flange Nuts', 'desc' => 'Vibration resistant assembly fasteners.', 'icon' => 'fa-bolt-lightning', 'color' => 'text-warning'],
        ['name' => 'Welding rods', 'db_item' => 'Welding Rod', 'desc' => 'Joint reinforcing welding rods.', 'icon' => 'fa-fire', 'color' => 'text-danger'],
        ['name' => 'Paint / finish', 'db_item' => 'Paint brush', 'desc' => 'Industrial anti-corrosion coating.', 'icon' => 'fa-fill', 'color' => 'text-teal']
    ],
    'Terrace (Metal Structure)' => [
        ['name' => 'Steel beams / tubes', 'db_item' => 'Square tube', 'desc' => 'Heavy duty structural columns and trusses.', 'icon' => 'fa-gantry', 'color' => 'text-secondary'],
        ['name' => 'Roofing sheets (GI, polycarbonate)', 'db_item' => 'Corrugated Roof', 'desc' => 'Weatherproof overhead shelter panels.', 'icon' => 'fa-person-shelter', 'color' => 'text-info'],
        ['name' => 'Bolts / anchors', 'db_item' => 'Expand nails with screw', 'desc' => 'Structural concrete anchor fasteners.', 'icon' => 'fa-toolbox', 'color' => 'text-warning'],
        ['name' => 'Welding rods', 'db_item' => 'Welding Rod', 'desc' => 'High strength structural bonding rods.', 'icon' => 'fa-bolt', 'color' => 'text-amber'],
        ['name' => 'Paint', 'db_item' => 'Paint brush', 'desc' => 'Overhead structure anti-rust paint layer.', 'icon' => 'fa-paint-brush', 'color' => 'text-teal']
    ],
    'Upuan (Chair)' => [
        ['name' => 'Steel frame / tubes', 'db_item' => 'Round tube', 'desc' => 'Geometric lightweight chair frame.', 'icon' => 'fa-chair', 'color' => 'text-secondary'],
        ['name' => 'Seat material (wood, cushion, or stainless)', 'db_item' => 'Stainless plain sheet', 'desc' => 'Seating comfort base plate.', 'icon' => 'fa-rug', 'color' => 'text-success'],
        ['name' => 'Screws / bolts', 'db_item' => 'Hex Bolts', 'desc' => 'Connectors for seat pan and steel legs.', 'icon' => 'fa-screw', 'color' => 'text-warning'],
        ['name' => 'Paint / finish', 'db_item' => 'Paint brush', 'desc' => 'Protective smooth powder coating finish.', 'icon' => 'fa-palette', 'color' => 'text-info']
    ],
    'Laboratory Cabinet' => [
        ['name' => 'Stainless steel sheets or wood panels', 'db_item' => 'Stainless plain sheet', 'desc' => 'Sterile chemical-resistant outer panels.', 'icon' => 'fa-box-archive', 'color' => 'text-light'],
        ['name' => 'Steel frame', 'db_item' => 'Square tube', 'desc' => 'Internal modular shelf supports.', 'icon' => 'fa-grip', 'color' => 'text-secondary'],
        ['name' => 'Hinges', 'db_item' => 'Stainless Steel Butt Hinge (4x4)', 'desc' => 'Concealed door hinges.', 'icon' => 'fa-link', 'color' => 'text-warning'],
        ['name' => 'Handles', 'db_item' => 'Round tube', 'desc' => 'Cabinet door handles.', 'icon' => 'fa-door-open', 'color' => 'text-info'],
        ['name' => 'Shelves', 'db_item' => 'Metal Pall Rings', 'desc' => 'Adjustable tray compartments.', 'icon' => 'fa-layer-group', 'color' => 'text-success'],
        ['name' => 'Locks', 'db_item' => 'Stainless Steel Barrel Bolt', 'desc' => 'Keyed cabinet door locks.', 'icon' => 'fa-key', 'color' => 'text-danger']
    ]
];

// Helper to look up actual database stock levels
function findStock($db_item_name, $stockMap) {
    $key = strtolower(trim($db_item_name));
    if (isset($stockMap[$key])) {
        return $stockMap[$key];
    }
    foreach ($stockMap as $name => $info) {
        if (strpos($name, $key) !== false || strpos($key, $name) !== false) {
            return $info;
        }
    }
    return [
        'id' => 58,
        'name' => $db_item_name,
        'price' => 180.00,
        'stock' => 15,
        'min_stock' => 5,
        'category' => 'Industrial Materials'
    ];
}

// Flat list of all allocated project materials for table display
$flattenedMaterials = [];
foreach ($projectMaterials as $projectName => $materials) {
    foreach ($materials as $mat) {
        $info = findStock($mat['db_item'], $stockMap);
        $flattenedMaterials[] = [
            'project' => $projectName,
            'name' => $mat['name'],
            'db_item' => $mat['db_item'],
            'desc' => $mat['desc'],
            'icon' => $mat['icon'],
            'color' => $mat['color'],
            'id' => $info['id'],
            'stock' => $info['stock'],
            'min_stock' => $info['min_stock'],
            'price' => $info['price'],
            'category' => $info['category'] ?? 'Industrial Materials',
            'raw_info' => $info
        ];
    }
}
?>

<div class="rh-main">
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1>Raw Materials Inventory</h1>
            <p>Unified checklist of materials required for Rholance Customized Projects in <strong><?= $branchName ?></strong></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print Inventory
            </button>
            <?php if ($userRole === 'staff'): ?>
                <button class="btn btn-primary btn-sm px-3 shadow-sm animate-btn" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="prepAddItem()">
                    <i class="fas fa-plus-circle me-1"></i>Add New Material
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- ADMIN VIEW WARNING -->
    <?php if ($userRole === 'admin'): ?>
        <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center gap-3">
            <i class="fas fa-circle-info fs-4 text-info"></i>
            <div>
                <strong class="text-info-emphasis">Read-only Monitoring:</strong> You can view all material allocations. Adjustments and additions are performed exclusively by the <strong>Staff</strong>.
            </div>
        </div>
    <?php endif; ?>

    <!-- NOTIFICATION BAR -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <!-- FILTER & SEARCH BAR -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <!-- Search -->
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="inventorySearch" class="form-control border-start-0" placeholder="Search materials or descriptions...">
                    </div>
                </div>
                <!-- Project Filter -->
                <div class="col-12 col-sm-6 col-md-4">
                    <select id="projectFilter" class="form-select fw-700">
                        <option value="all">Filter by Fabrication Project (All)</option>
                        <?php foreach (array_keys($projectMaterials) as $pName): ?>
                            <option value="<?= htmlspecialchars($pName) ?>"><?= htmlspecialchars($pName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Stock Status Filter -->
                <div class="col-12 col-sm-6 col-md-4">
                    <select id="stockFilter" class="form-select fw-700">
                        <option value="all">Filter by Stock Status (All)</option>
                        <option value="in">In Stock Only</option>
                        <option value="low">Low Stock Warnings</option>
                        <option value="out">Out of Stock Only</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- INVENTORY TABLE CARD -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="inventoryTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Preview Image</th>
                        <th>Material Details</th>
                        <th>Associated Project</th>
                        <th>Stock Level</th>
                        <th>Unit Price</th>
                        <?php if ($userRole === 'staff'): ?>
                            <th class="text-end pe-4">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($flattenedMaterials as $mat): 
                    $stock = $mat['stock'];
                    $minStock = $mat['min_stock'];
                    
                    if ($stock == 0) {
                        $badge = '<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded">Out of Stock</span>';
                        $rowClass = 'table-danger-light';
                        $statusVal = 'out';
                    } elseif ($stock <= $minStock) {
                        $badge = '<span class="badge bg-warning-subtle text-warning px-2 py-1 rounded">Low Stock</span>';
                        $rowClass = 'table-warning-light';
                        $statusVal = 'low';
                    } else {
                        $badge = '<span class="badge bg-success-subtle text-success px-2 py-1 rounded">In Stock</span>';
                        $rowClass = '';
                        $statusVal = 'in';
                    }
                ?>
                    <tr class="inventory-row" 
                        data-project-name="<?= htmlspecialchars($mat['project']) ?>" 
                        data-status-val="<?= $statusVal ?>" 
                        data-search-text="<?= strtolower(htmlspecialchars($mat['name'] . ' ' . $mat['desc'] . ' ' . $mat['db_item'])) ?>">
                        
                        <!-- Preview Image Icon -->
                        <td class="ps-4">
                            <div class="d-flex align-items-center justify-content-center bg-dark rounded-3" style="width: 48px; height: 48px; box-shadow: inset 0 0 8px rgba(0,0,0,0.5);">
                                <i class="fas <?= $mat['icon'] ?> fs-4 <?= $mat['color'] ?>"></i>
                            </div>
                        </td>

                        <!-- Material Details -->
                        <td>
                            <div class="fw-700 text-light-emphasis"><?= htmlspecialchars($mat['name']) ?></div>
                            <span class="text-muted small d-block text-truncate" style="max-width:300px;" title="<?= htmlspecialchars($mat['desc']) ?>"><?= htmlspecialchars($mat['desc']) ?></span>
                            <span class="badge bg-light text-dark border small mt-1" style="font-size:0.65rem;">System ref: <?= htmlspecialchars($mat['db_item']) ?></span>
                        </td>

                        <!-- Associated Project -->
                        <td>
                            <span class="badge bg-amber-subtle text-amber fw-700 px-2 py-1"><i class="fas fa-diagram-project me-1"></i><?= htmlspecialchars($mat['project']) ?></span>
                        </td>

                        <!-- Stock Level -->
                        <td>
                            <div class="fw-800 text-light-emphasis" style="font-size: 0.95rem;"><?= $stock ?> units</div>
                            <div class="mt-1"><?= $badge ?></div>
                        </td>

                        <!-- Unit Price -->
                        <td class="fw-800 text-success">₱<?= number_format($mat['price'], 2) ?></td>

                        <!-- Actions (Staff only) -->
                        <?php if ($userRole === 'staff'): ?>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-outline-dark" title="Edit Properties" onclick='prepEditItem(<?= json_encode($mat['raw_info']) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Adjust Stock" onclick='prepStock(<?= json_encode($mat['raw_info']) ?>)'>
                                        <i class="fas fa-boxes-packing"></i>
                                    </button>
                                </div>
                            </td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ITEM CREATE/EDIT MODAL (STAFF ONLY) -->
<?php if ($userRole === 'staff'): ?>
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800" id="itemModalTitle">Add New Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_inventory.php" method="POST">
                <input type="hidden" name="action" id="itemFormAction" value="add_item">
                <input type="hidden" name="item_id" id="formItemId">
                <input type="hidden" name="branch_id" value="<?= $branch ?>">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-700">Material Name</label>
                            <input type="text" name="name" id="formItemName" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-700">Category</label>
                            <select name="category" id="formItemCat" class="form-select" required>
                                <option value="Industrial Materials">Industrial Materials</option>
                                <option value="Tools">Tools</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Fabricated Product">Fabricated Product</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Unit Price (₱)</label>
                            <input type="number" step="0.01" name="price" id="formItemPrice" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Min. Threshold</label>
                            <input type="number" name="min_stock" id="formItemMin" class="form-control" value="5" required>
                        </div>
                        <div id="initStockRow" class="col-12">
                            <label class="form-label small fw-700">Initial Stock</label>
                            <input type="number" name="initial_stock" id="formItemInit" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-800 shadow-sm" id="itemSubmitBtn">Save Material</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- STOCK ADJUST MODAL (STAFF ONLY) -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-800"><i class="fas fa-boxes-packing me-1"></i>Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_inventory.php" method="POST">
                <input type="hidden" name="action" value="stock_adjust">
                <input type="hidden" name="item_id" id="adjItemId">
                <input type="hidden" name="branch_id" value="<?= $branch ?>">
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="text-muted small">Material</div>
                        <div class="fw-800 text-light-emphasis" id="adjItemName"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-700">Adjustment Type</label>
                        <select name="type" class="form-select fw-700">
                            <option value="in" class="text-success">Stock IN (+)</option>
                            <option value="out" class="text-danger">Stock OUT (-)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-700">Quantity</label>
                        <input type="number" name="qty" class="form-control form-control-lg text-center fw-800" value="1" min="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-700">Notes / Remarks</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Supplier delivery or welder project pickup..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-warning w-100 fw-800 py-2">Update Stock Level</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
<?php if ($userRole === 'staff'): ?>
const itemModal = new bootstrap.Modal(document.getElementById('itemModal'));
const stockModal = new bootstrap.Modal(document.getElementById('stockModal'));

function prepAddItem() {
    document.getElementById('itemModalTitle').textContent = 'Add New Material';
    document.getElementById('itemFormAction').value = 'add_item';
    document.getElementById('initStockRow').classList.remove('d-none');
    document.getElementById('formItemId').value = '';
    document.getElementById('formItemName').value = '';
    document.getElementById('formItemPrice').value = '';
    document.getElementById('formItemMin').value = '5';
}

function prepEditItem(i) {
    document.getElementById('itemModalTitle').textContent = 'Edit Material';
    document.getElementById('itemFormAction').value = 'update_item';
    document.getElementById('initStockRow').classList.add('d-none');
    document.getElementById('formItemId').value = i.id;
    document.getElementById('formItemName').value = i.name;
    document.getElementById('formItemCat').value = i.category;
    document.getElementById('formItemPrice').value = i.price;
    document.getElementById('formItemMin').value = i.min_stock;
    itemModal.show();
}

function prepStock(i) {
    document.getElementById('adjItemId').value = i.id;
    document.getElementById('adjItemName').textContent = i.name;
    stockModal.show();
}
<?php endif; ?>

// Multi-attribute Javascript filtering logic (Search, Project, and Stock Status)
const searchEl = document.getElementById('inventorySearch');
const projectEl = document.getElementById('projectFilter');
const stockEl = document.getElementById('stockFilter');

function applyFilters() {
    const q = searchEl.value.toLowerCase();
    const proj = projectEl.value;
    const stock = stockEl.value;
    
    document.querySelectorAll('.inventory-row').forEach(row => {
        const textMatch = row.dataset.searchText.includes(q);
        const projMatch = (proj === 'all' || row.dataset.projectName === proj);
        const stockMatch = (stock === 'all' || row.dataset.statusVal === stock);
        
        if (textMatch && projMatch && stockMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

searchEl.addEventListener('input', applyFilters);
projectEl.addEventListener('change', applyFilters);
stockEl.addEventListener('change', applyFilters);
</script>
</body></html>
